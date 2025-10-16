<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsstaffid']) == 0) {
    header('location:logout.php');
    exit;
} else {
    // Code for deletion
    if (isset($_GET['delid'])) {
        $rid = intval($_GET['delid']);
        $sql = "DELETE FROM tblnotice WHERE ID = :rid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':rid', $rid, PDO::PARAM_INT);
        $query->execute();
        echo "<script>if(window.showToast) showToast('Notice deleted successfully.','success');</script>";
        echo "<script>window.location.href = 'manage-notice.php'</script>";
    }

    // Search functionality
    $searchdata = '';
    if (isset($_POST['search'])) {
        $searchdata = $_POST['searchdata'];
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <title>Student Profiling System || Manage Notice</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
        <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
        <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
        <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="./css/style(v2).css">
    </head>

    <body>
        <div class="container-scroller">
            <?php include_once('includes/header.php'); ?>
            <div class="container-fluid page-body-wrapper">
                <?php include_once('includes/sidebar.php'); ?>
                <div class="main-panel">
                    <div class="content-wrapper">
                        <div class="page-header">
                            <h3 class="page-title"> Manage Notice </h3>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page"> Manage Notice</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="row">
                            <div class="col-md-12 grid-margin stretch-card">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-sm-flex align-items-center mb-4">
                                            <h4 class="card-title mb-sm-0">Manage Notice</h4>
                                            <form method="post" class="ml-auto">
                                                <input type="text" name="searchdata" class="form-control"
                                                    placeholder="Search by Notice Title"
                                                    value="<?php echo htmlentities($searchdata); ?>"
                                                    style="display: inline-block; width: auto;">
                                                <button type="submit" name="search" class="btn btn-primary">Search</button>
                                            </form>
                                        </div>
                                        <div class="table-responsive border rounded p-1">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th class="font-weight-bold">S.No</th>
                                                        <th class="font-weight-bold">Notice Title</th>
                                                        <th class="font-weight-bold">Notice Date</th>
                                                        <th class="font-weight-bold">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $sql = "SELECT NoticeTitle, CreationDate, ID as nid FROM tblnotice";
                                                    if (!empty($searchdata)) {
                                                        $sql .= " WHERE NoticeTitle LIKE :searchdata";
                                                    }
                                                    $query = $dbh->prepare($sql);
                                                    if (!empty($searchdata)) {
                                                        $query->bindValue(':searchdata', '%' . $searchdata . '%', PDO::PARAM_STR);
                                                    }
                                                    $query->execute();
                                                    $results = $query->fetchAll(PDO::FETCH_OBJ);

                                                    $cnt = 1;
                                                    if ($query->rowCount() > 0) {
                                                        foreach ($results as $row) { ?>
                                                            <tr>
                                                                <td><?php echo htmlentities($cnt); ?></td>
                                                                <td><?php echo htmlentities($row->NoticeTitle); ?></td>
                                                                <td><?php echo htmlentities($row->CreationDate); ?></td>
                                                                <td>
                                                                    <div>
                                                                        <a href="edit-notice-detail.php?editid=<?php echo htmlentities($row->nid); ?>"
                                                                            class="btn btn-info btn-xs" target="_blank">Edit</a>
                                                                        <a href="manage-notice.php?delid=<?php echo htmlentities($row->nid); ?>"
                                                                            onclick="return confirm('Do you really want to delete?');"
                                                                            class="btn btn-danger btn-xs">Delete</a>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <?php $cnt++;
                                                        }
                                                    } else { ?>
                                                        <tr>
                                                            <td colspan="4" style="text-align: center; color: red;">No Record
                                                                Found</td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
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