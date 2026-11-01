<?php

namespace App\Support;

use Illuminate\Http\Request;
use RuntimeException;

/**
 * Dilempar saat aksi workflow tidak sah (status salah, bukan approver tahap berjalan, dll).
 * Otomatis dirender sebagai redirect kembali dengan pesan error / JSON 422.
 */
class WorkflowException extends RuntimeException
{
    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 422);
        }

        return back()->withInput()->withErrors(['workflow' => $this->getMessage()]);
    }
}
