<?php
session_start();
require_once '../includes/middleware_admin.php';
require_once '../includes/db_connect.php';

// ID do evento
$id_evento = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_evento <= 0) {
    $_SESSION['error'] = 'ID de evento inválido.';
    header('Location: eventos.php');
    exit;
}

// dados do evento
$stmt = $conn->prepare("SELECT * FROM evento WHERE id_evento = ?");
$stmt->bind_param("i", $id_evento);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Evento não encontrado.';
    header('Location: eventos.php');
    exit;
}

$evento = $result->fetch_assoc();

// Decodificar JSON
$distancias = !empty($evento['distancias_disponiveis']) ? json_decode($evento['distancias_disponiveis'], true) : [];
$itens = !empty($evento['itens_incluidos']) ? json_decode($evento['itens_incluidos'], true) : [];
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Evento - Admin CRC</title>
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
        
        .item-tag {
            display: inline-block;
            background-color: #e9ecef;
            padding: 5px 10px;
            margin: 3px;
            border-radius: 5px;
            font-size: 0.85rem;
        }
        
        .item-tag .remove-tag {
            cursor: pointer;
            margin-left: 5px;
            color: #dc3545;
            font-weight: bold;
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
            <?php include '../includes/adminsidebar.php'; ?>
            
            <!-- Main Content -->
            <div class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Editar Evento</h1>
                    <a href="eventos.php" class="btn btn-secondary">← Voltar</a>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <form action="processar_evento.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id_evento" value="<?php echo $evento['id_evento']; ?>">
                            <input type="hidden" name="imagem_atual" value="<?php echo $evento['imagem']; ?>">
                            
                            <div class="row g-3">
                                <!-- Informações Básicas -->
                                <div class="col-12">
                                    <h5>Informações Básicas</h5>
                                    <hr>
                                </div>
                                
                                <div class="col-md-8">
                                    <label class="form-label">Título do Evento *</label>
                                    <input type="text" class="form-control" name="titulo" value="<?php echo htmlspecialchars($evento['titulo']); ?>" required>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Subtítulo</label>
                                    <input type="text" class="form-control" name="subtitulo" value="<?php echo htmlspecialchars($evento['subtitulo']); ?>">
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label">Descrição Curta *</label>
                                    <textarea class="form-control" name="descricao_curta" rows="2" required><?php echo htmlspecialchars($evento['descricao_curta']); ?></textarea>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label">Descrição Completa</label>
                                    <textarea class="form-control" name="descricao_completa" rows="4"><?php echo htmlspecialchars($evento['descricao_completa']); ?></textarea>
                                </div>
                                
                                <!-- Data e Local -->
                                <div class="col-12 mt-4">
                                    <h5>Data e Localização</h5>
                                    <hr>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Data do Evento *</label>
                                    <input type="date" class="form-control" name="data_evento" value="<?php echo $evento['data_evento']; ?>" required>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Hora de Início *</label>
                                    <input type="time" class="form-control" name="hora_evento" value="<?php echo $evento['hora_evento']; ?>" required>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Duração Estimada</label>
                                    <input type="text" class="form-control" name="duracao_estimada" value="<?php echo htmlspecialchars($evento['duracao_estimada']); ?>">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Nome do Local *</label>
                                    <input type="text" class="form-control" name="local_nome" value="<?php echo htmlspecialchars($evento['local_nome']); ?>" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Endereço Completo</label>
                                    <input type="text" class="form-control" name="local_endereco" value="<?php echo htmlspecialchars($evento['local_endereco']); ?>">
                                </div>
                                
                                <!-- Categoria e Preço -->
                                <div class="col-12 mt-4">
                                    <h5>Categoria e Inscrições</h5>
                                    <hr>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Categoria *</label>
                                    <select class="form-select" name="categoria" required>
                                        <option value="corrida" <?php echo $evento['categoria'] == 'corrida' ? 'selected' : ''; ?>>Corrida</option>
                                        <option value="trail" <?php echo $evento['categoria'] == 'trail' ? 'selected' : ''; ?>>Trail</option>
                                        <option value="maratona" <?php echo $evento['categoria'] == 'maratona' ? 'selected' : ''; ?>>Maratona</option>
                                        <option value="caminhada" <?php echo $evento['categoria'] == 'caminhada' ? 'selected' : ''; ?>>Caminhada</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Preço (€) *</label>
                                    <input type="number" step="0.01" class="form-control" name="preco" value="<?php echo $evento['preco']; ?>" required>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Vagas Totais</label>
                                    <input type="number" class="form-control" name="vagas_totais" value="<?php echo $evento['vagas_totais']; ?>">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Status *</label>
                                    <select class="form-select" name="status" required>
                                        <option value="ativo" <?php echo $evento['status'] == 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                                        <option value="cancelado" <?php echo $evento['status'] == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                        <option value="concluido" <?php echo $evento['status'] == 'concluido' ? 'selected' : ''; ?>>Concluído</option>
                                    </select>
                                </div>
                                
                                <!-- Distâncias -->
                                <div class="col-12">
                                    <label class="form-label">Distâncias Disponíveis</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="distanciaInput" placeholder="Ex: 5km">
                                        <button type="button" class="btn btn-outline-secondary" onclick="addDistancia()">Adicionar</button>
                                    </div>
                                    <div id="distanciasContainer" class="mt-2"></div>
                                    <input type="hidden" name="distancias_disponiveis" id="distanciasHidden">
                                </div>
                                
                                <!-- Detalhes Percurso -->
                                <div class="col-12">
                                    <label class="form-label">Detalhes do Percurso (JSON)</label>
                                    <textarea class="form-control" name="detalhes_percurso" rows="3"><?php echo htmlspecialchars($evento['detalhes_percurso']); ?></textarea>
                                </div>
                                
                                <!-- Itens Incluídos -->
                                <div class="col-12 mt-4">
                                    <h5>O que está incluído</h5>
                                    <hr>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label">Itens Incluídos</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="itemInput" placeholder="Ex: T-shirt">
                                        <button type="button" class="btn btn-outline-secondary" onclick="addItem()">Adicionar</button>
                                    </div>
                                    <div id="itensContainer" class="mt-2"></div>
                                    <input type="hidden" name="itens_incluidos" id="itensHidden">
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="tem_transporte" id="temTransporte" value="1" <?php echo $evento['tem_transporte'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="temTransporte">
                                            Tem Transporte Incluído
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Imagem -->
                                <div class="col-12 mt-4">
                                    <h5>Imagem do Evento</h5>
                                    <hr>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label">Imagem Atual:</label><br>
                                    <img src="../<?php echo htmlspecialchars($evento['imagem']); ?>" alt="Imagem atual" class="current-image">
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label">Nova Imagem (deixe vazio para manter a atual)</label>
                                    <input type="file" class="form-control" name="imagem" accept="image/*">
                                    <small class="text-muted">Formatos: JPG, PNG, WEBP (Max: 5MB)</small>
                                </div>
                                
                                <div class="col-12">
                                    <img id="imagePreview" src="" alt="Preview" style="max-width: 300px; display: none;" class="img-thumbnail">
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-floppy" viewBox="0 0 16 16">
                                   <path d="M11 2H9v3h2z"/>
                                   <path d="M1.5 0h11.586a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0M1 1.5v13a.5.5 0 0 0 .5.5H2v-4.5A1.5 1.5 0 0 1 3.5 9h9a1.5 1.5 0 0 1 1.5 1.5V15h.5a.5.5 0 0 0 .5-.5V2.914a.5.5 0 0 0-.146-.353l-1.415-1.415A.5.5 0 0 0 13.086 1H13v4.5A1.5 1.5 0 0 1 11.5 7h-7A1.5 1.5 0 0 1 3 5.5V1H1.5a.5.5 0 0 0-.5.5m3 4a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5V1H4zM3 15h10v-4.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5z"/>
                                   </svg>
                                   Guardar Alterações
                                </button>
                                <a href="eventos.php" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // distâncias existentes
        let distancias = <?php echo json_encode($distancias); ?>;
        let itens = <?php echo json_encode($itens); ?>;
        
        // Inicializar ao carregar
        document.addEventListener('DOMContentLoaded', function() {
            updateDistanciasDisplay();
            updateItensDisplay();
        });
        
        function addDistancia() {
            const input = document.getElementById('distanciaInput');
            const valor = input.value.trim();
            if (valor && !distancias.includes(valor)) {
                distancias.push(valor);
                updateDistanciasDisplay();
                input.value = '';
            }
        }
        
        function updateDistanciasDisplay() {
            const container = document.getElementById('distanciasContainer');
            container.innerHTML = distancias.map((d, i) => 
                `<span class="item-tag">${d}<span class="remove-tag" onclick="removeDistancia(${i})">×</span></span>`
            ).join('');
            document.getElementById('distanciasHidden').value = JSON.stringify(distancias);
        }
        
        function removeDistancia(index) {
            distancias.splice(index, 1);
            updateDistanciasDisplay();
        }
        
        function addItem() {
            const input = document.getElementById('itemInput');
            const valor = input.value.trim();
            if (valor && !itens.includes(valor)) {
                itens.push(valor);
                updateItensDisplay();
                input.value = '';
            }
        }
        
        function updateItensDisplay() {
            const container = document.getElementById('itensContainer');
            container.innerHTML = itens.map((item, i) => 
                `<span class="item-tag">${item}<span class="remove-tag" onclick="removeItem(${i})">×</span></span>`
            ).join('');
            document.getElementById('itensHidden').value = JSON.stringify(itens);
        }
        
        function removeItem(index) {
            itens.splice(index, 1);
            updateItensDisplay();
        }
        
        // Preview nova imagem
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
        
        // Enter para adicionar
        document.getElementById('distanciaInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); addDistancia(); }
        });
        
        document.getElementById('itemInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); addItem(); }
        });
    </script>
</body>
</html>