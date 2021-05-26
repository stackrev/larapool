<?php

namespace MstGhi\Larapool\Contracts;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use MstGhi\Larapool\LarapoolLog;
use MstGhi\Larapool\LarapoolTransaction;

abstract class PortAbstract
{
    /**
     * Status code for status field in poolport_transactions table
     */
    const TRANSACTION_INIT = 0;

    /**
     * Status code for status field in poolport_transactions table
     */
    const TRANSACTION_SUCCEED = 1;

    /**
     * Transaction succeed text for put in log
     */
    const TRANSACTION_SUCCEED_TEXT = 'پرداخت با موفقیت انجام شد.';

    /**
     * Status code for status field in poolport_transactions table
     */
    const TRANSACTION_FAILED = 2;

    /**
     * Status code for status field in poolport_transactions table
     */
    const TRANSACTION_PENDING = 3;

    /**
     * Transaction id
     *
     * @var null|int
     */
    protected $transactionId = null;

    /**
     * Transaction row in database
     */
    protected $transaction = null;

    /**
     * Customer card number
     *
     * @var string
     */
    protected $cardNumber = '';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var mixed
     */
    protected $request;

    /**
     * Port id
     *
     * @var int
     */
    protected $portId;

    /**
     * Reference id
     *
     * @var string
     */
    protected $refId;

    /**
     * Amount in Rial
     *
     * @var int
     */
    protected $amount;

    /**
     * Tracking code payment
     *
     * @var string
     */
    protected $trackingCode;

    /**
     * Initialize of class
     *
     * @param Config $config
     * @param $request
     * @param int $portId
     */
    public function __construct(Config $config, $request, int $portId)
    {
        $this->config = $config;
        $this->request = $request;
        $this->portId = $portId;
    }

    /**
     * Get port id, $this->portId
     *
     * @return int
     */
    public function portId()
    {
        return $this->portId;
    }

    /**
     * Return card number
     *
     * @return string
     */
    public function cardNumber()
    {
        return $this->cardNumber;
    }

    /**
     * Return tracking code
     */
    public function trackingCode()
    {
        return $this->trackingCode;
    }

    /**
     * Get transaction id
     *
     * @return int|null
     */
    public function transactionId()
    {
        return $this->transactionId;
    }

    /**
     * Return reference id
     */
    public function refId()
    {
        return $this->refId;
    }

    /**
     * Return result of payment
     * If result is done, return true, otherwise throws an related exception
     *
     * This method must be implements in child class
     *
     * @param object $transaction row of transaction in database
     *
     * @return void
     */
    public function verify(object $transaction)
    {
        $this->transaction = $transaction;
        $this->transactionId = intval($transaction->id);
        $this->amount = intval($transaction->price);
        $this->refId = $transaction->ref_id;
    }

    /**
     * Insert new transaction to poolport_transactions table
     *
     * @return int last inserted id
     */
    protected function newTransaction()
    {
        $date = new \DateTime;
        $date = $date->getTimestamp();

        $transaction = LarapoolTransaction::create([
            'port_id' => $this->portId,
            'res_id' => LarapoolTransaction::generateResId(),
            'price' => $this->amount,
            'status' => self::TRANSACTION_INIT,
            'last_change_date' => $date,
        ]);

        $this->transactionId = $transaction->id;

        return $this->transactionId;
    }

    /**
     * Commit transaction
     * Set status field to success status
     *
     * @return bool
     */
    protected function transactionSucceed()
    {
        $time = new \DateTime();

        return LarapoolTransaction::where('id', $this->transactionId)->update([
            'status' => self::TRANSACTION_SUCCEED,
            'card_number' => $this->cardNumber,
            'last_change_date' => $time->getTimestamp(),
            'payment_date' => $time->getTimestamp(),
            'tracking_code' => $this->trackingCode,
        ]);
    }

    /**
     * Failed transaction
     * Set status field to error status
     *
     * @return bool
     */
    protected function transactionFailed()
    {
        $time = new \DateTime();

        return LarapoolTransaction::where('id', $this->transactionId)->update([
            'status' => self::TRANSACTION_FAILED,
            'last_change_date' => $time->getTimestamp(),
        ]);
    }

    /**
     * Pending transaction
     * Set status pending to error status
     *
     * @param $transactionId int|null If this param not send, use class transactionId parameter
     *
     * @return bool
     */
    public function transactionPending(int $transactionId = null)
    {
        $transactionId = $transactionId == null ? $this->transactionId : $transactionId;

        $time = new \DateTime();

        return LarapoolTransaction::where('id', $transactionId)->update([
            'status' => self::TRANSACTION_PENDING,
            'last_change_date' => $time->getTimestamp(),
        ]);
    }

    /**
     * Update transaction refId
     *
     * @return void
     */
    protected function transactionSetRefId()
    {
        LarapoolTransaction::where('id', $this->transactionId)->update([
            'ref_id' => $this->refId,
        ]);
    }

    /**
     * New log
     *
     * @param string|int $statusCode
     * @param string $statusMessage
     */
    protected function newLog($statusCode, string $statusMessage)
    {
        $date = new \DateTime;

        LarapoolLog::create([
            'transaction_id' => $this->transactionId,
            'result_code' => $statusCode,
            'result_message' => $statusMessage,
            'log_date' => $date->getTimestamp(),
        ]);
    }

    /**
     * Reset a config per request in poolport.php configuration file
     *
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     */
    public function setConfig(string $key, $value)
    {
        $this->config->set($key, $value);

        return $this;
    }

    /**
     * Set all ports call back url
     *
     * @param string $url
     *
     * @return $this
     */
    public function setGlobalCallbackUrl(string $url)
    {
        $this->config->set('zarinpal.callback-url', $url);
        $this->config->set('mellat.callback-url', $url);
        $this->config->set('payline.callback-url', $url);
        $this->config->set('sadad.callback-url', $url);
        $this->config->set('jahanpay.callback-url', $url);
        $this->config->set('parsian.callback-url', $url);
        $this->config->set('pasargad.callback-url', $url);
        $this->config->set('saderat.callback-url', $url);
        $this->config->set('irankish.callback-url', $url);
        $this->config->set('simulator.callback-url', $url);
        $this->config->set('saman.callback-url', $url);
        $this->config->set('pay.callback-url', $url);
        $this->config->set('jibit.callback-url', $url);
        $this->config->set('ap.callback-url', $url);
        $this->config->set('bitpay.callback-url', $url);
        $this->config->set('idpay.callback-url', $url);
        $this->config->set('payping.callback-url', $url);

        return $this;
    }

    /**
     * Set user-mobile config for all ports that support this feature
     *
     * @param string $mobile In format 09xxxxxxxxx
     *
     * @return void
     */
    public function setGlobalUserMobile(string $mobile)
    {
        // Convert 09xxxxxxxxx format to 989xxxxxxxxx format for specific ports
        $withPrefixFormat = '989' . substr($mobile, 2);

        $this->config->set('mellat.user-mobile', $withPrefixFormat);
        $this->config->set('sadad.user-mobile', $mobile);
        $this->config->set('jibit.user-mobile', $mobile);
        $this->config->set('ap.user-mobile', $mobile);
        $this->config->set('idpay.user-mobile', $mobile);
        $this->config->set('payping.user-mobile', $mobile);
    }

    /**
     * Add query string to a url
     *
     * @param string $url
     * @param array $query
     * @return string
     */
    protected function buildQuery(string $url, array $query)
    {
        $query = http_build_query($query);

        $questionMark = strpos($url, '?');
        if (!$questionMark) {
            return "$url?$query";
        } else {
            return substr($url, 0, $questionMark + 1) . $query . "&" . substr($url, $questionMark + 1);
        }
    }

    /**
     * @param int|null $transactionId
     * @return PortAbstract
     */
    public function setTransactionId(?int $transactionId): PortAbstract
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * @param LarapoolTransaction $transaction
     * @return PortAbstract
     */
    public function setTransaction(LarapoolTransaction $transaction)
    {
        $this->transaction = $transaction;
        $this->amount = $transaction->price;
        return $this;
    }

    /**
     * @param mixed $request
     * @return PortAbstract
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

}
