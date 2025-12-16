<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'includes/db_connect.php';

$id_utilizador = $_SESSION['id_utilizador'];

$stmt = $conn->prepare("
    SELECT primeiro_nome, ultimo_nome, email, telefone
    FROM utilizador 
    WHERE id_utilizador = ?
");
$stmt->bind_param("i", $id_utilizador);
$stmt->execute();
$utilizador = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("
    SELECT 
        c.id_carrinho,
        c.quantidade,
        c.tamanho_escolhido,
        c.preco_unitario,
        p.id_produto,
        p.nome,
        p.imagem,
        (c.quantidade * c.preco_unitario) as subtotal
    FROM carrinho c
    INNER JOIN produto p ON c.id_produto = p.id_produto
    WHERE c.id_utilizador = ?
    ORDER BY c.data_adicao DESC
");
$stmt->bind_param("i", $id_utilizador);
$stmt->execute();
$result = $stmt->get_result();
$itens = $result->fetch_all(MYSQLI_ASSOC);

// Se carrinho vazio, redirecionar
if (empty($itens)) {
    header('Location: carrinho.php');
    exit;
}

// Calcular totais
$subtotal = 0;
foreach ($itens as $item) {
    $subtotal += $item['subtotal'];
}

$taxa_envio = $subtotal >= 50 ? 0 : 5.00;
$iva = $subtotal * 0.23;
$total = $subtotal + $taxa_envio;
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Coimbra Running Club - Finalizar Compra</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <style>
    :root {
      --primary: #420e76;
      --secondary: #0F172A;
      --accent: #FF00C8;
    }

    .bg-primary {
      background-color: var(--primary) !important;
    }

    .bg-secondary {
      background-color: var(--secondary) !important;
    }

    .border-primary { 
      border-color: var(--accent) !important;
    }

    .text-primary {
      color: var(--accent) !important;
    }

    .btn-primary {
      background-color: var(--primary) !important;
      border-color: var(--primary) !important;
      transition: all 0.3s ease;
    }

    .btn-primary:hover {
      background-color: #5a1199 !important;
      border-color: #5a1199 !important;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(66, 14, 118, 0.4);
    }

    .btn-accent {
      background-color: var(--accent);
      color: white;
      border: none;
      transition: all 0.3s;
      padding: 12px 30px;
      font-weight: bold;
    }

    .btn-accent:hover {
      background-color: #d400a8;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(255, 0, 200, 0.3);
    }

    .checkout-section {
      background-color: white;
      border-radius: 12px;
      padding: 30px;
      margin-bottom: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      border-left: 4px solid var(--accent);
    }

    .section-title {
      color: var(--primary);
      font-weight: bold;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 0.2rem rgba(255, 0, 200, 0.25);
    }

    .step-indicator {
      display: flex;
      justify-content: space-between;
      margin-bottom: 40px;
      position: relative;
    }

    .step-indicator::before {
      content: '';
      position: absolute;
      top: 20px;
      left: 0;
      right: 0;
      height: 2px;
      background-color: #e0e0e0;
      z-index: 0;
    }

    .step {
      display: flex;
      flex-direction: column;
      align-items: center;
      position: relative;
      z-index: 1;
      background-color: #f8f9fa;
      padding: 0 10px;
    }

    .step-number {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background-color: #e0e0e0;
      color: #666;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      margin-bottom: 8px;
      transition: all 0.3s;
    }

    .step.active .step-number {
      background-color: var(--accent);
      color: white;
    }

    .step.completed .step-number {
      background-color: var(--primary);
      color: white;
    }

    .step-label {
      font-size: 0.85rem;
      color: #666;
      text-align: center;
    }

    .step.active .step-label {
      color: var(--accent);
      font-weight: bold;
    }

    .order-summary {
      background-color: #f8f9fa;
      border-radius: 12px;
      padding: 25px;
      position: sticky;
      top: 20px;
    }

    .summary-item {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid #dee2e6;
    }

    .summary-item:last-child {
      border-bottom: none;
    }

    .summary-total {
      font-size: 1.3rem;
      font-weight: bold;
      color: var(--primary);
      padding-top: 15px;
      margin-top: 15px;
      border-top: 2px solid var(--accent);
    }

    .product-mini {
      display: flex;
      align-items: center;
      gap: 15px;
      padding: 15px;
      background-color: white;
      border-radius: 8px;
      margin-bottom: 10px;
      border: 1px solid #e0e0e0;
    }

    .product-mini img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 6px;
    }

    .product-mini-info {
      flex: 1;
    }

    .product-mini-title {
      font-weight: bold;
      font-size: 0.9rem;
      margin-bottom: 3px;
    }

    .product-mini-details {
      font-size: 0.8rem;
      color: #666;
    }

    .payment-method {
      border: 2px solid #e0e0e0;
      border-radius: 10px;
      padding: 20px;
      cursor: pointer;
      transition: all 0.3s;
      margin-bottom: 15px;
    }

    .payment-method:hover {
      border-color: var(--accent);
      background-color: #f8f9fa;
    }

    .payment-method.selected {
      border-color: var(--accent);
      background-color: rgba(255, 0, 200, 0.05);
    }

    .payment-method input[type="radio"] {
      width: 20px;
      height: 20px;
      accent-color: var(--accent);
    }

    @media (max-width: 768px) {
      .step-label {
        font-size: 0.7rem;
      }
      
      .step-number {
        width: 35px;
        height: 35px;
      }
    }
  </style>
</head>
<body class="bg-light">

<?php include 'includes/nav.php'; ?>

  <section class="bg-primary py-4 text-white">
    <div class="container">
      <div class="d-flex align-items-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="me-3" viewBox="0 0 16 16">
          <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
        </svg>
        <div>
          <h1 class="h3 fw-bold mb-0">Finalizar Compra</h1>
          <p class="mb-0 small opacity-75">Completa os teus dados para finalizar o pedido</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Step Indicator -->
  <section class="bg-light py-4">
    <div class="container">
      <div class="step-indicator">
        <div class="step completed">
          <div class="step-number">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
              <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
            </svg>
          </div>
          <span class="step-label">Carrinho</span>
        </div>
        <div class="step active">
          <div class="step-number">2</div>
          <span class="step-label">Dados de Envio</span>
        </div>
        <div class="step">
          <div class="step-number">3</div>
          <span class="step-label">Pagamento</span>
        </div>
        <div class="step">
          <div class="step-number">4</div>
          <span class="step-label">Confirmação</span>
        </div>
      </div>
    </div>
  </section>

  <section class="py-5">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-7">
          <form id="checkoutForm" method="POST" action="processar_encomenda.php">
            <div class="checkout-section">
              <h3 class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                </svg>
                Dados Pessoais
              </h3>
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="firstName" class="form-label">Nome *</label>
                  <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo htmlspecialchars($utilizador['primeiro_nome'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                  <label for="lastName" class="form-label">Apelido *</label>
                  <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo htmlspecialchars($utilizador['ultimo_nome'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                  <label for="email" class="form-label">Email *</label>
                  <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($utilizador['email'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                  <label for="phone" class="form-label">Telefone *</label>
                  <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($utilizador['telefone'] ?? ''); ?>" required>
                </div>
              </div>
            </div>

            <div class="checkout-section">
              <h3 class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>
                </svg>
                Morada de Envio
              </h3>
              <div class="row g-3">
                <div class="col-12">
                  <label for="address" class="form-label">Morada *</label>
                  <input type="text" class="form-control" id="address" name="address" placeholder="Rua, Avenida..." required>
                </div>
                <div class="col-md-6">
                  <label for="city" class="form-label">Cidade *</label>
                  <input type="text" class="form-control" id="city" name="city" required>
                </div>
                <div class="col-md-3">
                  <label for="postalCode" class="form-label">Código Postal *</label>
                  <input type="text" class="form-control" id="postalCode" name="postalCode" placeholder="0000-000" required>
                </div>
                <div class="col-md-3">
                  <label for="country" class="form-label">País *</label>
                  <select class="form-select" id="country" name="country" required>
                    <option value="Portugal" selected>Portugal</option>
                    <option value="Espanha">Espanha</option>
                    <option value="França">França</option>
                  </select>
                </div>
                <div class="col-12">
                  <label for="notes" class="form-label">Notas do Pedido (opcional)</label>
                  <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Ex: Deixar no portão, tocar campainha..."></textarea>
                </div>
              </div>
            </div>

            <div class="checkout-section">
              <h3 class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1H2zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7z"/>
                  <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1z"/>
                </svg>
                Método de Pagamento
              </h3>
              
              <div class="payment-method selected" onclick="selectPayment(this)">
                <div class="d-flex align-items-center">
                  <input type="radio" name="payment" id="mbway" value="mbway" checked>
                  <label for="mbway" class="ms-3 mb-0 flex-grow-1 cursor-pointer">
                    <div class="d-flex align-items-center justify-content-between">
                      <div>
                        <strong>MB WAY</strong>
                        <div class="small text-muted">Pagamento por telemóvel</div>
                      </div>
                      <div style="background: #009fe3; color: white; padding: 5px 15px; border-radius: 5px; font-weight: bold;">
                        MB WAY
                      </div>
                    </div>
                  </label>
                </div>
              </div>

              <div class="payment-method" onclick="selectPayment(this)">
                <div class="d-flex align-items-center">
                  <input type="radio" name="payment" id="multibanco" value="multibanco">
                  <label for="multibanco" class="ms-3 mb-0 flex-grow-1 cursor-pointer">
                    <div class="d-flex align-items-center justify-content-between">
                      <div>
                        <strong>Multibanco</strong>
                        <div class="small text-muted">Pagamento por referência</div>
                      </div>
                      <div style="background: #1a1a1a; color: white; padding: 5px 15px; border-radius: 5px; font-weight: bold; font-size: 0.85rem;">
                        MULTIBANCO
                      </div>
                    </div>
                  </label>
                </div>
              </div>

              <div class="payment-method" onclick="selectPayment(this)">
                <div class="d-flex align-items-center">
                  <input type="radio" name="payment" id="cartao" value="cartao">
                  <label for="cartao" class="ms-3 mb-0 flex-grow-1 cursor-pointer">
                    <div class="d-flex align-items-center justify-content-between">
                      <div>
                        <strong>Cartão de Crédito/Débito</strong>
                        <div class="small text-muted">Pagamento seguro</div>
                      </div>
                    </div>
                  </label>
                </div>
              </div>
            </div>

            <div class="checkout-section">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                <label class="form-check-label" for="terms">
                  Aceito os <a href="#" class="text-primary">Termos e Condições</a> e a <a href="#" class="text-primary">Política de Privacidade</a> *
                </label>
              </div>
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                <label class="form-check-label" for="newsletter">
                  Quero receber novidades e promoções exclusivas por email
                </label>
              </div>
            </div>
          </form>
        </div>

        <div class="col-lg-5">
          <div class="order-summary">
            <h3 class="section-title mb-4" style="color: var(--primary);">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
              </svg>
              Resumo do Pedido
            </h3>

            <div class="mb-3">
              <?php foreach ($itens as $item): ?>
                <div class="product-mini">
                  <img src="<?php echo htmlspecialchars($item['imagem']); ?>" alt="<?php echo htmlspecialchars($item['nome']); ?>">
                  <div class="product-mini-info">
                    <div class="product-mini-title"><?php echo htmlspecialchars($item['nome']); ?></div>
                    <div class="product-mini-details">
                      <?php if (!empty($item['tamanho_escolhido'])): ?>
                        Tamanho: <?php echo htmlspecialchars($item['tamanho_escolhido']); ?> | 
                      <?php endif; ?>
                      Qtd: <?php echo $item['quantidade']; ?>
                    </div>
                  </div>
                  <div class="fw-bold">€<?php echo number_format($item['subtotal'], 2, ',', '.'); ?></div>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="summary-item">
              <span>Subtotal</span>
              <span>€<?php echo number_format($subtotal, 2, ',', '.'); ?></span>
            </div>
            <div class="summary-item">
              <span>Envio</span>
              <span class="<?php echo $taxa_envio == 0 ? 'text-success fw-bold' : ''; ?>">
                <?php echo $taxa_envio == 0 ? 'Grátis' : '€' . number_format($taxa_envio, 2, ',', '.'); ?>
              </span>
            </div>

            <div class="summary-item summary-total">
              <span>Total</span>
              <span>€<?php echo number_format($total, 2, ',', '.'); ?></span>
            </div>

            <button type="button" class="btn btn-accent w-100 mt-4" onclick="submitCheckout()">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
              </svg>
              Finalizar Compra
            </button>
          </div>
        </div>  
      </div>
    </div>
  </section>

 <?php include 'includes/footer.php'; ?>
 
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function selectPayment(element) {
      document.querySelectorAll('.payment-method').forEach(method => {
        method.classList.remove('selected');
        method.querySelector('input[type="radio"]').checked = false;
      });
      element.classList.add('selected');
      element.querySelector('input[type="radio"]').checked = true;
    }

    function submitCheckout() {
      const form = document.getElementById('checkoutForm');
      
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }
      
      const termsCheckbox = document.getElementById('terms');
      if (!termsCheckbox.checked) {
        alert('Por favor, aceite os Termos e Condições para continuar.');
        termsCheckbox.focus();
        return;
      }
      
      const btn = event.target;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>A processar...';
      
      form.submit();
    }
  </script>
</body>
</html>
  

    