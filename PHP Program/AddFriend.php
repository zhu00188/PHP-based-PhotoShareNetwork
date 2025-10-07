<?php
$currentPage = 'myFriends'; 
include("./common/header.php"); 

if(!isset($_SESSION["isValid"]))
{
    header("Location: Login.php");
    exit();
}

$friendId = $error = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if ($_POST["friend"] == $_SESSION["userId"]) {
        // if user is themselves
        $error = "You cannot send a friend request to yourself.";
    }
    else {
        $friendId = $_POST["friend"];
        $friend = getUserById($friendId);
        
        if (!$friend) {
            // If user does not exist, return an error
            $error = "User ID does not exist.";
        } else {
            $friendResult = findFriend(getFriendsWithDetails($_SESSION["userId"]), $friendId);

            if ($friendResult) {
                if ($friendResult["FriendshipStatus"] == "accepted") {
                    $error = "You and " . htmlspecialchars($friendResult["FriendName"]) . " (ID: " . htmlspecialchars($friendResult["FriendId"]) . ") are already friends.";
                } else if ($friendResult["FriendshipStatus"] == "request") {
                    if ($friendResult["RequesterId"] == $_SESSION["userId"]) {
                        $error = "You have already sent " . htmlspecialchars($friendResult["FriendName"]) . " (" . htmlspecialchars($friendResult["FriendId"]) . ") a request. Please wait for confirmation.";
                    } else if ($friendResult["RequesterId"] == $friendId) {
                        $error = htmlspecialchars($friendResult["FriendName"]) . " (" . htmlspecialchars($friendResult["FriendId"]) . ") has already sent you a friend request. " .
                             'Click <a href="MyFriends.php">here</a> to accept or deny the request.';
                    }                    
                }
            } else {
                sendFriendRequest($_SESSION["userId"], $friendId);
                $error = "You request has been sent to " . htmlspecialchars($friend["Name"]) . " (ID: " . htmlspecialchars($friendId) . ") Please wait for confirmation.";
            }
        }
    }
}

?>

<main class="container w-75">
    <h1 class="text-center my-3">Add New Friend</h1>
    <p>Hello, <b><?php echo htmlspecialchars($_SESSION["Name"]); ?></b>! (Not you? Change user <a href="logout.php">here</a>)</p>
    <p>Enter the ID of the user you want to be friend with:</p>
    <form action="" method="post">
        <!-- Album Title -->
    <div class="mb-3 row">
        <label for="friend" class="form-label col-sm-1">ID</label>
        <div class="col-sm-3">
            <input type="text" class="form-control" id="friend" name="friend" placeholder="Enter user ID" required>
        </div>
        <div class="col-sm-3">
            <button type="submit" class="btn btn-primary">Send Friend Request</button>
        </div>
    </div>

        <span class="col-sm-4 text-danger my-auto">
            <?php 
            if ($error) {
                echo $error; 
            } 
            ?>
        </span>
        
        
        
        <!-- Space for message -->
        <span id="message" style="color: #FFFFFF;"><?php echo htmlspecialchars($message); ?></span>
        

    </form>

</main>

<?php include('./common/footer.php'); 