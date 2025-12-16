<?php
session_start();
header('Content-Type: application/json');

require_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método inválido']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['rememberMe']) ? true : false;

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Email e password são obrigatórios']);
    exit;
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Email inválido']);
    exit;
}

$stmt = $conn->prepare("SELECT id_utilizador, primeiro_nome, ultimo_nome, email, password_hash, ativo, tipo FROM utilizador WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Email ou password incorretos']);
    exit;
}

$user = $result->fetch_assoc();

// Verificar se a conta está ativa
if (!$user['ativo']) {
    echo json_encode(['success' => false, 'error' => 'Conta desativada. Contacte o suporte.']);
    exit;
}

// Verificar password
if (!password_verify($password, $user['password_hash'])) {
    echo json_encode(['success' => false, 'error' => 'Email ou password incorretos']);
    exit;
}

// Login bem-sucedido - criar sessão
$_SESSION['id_utilizador'] = $user['id_utilizador'];
$_SESSION['user_name'] = $user['primeiro_nome'] . ' ' . $user['ultimo_nome'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['tipo'] = $user['tipo'];
$_SESSION['logged_in'] = true;

// Se "Lembrar-me" estiver marcado, criar cookie 
if ($remember) {
    $token = bin2hex(random_bytes(32));
    
    // Guardar token na BD 
    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
    setcookie('user_id', $user['id_utilizador'], time() + (30 * 24 * 60 * 60), '/', '', false, true);
}

// Atualizar último login 
$stmt = $conn->prepare("UPDATE utilizador SET data_ultima_atualizacao = NOW() WHERE id_utilizador = ?");
$stmt->bind_param("i", $user['id_utilizador']);
$stmt->execute();

echo json_encode([
    'success' => true, 
    'message' => 'Login efetuado com sucesso!',
    'redirect' => 'home.php',
    'user' => [
        'id' => $user['id_utilizador'],
        'name' => $user['primeiro_nome'] . ' ' . $user['ultimo_nome']
    ]
]);

$stmt->close();
$conn->close();
?>