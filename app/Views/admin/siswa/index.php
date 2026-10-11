<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= lang('App.menu_siswa') ?>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
<style>
    .filter-card { margin-bottom: 1.5rem; }
    .table-responsive { overflow-x: auto; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="page-head">
    <div>
        <div class="eyebrow">Manajemen</div>
        <h1><?= lang('App.menu_siswa') ?></h1>
        <div class="sub">Daftar siswa dari database Jibas Akademik.</div>
    </div>
    <div class="actions">
        <button type="button" id="btnGenerateUser" class="btn btn-accent btn-sm">
            <i class="bi bi-person-gear me-1"></i> Generate User
        </button>
    </div>
</div>

<div class="card filter-card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Departemen</label>
                <select id="filter-departemen" class="form-select">
                    <option value="">-- Pilih Departemen --</option>
                    <?php foreach ($departemen as $d): ?>
                        <option value="<?= esc($d->departemen) ?>"><?= esc($d->departemen) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tahun Ajaran</label>
                <select id="filter-tahunajaran" class="form-select" disabled>
                    <option value="">Semua Tahun Ajaran</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tingkat</label>
                <select id="filter-tingkat" class="form-select" disabled>
                    <option value="">Semua Tingkat</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Kelas</label>
                <select id="filter-kelas" class="form-select" disabled>
                    <option value="">Semua Kelas</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped w-100" id="tableSiswa">
                <thead>
                    <tr>
                        <th>NIS</th>
                        <th>Nama Lengkap</th>
                        <th>Kelamin</th>
                        <th>Kelas</th>
                        <th>Tingkat</th>
                        <th>Departemen</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Diisi via DataTables Ajax -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('vendor/jquery/jquery-3.6.0.min.js') ?>"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    const table = $('#tableSiswa').DataTable({
        responsive: true,
        processing: true,
        ajax: {
            url: '<?= base_url('admin/siswa/data') ?>',
            data: function(d) {
                d.departemen = $('#filter-departemen').val();
                d.tahunajaran = $('#filter-tahunajaran').val();
                d.tingkat = $('#filter-tingkat').val();
                d.kelas = $('#filter-kelas').val();
            }
        },
        columns: [
            { data: 'nis' },
            { data: 'nama', render: function(data, type, row) {
                return `<span class="fw-semibold">${data}</span>`;
            }},
            { data: 'kelamin', render: function(data) {
                if (data === 'L') return '<span class="badge bg-primary">Laki-laki</span>';
                if (data === 'P') return '<span class="badge bg-danger">Perempuan</span>';
                return data;
            }},
            { data: 'kelas' },
            { data: 'tingkat' },
            { data: 'departemen' }
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
        }
    });

    // Handle chained dropdowns
    $('#filter-departemen').on('change', function() {
        const dept = $(this).val();
        
        // Reset sub-filters
        $('#filter-tahunajaran').html('<option value="">Semua Tahun Ajaran</option>').prop('disabled', !dept);
        $('#filter-tingkat').html('<option value="">Semua Tingkat</option>').prop('disabled', !dept);
        $('#filter-kelas').html('<option value="">Semua Kelas</option>').prop('disabled', true);
        
        if (dept) {
            // Fetch Tahun Ajaran
            $.get('<?= base_url('admin/siswa/options/tahunajaran') ?>', { departemen: dept }, function(data) {
                let options = '<option value="">Semua Tahun Ajaran</option>';
                data.forEach(item => {
                    options += `<option value="${item.replid}">${item.tahunajaran}</option>`;
                });
                $('#filter-tahunajaran').html(options);
            });
            
            // Fetch Tingkat
            $.get('<?= base_url('admin/siswa/options/tingkat') ?>', { departemen: dept }, function(data) {
                let options = '<option value="">Semua Tingkat</option>';
                data.forEach(item => {
                    options += `<option value="${item.replid}">${item.tingkat}</option>`;
                });
                $('#filter-tingkat').html(options);
            });
        }
        table.ajax.reload();
    });

    $('#filter-tahunajaran, #filter-tingkat').on('change', function() {
        const ta = $('#filter-tahunajaran').val();
        const tk = $('#filter-tingkat').val();
        
        // Either one could be filled, but they share the same 'kelas' endpoint
        // Wait, 'kelas' needs idtahunajaran or idtingkat. If neither is present but dept is, maybe we fetch all kelas for that dept?
        // Let's just fetch 'kelas' if at least 'dept' is selected, but passing ta and tk.
        const dept = $('#filter-departemen').val();
        
        if (ta || tk) {
            $('#filter-kelas').prop('disabled', false);
            $.get('<?= base_url('admin/siswa/options/kelas') ?>', { idtahunajaran: ta, idtingkat: tk }, function(data) {
                let options = '<option value="">Semua Kelas</option>';
                data.forEach(item => {
                    options += `<option value="${item.replid}">${item.kelas}</option>`;
                });
                $('#filter-kelas').html(options);
            });
        } else {
            $('#filter-kelas').html('<option value="">Semua Kelas</option>').prop('disabled', true);
        }
        
        table.ajax.reload();
    });

    $('#filter-kelas').on('change', function() {
        table.ajax.reload();
    });
    
    // Initial trigger if departemen is already selected (e.g., from browser cache/reload)
    if ($('#filter-departemen').val()) {
        $('#filter-departemen').trigger('change');
    }

    // Generate User action
    $('#btnGenerateUser').on('click', function() {
        const data = {
            departemen: $('#filter-departemen').val(),
            tahunajaran: $('#filter-tahunajaran').val(),
            tingkat: $('#filter-tingkat').val(),
            kelas: $('#filter-kelas').val()
        };

        Swal.fire({
            title: 'Generate User?',
            text: "User siswa akan digenerate ke database JBSCBT dengan password acak 6 karakter untuk data yang difilter saat ini. User yang sudah ada akan dilewati.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Generate!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang membuat user, mohon tunggu.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '<?= base_url('admin/siswa/generate') ?>',
                    type: 'POST',
                    data: data,
                    headers: { 'X-CSRF-TOKEN': '<?= csrf_hash() ?>' },
                    success: function(res) {
                        if (res.status === 'success') {
                            Swal.fire('Berhasil', res.message, 'success');
                        } else {
                            Swal.fire('Gagal', res.message || 'Terjadi kesalahan.', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Koneksi ke server terputus.', 'error');
                    }
                });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
