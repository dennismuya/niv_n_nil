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
        Schema::table('sales', function (Blueprint $table) {
            $table->string('deliverly_number')->nullable();
            $table->boolean('partial_payment')->default(false);
            $table->unsignedBigInteger('original_sale')->nullable();
            $table->foreign('original_sale')->references('id')->on('sales');

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
