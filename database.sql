CREATE TABLE IF NOT EXISTS usuarios (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(60),
    apellido VARCHAR(60),
    email VARCHAR(60),
    password VARCHAR(60),
    token VARCHAR(60),
    admin TINYINT(1) DEFAULT 0
);

-- Insert a test admin user (password: 123456)
-- You should change this password immediately in production!
-- Note: The password hash below is for '123456' using PHP's password_hash()
INSERT INTO usuarios (nombre, apellido, email, password, admin, token) 
VALUES ('Admin', 'User', 'admin@example.com', '$2y$10$d2OJYL0yPRhJ1fIXcZsX3.33JLt4.H25R59aYTVmthKK5CJAkvKW6', 1, '');

-- Insert a test normal user (password: 123456)
INSERT INTO usuarios (nombre, apellido, email, password, admin, token) 
VALUES ('Normal', 'User', 'user@example.com', '$2y$10$xovVQxoOrbGDazMVwT6Q7OcyrCX4gLVEO7Qm2etWV0JUrN3JHNYc.', 0, '');
