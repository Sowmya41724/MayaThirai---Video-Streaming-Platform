<?php
include "config.php";
session_start();

$is_logged_in = isset($_SESSION["id"]);

$channel_name = $video = $video_name = "";
$channel_name_err = $video_err = $video_name_err = $thumbnail_err = "";

$user_id = $_SESSION["id"];
$channels = [];
$sql = "SELECT id, channel_name FROM channels WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $channels[] = $row;
}
$stmt->close();

// If the user has no channels, redirect them to a "create channel" page
if (empty($channels)) {
    header("Location: create_channel.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $error = 0;

    $channel_id = intval($_POST["channel_id"] ?? 0);
    $video_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST["video_name"] ?? '');

    $valid_channel = false;
    foreach ($channels as $ch) {
        if ($ch['id'] == $channel_id) {
            $valid_channel = true;
            break;
        }
    }
    if (!$valid_channel) {
        $channel_name_err = "Invalid channel selected.";
        $error = 1;
    }

    if (empty($_POST["video_name"])) {
        $video_name_err = "Video Name is required.";
        $error = 1;
    }

    $video_filename = "";
    $file_size_bytes = 0;

    if ($error == 0 && isset($_FILES["document"]) && $_FILES["document"]["error"] == UPLOAD_ERR_OK) {

        $allowed_ext = ['mp4'];
        $allowed_mime = ['video/mp4'];
        $max_size = 100 * 1024 * 1024;

        $file_name = $_FILES["document"]["name"];
        $file_tmp = $_FILES["document"]["tmp_name"];
        $file_size = $_FILES["document"]["size"];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_ext)) {
            $video_err = "Only MP4 files are allowed.";
            $error = 1;
        } elseif (!in_array(mime_content_type($file_tmp), $allowed_mime)) {
            $video_err = "Invalid video format. Only MP4 is allowed.";
            $error = 1;
        } elseif ($file_size > $max_size) {
            $video_err = "Video is too large. Max limit is 100MB.";
            $error = 1;
        } else {
            // Get channel name for directory
            $channel_name_for_dir = "";
            foreach ($channels as $ch) {
                if ($ch['id'] == $channel_id) {
                    $channel_name_for_dir = $ch['channel_name'];
                    break;
                }
            }
            $safe_dir_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $channel_name_for_dir);

            // Determine if it's a short
            $is_short = isset($_POST["is_short"]) ? 1 : 0;
            $media_subdir = $is_short ? "shorts" : "videos";

            // Base upload directory: uploads/channel_name/
            $base_upload_dir = "uploads/" . $safe_dir_name . "/";
            $upload_dir = $base_upload_dir . $media_subdir . "/";
            $thumb_dir = $base_upload_dir . $media_subdir . "/thumbnails/";

            // Create directories if missing
            if (!is_dir($upload_dir))
                mkdir($upload_dir, 0755, true);
            if (!is_dir($thumb_dir))
                mkdir($thumb_dir, 0755, true);

            // Video filename
            $new_filename = $video_name . '.' . $file_ext;
            $destination = $upload_dir . $new_filename;
            if (file_exists($destination)) {
                $new_filename = $video_name . '_' . time() . '.' . $file_ext;
                $destination = $upload_dir . $new_filename;
            }

            // Move video file
            if (move_uploaded_file($file_tmp, $destination)) {
                $video_filename = $new_filename;
                $file_size_bytes = $file_size;
            } else {
                $video_err = "Failed to save video.";
                $error = 1;
            }

            // Handle thumbnail upload (only if video saved successfully)
            if ($error == 0 && isset($_FILES["thumbnail"]) && $_FILES["thumbnail"]["error"] == UPLOAD_ERR_OK) {
                $thumb_tmp = $_FILES["thumbnail"]["tmp_name"];
                $thumb_mime = mime_content_type($thumb_tmp);
                $allowed_thumb = ['image/jpeg', 'image/png'];
                if (in_array($thumb_mime, $allowed_thumb)) {
                    $thumb_ext = $thumb_mime == 'image/jpeg' ? 'jpg' : 'png';
                    $thumb_name = pathinfo($new_filename, PATHINFO_FILENAME) . "_thumb." . $thumb_ext;
                    $thumb_dest = $thumb_dir . $thumb_name;
                    if (move_uploaded_file($thumb_tmp, $thumb_dest)) {
                        $thumbnail_path = $thumb_dest;
                    } else {
                        $thumbnail_err = "Failed to save thumbnail.";
                        $error = 1;
                    }
                } else {
                    $thumbnail_err = "Only JPG/PNG allowed for thumbnail.";
                    $error = 1;
                }
            }
            // If no thumbnail uploaded, set default
            if (empty($thumbnail_path)) {
                $thumbnail_path = "images/default-thumb.jpg";
            }
        }
    } elseif ($_FILES["document"]["error"] != UPLOAD_ERR_NO_FILE) {
        $video_err = "File upload error code: " . $_FILES["document"]["error"];
        $error = 1;
    } elseif ($error == 0) {
        $video_err = "Please select a video file.";
        $error = 1;
    }

    if ($error === 0) {
        $user_id = $_SESSION["id"];

        $sql = "INSERT INTO uploads (user_id, channel_id, video_name, video_filename, video_size, is_short, thumbnail) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($sql);
        if (!$insert_stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $insert_stmt->bind_param("iissiis", $user_id, $channel_id, $video_name, $video_filename, $file_size_bytes, $is_short, $thumbnail_path);
        if ($insert_stmt->execute()) {
            header("Location: Profile/profile.php");
            exit;
        } else {
            echo "Insert error: " . $insert_stmt->error;
        }
        $insert_stmt->close();
    }
}

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="images/logo-tab.png">
    <link rel="stylesheet" href="Stylesheet/stylesheet.css">
    <link rel="stylesheet" href="js/jquery.min.js">
</head>

<body>
    <header class="page-header">
        <div class="logo-area">
            <div id="menu">
                <button onclick="myFunction()" style="background-color: transparent;border: none;"><i class="fa fa-bars"
                        style="color: white;"></i></button>
            </div>
            <img onclick="location.href='index.php'" src="images/logo-final.png" alt="logo" class="logo-img">
        </div>
        <div style="align-items: center;">
            <form class="example" action="/action_page.php">
                <input type="text" placeholder="Search.." name="search">
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>
        </div>
        <div class="header-right">
            <button class="btn" onclick="location.href='create.php'">+ Create</button>
            <span style="padding: 10px;"></span>
            <label class="mayathirai-switch" style="align-items: right;">
                <input type="checkbox" id="themeSwitch">
                <span class="switch-slider">
                    <span class="switch-icon switch-icon-dark"></span>
                    <span class="switch-icon switch-icon-light"></span>
                </span>
            </label>
            <span style="padding: 10px;"></span>
            <?php if ($is_logged_in): ?>
                <div class="dropdown">
                    <button class="dropbtn">
                        <!-- Changed <image> to <img> and fixed self-closing -->
                        <img class="profile-pic" alt="Profile Picture"
                            src="https://img.freepik.com/premium-vector/user-profile-icon-circle_1256048-12499.jpg?semt=ais_hybrid&w=740&q=80">
                    </button>
                    <div class="dropdown-content">
                        <a href="profile/profile.php">Profile</a>
                        <a href="profile/settings.php">Settings</a>
                        <a href="login/logout.php" style="color: red;">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Sign-in button for guests -->
                <button onclick="location.href='login/login.php'" class="btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            <?php endif; ?>
        </div>
    </header>
    <div class="page-container">
        <main class="content">
            <div class="form-container">
                <div class="left">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>"
                        enctype="multipart/form-data">
                        <label>Select Channel:</label><br>
                        <span class="error"><?php echo $channel_name_err; ?></span>
                        <select name="channel_id" required>
                            <option value="">-- Choose a channel --</option>
                            <?php foreach ($channels as $ch): ?>
                                <option value="<?php echo $ch['id']; ?>">
                                    <?php echo htmlspecialchars($ch['channel_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <br><br>

                        <label>Video Name:</label><br>
                        <span class="error">
                            <?php echo $video_name_err; ?>
                        </span>
                        <input type="text" id="video" name="video_name"
                            value="<?php echo htmlspecialchars($video_name); ?>">
                        <br><br>

                        <label>Thumbnail (JPG/PNG):</label><br>
                        <span class="error">
                            <?php echo $thumbnail_err; ?>
                        </span>
                        <input type="file" name="thumbnail" accept="image/jpeg,image/png">
                        <br><br>

                        <label>Upload Video:</label><br>
                        <span class="error">
                            <?php echo $video_err; ?>
                        </span>
                        <input type="file" id="video" name="document" accept=".mp4">
                        <br><br>

                        <label>
                            <input type="checkbox" name="is_short" value="1">
                            This video is a Short (vertical, under 60 seconds)
                            <p><i>"A Short is a vertical video (9:16 aspect ratio) that is 60 seconds or less. Check
                                    this
                                    box if
                                    your video meets these criteria."</i></p>
                        </label>
                        <br><br>

                        <input type="submit" value="Upload Now" class="btn" name="submit">
                    </form>
                </div>
                <div class="right">
                    <img src="https://thumbs.dreamstime.com/b/man-records-video-blog-vlog-concept-vector-flat-style-illustration-happy-blogger-97398592.jpg"
                        alt="Create_Channel">
                </div>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
</body>

</html>