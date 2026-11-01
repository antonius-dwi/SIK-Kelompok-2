@push('scripts')
<script>
    // Bila validasi server gagal pada salah satu modal, buka kembali modal itu agar
    // field bertanda merah (is-invalid) langsung terlihat tanpa klik ulang.
    document.querySelectorAll('.modal[data-reopen="1"]').forEach(function (el) {
        bootstrap.Modal.getOrCreateInstance(el).show();
    });
</script>
@endpush
