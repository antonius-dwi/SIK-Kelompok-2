<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom personel id_number (nomor kartu identitas/KTP) diganti menjadi phone_number (nomor HP). RENAME COLUMN hanya mengubah nama kolom: seluruh nilai lama tetap utuh, tidak ada baris yang dihapus atau diubah isinya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_personnel', function (Blueprint $table) {
            $table->renameColumn('id_number', 'phone_number');
        });
    }

    public function down(): void
    {
        Schema::table('application_personnel', function (Blueprint $table) {
            $table->renameColumn('phone_number', 'id_number');
        });
    }
};
