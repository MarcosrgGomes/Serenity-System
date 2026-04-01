-- ============================================================
--  Serenity — Schema do Banco de Dados
--  MySQL 8.0+ / MariaDB 10.6+
--
--  Como usar:
--    mysql -u root -p < schema.sql
--  Ou importe via phpMyAdmin.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── Banco de dados ───────────────────────────────────────────
CREATE DATABASE IF NOT EXISTS `serenity`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `serenity`;

-- ── Tabela: users ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
    `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(150)     NOT NULL,
    `email`         VARCHAR(200)     NOT NULL,
    `password_hash` VARCHAR(255)     NOT NULL,
    `role`          ENUM('admin','manager','operator') NOT NULL DEFAULT 'operator',
    `status`        ENUM('active','inactive')          NOT NULL DEFAULT 'active',
    `created_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabela: categories ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS `categories` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100)  NOT NULL,
    `description` VARCHAR(255)      NULL DEFAULT NULL,
    `status`      ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_categories_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabela: suppliers ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `suppliers` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(150)  NOT NULL,
    `cnpj`       VARCHAR(18)       NULL DEFAULT NULL,
    `email`      VARCHAR(200)      NULL DEFAULT NULL,
    `phone`      VARCHAR(20)       NULL DEFAULT NULL,
    `address`    VARCHAR(255)      NULL DEFAULT NULL,
    `city`       VARCHAR(100)      NULL DEFAULT NULL,
    `state`      VARCHAR(2)        NULL DEFAULT NULL,
    `status`     ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabela: products ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `products` (
    `id`           INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `sku`          VARCHAR(50)       NOT NULL,
    `name`         VARCHAR(200)      NOT NULL,
    `description`  TEXT                  NULL DEFAULT NULL,
    `category_id`  INT UNSIGNED          NULL DEFAULT NULL,
    `supplier_id`  INT UNSIGNED          NULL DEFAULT NULL,
    `cost_price`   DECIMAL(10,2)     NOT NULL DEFAULT 0.00,
    `sale_price`   DECIMAL(10,2)     NOT NULL DEFAULT 0.00,
    `quantity`     INT               NOT NULL DEFAULT 0,
    `min_quantity` INT               NOT NULL DEFAULT 0,
    `status`       ENUM('active','inactive','discontinued') NOT NULL DEFAULT 'active',
    `created_at`   DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_products_sku` (`sku`),
    KEY `fk_products_category` (`category_id`),
    KEY `fk_products_supplier` (`supplier_id`),
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`)
        REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_products_supplier` FOREIGN KEY (`supplier_id`)
        REFERENCES `suppliers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabela: stock_movements ──────────────────────────────────
CREATE TABLE IF NOT EXISTS `stock_movements` (
    `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `product_id`   INT UNSIGNED      NULL DEFAULT NULL,
    `user_id`      INT UNSIGNED      NULL DEFAULT NULL,
    `type`         ENUM('entry','exit','adjustment','return','loss') NOT NULL,
    `quantity`     INT           NOT NULL,
    `old_quantity` INT           NOT NULL DEFAULT 0,
    `new_quantity` INT           NOT NULL DEFAULT 0,
    `reason`       VARCHAR(255)      NULL DEFAULT NULL,
    `date`         DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `fk_movements_product` (`product_id`),
    KEY `fk_movements_user`    (`user_id`),
    CONSTRAINT `fk_movements_product` FOREIGN KEY (`product_id`)
        REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_movements_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  SEED — Dados iniciais
-- ============================================================

-- ── Usuário admin padrão ─────────────────────────────────────
--
-- PASSO OBRIGATÓRIO antes de importar:
--   Gere o hash bcrypt da sua senha e substitua o placeholder abaixo.
--
-- No terminal do servidor (com PHP disponível):
--   php -r "echo password_hash('admin123', PASSWORD_BCRYPT, ['cost'=>12]);"
--
-- Depois substitua SUA_SENHA_BCRYPT_AQUI pelo hash gerado (começa com $2y$12$...)
--
-- ⚠️  TROQUE A SENHA DO ADMIN IMEDIATAMENTE APÓS O PRIMEIRO ACESSO!
--
-- Credenciais padrão:
--   E-mail : admin@serenity.com
--   Senha  : admin123  (ou a que você escolheu acima)
--
INSERT IGNORE INTO `users` (`name`, `email`, `password_hash`, `role`, `status`) VALUES (
    'Administrador',
    'admin@serenity.com',
    '$2y$12$eImiTXuWVxfM37uY4JANjQ==',   -- <-- SUBSTITUA por: php -r "echo password_hash('admin123', PASSWORD_BCRYPT, ['cost'=>12]);"
    'admin',
    'active'
);

-- ALTERNATIVA: se tiver o PHP disponível, rode este comando no terminal
-- para inserir o usuário diretamente com o hash correto:
--
-- php -r "
-- \$pdo = new PDO('mysql:host=localhost;dbname=serenity', 'root', 'SUA_SENHA_MYSQL');
-- \$hash = password_hash('admin123', PASSWORD_BCRYPT, ['cost'=>12]);
-- \$stmt = \$pdo->prepare('INSERT IGNORE INTO users (name, email, password_hash, role, status) VALUES (?,?,?,?,?)');
-- \$stmt->execute(['Administrador', 'admin@serenity.com', \$hash, 'admin', 'active']);
-- echo 'Usuário admin criado com sucesso!' . PHP_EOL;
-- "
--

-- ── Categorias padrão ────────────────────────────────────────
INSERT IGNORE INTO `categories` (`name`, `description`, `status`) VALUES
    ('Ferramentas',    'Ferramentas manuais e elétricas',    'active'),
    ('Peças',          'Peças e componentes de reposição',   'active'),
    ('Consumíveis',    'Materiais de consumo e descartáveis','active'),
    ('Equipamentos',   'Equipamentos e maquinário',          'active'),
    ('EPI',            'Equipamentos de proteção individual','active');
