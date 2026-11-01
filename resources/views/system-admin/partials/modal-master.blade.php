{{-- Modal master sederhana (Area / Jenis pekerjaan / Departemen) --}}
<div class="modal fade" id="masterModal" tabindex="-1" aria-labelledby="masterModalLabel" aria-hidden="true" @if ($errForm === 'master') data-reopen="1" @endif>
    <div class="modal-dialog modal-dialog-centered"><form class="modal-content decision-form" method="post" id="masterForm">
        @csrf
        <input type="hidden" name="_form" value="master">
        <input type="hidden" name="_method" value="PUT" id="masterMethod" disabled>
        <div class="modal-header"><h2 class="modal-title" id="masterModalLabel">Tambah data</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
            <label class="detail-field">Nama<input class="form-control @if ($invalidOn('master', 'name')) is-invalid @endif" name="name" id="masterName" value="{{ old('name') }}" required></label>
            <label class="detail-field mt-3">Kode<input class="form-control @if ($invalidOn('master', 'code')) is-invalid @endif" name="code" id="masterCode" value="{{ old('code') }}" maxlength="10" required></label>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form></div>
</div>
