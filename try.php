<html>

<head>
    <style>
        body {
            background-color: black;
        }

        /* Wrapper */
        .category-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        /* Scroll container */
        .categories {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            scroll-behavior: smooth;
            padding: 10px;
        }

        /* Hide scrollbar */
        .categories::-webkit-scrollbar {
            display: none;
        }

        /* Chips */
        .chip {
            padding: 8px 14px;
            border-radius: 20px;
            border: none;
            background: #eee;
            cursor: pointer;
            white-space: nowrap;
        }

        /* Active chip */
        .chip.active {
            background: #efbf04;
            color: black;
        }

        /* Scroll buttons */
        .scroll-btn {
            position: absolute;
            background: white;
            border: none;
            font-size: 20px;
            cursor: pointer;
            height: 100%;
            width: 40px;
            z-index: 1;
        }

        .scroll-btn.left {
            left: 0;
        }

        .scroll-btn.right {
            right: 0;
        }
    </style>

</head>

<body>
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
            <button class="chip">All</button>
            <button class="chip">Music</button>
            <button class="chip">Movies</button>
            <button class="chip">Tamil</button>
            <button class="chip">Comedy</button>
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
            <button class="chip">C-Drama</button>
        </div>

        <button class="scroll-btn right" onclick="scrollChips(200)">❯</button>
    </div>
    <div onclick="window.location.href='You/history.php';" class="video-card" data-video-id="EPB6UyaJkGk ">
        <img src="https://img.youtube.com/vi/EPB6UyaJkGk/0.jpg" alt="Video Thumbnail" />
        <div class="video-info">
            <h4>Video Title</h4>
            <p>Channel Name</p>
            <span>1M views • 2 days ago</span>
        </div>
    </div>
    <script>
        function scrollChips(amount) {
            const container = document.getElementById("chipContainer");
            container.scrollBy({
                left: amount,
                behavior: "smooth"
            });
        }
    </script>
</body>

</html>