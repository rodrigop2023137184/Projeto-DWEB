<?php
session_start();
require_once '../includes/middleware_admin.php';
require_once '../includes/db_connect.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ========== CRIAR EVENTO ==========
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Receber dados básicos
    $titulo = trim($_POST['titulo']);
    $subtitulo = trim($_POST['subtitulo'] ?? '');
    $descricao_curta = trim($_POST['descricao_curta']);
    $descricao_completa = trim($_POST['descricao_completa'] ?? '');
    
    // Data e local
    $data_evento = $_POST['data_evento'];
    $hora_evento = $_POST['hora_evento'];
    $duracao_estimada = trim($_POST['duracao_estimada'] ?? '');
    $local_nome = trim($_POST['local_nome']);
    $local_endereco = trim($_POST['local_endereco'] ?? '');
    
    // Categoria e preço 
    $categoria = strtolower(trim($_POST['categoria']));
    $preco = floatval($_POST['preco']);
    $vagas_totais = !empty($_POST['vagas_totais']) ? intval($_POST['vagas_totais']) : null;
    $status = strtolower(trim($_POST['status']));
    
    // JSON fields
    $distancias_disponiveis = $_POST['distancias_disponiveis'] ?? '[]';
    $itens_incluidos = $_POST['itens_incluidos'] ?? '[]';
    $detalhes_percurso = trim($_POST['detalhes_percurso'] ?? '');
    
    // Se detalhes_percurso estiver vazio, usar null
    if (empty($detalhes_percurso)) {
        $detalhes_percurso = null;
    } else {
        // Validar se é JSON válido
        $test_json = json_decode($detalhes_percurso);
        if ($test_json === null && json_last_error() !== JSON_ERROR_NONE) {
            $detalhes_percurso = null;
        }
    }
    
    // Boolean
    $tem_transporte = isset($_POST['tem_transporte']) ? 1 : 0;
    
    // Validações
    if (empty($titulo) || empty($descricao_curta) || empty($data_evento) || empty($hora_evento) || empty($local_nome) || $preco < 0) {
        $_SESSION['error'] = 'Por favor, preencha todos os campos obrigatórios.';
        header('Location: eventos.php');
        exit;
    }
    
    // Validar categoria
    $categorias_validas = ['corrida', 'trail', 'maratona', 'caminhada'];
    if (!in_array($categoria, $categorias_validas)) {
        $_SESSION['error'] = 'Categoria inválida. Use: corrida, trail, maratona ou caminhada.';
        header('Location: eventos.php');
        exit;
    }
    
    // Validar status
    $status_validos = ['ativo', 'cancelado', 'concluido'];
    if (!in_array($status, $status_validos)) {
        $_SESSION['error'] = 'Status inválido.';
        header('Location: eventos.php');
        exit;
    }
    
    // Validar JSON
    $distancias_json = json_decode($distancias_disponiveis);
    $itens_json = json_decode($itens_incluidos);
    
    if ($distancias_json === null) $distancias_disponiveis = '[]';
    if ($itens_json === null) $itens_incluidos = '[]';
    
    // Upload da imagem
    if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = 'Por favor, faça upload de uma imagem.';
        header('Location: eventos.php');
        exit;
    }
    
    $file = $_FILES['imagem'];
    $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed)) {
        $_SESSION['error'] = 'Formato de imagem inválido. Use JPG, PNG ou WEBP.';
        header('Location: eventos.php');
        exit;
    }
    
    if ($file['size'] > $max_size) {
        $_SESSION['error'] = 'A imagem é muito grande. Máximo: 5MB.';
        header('Location: eventos.php');
        exit;
    }
    
    // Criar pasta se não existir
    $upload_dir = '../imgs/eventos';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Nome único para o arquivo
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'evento_' . time() . '_' . uniqid() . '.' . $extension;
    $filepath = $upload_dir . '/' . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        $_SESSION['error'] = 'Erro ao fazer upload da imagem.';
        header('Location: eventos.php');
        exit;
    }
    
    // Caminho relativo para guardar na BD
    $imagem_path = 'imgs/eventos/' . $filename;
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO evento (
                titulo, subtitulo, descricao_curta, descricao_completa,
                data_evento, hora_evento, duracao_estimada,
                local_nome, local_endereco,
                imagem,
                distancias_disponiveis, detalhes_percurso, itens_incluidos,
                vagas_totais, vagas_ocupadas,
                preco, categoria,
                tem_transporte, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            throw new Exception("Erro ao preparar statement: " . $conn->error);
        }
        
        $vagas_ocupadas = 0;
        
        $stmt->bind_param(
            "sssssssssssssidssis",
            $titulo,
            $subtitulo,
            $descricao_curta,
            $descricao_completa,
            $data_evento,
            $hora_evento,
            $duracao_estimada,
            $local_nome,
            $local_endereco,
            $imagem_path,
            $distancias_disponiveis,
            $detalhes_percurso,
            $itens_incluidos,
            $vagas_totais,
            $vagas_ocupadas,
            $preco,
            $categoria,
            $tem_transporte,
            $status
        );
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Evento criado com sucesso!';
        } else {
            throw new Exception($stmt->error);
        }
        
    } catch (Exception $e) {
        // Eliminar imagem em caso de erro
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        $_SESSION['error'] = 'Erro ao criar evento: ' . $e->getMessage();
    }
    
    header('Location: eventos.php');
    exit;
}

// ========== ATUALIZAR EVENTO ==========
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_evento = intval($_POST['id_evento']);
    $imagem_atual = $_POST['imagem_atual'];
    
    // Receber dados básicos
    $titulo = trim($_POST['titulo']);
    $subtitulo = trim($_POST['subtitulo'] ?? '');
    $descricao_curta = trim($_POST['descricao_curta']);
    $descricao_completa = trim($_POST['descricao_completa'] ?? '');
    
    // Data e local
    $data_evento = $_POST['data_evento'];
    $hora_evento = $_POST['hora_evento'];
    $duracao_estimada = trim($_POST['duracao_estimada'] ?? '');
    $local_nome = trim($_POST['local_nome']);
    $local_endereco = trim($_POST['local_endereco'] ?? '');
    
    // Categoria e preço
    $categoria = strtolower(trim($_POST['categoria']));
    $preco = floatval($_POST['preco']);
    $vagas_totais = !empty($_POST['vagas_totais']) ? intval($_POST['vagas_totais']) : null;
    $status = strtolower(trim($_POST['status']));
    
    // JSON fields
    $distancias_disponiveis = $_POST['distancias_disponiveis'] ?? '[]';
    $itens_incluidos = $_POST['itens_incluidos'] ?? '[]';
    $detalhes_percurso = trim($_POST['detalhes_percurso'] ?? '');
    
    if (empty($detalhes_percurso)) {
        $detalhes_percurso = null;
    }
    
    // Boolean
    $tem_transporte = isset($_POST['tem_transporte']) ? 1 : 0;
    
    // Validações
    if (empty($titulo) || empty($descricao_curta) || empty($data_evento) || empty($hora_evento) || empty($local_nome) || $preco < 0) {
        $_SESSION['error'] = 'Por favor, preencha todos os campos obrigatórios.';
        header('Location: editar_evento.php?id=' . $id_evento);
        exit;
    }
    
    // Validar categoria
    $categorias_validas = ['corrida', 'trail', 'maratona', 'caminhada'];
    if (!in_array($categoria, $categorias_validas)) {
        $_SESSION['error'] = 'Categoria inválida.';
        header('Location: editar_evento.php?id=' . $id_evento);
        exit;
    }
    
    // Validar status
    $status_validos = ['ativo', 'cancelado', 'concluido'];
    if (!in_array($status, $status_validos)) {
        $_SESSION['error'] = 'Status inválido.';
        header('Location: editar_evento.php?id=' . $id_evento);
        exit;
    }
    
    // Processar imagem 
    $imagem_path = $imagem_atual; // Manter atual por padrão
    
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['imagem'];
        $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $max_size = 5 * 1024 * 1024;
        
        if (!in_array($file['type'], $allowed)) {
            $_SESSION['error'] = 'Formato de imagem inválido.';
            header('Location: editar_evento.php?id=' . $id_evento);
            exit;
        }
        
        if ($file['size'] > $max_size) {
            $_SESSION['error'] = 'A imagem é muito grande. Máximo: 5MB.';
            header('Location: editar_evento.php?id=' . $id_evento);
            exit;
        }
        
        $upload_dir = '../imgs/eventos';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'evento_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $upload_dir . '/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Eliminar imagem antiga
            if (!empty($imagem_atual) && file_exists('../' . $imagem_atual)) {
                unlink('../' . $imagem_atual);
            }
            $imagem_path = 'imgs/eventos/' . $filename;
        }
    }
    
    try {
        $stmt = $conn->prepare("
            UPDATE evento SET
                titulo = ?,
                subtitulo = ?,
                descricao_curta = ?,
                descricao_completa = ?,
                data_evento = ?,
                hora_evento = ?,
                duracao_estimada = ?,
                local_nome = ?,
                local_endereco = ?,
                imagem = ?,
                distancias_disponiveis = ?,
                detalhes_percurso = ?,
                itens_incluidos = ?,
                vagas_totais = ?,
                preco = ?,
                categoria = ?,
                tem_transporte = ?,
                status = ?
            WHERE id_evento = ?
        ");
        
        if (!$stmt) {
            throw new Exception("Erro ao preparar statement: " . $conn->error);
        }
        
        $stmt->bind_param(
            "sssssssssssssidsisi",
            $titulo,
            $subtitulo,
            $descricao_curta,
            $descricao_completa,
            $data_evento,
            $hora_evento,
            $duracao_estimada,
            $local_nome,
            $local_endereco,
            $imagem_path,
            $distancias_disponiveis,
            $detalhes_percurso,
            $itens_incluidos,
            $vagas_totais,
            $preco,
            $categoria,
            $tem_transporte,
            $status,
            $id_evento
        );
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Evento atualizado com sucesso!';
        } else {
            throw new Exception($stmt->error);
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Erro ao atualizar evento: ' . $e->getMessage();
    }
    
    header('Location: eventos.php');
    exit;
}

// ========== ELIMINAR EVENTO ==========
if ($action === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        $_SESSION['error'] = 'ID inválido.';
        header('Location: eventos.php');
        exit;
    }
    
    try {
        // Buscar imagem para eliminar
        $stmt = $conn->prepare("SELECT imagem FROM evento WHERE id_evento = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $evento = $result->fetch_assoc();
        
        if (!$evento) {
            $_SESSION['error'] = 'Evento não encontrado.';
            header('Location: eventos.php');
            exit;
        }
        
        // Eliminar evento da BD
        $stmt = $conn->prepare("DELETE FROM evento WHERE id_evento = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Eliminar imagem do servidor
            if (!empty($evento['imagem'])) {
                $image_path = '../' . $evento['imagem'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            $_SESSION['success'] = 'Evento eliminado com sucesso!';
        } else {
            throw new Exception($stmt->error);
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Erro ao eliminar evento: ' . $e->getMessage();
    }
    
    header('Location: eventos.php');
    exit;
}

// Se chegou aqui sem action válida
$_SESSION['error'] = 'Ação inválida.';
header('Location: eventos.php');
exit;
?>