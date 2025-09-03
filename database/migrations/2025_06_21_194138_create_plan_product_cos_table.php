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
        Schema::create('plan_product_cos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->onDelete('cascade');
            // $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');
            // $table->foreignId('co_id')->nullable()->constrained('cos')->onDelete('cascade');
            $table->foreignId('co_product_id')->nullable()->constrained('co_products')->onDelete('cascade');
            $table->dateTime('shipment_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_product_cos');
    }
};
