<?php
// Verificar se está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Verificar se é admin
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    $_SESSION['error'] = 'Acesso negado. Apenas administradores podem aceder a esta área.';
    header('Location: home.php');
    exit;
}
?>