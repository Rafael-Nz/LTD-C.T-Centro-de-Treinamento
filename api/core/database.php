<?php
namespace Core;

use PDO;
use PDOException;

class Database {

    private static ?PDO $connection = null;

    // Configurações do banco de dados
    private static string $host = 'localhost';
    private static string $db   = 'db_centro_treinamento';
    private static string $user = 'root';
    private static string $pass = '';
    private static string $port = '3306';
    private static string $charset = 'utf8mb4';

    public static function getConnection(): PDO {
        if (self::$connection === null) {
            self::connect();
        }
        return self::$connection;
    }

    private static function connect(): void {
        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                self::$host,
                self::$port,
                self::$db,
                self::$charset
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
                PDO::ATTR_TIMEOUT            => 5,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ];

            self::$connection = new PDO($dsn, self::$user, self::$pass, $options);
            self::$connection->exec("SET time_zone = '-03:00'");
            self::$connection->exec("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");

        } catch (PDOException $e) {
            self::handleError($e);
        }
    }

    private static function handleError(PDOException $e): void {
        $date = new \DateTime('now', new \DateTimeZone('America/Fortaleza'));

        error_log(sprintf(
            "[%s] Erro de conexão PDO: %s em %s linha %s",
            $date->format('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        ));

        $code = $e->getCode();
        $message = "Erro ao conectar com o banco de dados. ";

        switch ($code) {
            case 1045:
                $message .= "Credenciais inválidas.";
                break;
            case 1049:
                $message .= "Banco de dados não encontrado.";
                break;
            case 2002:
                $message .= "Servidor indisponível.";
                break;
            default:
                $message .= $e->getMessage();
        }

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
    public static function now(): string {
        $stmt = self::getConnection()->query("SELECT NOW()");
        return $stmt->fetchColumn();
    }
}