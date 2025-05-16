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
        Schema::create('invoice_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice');
            $table->string('delivery_number');
            $table->string('delivered_by')->nullable();
            $table->integer('deliverer_number')->nullable();
            $table->timestamps();
            $table->foreign('invoice')->references('id')->on('invoices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_deliveries');
    }
};
