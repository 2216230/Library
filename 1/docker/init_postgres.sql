-- BSU-Bokod Library Management System Database Schema (PostgreSQL)
-- This file contains the basic table structure for PostgreSQL on Render

-- Admin table
CREATE TABLE IF NOT EXISTS admin (
  id SERIAL PRIMARY KEY,
  gmail VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  firstname VARCHAR(50) NOT NULL,
  lastname VARCHAR(50) NOT NULL,
  photo VARCHAR(200) DEFAULT NULL,
  created_on TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Students table
CREATE TABLE IF NOT EXISTS students (
  id SERIAL PRIMARY KEY,
  student_id VARCHAR(20) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  firstname VARCHAR(50) NOT NULL,
  lastname VARCHAR(50) NOT NULL,
  course_id INTEGER DEFAULT NULL,
  photo VARCHAR(200) DEFAULT NULL,
  created_on TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Faculty table
CREATE TABLE IF NOT EXISTS faculty (
  id SERIAL PRIMARY KEY,
  faculty_id VARCHAR(20) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  firstname VARCHAR(50) NOT NULL,
  lastname VARCHAR(50) NOT NULL,
  photo VARCHAR(200) DEFAULT NULL,
  created_on TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Course table
CREATE TABLE IF NOT EXISTS course (
  id SERIAL PRIMARY KEY,
  code VARCHAR(20) NOT NULL,
  name VARCHAR(100) NOT NULL
);

-- Books table
CREATE TABLE IF NOT EXISTS books (
  id SERIAL PRIMARY KEY,
  isbn VARCHAR(20) DEFAULT NULL,
  title VARCHAR(200) NOT NULL,
  author VARCHAR(200) DEFAULT NULL,
  publisher VARCHAR(200) DEFAULT NULL,
  publish_date DATE DEFAULT NULL,
  category_id INTEGER DEFAULT NULL,
  status VARCHAR(20) DEFAULT 'available' CHECK (status IN ('available','borrowed','damaged','lost')),
  created_on TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Calibre books (e-books) table
CREATE TABLE IF NOT EXISTS calibre_books (
  id SERIAL PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  author VARCHAR(200) DEFAULT NULL,
  publisher VARCHAR(200) DEFAULT NULL,
  isbn VARCHAR(20) DEFAULT NULL,
  file_path VARCHAR(500) DEFAULT NULL,
  category_id INTEGER DEFAULT NULL,
  created_on TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Category table
CREATE TABLE IF NOT EXISTS category (
  id SERIAL PRIMARY KEY,
  name VARCHAR(100) NOT NULL
);

-- Subjects table
CREATE TABLE IF NOT EXISTS subjects (
  id SERIAL PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  code VARCHAR(20) DEFAULT NULL
);

-- Borrow transactions table
CREATE TABLE IF NOT EXISTS borrow_transactions (
  id SERIAL PRIMARY KEY,
  student_id INTEGER DEFAULT NULL,
  faculty_id INTEGER DEFAULT NULL,
  book_id INTEGER NOT NULL,
  borrow_date TIMESTAMP NOT NULL,
  due_date TIMESTAMP NOT NULL,
  return_date TIMESTAMP DEFAULT NULL,
  status VARCHAR(20) DEFAULT 'borrowed' CHECK (status IN ('borrowed','returned','overdue'))
);

-- Posts/Announcements table
CREATE TABLE IF NOT EXISTS posts (
  id SERIAL PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  content TEXT NOT NULL,
  created_by INTEGER DEFAULT NULL,
  created_on TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- User logbook table
CREATE TABLE IF NOT EXISTS user_logbook (
  id SERIAL PRIMARY KEY,
  user_id VARCHAR(50) NOT NULL,
  user_type VARCHAR(20) NOT NULL CHECK (user_type IN ('admin','student','faculty')),
  firstname VARCHAR(50) DEFAULT NULL,
  lastname VARCHAR(50) DEFAULT NULL,
  ip_address VARCHAR(50) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  login_time TIMESTAMP NOT NULL,
  logout_time TIMESTAMP DEFAULT NULL
);

-- Insert default admin account (password: admin123)
INSERT INTO admin (gmail, password, firstname, lastname) 
VALUES ('admin@bsu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator')
ON CONFLICT (gmail) DO NOTHING;
