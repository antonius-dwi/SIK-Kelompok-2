{{-- Modal templat alur --}}
<div class="modal fade" id="workflowModal" tabindex="-1" aria-labelledby="workflowModalLabel" aria-hidden="true" @if ($errForm === 'workflow') data-reopen="1" @endif>
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><form class="modal-content decision-form" method="post" id="workflowForm">
        @csrf
        <input type="hidden" name="_form" value="workflow">
        <input type="hidden" name="_method" value="PUT" id="workflowMethod" disabled>
        <div class="modal-header"><h2 class="modal-title" id="workflowModalLabel">Tambah templat alur kerja</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
            <label class="detail-field">Area
                <select class="form-select @if ($invalidOn('workflow', 'area')) is-invalid @endif" name="area" id="workflowArea" required>
                    <option value="">Pilih area kerja</option>
                    @foreach ($activeAreas as $a)<option value="{{ $a->id }}" @selected((string) old('area') === (string) $a->id)>{{ $a->name }}</option>@endforeach
                </select>
            </label>
            <label class="detail-field mt-3">Jenis pekerjaan
                <select class="form-select @if ($invalidOn('workflow', 'workType')) is-invalid @endif" name="workType" id="workflowWorkType" required>
                    <option value="">Pilih jenis pekerjaan</option>
                    @foreach ($activeTypes as $t)<option value="{{ $t->id }}" @selected((string) old('workType') === (string) $t->id)>{{ $t->name }}</option>@endforeach
                </select>
            </label>
            <div class="table-toolbar"><strong>Tahap penyetuju (maksimal 8)</strong><button class="btn btn-sm btn-outline-primary" id="addWorkflowStep" type="button">Tambah tahap</button></div>
            <div id="workflowSteps" class="workflow-steps"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form></div>
</div>

<template id="tpl-workflow-step">
    <div class="workflow-step-row">
        <strong class="workflow-step-number"></strong>
        <input class="form-control workflow-title-input" placeholder="Jabatan penyetuju" required>
        <select class="form-select workflow-account-select" required>
            <option value="">Pilih akun approver</option>
            @foreach ($approvers as $approver)<option value="{{ $approver->id }}">{{ $approver->name }} · {{ $approver->username }}</option>@endforeach
        </select>
        <button class="btn btn-outline-danger remove-workflow-step" type="button">Hapus</button>
    </div>
</template>
