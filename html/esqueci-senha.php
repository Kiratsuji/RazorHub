<?php
    require_once("config/database.php");

    $error   = null;
    $success = false;
    $email_submetido = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email           = trim($_POST['email'] ?? '');
        $nova_senha      = $_POST['novaSenha']          ?? '';
        $confirmar_senha = $_POST['confirmarNovaSenha'] ?? '';
        $email_submetido = $email;

        // Validações
        if (empty($email)) {
            $error = "Informe seu e-mail.";
        } elseif (empty($nova_senha) || empty($confirmar_senha)) {
            $error = "Preencha todos os campos de senha.";
        } elseif (strlen($nova_senha) < 8) {
            $error = "A nova senha deve ter no mínimo 8 caracteres.";
        } elseif ($nova_senha !== $confirmar_senha) {
            $error = "As novas senhas não coincidem.";
        } else {
            // Verifica se o e-mail existe no banco
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (!$user) {
                $error = "Nenhuma conta encontrada com este e-mail.";
            } else {
                // Atualiza a senha do usuário
                $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $upd  = $pdo->prepare("UPDATE usuarios SET password = :password WHERE email = :email");
                $upd->execute([
                    'password' => $hash,
                    'email'    => $email,
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
    <title>RazorHub - Esqueci minha senha</title>
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
<body class="page-login">
    <header>
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="index.php">RazorHub</a>
            </div>
        </nav>
    </header>

    <main>
        <div class="container" style="max-width: 480px; padding: 2rem 0;">
            <div class="login-card">
                <p class="login-eyebrow mb-2">Recuperar acesso</p>
                <h1 class="login-title mb-3">ESQUECI A SENHA</h1>
                <hr class="login-divider mb-4" />

                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Senha redefinida com sucesso!
                        <a href="login.php" class="alert-link ms-1">Clique aqui para fazer login</a>
                    </div>
                <?php else: ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-info mb-4" style="font-size: 0.9rem;">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Informe seu e-mail cadastrado e crie uma nova senha.
                    </div>

                    <form action="" method="POST" novalidate>

                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail cadastrado</label>
                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                placeholder="seu@email.com"
                                value="<?= htmlspecialchars($email_submetido) ?>"
                                autocomplete="email"
                                required
                            />
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
                            <div class="form-text" id="senhaFeedback"></div>
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

                        <button type="submit" class="btn btn-gold w-100">Redefinir Senha</button>
                    </form>

                    <div class="divider-text mt-4">ou</div>

                    <p class="text-center mb-0" style="font-size: 0.9rem; color: #7a776e">
                        <a href="login.php" class="rh-link">
                            <i class="bi bi-arrow-left me-1"></i>Voltar para o login
                        </a>
                    </p>

                <?php endif; ?>
            </div>
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
        integrity="sha384-YUe2LzesAfRqEkBRFTkMoPNmQhkGIl+e+0gDsAk51nV+oHnTBBW8OxkIGIVDCk3"
        crossorigin="anonymous">
    </script>
    
    <script>
        // Função para mostrar/ocultar senha
        document.querySelectorAll('.toggle-senha').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = document.getElementById(btn.dataset.target);
                const icon  = btn.querySelector('i');
                const mostrar = input.type === 'password';
                
                input.type      = mostrar ? 'text' : 'password';
                icon.className  = mostrar ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        });
        
        // Validação de senha em tempo real
        const novaSenha = document.getElementById('novaSenha');
        const senhaFeedback = document.getElementById('senhaFeedback');
        
        if (novaSenha && senhaFeedback) {
            novaSenha.addEventListener('input', function() {
                const senha = this.value;
                let feedback = '';
                let isValid = true;
                
                if (senha.length > 0 && senha.length < 8) {
                    feedback = '❌ A senha deve ter no mínimo 8 caracteres';
                    isValid = false;
                } else if (senha.length >= 8) {
                    if (!/[A-Z]/.test(senha)) {
                        feedback = '❌ A senha deve conter pelo menos uma letra maiúscula';
                        isValid = false;
                    } else if (!/[a-z]/.test(senha)) {
                        feedback = '❌ A senha deve conter pelo menos uma letra minúscula';
                        isValid = false;
                    } else if (!/[0-9]/.test(senha)) {
                        feedback = '❌ A senha deve conter pelo menos um número';
                        isValid = false;
                    } else {
                        feedback = '✅ Senha forte!';
                        isValid = true;
                    }
                }
                
                senhaFeedback.innerHTML = feedback;
                senhaFeedback.className = isValid && senha.length >= 8 ? 'form-text text-success' : 'form-text text-danger';
            });
        }
    </script>
</body>
</html>