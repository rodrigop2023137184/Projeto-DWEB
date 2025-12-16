<?php
session_start();
header('Content-Type: application/json');

// Verificar se está logado
if (!isset($_SESSION['id_utilizador'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Sessão expirada.'
    ]);
    exit;
}

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error' => 'Método inválido.'
    ]);
    exit;
}

// Receber dados
$id_carrinho = isset($_POST['id_carrinho']) ? intval($_POST['id_carrinho']) : 0;
$quantidade = isset($_POST['quantidade']) ? intval($_POST['quantidade']) : 0;

// Validações
if ($id_carrinho <= 0 || $quantidade <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Dados inválidos.'
    ]);
    exit;
}

if ($quantidade > 10) {
    echo json_encode([
        'success' => false,
        'error' => 'Quantidade máxima: 10 unidades.'
    ]);
    exit;
}

require_once 'includes/db_connect.php';

try {
    $id_utilizador = $_SESSION['id_utilizador'];
    
    // Buscar dados do item e produto
    $stmt = $conn->prepare("
        SELECT c.id_produto, c.preco_unitario, p.stock_total
        FROM carrinho c
        INNER JOIN produto p ON c.id_produto = p.id_produto
        WHERE c.id_carrinho = ? AND c.id_utilizador = ?
    ");
    $stmt->bind_param("ii", $id_carrinho, $id_utilizador);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'error' => 'Item não encontrado.'
        ]);
        exit;
    }
    
    $item = $result->fetch_assoc();
    
    // Verificar stock
    if ($quantidade > $item['stock_total']) {
        echo json_encode([
            'success' => false,
            'error' => 'Stock insuficiente. Disponível: ' . $item['stock_total']
        ]);
        exit;
    }
    
    // Atualizar quantidade
    $stmt = $conn->prepare("
        UPDATE carrinho 
        SET quantidade = ?, data_atualizacao = CURRENT_TIMESTAMP 
        WHERE id_carrinho = ? AND id_utilizador = ?
    ");
    $stmt->bind_param("iii", $quantidade, $id_carrinho, $id_utilizador);
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao atualizar quantidade.');
    }
    
    // Calcular novo subtotal do item
    $subtotal = $quantidade * $item['preco_unitario'];
    
    // Calcular total do carrinho
    $stmt = $conn->prepare("
        SELECT SUM(quantidade * preco_unitario) as total 
        FROM carrinho 
        WHERE id_utilizador = ?
    ");
    $stmt->bind_param("i", $id_utilizador);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_row = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'message' => 'Quantidade atualizada.',
        'subtotal' => floatval($subtotal),
        'total_carrinho' => floatval($total_row['total'])
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao atualizar quantidade: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao atualizar. Tente novamente.'
    ]);
}

$conn->close();
?>