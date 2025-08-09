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
        Schema::create('cos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('code');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('co_user')->nullable();
            $table->dateTime('shipping_date')->nullable();
            $table->string('process_details')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->string('status')->default('pending');
            $table->string('remarks')->nullable();
            $table->boolean('draft')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cos');
    }
};
