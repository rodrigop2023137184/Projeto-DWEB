<style>
    .bg-primary {
    background-color: #420e76 !important;
  }
    .navbar-nav .nav-link {
    padding: 8px 16px;
    margin: 0 4px;
    transition: background-color 0.3s;
}

.navbar-nav .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
}

.navbar-nav .nav-link.active {
    border-bottom: 2px solid white;
}
</style>

<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-sm bg-primary navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-light" href="index.php">CRC</a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
            <div class="navbar-nav">
                <div class="nav-item">
                    <a class="nav-link <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?> fw-bold text-light" href="index.php">Home</a>
                </div>
                <div class="nav-item">
                    <a class="nav-link <?php echo ($currentPage == 'eventos.php') ? 'active' : ''; ?> fw-bold text-light" href="eventos.php">Eventos</a>
                </div>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage == 'merch.php') ? 'active' : ''; ?> fw-bold text-light" href="merch.php">Merch</a>
                </li>
                <div class="nav-item">
                    <a class="nav-link <?php echo ($currentPage == 'aboutus.php') ? 'active' : ''; ?> fw-bold text-light" href="aboutus.php">About Us</a>
                </div>
            </div>
        </div>
        
        <div class="navbar-nav ms-auto">
             <a class="nav-link text-light position-relative" href="carrinho.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-cart3" viewBox="0 0 16 16">
                   <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </svg>
               </a>
            <a class="nav-link text-light <?php echo ($currentPage == 'login.php') ? 'active' : ''; ?>" href="login.php" role="button">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                    <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
                </svg>  
            </a>
        </div>
    </div>
</nav>