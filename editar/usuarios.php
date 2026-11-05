<?php

session_start();

require_once __DIR__ . '/../config/conexao.php';

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id) {

    header('Location: ../Visualizar/usuarios.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Buscar usuário
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM usuarios
    WHERE id_usuario = ?
    LIMIT 1
");

$stmt->execute([$id]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {

    header('Location: ../Visualizar/usuarios.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Buscar equipes
|--------------------------------------------------------------------------
*/

$stmtEquipes = $pdo->prepare("
    SELECT id_equipe, setor
    FROM equipes
    ORDER BY setor ASC
");

$stmtEquipes->execute();

$equipes = $stmtEquipes->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Buscar empresas
|--------------------------------------------------------------------------
*/

$stmtEmpresas = $pdo->prepare("
    SELECT id_empresa
    FROM empresa
    ORDER BY id_empresa ASC
");

$stmtEmpresas->execute();

$empresas = $stmtEmpresas->fetchAll(PDO::FETCH_ASSOC);

$erro = '';


/*
|--------------------------------------------------------------------------
| Processamento
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome_usuario'] ?? '');
    $login = trim($_POST['user_usuario'] ?? '');
    $senha = $_POST['senha_usuario'] ?? '';
    $ocupacao = trim($_POST['ocupacao'] ?? '');

    $empresa = !empty($_POST['empresa_cod'])
        ? (int) $_POST['empresa_cod']
        : null;

    $equipe = !empty($_POST['equipe_id'])
        ? (int) $_POST['equipe_id']
        : null;


    if ($nome === '') {

        $erro = 'Informe o nome do usuário.';

    } elseif ($login === '') {

        $erro = 'Informe o nome de usuário.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | Verificar login duplicado
        |--------------------------------------------------------------------------
        */

        $check = $pdo->prepare("
            SELECT id_usuario
            FROM usuarios
            WHERE user_usuario = ?
            AND id_usuario != ?
            LIMIT 1
        ");

        $check->execute([
            $login,
            $id
        ]);

        if ($check->fetch()) {

            $erro = 'Esse nome de usuário já pertence a outra conta.';

        } else {

            /*
            |--------------------------------------------------------------------------
            | Atualizar com ou sem senha
            |--------------------------------------------------------------------------
            */

            if ($senha !== '') {

                $senhaHash = password_hash(
                    $senha,
                    PASSWORD_DEFAULT
                );

                $sql = "
                    UPDATE usuarios
                    SET
                        nome_usuario = ?,
                        user_usuario = ?,
                        senha_usuario = ?,
                        ocupacao = ?,
                        empresa_cod = ?,
                        equipe_id = ?
                    WHERE id_usuario = ?
                ";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    $nome,
                    $login,
                    $senhaHash,
                    $ocupacao !== '' ? $ocupacao : null,
                    $empresa,
                    $equipe,
                    $id
                ]);

            } else {

                $sql = "
                    UPDATE usuarios
                    SET
                        nome_usuario = ?,
                        user_usuario = ?,
                        ocupacao = ?,
                        empresa_cod = ?,
                        equipe_id = ?
                    WHERE id_usuario = ?
                ";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    $nome,
                    $login,
                    $ocupacao !== '' ? $ocupacao : null,
                    $empresa,
                    $equipe,
                    $id
                ]);
            }


            header('Location: ../Visualizar/usuarios.php');
            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar usuário</title>

    <link rel="stylesheet" href="../assets/css/usuarios.css">

</head>

<body>

<main class="container form-page">

    <header class="page-header">

        <div>

            <h1>Editar usuário</h1>

            <p>
                Altere as informações do usuário.
            </p>

        </div>

        <a href="../Visualizar/usuarios.php" class="btn btn-secondary">
            Voltar
        </a>

    </header>


    <?php if ($erro): ?>

        <div class="alert">
            <?= htmlspecialchars($erro) ?>
        </div>

    <?php endif; ?>


    <form method="POST" class="form-card">

        <div class="form-group">

            <label for="nome_usuario">
                Nome completo
            </label>

            <input
                type="text"
                id="nome_usuario"
                name="nome_usuario"
                maxlength="100"
                required
                value="<?= htmlspecialchars(
                    $_POST['nome_usuario'] ?? $usuario['nome_usuario']
                ) ?>"
            >

        </div>


        <div class="form-group">

            <label for="user_usuario">
                Nome de usuário
            </label>

            <input
                type="text"
                id="user_usuario"
                name="user_usuario"
                maxlength="50"
                required
                value="<?= htmlspecialchars(
                    $_POST['user_usuario'] ?? $usuario['user_usuario']
                ) ?>"
            >

        </div>


        <div class="form-group">

            <label for="senha_usuario">
                Nova senha
            </label>

            <input
                type="password"
                id="senha_usuario"
                name="senha_usuario"
                maxlength="255"
            >

            <small>
                Deixe vazio para manter a senha atual.
            </small>

        </div>


        <div class="form-group">

            <label for="ocupacao">
                Ocupação
            </label>

            <input
                type="text"
                id="ocupacao"
                name="ocupacao"
                maxlength="100"
                value="<?= htmlspecialchars(
                    $_POST['ocupacao'] ?? ($usuario['ocupacao'] ?? '')
                ) ?>"
            >

        </div>


        <div class="form-row">

            <div class="form-group">

                <label for="empresa_cod">
                    Empresa
                </label>

                <select name="empresa_cod" id="empresa_cod">

                    <option value="">
                        Nenhuma empresa
                    </option>

                    <?php foreach ($empresas as $empresaItem): ?>

                        <?php

                        $empresaSelecionada =
                            $_POST['empresa_cod']
                            ?? $usuario['empresa_cod'];

                        ?>

                        <option
                            value="<?= $empresaItem['id_empresa'] ?>"
                            <?= $empresaSelecionada == $empresaItem['id_empresa']
                                ? 'selected'
                                : '' ?>
                        >
                            Empresa #<?= $empresaItem['id_empresa'] ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-group">

                <label for="equipe_id">
                    Equipe
                </label>

                <select name="equipe_id" id="equipe_id">

                    <option value="">
                        Nenhuma equipe
                    </option>

                    <?php foreach ($equipes as $equipeItem): ?>

                        <?php

                        $equipeSelecionada =
                            $_POST['equipe_id']
                            ?? $usuario['equipe_id'];

                        ?>

                        <option
                            value="<?= $equipeItem['id_equipe'] ?>"
                            <?= $equipeSelecionada == $equipeItem['id_equipe']
                                ? 'selected'
                                : '' ?>
                        >
                            <?= htmlspecialchars($equipeItem['setor']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

        </div>


        <div class="form-actions">

            <a href="..Visualizar/usuarios.php" class="btn btn-secondary">
                Cancelar
            </a>

            <button type="submit" class="btn btn-primary">
                Salvar alterações
            </button>

        </div>

    </form>

</main>

</body>

</html>