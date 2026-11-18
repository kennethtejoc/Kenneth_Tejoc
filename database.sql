-- Creates the portfolio tables and adds the starting content.
-- Tables are created only if missing, but re-importing repeats the starter rows.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS admins (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(30)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,            -- Stores the administrator password hash.
  display_name  VARCHAR(100) NOT NULL DEFAULT '',
  email         VARCHAR(150) NOT NULL DEFAULT '',
  last_login    DATETIME NULL,
  created_at    DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS login_attempts (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username     VARCHAR(60) NOT NULL,
  ip_address   VARCHAR(45) NOT NULL,
  success      TINYINT(1)  NOT NULL DEFAULT 0,
  attempted_at DATETIME    NOT NULL,
  KEY idx_attempts_ip (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
  setting_key   VARCHAR(60) NOT NULL PRIMARY KEY,
  setting_value TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS profile (
  id             TINYINT UNSIGNED NOT NULL PRIMARY KEY,   -- Identifies the single profile row.
  full_name      VARCHAR(100) NOT NULL DEFAULT '',
  headline       VARCHAR(150) NOT NULL DEFAULT '',
  marquee_text   VARCHAR(150) NOT NULL DEFAULT '',
  intro          TEXT NULL,
  profile_image  VARCHAR(100) NOT NULL DEFAULT '',
  about_heading  VARCHAR(80)  NOT NULL DEFAULT '',
  about_intro    TEXT NULL,
  background     TEXT NULL,
  education_text TEXT NULL,
  career_goals   TEXT NULL,
  interests      VARCHAR(500) NOT NULL DEFAULT '',
  why_hire       TEXT NULL,
  skills_intro   TEXT NULL,
  projects_intro TEXT NULL,
  resume_intro   TEXT NULL,
  contact_intro  TEXT NULL,
  email          VARCHAR(150) NOT NULL DEFAULT '',
  phone          VARCHAR(40)  NOT NULL DEFAULT '',
  location       VARCHAR(150) NOT NULL DEFAULT '',
  facebook_url   VARCHAR(255) NOT NULL DEFAULT '',
  github_url     VARCHAR(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS skills (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100) NOT NULL,
  category    VARCHAR(60)  NOT NULL DEFAULT 'Other',
  level       TINYINT UNSIGNED NOT NULL DEFAULT 50,
  description TEXT NULL,
  sort_order  INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS projects (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(150) NOT NULL,
  category    VARCHAR(60)  NOT NULL DEFAULT '',
  status      VARCHAR(20)  NOT NULL DEFAULT 'Completed',
  description TEXT NULL,
  tags        VARCHAR(200) NOT NULL DEFAULT '',
  image       VARCHAR(100) NOT NULL DEFAULT '',
  link_url    VARCHAR(255) NOT NULL DEFAULT '',
  link_label  VARCHAR(40)  NOT NULL DEFAULT '',
  sort_order  INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS education (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  label       VARCHAR(100) NOT NULL,
  title       VARCHAR(150) NOT NULL,
  description TEXT NULL,
  sort_order  INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS experience (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  label        VARCHAR(100) NOT NULL,
  title        VARCHAR(150) NOT NULL,
  organization VARCHAR(150) NOT NULL DEFAULT '',
  description  TEXT NULL,
  sort_order   INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS achievements (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title        VARCHAR(150) NOT NULL,
  issuer       VARCHAR(150) NOT NULL DEFAULT '',
  date_awarded DATE NULL,
  description  TEXT NULL,
  image        VARCHAR(100) NOT NULL DEFAULT '',
  sort_order   INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS messages (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(150) NOT NULL,
  subject    VARCHAR(150) NOT NULL,
  message    TEXT NOT NULL,
  is_read    TINYINT(1) NOT NULL DEFAULT 0,
  ip_address VARCHAR(45) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL,
  KEY idx_messages_created (created_at),
  KEY idx_messages_ip (ip_address, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Adds the initial settings and portfolio content.

INSERT INTO settings (setting_key, setting_value) VALUES
('site_title', 'Portfolio'),
('logo_text', 'Kenneth'),
('site_logo', ''),
('footer_text', '© 2026 Kenneth Tejoc · Personal Portfolio'),
('default_theme', 'dark'),
('accent_color', '');

INSERT INTO profile (id, full_name, headline, marquee_text, intro, profile_image, about_heading, about_intro, background, education_text, career_goals, interests, why_hire, skills_intro, projects_intro, resume_intro, contact_intro, email, phone, location, facebook_url, github_url) VALUES
(1, 'Kenneth Tejoc', '3rd-Year BSIT Student & Web Developer', 'Kenneth Tejoc • BSIT Developer •', 'I’m a 3rd-year BSIT student interested in web development, system design, and practical technology. I enjoy turning my ideas into simple, useful projects—and solving the occasional bug that insists on becoming part of the project.', 'profile.png', 'More Than
Just Code', 'I’m Kenneth Tejoc, a 3rd-year BSIT student interested in web development, system design, networking, and digital design. I learn by building, testing, and fixing things—usually in that order.', 'I enjoy creating simple and useful digital projects. Through my school work, I have gained experience with programming, websites, databases, networking, and design.', 'I am taking Bachelor of Science in Information Technology at NORSU. I continue to improve my technical and problem-solving skills through classes and hands-on projects.', 'My goal is to become a dependable IT professional. I want to build useful systems, work well with others, and keep learning as technology changes.', 'Web Development, UI/UX Design, Networking, Digital Creativity, Technology & Innovation, Gaming', 'I am willing to learn, accept feedback, and work hard to improve. I may still be building my professional experience, but I bring curiosity, dedication, and a genuine interest in creating useful technology. I want to grow with a company while contributing wherever I can.', 'Technical skills developed through coursework, practice, and school projects. I’m still improving every skill—and yes, the missing semicolon still wins sometimes.', 'School and personal projects showing my experience in development, design, networking, and problem-solving. Some started with a simple idea and ended with several tabs open.', 'I use this page to show my education, technical practice, and student project experience. I am still learning, still building, and still figuring out why some bugs only appear during presentations.', 'I’m open to project ideas, questions, and collaboration. Send me a message with the form below or find me on my social platforms.', 'heyneht@gmail.com', '', '', 'https://www.facebook.com/share/1MhWG9ZYZe/', 'https://github.com/kennethtejoc');

INSERT INTO skills (name, category, level, description, sort_order) VALUES
('HTML & CSS', 'Web Development', 90, 'I am highly comfortable building and styling basic responsive websites. I can structure pages, create layouts, and keep my code organized.', 0),
('JavaScript', 'Web Development', 40, 'I can use JavaScript for interactive features such as navigation, validation, filtering, buttons, and simple page behavior.', 1),
('PHP', 'Web Development', 40, 'I have a working knowledge of PHP for basic server-side programming and connecting web pages with data.', 2),
('C++', 'Programming', 65, 'I can write basic C++ programs and understand core programming concepts such as variables, conditions, loops, methods, and classes.', 3),
('MySQL', 'Database', 70, 'I can create and work with basic databases, tables, queries, and relationships for school system projects.', 4),
('UI/UX Design', 'Design', 45, 'I can design simple and user-friendly interfaces while paying attention to layout, spacing, readability, and ease of use.', 5),
('Adobe Illustrator', 'Design', 60, 'I am comfortable creating digital designs, layouts, and visual elements using Adobe Illustrator.', 6),
('Adobe Photoshop', 'Design', 55, 'I can use Photoshop for basic image editing, composition, and visual improvements.', 7),
('Cisco Packet Tracer', 'Networking', 70, 'I can create network layouts, configure basic devices, and practice connectivity using Cisco Packet Tracer.', 8);

INSERT INTO projects (title, category, status, description, tags, image, link_url, link_label, sort_order) VALUES
('KioskToGo POS', 'Web', 'Completed', 'I created this point-of-sale project to practice faster and more organized ordering, sales tracking, and kiosk transactions.', 'HTML/CSS', 'kiosktogo-pos.jpg', '', '', 0),
('Cisco Packet Tracer Network Layout', 'Networking', 'Completed', 'I created this network layout in Cisco Packet Tracer to practice connecting devices and building a structured network setup.', 'Cisco Packet Tracer, Networking', 'cisco-packet-tracer.jpg', '', '', 1),
('Animate Project', 'Designs', 'Completed', 'I created this animation project to practice movement, timing, and visual storytelling using Adobe Animate.', 'Adobe Animate, Animation', 'animate-project.jpg', 'https://www.facebook.com/share/v/1A2hfbajcm/', 'Watch Video', 2),
('Adobe Illustrator Project', 'Designs', 'Completed', 'I created this digital design project in Adobe Illustrator to practice layout, illustration, and visual presentation.', 'Adobe Illustrator, Graphic Design', 'illustrator-project.jpg', '', '', 3),
('Personal Portfolio Website', 'Web', 'Ongoing', 'I created this portfolio website to present my skills, projects, experience, and progress as a BSIT student. I am building its PHP and MySQL version with an admin dashboard.', 'HTML, CSS, JavaScript, PHP, MySQL', 'portfolio-website.jpg', '', '', 4);

INSERT INTO education (label, title, description, sort_order) VALUES
('Senior High School', 'Bayawan Senior High School', 'I graduated from Bayawan Senior High School in 2024. This is where I built my foundation for continuing my studies in Information Technology.', 0),
('1st Year • 2024–2025', 'NORSU', 'I started my Bachelor of Science in Information Technology at Negros Oriental State University (NORSU) during the 2024–2025 school year.', 1),
('2nd Year • 2025–2026', 'NORSU', 'I continued my BSIT studies at NORSU during 2025–2026, where I developed more experience in programming, systems, networking, databases, and digital projects.', 2),
('3rd Year • 2026–2027', 'NORSU • Currently', 'I am currently a 3rd-year BSIT student at NORSU for the 2026–2027 school year, continuing to improve my technical and problem-solving skills.', 3);

INSERT INTO experience (label, title, organization, description, sort_order) VALUES
('Project Experience', 'Academic IT Projects', '', 'I have worked on websites, system concepts, network layouts, animation, and digital design projects. These projects help me improve my problem-solving, teamwork, documentation, and presentation skills.', 0),
('Technical Training', 'Continuous Skill Development', '', 'I practice HTML, CSS, JavaScript, PHP, Java, MySQL, Cisco Packet Tracer, Adobe tools, and other technologies through academic activities and hands-on projects.', 1);
