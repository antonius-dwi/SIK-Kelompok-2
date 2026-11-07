<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = ['code', 'name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Sisi bandara untuk instruksi surat: 'airside', 'landside', atau 'unknown'. Ditentukan dari kode area (config sik.area_sides), bukan labelnya, supaya tetap benar walau nama area diedit. */
    public function side(): string
    {
        $code = strtoupper(trim((string) $this->code));

        if (in_array($code, (array) config('sik.area_sides.airside', []), true)) {
            return 'airside';
        }

        if (in_array($code, (array) config('sik.area_sides.landside', []), true)) {
            return 'landside';
        }

        return 'unknown';
    }
}
