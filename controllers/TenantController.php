<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/Response.php';



class TenantController
{
    public static function register()
    {

        // Pega o token enviado no cabeçalho
        $headers = apache_request_headers();
        if (!isset($headers['Authorization'])) {
            Response::json(["error" => "Token não enviado"], 401);
        }

        $authHeader = $headers['Authorization'];
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            Response::json(["error" => "Formato do token inválido"], 401);
        }

        $token = $matches[1];
        $decoded = JWTHandler::validateToken($token);

        if (!$decoded || ($decoded['role'] ?? null) !== 'superadmin') {
            Response::json(["error" => "Acesso negado: apenas superadmin pode cadastrar empresas."], 403);
        }
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['name'], $input['subdomain'])) {
            Response::json(["error" => "Dados incompletos"], 400);
        }

        $name = trim($input['name']);
        $subdomain = strtolower(trim($input['subdomain']));
        $database_name = "ecommerce_" . preg_replace('/[^a-z0-9_]/', '_', $subdomain);

        try {
            $database = new Database();
            $conn = $database->connect();


            $stmt = $conn->prepare("INSERT INTO tenants (name, subdomain, database_name) VALUES (?, ?, ?)");
            $stmt->execute([$name, $subdomain, $database_name]);
            $tenant_id = $conn->lastInsertId();


            $createDb = $conn->prepare("CREATE DATABASE `$database_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $createDb->execute();


            $tenantConn = new PDO(
                "mysql:host={$_ENV['DB_HOST']};dbname={$database_name}",
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );


            $createProducts = "
                CREATE TABLE IF NOT EXISTS products (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    description TEXT,
                    price DECIMAL(10,2),
                    stock INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                );
            ";
            $tenantConn->exec($createProducts);

            Response::json([
                "message" => "Tenant cadastrado com sucesso",
                "tenant_id" => $tenant_id,
                "database_name" => $database_name
            ], 201);
        } catch (PDOException $e) {
            Response::json(["error" => "Erro: " . $e->getMessage()], 500);
        }
    }
}
