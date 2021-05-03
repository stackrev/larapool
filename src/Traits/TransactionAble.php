<?php

namespace MstGhi\Larapool\Traits;

use MstGhi\Larapool\LarapoolTransaction;

/**
 * Trait TransactionAble
 * @package App\Concerns
 *
 * @property LarapoolTransaction $last_transactions
 * @property LarapoolTransaction[] $transactions
 */
trait TransactionAble
{
    public function getLastTransactionAttribute()
    {
        return $this->transactions()
            ->with('logs')
            ->orderByDesc('id')
            ->first();
    }

    public function transactions()
    {
        return $this->morphMany(
            LarapoolTransaction::class,
            'transactionable',
            'transaction_able_type',
            'transaction_able_id'
        );
    }
}
