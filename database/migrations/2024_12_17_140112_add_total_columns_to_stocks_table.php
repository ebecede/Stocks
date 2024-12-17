<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->decimal('total_invested', 20, 2)->default(0);
            $table->integer('total_lot')->default(0);
            $table->decimal('total_average', 15, 2)->default(0);
            $table->decimal('total_profit', 20, 2)->default(0);
            $table->decimal('average_profit_percentage', 5, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn(['total_invested', 'total_lot', 'total_average', 'total_profit', 'average_profit_percentage']);
        });
    }
};
