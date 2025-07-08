<?php
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/JWTHandler.php';
require_once __DIR__ . '/../config/database.php';

class AdminController
{
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

            $stmt = $conn->prepare("SELECT * FROM superadmins WHERE email = ?");
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            // var_dump($admin);
            // var_dump($password);
            // var_dump(password_verify($password, $admin['password']));
            // exit;
            if ($admin && password_verify($password, $admin['password'])) {
                $payload = [
                    "id" => $admin['id'],
                    "email" => $admin['email'],
                    "role" => "superadmin",
                    "exp" => time() + (60 * 60 * 24)
                ];
                $token = JWTHandler::generateToken($payload);

                Response::json([
                    "message" => "Login superadmin realizado com sucesso",
                    "token" => $token
                ]);
            } else {
                Response::json(["error" => "Email ou senha incorretos"], 401);
            }
        } catch (PDOException $e) {
            Response::json(["error" => "Erro: " . $e->getMessage()], 500);
        }
    }
}
