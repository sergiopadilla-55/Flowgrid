<?php

session_start();

require_once __DIR__ . '/../config/conexao.php';

$erro = '';


/*
|--------------------------------------------------------------------------
| Processamento
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $setor = trim($_POST['setor'] ?? '');
    $descricao = trim($_POST['descricao_equipe'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Validação
    |--------------------------------------------------------------------------
    */

    if ($setor === '') {

        $erro = 'Informe o setor da equipe.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | Verificar se o setor já existe
        |--------------------------------------------------------------------------
        */

        $check = $pdo->prepare("
            SELECT id_equipe
            FROM equipes
            WHERE setor = ?
            LIMIT 1
        ");

        $check->execute([$setor]);

        if ($check->fetch()) {

            $erro = 'Já existe uma equipe cadastrada com esse setor.';

        } else {

            /*
            |--------------------------------------------------------------------------
            | Inserir
            |--------------------------------------------------------------------------
            */

            $sql = "
                INSERT INTO equipes (
                    setor,
                    descricao_equipe
                )
                VALUES (?, ?)
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $setor,
                $descricao !== '' ? $descricao : null
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

    <title>Adicionar equipe</title>

    <link rel="stylesheet" href="../assets/css/equipes.css">

</head>

<body>

<main class="container form-page">

    <header class="page-header">

        <div>

            <h1>Adicionar equipe</h1>

            <p>
                Cadastre uma nova equipe no sistema.
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
                placeholder="Ex.: Desenvolvimento"
                value="<?= htmlspecialchars(
                    $_POST['setor'] ?? ''
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
                $_POST['descricao_equipe'] ?? ''
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
                Cadastrar equipe
            </button>

        </div>

    </form>

</main>

</body>

</html>