<?php
session_start();
require_once 'includes/db_connect.php';

// Buscar próximos eventos 
$stmt = $conn->prepare("
    SELECT id_evento, titulo, data_evento, hora_evento, local_nome, imagem, descricao_curta
    FROM evento 
    WHERE data_evento >= CURDATE() 
    AND status = 'ativo'
    AND imagem IS NOT NULL
    AND imagem != ''
    ORDER BY data_evento ASC
    LIMIT 5
");
$stmt->execute();
$eventos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Se não houver eventos futuros, buscar os mais recentes
if (empty($eventos)) {
    $stmt = $conn->prepare("
        SELECT id_evento, titulo, data_evento, hora_evento, local_nome, imagem, descricao_curta
        FROM evento 
        WHERE status = 'ativo'
        AND imagem IS NOT NULL
        AND imagem != ''
        ORDER BY data_evento DESC
        LIMIT 5
    ");
    $stmt->execute();
    $eventos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Converter para JSON para JavaScript
$eventos_json = json_encode($eventos);
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
 <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Coimbra Running Club - Home</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
<style>
:root {
    --primary: #420e76;
    --accent: #FF00C8;
}

body {
    margin: 0;
    padding: 0;
    overflow-x: hidden;
    min-height: 100vh;
    position: relative;
}

.background-slider {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
}

.background-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    opacity: 0;
    transition: opacity 2s ease-in-out;
}

.background-slide.active {
    opacity: 1;
}

.background-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.7) 0%, rgba(66, 14, 118, 0.5) 100%);
    z-index: -1;
}

.event-card {
    position: fixed;
    bottom: 2rem;
    left: 2rem;
    max-width: 400px;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 2rem;
    color: white;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.1);
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 100;
}

.event-card.active {
    opacity: 1;
    transform: translateY(0);
}

.event-card-title {
    font-size: 1.8rem;
    font-weight: bold;
    margin-bottom: 1.2rem;
    color: var(--accent);
    line-height: 1.2;
}

.event-card-info {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
    font-size: 1rem;
    color: rgba(255, 255, 255, 0.9);
}

.event-card-info svg {
    flex-shrink: 0;
    opacity: 0.8;
}

.event-card-description {
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 1.5rem;
    line-height: 1.6;
    max-height: 60px;
    overflow: hidden;
}

.btn-event {
    background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
    border: none;
    padding: 12px 30px;
    border-radius: 50px;
    font-weight: bold;
    color: white;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-event:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(255, 0, 200, 0.4);
    color: white;
}



@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .event-card {
        left: 1rem;
        right: 1rem;
        bottom: 1rem;
        max-width: calc(100% - 2rem);
        padding: 1.5rem;
    }
    
    .event-card-title {
        font-size: 1.5rem;
    }
    
    .event-indicators {
        display: none;
    }
    
    .nav-buttons {
        display: none;
    }
}


</style>
</head>
<body>
    
<?php include 'includes/nav.php'; ?>

<div class="background-slider" id="backgroundSlider"></div>
<div class="background-overlay"></div>

<div class="event-card" id="eventCard">
    <h2 class="event-card-title" id="eventTitle">Carregando...</h2>
    <div class="event-card-info" id="eventDate">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
            <path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z"/>
            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
        </svg>
        <span></span>
    </div>
    <div class="event-card-info" id="eventLocation">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
            <path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A31.493 31.493 0 0 1 8 14.58a31.481 31.481 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94zM8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10z"/>
            <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
        </svg>
        <span></span>
    </div>
    <p class="event-card-description" id="eventDescription"></p>
    <a class="btn-event" id="eventLink" href="#">Saber mais</a>
</div>

<script>
const eventos = <?php echo $eventos_json; ?>;
let currentIndex = 0;
let autoPlayInterval = null;
let progressInterval = null;
const AUTO_PLAY_DURATION = 8000; 

document.addEventListener('DOMContentLoaded', function() {
    if (eventos.length === 0) {
        document.getElementById('eventCard').innerHTML = '<p>Nenhum evento disponível no momento.</p>';
        return;
    }
    
    createBackgroundSlides();
    showEvent(0);
    startAutoPlay();
  
    const eventCard = document.getElementById('eventCard');
    eventCard.addEventListener('mouseenter', stopAutoPlay);
    eventCard.addEventListener('mouseleave', startAutoPlay);
});

function createBackgroundSlides() {
    const slider = document.getElementById('backgroundSlider');
    eventos.forEach((evento, index) => {
        const slide = document.createElement('div');
        slide.className = 'background-slide';
        slide.style.backgroundImage = `url('${evento.imagem}')`;
        slide.id = `bg-slide-${index}`;
        slider.appendChild(slide);
    });
}

function showEvent(index) {
    currentIndex = index;
    const evento = eventos[index];
    
    // Atualizar backgrounds
    document.querySelectorAll('.background-slide').forEach((slide, i) => {
        slide.classList.toggle('active', i === index);
    });
    
    // Atualizar card com animação
    const card = document.getElementById('eventCard');
    card.classList.remove('active');
    
    setTimeout(() => {
        // Formatar data
        const data = new Date(evento.data_evento + 'T00:00:00');
        const dataFormatada = data.toLocaleDateString('pt-PT', { 
            day: '2-digit', 
            month: '2-digit', 
            year: 'numeric' 
        });
        
        // Formatar hora
        const horaFormatada = evento.hora_evento ? evento.hora_evento.substring(0, 5) : '';
        
        // Atualizar conteúdo
        document.getElementById('eventTitle').textContent = evento.titulo;
        document.querySelector('#eventDate span').textContent = `${dataFormatada}${horaFormatada ? ' às ' + horaFormatada : ''}`;
        document.querySelector('#eventLocation span').textContent = evento.local_nome || 'Local a definir';
        document.getElementById('eventDescription').textContent = evento.descricao_curta || '';
        document.getElementById('eventLink').href = `evento1.php?id=${evento.id_evento}`;
        
        card.classList.add('active');
    }, 400);
}

function startAutoPlay() {
    stopAutoPlay(); 
    
    autoPlayInterval = setInterval(() => {
        showEvent((currentIndex + 1) % eventos.length);
    }, AUTO_PLAY_DURATION);
}

function stopAutoPlay() {
    if (autoPlayInterval) {
        clearInterval(autoPlayInterval);
        autoPlayInterval = null;
    }
}

// Pausar/retomar com tecla Espaço
document.addEventListener('keydown', (e) => {
    if (e.key === ' ') {
        e.preventDefault();
        if (autoPlayInterval) {
            stopAutoPlay();
        } else {
            startAutoPlay();
        }
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>