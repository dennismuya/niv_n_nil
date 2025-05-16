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
        Schema::table('debt_history', function (Blueprint $table) {
            //
            Schema::table('debt_histories', function (Blueprint $table) {
                $table->unsignedBigInteger('store');
                $table->foreign('store')->references('id')->on('stores')->onDelete('Cascade');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('debt_history', function (Blueprint $table) {
            //
        });
    }
};
