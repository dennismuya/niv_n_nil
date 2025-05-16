<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::table('stock_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('sale')->nullable();
            $table->unsignedBigInteger('debt')->nullable();
            $table->foreign('sale')->references('id')->on('sales')->onDelete('CASCADE');
            $table->foreign('debt')->references('id')->on('debt_histories')->onDelete('CASCADE');
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
