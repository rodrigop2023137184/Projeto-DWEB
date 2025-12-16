<?php
$id_produto = isset($_GET['id']) ? intval($_GET['id']) : 1;

require_once 'includes/db_connect.php';

$stmt = $conn->prepare("SELECT * FROM produto WHERE id_produto = ? AND status = 'ativo'");
$stmt->bind_param("i", $id_produto);
$stmt->execute();
$result = $stmt->get_result();

// Se o produto não existir, redirecionar
if ($result->num_rows === 0) {
    header('Location: Merch.php');
    exit;
}

$produto = $result->fetch_assoc();

// Definir tamanhos disponíveis baseado no tipo
$tamanhos_disponiveis = [];
if ($produto['tem_tamanhos']) {
    if ($produto['tipo_tamanho'] == 'roupa') {
        $tamanhos_disponiveis = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
    } elseif ($produto['tipo_tamanho'] == 'calcado') {
        $tamanhos_disponiveis = ['35-36', '37-38', '39-40', '41-42', '43-44', '45-46'];
    } elseif ($produto['tipo_tamanho'] == 'numerico') {
        $tamanhos_disponiveis = ['250ml', '500ml', '750ml', '1L'];
    }
}

// Verificar stock
$em_stock = $produto['stock_total'] > 0;
$stock_baixo = $produto['stock_total'] > 0 && $produto['stock_total'] <= 10;
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
   <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($produto['nome']); ?> - CRC Merch</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="js/carrinho.js"></script> 
  <style>
:root {
            --primary: #420e76;
            --secondary: #0F172A;
            --accent: #FF00C8;
        }

        body {
            background-color: var(--secondary);
        }

        .bg-primary {
            background-color: var(--primary) !important;
        }

        .bg-secondary {
            background-color: var(--secondary) !important;
        }

        .text-accent {
            color: var(--accent) !important;
        }

        .btn-accent {
            background-color: var(--accent);
            color: white;
            border: none;
            transition: all 0.3s;
        }

        .btn-accent:hover {
            background-color: #d400a8;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 0, 200, 0.3);
        }

        .Galeria-de-Imagens {
            position: relative;
        }

        .main-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
            border-radius: 12px;
            cursor: zoom-in;
            transition: transform 0.3s;
        }

        .main-image:hover {
            transform: scale(1.02);
        }

        .size-selector {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .size-option {
            width: 50px;
            height: 50px;
            border: 2px solid #444;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            background-color: transparent;
            color: white;
            font-weight: bold;
        }

        .size-option:hover {
            border-color: var(--accent);
            transform: translateY(-2px);
        }

        .size-option.selected {
            background-color: var(--accent);
            border-color: var(--accent);
        }

        .size-option.unavailable {
            opacity: 0.3;
            cursor: not-allowed;
            text-decoration: line-through;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background-color: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .nav-tabs .nav-link {
            color: white;
            border: none;
            border-bottom: 2px solid transparent;
        }

        .nav-tabs .nav-link:hover {
            border-bottom-color: var(--accent);
        }

        .nav-tabs .nav-link.active {
            background-color: transparent;
            border-bottom-color: var(--accent);
            color: var(--accent);
        }

        .trust-signal {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            border-left: 3px solid var(--accent);
        }

        .cart-notification {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: var(--accent);
            color: white;
            padding: 20px 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
            display: none;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .qty-btn {
            width: 35px;
            height: 35px;
            border: 2px solid var(--accent);
            background-color: transparent;
            color: var(--accent);
            border-radius: 8px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .qty-btn:hover {
            background-color: var(--accent);
            color: white;
        }

        .qty-display {
            width: 50px;
            text-align: center;
            font-size: 1.2rem;
            font-weight: bold;
            color: white;
        }

        .price-section {
            background-color: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .hover-link:hover {
            color: var(--accent) !important;
        }

        .stock-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .stock-status.in-stock {
            background-color: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }

        .stock-status.low-stock {
            background-color: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
        }
</style>

<script>
let selectedSize = <?php echo $produto['tem_tamanhos'] ? 'null' : '""'; ?>;
let quantity = 1;

const productData = {
    id: <?php echo $produto['id_produto']; ?>,
    name: '<?php echo addslashes($produto['nome']); ?>',
    price: <?php echo $produto['preco']; ?>,
    image: '<?php echo addslashes($produto['imagem']); ?>',
    temTamanhos: <?php echo $produto['tem_tamanhos'] ? 'true' : 'false'; ?>,
    maxStock: <?php echo $produto['stock_total']; ?>
};

document.addEventListener('DOMContentLoaded', function() {
    // Atualizar contador do carrinho ao carregar
    if (typeof updateCartCount === 'function') {
        updateCartCount();
    }
    
    <?php if($produto['tem_tamanhos']): ?>
    // Seleção de tamanho
    const sizeButtons = document.querySelectorAll('.size-option:not(.unavailable)');
    sizeButtons.forEach(button => {
        button.addEventListener('click', function() {
            sizeButtons.forEach(btn => btn.classList.remove('selected'));
            this.classList.add('selected');
            selectedSize = this.dataset.size;
        });
    });
    <?php endif; ?>
    
    // Controle de quantidade
    const decreaseBtn = document.getElementById('decrease');
    const increaseBtn = document.getElementById('increase');
    const quantityDisplay = document.getElementById('quantity');
    
    if (decreaseBtn) {
        decreaseBtn.addEventListener('click', function() {
            if (quantity > 1) {
                quantity--;
                quantityDisplay.textContent = quantity;
            }
        });
    }
    
    if (increaseBtn) {
        increaseBtn.addEventListener('click', function() {
            if (quantity < productData.maxStock && quantity < 10) {
                quantity++;
                quantityDisplay.textContent = quantity;
            }
        });
    }
    
    // Adicionar ao carrinho
    const addToCartBtn = document.getElementById('addToCart');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            <?php if($produto['tem_tamanhos']): ?>
            // Validar se tamanho foi selecionado
            if (!selectedSize) {
                showAlert('Por favor, selecione um tamanho!', 'warning');
                const sizeSelector = document.querySelector('.size-selector');
                if (sizeSelector) {
                    sizeSelector.style.border = '2px solid #fbbf24';
                    setTimeout(() => {
                        sizeSelector.style.border = 'none';
                    }, 2000);
                }
                return;
            }
            <?php endif; ?>
            
            // Desabilitar botão durante o processamento
            const btn = document.getElementById('addToCart');
            const originalHTML = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>A adicionar...';
            
            // Preparar dados
            const formData = new FormData();
            formData.append('id_produto', productData.id);
            formData.append('quantidade', quantity);
            formData.append('tamanho', selectedSize || '');
            
            // Enviar para o servidor
            fetch('adicionar_ao_carrinho.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Sucesso
                    showSuccessAnimation();
                    showAlert(data.message, 'success');
                    
                    // Atualizar contador do carrinho
                    if (typeof updateCartCount === 'function') {
                        updateCartCount();
                    }
                    
                    // Resetar quantidade
                    quantity = 1;
                    quantityDisplay.textContent = quantity;
                } else {
                    showAlert(data.error || 'Erro ao adicionar ao carrinho', 'danger');
                    
                    // Se precisa fazer login, redirecionar
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 2000);
                    }
                }
                
                // Reativar botão
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            })
            .catch(error => {
                console.error('Erro:', error);
                showAlert('Erro ao adicionar ao carrinho. Tente novamente.', 'danger');
                
                // Reativar botão
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            });
        });
    }
});

// Função para mostrar alerta
function showAlert(message, type = 'info') {
    const existingAlerts = document.querySelectorAll('.custom-alert');
    existingAlerts.forEach(alert => alert.remove());
    
    const alert = document.createElement('div');
    alert.className = `custom-alert alert-${type}`;
    
    let icon = '';
    let bgColor = '';
    
    switch(type) {
        case 'success':
            icon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
            </svg>`;
            bgColor = '#22c55e';
            break;
        case 'warning':
            icon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
            </svg>`;
            bgColor = '#fbbf24';
            break;
        case 'danger':
            icon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
            </svg>`;
            bgColor = '#ef4444';
            break;
        default:
            bgColor = '#3b82f6';
    }
    
    alert.innerHTML = `
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="flex-shrink: 0;">${icon}</div>
            <div style="flex-grow: 1; font-weight: 500;">${message}</div>
            <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: white; cursor: pointer; font-size: 1.5rem; line-height: 1; padding: 0;">×</button>
        </div>
    `;
    
    alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: ${bgColor};
        color: white;
        padding: 16px 20px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        z-index: 9999;
        min-width: 300px;
        max-width: 500px;
        animation: slideInRight 0.3s ease-out;
    `;
    
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.style.animation = 'slideOutRight 0.3s ease-in';
        setTimeout(() => alert.remove(), 300);
    }, 4000);
}

// Animação de sucesso no botão
function showSuccessAnimation() {
    const btn = document.getElementById('addToCart');
    const originalHTML = btn.innerHTML;
    
    btn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="me-2" viewBox="0 0 16 16">
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
        </svg>
        Adicionado!
    `;
    
    btn.style.backgroundColor = '#22c55e';
    btn.disabled = true;
    
    setTimeout(() => {
        btn.innerHTML = originalHTML;
        btn.style.backgroundColor = '';
        btn.disabled = false;
    }, 2000);
}

// Adicionar animações CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(400px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(400px); opacity: 0; }
    }
    .size-selector { transition: border 0.3s ease; }
`;
document.head.appendChild(style);
</script>
</head>

<body class="bg-secondary">

<?php include 'includes/nav.php'; ?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="home.php" class="text-accent text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="merch.php" class="text-accent text-decoration-none">Merch</a></li>
            <li class="breadcrumb-item active text-light" aria-current="page"><?php echo htmlspecialchars($produto['nome']); ?></li>
        </ol>
    </nav>
</div>

<section class="py-5">
    <div class="container">
        <div class="row gx-5">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="Galeria-de-Imagens">
                    <img src="<?php echo htmlspecialchars($produto['imagem']); ?>" 
                         alt="<?php echo htmlspecialchars($produto['nome']); ?>" 
                         class="main-image" 
                         id="mainImage">
                </div>
            </div>

            <div class="col-lg-6">
                <div class="mb-3">
                    <?php if(!$em_stock): ?>
                        <span class="stock-status" style="background-color: rgba(239, 68, 68, 0.2); color: #ef4444;">
                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
                                <circle cx="8" cy="8" r="8"/>
                            </svg>
                            Esgotado
                        </span>
                    <?php elseif($stock_baixo): ?>
                        <span class="stock-status low-stock">
                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
                                <circle cx="8" cy="8" r="8"/>
                            </svg>
                            Últimas unidades (<?php echo $produto['stock_total']; ?> restantes)
                        </span>
                    <?php else: ?>
                        <span class="stock-status in-stock">
                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
                                <circle cx="8" cy="8" r="8"/>
                            </svg>
                            Em Stock
                        </span>
                    <?php endif; ?>
                </div>

                <h1 class="display-5 fw-bolder text-light mb-3"><?php echo htmlspecialchars($produto['nome']); ?></h1>
                
                <div class="price-section">
                    <div class="fs-2 fw-bold text-accent mb-2"><?php echo number_format($produto['preco'], 2); ?>€</div>
                    <p class="text-light mb-0 small">IVA incluído | Envio grátis acima de 50€</p>
                </div>

                <p class="lead text-light mb-4">
                    <?php echo nl2br(htmlspecialchars($produto['descricao'])); ?>
                </p>

                <?php if($produto['material'] || $produto['peso']): ?>
                <div class="mb-4">
                    <?php if($produto['material']): ?>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white" viewBox="0 0 16 16">
                                <path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
                            </svg>
                        </div>
                        <div class="text-light">
                            <strong>Material:</strong> <?php echo htmlspecialchars($produto['material']); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($produto['peso']): ?>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-fill" viewBox="0 0 16 16">
                              <path fill-rule="evenodd" d="M15.528 2.973a.75.75 0 0 1 .472.696v8.662a.75.75 0 0 1-.472.696l-7.25 2.9a.75.75 0 0 1-.557 0l-7.25-2.9A.75.75 0 0 1 0 12.331V3.669a.75.75 0 0 1 .471-.696L7.443.184l.004-.001.274-.11a.75.75 0 0 1 .558 0l.274.11.004.001zm-1.374.527L8 5.962 1.846 3.5 1 3.839v.4l6.5 2.6v7.922l.5.2.5-.2V6.84l6.5-2.6v-.4l-.846-.339Z"/>
                            </svg>
                        </div>
                        <div class="text-light">
                            <strong>Peso/Capacidade:</strong> <?php echo htmlspecialchars($produto['peso']); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if($em_stock): ?>
                    <?php if($produto['tem_tamanhos'] && !empty($tamanhos_disponiveis)): ?>
                    <div class="mb-4">
                        <label class="form-label text-light fw-bold mb-3">Tamanho</label>
                        <div class="size-selector">
                            <?php foreach($tamanhos_disponiveis as $tamanho): ?>
                                <button class="size-option" data-size="<?php echo $tamanho; ?>"><?php echo $tamanho; ?></button>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if($produto['tipo_tamanho'] == 'roupa'): ?>
                        <a href="#" class="text-accent text-decoration-none small mt-2 d-inline-block" data-bs-toggle="modal" data-bs-target="#sizeGuideModal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                            </svg>
                            Guia de tamanhos
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div>
                            <label class="form-label text-light fw-bold mb-2">Quantidade</label>
                            <div class="quantity-selector">
                                <button class="qty-btn" id="decrease">−</button>
                                <span class="qty-display" id="quantity">1</span>
                                <button class="qty-btn" id="increase">+</button>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-accent btn-lg w-100 mb-3" id="addToCart">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                            <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                        </svg>
                        Adicionar ao Carrinho
                    </button>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <strong>Produto Esgotado</strong><br>
                        Este produto está temporariamente indisponível.
                    </div>
                <?php endif; ?>

                <div class="mt-4">
                    <div class="trust-signal mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="text-accent" viewBox="0 0 16 16">
                            <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                        </svg>
                        <span class="text-light"><strong>Envio Grátis</strong> em compras acima de 50€</span>
                    </div>
                    <div class="trust-signal mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="text-accent" viewBox="0 0 16 16">
                            <path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.354 1.146zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4H2.5z"/>
                        </svg>
                        <span class="text-light"><strong>Devolução fácil</strong> em até 30 dias</span>
                    </div>
                    <div class="trust-signal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="text-accent" viewBox="0 0 16 16">
                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v4a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5.5a.5.5 0 0 0-1 0v4a.5.5 0 0 0 1 0V6zm2.5-.5a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5z"/>
                            <path d="M14 3a1 1 0 0 1-1-1H3a1 1 0 0 1-1 1H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3h-1zm-1 10H3V4h10v9z"/>
                        </svg>
                        <span class="text-light"><strong>Pagamento Seguro</strong> com SSL</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if($produto['tipo_tamanho'] == 'roupa'): ?>
<div class="modal fade" id="sizeGuideModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-secondary text-light">
            <div class="modal-header">
                <h5 class="modal-title">Guia de Tamanhos</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered text-light">
                    <thead>
                        <tr>
                            <th>Tamanho</th>
                            <th>Busto (cm)</th>
                            <th>Cintura (cm)</th>
                            <th>Quadril (cm)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>XS</td><td>81-86</td><td>61-66</td><td>86-91</td></tr>
                        <tr><td>S</td><td>86-91</td><td>66-71</td><td>91-96</td></tr>
                        <tr><td>M</td><td>91-96</td><td>71-76</td><td>96-101</td></tr>
                        <tr><td>L</td><td>96-101</td><td>76-81</td><td>101-106</td></tr>
                        <tr><td>XL</td><td>101-106</td><td>81-86</td><td>106-111</td></tr>
                        <tr><td>XXL</td><td>106-116</td><td>86-96</td><td>111-121</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
