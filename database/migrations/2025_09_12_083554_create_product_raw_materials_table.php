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
        Schema::create('product_raw_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('raw_mat_code')->nullable();
            $table->string('material_type')->nullable();
            $table->string('material_category')->nullable();
            $table->string('rm_name')->nullable();
            $table->string('rm_type')->nullable();
            $table->string('rm_subtype')->nullable();
            $table->decimal('cutsize_length', 10, 2)->nullable();
            $table->decimal('cutsize_width', 10, 2)->nullable();
            $table->decimal('raw_mat_thickness', 10, 2)->nullable();
            $table->string('sheet_substance')->nullable();
            $table->string('flute_type')->nullable();
            $table->decimal('qty_image', 10, 2)->nullable();
            $table->decimal('qty_plano', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_raw_materials');
    }
};
