<?php

namespace MstGhi\Larapool\IDPay;

use MstGhi\Larapool\Contracts\PortAbstract;
use MstGhi\Larapool\Contracts\PortInterface;

class IDPay extends PortAbstract implements PortInterface
{
    /**
     * Address of main CURL server
     *
     * @var string
     */
    protected $serverUrl = 'https://api.idpay.ir/v1.1/payment';

    /**
     * Address of CURL server for verify payment
     *
     * @var string
     */
    protected $serverVerifyUrl = 'https://api.idpay.ir/v1.1/payment/verify';

    /**
     * Address of gate for redirect
     *
     * @var string
     */
    protected $gateUrl = 'https://idpay.ir/p/ws/';

    /**
     * Sandbox address
     *
     * @var string
     */
    protected $sandboxGateUrl = 'https://idpay.ir/p/ws-sandbox/';

    /**
     * @var null|string
     */
    protected $orderId = null;

    /**
     * {@inheritdoc}
     */
    public function __construct($config, $request, int $portId)
    {
        parent::__construct($config, $request, $portId);
    }

    /**
     * {@inheritdoc}
     */
    public function set($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * {@inheritdoc}
     * @throws IDPaySendException
     */
    public function ready()
    {
        $this->sendPayRequest();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function redirect()
    {
        if ($this->config->get('larapool.idpay.sandbox')) {
            header('Location: ' . $this->sandboxGateUrl . $this->refId);
        } else {
            header('Location: ' . $this->gateUrl . $this->refId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function redirectLink()
    {
        if ($this->config->get('larapool.idpay.sandbox')) {
            return $this->sandboxGateUrl . $this->refId;
        } else {
            return $this->gateUrl . $this->refId;
        }
    }

    /**
     * {@inheritdoc}
     * @throws IDPayReceiveException
     */
    public function verify($transaction)
    {
        parent::verify($transaction);

        $this->userPayment();

        $this->verifyPayment();

        return $this;
    }

    /**
     * Send pay request to server
     *
     * @return bool
     *
     * @throws IDPaySendException
     */
    protected function sendPayRequest()
    {
        $this->newTransaction();

        $callback = $this->buildQuery(
            $this->config->get('larapool.idpay.callback-url'),
            array('t_id' => $this->transactionId())
        );

        $fields = array(
            'order_id' => $this->orderId,
            'amount' => $this->amount,
            'name' => $this->config->get('larapool.idpay.name'),
            'phone' => $this->config->get('larapool.idpay.user-mobile'),
            'mail' => $this->config->get('larapool.idpay.mail'),
            'desc' => $this->config->get('larapool.idpay.description'),
            'callback' => $callback
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_URL, $this->serverUrl);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'X-API-KEY:' . $this->config->get('idpay.api'),
            'X-SANDBOX:' . $this->config->get('idpay.sandbox'),
        ));

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (isset($response['link'])) {
            $this->refId = $response['id'];
            $this->transactionSetRefId();
            return true;
        }

        $this->transactionFailed();
        $this->newLog($response['status'], IDPaySendException::$errors[$response['errorCode']]);
        throw new IDPaySendException($response['errorCode']);
    }

    /**
     * Check user payment with GET data
     *
     * @return bool
     *
     * @throws IDPayReceiveException
     */
    protected function userPayment()
    {
        $status = $this->request->status;
        $this->trackingCode = $this->request->id;
        $this->cardNumber = $this->request->card_no;
        $this->refId = $this->request->track_id;

        if ($status == 10) {
            return true;
        }

        $this->transactionFailed();
        $this->newLog($status, IDPayReceiveException::$errors[$status]);
        throw new IDPayReceiveException($status);
    }

    /**
     * Verify user payment from zarinpal server
     *
     * @return bool
     *
     * @throws IDPayReceiveException
     */
    protected function verifyPayment()
    {
        $fields = array(
            'id' => $this->trackingCode,
            'order_id' => $this->orderId
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_URL, $this->serverVerifyUrl);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'X-API-KEY:' . $this->config->get('idpay.api'),
            'X-SANDBOX:' . $this->config->get('idpay.sandbox'),
        ));

        $response = json_decode(curl_exec($ch), true);

        curl_close($ch);

        if (isset($response['status']) && $response['status'] == 100 && $response['amount'] == $this->amount) {

            $this->transactionSucceed();
            $this->newLog($response['status'], self::TRANSACTION_SUCCEED_TEXT);
            return true;
        }

        $this->transactionFailed();
        $this->newLog($response['status'], IDPayReceiveException::$errors[$response['status']]);
        throw new IDPayReceiveException($response['status']);
    }

    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }
}
