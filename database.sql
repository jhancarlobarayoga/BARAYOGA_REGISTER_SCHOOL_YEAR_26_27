CREATE DATABASE IF NOT EXISTS webdev_business_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE webdev_business_db;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    address VARCHAR(255) NOT NULL,
    age TINYINT UNSIGNED NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS programmers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    specialization VARCHAR(120) NOT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    added_by INT UNSIGNED NOT NULL,
    last_updated_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_programmer_added_by FOREIGN KEY (added_by) REFERENCES users(id),
    CONSTRAINT fk_programmer_updated_by FOREIGN KEY (last_updated_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS clients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    programmer_id INT UNSIGNED NOT NULL,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    company_name VARCHAR(150) NOT NULL,
    project_name VARCHAR(150) NOT NULL,
    added_by INT UNSIGNED NOT NULL,
    last_updated_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_client_programmer FOREIGN KEY (programmer_id) REFERENCES programmers(id) ON DELETE RESTRICT,
    CONSTRAINT fk_client_added_by FOREIGN KEY (added_by) REFERENCES users(id),
    CONSTRAINT fk_client_updated_by FOREIGN KEY (last_updated_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- This table keeps a record of INSERT, UPDATE, DELETE, REGISTER, LOGIN and LOGOUT actions.
-- It intentionally does not have a foreign key to programmers/clients so a delete record remains in the log.
CREATE TABLE IF NOT EXISTS activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_name VARCHAR(50) NOT NULL,
    record_id BIGINT UNSIGNED DEFAULT NULL,
    action_name VARCHAR(30) NOT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    description VARCHAR(500) NOT NULL,
    action_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_activity_time (action_time),
    INDEX idx_activity_entity (entity_name, record_id)
) ENGINE=InnoDB;
