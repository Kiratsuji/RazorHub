<?php
    require_once("config/database.php");

    $error   = null;
    $success = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome            = trim($_POST['nome']            ?? '');
        $sobrenome       = trim($_POST['sobrenome']       ?? '');
        $email           = trim($_POST['email']           ?? '');
        $telefone        = trim($_POST['telefone']        ?? '');
        $senha           = $_POST['senha']                ?? '';
        $confirmar_senha = $_POST['confirmar_senha']      ?? '';
        $termos          = isset($_POST['termos']);

        // Validações do lado do servidor
        if (empty($nome) || empty($sobrenome) || empty($email) || empty($senha)) {
            $error = "Preencha todos os campos obrigatórios.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Informe um e-mail válido.";
        } elseif (strlen($senha) < 8) {
            $error = "A senha deve ter no mínimo 8 caracteres.";
        } elseif ($senha !== $confirmar_senha) {
            $error = "As senhas não coincidem.";
        } elseif (!$termos) {
            $error = "Você precisa aceitar os termos de uso.";
        } else {
            $password = password_hash($senha, PASSWORD_DEFAULT);

            try {
                $sql = "INSERT INTO usuarios
                            (nome, sobrenome, email, telefone, password, tipo, nivel_acesso)
                        VALUES
                            (:nome, :sobrenome, :email, :telefone, :password, 'cliente', 1)";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'nome'      => $nome,
                    'sobrenome' => $sobrenome,
                    'email'     => $email,
                    'telefone'  => $telefone !== '' ? $telefone : null,
                    'password'  => $password,
                ]);

                $success = true;

            } catch (PDOException $e) {
                // SQLSTATE 23000 = violação de constraint única (e-mail duplicado)
                if ($e->getCode() === '23000') {
                    $error = "Este e-mail já está cadastrado.";
                } else {
                    $error = "Erro ao criar conta. Tente novamente mais tarde.";
                }
            }
        }
    }
?>

<!doctype html>
<html lang="pt-BR" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RazorHub - Criar Conta</title>
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

  <body class="page-register">
    <header>
      <nav class="navbar navbar-expand-lg">
        <div class="container">
          <a class="navbar-brand" href="index.php">RazorHub</a>
        </div>
      </nav>
    </header>

    <main>
      <div class="register-card">
        <p class="register-eyebrow mb-2">Novo cliente</p>
        <h1 class="register-title mb-3">CRIAR CONTA</h1>
        <hr class="register-divider mb-4" />

        <?php if ($success): ?>
          <!-- Feedback de sucesso -->
          <div class="alert alert-success" role="alert">
            Conta criada com sucesso! <a href="login.html" class="alert-link">Faça login</a>.
          </div>
        <?php else: ?>

          <?php if ($error): ?>
            <!-- Feedback de erro -->
            <div class="alert alert-danger" role="alert">
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

          <form action="" method="POST" novalidate>

            <div class="row g-3 mb-3">
              <div class="col-6">
                <label for="nome" class="form-label">Nome</label>
                <input
                  type="text"
                  id="nome"
                  name="nome"
                  class="form-control"
                  placeholder="João"
                  value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"
                  autocomplete="given-name"
                  required
                />
              </div>
              <div class="col-6">
                <label for="sobrenome" class="form-label">Sobrenome</label>
                <input
                  type="text"
                  id="sobrenome"
                  name="sobrenome"
                  class="form-control"
                  placeholder="Silva"
                  value="<?= htmlspecialchars($_POST['sobrenome'] ?? '') ?>"
                  autocomplete="family-name"
                  required
                />
              </div>
            </div>

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
              <label for="telefone" class="form-label">Telefone</label>
              <input
                type="tel"
                id="telefone"
                name="telefone"
                class="form-control"
                placeholder="(31) 9 0000-0000"
                value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>"
                autocomplete="tel"
              />
            </div>

            <div class="mb-3">
              <label for="senha" class="form-label">Senha</label>
              <input
                type="password"
                id="senha"
                name="senha"
                class="form-control"
                placeholder="Mín. 8 caracteres"
                autocomplete="new-password"
                required
              />
            </div>

            <div class="mb-4">
              <label for="confirmar-senha" class="form-label">Confirmar senha</label>
              <input
                type="password"
                id="confirmar-senha"
                name="confirmar_senha"
                class="form-control"
                placeholder="Repita a senha"
                autocomplete="new-password"
                required
              />
            </div>

            <div class="form-check mb-4">
              <input
                class="form-check-input"
                type="checkbox"
                id="termos"
                name="termos"
                <?= isset($_POST['termos']) ? 'checked' : '' ?>
                required
              />
              <label class="form-check-label" for="termos">
                Li e aceito os
                <a href="termos.html" class="rh-link">termos de uso</a>
                e a
                <a href="privacidade.html" class="rh-link">política de privacidade</a>
              </label>
            </div>

            <button type="submit" class="btn btn-gold">Criar conta</button>
          </form>

          <div class="divider-text">ou</div>

          <p class="text-center mb-0" style="font-size: 0.9rem; color: #7a776e">
            Já tem uma conta?
            <a href="login.html" class="rh-link ms-1">Fazer login</a>
          </p>

        <?php endif; ?>
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