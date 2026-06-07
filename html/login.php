<?php
    require_once("config/database.php");

    // Se já estiver logado, redireciona
    if (isset($_SESSION['user_id'])) {
        header("Location: dashboard.php");
        exit();
    }

    $error = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email  = trim($_POST['email']  ?? '');
        $senha  = $_POST['senha']       ?? '';
        $lembrar = isset($_POST['lembrar']);

        if (empty($email) || empty($senha)) {
            $error = "Preencha o e-mail e a senha.";
        } else {
            // Busca pelo e-mail (campo único e obrigatório no schema)
            $sql  = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($senha, $user['password'])) {

                // Bloqueia contas inativas ou pendentes
                if ($user['status'] !== 'ativo') {
                    $error = "Sua conta está " . $user['status'] . ". Entre em contato com o suporte.";
                } else {

                    // "Lembrar de mim": estende a sessão para 30 dias
                    if ($lembrar) {
                        session_set_cookie_params(60 * 60 * 24 * 30);
                        session_regenerate_id(true);
                    }

                    // Grava dados essenciais na sessão
                    $_SESSION['user_id']      = $user['id'];
                    $_SESSION['nome']         = $user['nome'];
                    $_SESSION['tipo']         = $user['tipo'];
                    $_SESSION['nivel_acesso'] = $user['nivel_acesso'];

                    // Atualiza ultimo_login no banco
                    $upd = $pdo->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = :id");
                    $upd->execute(['id' => $user['id']]);

                    header('Location: dashboard.php');
                    exit();
                }

            } else {
                // Mensagem genérica para não revelar se o e-mail existe
                $error = "E-mail ou senha inválidos.";
            }
        }
    }
?>

<!doctype html>
<html lang="pt-BR" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RazorHub - Login</title>
    <link
      href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@300;400;600&display=swap"
      rel="stylesheet"
    />
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
      crossorigin="anonymous"
    />
    <link href="../css/style.css" rel="stylesheet" />

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
      <div class="login-card">
        <p class="login-eyebrow mb-2">Área do cliente</p>
        <h1 class="login-title mb-3">ENTRAR</h1>
        <hr class="login-divider mb-4" />

        <?php if ($error): ?>
          <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <form action="" method="POST" novalidate>
          <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input
              type="email"
              id="email"
              name="email"
              class="form-control"
              placeholder="seu@email.com"
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
              autocomplete="email"
              required
            />
          </div>

          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label for="senha" class="form-label mb-0">Senha</label>
              <a href="esqueci-senha.html" class="rh-link" style="font-size: 0.78rem">
                Esqueci minha senha
              </a>
            </div>
            <input
              type="password"
              id="senha"
              name="senha"
              class="form-control"
              placeholder="••••••••"
              autocomplete="current-password"
              required
            />
          </div>

          <div class="form-check mb-4">
            <input
              class="form-check-input"
              type="checkbox"
              id="lembrar"
              name="lembrar"
              <?= isset($_POST['lembrar']) ? 'checked' : '' ?>
            />
            <label class="form-check-label" for="lembrar">Lembrar de mim</label>
          </div>

          <button type="submit" class="btn btn-gold">Entrar</button>
        </form>

        <div class="divider-text">ou</div>

        <p class="text-center mb-0" style="font-size: 0.9rem; color: #7a776e">
          Ainda não tem conta?
          <a href="register.php" class="rh-link ms-1">Registre-se grátis</a>
        </p>
      </div>
    </main>

    <footer class="py-4">
      <div class="container d-flex flex-wrap justify-content-between align-items-center">
        <span class="footer-brand">RazorHub</span>
        <p class="footer-copy mb-0">Todos os direitos reservados &copy; 2026 RazorHub</p>
      </div>
    </footer>

    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YUe2LzesAfRqEkBRFTkMoPNmQhkGIl+e+0gDsAk51nV+oHnTBBW8OxkIGIVDCk3"
      crossorigin="anonymous"
    ></script>
  </body>
</html>