<?php

namespace MstGhi\Larapool;

use Illuminate\Support\Facades\DB;
use MstGhi\Larapool\Contracts\PortAbstract;
use MstGhi\Larapool\Exceptions\RetryException;
use MstGhi\Larapool\Exceptions\PortNotFoundException;
use MstGhi\Larapool\Exceptions\InvalidRequestException;
use MstGhi\Larapool\Exceptions\NotFoundTransactionException;
use MstGhi\Larapool\IDPay\IDPay;

/**
 * Class Larapool
 *
 * @package MstGhi\Larapool
 * @author Mostafa Gholami
 *
 */
class Larapool
{
    const P_MELLAT = 1;
    const P_SADAD = 2;
    const P_ZARINPAL = 3;
    const P_PAYLINE = 4;
    const P_JAHANPAY = 5;
    const P_PARSIAN = 6;
    const P_PASARGAD = 7;
    const P_SADERAT = 8;
    const P_IRANKISH = 9;
    const P_SIMULATOR = 10;
    const P_SAMAN = 11;
    const P_PAY = 12;
    const P_JIBIT = 13;
    const P_AP = 14;
    const P_BITPAY = 15;
    const P_IDPAY = 16;
    const P_PAYPING = 17;

    const PLATFORM_WEB = 'web';
    const PLATFORM_MOBILE = 'mobile';

    public $config;
    protected $request;
    protected $portClass;

    /**
     * @param int $port
     * @throws PortNotFoundException
     */
    public function __construct(int $port)
    {
        $this->config = app('config');
        $this->request = app('request');

        if (!empty($this->config->get('larapool.timezone'))) {
            date_default_timezone_set($this->config->get('larapool.timezone'));
        }

        if (!is_null($port)) {
            $this->buildPort($port);
        }
    }

    /**
     * Get supported ports
     *
     * @return array
     */
    public function getSupportedPorts()
    {
        return array(
            self::P_IDPAY,
            self::P_MELLAT,
        );
    }

    /**
     * Call methods of current driver
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->portClass, $name], $arguments);
    }

    /**
     * Verify transaction, use verifyLock for security reason
     *
     * @return mixed $this->portClass
     *
     * @throws InvalidRequestException
     * @throws NotFoundTransactionException
     * @throws PortNotFoundException
     * @throws RetryException
     */
    public function verify()
    {
        if (!isset($this->request->transaction_id)) {
            throw new InvalidRequestException;
        }

        $transactionId = intval($this->request->transaction_id);
        $transaction = LarapoolTransaction::where('id', $transactionId)->first();

        if (!$transaction) {
            throw new NotFoundTransactionException;
        }

        if (in_array($transaction->status, [PortAbstract::TRANSACTION_SUCCEED, PortAbstract::TRANSACTION_FAILED])) {
            throw new RetryException;
        }

        $this->buildPort($transaction->port_id);

        return $this->portClass->verify($transaction);
    }

    /**
     * Verify transaction, preventing duplicate request at the same time
     *
     * @return mixed $this->portClass
     *
     * @throws InvalidRequestException
     * @throws NotFoundTransactionException
     * @throws PortNotFoundException
     * @throws RetryException
     */
    public function verifyLock()
    {
        if (!isset($this->request->t_id)) {
            throw new InvalidRequestException;
        }

        $transactionId = intval($this->request->t_id);

        DB::beginTransaction();
        try {
            $transaction = LarapoolTransaction::where('id', $transactionId)->first();

            if (!$transaction) {
                throw new NotFoundTransactionException;
            }

            if ($transaction->status != PortAbstract::TRANSACTION_INIT) {
                throw new RetryException;
            }

            $this->buildPort($transaction->port_id);
            $this->portClass->transactionPending($transaction->id);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $this->portClass->verify($transaction);
    }

    /**
     * Create new object from port class
     *
     * @param int $port
     * @throws PortNotFoundException
     */
    private function buildPort(int $port)
    {
        switch ($port) {
            case self::P_IDPAY:
                $this->portClass = new IDPay($this->config, $this->request, self::P_IDPAY);
                break;

            case self::P_MELLAT:
                $this->portClass = new IDPay($this->config, $this->request, self::P_MELLAT);
                break;

            default:
                throw new PortNotFoundException;
        }
    }
}
