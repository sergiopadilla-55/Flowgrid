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

    header('Location: ../Visualizar/equipes.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Buscar equipe
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id_equipe,
        setor,
        descricao_equipe

    FROM equipes

    WHERE id_equipe = ?

    LIMIT 1
");

$stmt->execute([$id]);

$equipe = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$equipe) {

    header('Location: ../Visualizar/equipes.php');
    exit;
}


$erro = '';


/*
|--------------------------------------------------------------------------
| Processamento
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $setor = trim($_POST['setor'] ?? '');
    $descricao = trim($_POST['descricao_equipe'] ?? '');


    if ($setor === '') {

        $erro = 'Informe o setor da equipe.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | Verificar duplicidade
        |--------------------------------------------------------------------------
        */

        $check = $pdo->prepare("
            SELECT id_equipe

            FROM equipes

            WHERE setor = ?
            AND id_equipe != ?

            LIMIT 1
        ");

        $check->execute([
            $setor,
            $id
        ]);


        if ($check->fetch()) {

            $erro = 'Já existe outra equipe com esse setor.';

        } else {

            /*
            |--------------------------------------------------------------------------
            | Atualizar
            |--------------------------------------------------------------------------
            */

            $sql = "
                UPDATE equipes

                SET
                    setor = ?,
                    descricao_equipe = ?

                WHERE id_equipe = ?
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $setor,
                $descricao !== '' ? $descricao : null,
                $id
            ]);


            header('Location: ../Visualizar/equipes.php');
            exit;
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

    <title>Editar equipe</title>

    <link rel="stylesheet" href="../assets/css/equipes.css">

</head>

<body>

<main class="container form-page">

    <header class="page-header">

        <div>

            <h1>Editar equipe</h1>

            <p>
                Altere as informações da equipe.
            </p>

        </div>

        <a
            href="../Visualizar/equipes.php"
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

            <label for="setor">
                Setor
            </label>

            <input
                type="text"
                id="setor"
                name="setor"
                maxlength="100"
                required
                value="<?= htmlspecialchars(
                    $_POST['setor']
                    ?? $equipe['setor']
                ) ?>"
            >

        </div>


        <div class="form-group">

            <label for="descricao_equipe">
                Descrição da equipe
            </label>

            <textarea
                id="descricao_equipe"
                name="descricao_equipe"
                maxlength="1000"
                rows="7"
                placeholder="Descreva a função ou objetivo da equipe..."
            ><?= htmlspecialchars(
                $_POST['descricao_equipe']
                ?? ($equipe['descricao_equipe'] ?? '')
            ) ?></textarea>

            <small>
                Máximo de 1000 caracteres.
            </small>

        </div>


        <div class="form-actions">

            <a
                href="../Visualizar/equipes.php"
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