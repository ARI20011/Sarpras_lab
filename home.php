<?php
/**
 * ===========================
 * HOME.PHP - Admin Dashboard Statistics
 * ===========================
 * 
 * File ini berfungsi untuk:
 * 1. Menampilkan statistik utama dashboard admin
 * 2. Menampilkan data ringkas tentang sistem lab
 * 3. Menampilkan chart/grafik statistik
 * 
 * Statistik yang ditampilkan:
 * - Total jumlah lab
 * - Total inventaris
 * - Total barang
 * - Total peminjaman (jika ada)
 * - Grafik peminjaman per bulan
 * - Grafik peminjaman per kelas
 * 
 * Data sumber:
 * - Dari tabel 'lab' di database
 * - Dari tabel 'inventaris' di database
 * - Dari tabel 'barang' di database
 * - Dari tabel 'peminjaman_siswa' di database (jika ada)
 */

// Inisialisasi data chart
$chartAreaLabels = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
$chartAreaData = array_fill(0, 12, 0); // Array dengan 12 elemen (untuk 12 bulan), awal semua 0
$chartPieLabels = ["Belum Ada Data"];
$chartPieData = [1];

// Ambil data siswa untuk chart distribusi kelas
$totalSiswa = 0;
$siswaByKelas = [];
$querySiswaChart = mysqli_query($koneksi, "SELECT `kelas`, COUNT(*) AS total FROM `siswa` GROUP BY `kelas` ORDER BY `kelas` ASC");
if ($querySiswaChart) {
    while ($row = mysqli_fetch_assoc($querySiswaChart)) {
        $kelasLabel = trim($row['kelas'] ?? 'Tidak Diketahui');
        $jumlah = (int) ($row['total'] ?? 0);
        if ($kelasLabel === '') {
            $kelasLabel = 'Tidak Diketahui';
        }
        $siswaByKelas[$kelasLabel] = $jumlah;
        $totalSiswa += $jumlah;
    }
}

// Cek apakah tabel peminjaman_siswa ada di database
$cekTabelPeminjaman = mysqli_query($koneksi, "SHOW TABLES LIKE 'peminjaman_siswa'");
if ($cekTabelPeminjaman && mysqli_num_rows($cekTabelPeminjaman) > 0) {
    // Jika tabel ada, ambil semua data peminjaman
    $resultPinjam = mysqli_query($koneksi, "SELECT * FROM `peminjaman_siswa`");
    
    // Kolom tanggal yang mungkin ada di database (untuk fleksibilitas)
    $kolomTanggalKandidat = ['tgl_pinjam', 'tanggal_pinjam', 'tanggal', 'created_at', 'tgl', 'date'];
    
    // Kolom kelas yang mungkin ada di database (untuk fleksibilitas)
    $kolomKelasKandidat = ['kelas', 'nama_kelas', 'class', 'rombel', 'tingkat'];
    
    // Array untuk menghitung peminjaman per kelas
    $classCount = [];

    if ($resultPinjam) {
        while ($row = mysqli_fetch_assoc($resultPinjam)) {
            // Cari tanggal peminjaman dari kolom yang tersedia
            $tanggalDipakai = null;
            foreach ($kolomTanggalKandidat as $kolomTanggal) {
                if (!empty($row[$kolomTanggal])) {
                    $timestamp = strtotime((string) $row[$kolomTanggal]);
                    if ($timestamp !== false) {
                        $tanggalDipakai = $timestamp;
                        break;
                    }
                }
            }

            // Jika tanggal ditemukan, hitung peminjaman per bulan
            if ($tanggalDipakai !== null) {
                $bulanIndex = (int) date('n', $tanggalDipakai) - 1; // Bulan (0-11)
                if (isset($chartAreaData[$bulanIndex])) {
                    $chartAreaData[$bulanIndex]++; // Increment peminjaman untuk bulan tersebut
                }
            }

            // Cari kelas dari kolom yang tersedia
            $kelasDipakai = '';
            foreach ($kolomKelasKandidat as $kolomKelas) {
                if (!empty($row[$kolomKelas])) {
                    $kelasDipakai = trim((string) $row[$kolomKelas]);
                    break;
                }
            }

            // Jika tidak ada kelas, beri label default
            if ($kelasDipakai === '') {
                $kelasDipakai = 'Tanpa Kelas';
            }

            // Hitung jumlah peminjaman per kelas
            if (!isset($classCount[$kelasDipakai])) {
                $classCount[$kelasDipakai] = 0;
            }
            $classCount[$kelasDipakai]++;
        }
    }

    // Jika ada data peminjaman per kelas, gunakan untuk chart pie
    if (!empty($classCount)) {
        arsort($classCount); // Urutkan descending (terbanyak di depan)
        $classCount = array_slice($classCount, 0, 5, true); // Ambil 5 terbesar
        $chartPieLabels = array_keys($classCount);
        $chartPieData = array_values($classCount);
    }
}
?>
<!-- Earnings (Monthly) Card Example -->
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Total lab</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM lab")); ?></div>

                </div>
                <div class="col-auto">
                    <i class="fas fa-tv "></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Earnings (Monthly) Card Example -->
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-success shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Inventaris </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM inventaris")); ?></div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-book-open"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Earnings (Monthly) Card Example -->
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-info shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                        Total Barang</div>

                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"></div><?php echo mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM barang")); ?>
                </div>

                <div class="col-auto">
                    <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Pending Requests Card Example -->
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-warning shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                        Total User</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"></div><?php echo mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM user")); ?>

                </div>
                <div class="col-auto">
                    <i class="fas fa-comments fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Area Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Grafik Peminjaman</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="myAreaChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie Chart -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Aktivitas peminjaman</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="myPieChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <?php
                    $legendColors = ['text-primary', 'text-success', 'text-info', 'text-warning', 'text-danger'];
                    foreach ($chartPieLabels as $i => $label) :
                        $kelasColor = $legendColors[$i % count($legendColors)];
                    ?>
                        <span class="mr-2">
                            <i class="fas fa-circle <?php echo $kelasColor; ?>"></i> <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Total Siswa Card -->
    <div class="col-lg-4 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="text-success font-weight-bold text-uppercase mb-1">
                    Total Siswa
                </div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                    <?php echo number_format($totalSiswa); ?> siswa
                </div>
                <div class="mt-2 text-gray-600 small">
                    Data langsung dari tabel siswa
                </div>
            </div>
        </div>
    </div>
    
    <!-- Siswa Distribution Chart -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Distribusi Siswa per Kelas</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="siswaPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.dashboardChartData = {
    areaLabels: <?php echo json_encode($chartAreaLabels); ?>,
    areaData: <?php echo json_encode(array_map('intval', $chartAreaData)); ?>,
    pieLabels: <?php echo json_encode($chartPieLabels); ?>,
    pieData: <?php echo json_encode(array_map('intval', $chartPieData)); ?>,
    siswaLabels: <?php echo json_encode(array_keys($siswaByKelas ?: ['Belum Ada Data'])); ?>,
    siswaData: <?php echo json_encode(array_values($siswaByKelas ?: [1])); ?>
};

// Render siswa distribution chart
const ctxSiswa = document.getElementById('siswaPieChart');
if (ctxSiswa) {
    new Chart(ctxSiswa, {
        type: 'doughnut',
        data: {
            labels: window.dashboardChartData.siswaLabels,
            datasets: [{
                data: window.dashboardChartData.siswaData,
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'],
                hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#d4b21c', '#be2617', '#6e7080'],
                hoverBorderColor: 'rgba(234, 236, 244, 1)',
            }],
        },
        options: {
            maintainAspectRatio: false,
            legend: {
                display: true,
                position: 'bottom',
            },
            tooltips: {
                backgroundColor: 'rgb(255,255,255)',
                bodyFontColor: '#858796',
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                caretPadding: 10,
            },
        },
    });
}
</script>
