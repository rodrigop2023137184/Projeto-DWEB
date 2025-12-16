<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode([
        'success' => false,
        'error' => 'Sessão expirada. Por favor, faça login novamente.'
    ]);
    exit;
}

if (!isset($_SESSION['id_utilizador'])) {
    echo json_encode([
        'success' => false,
        'error' => 'ID de utilizador não encontrado. Faça login novamente.',
        'debug' => 'Sessão: ' . print_r($_SESSION, true)
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error' => 'Método inválido.'
    ]);
    exit;
}

$id_carrinho = isset($_POST['id_carrinho']) ? intval($_POST['id_carrinho']) : 0;

if ($id_carrinho <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Item inválido.',
        'debug' => 'ID recebido: ' . $id_carrinho
    ]);
    exit;
}

require_once 'includes/db_connect.php';

try {
    $id_utilizador = $_SESSION['id_utilizador'];

    error_log("Tentando remover: id_carrinho=$id_carrinho, id_utilizador=$id_utilizador");
    
    // Remover item 
    $stmt = $conn->prepare("DELETE FROM carrinho WHERE id_carrinho = ? AND id_utilizador = ?");
    
    if (!$stmt) {
        throw new Exception("Erro ao preparar statement: " . $conn->error);
    }
    
    $stmt->bind_param("ii", $id_carrinho, $id_utilizador);
    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao executar: " . $stmt->error);
    }
    
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Item removido do carrinho.'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'error' => 'Item não encontrado ou não pertence a você.',
            'debug' => "Affected rows: 0, id_carrinho=$id_carrinho, id_utilizador=$id_utilizador"
        ]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Erro ao remover: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'Erro ao remover item.',
        'debug' => $e->getMessage()
    ]);
}

$conn->close();
?>