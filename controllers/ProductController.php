<?php
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/JWTHandler.php';

class ProductController
{
    private static function getConnection($tenant_db)
    {
        return new PDO(
            "mysql:host={$_ENV['DB_HOST']};dbname={$tenant_db}",
            $_ENV['DB_USER'],
            $_ENV['DB_PASS'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    private static function authenticateAndGetTenant()
    {
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
        if (!$decoded) {
            Response::json(["error" => "Token inválido ou expirado"], 401);
        }

        if ($decoded['role'] === 'superadmin') {
            if (!isset($_GET['tenant_db'])) {
                Response::json(["error" => "tenant_db não informado"], 400);
            }
            $tenant_db = $_GET['tenant_db'];
        } elseif ($decoded['role'] === 'user') {
            $tenant_db = $decoded['tenant_db'];
        } else {
            Response::json(["error" => "Permissão negada"], 403);
        }

        return $tenant_db;
    }

    public static function list()
    {
        $tenant_db = self::authenticateAndGetTenant();
        $conn = self::getConnection($tenant_db);
        $stmt = $conn->query("SELECT * FROM products ORDER BY id DESC");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        Response::json($products);
    }

    public static function create()
    {
        $tenant_db = self::authenticateAndGetTenant();
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['name'], $input['price'], $input['stock'])) {
            Response::json(["error" => "Dados incompletos"], 400);
        }

        $name = trim($input['name']);
        $description = trim($input['description'] ?? '');
        $price = $input['price'];
        $stock = $input['stock'];

        $conn = self::getConnection($tenant_db);
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $stock]);

        Response::json(["message" => "Produto cadastrado com sucesso!"]);
    }

    public static function update($id)
    {
        $tenant_db = self::authenticateAndGetTenant();
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['name'], $input['price'], $input['stock'])) {
            Response::json(["error" => "Dados incompletos"], 400);
        }

        $name = trim($input['name']);
        $description = trim($input['description'] ?? '');
        $price = $input['price'];
        $stock = $input['stock'];

        $conn = self::getConnection($tenant_db);
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ? WHERE id = ?");
        $stmt->execute([$name, $description, $price, $stock, $id]);

        Response::json(["message" => "Produto atualizado com sucesso!"]);
    }

    public static function delete($id)
    {
        $tenant_db = self::authenticateAndGetTenant();
        $conn = self::getConnection($tenant_db);
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);

        Response::json(["message" => "Produto excluído com sucesso!"]);
    }
}
