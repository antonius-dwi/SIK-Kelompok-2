{{-- Modal pratinjau SIK: iframe dimuat saat modal dibuka, tombol Unduh PDF mencetak isi iframe
     (browser menawarkan "Save as PDF"; nama berkas mengikuti judul dokumen di permits/embed).
     Dipakai dari tombol dengan atribut data-permit-src. --}}
<div class="modal fade" id="permitModal" tabindex="-1" aria-labelledby="permitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-md-down permit-modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="permitModalLabel">Surat Izin Kerja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body permit-modal-body">
                <iframe class="permit-frame" src="about:blank" title="Pratinjau Surat Izin Kerja" loading="lazy"></iframe>
            </div>
            <div class="modal-footer permit-modal-footer">
                <span class="permit-modal-hint">Berkas PDF diunduh langsung tanpa membuka tab baru.</span>
                <span class="permit-download-status" data-permit-status role="status" aria-live="polite"></span>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" data-permit-download>
                    <i class="fa-solid fa-download me-2"></i><span data-permit-download-label>Unduh PDF</span>
                </button>
            </div>
        </div>
    </div>
</div>
