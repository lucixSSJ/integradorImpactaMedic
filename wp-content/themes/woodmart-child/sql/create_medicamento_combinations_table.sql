-- Crear tabla para almacenar combinaciones de medicamentos por usuario
-- Ejecutar este script manualmente si la tabla no se crea automáticamente

CREATE TABLE IF NOT EXISTS wpj1_medicamento_combinations (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    author_id bigint(20) UNSIGNED NOT NULL,
    combination_key varchar(255) NOT NULL,
    combination_data longtext NOT NULL,
    usage_count int(11) DEFAULT 1,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY author_id (author_id),
    KEY combination_key (combination_key),
    KEY usage_count (usage_count),
-- Añadir índice compuesto para mejorar rendimiento de consultas
    KEY idx_author_key_usage (author_id, combination_key, usage_count DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;