<?php

session_start();

require_once __DIR__ . '/../config/conexao.php';

if (empty($_SESSION['id_usuario'])) {
    header('Location: ../index.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: ../Visualizar/documentos.php');
    exit;
}


$stmt = $pdo->prepare("
    SELECT
        d.id_documento,
        d.nome_documento,
        d.arquivo,
        p.nome_projeto
    FROM documentos d
    LEFT JOIN projetos p
        ON d.projeto = p.id_projeto
    WHERE d.id_documento = ?
");

$stmt->execute([$id]);

$documento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$documento) {
    header('Location: ../Visualizar/documentos.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $stmt = $pdo->prepare("
            DELETE FROM documentos
            WHERE id_documento = ?
        ");

        $stmt->execute([$id]);

        header('Location: ../Visualizar/documentos.php');
        exit;

    } catch (Throwable $e) {

        $erro = 'Não foi possível excluir o documento.';
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Excluir documento</title>

    <link rel="stylesheet"
          href="../Visualizar/documentos.css">

</head>

<body>

<main class="container delete-container">

    <div class="delete-card">

        <div class="delete-icon">
            !
        </div>

        <h1>Excluir documento?</h1>

        <p>
            Você está prestes a excluir:
        </p>

        <div class="delete-document">

            <strong>
                <?= htmlspecialchars(
                    $documento['nome_documento']
                    ?: 'Documento sem nome'
                ) ?>
            </strong>

            <?php if (!empty($documento['nome_projeto'])): ?>

                <span>
                    Projeto:
                    <?= htmlspecialchars(
                        $documento['nome_projeto']
                    ) ?>
                </span>

            <?php endif; ?>

        </div>


        <div class="warning-box">

            <strong>Atenção</strong>

            <p>
                O documento será removido permanentemente do banco de dados.
            </p>

            <?php if (!empty($documento['arquivo'])): ?>

                <p>
                    O arquivo armazenado
                    também será apagado.
                </p>

            <?php endif; ?>

        </div>


        <?php if (!empty($erro)): ?>

            <div class="alert alert-error">

                <?= htmlspecialchars($erro) ?>

            </div>

        <?php endif; ?>


        <form method="POST">

            <div class="form-actions">

                <a
                    href="../Visualizar/documentos.php"
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