<?php

namespace App\Services;

use App\Models\ApplicationDocument;
use App\Models\ApplicationItem;
use App\Models\PermitApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Menyimpan isi form pengajuan (data umum, personel, barang, dokumen).
 * Dipakai oleh pemohon (draf/kirim) dan Admin (koreksi).
 */
class ApplicationDataService
{
    public const DISK = 'local'; // privat: storage/app/private (Laravel 11)

    /** Nomor pengajuan berikutnya: SIK-YYYY-NNNNN. Harus dipanggil di dalam transaksi. */
    public function nextApplicationNumber(): string
    {
        $year = now('Asia/Jakarta')->format('Y');
        $last = PermitApplication::where('number', 'like', "SIK-{$year}-%")
            ->lockForUpdate()->orderByDesc('number')->value('number');
        $sequence = $last ? (int) substr($last, -5) + 1 : 1;

        return sprintf('SIK-%s-%05d', $year, $sequence);
    }

    /** Mengisi field umum + baris personel/barang + dokumen dari request tervalidasi. */
    public function fill(PermitApplication $application, Request $request): PermitApplication
    {
        return DB::transaction(function () use ($application, $request) {
            $application->fill([
                'applicant_name' => $request->input('applicantName'),
                'contact_name' => $request->input('contactName'),
                'job_title' => $request->input('jobTitle'),
                'work_description' => trim((string) $request->input('workDescription')) ?: null,
                'area_id' => $request->input('area') ?: null,
                'work_type_id' => $request->input('workType') ?: null,
                'start_date' => $request->input('startDate') ?: null,
                'end_date' => $request->input('endDate') ?: null,
            ])->save();

            $this->syncPersonnel($application, $request);
            $this->syncItems($application, $request);
            $this->syncDocuments($application, $request);

            return $application->refresh();
        });
    }

    private function syncPersonnel(PermitApplication $application, Request $request): void
    {
        $names = (array) $request->input('personel_nama', []);
        $hp = (array) $request->input('personel_hp', []);
        $plates = (array) $request->input('personel_plat', []);

        $application->personnel()->delete();
        foreach ($names as $i => $name) {
            if (trim((string) $name) === '') {
                continue; // baris kosong (draf)
            }
            $application->personnel()->create([
                'name' => trim($name),
                'phone_number' => trim((string) ($hp[$i] ?? '')),
                'vehicle_plate' => trim((string) ($plates[$i] ?? '')) ?: null,
            ]);
        }
    }

    private function syncItems(PermitApplication $application, Request $request): void
    {
        $names = (array) $request->input('barang_nama', []);
        $existing = $application->items()->get()->keyBy('id');
        $photos = $request->file('barang_foto', []);
        $keptIds = [];

        foreach ($names as $i => $name) {
            if (trim((string) $name) === '') {
                continue;
            }

            // barang_consumable[] selalu satu nilai per baris ("1"/"0"); habis pakai => arah dipaksa IN.
            $consumable = ($request->input("barang_consumable.$i") ?? '0') === '1';
            $attributes = [
                'name' => trim($name),
                'brand' => $request->input("barang_brand.$i") ?: null,
                'model' => $request->input("barang_model.$i") ?: null,
                'serial_number' => $request->input("barang_sn.$i") ?: null,
                'asset_number' => $request->input("barang_asset_no.$i") ?: null,
                'quantity' => (int) ($request->input("barang_qty.$i") ?: 1),
                'condition' => $request->input("barang_kondisi.$i") ?: 'good',
                'movement' => $consumable ? 'IN' : ($request->input("barang_pergerakan.$i") ?: 'IN'),
                'frequency' => $request->input("barang_frekuensi.$i") ?: 'One-time',
                'is_consumable' => $consumable,
            ];

            $item = $existing->get((int) $request->input("barang_id.$i"));
            if ($item) {
                $item->fill($attributes);
            } else {
                $item = new ApplicationItem($attributes);
                $item->permit_application_id = $application->id;
            }

            $photo = $photos[$i] ?? null;
            if ($photo) {
                if ($item->photo_path) {
                    Storage::disk(self::DISK)->delete($item->photo_path);
                }
                $item->photo_path = $photo->store("sik/{$application->id}/photos", self::DISK);
            }

            $item->save();
            $keptIds[] = $item->id;
        }

        // Baris yang dihapus pengguna.
        $existing->except($keptIds)->each(function (ApplicationItem $item) {
            if ($item->photo_path) {
                Storage::disk(self::DISK)->delete($item->photo_path);
            }
            $item->delete();
        });
    }

    private function syncDocuments(PermitApplication $application, Request $request): void
    {
        // Surat permohonan: berkas baru menggantikan yang lama.
        if ($letter = $request->file('requestLetter')) {
            $this->replaceDocuments($application, ApplicationDocument::REQUEST, [$letter]);
        }
        // Lampiran: bila ada unggahan baru, seluruh lampiran lama diganti.
        $attachments = array_filter((array) $request->file('attachments', []));
        if ($attachments) {
            $this->replaceDocuments($application, ApplicationDocument::ATTACHMENT, $attachments);
        }
    }

    private function replaceDocuments(PermitApplication $application, string $kind, array $files): void
    {
        $application->documents()->where('kind', $kind)->get()->each(function (ApplicationDocument $document) {
            Storage::disk(self::DISK)->delete($document->path);
            $document->delete();
        });

        foreach ($files as $file) {
            $application->documents()->create([
                'kind' => $kind,
                'original_name' => $file->getClientOriginalName(),
                'path' => $file->store("sik/{$application->id}/documents", self::DISK),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }
}
