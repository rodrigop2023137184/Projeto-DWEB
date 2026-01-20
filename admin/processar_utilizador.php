<?php
session_start();
require_once 'includesadm/middleware_admin.php';
require_once '../includes/db_connect.php';

if (!isset($_GET['action']) || !isset($_GET['id'])) {
    $_SESSION['error'] = 'Ação inválida.';
    header('Location: utilizadores.php');
    exit;
}

$action = $_GET['action'];
$user_id = (int)$_GET['id'];

// Verificar se o utilizador existe
$stmt = $conn->prepare("SELECT * FROM utilizador WHERE id_utilizador = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    $_SESSION['error'] = 'Utilizador não encontrado.';
    header('Location: utilizadores.php');
    exit;
}

// Prevenir que o admin modifique a si mesmo em certas ações
if ($user_id == $_SESSION['id_utilizador'] && in_array($action, ['toggle_status', 'toggle_admin', 'toggle_block', 'delete'])) {
    $_SESSION['error'] = 'Não pode executar esta ação em si mesmo.';
    header('Location: utilizadores.php');
    exit;
}

switch ($action) {
    case 'toggle_status':
        $new_status = $user['ativo'] ? 0 : 1;
        $stmt = $conn->prepare("UPDATE utilizador SET ativo = ?, data_ultima_atualizacao = NOW() WHERE id_utilizador = ?");
        $stmt->bind_param("ii", $new_status, $user_id);
        
        if ($stmt->execute()) {
            $status_text = $new_status ? 'ativado' : 'desativado';
            $_SESSION['success'] = "Utilizador {$user['primeiro_nome']} {$user['ultimo_nome']} foi {$status_text} com sucesso.";
        } else {
            $_SESSION['error'] = 'Erro ao alterar o estado do utilizador.';
        }
        break;
        
    case 'toggle_block':
        // Verificar se o utilizador está bloqueado
        $bloqueado_ate = $user['bloqueado_ate'];
        $is_blocked = false;
        
        if ($bloqueado_ate) {
            $bloqueado = new DateTime($bloqueado_ate);
            $agora = new DateTime();
            $is_blocked = $bloqueado > $agora;
        }
        
        if ($is_blocked) {
            // Desbloquear
            $stmt = $conn->prepare("UPDATE utilizador SET bloqueado_ate = NULL, tentativas_login = 0, data_ultima_atualizacao = NOW() WHERE id_utilizador = ?");
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Utilizador {$user['primeiro_nome']} {$user['ultimo_nome']} foi desbloqueado com sucesso.";
            } else {
                $_SESSION['error'] = 'Erro ao desbloquear o utilizador.';
            }
        } else {
            // Bloquear
            $bloqueado_ate = date('Y-m-d H:i:s', strtotime('+7 days'));
            $stmt = $conn->prepare("UPDATE utilizador SET bloqueado_ate = ?, data_ultima_atualizacao = NOW() WHERE id_utilizador = ?");
            $stmt->bind_param("si", $bloqueado_ate, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Utilizador {$user['primeiro_nome']} {$user['ultimo_nome']} foi bloqueado por 7 dias.";
            } else {
                $_SESSION['error'] = 'Erro ao bloquear o utilizador.';
            }
        }
        break;
        
        
    case 'delete':
        
        $stmt = $conn->prepare("DELETE FROM utilizador WHERE id_utilizador = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Utilizador {$user['primeiro_nome']} {$user['ultimo_nome']} foi eliminado com sucesso.";
        } else {
            $_SESSION['error'] = 'Erro ao eliminar o utilizador. Pode existir dados associados.';
        }
        break;
        
    default:
        $_SESSION['error'] = 'Ação não reconhecida.';
        break;
}

header('Location: utilizadores.php');
exit;
?>