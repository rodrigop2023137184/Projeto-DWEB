<?php
session_start();

// Redirecionar se não estiver logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'includes/db_connect.php';

$id_utilizador = $_SESSION['id_utilizador'];

//itens do carrinho
$stmt = $conn->prepare("
    SELECT 
        c.id_carrinho,
        c.quantidade,
        c.tamanho_escolhido,
        c.preco_unitario,
        p.id_produto,
        p.nome,
        p.imagem,
        p.stock_total,
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

// Calcular totais
$subtotal = 0;
foreach ($itens as $item) {
    $subtotal += $item['subtotal'];
}

$taxa_envio = $subtotal > 0 ? ($subtotal >= 50 ? 0 : 5.00) : 0;
$total = $subtotal + $taxa_envio;
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Coimbra Running Club - Carrinho</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script>
// Função para atualizar quantidade
function updateQuantity(idCarrinho, novaQuantidade, elemento) {
    if (novaQuantidade < 1) return;
    
    const formData = new FormData();
    formData.append('id_carrinho', idCarrinho);
    formData.append('quantidade', novaQuantidade);
    
    fetch('atualizar_quantidade.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualizar subtotal do item
            const subtotalElement = elemento.querySelector('.item-subtotal');
            if (subtotalElement && data.subtotal) {
                subtotalElement.textContent = '€' + data.subtotal.toFixed(2).replace('.', ',');
            }
            
            // Atualizar quantidade no HTML
            const qtyElement = elemento.querySelector('.mx-3.fw-bold');
            if (qtyElement) {
                qtyElement.textContent = novaQuantidade;
            }
            
            // Atualizar total geral
            if (data.total_carrinho) {
                const totalElement = document.querySelector('.cart-total');
                if (totalElement) {
                    totalElement.textContent = '€' + data.total_carrinho.toFixed(2).replace('.', ',');
                }
            }
            
            // Atualizar contador do header
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            }
            
            // Refresh página para atualizar envio e totais
            location.reload();
        } else {
            alert(data.error || 'Erro ao atualizar quantidade');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao atualizar quantidade');
    });
}

// Função para remover item
function removeFromCart(idCarrinho, elemento) {
    if (!confirm('Deseja remover este item do carrinho?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('id_carrinho', idCarrinho);
    
    fetch('remover_do_carrinho.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Animação de remoção
            elemento.style.transition = 'opacity 0.3s, transform 0.3s';
            elemento.style.opacity = '0';
            elemento.style.transform = 'translateX(50px)';
            
            setTimeout(() => {
                // Recarregar página
                location.reload();
            }, 300);
        } else {
            alert(data.error || 'Erro ao remover item');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao remover item do carrinho');
    });
}
</script>
  <style>
    .bg-secondary {
      background-color: #0F172A !important;
    }
    
    .border-primary { 
      border-color: #FF00C8 !important;
    }

    .text-primary {
      color: #FF00C8 !important;
    }

    .btn-primary {
      background-color: #420e76 !important;
      border-color: #420e76 !important;
      transition: all 0.3s ease;
    }

    .btn-primary:hover {
      background-color: #5a1199 !important;
      border-color: #5a1199 !important;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(66, 14, 118, 0.4);
    }

    .btn-outline-primary {
      color: #420e76 !important;
      border-color: #420e76 !important;
    }

    .btn-outline-primary:hover {
      background-color: #420e76 !important;
      border-color: #420e76 !important;
      color: white !important;
    }

    .cart-item {
      transition: all 0.3s ease;
      border-left: 3px solid transparent;
      border-bottom: 1px solid #e9ecef;
    }

    .cart-item:hover {
      border-left-color: #FF00C8;
      background-color: rgba(66, 14, 118, 0.05);
    }

    .quantity-btn {
      width: 35px;
      height: 35px;
      padding: 0;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 2px solid #420e76;
      background: white;
      color: #420e76;
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .quantity-btn:hover {
      background-color: #420e76;
      color: white;
    }

    .quantity-btn:disabled {
      opacity: 0.4;
      cursor: not-allowed;
    }

    .empty-cart {
      min-height: 400px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .product-img-cart {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 8px;
    }

    .remove-btn {
      color: #dc3545;
      cursor: pointer;
      transition: all 0.3s;
    }

    .remove-btn:hover {
      color: #bb2d3b;
      transform: scale(1.2);
    }

    .summary-card {
      position: sticky;
      top: 20px;
    }
  </style>
</head>
<body class="bg-light">
  
<?php include 'includes/nav.php'; ?>

  <section class="bg-primary py-4 text-white">
    <div class="container">
      <div class="d-flex align-items-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-cart3 me-3" viewBox="0 0 16 16">
          <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
        </svg>
        <div>
          <h1 class="h3 fw-bold mb-0">O Meu Carrinho</h1>
          <p class="mb-0 small opacity-75">Revê os teus produtos antes de finalizar a compra</p>
        </div>
      </div>
    </div>
  </section>

  <section class="py-5">
    <div class="container">
      
      <?php if (empty($itens)): ?>
        <div class="empty-cart text-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="#ccc" class="bi bi-cart-x mb-3" viewBox="0 0 16 16">
            <path d="M7.354 5.646a.5.5 0 1 0-.708.708L7.793 7.5 6.646 8.646a.5.5 0 1 0 .708.708L8.5 8.207l1.146 1.147a.5.5 0 0 0 .708-.708L9.207 7.5l1.147-1.146a.5.5 0 0 0-.708-.708L8.5 6.793 7.354 5.646z"/>
            <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1H.5zm3.915 10L3.102 4h10.796l-1.313 7h-8.17zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
          </svg>
          <h4 class="text-muted mb-3">O teu carrinho está vazio</h4>
          <p class="text-muted mb-4">Adiciona produtos incríveis ao teu carrinho!</p>
          <a href="merch.php" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-shop me-2" viewBox="0 0 16 16">
              <path d="M2.97 1.35A1 1 0 0 1 3.73 1h8.54a1 1 0 0 1 .76.35l2.609 3.044A1.5 1.5 0 0 1 16 5.37v.255a2.375 2.375 0 0 1-4.25 1.458A2.371 2.371 0 0 1 9.875 8 2.37 2.37 0 0 1 8 7.083 2.37 2.37 0 0 1 6.125 8a2.37 2.37 0 0 1-1.875-.917A2.375 2.375 0 0 1 0 5.625V5.37a1.5 1.5 0 0 1 .361-.976l2.61-3.045zm1.78 4.275a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 1 0 2.75 0V5.37a.5.5 0 0 0-.12-.325L12.27 2H3.73L1.12 5.045A.5.5 0 0 0 1 5.37v.255a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0zM1.5 8.5A.5.5 0 0 1 2 9v6h1v-5a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v5h6V9a.5.5 0 0 1 1 0v6h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1V9a.5.5 0 0 1 .5-.5zM4 15h3v-5H4v5zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3zm3 0h-2v3h2v-3z"/>
            </svg>
            Continuar a Comprar
          </a>
        </div>
      <?php else: ?>
        <div class="row">
          <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
              <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold" style="color: #420e76;">
                  Produtos no Carrinho (<?php echo count($itens); ?>)
                </h5>
              </div>
              <div class="card-body p-0">
                <?php foreach ($itens as $item): ?>
                  <div class="cart-item p-3" data-id="<?php echo $item['id_carrinho']; ?>">
                    <div class="row align-items-center">
                      <!-- Imagem -->
                      <div class="col-md-2 col-3">
                        <img src="<?php echo htmlspecialchars($item['imagem']); ?>" 
                             alt="<?php echo htmlspecialchars($item['nome']); ?>" 
                             class="product-img-cart">
                      </div>

                      <div class="col-md-4 col-9">
                        <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($item['nome']); ?></h6>
                        <?php if (!empty($item['tamanho_escolhido'])): ?>
                          <small class="text-muted">Tamanho: <strong><?php echo htmlspecialchars($item['tamanho_escolhido']); ?></strong></small>
                        <?php endif; ?>
                        <div class="mt-1">
                          <small class="text-muted">€<?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?> / unidade</small>
                        </div>
                      </div>
             
                      <div class="col-md-3 col-6 mt-3 mt-md-0">
                        <div class="d-flex align-items-center justify-content-center">
                          <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id_carrinho']; ?>, <?php echo $item['quantidade'] - 1; ?>, this.closest('.cart-item'))" <?php echo $item['quantidade'] <= 1 ? 'disabled' : ''; ?>>
                            -
                          </button>
                          <span class="mx-3 fw-bold"><?php echo $item['quantidade']; ?></span>
                          <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id_carrinho']; ?>, <?php echo $item['quantidade'] + 1; ?>, this.closest('.cart-item'))" <?php echo $item['quantidade'] >= $item['stock_total'] || $item['quantidade'] >= 10 ? 'disabled' : ''; ?>>
                            +
                          </button>
                        </div>
                        <small class="text-muted d-block text-center mt-1">Stock: <?php echo $item['stock_total']; ?></small>
                      </div>
        
                      <div class="col-md-2 col-4 mt-3 mt-md-0 text-center">
                        <div class="fw-bold mb-2 item-subtotal">€<?php echo number_format($item['subtotal'], 2, ',', '.'); ?></div>
                        <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(<?php echo $item['id_carrinho']; ?>, this.closest('.cart-item'))">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                            <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
            
            <a href="merch.php" class="btn btn-outline-primary">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left me-2" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
              </svg>
              Continuar a Comprar
            </a>
          </div>
     
          <div class="col-lg-4">
            <div class="card shadow-sm summary-card">
              <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fw-bold">Resumo da Encomenda</h5>
              </div>
              <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                  <span>Subtotal:</span>
                  <span class="fw-bold">€<?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span>Envio:</span>
                  <span class="fw-bold <?php echo $taxa_envio == 0 ? 'text-success' : ''; ?>">
                    <?php echo $taxa_envio == 0 ? 'GRÁTIS' : '€' . number_format($taxa_envio, 2, ',', '.'); ?>
                  </span>
                </div>
                <?php if ($subtotal < 50 && $subtotal > 0): ?>
                  <div class="alert alert-info py-2 px-3 small">
                    Faltam €<?php echo number_format(50 - $subtotal, 2, ',', '.'); ?> para envio grátis!
                  </div>
                <?php endif; ?>
                <hr>
                <div class="d-flex justify-content-between mb-3">
                  <span class="fw-bold fs-5">Total:</span>
                  <span class="fw-bold fs-5 text-primary cart-total">€<?php echo number_format($total, 2, ',', '.'); ?></span>
                </div>
                <a href="dadosdeenvio.php" class="btn btn-primary w-100 py-2 mb-2">
                  Finalizar Compra
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right ms-2" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                  </svg>
                </a>
                <div class="text-center">
                  <small class="text-muted">Pagamento seguro garantido</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
      
    </div>
  </section>

<?php include 'includes/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/carrinho.js"></script>
</body>
</html>