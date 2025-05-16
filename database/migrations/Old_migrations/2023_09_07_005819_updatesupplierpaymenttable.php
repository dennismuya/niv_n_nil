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
        Schema::table('supply_payments',function (Blueprint $table){

            $table->integer('mpesa')->default(0);
            $table->integer('cash')->default(0);
            $table->integer('expenses')->default(0);
            $table->date('date');
            $table->unsignedBigInteger('user');
            $table->unsignedBigInteger('store');
            $table->foreign('user')->references('id')->on('users')->onDelete('Cascade');
            $table->foreign('store')->references('id')->on('stores')->onDelete('Cascade');
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
