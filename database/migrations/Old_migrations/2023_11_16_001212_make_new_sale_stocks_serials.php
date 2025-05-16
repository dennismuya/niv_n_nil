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
        Schema::table('new_sale_stocks', function (Blueprint $table) {
            $table->string('serial_number')->nullable();
            $table->json('serial_array')->nullable();


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
