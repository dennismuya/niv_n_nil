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
        //
        Schema::table('customer_supplies', function (Blueprint $table) {
            $table->boolean('debt_balanced')->default(false);
            $table->unsignedBigInteger('debt_balanced_by')->nullable();
            $table->date('debt_balance_date')->nullable();
            $table->foreign('debt_balanced_by')->references('id')->on('users')->onDelete('Cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
