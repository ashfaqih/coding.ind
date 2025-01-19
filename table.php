<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('config.php');

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Alur Search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = $search ? "WHERE nama_buah LIKE '%$search%'" : '';

// Mengambil (Get) Untuk Tabel Ringkasan
$query_summary = "SELECT 
    nama_buah,
    kategori,
    satuan,
    MAX(CASE WHEN id = (
        SELECT MAX(id) 
        FROM transaksi t2 
        WHERE t2.nama_buah = t1.nama_buah
    ) THEN stok_akhir END) as stok_terkini
FROM transaksi t1
GROUP BY nama_buah, kategori, satuan";
$result_summary = mysqli_query($conn, $query_summary);

// Mengambil (Get) Untuk Tabel Transaksi Dengan Pagination
$query_transactions = "SELECT * FROM transaksi $search_condition ORDER BY id DESC LIMIT $start, $limit";
$result_transactions = mysqli_query($conn, $query_transactions);

// Mengambil (Get) Total Halaman Untuk Paginasi
$total_records_query = "SELECT COUNT(*) as total FROM transaksi $search_condition";
$total_result = mysqli_query($conn, $total_records_query);
$total_records = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_records / $limit);

// Mengurus Penghapusan Data
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    
    // Mengambil (Get) Urutan ID Saat Ini
    $query_max_id = "SELECT MAX(id) as max_id FROM transaksi";
    $result_max_id = mysqli_query($conn, $query_max_id);
    $max_id = mysqli_fetch_assoc($result_max_id)['max_id'];
    
    // Menghapus Record
    mysqli_query($conn, "DELETE FROM transaksi WHERE id = $id");
    
    // Reset auto-increment
    if ($id == $max_id) {
        mysqli_query($conn, "ALTER TABLE transaksi AUTO_INCREMENT = $max_id");
    }
    
    header("Location: table.php");
}

// Mengambil (Get) Nama Buah Untuk Dropdown
$query_fruits = "SELECT DISTINCT nama_buah FROM transaksi";
$result_fruits = mysqli_query($conn, $query_fruits);

// Mengambil Data Untuk Notifikasi Stok Rendah
$query_notifications = "SELECT t1.nama_buah, t1.stok_akhir 
                       FROM transaksi t1
                       INNER JOIN (
                           SELECT nama_buah, MAX(id) as max_id
                           FROM transaksi
                           GROUP BY nama_buah
                       ) t2 ON t1.nama_buah = t2.nama_buah 
                       AND t1.id = t2.max_id
                       WHERE t1.stok_akhir < 20";
$result_notifications = mysqli_query($conn, $query_notifications);
$notification_count = mysqli_num_rows($result_notifications);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tabel Data - Gudang Buah</title>
        
        <!-- Montserrat Font -->
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        
        <!-- Material Icons -->
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
        
        <!-- Custom CSS -->
        <link rel="stylesheet" href="css\styles.css">
    </head>
    <body>
        <div class="grid-container">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <span>PT Hasan Agrojaya Indonesia</span>
                </div>
                <div class="header-right">
                    <div class="notification-badge <?php echo $notification_count > 0 ? 'has-notifications' : ''; ?>">
                        <span class="material-icons-outlined" id="notifButton">notifications</span>
                    </div>
                    <div id="notifPanel" class="notification-panel">
                        <div class="notification-content">
                            <h3>Stok Menipis:</h3>
                            <?php if ($notification_count > 0): ?>
                                <?php 
                                mysqli_data_seek($result_notifications, 0);
                                while ($row = mysqli_fetch_assoc($result_notifications)): 
                                ?>
                                    <p><?php echo $row['nama_buah']; ?> (<?php echo $row['stok_akhir']; ?> unit)</p>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p>Tidak ada notifikasi</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="material-icons-outlined">account_circle</span>
                    <span>Selamat Datang, <?php echo $_SESSION['username']; ?></span>
                </div>
            </header>

            <!-- Sidebar -->
            <aside id="sidebar">
                <div class="sidebar-title">
                    <div class="sidebar-brand">
                        <span class="material-icons-outlined">store</span> Gudang Buah
                    </div>
                </div>

                <ul class="sidebar-list">
                    <li class="sidebar-list-item">
                        <a href="dashboard.php">
                            <span class="material-icons-outlined">dashboard</span> Dashboard
                        </a>
                    </li>
                    <li class="sidebar-list-item">
                        <a href="table.php" class="active">
                            <span class="material-icons-outlined">table_chart</span> Tabel Data
                        </a>
                    </li>
                    <li class="sidebar-list-item">
                        <a href="https://prediksistokdanmusimbuah.streamlit.app/" target="_blank">
                            <span class="material-icons-outlined">analytics</span> Prediksi Musim Buah
                        </a>
                    </li>
                    <li class="sidebar-list-item">
                        <a href="logout.php">
                            <span class="material-icons-outlined">logout</span> Logout
                        </a>
                    </li>
                </ul>
            </aside>

            <!-- Main -->
            <main class="main-container">
                <div class="main-title">
                    <h2>TABEL DATA</h2>
                </div>

                <!-- Summary Table -->
                <div class="table-wrapper">
                    <div class="table-header">
                        <h3>Rangkuman Stok</h3>
                        <a href="download_summary_csv.php" class="download-button">
                            <span class="material-icons-outlined">download</span> 
                            Unduh CSV
                        </a>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nama Buah</th>
                                <th>Kategori</th>
                                <th>Satuan</th>
                                <th>Stok Terkini</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result_summary)): ?>
                                <tr>
                                    <td><?php echo $row['nama_buah']; ?></td>
                                    <td><?php echo $row['kategori']; ?></td>
                                    <td><?php echo $row['satuan']; ?></td>
                                    <td><?php echo $row['stok_terkini']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Transaction Table -->
                <div class="table-wrapper">
                    <div class="table-header">
                        <h3>Data Transaksi</h3>
                        <div class="table-actions">
                            <div class="search-add-wrapper">
                                <form class="search-form">
                                    <input type="text" name="search" placeholder="Cari..." 
                                           class="search-input" value="<?php echo $search; ?>">
                                    <button type="submit" class="search-button">Cari</button>
                                </form>
                                <button onclick="openAddModal()" class="add-button">Tambah Data Transaksi Baru</button>
                                <button onclick="openAddFruitModal()" class="add-button">Tambah Buah Baru</button>
                            </div>
                            <a href="download_transactions_csv.php" class="download-button">
                                <span class="material-icons-outlined">download</span> 
                                Unduh CSV
                            </a>
                        </div>
                    </div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Buah</th>
                                <th>Kategori</th>
                                <th>Satuan</th>
                                <th>Stok Awal</th>
                                <th>Stok Masuk</th>
                                <th>Stok Keluar</th>
                                <th>Stok Akhir</th>
                                <th>Tanggal Masuk</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result_transactions)): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo $row['nama_buah']; ?></td>
                                    <td><?php echo $row['kategori']; ?></td>
                                    <td><?php echo $row['satuan']; ?></td>
                                    <td><?php echo $row['stok_awal']; ?></td>
                                    <td><?php echo $row['stok_masuk']; ?></td>
                                    <td><?php echo $row['stok_keluar']; ?></td>
                                    <td><?php echo $row['stok_akhir']; ?></td>
                                    <td><?php echo $row['tanggal_masuk']; ?></td>
                                    <td>
                                        <button onclick='openEditModal(<?php echo json_encode($row); ?>)' 
                                                class="action-button edit-button">Ubah</button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="delete" 
                                                    class="action-button delete-button"
                                                    onclick="return confirm('Are you sure you want to delete this record?')">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>"
                               class="page-link <?php echo $page == $i ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
            </main>
        </div>

        <!-- Add Modal -->
        <div id="addModal" class="modal">
            <div class="modal-content">
                <h3>Tambah Data Transaksi Baru</h3>
                <form method="POST" action="add_transaction.php">
                    <div class="form-group">
                        <label>Nama Buah</label>
                        <select name="nama_buah" required>
                            <?php 
                            mysqli_data_seek($result_fruits, 0);
                            while ($fruit = mysqli_fetch_assoc($result_fruits)): 
                            ?>
                                <option value="<?php echo $fruit['nama_buah']; ?>">
                                    <?php echo $fruit['nama_buah']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Stok Masuk</label>
                        <input type="number" name="stok_masuk" required min="0">
                    </div>
                    <div class="form-group">
                        <label>Stok Keluar</label>
                        <input type="number" name="stok_keluar" required min="0">
                    </div>
                    <div class="modal-buttons">
                        <button type="button" onclick="closeAddModal()" class="modal-button cancel-button">Cancel</button>
                        <button type="submit" class="modal-button save-button">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add New Fruit Modal -->
        <div id="addFruitModal" class="modal">
            <div class="modal-content">
                <h3>Tambah Buah Baru</h3>
                <form method="POST" action="add_fruit.php">
                    <div class="form-group">
                        <label>Nama Buah</label>
                        <input type="text" name="nama_buah" required>
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategori" required>
                            <option value="buah musiman">Buah Musiman</option>
                            <option value="buah tropis">Buah Tropis</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Satuan</label>
                        <select name="satuan" required>
                            <option value="kg">Kg</option>
                            <option value="box">Box</option>
                            <option value="pcs">Pcs</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Stok Masuk</label>
                        <input type="number" name="stok_masuk" required min="0">
                    </div>
                    <div class="form-group">
                        <label>Stok Keluar</label>
                        <input type="number" name="stok_keluar" required min="0">
                    </div>
                    <div class="modal-buttons">
                        <button type="button" onclick="closeAddFruitModal()" class="modal-button cancel-button">Cancel</button>
                        <button type="submit" class="modal-button save-button">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Modal -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <h3>Mengubah Data Transaksi</h3>
                <form method="POST" action="edit_transaction.php">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>Nama Buah</label>
                        <input type="text" id="edit_nama_buah" readonly class="readonly-input">
                    </div>
                    <div class="form-group">
                        <label>Stok Masuk</label>
                        <input type="number" name="stok_masuk" id="edit_stok_masuk" required min="0">
                    </div>
                    <div class="form-group">
                        <label>Stok Keluar</label>
                        <input type="number" name="stok_keluar" id="edit_stok_keluar" required min="0">
                    </div>
                    <div class="modal-buttons">
                        <button type="button" onclick="closeEditModal()" class="modal-button cancel-button">Cancel</button>
                        <button type="submit" class="modal-button update-button">Update</button>
                    </div>
                </form>
            </div>
        </div>
    <script src="js/script.js" defer></script>
    </body>
</html>