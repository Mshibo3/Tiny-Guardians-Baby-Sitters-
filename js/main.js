// Tiny Guardians Contact Form Logic

(function () {

// Show GitHub Pages notice when the site is served from github.io
var hostname = window.location.hostname;
var isGitHubPages = hostname === "github.io" || hostname.endsWith(".github.io");
if (isGitHubPages) {
  var notice = document.getElementById("ghPagesNotice");
  if (notice) notice.style.display = "block";
}

const form = document.getElementById("contactForm");
const status = document.getElementById("formStatus");

if (!form) return;

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showStatus(msg, ok) {
  if (!status) return;
  status.textContent = msg;
  status.style.display = "block";
  status.style.background = ok ? "#d4edda" : "#f8d7da";
  status.style.color = ok ? "#155724" : "#721c24";
  status.style.border = "1px solid " + (ok ? "#c3e6cb" : "#f5c6cb");
}

form.addEventListener("submit", function (e) {
  e.preventDefault();

  const name    = (document.getElementById("name")    || {}).value || "";
  const email   = (document.getElementById("email")   || {}).value || "";
  const phone   = (document.getElementById("phone")   || {}).value || "";
  const message = (document.getElementById("message") || {}).value || "";

  // Client-side validation (mirrors server-side checks)
  if (name.trim().length < 2) {
    showStatus("Please enter your full name.", false);
    return;
  }

  if (!isValidEmail(email.trim())) {
    showStatus("Enter a valid email address.", false);
    return;
  }

  if (phone.trim().length < 7) {
    showStatus("Enter a valid phone number.", false);
    return;
  }

  if (message.trim().length < 10) {
    showStatus("Message must be at least 10 characters.", false);
    return;
  }

  // If on GitHub Pages, PHP won't run — inform the user instead of submitting
  if (isGitHubPages) {
    showStatus(
      "The contact form requires a PHP server and is not available on GitHub Pages. " +
      "Please email us directly at tinyguardiansbabysitters@gmail.com.",
      false
    );
    return;
  }

  const submitBtn = form.querySelector("button[type='submit']");
  if (submitBtn) submitBtn.disabled = true;
  showStatus("Sending message…", true);

  const data = new FormData(form);

  fetch("send.php", {
    method: "POST",
    body: data
  })
    .then(function (res) {
      return res.text().then(function (text) {
        return { ok: res.ok, text: text };
      });
    })
    .then(function (result) {
      if (result.ok) {
        showStatus("✓ Message sent successfully! We will get back to you soon.", true);
        form.reset();
      } else {
        showStatus("Error: " + (result.text || "Failed to send message. Please try again."), false);
      }
    })
    .catch(function () {
      showStatus("Network error. Please check your connection and try again.", false);
    })
    .finally(function () {
      if (submitBtn) submitBtn.disabled = false;
    });
});

})();

// Hero slider functionality

const slides = document.querySelectorAll(".slide");

let currentSlide = 0;

function showNextSlide(){

slides[currentSlide].classList.remove("active");

currentSlide++;

if(currentSlide >= slides.length){
currentSlide = 0;
}

slides[currentSlide].classList.add("active");

}

setInterval(showNextSlide, 3000);

const toggle = document.getElementById("menuToggle");
const nav = document.getElementById("navLinks");

toggle.addEventListener("click", () => {
nav.classList.toggle("show");
});