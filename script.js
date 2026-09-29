/* BASIC PORTFOLIO JAVASCRIPT */

/* Mobile menu */
var menu = document.getElementById("menu");
var hamburger = document.getElementById("hamburger");

if (menu && hamburger) {
    hamburger.onclick = function () {
        menu.classList.toggle("open");

        if (menu.classList.contains("open")) {
            hamburger.setAttribute("aria-expanded", "true");
        } else {
            hamburger.setAttribute("aria-expanded", "false");
        }
    };

    var links = menu.getElementsByTagName("a");

    for (var i = 0; i < links.length; i++) {
        links[i].onclick = function () {
            menu.classList.remove("open");
            hamburger.setAttribute("aria-expanded", "false");
        };
    }
}

/* Active navigation */
var page = location.pathname.split("/").pop();

if (page === "") {
    page = "index.html";
}

var navLinks = document.querySelectorAll(".menu a");

for (var i = 0; i < navLinks.length; i++) {
    if (navLinks[i].getAttribute("href") === page) {
        navLinks[i].classList.add("active");
    }
}

/* Simple typing effect */
var typingText = document.getElementById("homeRole");

if (typingText) {
    var text = typingText.getAttribute("data-text");
    var number = 0;

    typingText.textContent = "";

    var typing = setInterval(function () {
        typingText.textContent = text.substring(0, number + 1) + "|";
        number++;

        if (number === text.length) {
            clearInterval(typing);
        }
    }, 80);
}

/* Project search and filter */
var searchBox = document.getElementById("projectSearch");
var filterButtons = document.querySelectorAll(".filter-btn");
var projects = document.querySelectorAll(".project");

function showProjects() {
    var search = "";
    var category = "All";
    var shown = 0;

    if (searchBox) {
        search = searchBox.value.toLowerCase();
    }

    for (var i = 0; i < filterButtons.length; i++) {
        if (filterButtons[i].classList.contains("active")) {
            category = filterButtons[i].getAttribute("data-filter");
        }
    }

    for (var i = 0; i < projects.length; i++) {
        var projectText = projects[i].textContent.toLowerCase();
        var projectCategory = projects[i].getAttribute("data-category");

        var matchesSearch = projectText.indexOf(search) !== -1;
        var matchesCategory = category === "All" || projectCategory === category;

        if (matchesSearch && matchesCategory) {
            projects[i].classList.remove("hidden");
            shown++;
        } else {
            projects[i].classList.add("hidden");
        }
    }

    var result = document.getElementById("resultCount");

    if (result) {
        result.textContent = shown + " project(s) found";
    }
}

for (var i = 0; i < filterButtons.length; i++) {
    filterButtons[i].onclick = function () {
        for (var j = 0; j < filterButtons.length; j++) {
            filterButtons[j].classList.remove("active");
        }

        this.classList.add("active");
        showProjects();
    };
}

if (searchBox) {
    searchBox.oninput = showProjects;
}

if (filterButtons.length > 0) {
    filterButtons[0].classList.add("active");
    showProjects();
}

/* Dark and light mode */
var themeButton = document.getElementById("themeButton");
var themeCircle = document.getElementById("themeCircle");
var changingTheme = false;

function saveTheme(theme) {
    localStorage.setItem("theme", theme);
}

if (themeButton && themeCircle) {
    if (localStorage.getItem("theme") === "light") {
        document.body.classList.add("light");
    }

    themeButton.textContent = "Dark | Light";

    themeButton.onclick = function () {
        if (changingTheme) {
            return;
        }

        var lightMode = !document.body.classList.contains("light");

        saveTheme(lightMode ? "light" : "dark");

        if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
            document.body.classList.toggle("light");
            return;
        }

        var box = themeButton.getBoundingClientRect();
        var x = box.left + box.width / 2;
        var y = box.top + box.height / 2;
        var size = Math.max(window.innerWidth, window.innerHeight) * 2;

        if (lightMode) {
            document.body.style.background = "#0d0e12";
            themeCircle.style.background = "#dcdde0";
        } else {
            document.body.style.background = "#dcdde0";
            themeCircle.style.background = "#0d0e12";
        }

        changingTheme = true;

        themeCircle.style.left = x + "px";
        themeCircle.style.top = y + "px";
        themeCircle.style.width = size + "px";
        themeCircle.style.height = size + "px";

        document.body.classList.add("switching");
        themeCircle.classList.add("grow");

        setTimeout(function () {
            document.body.classList.toggle("light");
        }, 400);

        setTimeout(function () {
            document.body.style.background = "";
            document.body.classList.remove("switching");
            themeCircle.classList.remove("grow");
            changingTheme = false;
        }, 850);
    };
}

/* Skill descriptions */
var skillCards = document.querySelectorAll(".skill-card");

for (var i = 0; i < skillCards.length; i++) {
    skillCards[i].onclick = function () {
        this.classList.toggle("open");
    };
}

/* Static contact form */
var form = document.getElementById("contactForm");

if (form) {
    var message = document.getElementById("message");
    var count = document.getElementById("msgCount");

    form.onsubmit = function (event) {
        event.preventDefault();

        var name = document.getElementById("name").value.trim();
        var email = document.getElementById("email").value.trim();
        var messageText = message.value.trim();
        var valid = true;

        document.getElementById("nameError").textContent = "";
        document.getElementById("emailError").textContent = "";
        document.getElementById("messageError").textContent = "";
        document.getElementById("formSuccess").textContent = "";

        if (name === "") {
            document.getElementById("nameError").textContent = "Please enter your name.";
            valid = false;
        }

        if (email === "" || email.indexOf("@") === -1 || email.indexOf(".") === -1) {
            document.getElementById("emailError").textContent = "Please enter a valid email address.";
            valid = false;
        }

        if (messageText.length < 10) {
            document.getElementById("messageError").textContent =
                "Your message needs at least 10 characters.";
            valid = false;
        }

        if (valid) {
            document.getElementById("formSuccess").textContent =
                "Thanks, " + name + "! Your message was entered successfully.";

            form.reset();
            count.textContent = "0 / 10 min";
        }
    };

    message.oninput = function () {
        var length = message.value.trim().length;
        count.textContent = length + " / 10 min";
    };
}
