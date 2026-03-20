<?php
/**
 * ============================================================
 *  MENSA Infrastructure — PDO Database Connection
 *  Author : Wampamba Festo (Lead Software Engineer & Architect)
 *  File   : www/db_connect.php
 *
 *  SECURITY: This file is blocked from web access via Apache.
 *  Credentials are injected by Docker — never hardcoded here.
 * ============================================================
 */

declare(strict_types=1);

function getDbConnection(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $host   = getenv('DB_HOST') ?: 'db';
    $port   = getenv('DB_PORT') ?: '3306';
    $dbname = getenv('DB_NAME') ?: 'mensa_db';
    $user   = getenv('DB_USER') ?: 'mensa_user';
    $pass   = getenv('DB_PASS') ?: 'mensa26';

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log('[MENSA DB ERROR] ' . $e->getMessage());
        throw new RuntimeException(
            'Database connection unavailable. Please ensure the mensa_db container is running.',
            (int) $e->getCode()
        );
    }
}
