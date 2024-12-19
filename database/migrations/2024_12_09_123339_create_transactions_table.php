<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('cascade');
            $table->date('buy_date')->nullable();
            $table->decimal('buy_price', 15, 2)->nullable();
            $table->integer('buy_lot')->nullable();
            $table->decimal('average', 15, 2)->nullable();
            $table->decimal('total_invested', 20, 2)->default(0);
            $table->date('sell_date')->nullable();
            $table->decimal('sell_price', 15, 2)->nullable();
            $table->integer('sell_lot')->nullable();
            $table->decimal('total_sell', 20, 2)->default(0);
            $table->decimal('total_profit', 20, 2)->nullable();
            $table->decimal('profit_percentage', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
