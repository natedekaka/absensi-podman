<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config.php';
require_once '../includes/header.php';
?>

<style>
:root {
    --wa-green: #075E54;
    --wa-light: #25D366;
    --wa-chat: #dcf8c6;
    --wa-bg: #ECE5DD;
    --wa-text: #333;
}

body {
    margin: 0;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    background-color: var(--wa-bg) !important;
    color: var(--wa-text);
}

.content-wrapper {
    flex-grow: 1;
}

.absensi-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    transition: transform 0.2s, box-shadow 0.2s;
}
.absensi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(7,94,84,0.12);
}
.btn-wa-primary {
    background: var(--wa-green);
    border: none;
    border-radius: 50px;
    padding: 0.6rem 1.5rem;
    color: white;
    font-weight: 500;
}
.btn-wa-primary:hover {
    background: #054c43;
    transform: scale(1.03);
    color: white;
}
.search-input {
    border-radius: 50px;
    padding-left: 2.5rem;
    border: 2px solid #e0e0e0;
}
.search-input:focus {
    border-color: var(--wa-light);
    box-shadow: 0 0 0 0.2rem rgba(37,211,102,0.25);
}
.search-icon {
    position: absolute;
    left: 1.25rem;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
}
</style>

<div class="content-wrapper">
    <div class="container py-4">
        <!-- Pesan Sukses / Info -->
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-warning alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <strong><?= htmlspecialchars($_GET['msg']) ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                Absensi berhasil disimpan!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex align-items-center mb-4">
            <h2 class="fw-bold mb-0" style="color: var(--wa-green);">
                <i class="fas fa-calendar-check me-2"></i>Input Absensi
            </h2>
        </div>

        <form method="POST" action="proses.php" id="form-absensi">
            <!-- ... (semua form absensi tetap sama seperti sebelumnya) ... -->
            <input type="hidden" name="kelas_id" id="kelas_id">

            <div class="row g-4 mb-4">
                <div class="col-md-6 col-lg-4">
                    <div class="absensi-card p-3 h-100">
                        <label class="form-label fw-semibold d-flex align-items-center mb-2" style="color: var(--wa-green);">
                            <i class="fas fa-calendar-alt me-2"></i> Tanggal
                        </label>
                        <input type="date" name="tanggal" id="tanggal" 
                               class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="absensi-card p-3 h-100">
                        <label class="form-label fw-semibold d-flex align-items-center mb-2" style="color: var(--wa-green);">
                            <i class="fas fa-chalkboard me-2"></i> Kelas
                        </label>
                        <select id="kelas" class="form-select" required>
                            <option value="">Pilih Kelas</option>
                            <option value="all">Semua Kelas</option>
                            <?php
                            $kelas = $koneksi->query("SELECT * FROM kelas ORDER BY nama_kelas");
                            while ($row = $kelas->fetch_assoc()):
                            ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nama_kelas']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mb-4" id="searchContainer" style="display: none;">
                <div class="col-md-6">
                    <div class="position-relative">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="search_nama" class="form-control search-input" 
                               placeholder="Cari nama siswa...">
                    </div>
                </div>
            </div>

            <div id="tombolSimpanAtas" class="mb-4" style="display: none;">
                <button type="submit" class="btn btn-wa-primary">
                    <i class="fas fa-save me-2"></i>Simpan Absensi
                </button>
            </div>

            <div id="siswa-container" class="mb-4"></div>

            <div id="tombolSimpanBawah" class="text-center" style="display: none;">
                <button type="submit" class="btn btn-wa-primary btn-lg px-5">
                    <i class="fas fa-save me-2"></i>Simpan Semua Absensi
                </button>
            </div>
        </form>

        <!-- Tombol Hapus Semua Riwayat -->
        <div class="text-center my-5">
            <button type="button" class="btn btn-outline-danger btn-lg shadow-sm px-5" 
                    data-bs-toggle="modal" data-bs-target="#hapusSemuaModal">
                <i class="fas fa-trash-alt me-2"></i> Hapus Semua Riwayat Absensi (Reset Total)
            </button>
        </div>

        <!-- Tombol Export Backup Database -->
        <div class="text-center my-5">
            <button type="button" class="btn btn-success btn-lg shadow-sm px-5" 
                    onclick="window.location.href='backup_database.php'">
                <i class="fas fa-download me-2"></i> Export Backup Database (.sql)
            </button>
            <p class="text-muted small mt-2">
                Download backup lengkap database (struktur + semua data)
            </p>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Semua (tetap di luar content-wrapper karena modal Bootstrap bisa di mana saja) -->
<div class="modal fade" id="hapusSemuaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i> Peringatan Hapus Permanen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="lead">Anda akan <strong>menghapus SELURUH riwayat absensi</strong> semua siswa.</p>
                <p>Rekap historis (H/T/S/I/A) akan kembali ke <strong>0</strong>.</p>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-download me-2"></i>
                    <strong>Backup CSV otomatis</strong> akan di-download sebelum data dihapus.
                </div>
                <p class="text-danger fw-bold mt-3">TINDAKAN INI TIDAK BISA DIBALIK!</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger btn-lg px-4" id="confirmHapusSemua">
                    <i class="fas fa-trash me-2"></i> Ya, Hapus Permanen
                </button>
            </div>
        </div>
    </div>
</div>

<script>
/* ... (semua script JavaScript tetap sama seperti yang kamu punya) ... */
function toggleElements(show) {
    const display = show ? 'block' : 'none';
    document.getElementById('tombolSimpanAtas').style.display = display;
    document.getElementById('tombolSimpanBawah').style.display = display;
    document.getElementById('searchContainer').style.display = display;
}

document.getElementById('kelas').addEventListener('change', function () {
    const kelasId = this.value;
    document.getElementById('kelas_id').value = kelasId;

    if (kelasId) {
        toggleElements(true);
        loadSiswa();
    } else {
        toggleElements(false);
        document.getElementById('siswa-container').innerHTML = '';
    }
});

document.getElementById('tanggal').addEventListener('change', loadSiswa);
document.getElementById('search_nama').addEventListener('input', loadSiswa);

function loadSiswa() {
    const kelasId = document.getElementById('kelas').value;
    const tanggal = document.getElementById('tanggal').value;
    const search = document.getElementById('search_nama').value;

    if (kelasId) {
        let url = `get_siswa.php?kelas_id=${encodeURIComponent(kelasId)}&tanggal=${encodeURIComponent(tanggal)}`;
        if (search) url += `&search=${encodeURIComponent(search)}`;

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(data => {
                document.getElementById('siswa-container').innerHTML = data;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('siswa-container').innerHTML = 
                    '<div class="alert alert-danger rounded-3">Gagal memuat data siswa.</div>';
            });
    }
}

// Script Backup + Hapus Semua
document.getElementById('confirmHapusSemua').addEventListener('click', function() {
    const link = document.createElement('a');
    link.href = 'backup_absensi.php';
    link.download = '';
    link.target = '_blank';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    setTimeout(() => {
        if (confirm("Backup CSV sudah mulai terdownload?\nKlik OK untuk melanjutkan menghapus semua data absensi.")) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'proses.php';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'action';
            input.value = 'hapus_semua_absensi';
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
        }
    }, 1500);
});
</script>

<?php require_once '../includes/footer.php'; ?>