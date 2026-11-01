{{-- Modal pengguna --}}
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true" @if ($errForm === 'user') data-reopen="1" @endif>
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><form class="modal-content decision-form" method="post" id="userForm" autocomplete="off">
        @csrf
        <input type="hidden" name="_form" value="user">
        <input type="hidden" name="_method" value="PUT" id="userMethod" disabled>
        <div class="modal-header"><h2 class="modal-title" id="userModalLabel">Tambah pengguna</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
            <label class="detail-field">Nama<input class="form-control @if ($invalidOn('user', 'name')) is-invalid @endif" name="name" id="userName" value="{{ old('name') }}" required></label>
            <label class="detail-field mt-3">Nama pengguna<input class="form-control @if ($invalidOn('user', 'username')) is-invalid @endif" name="username" id="userUsername" value="{{ old('username') }}" required></label>
            <label class="detail-field mt-3">Kata sandi<input class="form-control @if ($invalidOn('user', 'password')) is-invalid @endif" name="password" id="userPassword" type="password" minlength="8" autocomplete="new-password"></label>
            <label class="detail-field mt-3">Peran
                <select class="form-select @if ($invalidOn('user', 'role')) is-invalid @endif" name="role" id="userRole" required>
                    <option value="">Pilih peran</option>
                    @foreach ($roles as $roleOption)<option value="{{ $roleOption->value }}" @selected(old('role') === $roleOption->value)>{{ $roleOption->label() }}</option>@endforeach
                </select>
            </label>
            <fieldset class="detail-field mt-3 module-checks">
                <legend class="module-checks-legend">Unit / departemen <span>(boleh lebih dari satu)</span></legend>
                <div class="module-check-grid">
                    @foreach ($activeDepartments as $d)
                        @php
                            $dipilih = in_array((string) $d->id, array_map('strval', (array) old('departments', [])), true);
                        @endphp
                        <label class="module-check">
                            <input type="checkbox" name="departments[]" value="{{ $d->id }}" @checked($dipilih)>
                            <span>{{ $d->name }}</span>
                        </label>
                    @endforeach
                </div>
                @if ($invalidOn('user', 'departments'))
                    <p class="invalid-feedback d-block">Pilih minimal satu unit/departemen.</p>
                @endif
            </fieldset>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form></div>
</div>
