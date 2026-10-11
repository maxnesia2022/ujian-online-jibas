<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?><?= $jurusan ? 'Edit Jurusan' : 'Tambah Jurusan Baru' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 fw-bold">
                    <i class="bi bi-<?= $jurusan ? 'pencil-square' : 'plus-circle' ?> me-1"></i> 
                    <?= $jurusan ? 'Edit Data Jurusan' : 'Form Tambah Jurusan' ?>
                </h6>
            </div>
            
            <form action="<?= base_url('/admin/jurusan/' . ($jurusan ? 'update/'.$jurusan->id : 'store')) ?>" method="POST">
                <?= csrf_field() ?>
                
                <div class="card-body p-4">
                    <?php if (session('errors')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0 ps-3">
                                <?php foreach (session('errors') as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif ?>

                    <div class="mb-4">
                        <label for="departemen" class="form-label fw-semibold">Departemen <span class="text-danger">*</span></label>
                        <select id="departemen" name="departemen" class="form-select" required>
                            <option value="">-- Pilih Departemen --</option>
                            <?php foreach ($departemen as $d): ?>
                                <option value="<?= esc($d->departemen) ?>" <?= old('departemen', $jurusan->departemen ?? '') == $d->departemen ? 'selected' : '' ?>><?= esc($d->departemen) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="idtingkat" class="form-label fw-semibold">Tingkat <span class="text-danger">*</span></label>
                        <select id="idtingkat" name="idtingkat" class="form-select" required data-selected="<?= old('idtingkat', $jurusan->idtingkat ?? '') ?>" <?= empty(old('departemen', $jurusan->departemen ?? '')) ? 'disabled' : '' ?>>
                            <option value="">-- Pilih Tingkat --</option>
                            <?php if (isset($tingkat)): ?>
                                <?php foreach ($tingkat as $t): ?>
                                    <option value="<?= esc($t->replid) ?>" <?= old('idtingkat', $jurusan->idtingkat ?? '') == $t->replid ? 'selected' : '' ?>><?= esc($t->tingkat) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="nama_jurusan" class="form-label fw-semibold">Nama Jurusan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg fs-6" id="nama_jurusan" name="nama_jurusan" 
                               value="<?= old('nama_jurusan', $jurusan->nama_jurusan ?? '') ?>" required
                               placeholder="Contoh: IPA, IPS, Teknik Kendaraan Ringan">
                    </div>

                    <div class="mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                                   <?= old('is_active', $jurusan->is_active ?? '1') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold ms-2" for="is_active">Jurusan Aktif</label>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light border-top py-3 d-flex justify-content-end gap-2">
                    <a href="<?= base_url('/admin/jurusan') ?>" class="btn btn-outline-secondary px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Simpan Jurusan
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

    deptSelect.addEventListener('change', function() {
        if (!this.value) {
            tingkatSelect.innerHTML = '<option value="">-- Pilih Tingkat --</option>';
            tingkatSelect.disabled = true;
            return;
        }

        tingkatSelect.innerHTML = '<option value="">Memuat...</option>';
        tingkatSelect.disabled = true;

        fetch('<?= base_url('admin/siswa/options/tingkat') ?>?departemen=' + this.value)
            .then(res => res.json())
            .then(data => {
                tingkatSelect.innerHTML = '<option value="">-- Pilih Tingkat --</option>';
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.text;
                    if(item.id == tingkatSelect.dataset.selected) opt.selected = true;
                    tingkatSelect.appendChild(opt);
                });
                tingkatSelect.disabled = false;
            });
    });

    if(deptSelect.value && tingkatSelect.options.length <= 1) {
        deptSelect.dispatchEvent(new Event('change'));
    }
</script>
<?= $this->endSection() ?>
