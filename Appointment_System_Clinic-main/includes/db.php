<?php
// Database connection using clinic_user
define('DB_HOST', 'localhost');
define('DB_USER', 'clinic_admin');
define('DB_PASS', '12345');
define('DB_NAME', 'clinic_db');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            die("Database connection failed. Please contact support.");
        }
    }
    return $pdo;
}

function dbQuery($sql, $params = []) {
    $pdo = getDB();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function dbFetch($sql, $params = []) {
    $stmt = dbQuery($sql, $params);
    return $stmt->fetch();
}

function dbFetchAll($sql, $params = []) {
    $stmt = dbQuery($sql, $params);
    return $stmt->fetchAll();
}

function dbInsert($table, $data) {
    $pdo = getDB();
    $keys = array_keys($data);
    $fields = implode(', ', $keys);
    $placeholders = implode(', ', array_map(fn($k) => ":$k", $keys));
    $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    return $pdo->lastInsertId();
}

function dbUpdate($table, $data, $where, $whereParams = []) {
    $pdo = getDB();
    $sets = [];
    foreach (array_keys($data) as $key) {
        $sets[] = "$key = :$key";
    }
    $sql = "UPDATE $table SET " . implode(', ', $sets) . " WHERE $where";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge($data, $whereParams));
    return $stmt->rowCount();
}

function dbDelete($table, $where, $params = []) {
    $pdo = getDB();
    $sql = "DELETE FROM $table WHERE $where";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}