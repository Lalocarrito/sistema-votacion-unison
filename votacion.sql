-- Base de datos del sistema de votación (simulación)
CREATE DATABASE IF NOT EXISTS votacion CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE votacion;

-- Cada fila es un voto. El expediente es llave primaria,
-- así la base de datos también impide votar dos veces.
CREATE TABLE IF NOT EXISTS mesa2 (
    expediente       VARCHAR(9)   NOT NULL,
    nombre           VARCHAR(255) NOT NULL,
    id_candidato     INT          NOT NULL,
    nombre_candidato VARCHAR(255) NOT NULL,
    PRIMARY KEY (expediente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
