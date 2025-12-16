<?php
// Buscar ID do evento da URL 
$id_evento = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Conexão à base de dados
require_once 'includes/db_connect.php';

// dados do evento
$stmt = $conn->prepare("SELECT * FROM evento WHERE id_evento = ? AND status = 'ativo'");
$stmt->bind_param("i", $id_evento);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: Eventos.php');
    exit;
}

$evento = $result->fetch_assoc();

// Decodificar JSON
$distancias = json_decode($evento['distancias_disponiveis'], true);
$percurso = json_decode($evento['detalhes_percurso'], true);
$itens = json_decode($evento['itens_incluidos'], true);

// Formatar data em português (requer extensão intl)
$formatter = new IntlDateFormatter(
    'pt_PT',
    IntlDateFormatter::LONG,
    IntlDateFormatter::NONE,
    'Europe/Lisbon',
    IntlDateFormatter::GREGORIAN
);

$timestamp = strtotime($evento['data_evento']);
$data_formatada = $formatter->format($timestamp);
$hora_formatada = date('H:i', strtotime($evento['hora_evento']));

// Calcular vagas disponíveis
$stmt = $conn->prepare("SELECT COUNT(*) as total_inscritos FROM inscricao_evento WHERE id_evento = ? AND status = 'ativa'");
$stmt->bind_param("i", $id_evento);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$vagas_ocupadas_reais = $result['total_inscritos'];

$vagas_disponiveis = $evento['vagas_totais'] - $vagas_ocupadas_reais;
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($evento['titulo']); ?> - Eventos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.bg-secondary {
    background-color: #0F172A !important;
} 

.border-primary { 
    border-color: #FF00C8 !important;
}

.text-accent {
    color: #FF00C8 !important;
}

.evento-hero {
    position: relative;
    height: 400px;
    background-size: cover;
    background-position: center;
    overflow: hidden;
}

.evento-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(15,23,42,0.9));
}

.evento-hero-content {
    position: relative;
    z-index: 1;
    height: 100%;
}

.info-card {
    background-color: rgba(66, 14, 118, 0.1);
    border: 2px solid #FF00C8;
    border-radius: 10px;
    padding: 1.5rem;
    transition: transform 0.3s, box-shadow 0.3s;
}

.info-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(255, 0, 200, 0.3);
}

.info-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #420e76, #FF00C8);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

.form-control, .form-select {
    background-color: #0F172A;
    border: 1px solid #FF00C8;
    color: #fff;
}

.form-control:focus, .form-select:focus {
    border-color: #FF00C8;
    box-shadow: 0 0 0 0.2rem rgba(255, 0, 200, 0.25);
    background-color: #0F172A;
    color: #fff;
}

.form-select option {
    background-color: #0F172A;
    color: #fff;
}

.btn-register-event {
    background: linear-gradient(135deg, #420e76, #FF00C8);
    border: none;
    padding: 12px 40px;
    font-weight: bold;
    transition: transform 0.3s, box-shadow 0.3s;
    color: white;
}

.btn-register-event:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(255, 0, 200, 0.4);
}

.section-title {
    position: relative;
    padding-bottom: 15px;
    margin-bottom: 2rem;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 3px;
    background: linear-gradient(to right, #420e76, #FF00C8);
}

.badge-custom {
    background: linear-gradient(135deg, #420e76, #FF00C8);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
}

.timeline-item {
    position: relative;
    padding-left: 40px;
    margin-bottom: 2rem;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    width: 20px;
    height: 20px;
    background: #FF00C8;
    border-radius: 50%;
    border: 3px solid #0F172A;
}

.timeline-item::after {
    content: '';
    position: absolute;
    left: 9px;
    top: 20px;
    width: 2px;
    height: calc(100% + 20px);
    background: #FF00C8;
}

.timeline-item:last-child::after {
    display: none;
}

.feature-list li {
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(255, 0, 200, 0.2);
}

.feature-list li:last-child {
    border-bottom: none;
}
</style>
</head>
<body class="bg-secondary text-white d-flex flex-column min-vh-100">
    
<?php include 'includes/nav.php'; ?>

    <div class="evento-hero" style="background-image: url('<?php echo htmlspecialchars($evento['imagem']); ?>');">
        <div class="evento-hero-content d-flex align-items-end">
            <div class="container pb-4">
                <span class="badge badge-custom mb-2"><?php echo htmlspecialchars($evento['subtitulo']); ?></span>
                <h1 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars($evento['titulo']); ?></h1>
                <p class="lead mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-calendar-event me-2" viewBox="0 0 16 16">
                        <path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z"/>
                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                    </svg>
                    <?php echo $data_formatada . ' · ' . $hora_formatada; ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-geo-alt ms-3 me-2" viewBox="0 0 16 16">
                        <path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A31.493 31.493 0 0 1 8 14.58a31.481 31.481 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94zM8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10z"/>
                        <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                    </svg>
                    <?php echo htmlspecialchars($evento['local_nome']); ?>
                </p>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="row g-4 mb-5">
                    <div class="col-md-3 col-6">
                        <div class="info-card text-center">
                            <div class="info-icon mx-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="white" viewBox="0 0 16 16">
                                    <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                    <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                </svg>
                            </div>
                            <h6 class="fw-bold mb-1">Duração</h6>
                            <p class="small mb-0 text-accent"><?php echo htmlspecialchars($evento['duracao_estimada']); ?></p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="info-card text-center">
                            <div class="info-icon mx-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="white" viewBox="0 0 16 16">
                                    <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                                    <path fill-rule="evenodd" d="M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216z"/>
                                    <path d="M4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"/>
                                </svg>
                            </div>
                            <h6 class="fw-bold mb-1">Participantes</h6>
                            <p class="small mb-0 text-accent"><?php echo $evento['vagas_ocupadas'] . '/' . $evento['vagas_totais']; ?></p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="info-card text-center">
                            <div class="info-icon mx-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="white" viewBox="0 0 16 16">
                                    <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                                </svg>
                            </div>
                            <h6 class="fw-bold mb-1">Transporte</h6>
                            <p class="small mb-0 text-accent"><?php echo $evento['tem_transporte'] ? 'Incluído' : 'Não incluído'; ?></p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="info-card text-center">
                            <div class="info-icon mx-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="white" viewBox="0 0 16 16">
                                    <path d="M5.5 9.511c.076.954.83 1.697 2.182 1.785V12h.6v-.709c1.4-.098 2.218-.846 2.218-1.932 0-.987-.626-1.496-1.745-1.76l-.473-.112V5.57c.6.068.982.396 1.074.85h1.052c-.076-.919-.864-1.638-2.126-1.716V4h-.6v.719c-1.195.117-2.01.836-2.01 1.853 0 .9.606 1.472 1.613 1.707l.397.098v2.034c-.615-.093-1.022-.43-1.114-.9H5.5zm2.177-2.166c-.59-.137-.91-.416-.91-.836 0-.47.345-.822.915-.925v1.76h-.005zm.692 1.193c.717.166 1.048.435 1.048.91 0 .542-.412.914-1.135.982V8.518l.087.02z"/>
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                    <path d="M8 13.5a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11zm0 .5A6 6 0 1 0 8 2a6 6 0 0 0 0 12z"/>
                                </svg>
                            </div>
                            <h6 class="fw-bold mb-1">Inscrição</h6>
                            <p class="small mb-0 text-accent"><?php echo number_format($evento['preco'], 2) . '€'; ?></p>
                        </div>
                    </div>
                </div>

                <section class="mb-5">
                    <h2 class="section-title">Sobre o Evento</h2>
                    <p class="lead mb-3">
                        <?php echo nl2br(htmlspecialchars($evento['descricao_curta'])); ?>
                    </p>
                    <p>
                        <?php echo nl2br(htmlspecialchars($evento['descricao_completa'])); ?>
                    </p>
                </section>

                <section class="mb-5">
                    <h2 class="section-title">Percurso</h2>
                    <div class="row">
                        <?php foreach ($percurso as $distancia => $pontos): ?>
                        <div class="col-md-<?php echo count($percurso) > 1 ? '6' : '12'; ?> mb-3">
                            <div class="p-4 rounded" style="background-color: rgba(66, 14, 118, 0.2); border-left: 4px solid #FF00C8;">
                                <h5 class="fw-bold text-accent mb-3"><?php echo strtoupper($distancia); ?></h5>
                                <?php foreach ($pontos as $ponto): ?>
                                <div class="timeline-item">
                                    <h6 class="fw-bold"><?php echo htmlspecialchars($ponto['nome']); ?></h6>
                                    <p class="small mb-0"><?php echo htmlspecialchars($ponto['local']); ?></p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                
                <section class="mb-5">
                    <h2 class="section-title">O que está incluído</h2>
                    <div class="row">
                        <?php 
                        $metade = ceil(count($itens) / 2);
                        $primeira_metade = array_slice($itens, 0, $metade);
                        $segunda_metade = array_slice($itens, $metade);
                        ?>
                        <div class="col-md-6">
                            <ul class="feature-list list-unstyled">
                                <?php foreach ($primeira_metade as $item): ?>
                                <li>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#FF00C8" class="bi bi-check-circle-fill me-2" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                    </svg>
                                    <?php echo htmlspecialchars($item); ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="feature-list list-unstyled">
                                <?php foreach ($segunda_metade as $item): ?>
                                <li>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#FF00C8" class="bi bi-check-circle-fill me-2" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                    </svg>
                                    <?php echo htmlspecialchars($item); ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </section>
            </div>
    
             <div class="col-lg-4">
                <div class="p-4 rounded" style="background-color: rgba(66, 14, 118, 0.2); border: 2px solid #FF00C8;">
                    <h3 class="fw-bold mb-4 text-center">Inscreva-se Agora</h3>
        
                  <div class="alert alert-danger d-none" id="alertError" role="alert"></div>
                  <div class="alert alert-success d-none" id="alertSuccess" role="alert"></div>
        
                  <?php if ($vagas_disponiveis <= 0): ?>
                     <div class="alert alert-danger text-center">
                     <strong>Evento Esgotado!</strong><br>
                     Não há vagas disponíveis.
                     </div>
                   <?php else: ?>
                      <?php
                         $ja_inscrito = false;
                         if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                          $stmt = $conn->prepare("SELECT id_inscricao FROM inscricao_evento WHERE id_evento = ? AND id_usuario = ?");
                          $stmt->bind_param("ii", $id_evento, $_SESSION['user_id']);
                          $stmt->execute();
                         $ja_inscrito = $stmt->get_result()->num_rows > 0;
                         }
                       ?>
            
                   <?php if ($ja_inscrito): ?>
                      <div class="alert alert-info text-center">
                         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-check-circle-fill mb-2" viewBox="0 0 16 16">
                           <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                          </svg>
                           <br>
                          <strong>Já está inscrito neste evento!</strong>
                        </div>
                       <a href="perfil.php" class="btn btn-register-event w-100">Ver Minhas Inscrições</a>
                   <?php else: ?>
                     <form id="inscricaoForm">
                      <input type="hidden" name="id_evento" value="<?php echo $evento['id_evento']; ?>">
                    
                    <div class="mb-3">
                        <label for="distance" class="form-label fw-bold">Distância</label>
                        <select class="form-select" id="distance" name="distancia" required>
                            <?php foreach ($distancias as $dist): ?>
                            <option value="<?php echo htmlspecialchars($dist); ?>">
                                <?php echo strtoupper(htmlspecialchars($dist)); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="size" class="form-label fw-bold">Tamanho da T-shirt</label>
                        <select class="form-select" id="size" name="tamanho" required>
                            <option value="XS">XS</option>
                            <option value="S">S</option>
                            <option value="M" selected>M</option>
                            <option value="L">L</option>
                            <option value="XL">XL</option>
                            <option value="XXL">XXL</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-register-event w-100" id="btnInscricao">
                        Inscrever-se por <?php echo number_format($evento['preco'], 2); ?>€
                    </button>
                    
                    <p class="text-center mt-3 mb-0 small text-light">
                        Restam <span id="vagasRestantes"><?php echo $vagas_disponiveis; ?></span> vagas
                    </p>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
        
             
        </div>
    </div>
<script>
 document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('inscricaoForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Limpar alertas
            document.getElementById('alertError').classList.add('d-none');
            document.getElementById('alertSuccess').classList.add('d-none');
            
            // block botão
            const btn = document.getElementById('btnInscricao');
            const btnTextoOriginal = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>A processar...';
            
            // Enviar dados
            const formData = new FormData(this);
            
            fetch('processar_inscricao.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('alertSuccess').textContent = 
                        `${data.message} Seu número de dorsal é: ${data.numero_dorsal}`;
                    document.getElementById('alertSuccess').classList.remove('d-none');
                    
                    // Esconder formulário e mostrar mensagem
                    setTimeout(() => {
                        form.innerHTML = `
                            <div class="alert alert-success text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-check-circle-fill mb-3" viewBox="0 0 16 16">
                                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                </svg>
                                <h5>Inscrição Confirmada!</h5>
                                <p class="mb-2">Seu número de dorsal: <strong>${data.numero_dorsal}</strong></p>
                                <a href="perfil.php" class="btn btn-register-event mt-2">Ver Minhas Inscrições</a>
                            </div>
                        `;
                        
                        // Atualizar vagas restantes 
                        const vagasElement = document.getElementById('vagasRestantes');
                        if (vagasElement) {
                            const vagasAtuais = parseInt(vagasElement.textContent);
                            vagasElement.textContent = vagasAtuais - 1;
                        }
                    }, 2000);
                    
                } else {
                    document.getElementById('alertError').textContent = data.error;
                    document.getElementById('alertError').classList.remove('d-none');
                    
                    // Se precisa fazer login, redirecionar
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 2000);
                    }
                    
                    // Reativar botão
                    btn.disabled = false;
                    btn.innerHTML = btnTextoOriginal;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                document.getElementById('alertError').textContent = 'Erro ao processar inscrição. Tente novamente.';
                document.getElementById('alertError').classList.remove('d-none');
                
                // Reativar botão
                btn.disabled = false;
                btn.innerHTML = btnTextoOriginal;
            });
        });
    }
 });
</script>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
  