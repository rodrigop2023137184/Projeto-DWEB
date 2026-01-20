<?php
header('Content-Type: application/json');

require_once '../includes/db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método inválido']);
    exit;
}

$primeiro_nome = trim($_POST['firstName'] ?? '');
$ultimo_nome = trim($_POST['lastName'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['phone'] ?? null);
$data_nascimento = $_POST['birthdate'] ?? '';
$genero = $_POST['gender'] ?? null;
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirmPassword'] ?? '';

if (empty($primeiro_nome) || empty($ultimo_nome) || empty($email) || empty($data_nascimento) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Todos os campos obrigatórios devem ser preenchidos']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Email inválido']);
    exit;
}

// Verificar se passwords coincidem
if ($password !== $confirm_password) {
    echo json_encode(['success' => false, 'error' => 'As passwords não coincidem']);
    exit;
}

// Verificar tamanho mínimo da password
if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'error' => 'A password deve ter pelo menos 8 caracteres']);
    exit;
}

// Verificar se email já existe
$stmt = $conn->prepare("SELECT id_utilizador FROM utilizador WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Este email já está registado']);
    exit;
}

// Hash da password
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Gerar token de verificação (opcional)
$token_verificacao = bin2hex(random_bytes(50));

// Inserir na base de dados
$sql = "INSERT INTO utilizador (primeiro_nome, ultimo_nome, email, telefone, data_nascimento, genero, password_hash, token_verificacao) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssss", $primeiro_nome, $ultimo_nome, $email, $telefone, $data_nascimento, $genero, $password_hash, $token_verificacao);

if ($stmt->execute()) {
    
    echo json_encode([
        'success' => true, 
        'message' => 'Conta criada com sucesso!',
        'redirect' => 'login.php'
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Erro ao criar conta. Tente novamente.']);
}

$stmt->close();
$conn->close();
?>