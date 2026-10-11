<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>Manajemen Jurusan<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Page Head -->
<div class="page-head rise">
    <div>
        <div class="eyebrow">Organisasi Akademik</div>
        <h1>Manajemen Jurusan</h1>
        <p class="sub">Kelola data jurusan yang berelasi dengan departemen dan tingkat (misalnya IPA, IPS, RPL).</p>
    </div>
    <div class="actions">
        <a href="<?= base_url('/admin/jurusan/create') ?>" class="btn btn-accent btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Tambah Jurusan
        </a>
    </div>
</div>

<div class="card rise" style="--d:80ms">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nama Jurusan</th>
                        <th>Departemen</th>
                        <th>Tingkat</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($jurusans)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty">
                                    <div class="empty-icon"><i class="bi bi-diagram-3"></i></div>
                                    <h6>Belum ada jurusan</h6>
                                    <p>Tambahkan jurusan pertama Anda untuk melengkapi struktur akademik.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($jurusans as $jurusan): ?>
                            <tr>
                                <td class="ps-4 fw-medium text-primary-dark"><?= esc($jurusan->nama_jurusan) ?></td>
                                <td><?= esc($jurusan->departemen) ?></td>
                                <td><span class="chip ghost num"><?= esc($jurusan->nama_tingkat ?? '-') ?></span></td>
                                <td>
                                    <?php if ($jurusan->is_active): ?>
                                        <span class="chip ok"><span class="dot breathe"></span> Aktif</span>
                                    <?php else: ?>
                                        <span class="chip danger"><i class="bi bi-slash-circle"></i> Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="<?= base_url('/admin/jurusan/edit/' . $jurusan->id) ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus" 
                                            onclick="confirmDelete(<?= $jurusan->id ?>, '<?= esc(addslashes($jurusan->nama_jurusan)) ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($pager): ?>
    <div class="card-footer py-3 border-top d-flex justify-content-end">
        <?= $pager->links('default', 'bootstrap_pagination') ?>
    </div>
    <?php endif; ?>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4 text-center">
                <p class="mb-0">Apakah Anda yakin ingin menghapus jurusan <br><strong id="deleteJurusanName" class="fs-5 mt-2 d-block"></strong></p>
                <p class="text-muted small mt-2">Data ini akan terhapus secara permanen dari sistem.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger px-4">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function confirmDelete(id, name) {
        document.getElementById('deleteJurusanName').textContent = name;
        document.getElementById('deleteForm').action = '<?= base_url('/admin/jurusan/delete/') ?>' + id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>
<?= $this->endSection() ?>
