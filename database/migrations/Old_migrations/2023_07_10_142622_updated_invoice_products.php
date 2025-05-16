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
        Schema::table('invoice_products',function (Blueprint $table){
            $table->boolean('returned')->default(0)->nullable();
            $table->unsignedBigInteger('returned_by')->nullable();
            $table->dateTime('returned_at')->nullable();
            $table->foreign('returned_by')->references('id')->on('users');
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
