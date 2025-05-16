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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user');
            $table->unsignedBigInteger('created_by');
            $table->integer('amount');
            $table->unsignedBigInteger('store');
            $table->string('reason')->nullable();
            $table->foreign('user')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('store')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expences');
    }
};

