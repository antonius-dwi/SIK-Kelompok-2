<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'username', 'email', 'password', 'role', 'department_id', 'is_active', 'notifications_read_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'is_active' => 'boolean',
            'notifications_read_at' => 'datetime',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Semua unit/departemen yang ditugaskan (khususnya Approver bisa > 1 unit).
     * Kolom `department_id` tetap dipakai sebagai unit utama untuk kompatibilitas.
     */
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class)->withTimestamps();
    }

    /** ID unit yang ditugaskan; jatuh ke kolom lama bila pivot belum berisi. */
    public function assignedDepartmentIds(): array
    {
        $ids = $this->departments()->pluck('departments.id')->all();

        if ($ids === [] && $this->department_id) {
            $ids = [(int) $this->department_id];
        }

        return array_map('intval', $ids);
    }

    /** Simpan penugasan unit (unik, urut sesuai pilihan) + selaraskan unit utama. */
    public function syncDepartments(array $ids): void
    {
        $clean = array_values(array_unique(array_filter(array_map('intval', $ids))));
        $clean = array_values(array_intersect($clean, Department::whereIn('id', $clean)->pluck('id')->all()));

        $this->departments()->sync($clean);
        $this->forceFill(['department_id' => $clean[0] ?? null])->save();
    }

    public function hasRole(Role ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function initials(): string
    {
        return collect(preg_split('/\s+/', trim($this->name)))
            ->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
    }

    public function scopeActiveApprovers($query)
    {
        return $query->where('role', Role::Approver->value)->where('is_active', true);
    }
}
