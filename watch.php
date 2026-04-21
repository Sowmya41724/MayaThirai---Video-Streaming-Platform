<?php
include "config.php";
session_start();

$is_logged_in = isset($_SESSION["id"]);

$video_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($video_id <= 0) {
    header("Location: index.php");
    exit;
}

$sql = "SELECT u.*, c.channel_name, c.profile_pic 
        FROM uploads u
        JOIN channels c ON u.channel_id = c.id
        WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $video_id);
$stmt->execute();
$result = $stmt->get_result();
$video = $result->fetch_assoc();

if (!$video) {
    die("Video not found.");
}

$update_views = "UPDATE uploads SET views = views + 1 WHERE id = ?";
$stmt_view = $conn->prepare($update_views);
$stmt_view->bind_param("i", $video_id);
$stmt_view->execute();

$is_short = $video['is_short'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo htmlspecialchars($video['video_name']); ?> - Mayathirai
    </title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="images/logo-tab.png">
    <link rel="stylesheet" href="Stylesheet/stylesheet.css">
    <link rel="stylesheet" href="js/jquery.min.js">
    <style>
        .video-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .video-player {
            width: 100%;
            background: #000;
        }

        /* Shorts player – vertical orientation */
        .shorts-player {
            max-width: 400px;
            margin: 0 auto;
            background: #000;
            border-radius: 16px;
            overflow: hidden;
        }

        .shorts-player video {
            width: 100%;
            max-height: 90vh;
            object-fit: contain;
        }

        .video-info {
            margin-top: 1rem;
        }

        .video-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .channel-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1rem 0;
        }

        .channel-pic {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
        }

        .views {
            color: #aaa;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <header class="page-header">
        <div class="logo-area">
            <div id="menu">
                <button onclick="myFunction()" style="background-color: transparent;border: none;"><i class="fa fa-bars"
                        style="color: white;"></i></button>
            </div>
            <img src="images/logo-final.png" alt="logo" class="logo-img">
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
        <main class="content">
            <div class="video-container">
                <!-- Player -->
                <?php if ($is_short): ?>
                    <div class="shorts-player">
                        <video controls autoplay loop playsinline>
                            <?php
                            $media_type = $is_short ? 'shorts' : 'videos';
                            $video_url = "uploads/" . urlencode($video['channel_name']) . "/" . $media_type . "/" . urlencode($video['video_filename']);
                            ?>
                            <source src="<?php echo $video_url; ?>" type="video/mp4">
                        </video>
                    </div>
                <?php else: ?>
                    <video class="video-player" controls autoplay>
                        <?php
                        $media_type = $is_short ? 'shorts' : 'videos';
                        $video_url = "uploads/" . urlencode($video['channel_name']) . "/" . $media_type . "/" . urlencode($video['video_filename']);
                        ?>
                        <source src="<?php echo $video_url; ?>" type="video/mp4">
                    </video>
                <?php endif; ?>

                <!-- Video info -->
                <div class="video-info">
                    <h1 class="video-title"><?php echo htmlspecialchars($video['video_name']); ?></h1>
                    <div class="channel-info">
                        <img class="channel-pic"
                            src="<?php echo htmlspecialchars($video['profile_pic'] ?? 'images/default-channel.png'); ?>">
                        <div>
                            <strong><?php echo htmlspecialchars($video['channel_name']); ?></strong>
                            <div class="views"><?php echo number_format($video['views']); ?> views</div>
                        </div>
                    </div>
                    <!-- Optional: description, likes, comments, etc. -->
                </div>
            </div>
        </main>

        <script src="script.js"></script>
</body>

</html>