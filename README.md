# Multi-Tenant PHP API

API REST Multi-Tenant desenvolvida em **PHP puro**.

---

## 🚀 Funcionalidades

* Cadastro de empresas (tenants) com criação automática de banco de dados.
* Cadastro e login de superadmin com acesso a todas as lojas.
* Cadastro e login de usuários vinculados a cada loja.
* CRUD de produtos por loja:

  * Superadmin gerencia qualquer loja.
  * Usuários gerenciam apenas sua própria loja.
* Autenticação JWT protegendo os endpoints.
* Estrutura limpa e extensível.

---

## 🛠️ Tecnologias utilizadas

* **PHP Puro** (8.2)
* **MySQL**
* **Composer**
* **Firebase JWT** (`firebase/php-jwt`)
* **vlucas/phpdotenv** para gerenciamento do `.env`
* **Postman** para testes manuais
* **PHPUnit** para testes automatizados

---

## ⚙️ Instalação

1️⃣ Clone o repositório:

```bash
git clone https://github.com/simonebatista23/multi-tenant-php-api.git
```

2️⃣ Instale as dependências:

```bash
composer install
```

3️⃣ Configure o banco de dados MySQL criando o banco central para os tenants.

4️⃣ Crie o arquivo `.env` na raiz do projeto:

```
JWT_SECRET_KEY=sua_chave_secreta_aqui
JWT_ALGORITHM=HS256
DB_HOST=localhost
DB_NAME=multi_tenant_central
DB_USER=root
DB_PASSWORD=
```

5️⃣ Suba o projeto em seu servidor local (XAMPP).

6️⃣ Configure o arquivo `.htaccess`:

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ public/index.php?url=$1 [QSA,L]
```

---

## 📲 Endpoints principais

* `POST /register-tenant` → Cadastrar nova empresa (tenant)
* `POST /superadmin-login` → Login do superadmin
* `POST /register-user` → Cadastro de usuário vinculado a tenant
* `POST /login-user` → Login do usuário vinculado
* `GET /products?tenant_db=loja_x` → Listar produtos
* `POST /products?tenant_db=loja_x` → Cadastrar produto
* `PUT /products/{id}?tenant_db=loja_x` → Atualizar produto
* `DELETE /products/{id}?tenant_db=loja_x` → Deletar produto

**Autenticação JWT obrigatória para endpoints protegidos.**

---

## 🧪 Testes

✅ Utilize **Postman** para testes manuais de cada rota.
✅ Utilize **PHPUnit** para testes automatizados garantindo estabilidade do projeto.

---


## 🤝 Contribuição

Sinta-se à vontade para abrir PRs ou issues caso identifique melhorias ou queira contribuir para o projeto.

---


**Feito por Simone 🚀**
