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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user');
            $table->unsignedBigInteger('store');
            $table->integer('mpesa')->default(0)->nullable();
            $table->integer('cash')->default(0)->nullable();
            $table->string('receipt')->unique();
            $table->integer('broker_total')->default(0);
            $table->integer('sale_total');
            $table->unsignedBigInteger('bank')->nullable();
            $table->string('customer_name')->nullable();
            $table->integer('customer_phone')->nullable();
            $table->string('ref_number',)->nullable();
            $table->foreign('user')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('store')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('bank')->references('id')->on('banks')->onDelete('cascade');
            $table->timestamps();

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
