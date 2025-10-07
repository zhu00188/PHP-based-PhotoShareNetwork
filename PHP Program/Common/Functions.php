<?php
include_once 'EntityClassLib.php';

//Connetion to the database
function getPDO()
{
    $dbConnection = parse_ini_file("Config/Project.ini");
    extract($dbConnection);
    return new PDO($dsn, $scriptUser, $scriptPassword);  
}

//Get user for login and new user
function getUserById($userId)
{
    $pdo = getPDO(); // Assume this function connects to the database
    
    // Use prepared statements to prevent SQL injection
    $sql = "SELECT UserId, Name FROM User WHERE UserId = :userId";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['userId' => $userId]);

    // Fetch the user data if a row is returned
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // User exists, return the user data
        return $user;
    } else {
        // User does not exist, return null
        return null;
    }
}

//Get user for login and new user
function getUserByIdAndPassword($userId, $password)
{
    $pdo = getPDO();
    
    // Use prepared statements to prevent SQL injection
    $sql = "SELECT UserId, Name, Phone, Password FROM user WHERE UserId = :userId";
    
    // Prepare the statement
    $pdoStmt = $pdo->prepare($sql);
    
    // Bind the studentId parameter
    $pdoStmt->bindParam(':userId', $userId, PDO::PARAM_STR);
    
    // Execute the statement
    $pdoStmt->execute();
    
    // Fetch the result
    $row = $pdoStmt->fetch(PDO::FETCH_ASSOC);
    
    // If a student is found and password matches
    if ($row && password_verify($password, $row['Password'])) {
        // Create and return a User object
        return new User($row['UserId'], $row['Name'], $row['Phone']);
    } else {
        // Return null if not found or password doesn't match
        return null;
    }
}

//Add new user for NewUser.php
function addNewUser($userId, $name, $phoneNumber, $password)
{
    $pdo = getPDO();

    // Hash the password before storing it
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Use prepared statements to prevent SQL injection
    $sql = "INSERT INTO User (UserId, Name, Phone, Password) VALUES (:userId, :name, :phoneNumber, :password)";
    $pdoStmt = $pdo->prepare($sql);

    // Bind parameters
    $pdoStmt->bindParam(':userId', $userId, PDO::PARAM_STR);
    $pdoStmt->bindParam(':name', $name, PDO::PARAM_STR);
    $pdoStmt->bindParam(':phoneNumber', $phoneNumber, PDO::PARAM_STR);
    $pdoStmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

    // Execute the statement
    $pdoStmt->execute();
}

//Get accessibility options for AddAlbum.php
function getAccessibilityOptions()
{
    $pdo = getPDO(); // Assume this function connects to the database

    // Use a prepared statement to prevent SQL injection
    $sql = "SELECT Accessibility_Code, Description FROM accessibility";
    $stmt = $pdo->prepare($sql);

    $stmt->execute(); // No parameters needed for this query

    // Fetch all results
    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($options) {
        return $options; // Return an array of accessibility options
    } else {
        return null; // Return null if no results found
    }
}

//Add new album for AddAlbum.php
function addNewAlbum($title, $userId, $accessibilityCode, $description = null)
{
    $pdo = getPDO();

    // Adjust SQL to handle optional description
    $sql = "INSERT INTO Album (Title, Description, Owner_Id, Accessibility_Code) VALUES (:title, :description, :ownerId, :accessibilityCode)";
    $stmt = $pdo->prepare($sql);
    
    // Bind parameters
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, $description === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindParam(':ownerId', $userId, PDO::PARAM_STR);
    $stmt->bindParam(':accessibilityCode', $accessibilityCode, PDO::PARAM_STR);

    // Execute the statement
    return $stmt->execute();
}

//Get album info
function getAllAlbums($ownerId)
{
    $pdo = getPDO();

    // SQL query to get album details, count pictures, and accessibility description for a specific owner
    $sql = "
        SELECT 
            a.Album_Id,
            a.Title,
            a.Accessibility_Code,
            acc.Description AS AccessibilityDescription,
            COUNT(p.Picture_Id) AS NumberOfPictures
        FROM 
            album a
        LEFT JOIN 
            picture p
        ON 
            a.Album_Id = p.Album_Id
        INNER JOIN 
            accessibility acc
        ON 
            a.Accessibility_Code = acc.Accessibility_Code
        WHERE 
            a.Owner_Id = :ownerId
        GROUP BY 
            a.Album_Id, a.Title, a.Accessibility_Code, acc.Description
        ORDER BY 
            a.Album_Id ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':ownerId', $ownerId, PDO::PARAM_STR);
    $stmt->execute();

    $albums = [];

    foreach ($stmt as $row) {
        $albums[] = [
            "AlbumId" => $row["Album_Id"],
            "Title" => $row["Title"],
            "AccessibilityCode" => $row["Accessibility_Code"],
            "AccessibilityDescription" => $row["AccessibilityDescription"],
            "NumberOfPictures" => $row["NumberOfPictures"]
        ];
    }

    return $albums;
}

//Delete Album for MyAlbums.php
function deleteAlbum($albumId)
{
    $pdo = getPDO();

    try {
        // Start a transaction to ensure both comments, pictures, and the album are deleted
        $pdo->beginTransaction();

        // First, delete comments associated with the pictures in this album
        $sqlComments = "DELETE FROM comment WHERE Picture_Id IN (SELECT Picture_Id FROM picture WHERE Album_Id = :albumId)";
        $stmtComments = $pdo->prepare($sqlComments);
        $stmtComments->bindParam(':albumId', $albumId, PDO::PARAM_INT);
        $stmtComments->execute();

        // Then, delete pictures associated with the album
        $sqlPictures = "DELETE FROM picture WHERE Album_Id = :albumId";
        $stmtPictures = $pdo->prepare($sqlPictures);
        $stmtPictures->bindParam(':albumId', $albumId, PDO::PARAM_INT);
        $stmtPictures->execute();

        // Finally, delete the album
        $sqlAlbum = "DELETE FROM album WHERE Album_Id = :albumId";
        $stmtAlbum = $pdo->prepare($sqlAlbum);
        $stmtAlbum->bindParam(':albumId', $albumId, PDO::PARAM_INT);
        $stmtAlbum->execute();

        // Commit the transaction
        $pdo->commit();

        return true;
    } catch (Exception $e) {
        // If something goes wrong, roll back the transaction
        $pdo->rollBack();
        echo "Error deleting album, pictures, and comments: " . $e->getMessage();
        return false;
    }
}


//Update Album Accessibility for MyAlbums.php
function updateAlbumAccessibility($albumId, $accCode)
{
        $pdo = getPDO();

        $sql = "UPDATE album SET Accessibility_Code = :accCode WHERE Album_Id = :albumId";
        $stmt = $pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':accCode', $accCode, PDO::PARAM_STR);
        $stmt->bindParam(':albumId', $albumId, PDO::PARAM_INT);

        // Execute the query
        $stmt->execute();
}

//Upload New Picture for UploadPictures.php
function uploadNewPicture($albumId, $fileName, $title, $description = null)
{
    $pdo = getPDO();

    // Adjust SQL to handle optional description
    $sql = "INSERT INTO picture (Album_Id, File_Name, Title, Description) 
            VALUES (:albumId, :fileName, :title, :description)";
    $stmt = $pdo->prepare($sql);
    
    // Bind parameters
    $stmt->bindParam(':albumId', $albumId, PDO::PARAM_INT);
    $stmt->bindParam(':fileName', $fileName, PDO::PARAM_STR);
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, $description === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

    // Execute the statement
    return $stmt->execute();
}

// Get Pictures Overview for MyPictures.php
function getPicturesByAlbum($albumId){
    $pdo = getPDO();

    // SQL query to get album details, count pictures, and accessibility description for a specific owner
    $sql = "
        SELECT Picture_Id, Album_Id, File_Name, Title, Description FROM picture
        WHERE 
            Album_Id = :albumId
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':albumId', $albumId, PDO::PARAM_INT);
    $stmt->execute();

    $pictures = [];

    foreach ($stmt as $row) {
        $pictures[] = [
            "PictureId" => $row["Picture_Id"],
            "AlbumId" => $row["Album_Id"],
            "FileName" => $row["File_Name"],
            "Title" => $row["Title"],
            "Description" => $row["Description"],
        ];
    }

    return $pictures;
}

// Get Picture Details for MyPictures.php
function getPictureDetails($pictureId) {
    $pdo = getPDO();

    // SQL query to get picture details by Picture_Id
    $sql = "
        SELECT 
            Picture_Id, 
            Album_Id, 
            File_Name, 
            Title, 
            Description 
        FROM picture
        WHERE Picture_Id = :pictureId
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':pictureId', $pictureId, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch the result as an associative array
    $picture = $stmt->fetch(PDO::FETCH_ASSOC);

    // Return the picture details or null if not found
    return $picture ? [
        "PictureId" => $picture["Picture_Id"],
        "AlbumId" => $picture["Album_Id"],
        "FileName" => $picture["File_Name"],
        "Title" => $picture["Title"],
        "Description" => $picture["Description"],
    ] : null;
}

// Add New Comment for MyPictures.php
function addNewComment($authorId, $pictureId, $commentText) 
{
    $pdo = getPDO();

    // Adjust SQL to handle optional description
    $sql = "INSERT INTO comment (Author_Id, Picture_Id, Comment_Text) 
            VALUES (:authorId, :pictureId, :commentText)";
    $stmt = $pdo->prepare($sql);
    
    // Bind parameters
    $stmt->bindParam(':authorId', $authorId, PDO::PARAM_STR);
    $stmt->bindParam(':pictureId', $pictureId, PDO::PARAM_INT);
    $stmt->bindParam(':commentText', $commentText, PDO::PARAM_STR);

    // Execute the statement
    return $stmt->execute();	
}

// Get Comments for MyPictures.php
function getCommentDetails($pictureId) {
    $pdo = getPDO();

    // SQL query to get picture details by Picture_Id
    $sql = "
        SELECT 
            c.Comment_Id, 
            c.Author_Id, 
            c.Picture_Id, 
            c.Comment_Text, 
            u.Name
        FROM 
            comment c
        JOIN 
            user u ON c.Author_Id = u.UserId
        WHERE 
            c.Picture_Id = :pictureId
        ORDER BY 
            c.Comment_Id DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':pictureId', $pictureId, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch all results as an associative array
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the array with proper keys
    return $comments ? array_map(function($comment) {
        return [
            "CommentId" => $comment["Comment_Id"],
            "UserId" => $comment["Author_Id"],
            "PictureId" => $comment["Picture_Id"],
            "CommentText" => $comment["Comment_Text"],
            "UserName" => $comment["Name"],
        ];
    }, $comments) : [];
}

// Get All Friends
function getFriendsWithDetails($userId) {
    $pdo = getPDO();

    // Query to fetch friend IDs, names, friendship status, requester, and requestee
    $sql = "
        SELECT 
            CASE 
                WHEN Friend_RequesterId = :userId THEN Friend_RequesteeId
                ELSE Friend_RequesterId
            END AS FriendId,
            u.Name AS FriendName,
            FriendshipStatus.Status_Code AS FriendshipStatus,
            Friendship.Friend_RequesterId AS RequesterId,
            Friendship.Friend_RequesteeId AS RequesteeId
        FROM 
            Friendship
        JOIN 
            FriendshipStatus ON Friendship.Status = FriendshipStatus.Status_Code
        JOIN 
            User u ON u.UserId = 
                CASE 
                    WHEN Friend_RequesterId = :userId THEN Friend_RequesteeId
                    ELSE Friend_RequesterId
                END
        WHERE 
            Friend_RequesterId = :userId OR Friend_RequesteeId = :userId";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['userId' => $userId]);
    
    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $friends; // Return an array with FriendId, FriendName, FriendshipStatus, RequesterId, and RequesteeId
}


// Find a specific friend
function findFriend($friends, $friendId) {
    foreach ($friends as $friend) {
        if ($friend['FriendId'] == $friendId) {
            return $friend; // Return the friend's details(FriendId, FriendName, FriendshipStatus, RequesterId, and RequesteeId) if found
        }
    }
    return null; // Return null if the friend is not found
}

// Send a friend request
function sendFriendRequest($userId, $friendId) {
    $pdo = getPDO();

    try {
        // Insert a new friend request into the friendship table
        $sql = "INSERT INTO friendship (Friend_RequesterId, Friend_RequesteeId, Status) 
                VALUES (:requesterId, :requesteeId, (SELECT Status_Code FROM friendshipstatus WHERE Status_Code = 'request'))";
        $stmt = $pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':requesterId', $userId, PDO::PARAM_STR);
        $stmt->bindParam(':requesteeId', $friendId, PDO::PARAM_STR);

        // Execute the query
        $stmt->execute();

        return true; // Friend request sent successfully
    } catch (Exception $e) {
        // Handle any exceptions
        echo "Error sending friend request: " . $e->getMessage();
        return false;
    }
}

// Accept friend request
function acceptFriendRequest($userId, $friendId) {
    $pdo = getPDO();

    try {
        // Update the friendship status to 'accepted' for the given user and friend
        $sql = "
            UPDATE friendship
            SET Status = (SELECT Status_Code FROM friendshipstatus WHERE Status_Code = 'accepted')
            WHERE Friend_RequesterId = :requesterId AND Friend_RequesteeId = :requesteeId
        ";
        $stmt = $pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':requesterId', $friendId, PDO::PARAM_STR);
        $stmt->bindParam(':requesteeId', $userId, PDO::PARAM_STR);

        // Execute the query
        $stmt->execute();

        return true; // Friend request accepted successfully
    } catch (Exception $e) {
        // Handle any exceptions
        echo "Error accepting friend request: " . $e->getMessage();
        return false;
    }
}

// Unfriend someone
function unfriendFriendRequest($userId, $friendId) {
    $pdo = getPDO();

    try {
        // Update the friendship status to 'accepted' for the given user and friend
        $sql = "
            Delete FROM friendship
            WHERE (Friend_RequesterId = :requesterId AND Friend_RequesteeId = :requesteeId)
            OR (Friend_RequesterId = :requesteeId AND Friend_RequesteeId = :requesterId)
        ";
        $stmt = $pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':requesterId', $friendId, PDO::PARAM_STR);
        $stmt->bindParam(':requesteeId', $userId, PDO::PARAM_STR);

        // Execute the query
        $stmt->execute();

        return true; // Friend request accepted successfully
    } catch (Exception $e) {
        // Handle any exceptions
        echo "Error unfriending friend request: " . $e->getMessage();
        return false;
    }
}

// Get Shared Album
function getSharedAlbums($albums)
{
    $sharedAlbums = [];

    // Loop through each album in the $albums array
    foreach ($albums as $album) {
        // Check if the AccessibilityCode indicates a shared album
        if ($album['AccessibilityCode'] === 'shared') {
            $sharedAlbums[] = $album;
        }
    }

    return $sharedAlbums;
}


