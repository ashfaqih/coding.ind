<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('config.php');

// Mengambil Total Stok Per Buah
$query_stock = "SELECT t1.nama_buah, t1.stok_akhir 
                FROM transaksi t1
                INNER JOIN (
                    SELECT nama_buah, MAX(id) as max_id
                    FROM transaksi
                    GROUP BY nama_buah
                ) t2 ON t1.nama_buah = t2.nama_buah 
                AND t1.id = t2.max_id
                GROUP BY t1.nama_buah";
$result_stock = mysqli_query($conn, $query_stock);

// Mengambil Data Stok Masuk (dengan mempertimbangkan ID terbaru)
$query_incoming = "SELECT DATE(t1.tanggal_masuk) as date, SUM(t1.stok_masuk) as total_masuk 
                  FROM transaksi t1
                  WHERE MONTH(t1.tanggal_masuk) = MONTH(CURRENT_DATE())
                  GROUP BY DATE(t1.tanggal_masuk)";
$result_incoming = mysqli_query($conn, $query_incoming);

// Mengambil Data Stok Keluar (dengan mempertimbangkan ID terbaru)
$query_outgoing = "SELECT DATE(t1.tanggal_masuk) as date, SUM(t1.stok_keluar) as total_keluar 
                  FROM transaksi t1
                  WHERE MONTH(t1.tanggal_masuk) = MONTH(CURRENT_DATE())
                  GROUP BY DATE(t1.tanggal_masuk)";
$result_outgoing = mysqli_query($conn, $query_outgoing);

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
    <title>Dashboard - Gudang Buah</title>
    
    <!-- Montserrat Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    
    <!-- ApexCharts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.35.5/apexcharts.min.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    
    <!-- Additional Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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
                        <?php 
                        if ($notification_count > 0) {
                            mysqli_data_seek($result_notifications, 0);
                            while ($row = mysqli_fetch_assoc($result_notifications)) { 
                        ?>
                            <p><?php echo $row['nama_buah']; ?> (<?php echo $row['stok_akhir']; ?> unit)</p>
                        <?php 
                            }
                        } else {
                        ?>
                            <p>Tidak ada notifikasi</p>
                        <?php
                        }
                        ?>
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
                    <a href="table.php">
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
                <h2>DASHBOARD</h2>
            </div>

            <div class="main-cards">
                <div class="card">
                    <div class="card-inner">
                        <h3>TOTAL STOK</h3>
                        <span class="material-icons-outlined">inventory_2</span>
                    </div>
                    <h1><?php
                        mysqli_data_seek($result_stock, 0);
                        $total_stock = 0;
                        while ($row = mysqli_fetch_assoc($result_stock)) {
                            $total_stock += $row['stok_akhir'];
                        }
                        echo $total_stock;
                    ?></h1>
                </div>

                <div class="card">
                    <div class="card-inner">
                        <h3>JENIS BUAH</h3>
                        <span class="material-icons-outlined">category</span>
                    </div>
                    <h1><?php
                        mysqli_data_seek($result_stock, 0);
                        echo mysqli_num_rows($result_stock);
                    ?></h1>
                </div>

                <div class="card">
                    <div class="card-inner">
                        <h3>STOK MASUK</h3>
                        <span class="material-icons-outlined">add_shopping_cart</span>
                    </div>
                    <h1><?php
                        mysqli_data_seek($result_incoming, 0);
                        $total_incoming = 0;
                        while ($row = mysqli_fetch_assoc($result_incoming)) {
                            $total_incoming += $row['total_masuk'];
                        }
                        echo $total_incoming;
                    ?></h1>
                </div>

                <div class="card">
                    <div class="card-inner">
                        <h3>STOK KELUAR</h3>
                        <span class="material-icons-outlined">remove_shopping_cart</span>
                    </div>
                    <h1><?php
                        mysqli_data_seek($result_outgoing, 0);
                        $total_outgoing = 0;
                        while ($row = mysqli_fetch_assoc($result_outgoing)) {
                            $total_outgoing += $row['total_keluar'];
                        }
                        echo $total_outgoing;
                    ?></h1>
                </div>
            </div>

            <div class="charts">
                <div class="charts-card">
                    <h2 class="chart-title">Stok Buah</h2>
                    <div id="stock-chart"></div>
                </div>

                <div class="charts-card">
                    <h2 class="chart-title">Stok Masuk & Keluar</h2>
                    <div id="movement-chart"></div>
                </div>
            </div>
            
            <div class="download-section">
                <button onclick="downloadPDF()">
                    <span class="material-icons-outlined">download</span>
                    Download as PDF
                </button>
            </div>
        </main>
    </div>
    <script>
        const stockData = <?php
            mysqli_data_seek($result_stock, 0);
            $stock_data = array();
            while ($row = mysqli_fetch_assoc($result_stock)) {
                $stock_data[] = array(
                    'name' => $row['nama_buah'],
                    'data' => intval($row['stok_akhir'])
                );
            }
            echo json_encode($stock_data);
        ?>;

        const movementData = {
            dates: [],
            incoming: [],
            outgoing: []
        };

        <?php
        mysqli_data_seek($result_incoming, 0);
        while ($row = mysqli_fetch_assoc($result_incoming)) {
            echo "movementData.dates.push('".$row['date']."');\n";
            echo "movementData.incoming.push(".$row['total_masuk'].");\n";
        }

        mysqli_data_seek($result_outgoing, 0);
        while ($row = mysqli_fetch_assoc($result_outgoing)) {
            echo "movementData.outgoing.push(".$row['total_keluar'].");\n";
        }
        ?>
    </script>
    <script src="js/script.js"></script>
</body>
</html>