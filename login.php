<?php
session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: perfil.php'); 
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Coimbra Running Club - Eventos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    
    if (!form) {
        console.error('Formulário não encontrado!');
        return;
    }
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Limpar alertas
        document.getElementById('errorAlert').style.display = 'none';
        document.getElementById('successAlert').style.display = 'none';
        
        // Validação básica
        if (!this.checkValidity()) {
            e.stopPropagation();
            this.classList.add('was-validated');
            return;
        }

        const formData = new FormData();
        formData.append('email', document.getElementById('email').value);
        formData.append('password', document.getElementById('password').value);
        
        
        // Enviar para o servidor
        fetch('ajax/processar_login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mostrar sucesso
                document.getElementById('successAlert').style.display = 'block';
                
                // Redirecionar
                setTimeout(() => {
                    window.location.href = data.redirect || 'index.php';
                }, 1000);
            } else {
                // Mostrar erro
                document.getElementById('errorAlert').textContent = data.error;
                document.getElementById('errorAlert').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            document.getElementById('errorAlert').textContent = 'Erro ao processar pedido';
            document.getElementById('errorAlert').style.display = 'block';
        });
    });
});

// Funções para login social (placeholder)
function loginWithGoogle() {
    alert('Login com Google ainda não implementado');
}

function loginWithFacebook() {
    alert('Login com Facebook ainda não implementado');
}
</script>

<style>
    
  .bg-secondary {
    background-color:#0F172A !important;
  } 
    
.login-container {
        min-height: calc(100vh - 56px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 0;
    }
.login-card {
        background-color:#420e76;
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 2.5rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        max-width: 400px;
        width: 100%;
    }
    .divisoria {
        text-align: center;
        margin: 1.5rem 0;
        position: relative;
    }
    .alertas {
        display: none;
    }

    .alertas-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
        padding: 0.75rem 1.25rem;
        border-radius: 5px;
        margin-bottom: 1rem;
    }
    .alertas-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        padding: 0.75rem 1.25rem;
        border-radius: 5px;
        margin-bottom: 1rem;
    }
    .btn-login {
        background-color: #420e76;
        border: 2px solid #FFFFFF;
        padding: 0.75rem;
        font-weight: bold;
        transition: background-color 0.3s;
    }
     .btn-login:hover {
        background-color: #7207dd;
    }
    .btn-social {
        flex: 1;
        padding: 0.75rem;
        border: 1px solid #dee2e6;
        transition: all 0.3s;
    }
    
    .btn-social:hover {
        background-color: #7207dd;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .social-login {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
</style>

</head>
<body class="bg-secondary">

<?php include 'includes/nav.php'; ?>

<div class="login-container">
    <div class="login-card">
        <div class="login-header text-white">
            <h2>Bem-vindo de volta!</h2>
            <p>Faça login para aceder à sua conta</p>
        </div>
        
        <div class="alertas alertas-danger" id="errorAlert" role="alert">
            Email ou password incorretos!
        </div>
        
        <div class="alertas alertas-success" id="successAlert" role="alert">
            Login efetuado com sucesso!
        </div>
        
        <form id="loginForm">
            <div class="mb-3">
                <label for="email" class="form-label text-white">Email</label>
                <input type="email" class="form-control" id="email" placeholder="exemplo@email.com" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label text-white">Password</label>
                <input type="password" class="form-control" id="password" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-login w-100 text-white">Entrar</button>
        </form>
        
        <div class="divisoria text-white">
            <span>--------------ou--------------</span>
        </div>
        
        <div class="social-login">
            <button type="button" class="btn btn-social" onclick="loginWithGoogle()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48">
                    <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
                    <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
                    <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
                    <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
                </svg>
            </button>
            <button type="button" class="btn btn-social" onclick="loginWithFacebook()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#1877F2" viewBox="0 0 16 16">
                    <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/>
                </svg>
            </button>
        </div>
        
        <p class="text-center text-white mt-3 mb-0">
            Não tem conta? <a href="registo.php" class="text-decoration-none" style="font-weight: bold; color: #ccaaee;">Registe-se aqui</a>
        </p>
    </div>

</div>

<?php include 'includes/footer.php'; ?>

 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

