<?php
require_once 'includes/db_connect.php';

$sql = "SELECT * FROM evento WHERE status = 'ativo' ORDER BY data_evento ASC";
$result = $conn->query($sql);

$eventos = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $eventos[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Coimbra Running Club - Eventos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <style>
    .bg-primary {
      background-color: #420e76 !important;
    }

    .bg-secondary {
      background-color: #0F172A !important;
    }
    
    .border-primary { 
      border-color: #FF00C8 !important;
    }

    .card {
      transition: all 0.3s ease;
      height: 100%;
    }

    .card:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 24px rgba(255, 0, 200, 0.3) !important;
    }

    .card-img-top {
      height: 200px;
      object-fit: cover;
    }
  </style>
</head>
<body class="bg-secondary">

<?php include 'includes/nav.php'; ?>

<div class="container-fluid py-5">
  <?php if(empty($eventos)): ?>
    <div class="row">
      <div class="col-12 text-center text-light py-5">
        <h3>Ainda não há eventos agendados</h3>
        <p>Volta em breve para conheceres os próximos eventos!</p>
      </div>
    </div>
  <?php else: ?>
    <div class="row g-3 mb-5">
      <?php foreach($eventos as $evento): ?>
        <div class="col-12 col-md-6 col-lg-4">
          <div class="card border border-primary" style="max-width: 300px; margin: 0 auto; margin-top: 3rem;">
            <img src="<?php echo htmlspecialchars($evento['imagem']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($evento['titulo']); ?>">
            <div class="card-body">
              <h5 class="fw-bolder">
                <a class="stretched-link card-title text-decoration-none text-dark" href="Evento1.php?id=<?php echo $evento['id_evento']; ?>">
                  <?php echo htmlspecialchars($evento['titulo']); ?>
                </a>
              </h5>        
              <h6 class="card-subtitle mb-2 text-muted">
                <?php echo date('d/m/Y', strtotime($evento['data_evento'])); ?>
              </h6>
              <p class="card-text">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-geo-alt me-2" viewBox="0 0 16 16">
                  <path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A31.493 31.493 0 0 1 8 14.58a31.481 31.481 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94zM8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10z"/>
                  <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                </svg>
                <?php echo htmlspecialchars($evento['local_nome']); ?>
              </p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
    
<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>