<!doctype html>
<html lang="pt-BR" data-bs-theme="dark">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RazorHub - Profile</title>

    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@300;400;600&display=swap"
        rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous" />
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

    <main>
        <form class="container mt-4">
            <section class="profile-section">
                <!-- ================================ Dados públicos -->
                <h1 class="h2-title mb-4">
                    DADOS <span>PÚBLICOS</span>
                </h1>

                <div class="mb-3">
                    <fieldset disabled>
                        <label for="tipoUsuario" class="form-label">Tipo de Usuário</label>
                        <select id="tipoUsuario" class="form-select">
                            <option value="cliente" selected>Cliente</option>
                            <option value="funcionario">Funcionário</option>
                            <option value="chefe">Chefe</option>
                        </select>
                    </fieldset>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Nome de Usuário</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Kiratsuji">
                    <div id="usernameHelp" class="form-text">Esse nome que aparecerá para nossos funcionários.</div>
                    <div class="mb-2 mt-2 form-check">
                        <input type="checkbox" class="form-check-input" id="usernameCheck">
                        <label class="form-check-label" for="usernameCheck">Utilizar seu nome e sobrenome</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="tel" class="form-control" id="telefone" name="telefone" placeholder="(31) 9 1234-5678">
                    <div id="telefoneHelp" class="form-text">Seu contato </div>
                </div>
            </section>

            <!-- ================================ Dados pessoais -->
            <section class="profile-section">
                <h1 class="h2-title mb-4">
                    DADOS <span>PESSOAIS</span>
                </h1>

                <div class="mb-3">
                    <fieldset disabled>
                        <label for="status" class="form-label">Status de Cadastro</label>
                        <select id="status" class="form-select" >
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                            <option value="pendente" selected>Pendente</option>
                        </select>
                    </fieldset>
                </div>
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="nome" name="nome" aria-describedby="emailHelp"
                        placeholder="Isaac">
                </div>
                <div class="mb-3">
                    <label for="sobrenome" class="form-label">Sobrenome</label>
                    <input type="text" class="form-control" id="sobrenome" name="sobrenome" placeholder="Lacerdag">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Endereço de e-mail</label>
                    <input type="email" class="form-control" id="email" name="email"
                        placeholder="isaac.lacerdag@example.com">
                    <div id="emailHelp" class="form-text">Fique tranquilo, não compartilhamos seu e-mail com ninguém.
                    </div>
                </div>
            </section>

            <!-- ================================ Zona de Perigo -->
            <section class="profile-section">
                <h1 class="h2-title mb-4">
                    ZONA DE <span>PERIGO</span>
                </h1>

                <div class="mb-3">
                    <label for="senhaAtual" class="form-label">Senha Atual</label>
                    <input type="password" class="form-control" id="senhaAtual" name="senhaAtual">
                </div>
                <div class="mb-3">
                    <label for="novaSenha" class="form-label">Nova Senha</label>
                    <input type="password" class="form-control" id="novaSenha" name="novaSenha">
                </div>
                <div class="mb-3">
                    <label for="confirmarNovaSenha" class="form-label">Confirmar Nova Senha</label>
                    <input type="password" class="form-control" id="confirmarNovaSenha" name="confirmarNovaSenha">
                </div>
                <button type="submit" class="btn btn-gold">Confirmar</button>
            </section>
        </form>
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
    <script src="../js/profile.js"></script>
</body>

</html>