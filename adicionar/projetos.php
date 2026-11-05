<?php

session_start();

require_once __DIR__ . '/../config/conexao.php';

$erro = '';


/*
|--------------------------------------------------------------------------
| Buscar usuários
|--------------------------------------------------------------------------
|
| Somente usuários cadastrados podem ser responsáveis.
|
*/

$stmtUsuarios = $pdo->prepare("
    SELECT
        id_usuario,
        nome_usuario,
        user_usuario

    FROM usuarios

    ORDER BY nome_usuario ASC
");

$stmtUsuarios->execute();

$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Processamento
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome_projeto'] ?? '');

    $status = isset($_POST['status_projeto'])
        ? (int) $_POST['status_projeto']
        : 1;

    $usuarioResponsavel = !empty($_POST['usuario_responsavel'])
        ? (int) $_POST['usuario_responsavel']
        : null;


    /*
    |--------------------------------------------------------------------------
    | Validação
    |--------------------------------------------------------------------------
    */

    if ($nome === '') {

        $erro = 'Informe o nome do projeto.';

    } elseif (!in_array($status, [1, 2, 3, 4], true)) {

        $erro = 'Status de projeto inválido.';

    } else {

        try {

            $pdo->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | Verificar projeto com mesmo nome
            |--------------------------------------------------------------------------
            */

            $check = $pdo->prepare("
                SELECT id_projeto

                FROM projetos

                WHERE nome_projeto = ?

                LIMIT 1
            ");

            $check->execute([$nome]);


            if ($check->fetch()) {

                throw new Exception(
                    'Já existe um projeto com esse nome.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Criar responsável
            |--------------------------------------------------------------------------
            */

            $responsavelId = null;


            if ($usuarioResponsavel !== null) {

                /*
                | Verificar se usuário existe
                */

                $checkUsuario = $pdo->prepare("
                    SELECT id_usuario

                    FROM usuarios

                    WHERE id_usuario = ?

                    LIMIT 1
                ");

                $checkUsuario->execute([
                    $usuarioResponsavel
                ]);


                if (!$checkUsuario->fetch()) {

                    throw new Exception(
                        'O usuário responsável não existe.'
                    );
                }


                /*
                | Procurar responsável existente
                */

                $checkResponsavel = $pdo->prepare("
                    SELECT responsavel_id

                    FROM responsavel

                    WHERE usuario_id = ?

                    LIMIT 1
                ");

                $checkResponsavel->execute([
                    $usuarioResponsavel
                ]);

                $responsavelExistente =
                    $checkResponsavel->fetch(PDO::FETCH_ASSOC);


                if ($responsavelExistente) {

                    $responsavelId =
                        $responsavelExistente['responsavel_id'];

                } else {

                    /*
                    | Criar responsável
                    */

                    $insertResponsavel = $pdo->prepare("
                        INSERT INTO responsavel (
                            usuario_id
                        )

                        VALUES (?)
                    ");

                    $insertResponsavel->execute([
                        $usuarioResponsavel
                    ]);

                    $responsavelId =
                        $pdo->lastInsertId();
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Criar projeto
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                INSERT INTO projetos (
                    nome_projeto,
                    status_projeto,
                    responsavel_id
                )

                VALUES (?, ?, ?)
            ");

            $stmt->execute([
                $nome,
                $status,
                $responsavelId
            ]);


            $pdo->commit();


            header('Location: ../Visualizar/projetos.php');
            exit;


        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $erro = $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Adicionar projeto</title>

    <link
        rel="stylesheet"
        href="../assets/css/projetos.css"
    >

</head>

<body>

<main class="container form-page">

    <header class="page-header">

        <div>

            <h1>Adicionar projeto</h1>

            <p>
                Cadastre um novo projeto.
            </p>

        </div>

        <a
            href="../Visualizar/projetos.php"
            class="btn btn-secondary"
        >
            Voltar
        </a>

    </header>


    <?php if ($erro): ?>

        <div class="alert">

            <?= htmlspecialchars($erro) ?>

        </div>

    <?php endif; ?>


    <form
        method="POST"
        class="form-card"
    >

        <div class="form-group">

            <label for="nome_projeto">
                Nome do projeto
            </label>

            <input
                type="text"
                id="nome_projeto"
                name="nome_projeto"
                maxlength="100"
                required
                placeholder="Ex.: Projeto Residencial Alpha"
                value="<?= htmlspecialchars(
                    $_POST['nome_projeto'] ?? ''
                ) ?>"
            >

        </div>


        <div class="form-group">

            <label for="status_projeto">
                Status
            </label>

            <select
                id="status_projeto"
                name="status_projeto"
                required
            >

                <option
                    value="1"
                    <?= (
                        ($_POST['status_projeto'] ?? '1') == '1'
                    ) ? 'selected' : '' ?>
                >
                    Em andamento
                </option>

                <option
                    value="2"
                    <?= (
                        ($_POST['status_projeto'] ?? '') == '2'
                    ) ? 'selected' : '' ?>
                >
                    Concluído
                </option>

                <option
                    value="3"
                    <?= (
                        ($_POST['status_projeto'] ?? '') == '3'
                    ) ? 'selected' : '' ?>
                >
                    Cancelado
                </option>

                <option
                    value="4"
                    <?= (
                        ($_POST['status_projeto'] ?? '') == '4'
                    ) ? 'selected' : '' ?>
                >
                    Atrasado
                </option>

            </select>

        </div>


        <div class="form-group">

            <label for="usuario_responsavel">
                Responsável
            </label>

            <select
                id="usuario_responsavel"
                name="usuario_responsavel"
            >

                <option value="">
                    Nenhum responsável
                </option>


                <?php foreach ($usuarios as $usuario): ?>

                    <option
                        value="<?= $usuario['id_usuario'] ?>"
                        <?= (
                            ($_POST['usuario_responsavel'] ?? '')
                            == $usuario['id_usuario']
                        ) ? 'selected' : '' ?>
                    >

                        <?= htmlspecialchars(
                            $usuario['nome_usuario']
                        ) ?>

                        <?php if (!empty($usuario['user_usuario'])): ?>

                            (@<?= htmlspecialchars(
                                $usuario['user_usuario']
                            ) ?>)

                        <?php endif; ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <div class="form-actions">

            <a
                href="../Visualizar/projetos.php"
                class="btn btn-secondary"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="btn btn-primary"
            >
                Cadastrar projeto
            </button>

        </div>

    </form>

</main>

</body>

</html>