CREATE DATABASE IF NOT EXISTS `careerpath-ai` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'ubaid'@'%' IDENTIFIED BY 'Pak123';
CREATE USER IF NOT EXISTS 'ubaid'@'localhost' IDENTIFIED BY 'Pak123';
GRANT ALL PRIVILEGES ON `careerpath-ai`.* TO 'ubaid'@'%';
GRANT ALL PRIVILEGES ON `careerpath-ai`.* TO 'ubaid'@'localhost';
FLUSH PRIVILEGES;
