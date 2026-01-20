<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Coimbra Running Club - About Us</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <style>
    .bg-secondary {
      background-color: #0F172A !important;
    }
    
    .border-primary { 
      border-color: #FF00C8 !important;
    }


    .bg-image-full {
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    min-height: 400px; /* Garante altura mínima */
    }

@media (max-width: 768px) {
    .bg-image-full {
        min-height: 300px;
    }
}

.header-logo {
    max-width: 250px;
    width: 90%;
    border: 4px solid rgba(255, 255, 255, 0.8);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    transition: transform 0.3s ease;
}

.header-logo:hover {
    transform: scale(1.05);
}

.lh-lg {
    line-height: 1.8 !important;
}

section {
    scroll-margin-top: 80px;
}

.shadow-sm {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
}
  </style>
</head>
<body class="bg-secondary">
    
<?php include 'includes/nav.php'; ?>


<header class="position-relative py-5 bg-image-full" style="background-image: url('imgs/coimbra.avif'); background-size: cover; background-position: center;">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(0, 0, 0, 0.4);"></div>

    <div class="position-relative text-center my-5">
        <img class="img-fluid rounded-circle mb-4" src="imgs/logo_branco.png" alt="Logo Coimbra Running Club" style="max-width: 250px; width: 90%;" />
        <h1 class="text-white fw-bold mb-2">Unidos pelo movimento</h1>
    </div>
</header>

        <section class="py-5" style="background: linear-gradient(135deg, #0F172A 0%, #420e76 100%);">
    <div class="container my-5">
        <div class="row g-5 align-items-center">
  
            <div class="col-lg-5 text-center">
                <div class="p-5 bg-white bg-opacity-10 rounded-circle d-inline-block">
                    <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" fill="#FF00C8" viewBox="0 0 16 16">
                        <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7Zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-5.784 6A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216ZM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/>
                    </svg>
                </div>
                <h2 class="text-white mt-4 fw-bold">Quem Somos?</h2>
            </div>

            <div class="col-lg-7">
                <p class="lead text-white mb-4">
                    O Coimbra Running Club nasceu da paixão pela corrida e da vontade de criar uma comunidade ativa, unida e inspiradora no coração da cidade de Coimbra.
                </p>
                
                <p class="text-white-50 lh-lg mb-4">
                    Fundado por corredores para corredores, o nosso clube reúne pessoas de todas as idades e níveis de experiência — desde quem está a dar os primeiros passos até atletas experientes que procuram novos desafios.
                </p>

                <div class="border-start border-4 ps-4 mt-5" style="border-color: #FF00C8 !important;">
                    <h3 class="h5 fw-bold text-white mb-3">A Nossa Missão</h3>
                    <p class="text-white-50 mb-0">
                        Promover um estilo de vida saudável, incentivar a prática regular de corrida e fortalecer o espírito comunitário através de treinos, eventos e formação.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
        
<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>