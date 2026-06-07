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
    <link rel="stylesheet" href="../css/style.css" />
    <link rel="icon" type="image/png" href="assets/images/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="assets/images/favicon/favicon.svg" />
    <link rel="shortcut icon" href="assets/images/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="RazorHub" />
    <link rel="manifest" href="assets/images/favicon/site.webmanifest" />

    <style>
        /* ── Layout base ── */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── Navbar ── */
        .rh-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 64px;
            z-index: 1040;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255,255,255,.07);
            background: var(--bs-body-bg, #111);
        }

        .rh-navbar .navbar-brand {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.6rem;
            letter-spacing: .08em;
        }

        /* Botão hamburguer (mobile) */
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            color: inherit;
            font-size: 1.4rem;
            padding: 0 .5rem 0 0;
            cursor: pointer;
        }

        /* Avatar + nome */
        .rh-user {
            display: flex;
            align-items: center;
            gap: .65rem;
            text-decoration: none;
            color: inherit;
        }

        .rh-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #c9a84c;          /* gold */
            color: #111;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 2px solid rgba(201,168,76,.4);
        }

        .rh-user-name {
            font-family: 'Barlow', sans-serif;
            font-weight: 600;
            font-size: .9rem;
            white-space: nowrap;
        }

        /* ── Sidebar ── */
        .rh-sidebar {
            position: fixed;
            top: 64px;
            left: 0;
            bottom: 0;
            width: 220px;
            background: var(--bs-body-bg, #111);
            border-right: 1px solid rgba(255,255,255,.07);
            padding: 1.5rem 0;
            z-index: 1030;
            display: flex;
            flex-direction: column;
            transition: transform .25s ease;
        }

        .rh-sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .7rem 1.5rem;
            font-family: 'Barlow', sans-serif;
            font-weight: 600;
            font-size: .9rem;
            color: rgba(255,255,255,.55);
            border-left: 3px solid transparent;
            transition: color .2s, border-color .2s, background .2s;
            text-decoration: none;
        }

        .rh-sidebar .nav-link i {
            font-size: 1.1rem;
        }

        .rh-sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,.05);
        }

        .rh-sidebar .nav-link.active {
            color: #c9a84c;
            border-left-color: #c9a84c;
            background: rgba(201,168,76,.07);
        }

        /* Logout no rodapé da sidebar */
        .rh-sidebar-footer {
            margin-top: auto;
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,.07);
        }

        .rh-sidebar-footer a {
            display: flex;
            align-items: center;
            gap: .65rem;
            font-family: 'Barlow', sans-serif;
            font-weight: 600;
            font-size: .85rem;
            color: rgba(255,255,255,.4);
            text-decoration: none;
            transition: color .2s;
        }

        .rh-sidebar-footer a:hover { color: #e05c5c; }

        /* ── Conteúdo principal ── */
        .rh-wrapper {
            margin-top: 64px;
            margin-left: 220px;
            flex: 1;
            padding: 2rem;
            transition: margin-left .25s ease;
        }

        /* ── Footer ── */
        footer {
            margin-left: 220px;
            border-top: 1px solid rgba(255,255,255,.07);
            transition: margin-left .25s ease;
        }

        /* ── Overlay mobile ── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 1025;
        }

        /* ── Responsivo ── */
        @media (max-width: 768px) {
            .sidebar-toggle { display: block; }

            .rh-sidebar {
                transform: translateX(-100%);
            }

            .rh-sidebar.open {
                transform: translateX(0);
            }

            .sidebar-overlay.open { display: block; }

            .rh-wrapper,
            footer { margin-left: 0; }

            .rh-user-name { display: none; }
        }
    </style>
</head>
<body>

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
        <a href="novo-agendamento.php" class="btn btn-gold">
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