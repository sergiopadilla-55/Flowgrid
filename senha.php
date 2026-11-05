<?php

require_once '/../config/conexao.php';

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {

        $mensagem = 'Digite seu Usuario.';
        $tipo_mensagem = 'error';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $mensagem = 'Digite um Usuario válido.';
        $tipo_mensagem = 'error';

    } else {

        try {

            $sql = "SELECT id_usuario 
                    FROM usuarios 
                    WHERE user_usuario = :email 
                    LIMIT 1";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {

                $mensagem = 'Usuario encontrado. Seu gerente falará com você';
                $tipo_mensagem = 'success';

            } else {

                $mensagem = 'Nenhuma conta foi encontrada com este usuario.';
                $tipo_mensagem = 'error';

            }

        } catch (PDOException $e) {

            $mensagem = 'Ocorreu um erro ao consultar o banco de dados.';
            $tipo_mensagem = 'error';

        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Recuperar senha</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #333333;
            font-size: 24px;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #666666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #444444;
            font-weight: 600;
            font-size: 14px;
        }

        .input-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cccccc;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.3s ease;
        }

        .input-group input:focus {
            border-color: #2a5298;
            outline: none;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #2a5298;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #1e3c72;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            border: 1px solid #c3e6cb;
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #888888;
        }

        .footer-text a {
            color: #2a5298;
            text-decoration: none;
            font-weight: 600;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

    </style>

</head>

<body>

    <div class="login-container">

        <div class="login-header">

            <h1>Recuperar senha</h1>

            <p>
                Digite o usuario cadastrado para verificar sua conta.
            </p>

        </div>


        <?php if (!empty($mensagem)): ?>

            <div class="alert-<?php echo $tipo_mensagem; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>

        <?php endif; ?>


        <form method="POST" action="">

            <div class="input-group">

                <label for="email">
                    Usuário:
                </label>

                <input
                    type="text"
                    id="email"
                    name="email"
                    placeholder="Digite seu usuario"
                    required
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                >

            </div>


            <button type="submit" class="btn-submit">
                Verificar usuario
            </button>

        </form>


        <div class="footer-text">

            <a href="../index.php">
                Voltar para o login
            </a>

        </div>

    </div>

</body>

</html>