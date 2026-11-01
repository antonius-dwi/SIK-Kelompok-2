<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_templates', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // wajib untuk FOREIGN KEY (hindari errno 150 bila default engine MyISAM)
            $table->id();
            $table->foreignId('area_id')->constrained()->restrictOnDelete();
            $table->foreignId('work_type_id')->constrained()->restrictOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Satu templat per kombinasi Area + Jenis pekerjaan.
            $table->unique(['area_id', 'work_type_id']);
        });

        Schema::create('workflow_template_steps', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // wajib untuk FOREIGN KEY (hindari errno 150 bila default engine MyISAM)
            $table->id();
            $table->foreignId('workflow_template_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('stage');           // 1..8
            $table->string('approver_title');               // mis. "Koordinator Operasi"
            $table->foreignId('approver_user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['workflow_template_id', 'stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_template_steps');
        Schema::dropIfExists('workflow_templates');
    }
};
