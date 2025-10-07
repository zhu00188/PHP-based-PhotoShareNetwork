<?php
$currentPage = 'myFriends';
include("./common/header.php");

if (!isset($_SESSION["isValid"])) {
    header("Location: Login.php");
    exit();
}

$userId = $_SESSION["userId"];
$friends = getFriendsWithDetails($userId); // Fetch friends with details

// Arrays to categorize friends based on their relationship status
$acceptedFriends = [];
$requestSentFriends = [];
$requestReceivedFriends = [];
$sharedAlbums = [];

foreach ($friends as $friend) {
    if ($friend['FriendshipStatus'] == 'accepted') {
        $acceptedFriends[] = $friend;
        // Get shared albums for the current friend
        $friendSharedAlbums = getSharedAlbums(getAllAlbums($friend["FriendId"]));
        // Store the count of shared albums in the sharedAlbums array
        $sharedAlbums[] = count($friendSharedAlbums);
    } elseif ($friend['FriendshipStatus'] == 'request') {
        if ($friend['RequesterId'] == $userId) {
            $requestSentFriends[] = $friend; // User has sent a request
        } else {
            $requestReceivedFriends[] = $friend; // User has received a request
        }
    }
}

// Process the selected action from the checkboxes
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Accepted Friends - Unfriend
    if (isset($_POST['remove'])) {
        foreach ($_POST['remove_friend'] as $friendId) {
            unfriendFriendRequest($userId, $friendId);
        }
    }

    // Friend Requests Sent - Cancel
    if (isset($_POST['cancel'])) {
        foreach ($_POST['cancel_friend'] as $friendId) {
            unfriendFriendRequest($userId, $friendId);
        }
    }

    // Friend Requests Received - Accept or Deny
    if (isset($_POST['action']) && isset($_POST['action_friend'])) {
        foreach ($_POST['action_friend'] as $friendId) {
            if ($_POST['action'] == 'accept') {
                acceptFriendRequest($userId, $friendId);
            } elseif ($_POST['action'] == 'deny') {
                unfriendFriendRequest($userId, $friendId);
            }
        }
    }
    
    header("Location: myFriends.php");
}
?>

<main class="container w-75">
    <h1 class="text-center my-3">My Friends</h1>
    <p>Hello, <b><?php echo htmlspecialchars($_SESSION["Name"]); ?></b>! (Not you? Change user <a href="logout.php">here</a>)</p>
    <p>Click <a href="AddFriend.php">here</a> to add a new friend.</p>

    <!-- Accepted Friends -->
    <?php if (!empty($acceptedFriends)): ?>
        <h3>Accepted Friends</h3>
        <form method="POST" class="mb-4">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50%;">Name</th>
                        <th style="width: 30%;">Shared Albums</th>
                        <th style="width: 20%;">Unfriend</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($acceptedFriends as $index => $friend): 
                        // Get the number of shared albums for this friend from the $sharedAlbums array
                        $sharedAlbumCount = isset($sharedAlbums[$index]) ? $sharedAlbums[$index] : 0;
                    ?>
                        <tr>
                            <td>
                                <a href="FriendPictures.php?friendId=<?= urlencode($friend['FriendId']) ?>">
                                    <?= htmlspecialchars($friend['FriendName']) ?>
                                </a>
                            </td>
                            <td><?= $sharedAlbumCount ?></td> <!-- Display the shared album count -->
                            <td>
                                <input type="checkbox" name="remove_friend[]" value="<?= htmlspecialchars($friend['FriendId']) ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="text-end me-4">
                <button type="submit" name="remove" class="btn btn-danger delete-button" style="width: 180px;">Unfriend Selected</button>
            </div>
        </form>
    <?php endif; ?>

    <!-- Friend Requests Sent -->
    <?php if (!empty($requestSentFriends)): ?>
        <h3>Friend Requests Sent</h3>
        <form method="POST" class="mb-4">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 80%;">Name</th>
                        <th style="width: 20%;">Cancel</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requestSentFriends as $friend): ?>
                        <tr>
                            <td><?= htmlspecialchars($friend['FriendName']) ?></td>
                            <td>
                                <input type="checkbox" name="cancel_friend[]" value="<?= htmlspecialchars($friend['FriendId']) ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="text-end me-4">
                <button type="submit" name="cancel" class="btn btn-danger cancel-button" style="width: 180px;">Cancel Selected</button>
            </div>
        </form>
    <?php endif; ?>

    <!-- Friend Requests Received -->
    <?php if (!empty($requestReceivedFriends)): ?>
        <h3>Friend Requests Received</h3>
        <form method="POST" class="mb-4">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 80%;">Name</th>
                        <th style="width: 20%;">Accept or Deny</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requestReceivedFriends as $friend): ?>
                        <tr>
                            <td><?= htmlspecialchars($friend['FriendName']) ?></td>
                            <td>
                                <input type="checkbox" name="action_friend[]" value="<?= htmlspecialchars($friend['FriendId']) ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="form-group text-end me-4">
                <button type="submit" name="action" value="accept" class="btn btn-success me-2" style="width: 180px;">Accept Selected</button>
                <button type="submit" name="action" value="deny" class="btn btn-danger deny-button" style="width: 180px;">Deny Selected</button>
            </div>
        </form>
    <?php endif; ?>

</main>

<script>

// Add confirmation for delete buttons
document.querySelectorAll('.delete-button').forEach(button => {
    button.addEventListener('click', function (event) {
        // Prevent form submission if the user cancels
        if (!confirm("Are you sure you want to delete this friend?")) {
            event.preventDefault();
        }
    });
});

// Add confirmation for cancel buttons
document.querySelectorAll('.cancel-button').forEach(button => {
    button.addEventListener('click', function (event) {
        // Prevent form submission if the user cancels
        if (!confirm("Are you sure you want to cancel this friend request?")) {
            event.preventDefault();
        }
    });
});

// Add confirmation for deny buttons
document.querySelectorAll('.deny-button').forEach(button => {
    button.addEventListener('click', function (event) {
        if (!confirm("Are you sure you want to deny this friend request?")) {
            event.preventDefault();
        }
    });
});

</script>

<?php include('./common/footer.php'); ?>
