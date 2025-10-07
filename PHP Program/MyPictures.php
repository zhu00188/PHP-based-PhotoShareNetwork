<?php
$currentPage = 'MyPictures';
include("./common/header.php");

if (!isset($_SESSION["isValid"])) {
    header("Location: Login.php");
    exit();
}

$userId = $_SESSION["userId"];
$albums = getAllAlbums($userId); // Fetch user albums
$albumId = $_POST['album'] ?? $_GET['albumId'] ?? null; // Retrieve albumId from POST or GET
$pictureId = $_POST['picture'] ?? $_GET['pictureId'] ?? null; // Selected picture
$comments = [];
$description = "";

// If albumId is set, fetch pictures for the selected album
if ($albumId) {
    $pictures = getPicturesByAlbum($albumId);
}

// Fetch picture details (description, comments)
if ($pictureId) {
    $pictureDetails = getPictureDetails($pictureId);
    $description = $pictureDetails['Description'];
    $comments = $pictureId ? getCommentDetails($pictureId) : []; 
}

// Handle comment submission
if (isset($_POST['addComment'])) {
    $comment = trim($_POST['comment']);
    if (!empty($comment) && $pictureId) {
        addNewComment($userId, $pictureId, $comment); // Add comment to database
        
        // Redirect
        header("Location: MyPictures.php?albumId=" . $albumId . "&pictureId=" . $pictureId);
        exit();
    }
}
?>

<main class="container w-75">
    <h1 class="text-center my-3">My Pictures</h1>

    <!-- Album Selection -->
    <form action="MyPictures.php" method="post">
        <div class="mb-3">
            <label for="album" class="form-label">Select an Album</label>
            <select id="album" name="album" class="form-select" onchange="this.form.submit()">
                <option value="">-- Choose an Album --</option>
                <?php foreach ($albums as $album): ?>
                    <option value="<?= htmlspecialchars($album['AlbumId']) ?>" <?= $albumId == $album['AlbumId'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($album['Title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
    
    <?php if ($albumId && !empty($pictures)): ?>
    <div class="d-flex align-items-start">    
    <!-- Picture Area -->
        <?php if ($pictureId): ?>
        
            <!-- Picture and Description-->
            <div class="me-2" style="flex: 3;">
                
                <!-- Picture Section -->
                <div class="picture-area text-center">
                    <img src="uploads/<?= htmlspecialchars($pictureDetails['FileName']) ?>" alt="<?= htmlspecialchars($pictureDetails['Title']) ?>" class="img-fluid" style="max-height: 400px;">
                    <h4 class="mt-3"><?= htmlspecialchars($pictureDetails['Title']) ?></h4>
                </div>
                
                <!-- Description Section -->
                <div>
                    <h6>Description</h6>
                    <?php if($description != ""): ?>
                    <p class="fst-italic"><?= htmlspecialchars($description) ?></p>
                    <?php else: ?>
                    <p class="text-secondary fst-italic">No description for this picture.</p>
                    <?php endif; ?> 
                </div>
                
            </div>
              
            <!-- Comment Section Container-->
            <div class="me-1" style="flex: 1;">

                <!-- Comments Section -->
                <div class="comments-section pt-2">
                    <h5>Comments</h5>
                    <?php if (!empty($comments)): ?>
                    <div class="comments-scroll">
                        <ul class="list-unstyled">
                            <?php foreach ($comments as $oneComment): ?>
                                <li>
                                    <p>
                                        <?= htmlspecialchars($oneComment['UserName']) ?>: <i class="text-secondary"><?= htmlspecialchars($oneComment['CommentText']) ?></i>
                                    </p>
                                    <p class="fst-italic"></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php else: ?>
                        <p>No comments yet. Be the first to leave one!</p>
                    <?php endif; ?>
                </div>

                <!-- Add Comment -->
                <form action="" method="post">
                    <input type="hidden" name="album" value="<?= htmlspecialchars($albumId) ?>">
                    <input type="hidden" name="picture" value="<?= htmlspecialchars($pictureId) ?>">
                    <div class="mb-3">
                        <label for="comment" class="form-label"></label>
                        <textarea id="comment" name="comment" rows="3" class="form-control" placeholder="Write your comment here..."></textarea>
                    </div>
                    <button type="submit" name="addComment" class="btn btn-success">Leave Comment</button>
                </form>
                
            </div>
        
        <?php else: ?>
        <p class="text-center">Select a picture to view details and comments.</p>
        <?php endif; ?>        
    </div>
    
    <!-- Thumbnails -->
    <div id="thumbnailScrollContainer" class="thumbnail-bar d-flex me-3">
        <div class="thumbnail-scroll-container">
            <?php foreach ($pictures as $picture): ?>
                <form action="MyPictures.php" method="post" class="m-1 d-inline-block">
                    <input type="hidden" name="album" value="<?= htmlspecialchars($albumId) ?>">
                    <input type="hidden" name="picture" value="<?= htmlspecialchars($picture['PictureId']) ?>">
                    <button type="submit" class="btn p-0 border-0">
                        <img src="uploads/<?= htmlspecialchars($picture['FileName']) ?>" 
                             class="thumbnail <?= $pictureId == $picture['PictureId'] ? 'border border-primary' : '' ?>" 
                             alt="<?= htmlspecialchars($picture['Title']) ?>"
                             data-id="<?= htmlspecialchars($picture['PictureId']) ?>"
                             width="100" height="100">
                    </button>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
    <?php elseif ($albumId): ?>
        <p class="text-center">No pictures found in this album.</p>
    <?php else: ?>
        <p class="text-center">Select an album to browse its pictures.</p>
    <?php endif; ?>
    
</main>

<script>
   document.addEventListener('DOMContentLoaded', function () {
    const scrollContainer = document.querySelector('.thumbnail-scroll-container');

    // Check if pictureId is set (sent from the server)
    const serverPictureId = "<?= htmlspecialchars($pictureId ?? '') ?>"; // PHP variable for current pictureId

    // If no picture is selected, clear localStorage for thumbnails
    if (!serverPictureId) {
        localStorage.removeItem('thumbnailScrollPosition');
        localStorage.removeItem('selectedThumbnailId');
    } else {
        // Restore scroll position and selected thumbnail
        const savedScrollPosition = localStorage.getItem('thumbnailScrollPosition');
        const savedSelectedPicture = localStorage.getItem('selectedThumbnailId');

        if (savedScrollPosition) {
            scrollContainer.scrollLeft = savedScrollPosition;
        }

        if (savedSelectedPicture && savedSelectedPicture === serverPictureId) {
            const selectedThumbnail = document.querySelector(
                `.thumbnail[data-id="${savedSelectedPicture}"]`
            );
            if (selectedThumbnail) {
                selectedThumbnail.classList.add('border', 'border-primary');
            }
        }
    }

    // Save scroll position and selected thumbnail on form submit
    document.querySelectorAll('.thumbnail-scroll-container form').forEach((form) => {
        form.addEventListener('submit', function () {
            const selectedPictureId = this.querySelector(
                'input[name="picture"]'
            ).value;

            localStorage.setItem(
                'thumbnailScrollPosition',
                scrollContainer.scrollLeft
            );
            localStorage.setItem('selectedThumbnailId', selectedPictureId);
        });
    });
});

</script>




<?php include('./common/footer.php'); ?>
