<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_utilizador'])) {
    echo json_encode([
        'success' => true,
        'count' => 0
    ]);
    exit;
}

require_once 'includes/db_connect.php';

try {
    $id_utilizador = $_SESSION['id_utilizador'];
    
    // Contar total de itens no carrinho
    $stmt = $conn->prepare("
        SELECT SUM(quantidade) as total_items 
        FROM carrinho 
        WHERE id_utilizador = ?
    ");
    $stmt->bind_param("i", $id_utilizador);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $count = $row['total_items'] ?? 0;
    
    echo json_encode([
        'success' => true,
        'count' => intval($count)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'count' => 0,
        'error' => 'Erro ao obter contador'
    ]);
}

$conn->close();
?>