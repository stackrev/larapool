<?php

namespace MstGhi\Larapool;

use Illuminate\Database\Eloquent\Model;
use MstGhi\Larapool\Traits\ModelTrait;

/**
 * Class LarapoolLog
 *
 * @package MstGhi\Larapool
 *
 * @property integer $id
 * @property integer $transaction_id
 * @property string $result_code
 * @property string $result_message
 * @property integer $log_date
 *
 * @property LarapoolTransaction $transaction
 *
 */
class LarapoolLog extends Model
{
    use ModelTrait;

    protected $table = 'larapool_logs';

    protected $fillable = [
        'transaction_id',
        'result_code',
        'result_message',
        'log_date',
    ];

    const CODE_SUCCESS = 'success';
    const CODE_FAILED = 'failed';
    const CODE_SUCCESS_GET_TOKEN = 'tokenWasReceived';
    const CODE_SUCCESS_CONNECT_TO_BANK = 'connectToBankSuccessful';
    const CODE_ERROR_CONNECT_TO_BANK = 'connectToBankEncounteredAnError';

    public function transaction()
    {
        return $this->belongsTo(LarapoolTransaction::class);
    }
}
