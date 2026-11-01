<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambah kolom uraian/tujuan pekerjaan pada pengajuan.
 * Sebelumnya surat SIK memakai `job_title` (jabatan) sebagai uraian pekerjaan,
 * sehingga jabatan seperti "Supervisor" tampil sebagai tujuan pekerjaan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permit_applications', function (Blueprint $table) {
            $table->string('work_description', 500)->nullable()->after('job_title');
        });
    }

    public function down(): void
    {
        Schema::table('permit_applications', function (Blueprint $table) {
            $table->dropColumn('work_description');
        });
    }
};
