(function () {
  const yearEl = document.getElementById("year");
  if (yearEl) yearEl.textContent = String(new Date().getFullYear());

  const form = document.getElementById("contactForm");
  if (!form) return;

  const status = document.getElementById("formStatus");

  function setError(fieldName, message) {
    const el = document.querySelector(`[data-error-for="${fieldName}"]`);
    if (el) el.textContent = message || "";
  }

  function isValidEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
  }

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    ["name", "email", "phone", "message"].forEach((k) => setError(k, ""));
    if (status) status.textContent = "";

    const name = form.elements.namedItem("name").value.trim();
    const email = form.elements.namedItem("email").value.trim();
    const phone = form.elements.namedItem("phone").value.trim();
    const message = form.elements.namedItem("message").value.trim();

    let ok = true;

    if (name.length < 2) { setError("name", "Name must be at least 2 characters."); ok = false; }
    if (!isValidEmail(email)) { setError("email", "Enter a valid email address."); ok = false; }
    if (phone && phone.length < 7) { setError("phone", "Phone looks too short."); ok = false; }
    if (message.length < 10) { setError("message", "Message must be at least 10 characters."); ok = false; }

    if (!ok) { if (status) status.textContent = "Fix the errors above and try again."; return; }

    if (status) status.textContent = "Message validated. (No email sent — add backend later.)";
    form.reset();
  });
})();
