<?php
session_start();
require_once '../includes/middleware_admin.php';
require_once '../includes/db_connect.php';

// filtros
$where_conditions = [];
$params = [];
$types = '';

// Filtro de nome ou email
if (!empty($_GET['search'])) {
    $where_conditions[] = "(primeiro_nome LIKE ? OR ultimo_nome LIKE ? OR email LIKE ?)";
    $search_param = '%' . $_GET['search'] . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

// Filtro por tipo
if (!empty($_GET['tipo'])) {
    $where_conditions[] = "tipo = ?";
    $params[] = $_GET['tipo'];
    $types .= 's';
}

// Filtro por estado ativo
if (isset($_GET['ativo']) && $_GET['ativo'] !== '') {
    $where_conditions[] = "ativo = ?";
    $params[] = (int)$_GET['ativo'];
    $types .= 'i';
}

// Construir query
$sql = "SELECT * FROM utilizador";
if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}
$sql .= " ORDER BY data_registo DESC";

// Executar query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$utilizadores = $stmt->get_result();

// Contar estatísticas
$stmt = $conn->query("SELECT COUNT(*) as total FROM utilizador");
$total_users = $stmt->fetch_assoc()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM utilizador WHERE ativo = 1");
$users_ativos = $stmt->fetch_assoc()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM utilizador WHERE tipo = 'admin'");
$total_admins = $stmt->fetch_assoc()['total'];

// Verificar se utilizador está bloqueado
function isUserBlocked($bloqueado_ate) {
    if (!$bloqueado_ate) return false;
    $bloqueado = new DateTime($bloqueado_ate);
    $agora = new DateTime();
    return $bloqueado > $agora;
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Utilizadores - Admin CRC</title>
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
        
        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            border-radius: 15px;
            padding: 1.5rem;
            color: white;
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card h3 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .table-compact th,
        .table-compact td {
            padding: 0.75rem;
            vertical-align: middle;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .btn-clear-filters {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-clear-filters:hover {
            background-color: #5a6268;
            color: white;
        }
        
        .icon-btn svg {
            width: 18px;
            height: 18px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/adminsidebar.php'; ?>
  
            <div class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Gestão de Utilizadores</h1>
                </div>
     
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <h3><?php echo $total_users; ?></h3>
                            <p class="mb-0">Total de Utilizadores</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <h3><?php echo $users_ativos; ?></h3>
                            <p class="mb-0">Utilizadores Ativos</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <h3><?php echo $total_admins; ?></h3>
                            <p class="mb-0">Administradores</p>
                        </div>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
      
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Buscar por nome ou email..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="tipo">
                                    <option value="">Todos os tipos</option>
                                    <option value="utilizador" <?php echo ($_GET['tipo'] ?? '') == 'utilizador' ? 'selected' : ''; ?>>Utilizador</option>
                                    <option value="admin" <?php echo ($_GET['tipo'] ?? '') == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="ativo">
                                    <option value="">Todos os estados</option>
                                    <option value="1" <?php echo ($_GET['ativo'] ?? '') === '1' ? 'selected' : ''; ?>>Ativo</option>
                                    <option value="0" <?php echo ($_GET['ativo'] ?? '') === '0' ? 'selected' : ''; ?>>Inativo</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                            </div>
                            <div class="col-md-2">
                                <a href="?" class="btn btn-clear-filters w-100">Limpar</a>
                            </div>
                        </form>
                    </div>
                </div>
    
                <div class="card">
                    <div class="card-body">
                        <?php if ($utilizadores->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-compact">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Utilizador</th>
                                        <th>Email</th>
                                        <th>Telefone</th>
                                        <th>Tipo</th>
                                        <th>Data Registo</th>
                                        <th>Estado</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($user = $utilizadores->fetch_assoc()): 
                                        $bloqueado = isUserBlocked($user['bloqueado_ate']);
                                    ?>
                                    <tr>
                                        <td>#<?php echo $user['id_utilizador']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="user-avatar">
                                                    <?php echo strtoupper(substr($user['primeiro_nome'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($user['primeiro_nome'] . ' ' . $user['ultimo_nome']); ?></strong>
                                                    <?php if ($bloqueado): ?>
                                                        <br><small class="text-danger">Bloqueado</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['telefone'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge <?php echo $user['tipo'] == 'admin' ? 'bg-danger' : 'bg-info'; ?>">
                                                <?php echo ucfirst($user['tipo']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($user['data_registo'])); ?></td>
                                        <td>
                                            <span class="badge <?php echo $user['ativo'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo $user['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary icon-btn" onclick="viewUser(<?php echo $user['id_utilizador']; ?>)" title="Ver Detalhes">
                                                    Ver
                                                </button>
                                                <button class="btn btn-outline-warning icon-btn" onclick="toggleBlock(<?php echo $user['id_utilizador']; ?>, <?php echo $bloqueado ? 'true' : 'false'; ?>)" title="<?php echo $bloqueado ? 'Desbloquear' : 'Bloquear'; ?>">
                                                    <?php if ($bloqueado): ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                          <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 1 1 9 0v3.75M3.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H3.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                                        </svg>
                                                    <?php else: ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                          <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                                        </svg>
                                                    <?php endif; ?>
                                                </button>
                                                <?php if($user['id_utilizador'] != $_SESSION['id_utilizador']): ?>
                                                <button class="btn btn-outline-danger icon-btn" onclick="deleteUser(<?php echo $user['id_utilizador']; ?>)" title="Eliminar">
                                                    Apagar
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info text-center">
                            <h5>Nenhum utilizador encontrado</h5>
                            <p>Tente ajustar os filtros de pesquisa.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewUser(id) {
            window.location.href = 'ver_utilizador.php?id=' + id;
        }
        
        function toggleBlock(id, isBlocked) {
            const action = isBlocked ? 'desbloquear' : 'bloquear';
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