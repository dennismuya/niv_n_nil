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

        Schema::table('invoice_payments',function (Blueprint $table){

            $table->unsignedBigInteger('bank')->nullable();
            $table->integer('cheque_amount')->default(0);
            $table->string('cheque_number')->nullable();
            $table->date('cheque_date')->nullable();
            $table->foreign('bank')->references('id')->on('banks')->onDelete('cascade');
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
