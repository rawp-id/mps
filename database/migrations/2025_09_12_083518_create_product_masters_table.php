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
        Schema::create('product_masters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('component_type')->nullable();
            $table->boolean('is_active')->default(true);
            // karena tidak ada tabel relasi, cukup simpan kode / string
            $table->string('mastercard_ref')->nullable(); // kode referensi dari legacy, bukan relasi
            $table->string('routing_ref')->nullable();    // kode routing dari legacy, bukan relasi
            $table->integer('total_rawmat')->default(0);
            $table->date('proof_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_masters');
    }
};
