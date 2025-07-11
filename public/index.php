<?php
require_once '../config/database.php';
require_once '../utils/Response.php';
require_once '../controllers/TenantController.php';
require_once '../controllers/AdminController.php';
require_once '../controllers/ProductController.php';
require_once '../controllers/UserController.php';
require_once '../utils/JWTHandler.php';

JWTHandler::init();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

$url = $_GET['url'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];


switch ($url) {
    case '':
        Response::json(["message" => "API Multi-Tenant PHP Puro funcionando 🚀"]);
        break;

    case 'register-tenant':
        if ($method === 'POST') {
            TenantController::register();
        } else {
            Response::json(["error" => "Método não permitido"], 405);
        }
        break;
    case 'superadmin-login':
        if ($method === 'POST') {
            AdminController::login();
        } else {
            Response::json(["error" => "Método não permitido"], 405);
        }
        break;
    case 'products':
        if ($method === 'GET') {
            ProductController::list();
        } elseif ($method === 'POST') {
            ProductController::create();
        } else {
            Response::json(["error" => "Método não permitido"], 405);
        }
        break;
    case 'register-user':
        if ($method === 'POST') {
            UserController::register();
        } else {
            Response::json(["error" => "Método não permitido"], 405);
        }
        break;
    case 'login-user':
        if ($method === 'POST') {
            UserController::login();
        } else {
            Response::json(["error" => "Método não permitido"], 405);
        }
        break;


    default:
        if (preg_match('#^products/(\d+)$#', $url, $matches)) {
            $id = $matches[1];
            if ($method === 'PUT') {
                ProductController::update($id);
            } elseif ($method === 'DELETE') {
                ProductController::delete($id);
            } else {
                Response::json(["error" => "Método não permitido"], 405);
            }
        } else {
            Response::json(["error" => "Endpoint não encontrado"], 404);
        }
        break;
}
