<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class ProductTest extends TestCase
{
    private $client;
    private $token;

    protected function setUp(): void
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost:8000',
            'http_errors' => false
        ]);


        $response = $this->client->post('/?url=superadmin-login', [ 
            'json' => [
                'email' => 'admin@admin.com', 
                'password' => 'admin123'
            ]
        ]);

        $body = json_decode($response->getBody(), true);
        $this->token = $body['token'] ?? null;

        $this->assertNotNull($this->token, "Token de autenticação não foi gerado.");
    }

    public function testProductList()
    {
        $response = $this->client->get('/?url=products&tenant_db=ecommerce_empresa_teste7', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token
            ]
        ]);

        var_dump($response->getBody()->getContents());

        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $this->assertIsArray($body, "O retorno não é um array.");
    }

    public function testProductCreate()
    {
        $response = $this->client->post('/?url=products&tenant_db=ecommerce_empresa_teste7', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token
            ],
            'json' => [
                'name' => 'Produto Teste ' . rand(1, 9999),
                'description' => 'Produto criado automaticamente pelo teste.',
                'price' => 59.90,
                'stock' => 10
            ]
        ]);

        var_dump($response->getBody()->getContents());

        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('message', $body, "Resposta não contém a chave 'message'.");
    }
}
