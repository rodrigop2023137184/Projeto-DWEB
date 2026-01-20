<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Coimbra Running Club - Eventos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script>

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado');
    
    const form = document.getElementById('registerForm');
    console.log('Formulário encontrado:', form);
    
    if (!form) {
        console.error('Formulário não encontrado!');
        return;
    }
    
    form.addEventListener('submit', function(e) {
        console.log('Submit capturado!');
        e.preventDefault();
        
        // Limpar alertas
        document.getElementById('errorAlert').style.display = 'none';
        document.getElementById('successAlert').style.display = 'none';
        
        // Validação
        if (!this.checkValidity()) {
            console.log('Formulário inválido');
            e.stopPropagation();
            this.classList.add('was-validated');
            return;
        }
        
        console.log('Formulário válido, a enviar...');
        
        // Verificar passwords
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        if (password !== confirmPassword) {
            console.log('Passwords não coincidem');
            document.getElementById('confirmPassword').classList.add('is-invalid');
            document.getElementById('errorMessage').textContent = 'As passwords não coincidem';
            document.getElementById('errorAlert').style.display = 'block';
            return;
        }
        
        // IMPORTANTE: Criar formData aqui
        const formData = new FormData(this);
        
        console.log('A enviar para processar_registo.php...');
        
        fetch('ajax/processar_registo.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Status:', response.status);
            console.log('Resposta recebida:', response);
            return response.text(); // Mudei para .text() para ver o erro
        })
        .then(text => {
            console.log('Resposta do servidor:', text); // VER ISTO NA CONSOLA
            try {
                const data = JSON.parse(text);
                console.log('Dados recebidos:', data);
                if (data.success) {
                    document.getElementById('successAlert').style.display = 'block';
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    document.getElementById('errorMessage').textContent = data.error;
                    document.getElementById('errorAlert').style.display = 'block';
                }
            } catch(e) {
                console.error('Erro ao fazer parse do JSON:', e);
                console.error('Texto recebido:', text);
                document.getElementById('errorMessage').textContent = 'Erro no servidor. Verifica a consola.';
                document.getElementById('errorAlert').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Erro no fetch:', error);
            document.getElementById('errorMessage').textContent = 'Erro ao processar pedido';
            document.getElementById('errorAlert').style.display = 'block';
        });
    });
});


</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<style>
    
 .bg-secondary {
    background-color:#0F172A !important;
  } 
 .registo-container {
        min-height: calc(100vh - 56px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 0;
    }
    .registo-card {
        background-color: #420e76;
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 2.5rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        max-width: 500px;
        width: 100%;
    }
    .btn-register {
        background-color: #420e76;
        border: 2px solid #FFFFFF;
        padding: 0.75rem;
        font-weight: bold;
        transition: background-color 0.3s;
    }
    
    .btn-register:hover {
        background-color: #5a1299;
    }
    
    .form-control:focus {
        border-color: #420e76;
        box-shadow: 0 0 0 0.2rem rgba(66, 14, 118, 0.25);
    }

    .alertas {
    display: none;
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 5px;
   }

   .alertas-danger {
    background-color: #dc3545;
    color: white;
   }

   .alertas-success {
    background-color: #28a745;
    color: white;
   }

    
</style>
</head>
<body class="bg-secondary">
<?php include 'includes/nav.php'; ?>    

<div class="registo-container">
    <div class="registo-card">
        <div class="text-white mb-4 text-center">
            <h2>Criar Conta</h2>
            <p>Preencha os seus dados para se registar</p>
        </div>
        
        <div class="alertas alertas-danger" id="errorAlert" role="alert">
            <strong>Erro!</strong> <span id="errorMessage"></span>
        </div>
        
        <div class="alertas alertas-success" id="successAlert" role="alert">
            <strong>Sucesso!</strong> Conta criada com sucesso! A redirecionar...
        </div>
        
        <form id="registerForm" novalidate>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="firstName" class="text-light">Primeiro Nome</label>
                    <input type="text" class="form-control" id="firstName" name="firstName" placeholder="João" required>
                    <div class="invalid-feedback">
                        Por favor insira o seu primeiro nome.
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="lastName" class="text-light">Último Nome</label>
                    <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Silva" required>
                    <div class="invalid-feedback text-light">
                        Por favor insira o seu último nome.
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="email" class="text-light">Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="exemplo@email.com" required>
                <div class="invalid-feedback text-light">
                    Por favor insira um email válido.
                </div>
            </div>
            
            <div class="mb-3">
                <label for="phone" class="text-white">Telemóvel</label>
                <input type="tel" class="form-control" id="phone" name="phone" placeholder="+351 900 000 000">
                <div class="form-text text-light">Formato: +351 900 000 000</div>
            </div>
            
            <div class="mb-3">
                <label for="birthdate" class="text-white">Data de Nascimento</label>
                <input type="date" class="form-control" id="birthdate" name="birthdate" required>
                <div class="invalid-feedback text-light">
                    Por favor insira a sua data de nascimento.
                </div>
            </div>
            
            <div class="mb-3">
                <label for="gender" class="text-white">Género</label>
                <select class="form-select" id="gender" name="gender">
                    <option value="" selected>Selecione...</option>
                    <option value="masculino">Masculino</option>
                    <option value="feminino">Feminino</option>
                    <option value="outro">Outro</option>
                    <option value="prefiro-nao-dizer">Prefiro não dizer</option>
                </select>
            </div>
            
            <div class="mb-3 position-relative">
                <label for="password" class="text-white">Password </label>
                <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                <div class="password-strength" id="passwordStrength"></div>
                <div class="text-white">Mínimo 8 caracteres, inclua letras, números e símbolos</div>
                <div class="invalid-feedback text-white">
                    A password deve ter pelo menos 8 caracteres.
                </div>
            </div>
            
            <div class="mb-3 position-relative">
                <label for="confirmPassword" class="text-white">Confirmar Password</label>
                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="••••••••" required>
                <div class="invalid-feedback text-white" id="confirmPasswordFeedback">
                    As passwords não coincidem.
                </div>
            </div>            
            
            <button type="submit" class="btn btn-primary btn-register w-100 text-white">Criar Conta</button>
        </form>
        
        <p class="text-center mt-3 mb-0 text-white">
            Já tem conta? <a href="login.php" class="text-decoration-none" style="color: #ccaaee; font-weight: bold;">Faça login aqui</a>
        </p>
    </div>
</div>

    <?php include 'includes/footer.php'; ?>

 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>