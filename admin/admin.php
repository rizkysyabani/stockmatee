<?php
require "../function.php"; //
require "../cek.php"; //

// Pastikan hanya role adminPusat (atau owner) yang bisa akses
if (!isset($_SESSION['roles']) || ($_SESSION['roles'] != 'adminPusat' && $_SESSION['roles'] != 'owner')) {
     header('location: ../index.php');
     exit;
}


// Gunakan username atau email dari session
$identifier = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : (isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'Admin'); //
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Kelola Pengguna - Admin Pusat</title> 
    <link href="../css/styles.css" rel="stylesheet" /> 
    <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" /> 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script> 
</head>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark" style="background-color:#003940;">
        <a class="navbar-brand" href="index.php">Stock <sup>Mate</sup></a>
        <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>
         <nav class="sb-topnav navbar navbar-expand navbar-dark" style="background-color: #003940; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <a class="navbar-brand" href="index.php" style="font-weight: bold; letter-spacing: 1px;">
                Stock <sup style="color: #80DEEA; font-weight: normal;">Mate</sup>
            </a>
            <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#" style="color: #ffffff; transition: transform 0.3s ease;">
                <i class="fas fa-bars fa-lg"></i>  
            </button>
        </nav>
    </nav>
    <div id="layoutSidenav">
        <?php require '_sidebar.php'; // Include sidebar utama admin ?> 
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Kelola Admin Baru</h1> 
                     <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Kelola Admin Baru</li>
                    </ol> 
                    <div class="card mb-4">
                        <div class="card-header">
                          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                            <i class="fas fa-user-plus"></i> Tambah Pengguna Baru
                          </button> 
                        </div>
                        <div class="card-body">
                           <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Aksi</th>
                                    </tr>
                                    </thead> 
                                    <tbody>
                                      <?php
                                    $ambilsemuadataadmin = mysqli_query($conn, "SELECT * FROM login ORDER BY roles, username"); // Urutkan berdasarkan role, lalu username //
                                     $i = 1; //
                                    while($data=mysqli_fetch_array($ambilsemuadataadmin)){ //
                                        $usernameAdmin = htmlspecialchars($data['username']); //
                                        $iduser = $data['iduser']; //
                                        $roles = htmlspecialchars($data['roles']); //
                                        $current_session_username = isset($_SESSION['username']) ? $_SESSION['username'] : null; // Ambil username sesi saat ini //
                                    ?>
                                    <tr>
                                        <td><?=$i++;?></td>
                                        <td><?=$usernameAdmin;?></td>
                                        <td><?=ucfirst($roles);?></td> <td> 
                                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#edit<?=$iduser;?>"> <i class="fas fa-edit"></i> Edit</button>
                                            <?php if($current_session_username != $usernameAdmin) : // Jangan biarkan user menghapus dirinya sendiri ?>
                                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#delete<?=$iduser;?>"> <i class="fas fa-trash"></i> Delete</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                     <div class="modal fade" id="edit<?=$iduser;?>">
                                        <div class="modal-dialog">
                                          <div class="modal-content">
                                            <div class="modal-header">
                                              <h4 class="modal-title">Edit Pengguna</h4> 
                                              <button type="button" class="close" data-dismiss="modal">&times;</button> 
                                            </div>
                                            <form method="post" action="../function.php"> <div class="modal-body"> 
                                              <div class="form-group">
                                                <label for="editUsername<?=$iduser;?>">Username:</label> 
                                                <input type="text" id="editUsername<?=$iduser;?>" name="usernameadmin" value="<?=$usernameAdmin;?>" class="form-control" required> 
                                              </div>
                                              <div class="form-group">
                                                <label for="editPassword<?=$iduser;?>">Password Baru:</label> 
                                                <input type="password" id="editPassword<?=$iduser;?>" name="passwordbaru" class="form-control" placeholder="Kosongkan jika tidak diubah"> 
                                              </div>
                                              <div class="form-group">
                                                 <label for="editRoles<?=$iduser;?>">Role:</label> 
                                                  <select id="editRoles<?=$iduser;?>" name="rolesbaru" class="form-control" required>
                                                      <option value="">-- Pilih Role --</option>
                                                      <option value="admin" <?= ($roles == 'admin') ? 'selected' : ''; ?>>Admin </option>
                                                      <option value="karyawan" <?= ($roles == 'karyawan') ? 'selected' : ''; ?>>Karyawan </option>
                                                      </select>
                                              </div>
                                              <input type="hidden" name="id" value="<?=$iduser;?>"> 
                                              <button type="submit" class="btn btn-primary" name="updateadmin">Update</button> 
                                            </div>
                                            </form>
                                          </div>
                                        </div>
                                      </div>

                                    <div class="modal fade" id="delete<?=$iduser;?>">
                                        <div class="modal-dialog">
                                          <div class="modal-content">
                                            <div class="modal-header">
                                              <h4 class="modal-title">Hapus Pengguna?</h4> 
                                              <button type="button" class="close" data-dismiss="modal">&times;</button> 
                                            </div>
                                            <form method="post" action="../function.php"> <div class="modal-body"> 
                                             Apakah anda yakin ingin menghapus pengguna <strong><?=$usernameAdmin;?></strong>? 
                                              <input type="hidden" name="id" value="<?=$iduser;?>"> 
                                              <br><br> 
                                              <button type="submit" class="btn btn-danger" name="hapusadmin">Hapus</button> 
                                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button> 
                                            </div>
                                            </form>
                                          </div>
                                        </div>
                                      </div>
                                    <?php }; ?> 
                                </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </main>
                 <footer class="py-4 bg-light mt-auto">
                     <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                             <div class="text-muted">Copyright &copy; StokMate 2025</div>
                            <div>
                                <a href="#">Privacy Policy</a>
                                &middot;
                                <a href="#">Terms &amp; Conditions</a>
                            </div>
                        </div>
                    </div>
                </footer> 
            </div>
        </div>

         <div class="modal fade" id="myModal">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title">Tambah Pengguna Cabang   Baru</h4> 
                  <button type="button" class="close" data-dismiss="modal">&times;</button> 
                </div>
                <form method="post" action="../function.php"> <div class="modal-body"> 
                  <div class="form-group">
                      <label for="addUsername">Username:</label> 
                      <input type="text" id="addUsername" name="username" placeholder="Masukkan Username" class="form-control" required> 
                  </div>
                  <div class="form-group">
                      <label for="addPassword">Password:</label> 
                      <input type="password" id="addPassword" name="password" placeholder="Masukkan Password" class="form-control" required> 
                  </div>
                   <div class="form-group">
                      <label for="addRoles">Role:</label> 
                      <select id="addRoles" name="roles" class="form-control" required>
                           <option value="">-- Pilih Role --</option>
                           <option value="admin">Admin</option>
                           <option value="karyawan">Karyawan</option>
                           </select>
                      </div>
                  <button type="submit" class="btn btn-primary" name="addadmin">Submit</button> 
                </div>
                </form>
              </div>
            </div>
          </div>

        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script> 
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script> 
        <script src="../js/scripts.js"></script> 
        <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script> 
        <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script> 
        <script> $(document).ready(function() { $('#dataTable').DataTable({ stateSave: true }); }); </script> 
    </body>
</html>