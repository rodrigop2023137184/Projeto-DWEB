<?php
session_start();
require_once 'includesadm/middleware_admin.php';
require_once '../includes/db_connect.php';

// todos os eventos
$stmt = $conn->prepare("SELECT * FROM evento ORDER BY data_evento DESC");
$stmt->execute();
$eventos = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Eventos - Admin CRC</title>
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
        
        .event-img-preview {
            width: 120px;
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includesadm/adminsidebar.php'; ?>
            
            <div class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Gestão de Eventos</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                     Criar Evento
                    </button>
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
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Imagem</th>
                                        <th>Título</th>
                                        <th>Data</th>
                                        <th>Local</th>
                                        <th>Categoria</th>
                                        <th>Vagas</th>
                                        <th>Preço</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($evento = $eventos->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php if(!empty($evento['imagem'])): ?>
                                                <img src="../<?php echo htmlspecialchars($evento['imagem']); ?>" 
                                                     alt="<?php echo htmlspecialchars($evento['titulo']); ?>" 
                                                     class="event-img-preview">
                                            <?php else: ?>
                                                <div class="event-img-preview bg-secondary d-flex align-items-center justify-content-center text-white">
                                                    Sem imagem
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($evento['titulo']); ?></strong>
                                            <?php if($evento['subtitulo']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($evento['subtitulo']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($evento['data_evento'])); ?><br>
                                            <small><?php echo substr($evento['hora_evento'], 0, 5); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($evento['local_nome']); ?></td>
                                        <td><span class="badge bg-info"><?php echo ucfirst($evento['categoria']); ?></span></td>
                                        <td>
                                            <?php 
                                            $vagas = $evento['vagas_totais'] ? ($evento['vagas_ocupadas'] . '/' . $evento['vagas_totais']) : 'Ilimitado';
                                            echo $vagas;
                                            ?>
                                        </td>
                                        <td>€<?php echo number_format($evento['preco'], 2, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge <?php 
                                                echo $evento['status'] == 'ativo' ? 'bg-success' : 
                                                    ($evento['status'] == 'cancelado' ? 'bg-danger' : 'bg-secondary'); 
                                            ?>">
                                                <?php echo ucfirst($evento['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="editEvent(<?php echo $evento['id_evento']; ?>)">Editar</button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteEvent(<?php echo $evento['id_evento']; ?>)">Apagar</button>
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

    <div class="modal fade" id="addEventModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Criar Novo Evento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="processar_evento.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="modal-body">
                        <div class="row g-3">

                            <div class="col-12">
                                <h6>Informações Básicas</h6>
                                <hr>
                            </div>
                            
                            <div class="col-md-8">
                                <label class="form-label">Título do Evento *</label>
                                <input type="text" class="form-control" name="titulo" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Subtítulo</label>
                                <input type="text" class="form-control" name="subtitulo" placeholder="Ex: 5 KM · 10 KM">
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Descrição Curta *</label>
                                <textarea class="form-control" name="descricao_curta" rows="2" required></textarea>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Descrição Completa</label>
                                <textarea class="form-control" name="descricao_completa" rows="4"></textarea>
                            </div>
                            
                            <!-- Data e Local -->
                            <div class="col-12 mt-4">
                                <h6>Data e Localização</h6>
                                <hr>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Data do Evento *</label>
                                <input type="date" class="form-control" name="data_evento" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Hora de Início *</label>
                                <input type="time" class="form-control" name="hora_evento" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Duração Estimada</label>
                                <input type="text" class="form-control" name="duracao_estimada" placeholder="Ex: 2-3 horas">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Nome do Local *</label>
                                <input type="text" class="form-control" name="local_nome" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Endereço Completo</label>
                                <input type="text" class="form-control" name="local_endereco">
                            </div>
                            
                            <!-- Categoria e Preço -->
                            <div class="col-12 mt-4">
                                <h6>Categoria e Inscrições</h6>
                                <hr>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Categoria *</label>
                                <select class="form-select" name="categoria" required>
                                    <option value="corrida">Corrida</option>
                                    <option value="trail">Trail</option>
                                    <option value="maratona">Maratona</option>
                                    <option value="caminhada">Caminhada</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Preço (€) *</label>
                                <input type="number" step="0.01" class="form-control" name="preco" required>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Vagas Totais</label>
                                <input type="number" class="form-control" name="vagas_totais" placeholder="Deixe vazio se ilimitado">
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Status *</label>
                                <select class="form-select" name="status" required>
                                    <option value="ativo">Ativo</option>
                                    <option value="cancelado">Cancelado</option>
                                    <option value="concluido">Concluído</option>
                                </select>
                            </div>
                            
                            <!-- Distâncias -->
                            <div class="col-12">
                                <label class="form-label">Distâncias Disponíveis</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="distanciaInput" placeholder="Ex: 5km, 10km, 21km">
                                    <button type="button" class="btn btn-outline-secondary" onclick="addDistancia()">Adicionar</button>
                                </div>
                                <small class="text-muted">Digite a distância e clique em "Adicionar"</small>
                                <div id="distanciasContainer" class="mt-2"></div>
                                <input type="hidden" name="distancias_disponiveis" id="distanciasHidden">
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Detalhes do Percurso (JSON)</label>
                                <textarea class="form-control" name="detalhes_percurso" rows="3" placeholder='{"7km": [{"nome": "Partida", "local": "Praça 8 de Maio"}, {"nome": "Ferreira Borges", "local": "Rua Ferreira Borges"}]}'></textarea>
                                <small class="text-muted">Formato JSON opcional com informações do percurso</small>
                            </div>
                            
                            <div class="col-12 mt-4">
                                <h6>O que está incluído</h6>
                                <hr>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Itens Incluídos na Inscrição</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="itemInput" placeholder="Ex: T-shirt oficial, Medalha, Seguro...">
                                    <button type="button" class="btn btn-outline-secondary" onclick="addItem()">Adicionar</button>
                                </div>
                                <div id="itensContainer" class="mt-2"></div>
                                <input type="hidden" name="itens_incluidos" id="itensHidden">
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="tem_transporte" id="temTransporte" value="1">
                                    <label class="form-check-label" for="temTransporte">
                                        Tem Transporte Incluído
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-12 mt-4">
                                <h6>Imagem do Evento</h6>
                                <hr>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Imagem Principal *</label>
                                <input type="file" class="form-control" name="imagem" accept="image/*" required>
                                <small class="text-muted">Formatos: JPG, PNG, WEBP (Max: 5MB)</small>
                            </div>
                            
                            <div class="col-12">
                                <img id="imagePreview" src="" alt="Preview" style="max-width: 300px; display: none;" class="img-thumbnail">
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Criar Evento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Arrays para armazenar distâncias e itens
        let distancias = [];
        let itens = [];
        
        // Adicionar distância
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
        
        // Adicionar item incluído
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
        
        // Enter para adicionar distância/item
        document.getElementById('distanciaInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addDistancia();
            }
        });
        
        document.getElementById('itemInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addItem();
            }
        });
        
        // Editar evento
        function editEvent(id) {
            window.location.href = 'editar_evento.php?id=' + id;
        }
        
        // Eliminar evento
        function deleteEvent(id) {
            if (confirm('Tem a certeza que deseja eliminar este evento?')) {
                window.location.href = 'processar_evento.php?action=delete&id=' + id;
            }
        }
    </script>
</body>
</html>