<?php
$currentPage = 'logIn'; 
include("./common/header.php"); 

$isValid = false;
$errors = [];
$userId = $password = "";

function ValidateUserId($userId) {
    $error = "";
    if (strlen(trim($userId)) == 0) {
        $error = "User ID is required";
    } 
    return $error;
}

function ValidatePassword($password) {
    $error = "";
    if (strlen(trim($_POST["password"])) == 0) {
        $error = "Password is required";
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
    }

    // Validate Password
    $passwordError = ValidatePassword($_POST["password"]);
    if ($passwordError) {
        $errors["password"] = $passwordError;
    } else {
        $password = $_POST["password"];
    }
    
    // Get student
    $incorrectError = "Incorrect User ID or Password.";
    if (empty($errors)) {
    $user = getUserByIdAndPassword($userId, $password);

    // If student is found, login is successful
    if ($user !== null) {
        $isValid = true;
        $_SESSION["isValid"] = $isValid;
        $_SESSION["Name"] = $user->getName(); // Set the session name
        $_SESSION["userId"] = $user->getUserId(); // Save the student ID to the session after successful login
        
        // Redirect to CurrentRegistration.php after successful login
        header("Location: MyFriends.php");
        exit();
    } else {
        // If student is not found or login fails, set error message
        $errors["incorrect"] = "Incorrect Student ID or Password.";
    }
}


}

?>

<main class="container w-75">
    <div>
        <h1 class="text-center my-3">Log In</h1>
    </div>

    <div class="row">
        <p class="p-5 py-1">You need to <a href="NewUser.php" >sign up</a> if you are a new user.</p>
        <form class="form p-5" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <!-- Enter User Id -->
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
            <!-- Error Alert -->
            <span class="col-sm-4 text-danger my-auto">
                    <?php echo $errors["incorrect"] ?? ''; ?>
            </span>
            <!-- Submit and Clear Buttons -->
            <div class="my-4">
                <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                <button type="reset" name="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>
</main>

<?php include('./common/footer.php'); ?>