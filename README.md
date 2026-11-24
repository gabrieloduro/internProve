# InternProve

Students log their internship, employers approve it, the directorate keeps the final record.
Stack: PHP, PostgreSQL, HTML/CSS, a little JavaScript. Plain PHP forms and sessions. No framework.

## Setup (Windows with XAMPP or any PHP + PostgreSQL setup)
1. Install PostgreSQL and enable the `pdo_pgsql` extension in `php.ini`
   (remove the `;` before `extension=pdo_pgsql`), then restart Apache.
2. In pgAdmin or psql: `CREATE DATABASE internprove;`
3. Run `database.sql` inside that database (creates the tables and the directorate account).
4. Put the `internprove` folder inside `htdocs`.
5. Open `includes/db.php` and set your PostgreSQL password.
6. Visit http://localhost/internprove/

## First login
- Directorate: `directorate@upsa.edu.gh` / `admin123` (change it later)

## Try the whole flow
1. Sign up a student at `register.php`.
2. Log in as directorate -> Create employer account -> Assign internship (student + employer + dates).
3. Log in as the student -> add entries -> "Submit for approval" on a cycle.
4. Log in as the employer -> Review -> approve or reject (a reason is required to reject).
5. Log in as directorate -> Report -> print the final report (approved entries only).

## How it works (for the presentation)
- `includes/db.php` connects to PostgreSQL with PDO. `includes/auth.php` starts the session and has `require_role()`.
- Every protected page starts with `require_role('student')` (or employer / directorate). Wrong role = sent away.
- Passwords are saved with `password_hash()` and checked with `password_verify()`.
- Every query uses `?` placeholders (prepared statements), so SQL injection does not work.
- Everything printed from the database goes through `e()` (htmlspecialchars), so injected HTML does not work.
- Forms use POST, then the page redirects (so refreshing does not send the form twice).
- Entry status: draft -> submitted -> approved or rejected. A rejected cycle can be edited and sent again.
- A cycle is two weeks: `get_cycle()` counts the days since the start date and divides by 14.

## Files
```
database.sql            tables + first directorate account
index.php               sends you to login or your dashboard
login.php, register.php, logout.php
includes/               db.php, auth.php, header.php, footer.php
student/                dashboard, add_log, edit_log, submit_cycle
employer/               dashboard, review
directorate/            dashboard, create_employer, assign, report
css/style.css           all styling
js/app.js               confirm box, character counter, message fade
```
