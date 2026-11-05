<?php
declare(strict_types=1);
 
session_start();
require_once __DIR__ . '/config/conexao.php';
 
// Se já está logado, não precisa ver a tela de login de novo.
if (!empty($_SESSION['id_usuario'])) {
    header('Location: dashboard.php');
    exit;
}
 
$erro = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim(filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $senha   = $_POST['senha'] ?? '';
 
    if ($usuario === '' || $senha === '') {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        // Consulta preparada — o nome da coluna é user_usuario (conferido no schema.sql)
        $stmt = $pdo->prepare(
            'SELECT id_usuario, nome_usuario, senha_usuario, ocupacao
             FROM usuarios WHERE user_usuario = :usuario LIMIT 1'
        );
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();
        $usuarioDb = $stmt->fetch(PDO::FETCH_ASSOC);
 
        // senha_usuario é VARCHAR(255) no schema, então cabe um hash bcrypt (60 chars)
        // ou argon2id — nunca compare senha em texto puro.
        if ($usuarioDb && password_verify($senha, $usuarioDb['senha_usuario'])) {
            session_regenerate_id(true);
            $_SESSION['id_usuario']   = $usuarioDb['id_usuario'];
            $_SESSION['nome_usuario'] = $usuarioDb['nome_usuario'];
            $_SESSION['ocupacao']     = $usuarioDb['ocupacao'] ?? 'Colaborador';
 
            header('Location: dashboard.php');
            exit;
        }
 
        $erro = 'Usuário ou senha incorretos!';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - FlowGrid</title>
<link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
 
<div class="login-container">
  <div class="login-header">
    <h1>Acesso ao Gerenciador</h1>
    <p>Digite suas credenciais para continuar</p>
  </div>
 
  <?php if ($erro !== ''): ?>
    <div class="alert-error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
 
  <form action="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') ?>" method="POST">
    <div class="input-group">
      <label for="usuario">Usuário</label>
      <input type="text" id="usuario" name="usuario" placeholder="Digite seu usuário" required autofocus>
    </div>
 
    <div class="input-group">
      <label for="senha">Senha</label>
      <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
    </div>
 
    <p>Não possui uma conta? <a href="cadastro.php">Se cadastre!</a></p>
    <p>Esqueceu a senha? <a href="senha.php">Clique aqui para redefinir!</a></p>
 
    <button type="submit" class="btn-submit">Entrar</button>
  </form>
 
  <div class="footer-text"></div>
</div>
 
</body>
</html>