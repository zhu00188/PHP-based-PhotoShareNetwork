<?php
$currentPage = 'myAlbums'; 
include("./common/header.php"); 

if (!isset($_SESSION["isValid"])) {
    header("Location: Login.php");
    exit();
}

// Retrieve all albums for the user
$albums = getAllAlbums($_SESSION["userId"]);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update accessibility
    if (isset($_POST['accessibility']) && isset($_POST['albumId'])) {
        foreach ($_POST['albumId'] as $index => $albumId) {
            $accessibility = $_POST['accessibility'][$index];
            // Call a function to update the album's accessibility
            updateAlbumAccessibility($albumId, $accessibility);
        }

        // Redirect after successful update
        header("Location: myAlbums.php");
        exit();
    }

    // Handle album deletion
    if (isset($_POST['deleteAlbumId'])) {
        $albumId = $_POST['deleteAlbumId'];
        // Call the function to delete the album
        deleteAlbum($albumId);

        // Redirect after successful deletion
        header("Location: myAlbums.php");
        exit();
    }
}

?>

<main class="container w-75">
    <h1 class="text-center my-3">My Albums</h1>
    <p>Hello, <b><?php echo htmlspecialchars($_SESSION["Name"]); ?></b>! (Not you? Change user <a href="logout.php">here</a>)</p>
    <p><a href="AddAlbum.php">Create a new album</a></p>
    
    <!-- Form for updating accessibility -->
    <form action="" method="POST" onsubmit="return confirmChanges()">
        <?php if (!empty($albums)): ?>
        <table class="table" style="table-layout: fixed;">
            <thead>
                <tr>
                    <th style="width: 200px;">Title</th>
                    <th style="width: 130px;">Number Of Pictures</th>
                    <th style="width: 250px;">Accessibility</th>
                    <th style="width: 50px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($albums as $album): ?>
                    <tr class="align-middle">
                        <td>
                            <a href="MyPictures.php?albumId=<?php echo htmlspecialchars($album['AlbumId']); ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($album['Title']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($album['NumberOfPictures']); ?></td>
                        <td>
                            <!-- Accessibility dropdown inside the main form -->
                            <input type="hidden" name="albumId[]" value="<?php echo htmlspecialchars($album['AlbumId']); ?>">
                            <select name="accessibility[]" class="form-select">
                                <option value="private" <?php echo $album['AccessibilityCode'] === 'private' ? 'selected' : ''; ?>>Private - Accessible only by the owner</option>
                                <option value="shared" <?php echo $album['AccessibilityCode'] === 'shared' ? 'selected' : ''; ?>>Shared - Accessible by the owner and friends</option>
                            </select>
                        </td>
                        <td>
                            <!-- Delete button without form, will trigger JavaScript -->
                            <button type="button" class="btn btn-danger btn-sm delete-button" data-album-id="<?php echo htmlspecialchars($album['AlbumId']); ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
            <div class="row justify-content-end">
                <button type="submit" class="btn btn-success col-2 mx-2">Save Changes</button>
            </div>
        <?php else: ?>
            <p>No albums available.</p>
        <?php endif; ?>
    </form>
    
    <!-- Hidden form for deleting an album -->
    <form id="deleteForm" action="" method="POST" style="display:none;">
        <input type="hidden" name="deleteAlbumId" id="deleteAlbumId">
    </form>
</main>

<script>
// Handle delete button click
document.querySelectorAll('.delete-button').forEach(button => {
    button.addEventListener('click', function() {
        const albumId = this.getAttribute('data-album-id');
        if (confirm("Are you sure you want to delete this album?")) {
            // Set the albumId in the hidden form
            document.getElementById('deleteAlbumId').value = albumId;

            // Submit the hidden form to delete the album
            document.getElementById('deleteForm').submit();
        }
    });
});

function confirmChanges() {
    return confirm("Are you sure you want to save the changes?");
}
</script>

<?php include('./common/footer.php'); ?>
