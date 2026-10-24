# StudentHub - Campus Portal Web Application
**Subject:** ITUE203 - Web Development Frameworks  
**Faculty:** Faculty of Technology and Engineering (FTE)  
**University:** Charotar University of Science and Technology (CHARUSAT)  
**Semester:** 3rd Semester, 2026-27 (ODD)  
**Scope:** Complete implementation of **Practicals 2 through 15** & Full-Stack Portal based on the Provided 10-Page Wireframe Specifications.

---

## 📌 1. Project Overview & Wireframe Architecture

The **StudentHub** web portal is an integrated full-stack campus platform developed for students and faculty of CHARUSAT FTE. It coordinates event registrations, technical hackathons, academic announcements, and student profiling, paired with an administrative monitoring console.

### Wireframe Specifications Implemented (10 Core Pages):
1. **Home (`pages/index.php`)**: Header (logo + nav), Hero banner with "Explore events" CTA, Highlight cards (events, notices, clubs), Image carousel slider, and Footer.
2. **About (`pages/about.php`)**: FTE portal description, mission, vision, course coordinator details, and student clubs showcase.
3. **Events Listing (`pages/events.php`)**: Search bar, category filter, date sorting, dynamic event cards (poster, title, date, venue, seat count), and pagination.
4. **Event Details (`pages/event_detail.php`)**: Poster banner, metadata, full description text, seats remaining counter, and one-click Register button.
5. **Register (`pages/register.php`)**: Full name, college email, mobile number, password, confirm password, course, year, gender, terms checkbox, password strength meter, and HTML5 Canvas CAPTCHA.
6. **Login (`pages/login.php`)**: Email, password, remember-me token checkbox, role-based redirection, timeout notification, and forgot password link.
7. **Student Dashboard (`pages/student_dashboard.php`)**: User avatar, quick links (my events, notices, profile), registered events list with cancellation action, and campus notices.
8. **Profile (`pages/profile.php`)**: Avatar, full name, email, mobile, department/course, semester year, address, bio, and Save changes button.
9. **Contact & Feedback (`pages/contact.php`)**: Name, email, subject, message textarea, college campus address/phone/email, and server-side CSV/JSON storage.
10. **Admin Dashboard (`pages/admin_dashboard.php`)**: Sidebar (Students, Events, Notices, Audit logs), Top metrics cards (Students, Events, Registrations, New Today), HTML5 Canvas analytics chart, and recent registrations table.

---

## 🧪 2. Practical Summary & CO Mapping (Practicals 2 to 15)

| Practical No. | Practical Title | Core Technology & Skills | CO Mapping | Implementation Status |
|:---:|:---|:---|:---:|:---:|
| **2** | Semantic HTML5 Pages with Accessibility-Ready Structure | Semantic HTML5, ARIA, Accessibility, Breadcrumbs, Skip-Link | CO1, CO2 | ✅ Implemented |
| **3** | Responsive UI Design using CSS Grid and Flexbox | CSS Grid, Flexbox, Mobile-First, Media Queries | CO2 | ✅ Implemented |
| **4** | JavaScript DOM Manipulation, Event Handling & Interactivity | DOM APIs, Theme Switcher, Carousel, FAQ Accordion, Modals | CO3, CO4 | ✅ Implemented |
| **5** | Registration Form with Frontend Validation & UX Error Handling | RegEx validation, Password meter, Canvas CAPTCHA | CO1, CO3 | ✅ Implemented |
| **6** | Rendering External JSON Data using Fetch API, Search & Filter | JSON, Fetch API, Filter/Sort/Pagination, Offline Caching | CO3, CO4 | ✅ Implemented |
| **7** | PHP Form Processing with Server-Side Validation & CSV/JSON Storage | POST handling, sanitization, file locking (`LOCK_EX`), CSRF | CO1, CO5 | ✅ Implemented |
| **8** | MySQL Schema Design, ER Model, PDO Connectivity & Stored Procedures | 3NF Schema, Foreign Keys, PDO & MySQLi, Stored Procedures | CO1, CO5 | ✅ Implemented |
| **9** | Secure User Registration with Database Insert & Password Hashing | `password_hash()`, duplicate check, SQL prepared statements | CO1, CO3, CO5 | ✅ Implemented |
| **10** | Secure Login/Logout with Sessions, RBAC, Timeout & Remember-Me | `password_verify()`, session timeout, persistent token cookie | CO1, CO5 | ✅ Implemented |
| **11** | Student Management CRUD with Search, Filter & MySQLi | SELECT/INSERT/UPDATE/DELETE, CSV export, Soft delete | CO1, CO4, CO5 | ✅ Implemented |
| **12** | Event Management CRUD with Poster Upload & File Validation | MIME verification, file size cap (2MB), safe renaming | CO1, CO4, CO5 | ✅ Implemented |
| **13** | PHP JSON API and AJAX/Fetch-Based CRUD without Page Reload | REST JSON API, Fetch API, Debounced search, Async DOM | CO3, CO4, CO5 | ✅ Implemented |
| **14** | Admin Dashboard with Role-Based Access Control & Audit Log | RBAC guards, SQL aggregation, Canvas chart, Audit trail | CO1, CO4, CO5 | ✅ Implemented |
| **15** | Final Integration, Testing, Documentation & Local Deployment | Full-stack deployment, test matrices, viva defense | CO2, CO4, CO5 | ✅ Implemented |

---

## 🗄️ 3. Database Schema (`studenthub_db`)

The relational database is normalized to 3NF and includes relational integrity constraints:
- `users`: ID, full_name, email, password_hash, role (`student`, `admin`), status, remember_token, last_login, created_at.
- `students`: ID, user_id (FK), student_id_number, full_name, email, mobile, course, year_of_study, gender, address, bio, profile_pic, status (`active`, `inactive`, `deleted`).
- `events`: ID, title, category, event_date, event_time, venue, description, poster_url, max_seats, registered_count, status (`upcoming`, `ongoing`, `completed`, `cancelled`).
- `registrations`: ID, user_id (FK), student_id, event_id (FK), student_name, student_email, status, registration_date.
- `notices`: ID, title, category, content, priority (`normal`, `urgent`), posted_date.
- `audit_logs`: ID, user_id, user_role, action, details, ip_address, created_at.
- `contact_messages`: ID, name, email, subject, message, status, created_at.

### Stored Procedures:
1. `sp_GetStudentRegistrationSummary(IN p_user_id INT)`: Fetches student event registrations and details.
2. `sp_RegisterStudentForEvent(IN p_user_id, IN p_event_id, ...)`: Validates seat availability and registers student atomically.

---

## 🚀 4. Installation & Local Deployment Guide

### Prerequisites:
- PHP 8.0+ (Installed with XAMPP or PHP CLI)
- MySQL / MariaDB (Running on standard port 3306)
- Modern web browser (Chrome, Edge, Firefox)

### Step 1: Start MySQL in XAMPP
Open the **XAMPP Control Panel** and click **Start** next to **MySQL**.

### Step 2: Initialize Database (One-Click Setup)
Navigate to the project root and run the setup script:
```bash
# Via PHP CLI:
php setup_database.php

# OR open in browser:
http://localhost/studenthub/setup_database.php
```
This automatically executes `data/database.sql`, creates `studenthub_db`, all 7 tables, stored procedures, and seeds:
- 21 Student profiles (for Practical 11)
- 15 Campus events (for Practical 6 & 12)
- Campus announcements and audit log history

### Step 3: Launch Local Web Server
You can run the portal using either option:

#### Option A: PHP Built-in Server (Recommended)
```bash
cd C:\Users\HELLO\.gemini\antigravity\scratch\studenthub
php -S localhost:8000
```
Then visit: **`http://localhost:8000`**

#### Option B: XAMPP Apache
Copy the folder to `C:\xampp\htdocs\studenthub` (already synced) and open:
**`http://localhost/studenthub`**

---

## 🔑 5. Default Demonstration Credentials

| Role | Email Address | Password | Landing Page |
|:---|:---|:---|:---|
| **System Administrator** | `admin@charusat.edu.in` | `Admin@123` | `pages/admin_dashboard.php` |
| **Registered Student** | `aarav.sharma@charusat.edu.in` | `Student@123` | `pages/student_dashboard.php` |

*(You can also use the one-click credential fill buttons directly on the Login page).*

---

## 🛡️ 6. Security Features Implemented

1. **Password Hashing:** Passwords are never stored in plain text. Hashed using `password_hash($password, PASSWORD_BCRYPT)` with cost factor 10.
2. **SQL Injection Defense:** All queries use parameterized prepared statements (`PDO::prepare()` and `mysqli::prepare()`).
3. **Cross-Site Request Forgery (CSRF):** Forms include cryptographically secure, randomized CSRF tokens verified on POST.
4. **Session Fixation Defense:** `session_regenerate_id(true)` called upon login to prevent session hijacking.
5. **Session Inactivity Timeout:** Automatic logout triggered after 15 minutes of inactivity.
6. **File Upload Security:** Uploaded event posters are validated using `finfo_file()` for MIME types (JPG, PNG, WebP), checked against 2MB limits, safely renamed using `uniqid()`, and protected from direct directory listing.
7. **Traceability:** Sensitive operations (logins, registrations, deletions, timeouts) are timestamped and logged in `audit_logs`.

---

## 🎓 7. Laboratory Examination & Viva Voce Q&A Cheat Sheet

1. **Q: Why use semantic HTML5 elements?**  
   *A:* Semantic tags (`<header>`, `<nav>`, `<main>`, `<article>`, `<aside>`, `<footer>`) provide structural meaning to web browsers, search engines, and screen readers, ensuring accessibility compliance (WCAG 2.1).

2. **Q: How does the Fetch API CRUD in Practical 13 operate without page reload?**  
   *A:* JavaScript dispatches asynchronous HTTP requests (`GET`, `POST`, `DELETE`) to PHP endpoints returning JSON headers. Upon receiving response promises, the client script mutates specific DOM elements directly.

3. **Q: What is the difference between hard delete and soft delete (Practical 11)?**  
   *A:* Hard delete permanently removes rows from the disk via `DELETE FROM`. Soft delete updates a status flag (`status = 'deleted'`), preserving historical data and audit trails while hiding records from standard views with the ability to restore.

4. **Q: How does the persistent "Remember Me" token work safely?**  
   *A:* It generates a 256-bit random token, hashes it using SHA-256 before saving to the database, and stores only the raw token inside an HTTP-Only, Secure browser cookie. If the database is compromised, attackers cannot forge the original cookie value.

---
**CHARUSAT FTE &bull; Department of Information Technology &bull; 2026-27**
