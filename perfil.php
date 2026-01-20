<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'includes/db_connect.php';

$id_utilizador = $_SESSION['id_utilizador'];

$stmt = $conn->prepare("SELECT * FROM utilizador WHERE id_utilizador = ?");
$stmt->bind_param("i", $id_utilizador);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("
    SELECT e.*, i.data_inscricao, i.distancia_escolhida, i.numero_dorsal, i.estado_pagamento, i.status
    FROM inscricao_evento i 
    INNER JOIN evento e ON i.id_evento = e.id_evento 
    WHERE i.id_utilizador = ? 
    ORDER BY e.data_evento DESC
");
$stmt->bind_param("i", $id_utilizador);
$stmt->execute();
$inscricoes = $stmt->get_result();

$stmt = $conn->prepare("
    SELECT * FROM encomenda 
    WHERE id_utilizador = ? 
    ORDER BY data_encomenda DESC
    LIMIT 10
");
$stmt->bind_param("i", $id_utilizador);
$stmt->execute();
$encomendas = $stmt->get_result();

$stmt = $conn->prepare("
    SELECT 
        c.*,
        p.nome,
        p.imagem,
        (c.quantidade * c.preco_unitario) as subtotal
    FROM carrinho c
    INNER JOIN produto p ON c.id_produto = p.id_produto
    WHERE c.id_utilizador = ?
");
$stmt->bind_param("i", $id_utilizador);
$stmt->execute();
$carrinho = $stmt->get_result();

$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM encomenda 
    WHERE id_utilizador = ?
");
$stmt->bind_param("i", $id_utilizador);
$stmt->execute();
$total_compras = $stmt->get_result()->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - CRC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bg-secondary {
            background-color: #0F172A !important;
        }
        .text-primary{
            color: #FF00C8 !important; 
        }
        .profile-card {
            background-color: #1E293B;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: linear-gradient(135deg, #420e76 0%, #FF00C8 100%);
            border-radius: 10px;
            padding: 1.5rem;
            color: white;
            text-align: center;
        }
        .order-item {
            background-color: #2d3748;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #FF00C8;
        }
        .badge-status {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85rem;
        }
        .cart-item-mini {
            display: flex;
            gap: 15px;
            padding: 15px;
            background-color: #2d3748;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .cart-item-mini img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }
        .btn-primary {
            background-color: #FF00C8;
            color: white;
            border: none;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: #d400a8;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 0, 200, 0.3);
        }
    </style>
</head>
<body class="bg-secondary">

<?php include 'includes/nav.php'; ?>

<div class="container py-5">
    <div class="profile-card text-light">
        <div class="row align-items-center">
            <div class="col-md-2 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                    <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
                </svg>
            </div>
            <div class="col-md-8">
                <h2><?php echo htmlspecialchars($user['primeiro_nome'] . ' ' . $user['ultimo_nome']); ?></h2>
                <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p class="mb-1"><strong>Telemóvel:</strong> <?php echo htmlspecialchars($user['telefone'] ?? 'Não definido'); ?></p>
                <p class="mb-0"><strong>Membro desde:</strong> <?php echo date('d/m/Y', strtotime($user['data_registo'])); ?></p>
            </div>
            <div class="col-md-2 text-center">
                <a href="logout.php" class="btn btn-outline-danger">Sair</a>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <h3><?php echo $inscricoes->num_rows; ?></h3>
                <p class="mb-0">Eventos Inscritos</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <h3><?php echo $total_compras; ?></h3>
                <p class="mb-0">Encomendas</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <h3><?php echo $carrinho->num_rows; ?></h3>
                <p class="mb-0">Itens no Carrinho</p>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active text-primary" id="eventos-tab" data-bs-toggle="tab" data-bs-target="#eventos" type="button">
                Meus Eventos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-primary" id="compras-tab" data-bs-toggle="tab" data-bs-target="#compras" type="button">
                Minhas Encomendas
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-primary" id="carrinho-tab" data-bs-toggle="tab" data-bs-target="#carrinho" type="button">
                Meu Carrinho
            </button>
        </li>
    </ul>

    <div class="tab-content" id="profileTabContent">
        <div class="tab-pane fade show active" id="eventos" role="tabpanel">
            <div class="profile-card text-light">
                <h4 class="mb-4">Eventos Inscritos</h4>
                <?php if ($inscricoes->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Evento</th>
                                    <th>Data</th>
                                    <th>Distância</th>
                                    <th>Dorsal</th>
                                    <th>Estado</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $inscricoes->data_seek(0); // Reset pointer
                                while($evento = $inscricoes->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($evento['nome'] ?? $evento['titulo'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($evento['data_evento'])); ?></td>
                                    <td><?php echo htmlspecialchars($evento['distancia_escolhida']); ?></td>
                                    <td>
                                        <?php 
                                        if (!empty($evento['numero_dorsal'])) {
                                            echo '<span class="badge bg-info">#' . $evento['numero_dorsal'] . '</span>';
                                        } else {
                                            echo '<span class="badge bg-secondary">Pendente</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status_badges = [
                                            'ativa' => 'bg-success',
                                            'cancelada' => 'bg-danger',
                                            'compareceu' => 'bg-primary',
                                            'nao_compareceu' => 'bg-warning'
                                        ];
                                        $badge_class = $status_badges[$evento['status']] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $evento['status'])); ?></span>
                                    </td>
                                    <td>
                                        <a href="evento1.php?id=<?php echo $evento['id_evento']; ?>" class="btn btn-sm btn-outline-light">Ver</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>Ainda não está inscrito em nenhum evento.</p>
                    <a href="eventos.php" class="btn btn-primary">Ver Eventos Disponíveis</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="tab-pane fade" id="compras" role="tabpanel">
            <div class="profile-card text-light">
                <h4 class="mb-4">Minhas Encomendas</h4>
                <?php if ($encomendas->num_rows > 0): ?>
                    <?php while($encomenda = $encomendas->fetch_assoc()): ?>
                        <div class="order-item">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">Encomenda #<?php echo str_pad($encomenda['id_encomenda'], 6, '0', STR_PAD_LEFT); ?></h6>
                                    <small><?php echo date('d/m/Y H:i', strtotime($encomenda['data_encomenda'])); ?></small>
                                </div>
                                <div>
                                    <?php
                                    $badges = [
                                        'pendente' => 'bg-warning',
                                        'confirmada' => 'bg-info',
                                        'a_preparar' => 'bg-primary',
                                        'enviada' => 'bg-success',
                                        'entregue' => 'bg-success',
                                        'cancelada' => 'bg-danger'
                                    ];
                                    $badge_class = $badges[$encomenda['estado']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $encomenda['estado'])); ?></span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Total: €<?php echo number_format($encomenda['total'], 2, ',', '.'); ?></strong>
                                    <br>
                                    <small>Pagamento: <?php echo ucfirst($encomenda['metodo_pagamento']); ?></small>
                                </div>
                                <a href="confirmacao.php?id=<?php echo $encomenda['id_encomenda']; ?>" class="btn btn-sm btn-outline-light">Ver Detalhes</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Ainda não fez nenhuma encomenda.</p>
                    <a href="merch.php" class="btn btn-primary">Ver Merch</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="tab-pane fade" id="carrinho" role="tabpanel">
            <div class="profile-card text-light">
                <h4 class="mb-4">Meu Carrinho</h4>
                <?php if ($carrinho->num_rows > 0): ?>
                    <?php 
                    $total_carrinho = 0;
                    while($item = $carrinho->fetch_assoc()): 
                        $total_carrinho += $item['subtotal'];
                    ?>
                        <div class="cart-item-mini">
                            <img src="<?php echo htmlspecialchars($item['imagem']); ?>" alt="<?php echo htmlspecialchars($item['nome']); ?>">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?php echo htmlspecialchars($item['nome']); ?></h6>
                                <small>
                                    <?php if (!empty($item['tamanho_escolhido'])): ?>
                                        Tamanho: <?php echo htmlspecialchars($item['tamanho_escolhido']); ?> | 
                                    <?php endif; ?>
                                    Qtd: <?php echo $item['quantidade']; ?>
                                </small>
                            </div>
                            <div class="text-end">
                                <strong>€<?php echo number_format($item['subtotal'], 2, ',', '.'); ?></strong>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <hr class="my-3" style="border-color: #4a5568;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Total:</h5>
                        <h5 style="color: #FF00C8;">€<?php echo number_format($total_carrinho, 2, ',', '.'); ?></h5>
                    </div>
                    <a href="carrinho.php" class="btn btn-primary w-100">Ver Carrinho Completo</a>
                <?php else: ?>
                    <p>O seu carrinho está vazio.</p>
                    <a href="merch.php" class="btn btn-primary">Adicionar Produtos</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>