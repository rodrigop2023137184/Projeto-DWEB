<?php
session_start();
header('Content-Type: application/json');

// Log de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        echo json_encode([
            'success' => false, 
            'error' => 'Precisa de fazer login para se inscrever', 
            'redirect' => 'login.php'
        ]);
        exit;
    }

    require_once 'includes/db_connect.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'error' => 'Método inválido']);
        exit;
    }

    $id_evento = intval($_POST['id_evento'] ?? 0);
    $distancia = trim($_POST['distancia'] ?? '');
    $tamanho = trim($_POST['tamanho'] ?? '');
    
    $id_utilizador = $_SESSION['id_utilizador'] ?? null;
    
    if (!$id_utilizador) {
        echo json_encode(['success' => false, 'error' => 'Sessão inválida. Faça login novamente.']);
        exit;
    }

    if (empty($id_evento) || empty($distancia) || empty($tamanho)) {
        echo json_encode(['success' => false, 'error' => 'Todos os campos são obrigatórios']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM evento WHERE id_evento = ? AND status = 'ativo'");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Erro na preparação da query: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("i", $id_evento);
    $stmt->execute();
    $evento = $stmt->get_result()->fetch_assoc();

    if (!$evento) {
        echo json_encode(['success' => false, 'error' => 'Evento não encontrado ou inativo']);
        exit;
    }

    $stmt = $conn->prepare("SELECT COUNT(*) as total_inscritos FROM inscricao_evento WHERE id_evento = ? AND status = 'ativa'");
    $stmt->bind_param("i", $id_evento);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $vagas_ocupadas_reais = $result['total_inscritos'];
    
    $vagas_disponiveis = $evento['vagas_totais'] - $vagas_ocupadas_reais;
    
    if ($vagas_disponiveis <= 0) {
        echo json_encode(['success' => false, 'error' => 'Evento esgotado! Não há vagas disponíveis.']);
        exit;
    }

    // Verificar se o utilizador já está inscrito neste evento
    $stmt = $conn->prepare("SELECT id_inscricao FROM inscricao_evento WHERE id_evento = ? AND id_utilizador = ? AND status = 'ativa'");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Erro ao verificar inscrição: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("ii", $id_evento, $id_utilizador);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Já está inscrito neste evento!']);
        exit;
    }

    // Gerar número do dorsal (único e sequencial por evento)
    $stmt = $conn->prepare("SELECT MAX(numero_dorsal) as ultimo_dorsal FROM inscricao_evento WHERE id_evento = ?");
    $stmt->bind_param("i", $id_evento);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $numero_dorsal = ($result['ultimo_dorsal'] ?? 0) + 1;

    // Iniciar transação para garantir consistência
    $conn->begin_transaction();

    try {
        // Inserir inscrição
        $sql = "INSERT INTO inscricao_evento (
            id_evento, 
            id_utilizador, 
            distancia_escolhida, 
            tamanho_tshirt, 
            valor_pago, 
            metodo_pagamento, 
            estado_pagamento, 
            numero_dorsal, 
            status,
            data_inscricao
        ) VALUES (?, ?, ?, ?, ?, 'pendente', 'pendente', ?, 'ativa', NOW())";

        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Erro ao preparar inscrição: ' . $conn->error);
        }
        
        $stmt->bind_param("iissdi", $id_evento, $id_utilizador, $distancia, $tamanho, $evento['preco'], $numero_dorsal);

        if (!$stmt->execute()) {
            throw new Exception('Erro ao executar inscrição: ' . $stmt->error);
        }

        // Atualizar vagas ocupadas no evento
        $stmt = $conn->prepare("UPDATE evento SET vagas_ocupadas = vagas_ocupadas + 1 WHERE id_evento = ?");
        $stmt->bind_param("i", $id_evento);
        
        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar vagas: ' . $stmt->error);
        }

        // Confirmar transação
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Inscrição realizada com sucesso!',
            'numero_dorsal' => $numero_dorsal,
            'evento' => $evento['titulo']
        ]);

    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $conn->rollback();
        throw $e;
    }

    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erro ao processar inscrição: ' . $e->getMessage()]);
}
?>