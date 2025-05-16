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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number');
            $table->unsignedBigInteger('user');
            $table->dateTime('due_date')->nullable();
            $table->unsignedBigInteger('customer');
            $table->integer('invoice_total')->nullable();
            $table->string('items_picked_by')->nullable();
            $table->unsignedBigInteger('store');
            $table->foreign('store')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('user')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('customer')->references('id')->on('customers');
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
