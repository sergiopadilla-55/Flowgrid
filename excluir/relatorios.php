<?php

session_start();

require_once __DIR__ . '/../config/conexao.php';

if (empty($_SESSION['id_usuario'])) {
    header('Location: ../index.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: ../Visualizar/relatorios.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Busca relatório
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        r.id_relatorio,
        r.data_relatorio,
        r.status,
        r.relatorio,
        p.nome_projeto,
        u.nome_usuario
    FROM relatorios r

    LEFT JOIN projetos p
        ON r.projeto = p.id_projeto

    LEFT JOIN usuarios u
        ON r.dono = u.id_usuario

    WHERE r.id_relatorio = ?
");

$stmt->execute([$id]);

$relatorio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$relatorio) {
    header('Location: ../Visualizar/relatorios.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Exclusão
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $stmt = $pdo->prepare("
            DELETE FROM relatorios
            WHERE id_relatorio = ?
        ");

        $stmt->execute([$id]);

        header('Location: ../Visualizar/relatorios.php');
        exit;

    } catch (Throwable $e) {

        $erro =
            'Não foi possível excluir o relatório.';
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Excluir relatório</title>

    <link rel="stylesheet"
          href="../assets/css/relatorios.css">

</head>

<body>

<main class="container delete-container">

    <div class="delete-card">

        <div class="delete-icon">
            
        </div>

        <h1>
            Excluir relatório?
        </h1>
		<br>
        <p>
            Você está prestes a excluir o relatório:
        </p>

		<br>
        <div class="delete-document">

            <strong>
                Relatório #<?= (int)$relatorio['id_relatorio'] ?>
            </strong>
		<br>
            <?php if (!empty($relatorio['data_relatorio'])): ?>

                <span>
                    Data:

                    <?= date(
                        'd/m/Y',
                        strtotime(
                            $relatorio['data_relatorio']
                        )
                    ) ?>
                </span>

            <?php endif; ?>
		<br>

            <?php if (!empty($relatorio['nome_projeto'])): ?>

                <span>
                    Projeto:

                    <?= htmlspecialchars(
                        $relatorio['nome_projeto']
                    ) ?>
                </span>

            <?php endif; ?>
		<br>

            <?php if (!empty($relatorio['nome_usuario'])): ?>

                <span>
                    Dono:

                    <?= htmlspecialchars(
                        $relatorio['nome_usuario']
                    ) ?>
                </span>

            <?php endif; ?>

        </div>

		<br>
        <div class="warning-box">

            <strong>Atenção</strong>

            <p>
                O relatório será removido permanentemente.
            </p>

            <?php if (!empty($relatorio['relatorio'])): ?>

                <p>
                    O arquivo armazenado
                    também será excluído.
                </p>

            <?php endif; ?>

        </div>
		<br>

        <?php if (!empty($erro)): ?>

            <div class="alert alert-error">

                <?= htmlspecialchars($erro) ?>

            </div>

        <?php endif; ?>

		<br>
        <form method="POST">

            <div class="form-actions">

                <a
                    href="../Visualizar/relatorios.php"
                    class="btn btn-secondary"
                >
                    Cancelar
                </a>

                <button
                    type="submit"
                    class="btn btn-danger"
                >
                    Sim, excluir
                </button>

            </div>

        </form>

    </div>

</main>

</body>

</html>