<?php

namespace MstGhi\Larapool\Mellat;

use DateTime;
use MstGhi\Larapool\Contracts\PortAbstract;
use MstGhi\Larapool\Contracts\PortInterface;
use MstGhi\Larapool\Contracts\SoapClient;
use SoapFault;

class Mellat extends PortAbstract implements PortInterface
{
    /**
     * Address of main SOAP server
     *
     * @var string
     */
    protected $serverUrl = 'https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl';

    /**
     * Start pay url address
     *
     * @var string
     */
    protected $startPayUrl = 'https://bpm.shaparak.ir/pgwchannel/startpay.mellat';

    /**
     * @var null|string
     */
    protected $orderId = null;

    /**
     * @var null|string
     */
    protected $mobile = null;

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
    public function set(int $amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * {@inheritdoc}
     * @throws MellatException
     * @throws SoapFault
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
        $refId = $this->refId;
        $mobile = $this->mobile ?? $this->config->get('mellat.user-mobile');
        $startPayUrl = $this->startPayUrl;

        require 'MellatRedirect.php';
    }

    /**
     * {@inheritdoc}
     * @throws MellatException
     */
    public function redirectLink()
    {
       throw new MellatException('-101');
    }

    /**
     * {@inheritdoc}
     * @throws MellatException
     * @throws SoapFault
     */
    public function verify($transaction)
    {
        parent::verify($transaction);

        $this->userPayment();
        $this->verifyPayment();
        $this->settleRequest();

        return $this;
    }

    /**
     * @throws MellatException
     * @throws SoapFault
     */
    protected function sendPayRequest()
    {
        $dateTime = new DateTime();

        $this->newTransaction();

        $fields = array(
            'terminalId' => $this->config->get('mellat.terminalId'),
            'userName' => $this->config->get('mellat.username'),
            'userPassword' => $this->config->get('mellat.password'),
            'orderId' => $this->orderId ?? $this->transactionId(),
            'amount' => $this->amount,
            'localDate' => $dateTime->format('Ymd'),
            'localTime' => $dateTime->format('His'),
            'additionalData' => '',
            'callBackUrl' => $this->buildQuery(
                $this->config->get('mellat.callback-url'),
                array('transaction_id' => $this->transactionId)
            ),
            'payerId' => 0,
        );

        try {
            $soap = new SoapClient($this->serverUrl, $this->config);
            $response = $soap->bpPayRequest($fields);

        } catch (SoapFault $e) {
            $this->transactionFailed();
            $this->newLog('SoapFault', $e->getMessage());
            throw $e;
        }

        $response = explode(',', $response->return);

        if ($response[0] != '0') {
            $this->transactionFailed();
            $this->newLog($response[0], MellatException::$errors[$response[0]]);
            throw new MellatException($response[0]);
        }
        $this->refId = $response[1];
        $this->transactionSetRefId();
    }

    /**
     * Check user payment
     *
     * @return bool
     *
     * @throws MellatException
     */
    protected function userPayment()
    {
        $payRequestResCode = $this->request->ResCode;
        $this->trackingCode = $this->request->SaleReferenceId;
        $this->cardNumber = $this->request->CardHolderPan;
        $this->refId = $this->request->RefId;

        if ($payRequestResCode == '0') {
            return true;
        }

        $this->transactionFailed();
        $this->newLog($payRequestResCode, MellatException::$errors[$payRequestResCode]);
        throw new MellatException($payRequestResCode);
    }

    /**
     * Verify user payment from bank server
     *
     * @return bool
     * @throws MellatException
     * @throws SoapFault
     */
    protected function verifyPayment()
    {
        $fields = array(
            'terminalId' => $this->config->get('mellat.terminalId'),
            'userName' => $this->config->get('mellat.username'),
            'userPassword' => $this->config->get('mellat.password'),
            'orderId' => $this->orderId ?? $this->transactionId(),
            'saleOrderId' => $this->transactionId(),
            'saleReferenceId' => $this->trackingCode()
        );

        try {
            $soap = new SoapClient($this->serverUrl, $this->config);
            $response = $soap->bpVerifyRequest($fields);

        } catch (\SoapFault $e) {
            $this->transactionFailed();
            $this->newLog('SoapFault', $e->getMessage());
            throw $e;
        }

        if ($response->return != '0') {
            $this->transactionFailed();
            $this->newLog($response->return, MellatException::$errors[$response->return]);
            throw new MellatException($response->return);
        }

        $this->transactionSucceed();
        $this->newLog($response['status'], self::TRANSACTION_SUCCEED_TEXT);
        return true;
    }

    /**
     * Send settle request
     *
     * @return bool
     * @throws MellatException
     * @throws SoapFault
     */
    protected function settleRequest()
    {
        $fields = array(
            'terminalId' => $this->config->get('mellat.terminalId'),
            'userName' => $this->config->get('mellat.username'),
            'userPassword' => $this->config->get('mellat.password'),
            'orderId' => $this->orderId ?? $this->transactionId(),
            'saleOrderId' => $this->transactionId(),
            'saleReferenceId' => $this->trackingCode
        );

        try {
            $soap = new SoapClient($this->serverUrl, $this->config);
            $response = $soap->bpSettleRequest($fields);

        } catch (\SoapFault $e) {
            $this->transactionFailed();
            $this->newLog('SoapFault', $e->getMessage());
            throw $e;
        }

        if ($response->return == '0' || $response->return == '45') {
            $this->transactionSucceed();
            $this->newLog($response->return, self::TRANSACTION_SUCCEED_TEXT);
            return true;
        }

        $this->transactionFailed();
        $this->newLog($response->return, MellatException::$errors[$response->return]);
        throw new MellatException($response->return);
    }

    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * @param string|null $mobile
     * @return Mellat
     */
    public function setMobile(?string $mobile): Mellat
    {
        $this->mobile = $mobile;
        return $this;
    }
}
