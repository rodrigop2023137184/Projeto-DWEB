<?php
session_start();
require_once '../includes/middleware_admin.php';
require_once '../includes/db_connect.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ========== CRIAR PRODUTO ==========
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Receber dados
    $nome = trim($_POST['nome']);
    $categoria = trim($_POST['categoria']);
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = floatval($_POST['preco']);
    $stock_total = intval($_POST['stock_total']);
    $status = $_POST['status'];
    $tem_tamanhos = intval($_POST['tem_tamanhos']);
    $tipo_tamanho = ($tem_tamanhos && !empty($_POST['tipo_tamanho'])) ? $_POST['tipo_tamanho'] : null;
    
    // Validações
    if (empty($nome) || empty($categoria) || $preco <= 0 || $stock_total < 0) {
        $_SESSION['error'] = 'Por favor, preencha todos os campos obrigatórios corretamente.';
        header('Location: produtos.php');
        exit;
    }
    
    // Upload da imagem
    if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = 'Por favor, faça upload de uma imagem.';
        header('Location: produtos.php');
        exit;
    }
    
    $file = $_FILES['imagem'];
    $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed)) {
        $_SESSION['error'] = 'Formato de imagem inválido. Use JPG, PNG ou WEBP.';
        header('Location: produtos.php');
        exit;
    }
    
    if ($file['size'] > $max_size) {
        $_SESSION['error'] = 'A imagem é muito grande. Máximo: 5MB.';
        header('Location: produtos.php');
        exit;
    }
  
    $upload_dir = '../imgs/produtos/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Nome único para o arquivo
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'produto_' . time() . '_' . uniqid() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        $_SESSION['error'] = 'Erro ao fazer upload da imagem.';
        header('Location: produtos.php');
        exit;
    }
    
    $imagem_path = 'imgs/produtos/' . $filename;
    
    // Gerar slug
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nome)));
    
    // Inserir na BD
    try {
        $stmt = $conn->prepare("
            INSERT INTO produto (
                nome, slug, categoria, descricao, 
                preco, stock_total, imagem, status, 
                tem_tamanhos, tipo_tamanho
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            "ssssdissii",
            $nome,
            $slug,
            $categoria,
            $descricao,
            $preco,
            $stock_total,
            $imagem_path,
            $status,
            $tem_tamanhos,
            $tipo_tamanho
        );
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Produto adicionado com sucesso!';
        } else {
            throw new Exception($stmt->error);
        }
        
    } catch (Exception $e) {
        // Eliminar imagem em caso de erro
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        $_SESSION['error'] = 'Erro ao adicionar produto: ' . $e->getMessage();
    }
    
    header('Location: produtos.php');
    exit;
}

// ========== ATUALIZAR PRODUTO ==========
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_produto = intval($_POST['id_produto']);
    $imagem_atual = $_POST['imagem_atual'];
    
    // Receber dados
    $nome = trim($_POST['nome']);
    $categoria = trim($_POST['categoria']);
    $descricao = trim($_POST['descricao'] ?? '');
    $peso = trim($_POST['peso'] ?? '');
    $material = trim($_POST['material'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $preco = floatval($_POST['preco']);
    $stock_total = intval($_POST['stock_total']);
    $status = $_POST['status'];
    $tem_tamanhos = intval($_POST['tem_tamanhos']);
    
    $tipo_tamanho = null; // Padrão é NULL
    if ($tem_tamanhos && isset($_POST['tipo_tamanho']) && $_POST['tipo_tamanho'] !== '') {
        $tipo_tamanho = $_POST['tipo_tamanho'];
    }
    
    // Validações
    if (empty($nome) || empty($categoria) || $preco <= 0 || $stock_total < 0) {
        $_SESSION['error'] = 'Por favor, preencha todos os campos obrigatórios.';
        header('Location: editar_produto.php?id=' . $id_produto);
        exit;
    }
    
    // Gerar slug se vazio
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nome)));
    }
    
    // Processar imagem (se houver nova)
    $imagem_path = $imagem_atual;
    
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['imagem'];
        $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $max_size = 5 * 1024 * 1024;
        
        if (!in_array($file['type'], $allowed)) {
            $_SESSION['error'] = 'Formato de imagem inválido. Use JPG, PNG ou WEBP.';
            header('Location: editar_produto.php?id=' . $id_produto);
            exit;
        }
        
        if ($file['size'] > $max_size) {
            $_SESSION['error'] = 'A imagem é muito grande. Máximo: 5MB.';
            header('Location: editar_produto.php?id=' . $id_produto);
            exit;
        }
        
        $upload_dir = '../imgs';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'produto_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $upload_dir . '/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            if (!empty($imagem_atual) && file_exists('../' . $imagem_atual)) {
                unlink('../' . $imagem_atual);
            }
            $imagem_path = 'imgs/' . $filename;
        } else {
            $_SESSION['error'] = 'Erro ao fazer upload da imagem.';
            header('Location: editar_produto.php?id=' . $id_produto);
            exit;
        }
    }
    
    try {
        $stmt = $conn->prepare("
            UPDATE produto SET
                nome = ?,
                slug = ?,
                categoria = ?,
                descricao = ?,
                peso = ?,
                material = ?,
                preco = ?,
                stock_total = ?,
                imagem = ?,
                status = ?,
                tem_tamanhos = ?,
                tipo_tamanho = ?
            WHERE id_produto = ?
        ");
        
        $stmt->bind_param(
            "ssssssdissisi",
            $nome,
            $slug,
            $categoria,
            $descricao,
            $peso,
            $material,
            $preco,
            $stock_total,
            $imagem_path,
            $status,
            $tem_tamanhos,
            $tipo_tamanho,
            $id_produto
        );
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Produto atualizado com sucesso!';
        } else {
            throw new Exception($stmt->error);
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Erro ao atualizar produto: ' . $e->getMessage();
    }
    
    header('Location: produtos.php');
    exit;
}

// ========== ELIMINAR PRODUTO ==========
if ($action === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        $_SESSION['error'] = 'ID inválido.';
        header('Location: produtos.php');
        exit;
    }
    
    try {
        // imagem para eliminar
        $stmt = $conn->prepare("SELECT imagem FROM produto WHERE id_produto = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $produto = $result->fetch_assoc();
        
        if (!$produto) {
            $_SESSION['error'] = 'Produto não encontrado.';
            header('Location: produtos.php');
            exit;
        }
        
        // Eliminar produto da BD
        $stmt = $conn->prepare("DELETE FROM produto WHERE id_produto = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Eliminar imagem do servidor
            $image_path = '../' . $produto['imagem'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
            
            $_SESSION['success'] = 'Produto eliminado com sucesso!';
        } else {
            throw new Exception($stmt->error);
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Erro ao eliminar produto: ' . $e->getMessage();
    }
    
    header('Location: produtos.php');
    exit;
}

$_SESSION['error'] = 'Ação inválida.';
header('Location: produtos.php');
exit;
?>