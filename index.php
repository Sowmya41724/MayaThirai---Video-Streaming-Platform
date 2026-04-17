<?php
include "config.php";
session_start();

$is_logged_in = isset($_SESSION["id"]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
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
            <img src="images/logo-final.png" alt="logo" class="logo-img">
        </div>
        <div style="align-items: center;">
            <form class="example" action="/action_page.php">
                <input type="text" placeholder="Search.." name="search">
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>
        </div>
        <div class="header-right">
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
                        <a href="profile.php">Profile</a>
                        <a href="settings.php">Settings</a>
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

            <hr>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='You/profile.php';">
                    YOU <i class="fas fa-chevron-right arrow"></i>
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='You/history.php';">
                    <i class="fa fa-history"></i>History
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='You/playlist.php';">
                    <i class="fas fa-list"></i>Playlist
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='You/watch_later.php';">
                    <i class="fas fa-clock"></i>Watch Later
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='You/liked_videos.php';">
                    <i class="fas fa-thumbs-up"></i>Liked Videos
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='You/your_videos.php';">
                    <i class="fas fa-video"></i>Your Videos
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='You/downloads.php';">
                    <i class="fas fa-download"></i>Downloads
                </div>
            </div>

            <hr>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='explore.php';">
                    EXPLORE <i class="fas fa-chevron-right arrow"></i>
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='You/liked_videos.php';">
                    <i class="fas fa-shopping-cart"></i>Shopping
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='You/your_videos.php';">
                    <i class="fas fa-music"></i>Music
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-head dashboard" onclick="window.location.href='You/downloads.php';">
                    <i class="fas fa-film"></i>Film
                </div>
            </div>

            <div class="menu-item">
                <!-- The list comes first now -->
                <ul class="submenu" id="more-menu">
                    <li><a href="#"><i class="fas fa-broadcast-tower"></i><span>Live</span></a></li>
                    <li><a href="#"><i class="fas fa-newspaper"></i><span>News</span></a></li>
                    <li><a href="#"><i class="fas fa-trophy"></i><span>Sports</span></a></li>
                    <li><a href="#"><i class="fas fa-book-open"></i><span>Courses</span></a></li>
                    <li><a href="#"><i class="fas fa-tshirt"></i><span>Fashion & Beauty</span></a></li>
                    <li><a href="#"><i class="fas fa-podcast"></i><span>Podcasts</span></a></li>
                    <li><a href="#"><i class="fas fa-gamepad"></i><span>Playables</span></a></li>
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
            <div class="category-wrapper">
                <button class="scroll-btn left" onclick="scrollChips(-200)">❮</button>

                <div class="categories" id="chipContainer">
                    <button class="chip active">All</button>
                    <button class="chip">Music</button>
                    <button class="chip">Movies</button>
                    <button class="chip">Tamil</button>
                    <button class="chip">Comedy</button>
                    <button class="chip">Devotional</button>
                    <button class="chip">Drama</button>
                    <button class="chip">Action</button>
                    <button class="chip">AI</button>
                    <button class="chip">Tech</button>
                    <button class="chip">Stand-Up</button>
                    <button class="chip">Tamil Movies</button>
                    <button class="chip">Dance</button>
                    <button class="chip">Series</button>
                    <button class="chip">Frontend</button>
                    <button class="chip">ASMR</button>
                    <button class="chip">Java</button>
                    <button class="chip">Mic-Set</button>
                    <button class="chip">Programming</button>
                    <button class="chip">Unboxing</button>
                    <button class="chip end">C-Drama</button>
                </div>

                <button class="scroll-btn right" onclick="scrollChips(200)">❯</button>
            </div>

            <div class="video-grid">

                <div onclick="window.location.href='You/history.php';" class="video-card" data-video-id="EPB6UyaJkGk ">
                    <img src="https://img.youtube.com/vi/EPB6UyaJkGk/0.jpg" alt="Video Thumbnail" />
                    <div class="video-info">
                        <h4>Video Title</h4>
                        <p>Channel Name</p>
                        <span>1M views • 2 days ago</span>
                    </div>
                </div>
                <div onclick="window.location.href='You/history.php';" class="video-card" data-video-id="Ih7bldj2nJE ">
                    <img src="https://img.youtube.com/vi/Ih7bldj2nJE/0.jpg" alt="Video Thumbnail" />
                    <div class="video-info">
                        <h4>Video Title</h4>
                        <p>Channel Name</p>
                        <span>1M views • 2 days ago</span>
                    </div>
                </div>
                <div onclick="window.location.href='You/history.php';" class="video-card" data-video-id="-pGdzIi9Lmg ">
                    <img src="https://img.youtube.com/vi/-pGdzIi9Lmg/0.jpg" alt="Video Thumbnail" />
                    <div class="video-info">
                        <h4>Video Title</h4>
                        <p>Channel Name</p>
                        <span>1M views • 2 days ago</span>
                    </div>
                </div>
                <div onclick="window.location.href='You/history.php';" class="video-card" data-video-id="gfKVnjoBD3c ">
                    <img src="https://img.youtube.com/vi/gfKVnjoBD3c/0.jpg" alt="Video Thumbnail" />
                    <div class="video-info">
                        <h4>Video Title</h4>
                        <p>Channel Name</p>
                        <span>1M views • 2 days ago</span>
                    </div>
                </div>
                <div onclick="window.location.href='You/history.php';" class="video-card" data-video-id="2Fa1Il-3k88 ">
                    <img src="https://img.youtube.com/vi/2Fa1Il-3k88/0.jpg" alt="Video Thumbnail" />
                    <div class="video-info">
                        <h4>Video Title</h4>
                        <p>Channel Name</p>
                        <span>1M views • 2 days ago</span>
                    </div>
                </div>
                <div onclick="window.location.href='You/history.php';" class="video-card" data-video-id="35npVaFGHMY ">
                    <img src="https://img.youtube.com/vi/35npVaFGHMY/0.jpg" alt="Video Thumbnail" />
                    <div class="video-info">
                        <h4>Video Title</h4>
                        <p>Channel Name</p>
                        <span>1M views • 2 days ago</span>
                    </div>
                </div>
                <div onclick="window.location.href='You/history.php';" class="video-card" data-video-id="C6UKeUSXpQw">
                    <img src="https://img.youtube.com/vi/C6UKeUSXpQw/0.jpg" alt="Video Thumbnail" />
                    <div class="video-info">
                        <h4>Video Title</h4>
                        <p>Channel Name</p>
                        <span>1M views • 2 days ago</span>
                    </div>
                </div>
                <div onclick="window.location.href='You/history.php';" class="video-card" data-video-id="p8mXAQ6cPxg ">
                    <img src="https://img.youtube.com/vi/p8mXAQ6cPxg/0.jpg" alt="Video Thumbnail" />
                    <div class="video-info">
                        <h4>Video Title</h4>
                        <p>Channel Name</p>
                        <span>1M views • 2 days ago</span>
                    </div>
                </div>
                <div onclick="window.location.href='You/history.php';" class="video-card" data-video-id="98kYg52aQeY ">
                    <img src="https://img.youtube.com/vi/98kYg52aQeY/0.jpg" alt="Video Thumbnail" />
                    <div class="video-info">
                        <h4>Video Title</h4>
                        <p>Channel Name</p>
                        <span>1M views • 2 days ago</span>
                    </div>
                </div>
                <div onclick="window.location.href='You/history.php';" class="video-card" data-video-id="klFLX-g71TE ">
                    <img src="https://img.youtube.com/vi/klFLX-g71TE/0.jpg" alt="Video Thumbnail" />
                    <div class="video-info">
                        <h4>Video Title</h4>
                        <p>Channel Name</p>
                        <span>1M views • 2 days ago</span>
                    </div>
                </div>
                <div onclick="window.location.href='You/history.php';" class="video-card" data-video-id="Igs0u3_-HEk ">
                    <img src="https://img.youtube.com/vi/Igs0u3_-HEk/0.jpg" alt="Video Thumbnail" />
                    <div class="video-info">
                        <h4>Video Title</h4>
                        <p>Channel Name</p>
                        <span>1M views • 2 days ago</span>
                    </div>
                </div>
                <div onclick="window.location.href='You/history.php';" class="video-card" data-video-id="efEiBG7DLB4 ">
                    <img src="https://img.youtube.com/vi/efEiBG7DLB4/0.jpg" alt="Video Thumbnail" />
                    <div class="video-info">
                        <h4>Video Title</h4>
                        <p>Channel Name</p>
                        <span>1M views • 2 days ago</span>
                    </div>
                </div>
                <div onclick="window.location.href='You/history.php';" class="video-card" data-video-id="IhJa4YysIfY ">
                    <img src="https://img.youtube.com/vi/IhJa4YysIfY/0.jpg" alt="Video Thumbnail" />
                    <div class="video-info">
                        <h4>Video Title</h4>
                        <p>Channel Name</p>
                        <span>1M views • 2 days ago</span>
                    </div>
                </div>
                <div onclick="window.location.href='You/history.php';" class="video-card" data-video-id="uU4dMlPqtyk ">
                    <img src="https://img.youtube.com/vi/uU4dMlPqtyk/0.jpg" alt="Video Thumbnail" />
                    <div class="video-info">
                        <h4>Video Title</h4>
                        <p>Channel Name</p>
                        <span>1M views • 2 days ago</span>
                    </div>
                </div>
                <div onclick="window.location.href='You/history.php';" class="video-card" data-video-id="qhAg6kFmlN7w ">
                    <img src="https://img.youtube.com/vi/hAg6kFmlN7w/0.jpg" alt="Video Thumbnail" />
                    <div class="video-info">
                        <h4>Video Title</h4>
                        <p>Channel Name</p>
                        <span>1M views • 2 days ago</span>
                    </div>
                </div>
                <div onclick="window.location.href='You/history.php';" class="video-card" data-video-id="06VCO1Y-CZk ">
                    <img src="https://img.youtube.com/vi/06VCO1Y-CZk/0.jpg" alt="Video Thumbnail" />
                    <div class="video-info">
                        <h4>Video Title</h4>
                        <p>Channel Name</p>
                        <span>1M views • 2 days ago</span>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
</body>

</html>