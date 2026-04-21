function toggleSubmenu(btn) {
  const menu = document.getElementById("more-menu");
  const text = document.getElementById("toggleText");

  menu.classList.toggle("open");
  btn.classList.toggle("active"); // Rotates the arrow

  if (menu.classList.contains("open")) {
    text.innerText = "Show Less";
  } else {
    text.innerText = "Show More";
  }
}

const themeSwitch = document.getElementById("themeSwitch");

// Check saved preference
const savedTheme = localStorage.getItem("mayathirai-theme");
if (savedTheme === "light") {
  document.body.classList.add("light-mode");
  themeSwitch.checked = true;
}

// Toggle theme
themeSwitch.addEventListener("change", () => {
  if (themeSwitch.checked) {
    document.body.classList.add("light-mode");
    localStorage.setItem("mayathirai-theme", "light");
  } else {
    document.body.classList.remove("light-mode");
    localStorage.setItem("mayathirai-theme", "dark");
  }
});

function scrollChips(amount) {
  const container = document.getElementById("chipContainer");
  container.scrollBy({
    left: amount,
    behavior: "smooth",
  });
}

function toggleNav() {
  document.getElementById("mySidebar").style.width = "200px";
  document.getElementById("menu").style.marginLeft = "200px";

  menu.addEventListener("click", () => {
    document.body.classList.toggle("close");
    document.body.classList.toggle("open");

    if (document.body.classList.contains("close")) {
      menu;
      localStorage.setItem("theme", "dark");
    } else {
      icon.textContent = "🌙";
      localStorage.setItem("theme", "light");
    }
  });
}

$(document).ready(function () {
  $("menu").click(function () {
    $("menu").toggle();
  });
});

$(document).ready(function () {
  $(".menu-toggle").click(function () {
    $(this).toggleClass("active");
  });
});

function myFunction() {
  var x = document.getElementById("mySidebar");
  if (x.style.display === "none") {
    x.style.display = "block";
  } else {
    x.style.display = "none";
  }
}

/* ---------- Setting ------------- */
function openNav() {
  document.getElementById("offcanvasSidebar").style.width = "250px";
  document.getElementById("overlay").style.display = "block";
  document.body.classList.add("menu-open");
}

function closeNav() {
  document.getElementById("offcanvasSidebar").style.width = "0";
  document.getElementById("overlay").style.display = "none";
  document.body.classList.remove("menu-open");
}
