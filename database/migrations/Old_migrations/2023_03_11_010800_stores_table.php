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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
           $table->string('store_name');
           $table->string('location')->nullable();
           $table->string('building')->nullable();
           $table->string('primary_phone')->nullable();
           $table->string('secondary_phone')->nullable();
           $table->string('website')->nullable();
           $table->string('tagline')->nullable();
           $table->string('products')->nullable();
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
