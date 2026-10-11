<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?><?= $module ? 'Edit Modul' : 'Tambah Modul Baru' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 fw-bold">
                    <i class="bi bi-<?= $module ? 'pencil-square' : 'plus-circle' ?> me-1"></i> 
                    <?= $module ? 'Edit Data Modul' : 'Form Tambah Modul' ?>
                </h6>
            </div>
            
            <form action="<?= base_url('/admin/modules/' . ($module ? 'update/'.$module->id : 'store')) ?>" method="POST">
                <?= csrf_field() ?>
                
                <div class="card-body p-4">
                    <?php if (session()->has('errors')): ?>
                        <div class="alert alert-danger rounded-3">
                            <ul class="mb-0 ps-3">
                            <?php foreach (session('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach ?>
                            </ul>
                        </div>
                    <?php endif ?>

                    <div class="mb-4">
                        <label for="name" class="form-label fw-semibold">Nama Modul <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg fs-6" id="name" name="name" 
                               value="<?= old('name', $module->name ?? '') ?>" required
                               placeholder="Contoh: Ujian Akhir Semester">
                    </div>

                    <div class="mb-4">
                        <label for="departemen" class="form-label fw-semibold">Departemen (Opsional)</label>
                        <select id="departemen" name="departemen" class="form-select">
                            <option value="">-- Berlaku untuk semua --</option>
                            <?php foreach ($departemen as $d): ?>
                                <option value="<?= esc($d->departemen) ?>" <?= old('departemen', $module->departemen ?? '') == $d->departemen ? 'selected' : '' ?>><?= esc($d->departemen) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="idtingkat" class="form-label fw-semibold">Tingkat (Opsional)</label>
                        <select id="idtingkat" name="idtingkat" class="form-select" data-selected="<?= old('idtingkat', $module->idtingkat ?? '') ?>" <?= empty(old('departemen', $module->departemen ?? '')) ? 'disabled' : '' ?>>
                            <option value="">-- Berlaku untuk semua --</option>
                            <?php if (isset($tingkat)): ?>
                                <?php foreach ($tingkat as $t): ?>
                                    <option value="<?= esc($t->replid) ?>" <?= old('idtingkat', $module->idtingkat ?? '') == $t->replid ? 'selected' : '' ?>><?= esc($t->tingkat) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="idjurusan" class="form-label fw-semibold">Jurusan (Opsional)</label>
                        <select id="idjurusan" name="idjurusan" class="form-select" data-selected="<?= old('idjurusan', $module->idjurusan ?? '') ?>" <?= empty(old('idtingkat', $module->idtingkat ?? '')) ? 'disabled' : '' ?>>
                            <option value="">-- Berlaku untuk semua --</option>
                            <?php if (isset($jurusan)): ?>
                                <?php foreach ($jurusan as $j): ?>
                                    <option value="<?= esc($j->id) ?>" <?= old('idjurusan', $module->idjurusan ?? '') == $j->id ? 'selected' : '' ?>><?= esc($j->nama_jurusan) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_enabled" name="is_enabled" value="1" style="width: 2.5rem; height: 1.25rem;"
                                   <?= old('is_enabled', $module->is_enabled ?? '1') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold ms-2 mt-1" for="is_enabled">Modul Aktif</label>
                        </div>
                        <div class="form-text mt-2 text-muted">Jika dinonaktifkan, seluruh subjek dan soal dalam modul ini tidak akan muncul dalam ujian.</div>
                    </div>
                </div>

                <div class="card-footer bg-light py-3 border-top d-flex justify-content-end gap-2">
                    <a href="<?= base_url('/admin/modules') ?>" class="btn btn-outline-secondary px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Simpan Modul
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const deptSelect = document.getElementById('departemen');
    const tingkatSelect = document.getElementById('idtingkat');
    const jurusanSelect = document.getElementById('idjurusan');

    function loadOptions(url, targetSelect, selectedValue = '') {
        targetSelect.innerHTML = '<option value="">Memuat...</option>';
        targetSelect.disabled = true;
        fetch(url)
            .then(res => res.json())
            .then(data => {
                targetSelect.innerHTML = '<option value="">-- Berlaku untuk semua --</option>';
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.text;
                    if(item.id == selectedValue) opt.selected = true;
                    targetSelect.appendChild(opt);
                });
                targetSelect.disabled = false;
            });
    }

    deptSelect.addEventListener('change', function() {
        if (!this.value) {
            tingkatSelect.innerHTML = '<option value="">-- Berlaku untuk semua --</option>';
            tingkatSelect.disabled = true;
            jurusanSelect.innerHTML = '<option value="">-- Berlaku untuk semua --</option>';
            jurusanSelect.disabled = true;
            return;
        }

        loadOptions('<?= base_url('admin/siswa/options/tingkat') ?>?departemen=' + this.value, tingkatSelect, tingkatSelect.dataset.selected);
        
        jurusanSelect.innerHTML = '<option value="">-- Berlaku untuk semua --</option>';
        jurusanSelect.disabled = true;
        tingkatSelect.dataset.selected = '';
        jurusanSelect.dataset.selected = '';
    });

    tingkatSelect.addEventListener('change', function() {
        if (!this.value) {
            jurusanSelect.innerHTML = '<option value="">-- Berlaku untuk semua --</option>';
            jurusanSelect.disabled = true;
            return;
        }

        loadOptions('<?= base_url('admin/jurusan/options') ?>?departemen=' + deptSelect.value + '&idtingkat=' + this.value, jurusanSelect, jurusanSelect.dataset.selected);
        jurusanSelect.dataset.selected = '';
    });

    if(deptSelect.value && tingkatSelect.options.length <= 1) {
        deptSelect.dispatchEvent(new Event('change'));
    }
</script>
<?= $this->endSection() ?>
