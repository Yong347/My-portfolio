
CREATE DATABASE IF NOT EXISTS myportfolio;
USE myportfolio;

/*
   string admin
   binary 01110000 01100001 01110011 01110011 01110111 01101111 01110010 01100100 00110001 00110010 00110011
*/
CREATE TABLE IF NOT EXISTS portfolio_content (
    id INT PRIMARY KEY AUTO_INCREMENT,
    section_name VARCHAR(50) NOT NULL,
    content_key VARCHAR(100) NOT NULL,
    content_value TEXT,
    UNIQUE KEY unique_section_key (section_name, content_key)
);


CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS experience (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    start_date DATE NULL,
    end_date DATE NULL
);


CREATE TABLE IF NOT EXISTS certifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    issuer VARCHAR(150) NOT NULL,
    issued_date DATE NULL,
    credential_url VARCHAR(255) NULL
);

-- Insert default content
INSERT IGNORE INTO portfolio_content (section_name, content_key, content_value) VALUES
('about', 'about_text', 'I''m a passionate web developer with expertise in creating dynamic and responsive websites. With a strong foundation in both front-end and back-end technologies, I strive to create engaging user experiences with clean and efficient code.'),
('about', 'education_elementary', 'Elementary (2010 – 2016)<br>Polangui South Central Elementary School (PSCES)'),
('about', 'education_junior', 'Junior High School (2016 – 2020)<br>(PGCHS) Polangui General Comprehensive High School'),
('about', 'education_senior', 'Senior High School (2020 – 2023)<br>(PGCHS) Polangui General Comprehensive High School<br>GAS STRAND'),
('about', 'education_college', 'College (2022 – Present)<br>(CSPC) Camarines Sur Polytechnic Colleges'),
('projects', 'project1_title', 'CSPC Intramural Sports Website'),
('projects', 'project1_desc', 'A fully responsive intramural sports platform where users can view team standings, schedules, teams, and more.'),
('projects', 'project1_link', 'https://yongsarte-cspc-intramural-web.vercel.app/'),
('contact', 'contact_email', 'josarte@my.cspc.edu.ph.com'),
('contact', 'contact_phone', '09947279813');


INSERT IGNORE INTO experience (title, description, start_date, end_date) VALUES
('Internship - Web Developer', 'Worked on front-end and back-end development projects during internship.', '2023-06-01', '2023-08-30');


INSERT IGNORE INTO certifications (name, issuer, issued_date, credential_url) VALUES
('Web Development Certificate', 'FreeCodeCamp', '2023-05-01', 'https://www.freecodecamp.org/certificate/example');

