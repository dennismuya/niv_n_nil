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
        Schema::create('daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->dateTime('opening_time')->nullable();
            $table->unsignedBigInteger('store');
            $table->integer('opening_balance')->default(0);
            $table->integer('sales_cash')->default(0);
            $table->integer('sales_mpesa')->default(0);
            $table->integer('sales_total')->default(0);
            $table->integer('sales_debt')->default(0);
            $table->integer('expenses')->default(0);
            $table->integer('supply_payments')->default(0);
            $table->integer('debt_recovered_cash')->default(0);
            $table->integer('debt_recovered_mpesa')->default(0);
            $table->integer('debt_recovered_total')->default(0);
            $table->integer('closing_balance')->default(0);
            $table->dateTime('closing_time')->nullable();
            $table->foreign('store')->references('id')->on('stores')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_summaries');
    }
};
