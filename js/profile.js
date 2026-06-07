const usernameField = document.getElementById('username');
const usernameCheck = document.getElementById('usernameCheck');

// Desabilitar campo de nome de usuário se o checkbox estiver marcado
usernameCheck.addEventListener('change', () => {
    if (usernameCheck.checked) {
        usernameField.disabled = true;
        usernameField.placeholder = '-';
    } else {
        usernameField.disabled = false;
        usernameField.placeholder = 'Kiratusji';
    }
});