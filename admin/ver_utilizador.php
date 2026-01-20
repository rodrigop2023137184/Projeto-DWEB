<?php
session_start();
require_once 'includesadm/middleware_admin.php';
require_once '../includes/db_connect.php';

// Verificar se o ID foi disponibilizado
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'ID do utilizador não disponibilizado.';
    header('Location: utilizadores.php');
    exit;
}

$user_id = (int)$_GET['id'];

// dados do utilizador
$stmt = $conn->prepare("SELECT * FROM utilizador WHERE id_utilizador = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    $_SESSION['error'] = 'Utilizador não encontrado.';
    header('Location: utilizadores.php');
    exit;
}


// Calcular idade se tiver data de nascimento
$idade = null;
if ($user['data_nascimento']) {
    $nascimento = new DateTime($user['data_nascimento']);
    $hoje = new DateTime();
    $idade = $hoje->diff($nascimento)->y;
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Utilizador - Admin CRC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #420e76;
            --accent: #FF00C8;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary) 0%, #1a0a2e 100%);
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1050;
            overflow-y: auto;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 2rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            border: none;
        }
        
        .btn-primary:hover {
            opacity: 0.9;
        }
        
        .user-profile-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            border-radius: 15px;
            padding: 2rem;
            color: white;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(66, 14, 118, 0.3);
        }
        
        .user-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            border: 4px solid white;
            margin: 0 auto 1rem;
        }
        
        .info-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            transition: transform 0.3s;
        }
        
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(66, 14, 118, 0.2);
        }
        
        .info-card h5 {
            color: var(--primary);
            font-weight: bold;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--accent);
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
        }
        
        .info-value {
            color: #333;
            text-align: right;
        }
        
        .status-badge-large {
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: bold;
            display: inline-block;
        }
        
        .stat-box {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            border-radius: 10px;
            padding: 1.5rem;
            color: white;
            text-align: center;
        }
        
        .stat-box h3 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-box p {
            margin: 0;
            opacity: 0.9;
        }
        
        .action-buttons .btn {
            margin: 0.25rem;
        }
        
        .timeline-item {
            padding: 1rem;
            border-left: 3px solid var(--accent);
            margin-left: 1rem;
            margin-bottom: 1rem;
        }
        
        .timeline-item h6 {
            color: var(--primary);
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includesadm/adminsidebar.php'; ?>

            <div class="main-content">
                <div class="mb-3">
                    <a href="utilizadores.php" class="btn btn-outline-secondary">
                        ← Voltar à Lista
                    </a>
                </div>
    
                <div class="user-profile-header">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            <div class="user-avatar-large">
                                <?php echo strtoupper(substr($user['primeiro_nome'], 0, 1)); ?>
                            </div>
                            <span class="status-badge-large <?php echo $user['ativo'] ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo $user['ativo'] ? 'Ativo' : 'Inativo'; ?>
                            </span>
                        </div>
                        <div class="col-md-9">
                            <h2 class="mb-2"><?php echo htmlspecialchars($user['primeiro_nome'] . ' ' . $user['ultimo_nome']); ?></h2>
                            <p class="mb-1">
                                <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
                                <?php if ($user['email_verificado']): ?>
                                    <span class="badge bg-success ms-2">Verificado</span>
                                <?php else: ?>
                                    <span class="badge bg-warning ms-2">Não Verificado</span>
                                <?php endif; ?>
                            </p>
                            <p class="mb-1">
                                <strong>Tipo:</strong> 
                                <span class="badge <?php echo $user['tipo'] == 'admin' ? 'bg-danger' : 'bg-info'; ?>">
                                    <?php echo $user['tipo'] == 'admin' ? 'Administrador' : 'Utilizador'; ?>
                                </span>
                            </p>
                            <p class="mb-0">
                                <strong>ID:</strong> #<?php echo $user['id_utilizador']; ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-card">
                            <h5>Informações Pessoais</h5>
                            <div class="info-item">
                                <span class="info-label">Nome Completo:</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['primeiro_nome'] . ' ' . $user['ultimo_nome']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Email:</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Telefone:</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['telefone'] ?? 'Não fornecido'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Data de Nascimento:</span>
                                <span class="info-value">
                                    <?php 
                                    if ($user['data_nascimento']) {
                                        echo date('d/m/Y', strtotime($user['data_nascimento']));
                                        if ($idade) echo " ($idade anos)";
                                    } else {
                                        echo 'Não fornecido';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Género:</span>
                                <span class="info-value">
                                    <?php 
                                    $generos = [
                                        'M' => 'Masculino',
                                        'F' => 'Feminino',
                                        'Outro' => 'Outro',
                                        'Prefiro não dizer' => 'Prefiro não dizer'
                                    ];
                                    echo htmlspecialchars($generos[$user['genero']] ?? $user['genero'] ?? 'Não especificado');
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
    
                    <div class="col-md-6">
                        <div class="info-card">
                            <h5>Informações da Conta</h5>
                            <div class="info-item">
                                <span class="info-label">Tipo de Conta:</span>
                                <span class="info-value">
                                    <span class="badge <?php echo $user['tipo'] == 'admin' ? 'bg-danger' : 'bg-info'; ?>">
                                        <?php echo ucfirst($user['tipo']); ?>
                                    </span>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Estado:</span>
                                <span class="info-value">
                                    <span class="badge <?php echo $user['ativo'] ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $user['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                    </span>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Email Verificado:</span>
                                <span class="info-value">
                                    <span class="badge <?php echo $user['email_verificado'] ? 'bg-success' : 'bg-warning'; ?>">
                                        <?php echo $user['email_verificado'] ? 'Sim' : 'Não'; ?>
                                    </span>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Data de Registo:</span>
                                <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($user['data_registo'])); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Última Atualização:</span>
                                <span class="info-value">
                                    <?php 
                                    if ($user['data_ultima_atualizacao']) {
                                        echo date('d/m/Y H:i', strtotime($user['data_ultima_atualizacao']));
                                    } else {
                                        echo 'Nunca';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-card">
                            <h5>Segurança</h5>
                            <div class="info-item">
                                <span class="info-label">Tentativas de Login:</span>
                                <span class="info-value">
                                    <span class="badge <?php echo $user['tentativas_login'] > 3 ? 'bg-danger' : 'bg-success'; ?>">
                                        <?php echo $user['tentativas_login'] ?? 0; ?>
                                    </span>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Conta Bloqueada:</span>
                                <span class="info-value">
                                    <?php 
                                    if ($user['bloqueado_ate']) {
                                        $bloqueado_ate = new DateTime($user['bloqueado_ate']);
                                        $agora = new DateTime();
                                        if ($bloqueado_ate > $agora) {
                                            echo '<span class="badge bg-danger">Sim até ' . $bloqueado_ate->format('d/m/Y H:i') . '</span>';
                                        } else {
                                            echo '<span class="badge bg-success">Não</span>';
                                        }
                                    } else {
                                        echo '<span class="badge bg-success">Não</span>';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Token de Recuperação:</span>
                                <span class="info-value">
                                    <?php echo $user['token_recuperacao_password'] ? '<span class="badge bg-warning">Ativo</span>' : '<span class="badge bg-secondary">Nenhum</span>'; ?>
                                </span>
                            </div>
                            <?php if ($user['data_expiracao_token']): ?>
                            <div class="info-item">
                                <span class="info-label">Expiração do Token:</span>
                                <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($user['data_expiracao_token'])); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-card">
                            <h5>Estatísticas</h5>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="stat-box">
                                        <h3>0</h3>
                                        <p>Reservas</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-box">
                                        <h3>0</h3>
                                        <p>Comentários</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-box">
                                        <h3>0</h3>
                                        <p>Avaliações</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-box">
                                        <h3>
                                            <?php 
                                            $dias_desde_registo = (new DateTime())->diff(new DateTime($user['data_registo']))->days;
                                            echo $dias_desde_registo;
                                            ?>
                                        </h3>
                                        <p>Dias Registado</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
             
                <div class="row">
                    <div class="col-12">
                        <div class="info-card">
                            <h5>Histórico de Atividade</h5>
                            <div class="timeline-item">
                                <h6>Registo da Conta</h6>
                                <p class="text-muted mb-0"><?php echo date('d/m/Y H:i', strtotime($user['data_registo'])); ?></p>
                            </div>
                            <?php if ($user['data_ultima_atualizacao']): ?>
                            <div class="timeline-item">
                                <h6>Última Atualização</h6>
                                <p class="text-muted mb-0"><?php echo date('d/m/Y H:i', strtotime($user['data_ultima_atualizacao'])); ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if ($user['email_verificado']): ?>
                            <div class="timeline-item">
                                <h6>Email Verificado</h6>
                                <p class="text-muted mb-0">Conta verificada com sucesso</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleBlock(id, isBlocked) {
            const action = isBlocked ? 'desbloquear' : 'bloquear por 24 horas';
            if (confirm(`Tem certeza que deseja ${action} este utilizador?`)) {
                window.location.href = 'processar_utilizador.php?action=toggle_block&id=' + id;
            }
        }
        
        function toggleAdmin(id, currentType) {
            const newType = currentType === 'admin' ? 'utilizador' : 'admin';
            const action = newType === 'admin' ? 'promover a administrador' : 'remover privilégios de administrador';
            
            if (confirm(`Tem certeza que deseja ${action} este utilizador?`)) {
                window.location.href = 'processar_utilizador.php?action=toggle_admin&id=' + id;
            }
        }
        
        function deleteUser(id) {
            if (confirm('ATENÇÃO: Esta ação é irreversível! Tem certeza que deseja eliminar este utilizador?')) {
                if (confirm('Confirma NOVAMENTE a eliminação?')) {
                    window.location.href = 'processar_utilizador.php?action=delete&id=' + id;
                }
            }
        }
    </script>
</body>
</html>