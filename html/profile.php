<?php
require_once("config/database.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = null;
$error   = null;

// Carrega dados atuais do usuário
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── Atualizar dados do perfil ──────────────────────────────
    if ($action === 'update_profile') {
        $nome      = trim($_POST['nome']      ?? '');
        $sobrenome = trim($_POST['sobrenome'] ?? '');
        $email     = trim($_POST['email']     ?? '');
        $telefone  = trim($_POST['telefone']  ?? '');
        $username  = trim($_POST['username']  ?? '') ?: null;

        if (empty($nome) || empty($sobrenome) || empty($email)) {
            $error = "Nome, sobrenome e e-mail são obrigatórios.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Informe um e-mail válido.";
        } else {
            try {
                $upd = $pdo->prepare("
                    UPDATE usuarios
                    SET nome = :nome, sobrenome = :sobrenome,
                        email = :email, telefone = :telefone, username = :username
                    WHERE id = :id
                ");
                $upd->execute([
                    'nome'      => $nome,
                    'sobrenome' => $sobrenome,
                    'email'     => $email,
                    'telefone'  => $telefone ?: null,
                    'username'  => $username,
                    'id'        => $user_id,
                ]);

                // Atualiza nome na sessão
                $_SESSION['nome'] = $nome;

                // Recarrega dados atualizados
                $stmt->execute(['id' => $user_id]);
                $user    = $stmt->fetch();
                $success = "Perfil atualizado com sucesso!";

            } catch (PDOException $e) {
                $error = ($e->getCode() === '23000')
                    ? "Este e-mail ou nome de usuário já está em uso."
                    : "Erro ao atualizar perfil. Tente novamente.";
            }
        }

    // ── Alterar senha ──────────────────────────────────────────
    } elseif ($action === 'change_password') {
        $senha_atual     = $_POST['senhaAtual']         ?? '';
        $nova_senha      = $_POST['novaSenha']          ?? '';
        $confirmar_senha = $_POST['confirmarNovaSenha'] ?? '';

        if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_senha)) {
            $error = "Preencha todos os campos de senha.";
        } elseif (strlen($nova_senha) < 8) {
            $error = "A nova senha deve ter no mínimo 8 caracteres.";
        } elseif ($nova_senha !== $confirmar_senha) {
            $error = "As novas senhas não coincidem.";
        } elseif (!password_verify($senha_atual, $user['password'])) {
            $error = "Senha atual incorreta.";
        } else {
            $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $upd  = $pdo->prepare("UPDATE usuarios SET password = :password WHERE id = :id");
            $upd->execute(['password' => $hash, 'id' => $user_id]);
            $success = "Senha alterada com sucesso!";
        }
    }
}

$nome    = $_SESSION['nome'];
$inicial = mb_strtoupper(mb_substr($nome, 0, 1));
?>
<!doctype html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RazorHub - Perfil</title>
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
        body { display: flex; flex-direction: column; min-height: 100vh; }

        /* ── Navbar ── */
        .rh-navbar {
            position: fixed; top: 0; left: 0; right: 0; height: 64px;
            z-index: 1040; display: flex; align-items: center;
            padding: 0 1.5rem; justify-content: space-between;
            border-bottom: 1px solid rgba(255,255,255,.07);
            background: var(--bs-body-bg, #111);
        }
        .rh-navbar .navbar-brand {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.6rem; letter-spacing: .08em;
        }
        .sidebar-toggle {
            display: none; background: none; border: none;
            color: inherit; font-size: 1.4rem;
            padding: 0 .5rem 0 0; cursor: pointer;
        }
        .rh-user {
            display: flex; align-items: center; gap: .65rem;
            text-decoration: none; color: inherit;
        }
        .rh-avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: #c9a84c; color: #111;
            font-family: 'Bebas Neue', sans-serif; font-size: 1.1rem;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; border: 2px solid rgba(201,168,76,.4);
        }
        .rh-user-name {
            font-family: 'Barlow', sans-serif;
            font-weight: 600; font-size: .9rem; white-space: nowrap;
        }

        /* ── Sidebar ── */
        .rh-sidebar {
            position: fixed; top: 64px; left: 0; bottom: 0; width: 220px;
            background: var(--bs-body-bg, #111);
            border-right: 1px solid rgba(255,255,255,.07);
            padding: 1.5rem 0; z-index: 1030;
            display: flex; flex-direction: column;
            transition: transform .25s ease;
        }
        .rh-sidebar .nav-link {
            display: flex; align-items: center; gap: .75rem;
            padding: .7rem 1.5rem;
            font-family: 'Barlow', sans-serif; font-weight: 600; font-size: .9rem;
            color: rgba(255,255,255,.55);
            border-left: 3px solid transparent;
            transition: color .2s, border-color .2s, background .2s;
            text-decoration: none;
        }
        .rh-sidebar .nav-link i { font-size: 1.1rem; }
        .rh-sidebar .nav-link:hover { color: #fff; background: rgba(255,255,255,.05); }
        .rh-sidebar .nav-link.active {
            color: #c9a84c; border-left-color: #c9a84c;
            background: rgba(201,168,76,.07);
        }
        .rh-sidebar-footer {
            margin-top: auto; padding: 1rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,.07);
        }
        .rh-sidebar-footer a {
            display: flex; align-items: center; gap: .65rem;
            font-family: 'Barlow', sans-serif; font-weight: 600; font-size: .85rem;
            color: rgba(255,255,255,.4); text-decoration: none; transition: color .2s;
        }
        .rh-sidebar-footer a:hover { color: #e05c5c; }

        /* ── Wrapper + footer ── */
        .rh-wrapper {
            margin-top: 64px; margin-left: 220px; flex: 1; padding: 2rem;
            transition: margin-left .25s ease;
        }
        footer { margin-left: 220px; border-top: 1px solid rgba(255,255,255,.07); transition: margin-left .25s ease; }

        /* ── Overlay mobile ── */
        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.5); z-index: 1025;
        }

        /* ── Seções do perfil ── */
        .profile-section {
            max-width: 560px;
            padding-bottom: 2rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(255,255,255,.07);
        }
        .profile-section:last-child { border-bottom: none; }

        /* ── Responsivo ── */
        @media (max-width: 768px) {
            .sidebar-toggle { display: block; }
            .rh-sidebar { transform: translateX(-100%); }
            .rh-sidebar.open { transform: translateX(0); }
            .sidebar-overlay.open { display: block; }
            .rh-wrapper, footer { margin-left: 0; }
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
            <div class="rh-avatar"><?= htmlspecialchars($inicial) ?></div>
            <span class="rh-user-name"><?= htmlspecialchars($nome) ?></span>
        </a>
    </header>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ── Sidebar ── -->
    <aside class="rh-sidebar" id="sidebar">
        <nav>
            <a href="inicio.php" class="nav-link">
                <i class="bi bi-house"></i> Início
            </a>
            <a href="profile.php" class="nav-link active">
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

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="max-width:560px">
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="max-width:560px">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- ════ Form: dados públicos + pessoais ════ -->
        <form method="POST" action="" novalidate>
            <input type="hidden" name="action" value="update_profile" />

            <!-- Dados públicos -->
            <section class="profile-section">
                <h1 class="h2-title mb-4">DADOS <span>PÚBLICOS</span></h1>

                <div class="mb-3">
                    <fieldset disabled>
                        <label for="tipoUsuario" class="form-label">Tipo de Usuário</label>
                        <select id="tipoUsuario" class="form-select">
                            <option value="cliente"    <?= $user['tipo']==='cliente'    ? 'selected':'' ?>>Cliente</option>
                            <option value="funcionario"<?= $user['tipo']==='funcionario'? 'selected':'' ?>>Funcionário</option>
                            <option value="chefe"      <?= $user['tipo']==='chefe'      ? 'selected':'' ?>>Chefe</option>
                        </select>
                    </fieldset>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Nome de Usuário</label>
                    <input type="text" class="form-control" id="username" name="username"
                        placeholder="Ex: joaosilva"
                        value="<?= htmlspecialchars($user['username'] ?? '') ?>" />
                    <div class="form-text">Esse nome aparecerá para nossos funcionários.</div>
                    <div class="mb-2 mt-2 form-check">
                        <input type="checkbox" class="form-check-input" id="usernameCheck" />
                        <label class="form-check-label" for="usernameCheck">
                            Utilizar meu nome e sobrenome
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="tel" class="form-control" id="telefone" name="telefone"
                        placeholder="(31) 9 1234-5678"
                        value="<?= htmlspecialchars($user['telefone'] ?? '') ?>" />
                </div>
            </section>

            <!-- Dados pessoais -->
            <section class="profile-section">
                <h1 class="h2-title mb-4">DADOS <span>PESSOAIS</span></h1>

                <div class="mb-3">
                    <fieldset disabled>
                        <label for="status" class="form-label">Status de Cadastro</label>
                        <select id="status" class="form-select">
                            <option value="ativo"    <?= $user['status']==='ativo'    ? 'selected':'' ?>>Ativo</option>
                            <option value="inativo"  <?= $user['status']==='inativo'  ? 'selected':'' ?>>Inativo</option>
                            <option value="pendente" <?= $user['status']==='pendente' ? 'selected':'' ?>>Pendente</option>
                        </select>
                    </fieldset>
                </div>

                <div class="mb-3">
                    <label for="nome" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="nome" name="nome" required
                        value="<?= htmlspecialchars($user['nome']) ?>" />
                </div>

                <div class="mb-3">
                    <label for="sobrenome" class="form-label">Sobrenome</label>
                    <input type="text" class="form-control" id="sobrenome" name="sobrenome" required
                        value="<?= htmlspecialchars($user['sobrenome']) ?>" />
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" required
                        value="<?= htmlspecialchars($user['email']) ?>" />
                    <div class="form-text">Não compartilhamos seu e-mail com ninguém.</div>
                </div>

                <button type="submit" class="btn btn-gold">Salvar alterações</button>
            </section>
        </form>

        <!-- ════ Form: alterar senha ════ -->
        <form method="POST" action="" novalidate>
            <input type="hidden" name="action" value="change_password" />

            <section class="profile-section">
                <h1 class="h2-title mb-4">ZONA DE <span>PERIGO</span></h1>

                <div class="mb-3">
                    <label for="senhaAtual" class="form-label">Senha Atual</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="senhaAtual" name="senhaAtual"
                            autocomplete="current-password" />
                        <button type="button" class="btn btn-outline-secondary toggle-senha"
                            data-target="senhaAtual" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="novaSenha" class="form-label">Nova Senha</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="novaSenha" name="novaSenha"
                            placeholder="Mín. 8 caracteres" autocomplete="new-password" />
                        <button type="button" class="btn btn-outline-secondary toggle-senha"
                            data-target="novaSenha" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="confirmarNovaSenha" class="form-label">Confirmar Nova Senha</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="confirmarNovaSenha"
                            name="confirmarNovaSenha" placeholder="Repita a nova senha"
                            autocomplete="new-password" />
                        <button type="button" class="btn btn-outline-secondary toggle-senha"
                            data-target="confirmarNovaSenha" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-danger">Alterar senha</button>
            </section>
        </form>

    </main>

    <!-- ── Footer ── -->
    <footer class="py-4">
        <div class="container d-flex flex-wrap justify-content-between align-items-center">
            <span class="footer-brand">RazorHub</span>
            <p class="footer-copy mb-0">Todos os direitos reservados &copy; 2026 RazorHub</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YUe2LzesAfRqEkBRFTkMoPNmQhkGIl+e+0gDsAk51nV+oHnTBBW8OxkIGIVDCk3"
        crossorigin="anonymous"></script>

    <script>
        // ── Sidebar toggle (mobile) ──────────────────────────────
        const toggle  = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        const openSidebar  = () => { sidebar.classList.add('open');    overlay.classList.add('open'); };
        const closeSidebar = () => { sidebar.classList.remove('open'); overlay.classList.remove('open'); };

        toggle.addEventListener('click', () => sidebar.classList.contains('open') ? closeSidebar() : openSidebar());
        overlay.addEventListener('click', closeSidebar);

        // ── Mostrar/ocultar senha ────────────────────────────────
        document.querySelectorAll('.toggle-senha').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = document.getElementById(btn.dataset.target);
                const icon  = btn.querySelector('i');
                const show  = input.type === 'password';
                input.type     = show ? 'text' : 'password';
                icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        });

        // ── Checkbox "usar nome e sobrenome" ─────────────────────
        const check     = document.getElementById('usernameCheck');
        const usernameInput = document.getElementById('username');
        const nomeInput     = document.getElementById('nome');
        const sobrenomeInput = document.getElementById('sobrenome');

        check.addEventListener('change', () => {
            if (check.checked) {
                const full = (nomeInput.value.trim() + ' ' + sobrenomeInput.value.trim()).trim();
                usernameInput.value    = full;
                usernameInput.readOnly = true;
            } else {
                usernameInput.readOnly = false;
            }
        });

        // Atualiza username em tempo real enquanto checkbox está marcado
        [nomeInput, sobrenomeInput].forEach(el => {
            el.addEventListener('input', () => {
                if (check.checked) {
                    usernameInput.value = (nomeInput.value.trim() + ' ' + sobrenomeInput.value.trim()).trim();
                }
            });
        });
    </script>
</body>
</html>