<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', '1');

include_once(__DIR__ . '/includes/dbconnection.php');

// Include PhpSpreadsheet autoload file (use absolute path so includes work from admin/)
require_once __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

if (isset($_POST['import'])) {
    // Basic upload validation
    if (!isset($_FILES['import_file'])) {
        $_SESSION['import_status_error'] = "No file uploaded.";
        header("Location: import-file.php");
        exit();
    }

    if ($_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['import_status_error'] = "File upload error (code: " . $_FILES['import_file']['error'] . ").";
        header("Location: import-file.php");
        exit();
    }

    $uploadedTmp = $_FILES['import_file']['tmp_name'];
    $originalName = $_FILES['import_file']['name'];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    try {
        // Choose reader based on extension (more robust than relying on mime-type)
        if (in_array($ext, ['xlsx'])) {
            $reader = new Xlsx();
        } elseif (in_array($ext, ['xls'])) {
            $reader = new Xls();
        } elseif (in_array($ext, ['csv'])) {
            $reader = new Csv();
        } else {
            $_SESSION['import_status_error'] = "Invalid file extension. Please upload .csv, .xls or .xlsx.";
            header("Location: import-file.php");
            exit();
        }

        $spreadsheet = $reader->load($uploadedTmp);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $successful_imports = 0;
        $failed_imports = 0;
        $skipped_duplicates = 0;
        $skipped_invalid_format = 0;
        $defaultPassword = "student123";
        // Use password_hash instead of md5 for secure password storage
        $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

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

            // Validate Student ID format with optional spaces
            if (!preg_match('/^\d{3}\s*-\s*\d{5}$/', $stuID)) {
                $skipped_invalid_format++;
                continue; // Skip rows with invalid student ID format
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
        if ($skipped_invalid_format > 0) {
            $message .= "Skipped (invalid ID format): <strong>{$skipped_invalid_format}</strong><br>";
        }
        if ($failed_imports > 0) {
            $message .= "Failed rows: <strong>{$failed_imports}</strong>";
        }
        $_SESSION['import_status_success'] = $message;
        header("Location: import-file.php");
        exit();

    } catch (\Throwable $e) {
        // Log error for debugging and show friendly message
        error_log("Import file error: " . $e->getMessage());
        $_SESSION['import_status_error'] = "Error processing file: " . $e->getMessage();
        header("Location: import-file.php");
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
    <link rel="stylesheet" href="./css/style(v2).css">
    <link rel="stylesheet" href="./css/modal.css">
    <link rel="stylesheet" href="css/responsive.css">
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
                    </div>
                    <div class="row">
                        <div class="col-md-8 mx-auto">
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

                            <div class="import-card">
                                <h1 class="import-card-title">Import Students From File</h1>
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="file-section">
                                        <div class="section-label">Select File</div>
                                        <div class="section-description">Upload a CSV or Excel file to import student data.</div>

                                        <div class="file-upload-area" id="uploadArea">
                                            <div class="upload-icon">📁</div>
                                            <div class="upload-text">Click to browse or drag and drop</div>
                                            <div class="upload-subtext">CSV, XLS, XLSX files accepted</div>
                                        </div>

                                        <input type="file" 
                                               id="fileInput" 
                                               name="import_file"
                                               class="file-input" 
                                               required
                                               accept=".csv,.xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                                               onchange="handleFileSelect(event)">

                                        <div class="file-info" id="fileInfo">
                                            <div class="file-icon">📄</div>
                                            <div class="file-details">
                                                <div class="file-name" id="fileName">students.csv</div>
                                                <div class="file-size" id="fileSize">125 KB</div>
                                            </div>
                                            <button type="button" class="remove-file" onclick="removeFile()">×</button>
                                        </div>
                                    </div>

                                    <div class="supported-formats">
                                        <div class="formats-label">Supported Formats</div>
                                        <div class="format-badges">
                                            <span class="format-badge">CSV</span>
                                            <span class="format-badge">XLS</span>
                                            <span class="format-badge">XLSX</span>
                                        </div>
                                    </div>

                                    <div class="button-group">
                                        <a href="manage-students.php" class="btn btn-back">Back</a>
                                        <button type="submit" class="btn btn-import" name="import" id="importBtn" disabled>Import Students</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <script src="js/toast.js"></script>
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <script>
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const fileInfo = document.getElementById('fileInfo');
        const importBtn = document.getElementById('importBtn');
        let selectedFile = null;

        uploadArea.addEventListener('click', () => fileInput.click());

        // Drag and drop functionality
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFile(files[0]);
            }
        });

        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                handleFile(file);
            }
        }

        function handleFile(file) {
            const validTypes = ['.csv', '.xls', '.xlsx', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
            
            if (!validTypes.includes(fileExtension) && !validTypes.includes(file.type)) {
                alert('Please select a valid file format (CSV, XLS, or XLSX)');
                removeFile();
                return;
            }

            selectedFile = file;
            
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = formatFileSize(file.size);
            fileInfo.classList.add('show');
            
            importBtn.disabled = false;
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        function removeFile() {
            selectedFile = null;
            fileInput.value = '';
            fileInfo.classList.remove('show');
            importBtn.disabled = true;
        }
    </script>
</body>

</html>