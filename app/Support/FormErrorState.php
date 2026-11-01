<?php

namespace App\Support;

use Illuminate\Support\ViewErrorBag;

/**
 * Menentukan form modal mana yang terakhir dikirim (untuk halaman System Administrator yang
 * punya beberapa modal di satu halaman: master data, pengguna, templat alur).
 *
 * Dipakai di Blade: [$errForm, $invalidOn] = FormErrorState::detect($errors, old('_form'));
 */
final class FormErrorState
{
    /**
     * @return array{0: ?string, 1: callable(string, string): bool}
     */
    public static function detect(ViewErrorBag $errors, ?string $submitted): array
    {
        $form = $submitted;

        // Cadangan bila penanda _form tidak terkirim: simpulkan dari kunci error yang khas.
        if ($form === null && $errors->any()) {
            $form = match (true) {
                $errors->has('code') => 'master',
                $errors->hasAny(['username', 'password', 'role', 'department_id']) => 'user',
                $errors->hasAny(['area', 'workType', 'steps']) => 'workflow',
                default => null,
            };
        }

        $invalidOn = fn (string $target, string $key): bool => $form === $target && $errors->has($key);

        return [$form, $invalidOn];
    }
}
