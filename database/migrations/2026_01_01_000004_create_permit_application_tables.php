<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permit_applications', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // wajib untuk FOREIGN KEY (hindari errno 150 bila default engine MyISAM)
            $table->id();
            $table->string('number', 20)->unique();          // SIK-2026-00001 (nomor pengajuan)
            $table->foreignId('user_id')->constrained()->restrictOnDelete(); // pemilik/pemohon

            $table->string('applicant_name')->nullable();      // nama perusahaan / pemohon (boleh kosong saat draf)
            $table->string('contact_name')->nullable();
            $table->string('job_title')->nullable();
            $table->foreignId('area_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('work_type_id')->nullable()->constrained()->restrictOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->string('status', 20)->default('DRAFT')->index();
            $table->unsignedTinyInteger('current_stage')->default(0);
            $table->unsignedTinyInteger('total_stages')->default(0);
            $table->text('correction_note')->nullable();      // catatan terakhir (return / koreksi / revisi)

            // Nomor SIK resmi: SIK/AYANI/YYYY/NNN. UNIQUE di DB mencegah dua penerbitan bersamaan.
            $table->string('permit_number', 18)->nullable()->unique();
            $table->date('issue_date')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        // Snapshot alur persetujuan yang berlaku untuk pengajuan (diisi saat verifikasi diteruskan).
        Schema::create('application_approval_steps', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // wajib untuk FOREIGN KEY (hindari errno 150 bila default engine MyISAM)
            $table->id();
            $table->foreignId('permit_application_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('stage');
            $table->string('approver_title');
            $table->foreignId('approver_user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['permit_application_id', 'stage']);
            $table->index('approver_user_id');
        });

        Schema::create('application_personnel', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // wajib untuk FOREIGN KEY (hindari errno 150 bila default engine MyISAM)
            $table->id();
            $table->foreignId('permit_application_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('id_number');                      // nomor KTP / paspor
            $table->string('vehicle_plate')->nullable();
            $table->timestamps();
        });

        Schema::create('application_items', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // wajib untuk FOREIGN KEY (hindari errno 150 bila default engine MyISAM)
            $table->id();
            $table->foreignId('permit_application_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('asset_number')->nullable();
            $table->unsignedInteger('quantity');
            $table->string('condition', 20)->default('good');      // good | fair | needs_repair
            $table->string('movement', 10)->default('IN');         // IN | OUT | IN & OUT
            $table->string('frequency', 10)->default('One-time');  // One-time | Daily | Weekly | Other
            $table->boolean('is_consumable')->default(false);      // true => movement dipaksa IN
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });

        Schema::create('application_documents', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // wajib untuk FOREIGN KEY (hindari errno 150 bila default engine MyISAM)
            $table->id();
            $table->foreignId('permit_application_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 12);                       // request | attachment
            $table->string('original_name');
            $table->string('path');
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });

        Schema::create('application_histories', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // wajib untuk FOREIGN KEY (hindari errno 150 bila default engine MyISAM)
            $table->id();
            $table->foreignId('permit_application_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20);
            $table->unsignedTinyInteger('stage')->default(0);
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name');
            $table->string('actor_role');
            $table->string('decision')->nullable();           // APPROVE | RETURN | REJECT | teks aksi
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['permit_application_id', 'created_at']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // wajib untuk FOREIGN KEY (hindari errno 150 bila default engine MyISAM)
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name');
            $table->string('role')->nullable();
            $table->string('action');
            $table->string('target')->nullable()->index();    // nomor pengajuan / nama master
            $table->text('detail')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('application_histories');
        Schema::dropIfExists('application_documents');
        Schema::dropIfExists('application_items');
        Schema::dropIfExists('application_personnel');
        Schema::dropIfExists('application_approval_steps');
        Schema::dropIfExists('permit_applications');
    }
};
