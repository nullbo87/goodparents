<?php
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }
require_once __DIR__ . '/../config.php';

function db() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, array(
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ));
            $pdo->exec('SET NAMES ' . DB_CHARSET);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
            exit('데이터베이스 연결에 실패했습니다. config.php 설정을 확인하세요.<br><small>'
                . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</small>');
        }
    }
    return $pdo;
}

function q($sql, $params = array()) {
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}
function all($sql, $params = array()) { return q($sql, $params)->fetchAll(); }
function one($sql, $params = array()) { $r = q($sql, $params)->fetch(); return $r === false ? null : $r; }
function col($sql, $params = array()) {
    $r = q($sql, $params)->fetch(PDO::FETCH_NUM);
    return $r === false ? null : $r[0];
}
function last_id() { return (int) db()->lastInsertId(); }
