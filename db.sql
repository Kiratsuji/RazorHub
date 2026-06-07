CREATE DATABASE razorhub
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    sobrenome VARCHAR(50) NOT NULL,
    username VARCHAR(50) UNIQUE,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    password VARCHAR(255) NOT NULL, -- Hash maior para password_hash()
    tipo ENUM('cliente', 'funcionario', 'chefe') DEFAULT 'cliente',
    nivel_acesso INT(11) DEFAULT 1,
    status ENUM('ativo', 'inativo', 'pendente') DEFAULT 'ativo',
    criado_por INT(11) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ultimo_login DATETIME NULL,
    FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_tipo (tipo),
    INDEX idx_status (status)
);