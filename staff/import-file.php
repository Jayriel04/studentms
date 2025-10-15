<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
error_reporting(0);
include_once('includes/dbconnection.php');

require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['import'])) {
    $allowed_file_types = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    $file_type = $_FILES['import_file']['type'];

    if (!in_array($file_type, $allowed_file_types)) {
        $_SESSION['import_status_error'] = "Invalid file type. Please upload a CSV or Excel file.";
        header("Location: import-file.php");
        exit();
    }

    $fileName = $_FILES['import_file']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($fileName);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $successful_imports = 0;
        $failed_imports = 0;
        $skipped_duplicates = 0;
        $defaultPassword = "student123";
        $hashedPassword = md5($defaultPassword);

        // Remove header row
        $header = array_shift($sheetData);

        foreach ($sheetData as $row_num => $row) {
            // Assign columns to variables
            $stuID = $row['A'] ?? '';
            $familyName = $row['B'] ?? '';
            $firstName = $row['C'] ?? '';
            $middleName = $row['D'] ?? '';
            $program = $row['E'] ?? '';
            $major = $row['F'] ?? '';
            $lrn = $row['G'] ?? '';
            $dob = $row['H'] ?? '';
            $pob = $row['I'] ?? '';
            $gender = $row['J'] ?? '';
            $civilStatus = $row['K'] ?? '';
            $religion = $row['L'] ?? '';
            $height = $row['M'] ?? '';
            $weight = $row['N'] ?? '';
            $citizenship = $row['O'] ?? '';
            $fathersName = $row['P'] ?? '';
            $mothersMaidenName = $row['Q'] ?? '';
            $buildingHouseNumber = $row['R'] ?? '';
            $streetName = $row['S'] ?? '';
            $barangay = $row['T'] ?? '';
            $cityMunicipality = $row['U'] ?? '';
            $province = $row['V'] ?? '';
            $postalCode = $row['W'] ?? '';
            $contactNumber = $row['X'] ?? '';
            $emailAddress = $row['Y'] ?? '';
            $emergencyContactPerson = $row['Z'] ?? '';
            $emergencyRelationship = $row['AA'] ?? '';
            $emergencyContactNumber = $row['AB'] ?? '';
            $emergencyAddress = $row['AC'] ?? '';
            $category = $row['AD'] ?? '';
            $yearLevel = $row['AE'] ?? '';

            if (empty($stuID)) {
                $failed_imports++;
                continue; // Skip rows without a student ID
            }

            // Check for duplicates
            $check_query = $dbh->prepare("SELECT StuID FROM tblstudent WHERE StuID = :stuid");
            $check_query->bindParam(':stuid', $stuID, PDO::PARAM_STR);
            $check_query->execute();

            if ($check_query->rowCount() === 0) {
                // Insert new student
                $sql = "INSERT INTO tblstudent (StuID, FamilyName, FirstName, MiddleName, Program, Major, LearnersReferenceNo, DOB, PlaceOfBirth, Gender, CivilStatus, Religion, Height, Weight, Citizenship, FathersName, MothersMaidenName, BuildingHouseNumber, StreetName, Barangay, CityMunicipality, Province, PostalCode, ContactNumber, EmailAddress, EmergencyContactPerson, EmergencyRelationship, EmergencyContactNumber, EmergencyAddress, Category, YearLevel, Password) VALUES (:stuid, :fname, :firstname, :mname, :program, :major, :lrn, :dob, :pob, :gender, :civilstatus, :religion, :height, :weight, :citizenship, :fathersname, :mothersname, :house, :street, :brgy, :city, :province, :postal, :contact, :email, :econtactperson, :erelationship, :econtactnum, :eaddress, :category, :yearlevel, :password)";
                $query = $dbh->prepare($sql);

                $query->bindParam(':stuid', $stuID, PDO::PARAM_STR);
                $query->bindParam(':fname', $familyName, PDO::PARAM_STR);
                $query->bindParam(':firstname', $firstName, PDO::PARAM_STR);
                $query->bindParam(':mname', $middleName, PDO::PARAM_STR);
                $query->bindParam(':program', $program, PDO::PARAM_STR);
                $query->bindParam(':major', $major, PDO::PARAM_STR);
                $query->bindParam(':lrn', $lrn, PDO::PARAM_STR);
                $query->bindParam(':dob', $dob, PDO::PARAM_STR);
                $query->bindParam(':pob', $pob, PDO::PARAM_STR);
                $query->bindParam(':gender', $gender, PDO::PARAM_STR);
                $query->bindParam(':civilstatus', $civilStatus, PDO::PARAM_STR);
                $query->bindParam(':religion', $religion, PDO::PARAM_STR);
                $query->bindParam(':height', $height, PDO::PARAM_STR);
                $query->bindParam(':weight', $weight, PDO::PARAM_STR);
                $query->bindParam(':citizenship', $citizenship, PDO::PARAM_STR);
                $query->bindParam(':fathersname', $fathersName, PDO::PARAM_STR);
                $query->bindParam(':mothersname', $mothersMaidenName, PDO::PARAM_STR);
                $query->bindParam(':house', $buildingHouseNumber, PDO::PARAM_STR);
                $query->bindParam(':street', $streetName, PDO::PARAM_STR);
                $query->bindParam(':brgy', $barangay, PDO::PARAM_STR);
                $query->bindParam(':city', $cityMunicipality, PDO::PARAM_STR);
                $query->bindParam(':province', $province, PDO::PARAM_STR);
                $query->bindParam(':postal', $postalCode, PDO::PARAM_STR);
                $query->bindParam(':contact', $contactNumber, PDO::PARAM_STR);
                $query->bindParam(':email', $emailAddress, PDO::PARAM_STR);
                $query->bindParam(':econtactperson', $emergencyContactPerson, PDO::PARAM_STR);
                $query->bindParam(':erelationship', $emergencyRelationship, PDO::PARAM_STR);
                $query->bindParam(':econtactnum', $emergencyContactNumber, PDO::PARAM_STR);
                $query->bindParam(':eaddress', $emergencyAddress, PDO::PARAM_STR);
                $query->bindParam(':category', $category, PDO::PARAM_STR);
                $query->bindParam(':yearlevel', $yearLevel, PDO::PARAM_STR);
                $query->bindParam(':password', $hashedPassword, PDO::PARAM_STR); // Automatically set default password

                if ($query->execute()) {
                    $successful_imports++;
                } else {
                    $failed_imports++;
                }
            } else {
                $skipped_duplicates++;
            }
        }

        $message = "Import complete! <br>";
        $message .= "Successfully imported: <strong>{$successful_imports}</strong><br>";
        if ($skipped_duplicates > 0) {
            $message .= "Skipped (duplicates): <strong>{$skipped_duplicates}</strong><br>";
        }
        if ($failed_imports > 0) {
            $message .= "Failed rows: <strong>{$failed_imports}</strong>";
        }
        $_SESSION['import_status_success'] = $message;
        header("Location: import-file.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['import_status_error'] = "Error processing file: " . $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Student Profiling System || Import Students</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style(v2).css">
</head>

<body>
    <div class="container-scroller">
        <?php include_once('includes/header.php'); ?>
        <div class="container-fluid page-body-wrapper">
            <?php include_once('includes/sidebar.php'); ?>
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="page-header">
                        <h3 class="page-title"> Import Students </h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="manage-students.php">Manage Students</a></li>
                                <li class="breadcrumb-item active" aria-current="page"> Import Students</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mx-auto">
                            <div class="card auth-form-light">
                                <div class="card-body">
                                    <h4 class="card-title text-center mb-4">Import Students from File</h4>

                                    <?php if (isset($_SESSION['import_status_success'])): ?>
                                        <div class="alert alert-success" role="alert">
                                            <?php echo $_SESSION['import_status_success'];
                                            unset($_SESSION['import_status_success']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (isset($_SESSION['import_status_error'])): ?>
                                        <div class="alert alert-danger" role="alert">
                                            <?php echo $_SESSION['import_status_error'];
                                            unset($_SESSION['import_status_error']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <form class="forms-sample" method="POST" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="import_file">Select File</label>
                                            <p class="card-description">Upload a CSV or Excel file to import student
                                                data.</p>
                                            <input type="file" name="import_file" id="import_file"
                                                class="form-control-file" required
                                                accept=".csv, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                                        </div>
                                        <div class="mt-3">
                                            <button type="submit"
                                                class="btn btn-block btn-success btn-lg font-weight-medium auth-form-btn loginbtn"
                                                name="import">Import Students</button>
                                        </div>
                                        <div class="text-center mt-4 font-weight-light">
                                            <a href="manage-students.php" class="btn btn-light">Back</a>
                                        </div>
                                    </form>
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