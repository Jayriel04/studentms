<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsaid'] == 0)) {
  header('location:logout.php');
} else {
  // Code for deletion
  if (isset($_GET['delid'])) {
    $rid = intval($_GET['delid']);
    $sql = "DELETE FROM tblstudent WHERE ID=:rid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':rid', $rid, PDO::PARAM_STR);
    $query->execute();
    echo "<script>alert('Data deleted');</script>";
    echo "<script>window.location.href = 'manage-students.php'</script>";
  }
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Student Management System || Search Students</title>
  <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
  <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="./css/style.css">
</head>

<body>
  <div class="container-scroller">
    <?php include_once('includes/header.php'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php include_once('includes/sidebar.php'); ?>
      <div class="main-panel">
        <div class="content-wrapper">
          <div class="page-header">
            <h3 class="page-title"> Search Student </h3>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page"> Search Student</li>
              </ol>
            </nav>
          </div>
          <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <form method="post">
                    <div class="form-group">
                      <strong>Search Student:</strong>
                      <input id="searchdata" type="text" name="searchdata" required="true" class="form-control"
                        placeholder="Search by Student ID, Family Name, or First Name">
                    </div>
                    <button type="submit" class="btn btn-primary" name="search" id="submit">Search</button>
                  </form>
                  <div class="d-sm-flex align-items-center mb-4">
                    <?php
                    if (isset($_POST['search'])) {
                      $sdata = $_POST['searchdata'];
                    ?>
                      <hr />
                      <h4 align="center">Result against "<?php echo $sdata; ?>" keyword </h4>
                  </div>
                  <div class="table-responsive border rounded p-1">
                    <table class="table">
                      <thead>
                        <tr>
                          <th class="font-weight-bold">S.No</th>
                          <th class="font-weight-bold">Student ID</th>
                          <th class="font-weight-bold">Family Name</th>
                          <th class="font-weight-bold">First Name</th>
                          <th class="font-weight-bold">Program</th>
                          <th class="font-weight-bold">Contact Number</th>
                          <th class="font-weight-bold">Email Address</th>
                          <th class="font-weight-bold">Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        if (isset($_GET['pageno'])) {
                          $pageno = $_GET['pageno'];
                        } else {
                          $pageno = 1;
                        }
                        // Formula for pagination
                        $no_of_records_per_page = 5;
                        $offset = ($pageno - 1) * $no_of_records_per_page;
                        $ret = "SELECT ID FROM tblstudent";
                        $query1 = $dbh->prepare($ret);
                        $query1->execute();
                        $results1 = $query1->fetchAll(PDO::FETCH_OBJ);
                        $total_rows = $query1->rowCount();
                        $total_pages = ceil($total_rows / $no_of_records_per_page);
                        $sql = "SELECT ID as sid, StuID, FamilyName, FirstName, Program, ContactNumber, EmailAddress 
                                FROM tblstudent 
                                WHERE StuID LIKE :sdata OR FamilyName LIKE :sdata OR FirstName LIKE :sdata 
                                LIMIT $offset, $no_of_records_per_page";
                        $query = $dbh->prepare($sql);
                        $query->bindValue(':sdata', '%' . $sdata . '%', PDO::PARAM_STR);
                        $query->execute();
                        $results = $query->fetchAll(PDO::FETCH_OBJ);

                        $cnt = 1;
                        if ($query->rowCount() > 0) {
                          foreach ($results as $row) { ?>
                            <tr>
                              <td><?php echo htmlentities($cnt); ?></td>
                              <td><?php echo htmlentities($row->StuID); ?></td>
                              <td><?php echo htmlentities($row->FamilyName); ?></td>
                              <td><?php echo htmlentities($row->FirstName); ?></td>
                              <td><?php echo htmlentities($row->Program); ?></td>
                              <td><?php echo htmlentities($row->ContactNumber); ?></td>
                              <td><?php echo htmlentities($row->EmailAddress); ?></td>
                              <td>
                                <div>
                                  <a href="edit-student-detail.php?editid=<?php echo htmlentities($row->sid); ?>" class="btn btn-info btn-xs" target="blank">Edit</a>
                                  <a href="search.php?delid=<?php echo htmlentities($row->sid); ?>" onclick="return confirm('Do you really want to Delete ?');" class="btn btn-danger btn-xs">Delete</a>
                                </div>
                              </td>
                            </tr>
                        <?php
                            $cnt++;
                          }
                        } else { ?>
                          <tr>
                            <td colspan="8" style="text-align: center;">No record found against this search</td>
                          </tr>
                        <?php }
                      } ?>
                      </tbody>
                    </table>
                  </div>
                  <div align="left" class="mt-4">
                    <ul class="pagination">
                      <li><a href="?pageno=1"><strong>First</strong></a></li>
                      <li class="<?php if ($pageno <= 1) {
                                    echo 'disabled';
                                  } ?>">
                        <a href="<?php if ($pageno <= 1) {
                                    echo '#';
                                  } else {
                                    echo "?pageno=" . ($pageno - 1);
                                  } ?>"><strong style="padding-left: 10px">Prev</strong></a>
                      </li>
                      <li class="<?php if ($pageno >= $total_pages) {
                                    echo 'disabled';
                                  } ?>">
                        <a href="<?php if ($pageno >= $total_pages) {
                                    echo '#';
                                  } else {
                                    echo "?pageno=" . ($pageno + 1);
                                  } ?>"><strong style="padding-left: 10px">Next</strong></a>
                      </li>
                      <li><a href="?pageno=<?php echo $total_pages; ?>"><strong style="padding-left: 10px">Last</strong></a></li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php include_once('includes/footer.php'); ?>
      </div>
    </div>
  </div>
  <script src="vendors/js/vendor.bundle.base.js"></script>
  <script src="js/off-canvas.js"></script>
  <script src="js/misc.js"></script>
</body>

</html>
<?php } ?>