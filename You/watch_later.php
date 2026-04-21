<?php
include "../config.php";
session_start();

$is_logged_in = isset($_SESSION["id"]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Later</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="../images/logo-tab.png">
    <link rel="stylesheet" href="../stylesheet/stylesheet.css">
    <link rel="stylesheet" href="../js/jquery.min.js">
</head>

<body>
    <header class="page-header">
        <div class="logo-area">
            <div id="menu">
                <button onclick="myFunction()" style="background-color: transparent;border: none;">
                    <i class="fa fa-bars" style="color: white;"></i>
                </button>
            </div>
            <img onclick="location.href='../index.php'" src="../images/logo-final.png" alt="logo" class="logo-img">
        </div>
        <div style="align-items: center;">
            <form class="example" action="/action_page.php">
                <input type="text" placeholder="Search.." name="search">
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>
        </div>
        <div class="header-right">
            <button class="btn" onclick="location.href='../create.php'">+ Create</button>
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
                        <a href="../profile/profile.php">Profile</a>
                        <a href="../profile/settings.php">Settings</a>
                        <a href="../login/logout.php" style="color: red;">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Sign-in button for guests -->
                <button onclick="location.href='../login/login.php'" class="btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            <?php endif; ?>
        </div>
    </header>
    <div class="app-container">

        <!-- SIDE MENU -->
        <nav class="side-menu" id="mySidebar">
            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='../index.php';">
                    <i class="fa fa-home"></i>Home
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='../shorts.php';">
                    <i class="fa fa-play"></i>Shorts
                </div>
            </div>

            <hr>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='profile.php';">
                    YOU <i class="fas fa-chevron-right arrow"></i>
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='history.php';">
                    <i class="fa fa-history"></i>History
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='playlist.php';">
                    <i class="fas fa-list"></i>Playlist
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='#watch_later';">
                    <i class="fas fa-clock"></i>Watch Later
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='liked_videos.php';">
                    <i class="fas fa-thumbs-up"></i>Liked Videos
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='your_videos.php';">
                    <i class="fas fa-video"></i>Your Videos
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='downloads.php';">
                    <i class="fas fa-download"></i>Downloads
                </div>
            </div>

            <hr>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='../explore/explore.php';">
                    EXPLORE <i class="fas fa-chevron-right arrow"></i>
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='../explore/shopping.php';">
                    <i class="fas fa-shopping-cart"></i>Shopping
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='../explore/music.php';">
                    <i class="fas fa-music"></i>Music
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='../explore/film.php';">
                    <i class="fas fa-film"></i>Film
                </div>
            </div>

            <div class="menu-item">
                <!-- The list comes first now -->
                <ul class="submenu" id="more-menu">
                    <li><a href="../explore/live.php"><i class="fas fa-broadcast-tower"></i><span>Live</span></a>
                    </li>
                    <li><a href="../explore/news.php"><i class="fas fa-newspaper"></i><span>News</span></a></li>
                    <li><a href="../explore/sports.php"><i class="fas fa-trophy"></i><span>Sports</span></a></li>
                    <li><a href="../explore/courses.php"><i class="fas fa-book-open"></i><span>Courses</span></a></li>
                    <li><a href="../explore/fashion_beauty.php"><i class="fas fa-tshirt"></i><span>Fashion &
                                Beauty</span></a>
                    </li>
                    <li><a href="../explore/podcast.php"><i class="fas fa-podcast"></i><span>Podcasts</span></a></li>
                    <li><a href="../explore/playables.php"><i class="fas fa-gamepad"></i><span>Playables</span></a></li>
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

        <script src="../script.js"></script>
</body>

</html>