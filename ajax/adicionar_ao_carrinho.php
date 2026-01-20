<?php
session_start();
header('Content-Type: application/json');

// Verificar se o utilizador está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode([
        'success' => false, 
        'error' => 'Precisa de fazer login para adicionar produtos ao carrinho',
        'redirect' => 'login.php'
    ]);
    exit;
}

// Conexão à base de dados
require_once '../includes/db_connect.php';

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método inválido']);
    exit;
}

// Receber dados
$id_produto = intval($_POST['id_produto'] ?? 0);
$quantidade = intval($_POST['quantidade'] ?? 1);
$tamanho = trim($_POST['tamanho'] ?? '');
$id_utilizador = $_SESSION['id_utilizador'];

// Validações
if (empty($id_produto) || $quantidade < 1) {
    echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
    exit;
}

// Limitar quantidade máxima por adição
if ($quantidade > 10) {
    echo json_encode(['success' => false, 'error' => 'Quantidade máxima: 10 unidades por vez']);
    exit;
}

// Verificar se o produto existe e tem stock
$stmt = $conn->prepare("SELECT * FROM produto WHERE id_produto = ? AND status = 'ativo'");
$stmt->bind_param("i", $id_produto);
$stmt->execute();
$produto = $stmt->get_result()->fetch_assoc();

if (!$produto) {
    echo json_encode(['success' => false, 'error' => 'Produto não encontrado']);
    exit;
}

// Verificar se o produto requer tamanho
if ($produto['tem_tamanhos'] && empty($tamanho)) {
    echo json_encode(['success' => false, 'error' => 'Por favor, selecione um tamanho']);
    exit;
}

// Verificar stock disponível
if ($produto['stock_total'] < $quantidade) {
    echo json_encode([
        'success' => false, 
        'error' => 'Stock insuficiente. Disponível: ' . $produto['stock_total'] . ' unidades'
    ]);
    exit;
}

// Verificar se produto com tamanho já está no carrinho
$stmt = $conn->prepare("SELECT id_carrinho, quantidade FROM carrinho WHERE id_utilizador = ? AND id_produto = ? AND tamanho_escolhido <=> ?");
$stmt->bind_param("iis", $id_utilizador, $id_produto, $tamanho);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Produto já existe no carrinho - atualizar quantidade
    $item = $result->fetch_assoc();
    $nova_quantidade = $item['quantidade'] + $quantidade;
    
    // Verificar se não excede o stock
    if ($nova_quantidade > $produto['stock_total']) {
        echo json_encode([
            'success' => false, 
            'error' => 'Quantidade total excederia o stock disponível'
        ]);
        exit;
    }
    
    // Limitar quantidade total a 10
    if ($nova_quantidade > 10) {
        $nova_quantidade = 10;
        echo json_encode([
            'success' => true, 
            'message' => 'Quantidade atualizada para o máximo permitido (10 unidades)',
            'action' => 'updated'
        ]);
    } else {
        $stmt = $conn->prepare("UPDATE carrinho SET quantidade = ?, data_atualizacao = NOW() WHERE id_carrinho = ?");
        $stmt->bind_param("ii", $nova_quantidade, $item['id_carrinho']);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'Quantidade atualizada no carrinho!',
                'action' => 'updated'
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erro ao atualizar carrinho']);
        }
    }
} else {
    // Adicionar novo item ao carrinho
    $stmt = $conn->prepare("INSERT INTO carrinho (id_utilizador, id_produto, quantidade, tamanho_escolhido, preco_unitario) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisd", $id_utilizador, $id_produto, $quantidade, $tamanho, $produto['preco']);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Produto adicionado ao carrinho!',
            'action' => 'added'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erro ao adicionar ao carrinho: ' . $stmt->error]);
    }
}

$stmt->close();
$conn->close();
?>