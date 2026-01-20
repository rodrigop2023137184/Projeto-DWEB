<?php
session_start();
require_once 'includesadm/middleware_admin.php';
require_once '../includes/db_connect.php';

//ID do produto
$id_produto = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_produto <= 0) {
    $_SESSION['error'] = 'ID de produto inválido.';
    header('Location: produtos.php');
    exit;
}

// dados do produto
$stmt = $conn->prepare("SELECT * FROM produto WHERE id_produto = ?");
$stmt->bind_param("i", $id_produto);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Produto não encontrado.';
    header('Location: produtos.php');
    exit;
}

$produto = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto - Admin CRC</title>
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
        
        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }
        
        .current-image {
            max-width: 300px;
            border-radius: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includesadm/adminsidebar.php'; ?>
            
            <!-- Main Content -->
            <div class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Editar Produto</h1>
                    <a href="produtos.php" class="btn btn-secondary">← Voltar</a>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <form action="processar_produto.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id_produto" value="<?php echo $produto['id_produto']; ?>">
                            <input type="hidden" name="imagem_atual" value="<?php echo $produto['imagem']; ?>">
                            
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Nome do Produto *</label>
                                    <input type="text" class="form-control" name="nome" value="<?php echo htmlspecialchars($produto['nome']); ?>" required>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Categoria *</label>
                                    <select class="form-select" name="categoria" required>
                                        <option value="roupa" <?php echo $produto['categoria'] == 'roupa' ? 'selected' : ''; ?>>Roupa</option>
                                        <option value="acessorios" <?php echo $produto['categoria'] == 'acessorios' ? 'selected' : ''; ?>>Acessórios</option>
                                        <option value="equipamento" <?php echo $produto['categoria'] == 'equipamento' ? 'selected' : ''; ?>>Equipamento</option>
                                        <option value="outros" <?php echo $produto['categoria'] == 'outros' ? 'selected' : ''; ?>>Outros</option>
                                    </select>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label">Descrição</label>
                                    <textarea class="form-control" name="descricao" rows="4"><?php echo htmlspecialchars($produto['descricao']); ?></textarea>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Peso</label>
                                    <input type="text" class="form-control" name="peso" value="<?php echo htmlspecialchars($produto['peso']); ?>" placeholder="g ou ml">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Material</label>
                                    <input type="text" class="form-control" name="material" value="<?php echo htmlspecialchars($produto['material']); ?>" placeholder="Material">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Slug</label>
                                    <input type="text" class="form-control" name="slug" value="<?php echo htmlspecialchars($produto['slug']); ?>" placeholder="Slug">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Preço (€) *</label>
                                    <input type="number" step="0.01" class="form-control" name="preco" value="<?php echo $produto['preco']; ?>" required>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Stock Total *</label>
                                    <input type="number" class="form-control" name="stock_total" value="<?php echo $produto['stock_total']; ?>" required>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Status *</label>
                                    <select class="form-select" name="status" required>
                                        <option value="ativo" <?php echo $produto['status'] == 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                                        <option value="inativo" <?php echo $produto['status'] == 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                                        <option value="esgotado" <?php echo $produto['status'] == 'esgotado' ? 'selected' : ''; ?>>Esgotado</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Tem Tamanhos?</label>
                                    <select class="form-select" name="tem_tamanhos" id="temTamanhos">
                                        <option value="0" <?php echo !$produto['tem_tamanhos'] ? 'selected' : ''; ?>>Não</option>
                                        <option value="1" <?php echo $produto['tem_tamanhos'] ? 'selected' : ''; ?>>Sim</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6" id="tipoTamanhoDiv" style="display: <?php echo $produto['tem_tamanhos'] ? 'block' : 'none'; ?>;">
                                    <label class="form-label">Tipo de Tamanho</label>
                                    <select class="form-select" name="tipo_tamanho">
                                        <option value="">Nenhum</option>
                                        <option value="roupa" <?php echo $produto['tipo_tamanho'] == 'roupa' ? 'selected' : ''; ?>>Roupa (XS, S, M, L, XL, XXL)</option>
                                        <option value="calcado" <?php echo $produto['tipo_tamanho'] == 'calcado' ? 'selected' : ''; ?>>Calçado (35-46)</option>
                                        <option value="numerico" <?php echo $produto['tipo_tamanho'] == 'numerico' ? 'selected' : ''; ?>>Numérico (ml, L, etc)</option>
                                    </select>
                                </div>
                                
                                <div class="col-12 mt-4">
                                    <label class="form-label">Imagem Atual:</label><br>
                                    <img src="../<?php echo htmlspecialchars($produto['imagem']); ?>" alt="Imagem atual" class="current-image">
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label">Nova Imagem (deixe vazio para manter a atual)</label>
                                    <input type="file" class="form-control" name="imagem" accept="image/*">
                                    <small class="text-muted">Formatos aceites: JPG, PNG, WEBP (Max: 5MB)</small>
                                </div>
                                
                                <div class="col-12">
                                    <img id="imagePreview" src="" alt="Preview" style="max-width: 200px; display: none;" class="img-thumbnail">
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-floppy" viewBox="0 0 16 16">
                                   <path d="M11 2H9v3h2z"/>
                                   <path d="M1.5 0h11.586a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0M1 1.5v13a.5.5 0 0 0 .5.5H2v-4.5A1.5 1.5 0 0 1 3.5 9h9a1.5 1.5 0 0 1 1.5 1.5V15h.5a.5.5 0 0 0 .5-.5V2.914a.5.5 0 0 0-.146-.353l-1.415-1.415A.5.5 0 0 0 13.086 1H13v4.5A1.5 1.5 0 0 1 11.5 7h-7A1.5 1.5 0 0 1 3 5.5V1H1.5a.5.5 0 0 0-.5.5m3 4a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5V1H4zM3 15h10v-4.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5z"/>
                                   </svg> Guardar Alterações</button>
                                <a href="produtos.php" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
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
        
        // Preview da nova imagem
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
    </script>
</body>
</html>