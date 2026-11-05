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

    header('Location: ../Visualizarprojetos.php');
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
        p.status_projeto

    FROM projetos p

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
| Contar documentos
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM documentos
    WHERE projeto = ?
");

$stmt->execute([$id]);

$totalDocumentos = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Contar relatórios
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM relatorios
    WHERE projeto = ?
");

$stmt->execute([$id]);

$totalRelatorios = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Contar equipes
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM equipe_projeto
    WHERE projeto_id = ?
");

$stmt->execute([$id]);

$totalEquipes = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Exclusão
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $pdo->prepare("
        DELETE FROM projetos
        WHERE id_projeto = ?
    ");

    $stmt->execute([$id]);

    header('Location: ../Visualizar/projetos.php');
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

    <title>Excluir projeto</title>

    <link
        rel="stylesheet"
        href="../assets/css/projetos.css"
    >

</head>

<body>

<main class="container">

    <section class="delete-card">

        <div class="delete-icon">
            !
        </div>


        <h1>Excluir projeto?</h1>


        <p>
            Você está prestes a excluir:
        </p>


        <strong class="delete-name">

            <?= htmlspecialchars(
                $projeto['nome_projeto']
                ?? 'Projeto sem nome'
            ) ?>

        </strong>


        <?php if ($totalDocumentos > 0): ?>

            <div class="warning-box">

                <strong>
                    <?= $totalDocumentos ?>
                    <?= $totalDocumentos == 1
                        ? 'documento relacionado'
                        : 'documentos relacionados' ?>
                </strong>

                <p>
                    Os documentos não serão excluídos.
                    A referência ao projeto será removida.
                </p>

            </div>

        <?php endif; ?>


        <?php if ($totalRelatorios > 0): ?>

            <div class="warning-box">

                <strong>
                    <?= $totalRelatorios ?>
                    <?= $totalRelatorios == 1
                        ? 'relatório relacionado'
                        : 'relatórios relacionados' ?>
                </strong>

                <p>
                    Os relatórios não serão excluídos.
                    A referência ao projeto será removida.
                </p>

            </div>

        <?php endif; ?>


        <?php if ($totalEquipes > 0): ?>

            <div class="warning-box">

                <strong>
                    <?= $totalEquipes ?>
                    <?= $totalEquipes == 1
                        ? 'equipe relacionada'
                        : 'equipes relacionadas' ?>
                </strong>

                <p>
                    A relação entre as equipes e este projeto
                    será removida.
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
                href="../Visualizar/projetos.php"
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