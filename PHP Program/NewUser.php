<?php 

$currentPage = 'customerInformation'; 
include("./common/header.php"); 

$isValid = false;
$errors = [];
$userId = $name = $phoneNumber = $password = $passwordCheck = "";

function ValidateUserId($userId) {
    $error = "";
    if (strlen(trim($userId)) == 0) {
        $error = "User ID is required";
    } else {
        // Check if the user ID exists using the updated getUserById function
        $user = getUserById($userId);
        if ($user) {
            // If user exists, return an error
            $error = "User ID already exists.";
        }
    }
    return $error;
}


function ValidateName($name) {
    $error = "";
    if (strlen(trim($name)) == 0) {
        $error = "Name is required";
    }
    return $error;
}

function ValidatePhoneNumber($phoneNumber) {
    $error = "";
    if (strlen(trim($_POST["phoneNumber"])) == 0) {
        $error = "Phone number is required";
    } elseif (!preg_match('/^[2-9]\d{2}-[2-9]\d{2}-\d{4}$/', $phoneNumber)) {
        $error = "Incorrect phone number";
    }
    return $error;
}

function ValidatePassword($password) {
    $error = "";
    if (strlen(trim($_POST["password"])) == 0) {
        $error = "Password is required";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/', $password)) {
        $error = "Incorrect password";
    }
    return $error;
}

function ValidatePasswordAgain($passwordCheck, $password) {
    $error = "";
    if ($passwordCheck != $password) {
        $error = "Passwords do not match. Please try again.";
    }
    return $error;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate student ID
    $userIdError = ValidateUserId($_POST["userId"]);
    if ($userIdError) {
        $errors["userId"] = $userIdError;
    } else {
        $userId = $_POST["userId"];
        $_SESSION["userId"] = $_POST["userId"];
    }
    
    // Validate name
    $nameError = ValidateName($_POST["name"]);
    if ($nameError) {
        $errors["name"] = $nameError;
    } else {
        $name = $_POST["name"];
        $_SESSION["name"] = $_POST["name"];
    }

    // Validate Phone Number
    $phoneError = ValidatePhoneNumber($_POST["phoneNumber"]);
    if ($phoneError) {
        $errors["phoneNumber"] = $phoneError;
    } else {
        $phoneNumber = $_POST["phoneNumber"];
        $_SESSION["phoneNumber"] = $_POST["phoneNumber"];
    }

    // Validate Password
    $passwordError = ValidatePassword($_POST["password"]);
    $passwordCheckError = ValidatePasswordAgain($_POST["passwordCheck"], $_POST["password"]);
    if ($passwordError) {
        $errors["password"] = $passwordError;
    } elseif ($passwordCheckError) {
        $errors["passwordCheck"] = $passwordCheckError;
    } else {
        $password = $_POST["password"];
        $_SESSION["password"] = $_POST["password"];
        $passwordCheck = $_POST["passwordCheck"];
        $_SESSION["passwordCheck"] = $_POST["passwordCheck"];
    }

    $isValid = empty($errors);
    
    if ($isValid) {
        addNewUser($userId, $name, $phoneNumber, $password);
        $_SESSION["isValid"] = $isValid;
        $_SESSION["Name"] = $name;
        $_SESSION["userId"] = $userId;
        header("Location: MyFriends.php");
        exit();
    } 

}

?>


<main class="container w-75">
    <h1 class="text-center my-3">Sign Up</h1>
    <div class="row">
        <p class="p-5 py-1">All fields are required.</p>
        <form class="form p-5" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <!--Enter user id-->
            <div class="form-group row py-1">
                <label for="userId" class="col-sm-4 col-form-label">User ID:</label>
                <div class="col-sm-4">
                    <input type="text" id="userId" name="userId" class="form-control"
                           value="<?php echo isset($userId) ? htmlspecialchars($userId) : ''; ?>">
                </div>
                <span class="col-sm-4 text-danger my-auto">
                    <?php 
                    if (!empty($errors["userId"])) {
                        echo $errors["userId"]; 
                    } 
                    ?>
                </span>
            </div>
            
            <!--Enter Name-->
            <div class="form-group row py-1">
                <label for="name" class="col-sm-4 col-form-label">Name:</label>
                <div class="col-sm-4">
                    <input type="text" id="name" name="name" class="form-control"
                           value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                </div>
                <span class="col-sm-4 text-danger my-auto">
                    <?php 
                    if (!empty($errors["name"])) {
                        echo $errors["name"]; 
                    } 
                    ?>
                </span>
            </div>
            <!--Enter Phone Number-->
            <div class="form-group row py-1">
                <label for="phoneNumber" class="col-sm-4 col-form-label">Phone Number (nnn-nnn-nnnn):</label>
                <div class="col-sm-4">
                    <input type="text" id="phoneNumber" name="phoneNumber" class="form-control"
                           value="<?php echo isset($phoneNumber) ? htmlspecialchars($phoneNumber) : ''; ?>">
                </div>
                <span class="col-sm-4 text-danger my-auto">
                    <?php 
                    if (!empty($errors["phoneNumber"])) {
                        echo $errors["phoneNumber"]; 
                    } 
                    ?>
                </span>
            </div>
            <!-- Enter Password -->
            <div class="form-group row py-1">
                <label for="password" class="col-sm-4 col-form-label">Password:</label>
                <div class="col-sm-4">
                    <input type="password" id="password" name="password" class="form-control"
                           value="<?php echo isset($password) ? htmlspecialchars($password) : ''; ?>">
                </div>
                <span class="col-sm-4 text-danger my-auto">
                    <?php 
                    if (!empty($errors["password"])) {
                        echo $errors["password"]; 
                    } 
                    ?>
                </span>
            </div>
            <!-- Enter Password Again -->
            <div class="form-group row py-1">
                <label for="passwordCheck" class="col-sm-4 col-form-label">Password Again:</label>
                <div class="col-sm-4">
                    <input type="password" id="passwordCheck" name="passwordCheck" class="form-control"
                           value="<?php echo isset($passwordCheck) ? htmlspecialchars($passwordCheck) : ''; ?>">
                </div>
                <span class="col-sm-4 text-danger my-auto">
                    <?php 
                    if (!empty($errors["passwordCheck"])) {
                        echo $errors["passwordCheck"]; 
                    } 
                    ?>
                </span>
            </div>
            <!-- Submit and Clear Buttons -->
            <div class="my-4">
                <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                <button type="reset" name="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>
</main>


<?php include('./common/footer.php'); ?>

