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
        Schema::create('slips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('slipped_by');
            $table->date('slip_date');
            $table->integer('supply_amount_before')->default(0);
            $table->integer('debt_owed_before')->default(0);
            $table->integer('slip_total');
            $table->unsignedBigInteger('store');
            $table->unsignedBigInteger('customer');
            $table->boolean('slip_verified')->nullable();
            $table->string('comment')->nullable();
            $table->foreign('slipped_by')->references('id')->on('users')->onDelete('Cascade');
            $table->foreign('store')->references('id')->on('stores')->onDelete('Cascade');
            $table->foreign('customer')->references('id')->on('customers')->onDelete('Cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slips');
    }
};
