InternProve
Evidence-based internship tracking. Students log their work, employers approve it, and the directorate keeps the final record.
Live demo: https://internprove-1.onrender.com/login.php
Stack: PHP, PostgreSQL, HTML/CSS, a little JavaScript. Plain PHP forms and sessions. No framework.
---
How it works
Three roles, one pipeline:
Role	What they do
Student	Adds daily log entries, then submits each two-week cycle for approval
Employer	Reviews a submitted cycle, approves it or rejects it with a reason
Directorate	Creates employer accounts, assigns internships, prints the final report
Entry status flow: `draft` -> `submitted` -> `approved` or `rejected`. A rejected cycle can be edited and sent again. Only approved entries appear in the final report.
A cycle is two weeks. `get_cycle()` counts the days since the internship start date and divides by 14.
---
Quick start
Works on Windows with XAMPP, or any PHP + PostgreSQL setup.
Install PostgreSQL and enable the `pdo_pgsql` extension in `php.ini` (remove the `;` before `extension=pdo_pgsql`). Restart Apache.
Create the database:
```sql
   CREATE DATABASE internprove;
   ```
Run `database.sql` inside that database. It creates the tables and the directorate account.
Place the `internprove` folder inside `htdocs`.
Open `includes/db.php` and set your PostgreSQL password.
Visit `http://localhost/internprove/login.php`
First login
Role	Email	Password
Directorate	`directorate@upsa.edu.gh`	`admin123`
Change this password after your first login. Do not leave the default in any deployed instance.
---
Try the full flow
Sign up a student at `register.php`.
Log in as directorate, create an employer account, then assign an internship (student + employer + dates).
Log in as the student, add entries, and press Submit for approval on a cycle.
Log in as the employer, open Review, and approve or reject. A reason is required to reject.
Log in as directorate, open Report, and print the final report.
---
Deployment
Deployed on Render: https://internprove-1.onrender.com
On the free tier the app sleeps when idle, so the first load can take about a minute. Database credentials are set on the server, not committed to the repo. Change the default directorate password on the live instance.
---
Security
Passwords are stored with `password_hash()` and checked with `password_verify()`.
Every query uses `?` placeholders (PDO prepared statements), which blocks SQL injection.
Everything printed from the database goes through `e()` (`htmlspecialchars`), which blocks injected HTML.
Every protected page starts with `require_role()`. The wrong role is redirected away.
Forms use POST and then redirect, so refreshing never resubmits a form.
---
Project structure
```
internprove/
├── database.sql        tables + first directorate account
├── index.php           redirects to login or your dashboard
├── login.php
├── register.php
├── logout.php
├── includes/
│   ├── db.php          PostgreSQL connection (PDO)
│   ├── auth.php        session start + require_role()
│   ├── header.php
│   └── footer.php
├── student/            dashboard, add_log, edit_log, submit_cycle
├── employer/           review and approve or reject cycles
└── directorate/        accounts, assignments, final report
```
---
Notes
Built as a university project at UPSA to replace paper logbooks with a verifiable record of internship work.
