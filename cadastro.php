<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/config/conexao.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome      = trim(filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $usuario   = trim(filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $empresaId = filter_input(INPUT_POST, 'empresa_id', FILTER_VALIDATE_INT);
    $senha           = $_POST['senha'] ?? '';
    $confirmarSenha  = $_POST['confirmar_senha'] ?? '';

    if ($nome === '' || $usuario === '' || !$empresaId || $senha === '' || $confirmarSenha === '') {
        $erro = 'Por favor, preencha todos os campos corretamente.';
    } elseif ($senha !== $confirmarSenha) {
        $erro = 'As palavras-passe não coincidem!';
    } elseif (strlen($senha) < 6) {
        $erro = 'A palavra-passe deve ter pelo menos 6 caracteres.';
    } elseif (strlen($senha) > 72) {
        $erro = 'A palavra-passe não pode ter mais de 72 caracteres.';
    } else {
        try {
            // Voltámos a usar $pdo, que é a variável correta do seu conexao.php
            $stmtEmpresa = $pdo->prepare('SELECT id_empresa FROM empresa WHERE id_empresa = :empresa_id');
            $stmtEmpresa->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmtEmpresa->execute();

            if ($stmtEmpresa->rowCount() === 0) {
                $erro = 'O Código da Empresa informado não existe no sistema.';
            } else {
                $stmtUser = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE user_usuario = :usuario');
                $stmtUser->bindParam(':usuario', $usuario);
                $stmtUser->execute();

                if ($stmtUser->rowCount() > 0) {
                    $erro = 'Este nome de utilizador já está em uso. Escolha outro.';
                } else {
                    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

                    $stmtInsert = $pdo->prepare(
                        'INSERT INTO usuarios (nome_usuario, user_usuario, empresa_cod, senha_usuario)
                         VALUES (:nome, :usuario, :empresa_id, :senha)'
                    );
                    $stmtInsert->bindParam(':nome', $nome);
                    $stmtInsert->bindParam(':usuario', $usuario);
                    $stmtInsert->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
                    $stmtInsert->bindParam(':senha', $senhaHash);

                    if ($stmtInsert->execute()) {
                        $sucesso = 'Cadastro realizado com sucesso! A redirecionar para o login...';
                        // Redireciona para o index.php
                        header('refresh:2;url=index.php');
                    } else {
                        $erro = 'Erro ao cadastrar. Tente novamente.';
                    }
                }
            }
        } catch (PDOException $e) {
            $erro = 'Erro na base de dados. Tente novamente mais tarde.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cadastro - FlowGrid</title>
<link rel="stylesheet" href="assets/css/cadstro.css">
</head>
<body>

<div class="register-container">
  <div class="register-header">
    <h1>Criar Nova Conta</h1>
    <p>Preencha os dados abaixo para se cadastrar</p>
  </div>

  <?php if ($erro !== ''): ?>
    <div class="alert-error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <?php if ($sucesso !== ''): ?>
    <div class="alert-success"><?= htmlspecialchars($sucesso, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <form action="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') ?>" method="POST">
    <div class="input-group">
      <label for="nome">Nome Completo</label>
      <input type="text" id="nome" name="nome" placeholder="Digite o seu nome completo" required autofocus>
    </div>

    <div class="input-group">
      <label for="usuario">Nome de Utilizador</label>
      <input type="text" id="usuario" name="usuario" placeholder="Escolha um nome de utilizador" required>
    </div>

    <div class="input-group">
      <label for="empresa_id">Código da Empresa</label>
      <input type="number" id="empresa_id" name="empresa_id" placeholder="ID da empresa" required>
    </div>

    <div class="input-group">
      <label for="senha">Palavra-passe</label>
      <input type="password" id="senha" name="senha" placeholder="Mínimo de 6 caracteres" required>
    </div>

    <div class="input-group">
      <label for="confirmar_senha">Confirmar Palavra-passe</label>
      <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Repita a palavra-passe" required>
    </div>

    <button type="submit" class="btn-submit">Cadastrar</button>
  </form>

  <div class="login-link">Já possui uma conta? <a href="index.php">Fazer Login</a></div>

  <div class="footer-text"></div>
</div>

</body>
</html>