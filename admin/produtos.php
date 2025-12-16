<?php
session_start();
require_once '../includes/middleware_admin.php';
require_once '../includes/db_connect.php';

//todos os produtos
$stmt = $conn->prepare("SELECT * FROM produto ORDER BY data_criacao DESC");
$stmt->execute();
$produtos = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Produtos - Admin CRC</title>
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
        
        .product-img-preview {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
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
        
        .table-compact th,
        .table-compact td {
            padding: 0.5rem 0.5rem;
            white-space: nowrap;
        }
        
        .table-compact th:nth-child(2),
        .table-compact td:nth-child(2) {
            width: auto;
            max-width: 200px;
            white-space: normal;
            padding-right: 0.75rem;
        }
        
        .table-compact th:nth-child(3),
        .table-compact td:nth-child(3) {
            width: 100px;
            padding-left: 0.5rem;
        }
        
        .table-compact th:nth-child(4),
        .table-compact td:nth-child(4) {
            width: 100px;
        }
        
        .table-compact th:nth-child(5),
        .table-compact td:nth-child(5) {
            width: 90px;
            text-align: center;
        }
        
        .table-compact th:nth-child(6),
        .table-compact td:nth-child(6) {
            width: 90px;
            text-align: center;
        }
        
        .table-compact th:nth-child(7),
        .table-compact td:nth-child(7) {
            width: 120px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/adminsidebar.php'; ?> 
            
            <div class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Gestão de Produtos</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                         Adicionar Produto
                    </button>
                </div>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php 
                        echo $_SESSION['success']; 
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-compact">
                                <thead>
                                    <tr>
                                        <th style="width: 100px;">Imagem</th>
                                        <th>Nome</th>
                                        <th>Categoria</th>
                                        <th>Preço</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($produto = $produtos->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <img src="../<?php echo htmlspecialchars($produto['imagem']); ?>" 
                                                 alt="<?php echo htmlspecialchars($produto['nome']); ?>" 
                                                 class="product-img-preview">
                                        </td>
                                        <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($produto['categoria']); ?></td>
                                        <td>€<?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge <?php echo $produto['stock_total'] > 10 ? 'bg-success' : ($produto['stock_total'] > 0 ? 'bg-warning' : 'bg-danger'); ?>">
                                                <?php echo $produto['stock_total']; ?> un.
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $produto['status'] == 'ativo' ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo ucfirst($produto['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="editProduct(<?php echo $produto['id_produto']; ?>)" >Editar</button> 
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(<?php echo $produto['id_produto']; ?>)">Apagar</button>   
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Novo Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="processar_produto.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="modal-body">
                        <div class="row g-3">            
                            <div class="col-md-8">
                                <label class="form-label">Nome do Produto *</label>
                                <input type="text" class="form-control" name="nome" required>
                            </div>
 
                            <div class="col-md-4">
                                <label class="form-label">Categoria *</label>
                                <select class="form-select" name="categoria" required>
                                    <option value="roupa">Roupa</option>
                                    <option value="acessorios">Acessórios</option>
                                    <option value="equipamento">Equipamento</option>
                                    <option value="outros">Outros</option>
                                </select>
                            </div>
                            

                            <div class="col-12">
                                <label class="form-label">Descrição</label>
                                <textarea class="form-control" name="descricao" rows="4"></textarea>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Preço (€) *</label>
                                <input type="number" step="0.01" class="form-control" name="preco" required>
                            </div>
            
                            <div class="col-md-4">
                                <label class="form-label">Stock Total *</label>
                                <input type="number" class="form-control" name="stock_total" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Status *</label>
                                <select class="form-select" name="status" required>
                                    <option value="ativo">Ativo</option>
                                    <option value="inativo">Inativo</option>
                                    <option value="esgotado">Esgotado</option>
                                </select>
                            </div>
              
                            <div class="col-md-6">
                                <label class="form-label">Tem Tamanhos?</label>
                                <select class="form-select" name="tem_tamanhos" id="temTamanhos">
                                    <option value="0">Não</option>
                                    <option value="1">Sim</option>
                                </select>
                            </div>
                
                            <div class="col-md-6" id="tipoTamanhoDiv" style="display: none;">
                                <label class="form-label">Tipo de Tamanho</label>
                                <select class="form-select" name="tipo_tamanho">
                                    <option value="">Nenhum</option>
                                    <option value="roupa">Roupa (XS, S, M, L, XL, XXL)</option>
                                    <option value="calcado">Calçado (35-46)</option>
                                    <option value="numerico">Numérico (ml, L, etc)</option>
                                </select>
                            </div>
       
                            <div class="col-12">
                                <label class="form-label">Imagem do Produto *</label>
                                <input type="file" class="form-control" name="imagem" accept="image/*" required>
                                <small class="text-muted">Formatos aceites: JPG, PNG, WEBP (Max: 5MB)</small>
                            </div>
                
                            <div class="col-12">
                                <img id="imagePreview" src="" alt="Preview" style="max-width: 200px; display: none;" class="img-thumbnail">
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Adicionar Produto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar/esconder tipo de tamanho
        document.getElementById('temTamanhos').addEventListener('change', function() {
            const tipoDiv = document.getElementById('tipoTamanhoDiv');
            tipoDiv.style.display = this.value === '1' ? 'block' : 'none';
        });
        
        // Preview da imagem
        document.querySelector('input[name="imagem"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Editar produto
        function editProduct(id) {
            window.location.href = 'editar_produto.php?id=' + id;
        }
        
        // Eliminar produto
        function deleteProduct(id) {
            if (confirm('Tem a certeza que deseja eliminar este produto?')) {
                window.location.href = 'processar_produto.php?action=delete&id=' + id;
            }
        }
    </script>
</body>
</html>