<?php
$currentPage = 'uploadPictures'; 
include("./common/header.php"); 

if(!isset($_SESSION["isValid"])) {
    header("Location: Login.php");
    exit();
}

$albums = getAllAlbums($_SESSION["userId"]);
$errors = [];
$title = "";
$description = null;
$message = "";  // Variable to hold the success/error message

// Validate Title Function
function ValidateTitle($title) {
    $error = "";
    if (strlen(trim($title)) == 0) {
        $error = "Title is required";
    }
    return $error;
}

// Validate Album Function
function ValidateAlbum($albumId, $albums) {
    $error = "";
    // Define valid options (you can fetch these from the database if needed)
    $validOptions = [];
    foreach ($albums as $album) {
        $validOptions[] = $album['AlbumId'];
    }

    // Check if the selected value is in the valid options
    if (!in_array($albumId, $validOptions)) {
        $error = "Invalid album selected.";
    }

    return $error;
}

if (isset($_POST['submit'])) {
    // Form validation
    $title = $_POST['title'];
    $albumId = $_POST['album'];
    $description = $_POST['description'];

    // Validate title and album
    $titleError = ValidateTitle($title);
    if ($titleError) {
        $errors["title"] = $titleError;
    }

    $albumError = ValidateAlbum($albumId, $albums);
    if ($albumError) {
        $errors["album"] = $albumError;
    }

    // If no validation errors, proceed with file upload
    if (empty($errors)) {
        // Folder where uploaded files will be stored
        $uploadDir = "uploads/";

        // Check if the directory exists, if not, create it
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Loop through each uploaded file
        foreach ($_FILES['files']['name'] as $key => $fileName) {
            // Get the file's temporary name, file size, and type
            $tempName = $_FILES['files']['tmp_name'][$key];
            $fileSize = $_FILES['files']['size'][$key];
            $fileType = $_FILES['files']['type'][$key];

            // Generate a unique name for the file
            $newFileName = uniqid() . "_" . basename($fileName);

            // Define the upload path
            $uploadFilePath = $uploadDir . $newFileName;

            // File validation (optional)
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($fileType, $allowedTypes)) {
                $message = "File type not allowed. Only JPG, PNG, and GIF are allowed.";
                continue;
            }

            // Check if the file size is less than 5MB
            if ($fileSize > 5000000) {
                $message = "File size too large. Maximum size is 5MB.";
                continue;
            }

            // Move the uploaded file to the target directory
            if (move_uploaded_file($tempName, $uploadFilePath)) {
                $message = "The file " . htmlspecialchars($newFileName) . " has been uploaded successfully.";

                // After moving the file, call the function to insert the data into the database
                $uploadSuccess = uploadNewPicture($albumId, $newFileName, $title, $description);

                if ($uploadSuccess) {
                    $message .= " Picture details saved to the database.";
                } else {
                    $message .= " There was an error saving the picture details to the database.";
                }
            } else {
                $message = "Sorry, there was an error uploading the file " . htmlspecialchars($fileName) . ".";
            }
        }
    }
}
?>

<main class="container w-75">
    <h1 class="text-center my-3">Upload Pictures</h1>
    <p>Accepted picture types: JPG(JPEG), GIF and PNG</p>
    <p>You can add multiple pictures at a time by pressing the shift key while selecting pictures.</p>
    <p>When uploading multiple pictures, the title and description fields will be applied to all pictures.</p>
    
    <form action="" method="post" enctype="multipart/form-data">
        <!-- Choose Album -->
        <div class="mb-3">
            <label for="album" class="form-label">Upload to Album</label>
            <select id="album" name="album" class="form-select" required>
                <?php
                foreach ($albums as $album) {
                    echo "<option value=\"{$album['AlbumId']}\">{$album['Title']}</option>";
                }
                ?>
            </select>
            <span class="col-sm-4 text-danger my-auto">
                <?php 
                if (!empty($errors["album"])) {
                    echo $errors["album"]; 
                } 
                ?>
            </span>
        </div>        
        
        <!-- Upload Picture -->
        <div class="mb-3">
            <label for="files" class="form-label">Choose files to upload:</label>
            <input type="file" name="files[]" id="files" class="form-control" multiple accept=".jpg,.jpeg,.gif,.png">
        </div>

        
        <!-- Picture Title -->
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" placeholder="Enter picture title" value="<?= htmlspecialchars($title) ?>" required>
            <span class="col-sm-4 text-danger my-auto">
                <?php 
                if (!empty($errors["title"])) {
                    echo $errors["title"]; 
                } 
                ?>
            </span>
        </div>
        
        <!-- Description -->
        <div class="mb-3">
            <label for="description" class="form-label">Description (Optional)</label>
            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter picture description"></textarea>
        </div>
        
        <!-- Display Message -->
        <?php if ($message): ?>
            <div class="alert alert-info" role="alert">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Submit Button -->
        <div class="row justify-content-center">
                <button type="submit" name="submit" class="btn btn-success col-1 mx-2">Upload</button>
        </div>
    </form>

</main>

<?php include('./common/footer.php'); ?>
