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

// Função para validar telefone brasileiro
function validarTelefone($telefone) {
    // Remove caracteres não numéricos
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    
    // Verifica se tem 10 ou 11 dígitos (com ou sem 9º dígito)
    return strlen($telefone) === 10 || strlen($telefone) === 11;
}

// Função para formatar telefone automaticamente
function formatarTelefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    
    if (strlen($telefone) === 11) {
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
    } elseif (strlen($telefone) === 10) {
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
    }
    return $telefone;
}

// Função para gerar username automático
function gerarUsername($nome, $sobrenome, $pdo, $user_id = null) {
    $base = strtolower(trim($nome . '.' . $sobrenome));
    $base = preg_replace('/[^a-z0-9.]/', '', $base);
    $username = $base;
    $counter = 1;
    
    // Verifica se o username já existe (excluindo o usuário atual)
    $sql = "SELECT COUNT(*) FROM usuarios WHERE username = :username";
    $params = ['username' => $username];
    
    if ($user_id) {
        $sql .= " AND id != :user_id";
        $params['user_id'] = $user_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    while ($stmt->fetchColumn() > 0) {
        $username = $base . $counter;
        $params['username'] = $username;
        $stmt->execute($params);
        $counter++;
    }
    
    return $username;
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

        // Validações aprimoradas
        $errors = [];

        if (empty($nome)) {
            $errors[] = "O nome é obrigatório.";
        } elseif (strlen($nome) < 2) {
            $errors[] = "O nome deve ter pelo menos 2 caracteres.";
        }

        if (empty($sobrenome)) {
            $errors[] = "O sobrenome é obrigatório.";
        } elseif (strlen($sobrenome) < 2) {
            $errors[] = "O sobrenome deve ter pelo menos 2 caracteres.";
        }

        if (empty($email)) {
            $errors[] = "O e-mail é obrigatório.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Informe um e-mail válido.";
        }

        if (!empty($telefone) && !validarTelefone($telefone)) {
            $errors[] = "Informe um telefone válido (formato: (99) 99999-9999 ou (99) 9999-9999).";
        }

        // Limpa e formata o telefone para salvar no banco
        if (!empty($telefone)) {
            $telefone = preg_replace('/[^0-9]/', '', $telefone);
        } else {
            $telefone = null;
        }

        if (empty($errors)) {
            try {
                // Se username não foi preenchido, gera automaticamente
                if (empty($username)) {
                    $username = gerarUsername($nome, $sobrenome, $pdo, $user_id);
                }

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
                    'telefone'  => $telefone,
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
                if ($e->getCode() === '23000') {
                    if (strpos($e->getMessage(), 'email') !== false) {
                        $error = "Este e-mail já está em uso por outro usuário.";
                    } elseif (strpos($e->getMessage(), 'username') !== false) {
                        $error = "Este nome de usuário já está em uso. Tente outro.";
                    } else {
                        $error = "Já existe um registro com estas informações.";
                    }
                } else {
                    $error = "Erro ao atualizar perfil. Tente novamente.";
                }
            }
        } else {
            $error = implode(" ", $errors);
        }

    // ── Alterar senha ──────────────────────────────────────────
    } elseif ($action === 'change_password') {
        $senha_atual     = $_POST['senhaAtual']         ?? '';
        $nova_senha      = $_POST['novaSenha']          ?? '';
        $confirmar_senha = $_POST['confirmarNovaSenha'] ?? '';

        $errors = [];

        if (empty($senha_atual)) {
            $errors[] = "A senha atual é obrigatória.";
        }

        if (empty($nova_senha)) {
            $errors[] = "A nova senha é obrigatória.";
        } elseif (strlen($nova_senha) < 8) {
            $errors[] = "A nova senha deve ter no mínimo 8 caracteres.";
        } elseif (!preg_match('/[A-Z]/', $nova_senha)) {
            $errors[] = "A nova senha deve conter pelo menos uma letra maiúscula.";
        } elseif (!preg_match('/[a-z]/', $nova_senha)) {
            $errors[] = "A nova senha deve conter pelo menos uma letra minúscula.";
        } elseif (!preg_match('/[0-9]/', $nova_senha)) {
            $errors[] = "A nova senha deve conter pelo menos um número.";
        }

        if (empty($confirmar_senha)) {
            $errors[] = "A confirmação de senha é obrigatória.";
        } elseif ($nova_senha !== $confirmar_senha) {
            $errors[] = "As novas senhas não coincidem.";
        }

        if (empty($errors)) {
            if (!password_verify($senha_atual, $user['password'])) {
                $error = "Senha atual incorreta.";
            } else {
                $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $upd  = $pdo->prepare("UPDATE usuarios SET password = :password WHERE id = :id");
                $upd->execute(['password' => $hash, 'id' => $user_id]);
                $success = "Senha alterada com sucesso!";
            }
        } else {
            $error = implode(" ", $errors);
        }
    }
}

$nome    = $_SESSION['nome'];
$inicial = mb_strtoupper(mb_substr($nome, 0, 1));

// Formata o telefone para exibição
$telefone_formatado = !empty($user['telefone']) ? formatarTelefone($user['telefone']) : '';
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
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="max-width:560px">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
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
                    <div class="form-text">Este nome aparecerá para nossos funcionários. Deixe em branco para gerar automaticamente.</div>
                    <div class="mb-2 mt-2 form-check">
                        <input type="checkbox" class="form-check-input" id="usernameCheck" />
                        <label class="form-check-label" for="usernameCheck">
                            Utilizar meu nome e sobrenome como nome de usuário
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="tel" class="form-control" id="telefone" name="telefone"
                        placeholder="(99) 99999-9999"
                        value="<?= htmlspecialchars($telefone_formatado) ?>" />
                    <div class="form-text">Formato: (DDD) 99999-9999 ou (DDD) 9999-9999</div>
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
                        placeholder="Seu nome"
                        value="<?= htmlspecialchars($user['nome']) ?>" />
                    <div class="form-text">Mínimo de 2 caracteres.</div>
                </div>

                <div class="mb-3">
                    <label for="sobrenome" class="form-label">Sobrenome</label>
                    <input type="text" class="form-control" id="sobrenome" name="sobrenome" required
                        placeholder="Seu sobrenome"
                        value="<?= htmlspecialchars($user['sobrenome']) ?>" />
                    <div class="form-text">Mínimo de 2 caracteres.</div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" required
                        placeholder="seu@email.com"
                        value="<?= htmlspecialchars($user['email']) ?>" />
                    <div class="form-text">Não compartilhamos seu e-mail com ninguém.</div>
                </div>

                <button type="submit" class="btn btn-gold">
                    <i class="bi bi-save me-1"></i> Salvar alterações
                </button>
            </section>
        </form>

        <!-- ════ Form: alterar senha ════ -->
        <form method="POST" action="" novalidate>
            <input type="hidden" name="action" value="change_password" />

            <section class="profile-section">
                <h1 class="h2-title mb-4">ZONA DE <span>PERIGO</span></h1>

                <div class="alert alert-warning mb-4" style="font-size:0.9rem">
                    <i class="bi bi-shield-exclamation me-2"></i>
                    A senha deve ter no mínimo 8 caracteres, incluindo letras maiúsculas, minúsculas e números.
                </div>

                <div class="mb-3">
                    <label for="senhaAtual" class="form-label">Senha Atual</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="senhaAtual" name="senhaAtual"
                            autocomplete="current-password" placeholder="Digite sua senha atual" />
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
                    <div class="form-text" id="senhaFeedback"></div>
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

                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-key me-1"></i> Alterar senha
                </button>
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

        // ── Formatação automática de telefone ────────────────────
        const telefoneInput = document.getElementById('telefone');
        if (telefoneInput) {
            telefoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                if (value.length > 11) {
                    value = value.slice(0, 11);
                }
                
                if (value.length > 10) {
                    value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                } else if (value.length > 6) {
                    value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                } else if (value.length > 2) {
                    value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
                } else if (value.length > 0) {
                    value = value.replace(/(\d{0,2})/, '($1');
                }
                
                e.target.value = value;
            });
        }

        // ── Validação de senha em tempo real ─────────────────────
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

        // ── Checkbox "usar nome e sobrenome" ─────────────────────
        const check     = document.getElementById('usernameCheck');
        const usernameInput = document.getElementById('username');
        const nomeInput     = document.getElementById('nome');
        const sobrenomeInput = document.getElementById('sobrenome');

        if (check && usernameInput && nomeInput && sobrenomeInput) {
            check.addEventListener('change', () => {
                if (check.checked) {
                    const full = (nomeInput.value.trim() + ' ' + sobrenomeInput.value.trim()).trim().toLowerCase().replace(/\s+/g, '.');
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
                        const full = (nomeInput.value.trim() + ' ' + sobrenomeInput.value.trim()).trim().toLowerCase().replace(/\s+/g, '.');
                        usernameInput.value = full;
                    }
                });
            });
        }
    </script>
</body>
</html>