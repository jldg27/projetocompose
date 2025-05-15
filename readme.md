# 🎲 API de Brinquedos com Interface Web (PHP + MySQL + Docker)

Essa é uma API simples feita em PHP com MySQL e Docker, com uma interface HTML para interagir com ela. É ideal para aprender como funcionam APIs, consumo via JavaScript e como usar Docker para subir um ambiente completo.

---

## ✅ Requisitos

Antes de começar, você precisa ter:

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/install/)

---

## ▶️ Passo a passo para rodar o projeto

1. Crie uma pasta para o projeto e coloque dentro dela os seguintes arquivos:

---

### 1️⃣ Crie um arquivo chamado `docker-compose.yml` com o seguinte conteúdo:

```yaml
version: '3.8'

services:
  app:
    image: php:8.2-apache
    container_name: brinquedos-app
    volumes:
      - .:/var/www/html
    ports:
      - "8080:80"
    depends_on:
      - mysql

  mysql:
    image: mysql:8.0
    container_name: brinquedos-db
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: meu_banco
      MYSQL_USER: meu_usuario
      MYSQL_PASSWORD: minha_senha
    ports:
      - "3306:3306"

2️⃣ Crie um arquivo chamado index.php com o seguinte conteúdo:

<?php
$host = 'mysql';
$user = 'meu_usuario';
$pass = 'minha_senha';
$db = 'meu_banco';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

function criarTabela() {
    global $conn;
    $sql = "CREATE TABLE IF NOT EXISTS brinquedos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        descricao TEXT,
        preco DECIMAL(10, 2)
    )";
    $conn->query($sql);
}

criarTabela();

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

if (preg_match('/\/index\.php\/brinquedos/', $requestUri)) {
    if ($requestMethod === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $nome = $data['nome'] ?? '';
        $descricao = $data['descricao'] ?? '';
        $preco = $data['preco'] ?? 0;

        if ($nome && $preco) {
            $sql = "INSERT INTO brinquedos (nome, descricao, preco) VALUES ('$nome', '$descricao', '$preco')";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(["message" => "Brinquedo cadastrado com sucesso!"]);
            } else {
                echo json_encode(["error" => "Erro ao cadastrar brinquedo: " . $conn->error]);
            }
        } else {
            echo json_encode(["error" => "Nome e preço são obrigatórios."]);
        }
    } elseif ($requestMethod === 'GET') {
        $sql = "SELECT * FROM brinquedos";
        $result = $conn->query($sql);

        $brinquedos = [];
        while ($row = $result->fetch_assoc()) {
            $brinquedos[] = $row;
        }
        echo json_encode($brinquedos);
    } elseif ($requestMethod === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? 0;
        $nome = $data['nome'] ?? '';
        $descricao = $data['descricao'] ?? '';
        $preco = $data['preco'] ?? 0;

        if ($id && $nome && $preco) {
            $sql = "UPDATE brinquedos SET nome='$nome', descricao='$descricao', preco='$preco' WHERE id=$id";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(["message" => "Brinquedo atualizado com sucesso!"]);
            } else {
                echo json_encode(["error" => "Erro ao atualizar brinquedo: " . $conn->error]);
            }
        } else {
            echo json_encode(["error" => "ID, nome e preço são obrigatórios."]);
        }
    } elseif ($requestMethod === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? 0;

        if ($id) {
            $sql = "DELETE FROM brinquedos WHERE id=$id";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(["message" => "Brinquedo deletado com sucesso!"]);
            } else {
                echo json_encode(["error" => "Erro ao deletar brinquedo: " . $conn->error]);
            }
        } else {
            echo json_encode(["error" => "ID é obrigatório."]);
        }
    } else {
        echo json_encode(["error" => "Método HTTP não permitido."]);
    }
} else {
    echo json_encode(["error" => "Rota não encontrada."]);
}

$conn->close();
?>

3️⃣ Crie um arquivo index.html com o seguinte conteúdo:

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Brinquedos API</title>
  <style>
    body { font-family: Arial; padding: 20px; }
    input, button { margin: 5px 0; }
  </style>
</head>
<body>
  <h1>Gerenciador de Brinquedos</h1>

  <h2>Cadastrar Brinquedo</h2>
  <input type="text" id="nome" placeholder="Nome"><br>
  <input type="text" id="descricao" placeholder="Descrição"><br>
  <input type="number" id="preco" placeholder="Preço"><br>
  <button onclick="cadastrarBrinquedo()">Cadastrar</button>

  <h2>Lista de Brinquedos</h2>
  <button onclick="listarBrinquedos()">Atualizar Lista</button>
  <ul id="listaBrinquedos"></ul>

  <script>
    const API_URL = 'http://localhost:8080/index.php/brinquedos';

    function listarBrinquedos() {
      fetch(API_URL)
        .then(res => res.json())
        .then(data => {
          const lista = document.getElementById('listaBrinquedos');
          lista.innerHTML = '';

          if (Array.isArray(data)) {
            data.forEach(b => {
              const li = document.createElement('li');
              li.innerHTML = `<strong>${b.nome}</strong> - R$${b.preco.toFixed(2)} 
                              <button onclick="deletarBrinquedo(${b.id})">Excluir</button>`;
              lista.appendChild(li);
            });
          } else {
            lista.innerHTML = `<li>${data.message || data.error}</li>`;
          }
        });
    }

    function cadastrarBrinquedo() {
      const nome = document.getElementById('nome').value;
      const descricao = document.getElementById('descricao').value;
      const preco = parseFloat(document.getElementById('preco').value);

      fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nome, descricao, preco })
      })
        .then(res => res.json())
        .then(data => {
          alert(data.message || data.error);
          listarBrinquedos();
        });
    }

    function deletarBrinquedo(id) {
      if (!confirm('Tem certeza que deseja excluir este brinquedo?')) return;

      fetch(API_URL, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      })
        .then(res => res.json())
        .then(data => {
          alert(data.message || data.error);
          listarBrinquedos();
        });
    }

    listarBrinquedos();
  </script>
</body>
</html>




🧪 Como testar com Postman
Abra o Postman e envie uma requisição para:

GET → http://localhost:8080/index.php/brinquedos
Retorna todos os brinquedos cadastrados.

POST → http://localhost:8080/index.php/brinquedos
Cadastra um novo brinquedo. Envie um JSON assim no corpo da requisição:
{
  "nome": "Carrinho",
  "descricao": "Carrinho vermelho",
  "preco": 25.90
}

PUT → http://localhost:8080/index.php/brinquedos
Atualiza um brinquedo. Exemplo de JSON:
{
  "id": 1,
  "nome": "Carrinho Azul",
  "descricao": "Versão nova",
  "preco": 30.00
}

DELETE → http://localhost:8080/index.php/brinquedos
Remove um brinquedo pelo ID. Exemplo de JSON:
{
  "id": 1
}

✅ Pronto!
Depois de subir o projeto com docker-compose up -d, a aplicação estará acessível em:
http://localhost:8080

Você pode cadastrar, listar e excluir brinquedos tanto pela interface web quanto pelo Postman.

