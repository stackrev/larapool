<?php

namespace MstGhi\Larapool;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use MstGhi\Larapool\Traits\ModelTrait;

/**
 * Class LarapoolTransaction
 *
 * @package MstGhi\Larapool
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $port_id
 * @property integer $transaction_able_id
 * @property string $transaction_able_type
 * @property integer $price
 * @property string $ref_id
 * @property string $res_id
 * @property string $tracking_code
 * @property string $card_number
 * @property string $platform
 * @property boolean $status
 * @property integer $payment_date
 * @property integer $last_change_date
 *
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property LarapoolLog[] $logs
 * @property mixed $transactionable
 */
class LarapoolTransaction extends Model
{
    use ModelTrait;

    protected $table = 'larapool_transactions';

    protected $fillable = [
        'user_id',
        'port_id',
        'transaction_able_id',
        'transaction_able_type',
        'price',
        'ref_id',
        'res_id',
        'tracking_code',
        'card_number',
        'platform',
        'status',
        'payment_date',
        'last_change_date',
    ];

    const TRANSACTION_INIT = 0;
    const TRANSACTION_SUCCEED = 1;
    const TRANSACTION_FAILED = 2;
    const TRANSACTION_PENDING = 3;

    const TRANSACTION_PLATFORM_WEB = 'web';
    const TRANSACTION_PLATFORM_MOBILE = 'mobile';

    public function transactionable()
    {
        return $this->morphTo(
            'transactionable',
            'transaction_able_type',
            'transaction_able_id'
        );
    }

    public function logs()
    {
        return $this->hasMany(LarapoolLog::class, 'transaction_id');
    }

    public static function generateResId()
    {
        $resId = \Illuminate\Support\Str::random(48);
        $transaction = static::where('res_id', $resId)->first();

        while ($transaction) {
            $resId = \Illuminate\Support\Str::random(48);
            $transaction = static::where('res_id', $resId)->first();
        }

        return $resId;
    }
}
