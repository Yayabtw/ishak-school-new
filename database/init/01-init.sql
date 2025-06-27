-- Script d'initialisation pour Ishak'School

-- Créer la base de données si elle n'existe pas
CREATE DATABASE IF NOT EXISTS ishak_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Utiliser la base de données
USE ishak_db;

-- Créer l'utilisateur s'il n'existe pas
CREATE USER IF NOT EXISTS 'ishak_user'@'%' IDENTIFIED BY 'ishak_password';

-- Accorder tous les privilèges sur la base de données
GRANT ALL PRIVILEGES ON ishak_db.* TO 'ishak_user'@'%';

-- Rafraîchir les privilèges
FLUSH PRIVILEGES; 