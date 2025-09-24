<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_shift', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('role')->nullable(); // misal: Operator, Helper
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_shift');
    }
};
