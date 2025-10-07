<?php
$currentPage = 'myAlbums'; 
include("./common/header.php"); 

if(!isset($_SESSION["isValid"]))
{
    header("Location: Login.php");
    exit();
}

$errors = [];
$title = $accessibilityCode = $message = "";
$description = null;


function ValidateTitle($title) {
    $error = "";
    if (strlen(trim($title)) == 0) {
        $error = "Title is required";
    }
    return $error;
}

function ValidateAccessibilityCode($accessibilityCode) {
    $error = "";
    // Define valid options (you can fetch these from the database if needed)
    $validOptions = ["private", "shared"];

    // Check if the selected value is in the valid options
    if (!in_array($accessibilityCode, $validOptions)) {
        $error = "Invalid accessibility code selected.";
    }

    return $error;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate Title
    $titleError = ValidateTitle($_POST["title"]);
    if ($titleError) {
        $errors["title"] = $titleError;
    } else {
        $title = trim($_POST["title"]);
    }
    
    // Validate Accessibility Code
    $AccCodeError = ValidateAccessibilityCode($_POST["accessibility"]);
    if ($AccCodeError) {
        $errors["accessibility"] = $AccCodeError;
    } else {
        $accessibilityCode = $_POST["accessibility"];
    }
    
    if (!empty($_POST["description"])) {
        $description = trim($_POST["description"]);
    }
    
    if (empty($errors)) {
        if (addNewAlbum($title, $_SESSION["userId"], $accessibilityCode, $description)) {
            $message = "Album added successfully!";
        } else {
            $message = "Failed to add the album. Please try again.";
        }
    }
}

?>

<main class="container w-75">
    <h1 class="text-center my-3">Create A New Album</h1>
    <p>Hello, <b><?php echo htmlspecialchars($_SESSION["Name"]); ?></b>! (Not you? Change user <a href="logout.php">here</a>)</p>
    
    <form action="" method="post">
        <!-- Album Title -->
        <div class="mb-3">
            <label for="title" class="form-label">Album Title</label>
            <input type="text" class="form-control" id="title" name="title" placeholder="Enter album title" required>
            <span class="col-sm-4 text-danger my-auto">
                <?php 
                if (!empty($errors["title"])) {
                    echo $errors["title"]; 
                } 
                ?>
            </span>
        </div>
        
        
        <!-- Accessibility -->
        <div class="mb-3">
            <label for="accessibility" class="form-label">Accessibility</label>
            <select id="accessibility" name="accessibility" class="form-select" required>
                <option value="" disabled selected>Select accessibility</option>
                <?php
                // Fetch accessibility options from the database
                $options = getAccessibilityOptions();

                foreach ($options as $option) {
                    echo "<option value=\"{$option['Accessibility_Code']}\">{$option['Accessibility_Code']} - {$option['Description']}</option>";
                }
                ?>
            </select>
            <span class="col-sm-4 text-danger my-auto">
                <?php 
                if (!empty($errors["accessibilityCode"])) {
                    echo $errors["accessibilityCode"]; 
                } 
                ?>
            </span>
        </div>
        
        <!-- Description -->
        <div class="mb-3">
            <label for="description" class="form-label">Description (Optional)</label>
            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter album description"></textarea>
        </div>
        
        <!-- Display Message -->
        <?php if ($message): ?>
            <div class="alert alert-info" role="alert">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <!-- Submit Button -->
        <div class="row justify-content-center">
                <button type="submit" class="btn btn-success col-1 mx-2">Submit</button>
                <button type="reset" class="btn btn-secondary col-1 mx-2">Clear</button>
        </div>
    </form>

</main>

<?php include('./common/footer.php'); 