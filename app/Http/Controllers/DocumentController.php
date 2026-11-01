<?php

namespace App\Http\Controllers;

use App\Models\ApplicationDocument;
use App\Models\ApplicationItem;
use App\Services\ApplicationDataService;
use Illuminate\Support\Facades\Storage;

/** Berkas privat disajikan lewat controller dengan otorisasi per pengajuan (bukan URL publik). */
class DocumentController extends Controller
{
    public function document(ApplicationDocument $document)
    {
        $this->authorize('view', $document->application);

        return Storage::disk(ApplicationDataService::DISK)->response(
            $document->path, $document->original_name, ['Content-Type' => 'application/pdf'], 'inline'
        );
    }

    public function itemPhoto(ApplicationItem $item)
    {
        $this->authorize('view', $item->application);
        abort_unless($item->photo_path && Storage::disk(ApplicationDataService::DISK)->exists($item->photo_path), 404);

        return Storage::disk(ApplicationDataService::DISK)->response($item->photo_path);
    }
}
