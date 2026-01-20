<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: carrinho.php');
    exit;
}

require_once 'includes/db_connect.php';

$id_utilizador = $_SESSION['id_utilizador'];

// Receber dados do formulário
$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$postalCode = trim($_POST['postalCode'] ?? '');
$country = trim($_POST['country'] ?? 'Portugal');
$notes = trim($_POST['notes'] ?? '');
$payment = $_POST['payment'] ?? 'mbway';

// Validações básicas
if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($address) || empty($city) || empty($postalCode)) {
    $_SESSION['error'] = 'Por favor, preencha todos os campos obrigatórios.';
    header('Location: dadosdeenvio.php');
    exit;
}

try {
    $conn->begin_transaction();

    $stmt = $conn->prepare("
        SELECT 
            c.id_carrinho,
            c.id_produto,
            c.quantidade,
            c.tamanho_escolhido,
            c.preco_unitario,
            p.nome as nome_produto,
            p.stock_total,
            (c.quantidade * c.preco_unitario) as subtotal
        FROM carrinho c
        INNER JOIN produto p ON c.id_produto = p.id_produto
        WHERE c.id_utilizador = ?
    ");
    $stmt->bind_param("i", $id_utilizador);
    $stmt->execute();
    $result = $stmt->get_result();
    $itens = $result->fetch_all(MYSQLI_ASSOC);
    
    if (empty($itens)) {
        throw new Exception('Carrinho vazio.');
    }
    
    // Calcular totais
    $subtotal = 0;
    foreach ($itens as $item) {
        $subtotal += $item['subtotal'];
        
        // Verificar stock
        if ($item['quantidade'] > $item['stock_total']) {
            throw new Exception('Stock insuficiente para ' . $item['nome_produto']);
        }
    }
    
    $taxa_envio = $subtotal >= 50 ? 0 : 5.00;
    $total = $subtotal + $taxa_envio;
    
    // Nome completo para envio
    $nome_destinatario = $firstName . ' ' . $lastName;
    
    // Mapear métodos de pagamento
    $metodos_validos = ['mbway', 'multibanco', 'cartao', 'transferencia'];
    if (!in_array($payment, $metodos_validos)) {
        $payment = 'mbway';
    }
    
    // Inserir encomenda
    $stmt = $conn->prepare("
        INSERT INTO encomenda (
            id_utilizador, 
            subtotal, 
            taxa_envio, 
            total,
            nome_destinatario,
            morada_envio,
            codigo_postal,
            cidade,
            telefone,
            metodo_pagamento,
            estado_pagamento,
            estado,
            notas_cliente
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendente', 'pendente', ?)
    ");
    
    $stmt->bind_param(
        "idddsssssss",
        $id_utilizador,
        $subtotal,
        $taxa_envio,
        $total,
        $nome_destinatario,
        $address,
        $postalCode,
        $city,
        $phone,
        $payment,
        $notes
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao criar encomenda: ' . $stmt->error);
    }
    
    $id_encomenda = $conn->insert_id;
    
    // Inserir itens da encomenda
    $stmt = $conn->prepare("
        INSERT INTO encomenda_item (
            id_encomenda,
            id_produto,
            nome_produto,
            tamanho,
            quantidade,
            preco_unitario,
            subtotal
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($itens as $item) {
        $stmt->bind_param(
            "iissidd",
            $id_encomenda,
            $item['id_produto'],
            $item['nome_produto'],
            $item['tamanho_escolhido'],
            $item['quantidade'],
            $item['preco_unitario'],
            $item['subtotal']
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Erro ao adicionar item: ' . $stmt->error);
        }
        
        // Atualizar stock do produto
        $stmt_stock = $conn->prepare("
            UPDATE produto 
            SET stock_total = stock_total - ? 
            WHERE id_produto = ?
        ");
        $stmt_stock->bind_param("ii", $item['quantidade'], $item['id_produto']);
        
        if (!$stmt_stock->execute()) {
            throw new Exception('Erro ao atualizar stock.');
        }
    }
    
    // Limpar carrinho
    $stmt = $conn->prepare("DELETE FROM carrinho WHERE id_utilizador = ?");
    $stmt->bind_param("i", $id_utilizador);
    $stmt->execute();
    
    // Commit da transação
    $conn->commit();
    
    // Gerar referência de pagamento (simulada)
    if ($payment == 'multibanco') {
        $entidade = '12345';
        $referencia = str_pad($id_encomenda, 9, '0', STR_PAD_LEFT);
        
        // Atualizar encomenda com referência
        $stmt = $conn->prepare("UPDATE encomenda SET referencia_pagamento = ? WHERE id_encomenda = ?");
        $ref_completa = "Entidade: $entidade | Referência: $referencia";
        $stmt->bind_param("si", $ref_completa, $id_encomenda);
        $stmt->execute();
    } elseif ($payment == 'mbway') {
        $referencia = 'MBWAY-' . $id_encomenda;
        $stmt = $conn->prepare("UPDATE encomenda SET referencia_pagamento = ? WHERE id_encomenda = ?");
        $stmt->bind_param("si", $referencia, $id_encomenda);
        $stmt->execute();
    }
    
    // Redirecionar para página de confirmação
    $_SESSION['encomenda_id'] = $id_encomenda;
    $_SESSION['success'] = 'Encomenda criada com sucesso!';
    header('Location: confirmacao.php?id=' . $id_encomenda);
    exit;
    
} catch (Exception $e) {
    // Rollback em caso de erro
    $conn->rollback();
    
    error_log("Erro ao processar encomenda: " . $e->getMessage());
    $_SESSION['error'] = 'Erro ao processar encomenda: ' . $e->getMessage();
    header('Location: dadosdeenvio.php');
    exit;
}

$conn->close();
?>