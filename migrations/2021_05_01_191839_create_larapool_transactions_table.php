<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateLarapoolTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        Schema::create('larapool_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->index()->unsigned();
            $table->bigInteger('transaction_able_id')->unsigned()->nullable();
            $table->string('transaction_able_type')->nullable();
            $table->unsignedTinyInteger('port_id');
            $table->decimal('price', 15, 2);
            $table->string('ref_id')->unique()->nullable();
            $table->string('res_id')->unique();
            $table->string('tracking_code', 50)->nullable();
            $table->string('card_number', 50)->nullable();
            $table->string('platform', 50)->nullable();
            $table->tinyInteger('status')->default(0);
            $table->integer('payment_date')->nullable();
            $table->integer('last_change_date');
            $table->timestamps();
        });

        Schema::create('larapool_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('transaction_id')->index()->unsigned()->nullable();
            $table->string('result_code', 255)->nullable();
            $table->string('result_message', 255)->nullable();
            $table->integer('log_date');
            $table->timestamps();
        });

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('larapool_logs');
        Schema::dropIfExists('larapool_transactions');
    }
}
