<nav class="offcanvas-menu" id="offcanvasSidebar">
    <div class="menu-container">
        <div class="menu-head dashboard" onclick="window.location.href='../index.php';closeNav();">
            <i class="fa fa-home"></i>Home
        </div>


        <div class="menu-item">
            <div class="menu-head dashboard" onclick="window.location.href='../shorts.php';closeNav();">
                <i class="fa fa-play"></i>Shorts
            </div>
        </div>

        <hr>

        <div class="menu-item">
            <div class="menu-head dashboard" onclick="window.location.href='../you/profile.php';closeNav();">
                YOU <i class="fas fa-chevron-right arrow"></i>
            </div>
        </div>

        <div class="menu-item">
            <div class="menu-head dashboard" onclick="window.location.href='../you/history.php';closeNav();">
                <i class="fa fa-history"></i>History
            </div>
        </div>

        <div class="menu-item">
            <div class="menu-head dashboard" onclick="window.location.href='../you/playlist.php';closeNav();">
                <i class="fas fa-list"></i>Playlist
            </div>
        </div>

        <div class="menu-item">
            <div class="menu-head dashboard" onclick="window.location.href='../you/watch_later.php';closeNav();">
                <i class="fas fa-clock"></i>Watch Later
            </div>
        </div>

        <div class="menu-item">
            <div class="menu-head dashboard" onclick="window.location.href='../you/liked_videos.php';closeNav();">
                <i class="fas fa-thumbs-up"></i>Liked Videos
            </div>
        </div>

        <div class="menu-item">
            <div class="menu-head dashboard" onclick="window.location.href='../you/your_videos.php';closeNav();">
                <i class="fas fa-video"></i>Your Videos
            </div>
        </div>

        <div class="menu-item">
            <div class="menu-head dashboard" onclick="window.location.href='../you/downloads.php';closeNav();">
                <i class="fas fa-download"></i>Downloads
            </div>
        </div>

        <hr>

        <div class="menu-item">
            <div class="menu-head dashboard" onclick="window.location.href='explore.php';closeNav();">
                EXPLORE <i class="fas fa-chevron-right arrow"></i>
            </div>
        </div>

        <div class="menu-item">
            <div class="menu-head dashboard" onclick="window.location.href='shopping.php';closeNav();">
                <i class="fas fa-shopping-cart"></i>Shopping
            </div>
        </div>

        <div class="menu-item">
            <div class="menu-head dashboard" onclick="window.location.href='music.php';closeNav();">
                <i class="fas fa-music"></i>Music
            </div>
        </div>

        <div class="menu-item">
            <div class="menu-head dashboard" onclick="window.location.href='film.php';closeNav();">
                <i class="fas fa-film"></i>Film
            </div>
        </div>

        <div class="menu-item">
            <!-- The list comes first now -->
            <ul class="submenu" id="more-menu">
                <li><a href="live.php"><i class="fas fa-broadcast-tower"></i><span>Live</span></a>
                </li>
                <li><a href="news.php"><i class="fas fa-newspaper"></i><span>News</span></a></li>
                <li><a href="sports.php"><i class="fas fa-trophy"></i><span>Sports</span></a></li>
                <li><a href="#courses"><i class="fas fa-book-open"></i><span>Courses</span></a></li>
                <li><a href="fashion_beauty.php"><i class="fas fa-tshirt"></i><span>Fashion &
                            Beauty</span></a>
                </li>
                <li><a href="podcast.php"><i class="fas fa-podcast"></i><span>Podcasts</span></a></li>
                <li><a href="playables.php"><i class="fas fa-gamepad"></i><span>Playables</span></a></li>
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

    </div>
</nav>
<div id="overlay" onclick="closeNav()"></div>