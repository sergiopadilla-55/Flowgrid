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
    SELECT
        id_usuario,
        nome_usuario,
        user_usuario,
        ocupacao
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
| Confirmar exclusão
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	try{
    $stmt = $pdo->prepare("
        DELETE FROM usuarios
        WHERE id_usuario = ?
    ");

    $stmt->execute([$id]);

    header('Location: ../Visualizar/usuarios.php');
    exit;
    } catch (Throwable $e) {

        $erro =
            'Não foi possível excluir o usuário.';
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Excluir usuário</title>

    <link rel="stylesheet" href="../assets/css/usuarios.css">

</head>

<body>

<main class="container">

    <section class="delete-card">

        <div class="delete-icon">
            !
        </div>

        <h1>Excluir usuário?</h1>

        <p>
            Você está prestes a excluir o usuário:
        </p>

        <strong class="delete-name">
            <?= htmlspecialchars($usuario['nome_usuario']) ?>
        </strong>

        <p class="warning">
            Essa ação não poderá ser desfeita.
        </p>


        <form method="POST" class="form-actions">

            <a href="../Visualizar/usuarios.php" class="btn btn-secondary">
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