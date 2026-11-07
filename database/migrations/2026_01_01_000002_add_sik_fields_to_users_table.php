<?php

use App\Enums\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menambah kolom SIK ke tabel users, aman dijalankan pada project yang sudah punya tabel users berisi data dan kolom role lama. Kolom yang sudah ada tidak dibuat ulang (role diubah menjadi string), nilai role bukan role SIK dinormalkan menjadi applicant, user lama tanpa username diberi username dari email (atau user{id}); login memakai username, jadi email dibuat nullable.
 */
return new class extends Migration
{
    public function up(): void
    {
        $hadRole = Schema::hasColumn('users', 'role');

        Schema::table('users', function (Blueprint $table) use ($hadRole) {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('name');
            }
            if (! $hadRole) {
                $table->string('role', 20)->default('applicant')->index()->after('password');
            }
            if (! Schema::hasColumn('users', 'department_id')) {
                // Tanpa FOREIGN KEY di level database: tipe/engine tabel users lama bisa tidak cocok (errno 150).
                // Relasi tetap dipakai lewat Eloquent (User::department()).
                $table->unsignedBigInteger('department_id')->nullable()->index();
            }
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (! Schema::hasColumn('users', 'notifications_read_at')) {
                $table->timestamp('notifications_read_at')->nullable();
            }
        });

        Schema::table('users', function (Blueprint $table) use ($hadRole) {
            $table->string('email')->nullable()->change();
            if ($hadRole) {
                // Kolom lama bisa bertipe enum('user','admin'); jadikan string agar nilai SIK bisa disimpan.
                $table->string('role', 20)->default('applicant')->change();
            }
        });

        // Normalisasi role lama ("user", dll.) -> applicant.
        DB::table('users')
            ->whereNotIn('role', array_column(Role::cases(), 'value'))
            ->orWhereNull('role')
            ->update(['role' => Role::Applicant->value]);

        // Username untuk user lama.
        DB::table('users')->whereNull('username')->orderBy('id')->each(function ($user) {
            $base = $user->email ? strtolower(strstr($user->email, '@', true) ?: $user->email) : 'user';
            $username = DB::table('users')->where('username', $base)->exists() ? "{$base}{$user->id}" : $base;
            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(array_values(array_filter(
                ['username', 'role', 'department_id', 'is_active', 'notifications_read_at'],
                fn ($column) => Schema::hasColumn('users', $column)
            )));
        });
    }
};
