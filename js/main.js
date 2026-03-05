// Tiny Guardians Contact Form Logic

(function () {

const form = document.getElementById("contactForm");
const status = document.getElementById("formStatus");

if(!form) return;

function isValidEmail(email){
return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

form.addEventListener("submit", function(e){

e.preventDefault();

const name = document.getElementById("name").value.trim();
const email = document.getElementById("email").value.trim();
const phone = document.getElementById("phone").value.trim();
const message = document.getElementById("message").value.trim();

if(name.length < 2){
status.textContent = "Please enter your full name.";
return;
}

if(!isValidEmail(email)){
status.textContent = "Enter a valid email address.";
return;
}

if(phone.length < 7){
status.textContent = "Enter a valid phone number.";
return;
}

if(message.length < 10){
status.textContent = "Message must be at least 10 characters.";
return;
}

status.textContent = "Sending message...";

emailjs.send("YOUR_SERVICE_ID","YOUR_TEMPLATE_ID",{
name: name,
email: email,
phone: phone,
message: message,
to_email: "tinyguardiansbabysitters@gmail.com"
})

.then(function(){

status.textContent = "Message sent successfully!";
form.reset();

})

.catch(function(){

status.textContent = "Failed to send message. Please try again.";

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