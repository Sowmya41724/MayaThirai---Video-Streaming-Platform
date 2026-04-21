<?php
include "config.php";
session_start();

$is_logged_in = isset($_SESSION["id"]);
if (!$is_logged_in) {
    header("Location: login/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $channel_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST["channel_name"]);
    $user_id = $_SESSION["id"];

    $sql = "INSERT INTO channels (user_id, channel_name) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $channel_name);
    $stmt->execute();
    $channel_id = $conn->insert_id;

    $base_dir = "uploads/" . $channel_name . "/";
    $subdirs = ['channel_pic', 'videos', 'videos/thumbnails', 'shorts', 'shorts/thumbnails'];
    foreach ($subdirs as $sub) {
        $path = $base_dir . $sub;
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    $profile_pic_path = null;
    if (isset($_FILES["profile_pic"]) && $_FILES["profile_pic"]["error"] == UPLOAD_ERR_OK) {

        $allowed = ['image/jpeg', 'image/png'];
        $tmp = $_FILES["profile_pic"]["tmp_name"];
        $mime = mime_content_type($tmp);

        if (in_array($mime, $allowed)) {
            $ext = $mime == 'image/jpeg' ? 'jpg' : 'png';
            $filename = "profile." . $ext;
            $dest = $base_dir . "channel_pic/" . $filename;
            if (move_uploaded_file($tmp, $dest)) {
                $profile_pic_path = $dest;
            }
        }
    }

    if ($profile_pic_path) {
        $update = "UPDATE channels SET profile_pic = ? WHERE id = ?";
        $stmt2 = $conn->prepare($update);
        $stmt2->bind_param("si", $profile_pic_path, $channel_id);
        $stmt2->execute();
    }

    header("Location: create.php");
    exit;
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
    <div class="app-container">

        <!-- SIDE MENU -->
        <nav class="side-menu" id="mySidebar">
            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='#Home';">
                    <i class="fa fa-home"></i>Home
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='shorts.php';">
                    <i class="fa fa-play"></i>Shorts
                </div>
            </div>

            <hr>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='you/profile.php';">
                    YOU <i class="fas fa-chevron-right arrow"></i>
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='you/history.php';">
                    <i class="fa fa-history"></i>History
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='you/playlist.php';">
                    <i class="fas fa-list"></i>Playlist
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='you/watch_later.php';">
                    <i class="fas fa-clock"></i>Watch Later
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='you/liked_videos.php';">
                    <i class="fas fa-thumbs-up"></i>Liked Videos
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='you/your_videos.php';">
                    <i class="fas fa-video"></i>Your Videos
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='you/downloads.php';">
                    <i class="fas fa-download"></i>Downloads
                </div>
            </div>

            <hr>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='explore/explore.php';">
                    EXPLORE <i class="fas fa-chevron-right arrow"></i>
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='explore/shopping.php';">
                    <i class="fas fa-shopping-cart"></i>Shopping
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='explore/music.php';">
                    <i class="fas fa-music"></i>Music
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='explore/film.php';">
                    <i class="fas fa-film"></i>Film
                </div>
            </div>

            <div class="menu-item">
                <!-- The list comes first now -->
                <ul class="submenu" id="more-menu">
                    <li><a href="explore/live.php"><i class="fas fa-broadcast-tower"></i><span>Live</span></a></li>
                    <li><a href="explore/news.php"><i class="fas fa-newspaper"></i><span>News</span></a></li>
                    <li><a href="explore/sports.php"><i class="fas fa-trophy"></i><span>Sports</span></a></li>
                    <li><a href="explore/courses.php"><i class="fas fa-book-open"></i><span>Courses</span></a></li>
                    <li><a href="explore/fashion_beauty.php"><i class="fas fa-tshirt"></i><span>Fashion &
                                Beauty</span></a></li>
                    <li><a href="explore/podcast.php"><i class="fas fa-podcast"></i><span>Podcasts</span></a></li>
                    <li><a href="explore/playables.php"><i class="fas fa-gamepad"></i><span>Playables</span></a></li>
                </ul>

                <!-- The button is at the bottom -->
                <div class="menu-head" onclick="toggleSubmenu(this)">
                    <span id="toggleText">Show More</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </div>
            </div>


            <hr>

            <div style="color: darkslategray; text-align: center;">
                <p>&copy;
                    <?php echo date('Y'); ?> MayaThirai. All rights reserved.
                </p>
            </div>
        </nav>
        <main>
            <div class="form-container">
                <div class="left">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>"
                        enctype="multipart/form-data">
                        <h1 class="gradient-text">Create a Channel</h1>
                        <label>Channel Name:</label>
                        <input type="text" name="channel_name" required>
                        <br><br>

                        <label>Profile Pic:</label>
                        <input type="file" name="profile_pic" accept="image/jpeg,image/png">
                        <br><br>

                        <input type="submit" value="Create Channel" class="btn">
                    </form>
                </div>
                <div class="right">
                    <img src="https://kinsta.com/wp-content/uploads/2021/07/how-to-create-a-youtube-channel.jpg"
                        alt="Create_Channel">
                </div>
            </div>
        </main>
        <script src="script.js"></script>
</body>

</html>