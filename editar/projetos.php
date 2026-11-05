<?php

session_start();

require_once __DIR__ . '/../config/conexao.php';


/*
|--------------------------------------------------------------------------
| ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id) {

    header('Location: ../Visualizar/projetos.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Buscar projeto
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        p.id_projeto,
        p.nome_projeto,
        p.status_projeto,
        p.responsavel_id,
        r.usuario_id

    FROM projetos p

    LEFT JOIN responsavel r
        ON p.responsavel_id = r.responsavel_id

    WHERE p.id_projeto = ?

    LIMIT 1
");

$stmt->execute([$id]);

$projeto = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$projeto) {

    header('Location: ../Visualizar/projetos.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Buscar usuários
|--------------------------------------------------------------------------
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

$erro = '';


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

    $usuarioResponsavel =
        !empty($_POST['usuario_responsavel'])
        ? (int) $_POST['usuario_responsavel']
        : null;


    if ($nome === '') {

        $erro = 'Informe o nome do projeto.';

    } elseif (!in_array($status, [1, 2, 3, 4], true)) {

        $erro = 'Status inválido.';

    } else {

        try {

            $pdo->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | Verificar nome duplicado
            |--------------------------------------------------------------------------
            */

            $check = $pdo->prepare("
                SELECT id_projeto

                FROM projetos

                WHERE nome_projeto = ?
                AND id_projeto != ?

                LIMIT 1
            ");

            $check->execute([
                $nome,
                $id
            ]);


            if ($check->fetch()) {

                throw new Exception(
                    'Já existe outro projeto com esse nome.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Responsável
            |--------------------------------------------------------------------------
            */

            $responsavelId = null;


            if ($usuarioResponsavel !== null) {

                /*
                | Verificar usuário
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
                        'O usuário selecionado não existe.'
                    );
                }


                /*
                | Verificar responsável existente
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
            | Atualizar projeto
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                UPDATE projetos

                SET
                    nome_projeto = ?,
                    status_projeto = ?,
                    responsavel_id = ?

                WHERE id_projeto = ?
            ");

            $stmt->execute([
                $nome,
                $status,
                $responsavelId,
                $id
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

    <title>Editar projeto</title>

    <link
        rel="stylesheet"
        href="../assets/css/projetos.css"
    >

</head>

<body>

<main class="container form-page">

    <header class="page-header">

        <div>

            <h1>Editar projeto</h1>

            <p>
                Altere as informações do projeto.
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
                value="<?= htmlspecialchars(
                    $_POST['nome_projeto']
                    ?? ($projeto['nome_projeto'] ?? '')
                ) ?>"
            >

        </div>


        <div class="form-group">

            <label for="status_projeto">
                Status
            </label>

            <?php

            $statusSelecionado =
                $_POST['status_projeto']
                ?? $projeto['status_projeto'];

            ?>

            <select
                id="status_projeto"
                name="status_projeto"
                required
            >

                <option
                    value="1"
                    <?= $statusSelecionado == 1
                        ? 'selected'
                        : '' ?>
                >
                    Em andamento
                </option>

                <option
                    value="2"
                    <?= $statusSelecionado == 2
                        ? 'selected'
                        : '' ?>
                >
                    Concluído
                </option>

                <option
                    value="3"
                    <?= $statusSelecionado == 3
                        ? 'selected'
                        : '' ?>
                >
                    Cancelado
                </option>

                <option
                    value="4"
                    <?= $statusSelecionado == 4
                        ? 'selected'
                        : '' ?>
                >
                    Atrasado
                </option>

            </select>

        </div>


        <div class="form-group">

            <label for="usuario_responsavel">
                Responsável
            </label>

            <?php

            $responsavelSelecionado =
                $_POST['usuario_responsavel']
                ?? ($projeto['usuario_id'] ?? '');

            ?>

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
                        <?= $responsavelSelecionado
                            == $usuario['id_usuario']
                            ? 'selected'
                            : '' ?>
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
                Salvar alterações
            </button>

        </div>

    </form>

</main>

</body>

</html>