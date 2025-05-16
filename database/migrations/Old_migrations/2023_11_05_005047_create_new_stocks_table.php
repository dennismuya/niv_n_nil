<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('new_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('stock_name')->unique();
            $table->string('stock_properties')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->integer('nakuru_quantity')->default(0);
            $table->integer('old_nation_quantity')->default(0);
            $table->integer('chini_ya_mnazi_quantity')->default(0);
            $table->integer('selling_price')->default(0);
            $table->integer('available stock')->default(0);
            $table->string('SKU')->nullable();
            $table->foreign('product_id')->references('id')->on('products');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_stocks');
    }
};
