<?php
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/JWTHandler.php';
require_once __DIR__ . '/../config/database.php';

class UserController
{
    public static function register()
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

        if (!$decoded || !in_array($decoded['role'], ['admin', 'superadmin'])) {
            Response::json(["error" => "Acesso negado"], 403);
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['name'], $input['email'], $input['password'])) {
            Response::json(["error" => "Dados incompletos"], 400);
        }

        $name = trim($input['name']);
        $email = trim($input['email']);
        $password = password_hash(trim($input['password']), PASSWORD_DEFAULT);

       
        if ($decoded['role'] === 'superadmin') {
            if (!isset($input['tenant_id'])) {
                Response::json(["error" => "tenant_id é obrigatório para superadmin"], 400);
            }
            $tenant_id = intval($input['tenant_id']);
        } else {
            
            $tenant_id = intval($decoded['tenant_id']);
        }

        try {
            $database = new Database();
            $conn = $database->connect();

            $stmt = $conn->prepare("INSERT INTO users (name, email, password, tenant_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $password, $tenant_id]);

            Response::json([
                "message" => "Usuário cadastrado com sucesso na loja com ID $tenant_id"
            ], 201);
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                Response::json(["error" => "Email já cadastrado"], 409);
            }
            Response::json(["error" => "Erro: " . $e->getMessage()], 500);
        }
    }

    public static function login()
{
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['email'], $input['password'])) {
        Response::json(["error" => "Dados incompletos"], 400);
    }

    $email = trim($input['email']);
    $password = trim($input['password']);

    try {
        $database = new Database();
        $conn = $database->connect();

       
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            Response::json(["error" => "Email ou senha incorretos"], 401);
        }

       
        $tenantStmt = $conn->prepare("SELECT database_name FROM tenants WHERE id = ?");
        $tenantStmt->execute([$user['tenant_id']]);
        $tenant = $tenantStmt->fetch(PDO::FETCH_ASSOC);

        if (!$tenant) {
            Response::json(["error" => "Tenant não encontrado para este usuário"], 404);
        }

        $payload = [
            "id" => $user['id'],
            "email" => $user['email'],
            "role" => "user",
            "tenant_id" => $user['tenant_id'],
            "tenant_db" => $tenant['database_name'],
            "exp" => time() + (60 * 60 * 24) 
        ];

        $token = JWTHandler::generateToken($payload);

        Response::json([
            "message" => "Login realizado com sucesso",
            "token" => $token
        ]);
    } catch (PDOException $e) {
        Response::json(["error" => "Erro: " . $e->getMessage()], 500);
    }
}

}
