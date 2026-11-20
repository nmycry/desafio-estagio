-- Executado uma única vez pelo container do MySQL, na criação do volume.
-- O banco `citizens` e o usuário `citizens` já são criados pelas variáveis
-- MYSQL_DATABASE e MYSQL_USER do docker-compose.

CREATE DATABASE IF NOT EXISTS citizens
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE DATABASE IF NOT EXISTS citizens_test
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON citizens.* TO 'citizens'@'%';
GRANT ALL PRIVILEGES ON citizens_test.* TO 'citizens'@'%';

CREATE TABLE citizens.citizens (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    cpf CHAR(11) NOT NULL,
    state CHAR(2) NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_citizens_cpf (cpf),
    KEY idx_citizens_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE citizens_test.citizens LIKE citizens.citizens;
