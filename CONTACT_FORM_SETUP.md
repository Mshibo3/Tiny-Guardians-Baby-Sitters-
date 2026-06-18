# Contact Form Setup Guide

This guide explains how to make the **Send Message** button on the *Contact Us* page send real emails to `tinyguardiansbabysitters@gmail.com`.

---

## Why GitHub Pages cannot run the form

GitHub Pages serves only **static files** (HTML, CSS, JavaScript, images).  
It has no server-side runtime — PHP is never executed; the browser just receives the `.php` file as plain text.

> **The contact form will only send emails once the site is hosted on a cPanel (PHP-capable) server.**

While the site is still on GitHub Pages, a notice is shown on the contact page and the form is blocked from submitting — visitors are directed to the Gmail address instead.

---

## Files added to the repository

| File | Purpose |
|------|---------|
| `send.php` | PHP backend that receives the form POST, validates input, and sends the email |
| `CONTACT_FORM_SETUP.md` | This file |

`contact.html` and `js/main.js` have also been updated to submit the form to `send.php` and show user-friendly feedback.

---

## Deploying to cPanel (step-by-step)

### 1. Log in to cPanel
Go to `https://yourdomain.com/cpanel` (or the URL your host provides).

### 2. Upload the site files
Use **File Manager** or an FTP client (e.g. FileZilla):

- Upload **all** repository files into `public_html/` (the web root).
- Your structure should look like:

```
public_html/
├── contact.html
├── index.html
├── about.html
├── send.php          ← new
├── css/
├── js/
└── images/
```

### 3. Choose an email sending method

#### Option A — Gmail SMTP (recommended for reliability)

1. **Enable 2-Step Verification** on your Google account:  
   <https://myaccount.google.com/security>

2. **Create a Gmail App Password**:
   - Go to <https://myaccount.google.com/apppasswords>
   - App: *Mail* → Device: *Other (custom name)* → type "Tiny Guardians cPanel"
   - Copy the 16-character password shown (e.g. `abcd efgh ijkl mnop`).

3. **Edit `send.php`** on cPanel (File Manager → right-click → Edit):
   ```php
   define('SMTP_ENABLED',  true);                  // ← change false → true
   define('SMTP_HOST',     'smtp.gmail.com');
   define('SMTP_PORT',     587);
   define('SMTP_SECURITY', 'tls');
   define('SMTP_USERNAME', 'tinyguardiansbabysitters@gmail.com');
   define('SMTP_PASSWORD', 'abcd efgh ijkl mnop'); // ← your App Password
   ```
   Also update:
   ```php
   define('FROM_EMAIL', 'tinyguardiansbabysitters@gmail.com');
   define('FROM_NAME',  'Tiny Guardians Website');
   ```

4. **Install PHPMailer** (required for SMTP):

   **Method 1 — Composer (easiest if your host supports it):**
   ```bash
   # SSH into cPanel or use the Terminal tool
   cd ~/public_html
   composer require phpmailer/phpmailer
   ```

   **Method 2 — Manual upload (PHPMailer 6.x):**
   - Download the latest **6.x** release from <https://github.com/PHPMailer/PHPMailer/releases>
   - Extract and upload only these three files into `public_html/vendor/phpmailer/`:
     - `src/PHPMailer.php` → `vendor/phpmailer/PHPMailer.php`
     - `src/SMTP.php`     → `vendor/phpmailer/SMTP.php`
     - `src/Exception.php`→ `vendor/phpmailer/Exception.php`

#### Option B — PHP `mail()` (simpler, but less reliable)

Leave `SMTP_ENABLED` as `false` in `send.php` and update:

```php
define('FROM_EMAIL', 'noreply@yourdomain.com'); // ← a cPanel email account you created
define('FROM_NAME',  'Tiny Guardians Website');
```

Create that email account in cPanel → **Email Accounts** before testing.  
`mail()` works on most shared hosts but some block outgoing mail or deliver to spam — use SMTP if emails are not arriving.

---

## Testing the form on cPanel

1. Open your live site and go to the **Contact Us** page.
2. Fill in all fields and click **Send Message**.
3. You should see a green **"✓ Message sent successfully!"** banner on the page.
4. Check `tinyguardiansbabysitters@gmail.com` — the email should arrive within a minute.
5. If something goes wrong, check cPanel → **Error Logs** for PHP errors.

---

## Security features built into `send.php`

| Feature | What it does |
|---------|-------------|
| **Input validation** | Name, email (format-checked), phone, and message are all validated before the email is sent |
| **Length limits** | Name ≤ 100 chars, email ≤ 254, phone ≤ 30, message ≤ 3 000 — prevents oversized payloads |
| **Honeypot field** | A hidden `<input name="website">` is injected into the form; bots fill it, humans don't — bot requests are silently discarded |
| **Session rate limit** | Maximum 5 submissions per 10 minutes per session/IP — prevents spam floods |
| **Reply-To header** | Replies go to the visitor's email, not to the server's From address |
| **Error log (not exposed)** | Real error details are written to the PHP error log, not shown to visitors |

---

## Troubleshooting

| Symptom | Likely cause | Fix |
|---------|-------------|-----|
| Form submits but no email arrives | `mail()` blocked by host | Switch to SMTP (Option A) |
| "Failed to send message" error | SMTP credentials wrong | Double-check App Password; re-check host/port |
| "PHPMailer library not found" | `vendor/` folder missing | Install PHPMailer (see above) |
| Form works but email lands in spam | `FROM_EMAIL` is not a real domain address | Use a cPanel email account as `FROM_EMAIL` |
| 404 on `send.php` | File not uploaded | Upload `send.php` to `public_html/` |

---

## Need help?

Email: **tinyguardiansbabysitters@gmail.com**  
WhatsApp: **+27 81 667 9789**
