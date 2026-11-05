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


/*
|--------------------------------------------------------------------------
| Contar usuários
|--------------------------------------------------------------------------
*/

$stmtUsuarios = $pdo->prepare("
    SELECT COUNT(*)
    FROM usuarios
    WHERE equipe_id = ?
");

$stmtUsuarios->execute([$id]);

$totalUsuarios = (int) $stmtUsuarios->fetchColumn();


/*
|--------------------------------------------------------------------------
| Contar projetos relacionados
|--------------------------------------------------------------------------
*/

$stmtProjetos = $pdo->prepare("
    SELECT COUNT(*)
    FROM equipe_projeto
    WHERE equipe_id = ?
");

$stmtProjetos->execute([$id]);

$totalProjetos = (int) $stmtProjetos->fetchColumn();


/*
|--------------------------------------------------------------------------
| Exclusão
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $pdo->prepare("
        DELETE FROM equipes
        WHERE id_equipe = ?
    ");

    $stmt->execute([$id]);

    header('Location: ../Visualizar/equipes.php');
    exit;
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

    <title>Excluir equipe</title>

    <link rel="stylesheet" href="../assets/css/equipes.css">

</head>

<body>

<main class="container">

    <section class="delete-card">

        <div class="delete-icon">
            !
        </div>


        <h1>Excluir equipe?</h1>


        <p>
            Você está prestes a excluir a equipe:
        </p>


        <strong class="delete-name">

            <?= htmlspecialchars(
                $equipe['setor']
            ) ?>

        </strong>


        <?php if ($totalUsuarios > 0): ?>

            <div class="warning-box">

                <strong>
                    Atenção
                </strong>

                <p>

                    Existem
                    <strong>
                        <?= $totalUsuarios ?>
                    </strong>

                    <?= $totalUsuarios == 1
                        ? 'usuário associado'
                        : 'usuários associados'
                    ?>
                    a esta equipe.

                </p>

                <p>
                    Os usuários não serão excluídos.
                    Eles ficarão sem equipe.
                </p>

            </div>

        <?php endif; ?>


        <?php if ($totalProjetos > 0): ?>

            <div class="warning-box">

                <strong>
                    Projetos relacionados
                </strong>

                <p>

                    Esta equipe está relacionada a
                    <strong>
                        <?= $totalProjetos ?>
                    </strong>

                    <?= $totalProjetos == 1
                        ? 'projeto'
                        : 'projetos'
                    ?>.

                </p>

                <p>
                    A relação entre a equipe e esses
                    projetos será removida.

                </p>

            </div>

        <?php endif; ?>


        <p class="warning">
            Essa ação não poderá ser desfeita.
        </p>


        <form
            method="POST"
            class="form-actions"
        >

            <a
                href="../Visualizar/equipes.php"
                class="btn btn-secondary"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="btn btn-delete"
            >
                Sim, excluir
            </button>

        </form>

    </section>

</main>

</body>

</html>