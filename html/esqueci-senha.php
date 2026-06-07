<?php
    require_once("config/database.php");

    // Página protegida — exige sessão ativa
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $error   = null;
    $success = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $senha_atual     = $_POST['senhaAtual']         ?? '';
        $nova_senha      = $_POST['novaSenha']          ?? '';
        $confirmar_senha = $_POST['confirmarNovaSenha'] ?? '';

        // Validações
        if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_senha)) {
            $error = "Preencha todos os campos.";
        } elseif (strlen($nova_senha) < 8) {
            $error = "A nova senha deve ter no mínimo 8 caracteres.";
        } elseif ($nova_senha !== $confirmar_senha) {
            $error = "As novas senhas não coincidem.";
        } else {
            // Busca o hash atual do usuário logado
            $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id = :id AND status = 'ativo' LIMIT 1");
            $stmt->execute(['id' => $_SESSION['user_id']]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($senha_atual, $user['password'])) {
                $error = "Senha atual incorreta.";
            } else {
                // Atualiza a senha — updated_at é atualizado automaticamente pelo banco
                $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $upd  = $pdo->prepare("UPDATE usuarios SET password = :password WHERE id = :id");
                $upd->execute([
                    'password' => $hash,
                    'id'       => $_SESSION['user_id'],
                ]);
                $success = true;
            }
        }
    }
?>

<!doctype html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RazorHub - Alterar Senha</title>
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
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="#">RazorHub</a>
            </div>
        </nav>
    </header>

    <main style="margin-bottom: auto;">
        <div class="container mt-4" style="max-width: 480px;">

            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    Senha alterada com sucesso!
                    <a href="dashboard.php" class="alert-link ms-1">Voltar ao início</a>
                </div>
            <?php else: ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" novalidate>

                    <div class="mb-3">
                        <label for="senhaAtual" class="form-label">Senha Atual</label>
                        <div class="input-group">
                            <input
                                type="password"
                                class="form-control"
                                id="senhaAtual"
                                name="senhaAtual"
                                autocomplete="current-password"
                                required
                            />
                            <button type="button" class="btn btn-outline-secondary toggle-senha" data-target="senhaAtual" tabindex="-1">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="novaSenha" class="form-label">Nova Senha</label>
                        <div class="input-group">
                            <input
                                type="password"
                                class="form-control"
                                id="novaSenha"
                                name="novaSenha"
                                placeholder="Mín. 8 caracteres"
                                autocomplete="new-password"
                                required
                            />
                            <button type="button" class="btn btn-outline-secondary toggle-senha" data-target="novaSenha" tabindex="-1">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="confirmarNovaSenha" class="form-label">Confirmar Nova Senha</label>
                        <div class="input-group">
                            <input
                                type="password"
                                class="form-control"
                                id="confirmarNovaSenha"
                                name="confirmarNovaSenha"
                                placeholder="Repita a nova senha"
                                autocomplete="new-password"
                                required
                            />
                            <button type="button" class="btn btn-outline-secondary toggle-senha" data-target="confirmarNovaSenha" tabindex="-1">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-gold">Confirmar</button>
                    <a href="dashboard.php" class="btn btn-outline-secondary ms-2">Cancelar</a>

                </form>

            <?php endif; ?>

        </div>
    </main>

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
        crossorigin="anonymous">
    </script>
    <script src="../js/profile.js"></script>
    <script>
        document.querySelectorAll('.toggle-senha').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = document.getElementById(btn.dataset.target);
                const icon  = btn.querySelector('i');
                const mostrar = input.type === 'password';

                input.type      = mostrar ? 'text' : 'password';
                icon.className  = mostrar ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        });
    </script>
</body>
</html>