<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'includes/db_connect.php';

$id_encomenda = isset($_GET['id']) ? intval($_GET['id']) : 0;
$id_utilizador = $_SESSION['id_utilizador'];

// dados da encomenda
$stmt = $conn->prepare("
    SELECT * FROM encomenda 
    WHERE id_encomenda = ? AND id_utilizador = ?
");
$stmt->bind_param("ii", $id_encomenda, $id_utilizador);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: home.php');
    exit;
}

$encomenda = $result->fetch_assoc();

// itens da encomenda
$stmt = $conn->prepare("
    SELECT * FROM encomenda_item 
    WHERE id_encomenda = ?
");
$stmt->bind_param("i", $id_encomenda);
$stmt->execute();
$itens = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Encomenda Confirmada - CRC</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <style>
    :root {
      --primary: #420e76;
      --accent: #FF00C8;
    }

    .success-icon {
      width: 80px;
      height: 80px;
      background-color: #22c55e;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      animation: scaleIn 0.5s ease-out;
    }

    @keyframes scaleIn {
      from {
        transform: scale(0);
      }
      to {
        transform: scale(1);
      }
    }

    .order-card {
      background: white;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }

    .order-number {
      font-size: 2rem;
      font-weight: bold;
      color: var(--primary);
    }

    .info-box {
      background-color: #f8f9fa;
      border-left: 4px solid var(--accent);
      padding: 15px;
      margin-bottom: 15px;
      border-radius: 6px;
    }

    .btn-primary {
      background-color: var(--primary) !important;
      border-color: var(--primary) !important;
    }

    .btn-primary:hover {
      background-color: #5a1199 !important;
    }

    .payment-ref {
      background-color: #fff3cd;
      border: 2px dashed #ffc107;
      padding: 20px;
      border-radius: 10px;
      text-align: center;
    }

    .ref-number {
      font-size: 1.5rem;
      font-weight: bold;
      color: #333;
      letter-spacing: 2px;
    }
  </style>
</head>
<body class="bg-light">

<?php include 'includes/nav.php'; ?>

  <section class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <!-- Sucesso -->
          <div class="order-card text-center">
            <div class="success-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="white" viewBox="0 0 16 16">
                <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
              </svg>
            </div>
            <h1 class="mb-3">Encomenda Confirmada!</h1>
            <p class="text-muted mb-4">Obrigado pela tua compra. A tua encomenda foi recebida e está a ser processada.</p>
            <div class="order-number mb-2">#<?php echo str_pad($encomenda['id_encomenda'], 6, '0', STR_PAD_LEFT); ?></div>
            <small class="text-muted">Número da Encomenda</small>
          </div>

          <div class="order-card">
            <h3 class="mb-4" style="color: var(--primary);">Detalhes da Encomenda</h3>
            
            <div class="row g-3">
              <div class="col-md-6">
                <div class="info-box">
                  <strong>Data:</strong><br>
                  <?php echo date('d/m/Y H:i', strtotime($encomenda['data_encomenda'])); ?>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-box">
                  <strong>Estado:</strong><br>
                  <span class="badge bg-warning">Pendente</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-box">
                  <strong>Método de Pagamento:</strong><br>
                  <?php 
                    $metodos = [
                      'mbway' => 'MB WAY',
                      'multibanco' => 'Multibanco',
                      'cartao' => 'Cartão',
                      'transferencia' => 'Transferência'
                    ];
                    echo $metodos[$encomenda['metodo_pagamento']] ?? 'N/A';
                  ?>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-box">
                  <strong>Total:</strong><br>
                  <span class="fs-5 fw-bold" style="color: var(--primary);">€<?php echo number_format($encomenda['total'], 2, ',', '.'); ?></span>
                </div>
              </div>
            </div>

            <?php if ($encomenda['metodo_pagamento'] == 'multibanco' && !empty($encomenda['referencia_pagamento'])): ?>
              <div class="payment-ref mt-4">
                <h5 class="mb-3">Referência Multibanco</h5>
                <div class="ref-number mb-2"><?php echo htmlspecialchars($encomenda['referencia_pagamento']); ?></div>
                <small class="text-muted">Por favor, efetua o pagamento até 48 horas</small>
              </div>
            <?php elseif ($encomenda['metodo_pagamento'] == 'mbway' && !empty($encomenda['referencia_pagamento'])): ?>
              <div class="payment-ref mt-4">
                <h5 class="mb-3">Pagamento MB WAY</h5>
                <p>Foi enviado um pedido de pagamento para o seu telemóvel.</p>
              </div>
            <?php endif; ?>
          </div>

          <div class="order-card">
            <h4 class="mb-3" style="color: var(--primary);">Morada de Envio</h4>
            <p class="mb-1"><strong><?php echo htmlspecialchars($encomenda['nome_destinatario']); ?></strong></p>
            <p class="mb-1"><?php echo htmlspecialchars($encomenda['morada_envio']); ?></p>
            <p class="mb-1"><?php echo htmlspecialchars($encomenda['codigo_postal']); ?> <?php echo htmlspecialchars($encomenda['cidade']); ?></p>
            <p class="mb-0"><strong>Tel:</strong> <?php echo htmlspecialchars($encomenda['telefone']); ?></p>
          </div>

          <div class="order-card">
            <h4 class="mb-3" style="color: var(--primary);">Produtos</h4>
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th>Produto</th>
                    <th>Tamanho</th>
                    <th>Qtd</th>
                    <th>Preço</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($itens as $item): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($item['nome_produto']); ?></td>
                      <td><?php echo htmlspecialchars($item['tamanho'] ?? 'N/A'); ?></td>
                      <td><?php echo $item['quantidade']; ?></td>
                      <td>€<?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                      <td>€<?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                    <td><strong>€<?php echo number_format($encomenda['subtotal'], 2, ',', '.'); ?></strong></td>
                  </tr>
                  <tr>
                    <td colspan="4" class="text-end"><strong>Envio:</strong></td>
                    <td><strong><?php echo $encomenda['taxa_envio'] == 0 ? 'Grátis' : '€' . number_format($encomenda['taxa_envio'], 2, ',', '.'); ?></strong></td>
                  </tr>
                  <tr class="table-active">
                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                    <td><strong style="color: var(--primary);">€<?php echo number_format($encomenda['total'], 2, ',', '.'); ?></strong></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <div class="text-center">
            <a href="home.php" class="btn btn-primary btn-lg me-2">
              Voltar à Loja
            </a>
            <a href="perfil.php" class="btn btn-primary btn-lg">
              Ver Minhas Encomendas
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

<?php include 'includes/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>