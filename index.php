<?php
$host = 'mysql';
$user = 'meu_usuario';
$pass = 'minha_senha';
$db = 'meu_banco';

$conn = new mysqli($host, $user, $pass, $db);

// Verificar se a conexão foi bem-sucedida
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Função para criar a tabela de brinquedos, se ela não existir
function criarTabela() {
    global $conn;
    $sql = "CREATE TABLE IF NOT EXISTS brinquedos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        descricao TEXT,
        preco DECIMAL(10, 2)
    )";

    if ($conn->query($sql) === TRUE) {
        //echo "Tabela 'brinquedos' criada ou já existente.<br>";
    } else {
        echo "Erro ao criar a tabela: " . $conn->error . "<br>";
    }
}

// Chamar a função para garantir que a tabela exista
criarTabela();

// Determinar o método HTTP e a URL solicitada
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

// Verificar se a URL corresponde à "/index.php/brinquedos"
if (preg_match('/\/index\.php\/brinquedos/', $requestUri)) {
    // Cadastro de brinquedos (POST)
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
    }

    // Listar brinquedos (GET)
    elseif ($requestMethod === 'GET') {
        $sql = "SELECT * FROM brinquedos";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $brinquedos = [];
            while($row = $result->fetch_assoc()) {
                $brinquedos[] = $row;
            }
            echo json_encode($brinquedos);
        } else {
            echo json_encode(["message" => "Nenhum brinquedo encontrado."]);
        }
    }

    // Atualizar brinquedo (PUT)
    elseif ($requestMethod === 'PUT') {
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
    }

    // Deletar brinquedo (DELETE)
    elseif ($requestMethod === 'DELETE') {
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
