<?php
    require_once("config/database.php");

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $nome = $_SESSION['nome'];
    // Inicial para o avatar
    $inicial = mb_strtoupper(mb_substr($nome, 0, 1));
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RazorHub - Início</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@300;400;600&display=swap"
        rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="icon" type="image/png" href="assets/images/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="assets/images/favicon/favicon.svg" />
    <link rel="shortcut icon" href="assets/images/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="RazorHub" />
    <link rel="manifest" href="assets/images/favicon/site.webmanifest" />
</head>
<body class="has-sidebar">

    <!-- ── Navbar ── -->
    <header class="rh-navbar">
        <div class="d-flex align-items-center gap-2">
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="Abrir menu">
                <i class="bi bi-list"></i>
            </button>
            <a class="navbar-brand" href="inicio.php">RazorHub</a>
        </div>

        <a href="profile.php" class="rh-user">
            <div class="rh-avatar">
                <?= htmlspecialchars($inicial) ?>
            </div>
            <span class="rh-user-name"><?= htmlspecialchars($nome) ?></span>
        </a>
    </header>

    <!-- Overlay para fechar sidebar no mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ── Sidebar ── -->
    <aside class="rh-sidebar" id="sidebar">
        <nav>
            <a href="inicio.php" class="nav-link active">
                <i class="bi bi-house"></i> Início
            </a>
            <a href="profile.php" class="nav-link">
                <i class="bi bi-person"></i> Perfil
            </a>
            <a href="agendamentos.php" class="nav-link">
                <i class="bi bi-calendar3"></i> Agendamentos
            </a>
        </nav>

        <div class="rh-sidebar-footer">
            <a href="logout.php">
                <i class="bi bi-box-arrow-left"></i> Sair
            </a>
        </div>
    </aside>

    <!-- ── Conteúdo ── -->
    <main class="rh-wrapper">
        <a href="#" class="btn btn-gold">
            <i class="bi bi-plus-lg me-1"></i> Novo Agendamento
        </a>
    </main>

    <!-- ── Footer ── -->
    <footer class="py-4">
        <div class="container d-flex flex-wrap justify-content-between align-items-center">
            <span class="footer-brand">RazorHub</span>
            <p class="footer-copy mb-0">
                Todos os direitos reservados &copy; 2026 RazorHub
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YUe2LzesAfRqEkBRFTkMoPNmQhkGIl+e+0gDsAk51nV+oHnTBbjZqJLB"
        crossorigin="anonymous"></script>

    <script>
        // Toggle sidebar no mobile
        const toggle   = document.getElementById('sidebarToggle');
        const sidebar  = document.getElementById('sidebar');
        const overlay  = document.getElementById('sidebarOverlay');

        function openSidebar()  { sidebar.classList.add('open');  overlay.classList.add('open'); }
        function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('open'); }

        toggle.addEventListener('click', () => sidebar.classList.contains('open') ? closeSidebar() : openSidebar());
        overlay.addEventListener('click', closeSidebar);
    </script>
</body>
</html>