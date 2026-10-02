<?php
require "../function.php";
require "../cek.php";

// USER IDENTIFICATION AND ROLE CHECK
$username_session = $_SESSION['username'] ?? '';
$role_session = $_SESSION['roles'] ?? '';

$specific_admin_usernames = ['adminPusat', 'adminParahita', 'adminSerpong', 'owner'];
if ($role_session != 'admin' || in_array($username_session, $specific_admin_usernames) || empty($username_session)) {
    header('location: ../index.php');
    exit;
}

$username_display = htmlspecialchars($username_session);
$store_name_display = "Toko " . $username_display;
$store_prefix = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $username_session));

if (empty($store_prefix)) {
    // Handle error, this should not happen if username is valid
    echo "Error: Konfigurasi toko tidak valid untuk export.";
    exit;
}
?>
<html>
<head>
  <title>Export Stock Barang - <?= htmlspecialchars($store_name_display); ?></title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.dataTables.min.css">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
  <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
</head>

<body>
<div class="container">
            <h2>Stock Barang <?= htmlspecialchars($store_name_display); ?></h2>
			<h4>(Inventory)</h4>
				<div class="data-tables datatable-dark table-responsive">
					<table class="table table-bordered" id="mauexport" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Barang</th>
                                <th>Deskripsi</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $ambilsemuadatastock = mysqli_query($conn, "SELECT * FROM `{$store_prefix}_stock`");
                             $i = 1;
                            if ($ambilsemuadatastock) {
                                while($data=mysqli_fetch_array($ambilsemuadatastock)){
                                    $namabarang = htmlspecialchars($data['namabarang']);
                                    $deskripsi = htmlspecialchars($data['deskripsi']);
                                    $stock = htmlspecialchars($data['stock']);
                            ?>
                            <tr>
                                <td><?=$i++;?></td>
                                <td><?=$namabarang;?></td>
                                <td><?=$deskripsi;?></td>
                                <td><?=$stock;?></td>
                            </tr>
                            <?php 
                                } // end while
                            } else {
                                echo "<tr><td colspan='4' class='text-center'>Gagal mengambil data atau belum ada stok.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
				</div>
</div>

<script>
$(document).ready(function() {
    $('#mauexport').DataTable( {
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    } );
} );
</script>

<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.print.min.js"></script>

</body>
</html>