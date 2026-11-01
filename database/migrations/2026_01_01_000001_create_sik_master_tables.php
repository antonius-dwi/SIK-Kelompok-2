<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // wajib untuk FOREIGN KEY (hindari errno 150 bila default engine MyISAM)
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('areas', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // wajib untuk FOREIGN KEY (hindari errno 150 bila default engine MyISAM)
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('work_types', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // wajib untuk FOREIGN KEY (hindari errno 150 bila default engine MyISAM)
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_types');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('departments');
    }
};
