<?php
session_start();
require_once 'includesadm/middleware_admin.php';
require_once '../includes/db_connect.php';

// Estatísticas
$total_users = $conn->query("SELECT COUNT(*) as total FROM utilizador")->fetch_assoc()['total'];
$total_eventos = $conn->query("SELECT COUNT(*) as total FROM evento")->fetch_assoc()['total'];
$total_produtos = $conn->query("SELECT COUNT(*) as total FROM produto")->fetch_assoc()['total'];
$total_encomendas = $conn->query("SELECT COUNT(*) as total FROM encomenda")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - CRC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #420e76;
            --accent: #FF00C8;
        }
        .btn-bg{
            background: var(--primary);
            color: white;
        }
        .btn-bg:hover{
            background: #2a0a4aff;
            color: white;
        }

        .bg-acoesrapidas {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: white;
        }
        .stat-card {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            border-radius: 15px;
            padding: 2rem;
            color: white;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row"> 
            <?php include 'includesadm/adminsidebar.php'; ?>                     
            <!-- Content -->
            <div class="col-md-10 p-4">
                <h1 class="mb-4">Dashboard</h1>
                
                <!-- Stats -->
                <div class="row g-4 mb-5">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h3><?php echo $total_users; ?></h3>
                            <p class="mb-0">Utilizadores</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h3><?php echo $total_eventos; ?></h3>
                            <p class="mb-0">Eventos</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h3><?php echo $total_produtos; ?></h3>
                            <p class="mb-0">Produtos</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h3><?php echo $total_encomendas; ?></h3>
                            <p class="mb-0">Encomendas</p>
                        </div>
                    </div>
                </div>
                
                <!-- acoes rapidas -->
                <div class="card">
                    <div class="card-header bg-acoesrapidas text-white">
                        <h5 class="mb-0">Ações Rápidas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <a href="eventos.php?action=create" class="btn btn-bg w-100">
                                     Criar Evento
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="produtos.php" class="btn btn-bg w-100">
                                    Adicionar Produto
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="utilizadores.php" class="btn btn-bg w-100">
                                    Ver Utilizadores
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>