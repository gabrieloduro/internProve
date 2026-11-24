-- InternProve database (PostgreSQL)
-- Step 1: in psql run:  CREATE DATABASE internprove;
-- Step 2: connect to it and run this file:  \c internprove  then  \i database.sql

DROP TABLE IF EXISTS log_entries;
DROP TABLE IF EXISTS internships;
DROP TABLE IF EXISTS users;

-- Everyone who can log in. The "role" column decides what they can see.
CREATE TABLE users (
    id             SERIAL PRIMARY KEY,
    full_name      VARCHAR(100) NOT NULL,
    email          VARCHAR(100) NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,
    role           VARCHAR(20)  NOT NULL CHECK (role IN ('student', 'employer', 'directorate')),
    student_number VARCHAR(30),   -- only used by students
    company        VARCHAR(100),  -- only used by employers
    created_at     TIMESTAMP DEFAULT NOW()
);

-- One internship links one student to one employer for a period.
CREATE TABLE internships (
    id           SERIAL PRIMARY KEY,
    student_id   INTEGER NOT NULL UNIQUE REFERENCES users(id),  -- UNIQUE = one internship per student
    employer_id  INTEGER NOT NULL REFERENCES users(id),
    company_name VARCHAR(100) NOT NULL,
    start_date   DATE NOT NULL,
    end_date     DATE NOT NULL
);

-- Daily entries written by the student.
-- status: draft -> submitted -> approved (or rejected, then the student fixes and resubmits)
CREATE TABLE log_entries (
    id               SERIAL PRIMARY KEY,
    internship_id    INTEGER NOT NULL REFERENCES internships(id),
    entry_date       DATE NOT NULL,
    description      TEXT NOT NULL,
    cycle_number     INTEGER NOT NULL,   -- two-week cycle: days 0-13 = cycle 1, 14-27 = cycle 2, ...
    status           VARCHAR(20) NOT NULL DEFAULT 'draft'
                     CHECK (status IN ('draft', 'submitted', 'approved', 'rejected')),
    employer_comment TEXT,
    reviewed_at      TIMESTAMP,
    created_at       TIMESTAMP DEFAULT NOW()
);

-- First directorate account (password: admin123). Change it after your first login.
INSERT INTO users (full_name, email, password_hash, role)
VALUES ('Internship Directorate', 'directorate@upsa.edu.gh',
        '$2y$10$p1aSvMKTHg1dN1FBEDWxH.nUINfJmPw1oZeFo/cgV1m1ANPnAyhsC', 'directorate');
