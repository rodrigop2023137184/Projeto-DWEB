<?php

require_once 'includes/db_connect.php';

$sql = "SELECT * FROM produto WHERE status = 'ativo' AND visivel = TRUE ORDER BY data_criacao DESC";
$result = $conn->query($sql);

$produtos = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
<meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Coimbra Running Club - Merch</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.bg-secondary {
    background-color:#0F172A !important;
  } 

.border-primary { 
    border-color:  #FF00C8!important;
}
    
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(255, 0, 200, 0.3) !important;
}

.card-img-top {
    height: 250px;
    object-fit: cover;
}

body.bg-secondary {
    background: linear-gradient(135deg, #0F172A 0%, #420e76 100%);
    background-attachment: fixed;
}

.glass-header {
    background: rgba(66, 14, 118, 0.3) !important;
    backdrop-filter: blur(16px) saturate(180%);
    -webkit-backdrop-filter: blur(16px) saturate(180%);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
}

.glass-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, 
        rgba(255, 0, 200, 0.1) 0%, 
        rgba(66, 14, 118, 0.2) 100%);
    z-index: 1;
}


.badge-stock-baixo {
    background: #dc3545;
    color: white;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.7rem;
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 10;
}
</style>
</head>
<body class="bg-secondary">
  
<?php include 'includes/nav.php'; ?>

<section class="glass-header py-4 text-white position-relative overflow-hidden">
    <div class="container position-relative" style="z-index: 2;">
        <div class="text-center">
            <h1 class="h2 fw-bold mb-2">Loja Oficial CRC</h1>
            <p class="mb-0 small">Equipamento de qualidade para corredores apaixonados</p>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container px-4 px-lg-5 mt-5">
        <?php if(empty($produtos)): ?>
            <div class="row">
                <div class="col-12 text-center text-light py-5">
                    <h3>Ainda não há produtos disponíveis</h3>
                    <p>Volta em breve para ver as novidades!</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
                <?php foreach($produtos as $produto): ?>
                    <div class="col mb-5">
                        <div class="card shadow border border-primary h-100 position-relative"> 
                           
                            <?php if($produto['stock_total'] > 0 && $produto['stock_total'] <= 10): ?>
                                <span class="badge-stock-baixo">
                                    Últimas unidades!
                                </span>
                            <?php elseif($produto['stock_total'] == 0): ?>
                                <span class="badge-stock-baixo">
                                    Esgotado
                                </span>
                            <?php endif; ?>
                            
                            <img class="card-img-top" 
                                 src="<?php echo htmlspecialchars($produto['imagem']); ?>" 
                                 alt="<?php echo htmlspecialchars($produto['nome']); ?>" />
                            
                            <div class="card-body p-4">
                                <div class="text-center">
                                    <h5 class="fw-bolder mb-3">
                                        <a href="Produto1.php?id=<?php echo $produto['id_produto']; ?>" 
                                           class="stretched-link text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($produto['nome']); ?>
                                        </a>
                                    </h5>
                                    
                                    <?php if($produto['tem_tamanhos']): ?>
                                        <p class="small text-muted mb-2">
                                            <?php 
                                            if($produto['tipo_tamanho'] == 'roupa') {
                                                echo 'Tamanhos: XS - XXL';
                                            } elseif($produto['tipo_tamanho'] == 'calcado') {
                                                echo 'Tamanhos: 35 - 46';
                                            } else {
                                                echo 'Vários tamanhos';
                                            }
                                            ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <p class="fw-bold text-primary mb-0">
                                        <?php echo number_format($produto['preco'], 2); ?>€
                                    </p>
                                    
                                    <?php if($produto['stock_total'] > 0 && $produto['stock_total'] <= 20): ?>
                                        <p class="small text-danger mb-0">
                                            Apenas <?php echo $produto['stock_total']; ?> em stock
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>