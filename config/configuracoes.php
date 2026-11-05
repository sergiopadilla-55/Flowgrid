<?php
declare(strict_types=1);

/**
 * Executa uma query escalar (COUNT, SUM, etc.) e devolve um inteiro.
 */
function scalarQuery(PDO $pdo, string $sql, array $params = []): int
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

/**
 * Verifica se uma coluna existe numa tabela do banco atual.
 * Usado para ativar recursos opcionais (ex.: badges de crescimento)
 * somente quando as colunas de data de criação existirem.
 */
function columnExists(PDO $pdo, string $table, string $column): bool
{
    static $cache = [];
    $key = $table . '.' . $column;

    if (!array_key_exists($key, $cache)) {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $stmt->execute([$table, $column]);
        $cache[$key] = ((int) $stmt->fetchColumn()) > 0;
    }

    return $cache[$key];
}

/**
 * Retorna os totais de distribuição por status como array [label => total],
 * preenchendo com 0 os status que não aparecerem no banco.
 */
function statusDistribution(PDO $pdo, string $table, string $statusColumn, array $labels): array
{
    $stmt = $pdo->prepare("SELECT {$statusColumn} AS status, COUNT(*) AS total FROM {$table} GROUP BY {$statusColumn}");
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $result = array_fill_keys(array_values($labels), 0);
    foreach ($rows as $row) {
        $label = $labels[(int) $row['status']] ?? null;
        if ($label !== null) {
            $result[$label] += (int) $row['total'];
        }
    }

    return $result;
}