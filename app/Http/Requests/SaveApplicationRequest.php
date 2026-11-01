<?php

namespace App\Http\Requests;

use App\Models\ApplicationDocument;
use App\Models\WorkflowTemplate;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form pemohon (draf / kirim). Nama field mengikuti form frontend:
 * applicantName, contactName, jobTitle, workDescription, area, workType (ID master), startDate, endDate,
 * personel_*[], barang_*[], requestLetter, attachments[].
 *
 * Tombol submit mengirim `action` = "draft" atau "submit".
 */
class SaveApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi dilakukan di controller lewat PermitApplicationPolicy
    }

    public function isSubmit(): bool
    {
        return $this->input('action') === 'submit';
    }

    /** Aturan "wajib" hanya berlaku saat kirim; draf boleh belum lengkap. */
    protected function required(): string
    {
        return $this->isSubmit() ? 'required' : 'nullable';
    }

    public function rules(): array
    {
        $req = $this->required();
        $application = $this->route('application');
        $hasRequestLetter = $application?->documents()->where('kind', ApplicationDocument::REQUEST)->exists() ?? false;

        return [
            'action' => ['required', Rule::in(['draft', 'submit'])],

            'applicantName' => [$req, 'string', 'max:255'],
            'contactName' => [$req, 'string', 'max:255'],
            'jobTitle' => [$req, 'string', 'max:255'],
            // Uraian/tujuan pekerjaan: dipakai pada surat SIK (bukan jabatan).
            'workDescription' => [$req, 'string', 'max:500'],
            'area' => [$req, 'integer', Rule::exists('areas', 'id')->where('is_active', true)],
            'workType' => [$req, 'integer', Rule::exists('work_types', 'id')->where('is_active', true)],
            'startDate' => [$req, 'date'],
            'endDate' => [$req, 'date', 'after_or_equal:startDate'],

            // Personel (array sejajar)
            'personel_nama' => [$this->isSubmit() ? 'required' : 'nullable', 'array', 'min:' . ($this->isSubmit() ? 1 : 0)],
            'personel_nama.*' => [$req, 'string', 'max:255'],
            'personel_hp' => [$this->isSubmit() ? 'required' : 'nullable', 'array'],
            // Nomor HP disimpan sebagai string (kolom varchar) agar angka 0 di depan tidak hilang.
            'personel_hp.*' => [$req, 'string', 'max:25'],
            'personel_plat' => ['nullable', 'array'],
            'personel_plat.*' => ['nullable', 'string', 'max:20'],

            // Barang / peralatan (array sejajar)
            'barang_id' => ['nullable', 'array'],
            'barang_id.*' => ['nullable', 'integer'],
            'barang_nama' => [$this->isSubmit() ? 'required' : 'nullable', 'array', 'min:' . ($this->isSubmit() ? 1 : 0)],
            'barang_nama.*' => [$req, 'string', 'max:255'],
            'barang_brand.*' => ['nullable', 'string', 'max:255'],
            'barang_model.*' => ['nullable', 'string', 'max:255'],
            'barang_sn.*' => ['nullable', 'string', 'max:255'],
            'barang_asset_no.*' => ['nullable', 'string', 'max:255'],
            'barang_qty' => [$this->isSubmit() ? 'required' : 'nullable', 'array'],
            'barang_qty.*' => [$req, 'integer', 'min:1', 'max:100000'],
            'barang_kondisi.*' => ['nullable', Rule::in(['good', 'fair', 'needs_repair'])],
            'barang_pergerakan.*' => ['nullable', Rule::in(['IN', 'OUT', 'IN & OUT'])],
            'barang_frekuensi.*' => ['nullable', Rule::in(['One-time', 'Daily', 'Weekly', 'Other'])],
            'barang_consumable.*' => ['nullable', Rule::in(['0', '1'])],
            'barang_foto.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            // Dokumen (PDF)
            'requestLetter' => [$hasRequestLetter || ! $this->isSubmit() ? 'nullable' : 'required', 'file', 'mimes:pdf', 'max:10240'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:pdf', 'max:10240'],
        ];
    }

    public function attributes(): array
    {
        return [
            'applicantName' => 'nama perusahaan / pemohon',
            'contactName' => 'nama penanggung jawab',
            'jobTitle' => 'jabatan pekerjaan',
            'workDescription' => 'uraian pekerjaan',
            'area' => 'area',
            'workType' => 'jenis pekerjaan',
            'startDate' => 'tanggal mulai',
            'endDate' => 'tanggal selesai',
            'requestLetter' => 'surat permohonan',
            'attachments.*' => 'lampiran',
            // Kunci induk (tanpa .*) dipakai saat array-nya sama sekali tidak dikirim;
            // tanpa ini pesannya tampil mentah, misalnya "personel_nama wajib diisi".
            'personel_nama' => 'nama personel',
            'personel_nama.*' => 'nama personel',
            'personel_hp' => 'nomor HP personel',
            'personel_hp.*' => 'nomor HP personel',
            'barang_nama' => 'nama barang',
            'barang_nama.*' => 'nama barang',
            'barang_qty' => 'jumlah barang',
            'barang_qty.*' => 'jumlah barang',
            'barang_foto.*' => 'foto barang',
        ];
    }

    public function messages(): array
    {
        return [
            'endDate.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'startDate.date' => 'Tanggal mulai harus berupa tanggal yang valid.',
            'endDate.date' => 'Tanggal selesai harus berupa tanggal yang valid.',
            'personel_nama.required' => 'Minimal satu personel wajib diisi.',
            'personel_nama.min' => 'Minimal satu personel wajib diisi.',
            'personel_hp.required' => 'Nomor HP personel wajib diisi.',
            'personel_hp.*.max' => 'Nomor HP personel maksimal 25 karakter.',
            'barang_nama.required' => 'Minimal satu barang wajib diisi.',
            'barang_nama.min' => 'Minimal satu barang wajib diisi.',
            'barang_qty.required' => 'Jumlah barang wajib diisi.',
            'barang_qty.*.integer' => 'Jumlah barang harus berupa angka.',
            'barang_qty.*.min' => 'Jumlah barang minimal 1.',
            'barang_qty.*.max' => 'Jumlah barang maksimal 100.000.',
            'requestLetter.required' => 'Surat permohonan (PDF) wajib diunggah saat Kirim pengajuan.',
            'requestLetter.mimes' => 'Surat permohonan harus berformat PDF.',
            'requestLetter.max' => 'Surat permohonan maksimal 10 MB.',
            'attachments.*.mimes' => 'Lampiran harus berformat PDF.',
            'attachments.*.max' => 'Lampiran maksimal 10 MB per berkas.',
            'barang_foto.*.image' => 'Foto barang harus berupa gambar (JPG, PNG, atau WEBP).',
            'barang_foto.*.mimes' => 'Foto barang harus berformat JPG, PNG, atau WEBP.',
            'barang_foto.*.max' => 'Foto barang maksimal 5 MB.',
        ];
    }

    /** Baris personel/barang harus sejajar (jumlah entri tiap array sama). */
    public function after(): array
    {
        return [function (Validator $validator) {
            // Kombinasi tanpa templat TIDAK BOLEH dikirim ke Admin (hanya saat
            // Kirim pengajuan; Simpan draf tetap boleh). Ini pagar server-nya:
            // peringatan di form bisa dilewati client, validasi ini tidak.
            // Dilewati bila area/workType sendiri sudah gagal validasi, supaya
            // tidak menumpuk pesan yang salah di atas error required/exists.
            if ($this->isSubmit()) {
                $areaId = (int) $this->input('area');
                $workTypeId = (int) $this->input('workType');
                if ($areaId && $workTypeId
                    && ! $validator->errors()->has('area')
                    && ! $validator->errors()->has('workType')) {
                    $template = WorkflowTemplate::resolve($areaId, $workTypeId);
                    if (! $template || $template->steps->isEmpty()) {
                        $validator->errors()->add('workType', 'Kombinasi Area + Jenis pekerjaan ini belum memiliki templat alur kerja yang aktif, sehingga pengajuan tidak dapat dikirim ke Admin. Pilih kombinasi lain atau hubungi System Admin.');
                    }
                }
            }

            $groups = [
                'personel' => ['personel_nama', 'personel_hp', 'personel_plat'],
                'barang' => ['barang_nama', 'barang_brand', 'barang_model', 'barang_sn', 'barang_asset_no',
                    'barang_qty', 'barang_kondisi', 'barang_pergerakan', 'barang_frekuensi', 'barang_consumable'],
            ];
            foreach ($groups as $label => $keys) {
                $lengths = collect($keys)->map(fn ($k) => $this->has($k) ? count((array) $this->input($k)) : null)
                    ->filter(fn ($n) => $n !== null)->unique();
                if ($lengths->count() > 1) {
                    $validator->errors()->add($keys[0], "Jumlah baris {$label} tidak sejajar antar kolom.");
                }
            }
        }];
    }
}
