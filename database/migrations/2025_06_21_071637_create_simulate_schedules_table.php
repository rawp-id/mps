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
        Schema::create('simulate_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('process_id')->nullable()->constrained('processes')->onDelete('cascade');
            $table->foreignId('machine_id')->nullable()->constrained('machines')->onDelete('cascade');
            $table->foreignId('operation_id')->nullable()->constrained('operations')->onDelete('cascade');
            $table->foreignId('previous_schedule_id')->nullable()->constrained('schedules')->onDelete('cascade');
            $table->foreignId('process_dependency_id')->nullable()->constrained('schedules')->onDelete('cascade');
            $table->boolean('is_start_process')->default(false);
            $table->boolean('is_final_process')->default(false);
            $table->bigInteger('quantity')->default(0);
            $table->bigInteger('plan_speed')->default(0);
            $table->decimal('conversion_value')->nullable();
            $table->integer('plan_duration')->default(0);
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simulate_schedules');
    }
};
