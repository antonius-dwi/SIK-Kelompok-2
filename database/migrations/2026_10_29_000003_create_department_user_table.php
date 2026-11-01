<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Penugasan multi-unit untuk pengguna (khususnya Approver).
 *
 * Non-destruktif: kolom lama `users.department_id` tetap dipertahankan sebagai
 * unit utama (dipakai tampilan lama dan kompatibilitas data), sedangkan daftar
 * lengkap unit disimpan di pivot `department_user`. Data lama disalin ke pivot
 * sehingga tidak ada penugasan yang hilang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'department_id']);
        });

        // Salin penugasan lama (satu unit per pengguna) ke pivot.
        $rows = DB::table('users')->whereNotNull('department_id')->get(['id', 'department_id']);
        foreach ($rows as $row) {
            DB::table('department_user')->insertOrIgnore([
                'user_id' => $row->id,
                'department_id' => $row->department_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('department_user');
    }
};
