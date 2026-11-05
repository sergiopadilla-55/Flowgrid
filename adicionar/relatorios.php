
<?php

session_start();

require_once __DIR__ . '/../config/conexao.php';

if (empty($_SESSION['id_usuario'])) {
    header('Location: ../index.php');
    exit;
}

$erros = [];

$data_relatorio = date('Y-m-d');
$status = 1;
$projeto = 0;
$dono = 0;


/*
|--------------------------------------------------------------------------
| Projetos
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id_projeto,
        nome_projeto
    FROM projetos
    ORDER BY nome_projeto ASC
");

$projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Usuários
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id_usuario,
        nome_usuario,
        user_usuario
    FROM usuarios
    ORDER BY nome_usuario ASC
");

$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Cadastro
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data_relatorio = $_POST['data_relatorio'] ?? '';
    $status = (int)($_POST['status'] ?? 1);
    $projeto = (int)($_POST['projeto'] ?? 0);
    $dono = (int)($_POST['dono'] ?? 0);


    /*
    |--------------------------------------------------------------------------
    | Validações
    |--------------------------------------------------------------------------
    */

    if (
        $data_relatorio !== ''
        &&
        !DateTime::createFromFormat(
            'Y-m-d',
            $data_relatorio
        )
    ) {

        $erros[] = 'Data do relatório inválida.';
    }


    if (!in_array($status, [1, 2, 3, 4], true)) {

        $erros[] = 'Status inválido.';
    }


    /*
    |--------------------------------------------------------------------------
    | Arquivo
    |--------------------------------------------------------------------------
    */

    $arquivo = null;

    if (
        isset($_FILES['relatorio'])
        &&
        $_FILES['relatorio']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['relatorio']['error'] !== UPLOAD_ERR_OK) {

            $erros[] =
                'Ocorreu um erro durante o envio do relatório.';

        } else {

            $arquivo = file_get_contents(
                $_FILES['relatorio']['tmp_name']
            );

            if ($arquivo === false) {

                $erros[] =
                    'Não foi possível ler o arquivo enviado.';
            }
        }
    }


    if (empty($erros)) {

        try {

            $pdo->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | Projeto
            |--------------------------------------------------------------------------
            */

            $projetoId = null;

            if ($projeto > 0) {

                $stmt = $pdo->prepare("
                    SELECT id_projeto
                    FROM projetos
                    WHERE id_projeto = ?
                ");

                $stmt->execute([$projeto]);

                if (!$stmt->fetch()) {

                    throw new Exception(
                        'O projeto selecionado não existe.'
                    );
                }

                $projetoId = $projeto;
            }


            /*
            |--------------------------------------------------------------------------
            | Dono
            |--------------------------------------------------------------------------
            */

            $donoId = null;

            if ($dono > 0) {

                $stmt = $pdo->prepare("
                    SELECT id_usuario
                    FROM usuarios
                    WHERE id_usuario = ?
                ");

                $stmt->execute([$dono]);

                if (!$stmt->fetch()) {

                    throw new Exception(
                        'O usuário selecionado não existe.'
                    );
                }

                $donoId = $dono;
            }


            /*
            |--------------------------------------------------------------------------
            | INSERT
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                INSERT INTO relatorios (
                    data_relatorio,
                    relatorio,
                    status,
                    projeto,
                    dono
                )
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->bindValue(
                1,
                $data_relatorio !== ''
                    ? $data_relatorio
                    : null,
                $data_relatorio !== ''
                    ? PDO::PARAM_STR
                    : PDO::PARAM_NULL
            );

            if ($arquivo !== null) {

                $stmt->bindValue(
                    2,
                    $arquivo,
                    PDO::PARAM_LOB
                );

            } else {

                $stmt->bindValue(
                    2,
                    null,
                    PDO::PARAM_NULL
                );
            }

            $stmt->bindValue(
                3,
                $status,
                PDO::PARAM_INT
            );

            $stmt->bindValue(
                4,
                $projetoId,
                $projetoId !== null
                    ? PDO::PARAM_INT
                    : PDO::PARAM_NULL
            );

            $stmt->bindValue(
                5,
                $donoId,
                $donoId !== null
                    ? PDO::PARAM_INT
                    : PDO::PARAM_NULL
            );

            $stmt->execute();

            $pdo->commit();

            header('Location: ../Visualizar/relatorios.php');
            exit;

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $erros[] = $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Adicionar relatório</title>

    <link rel="stylesheet"
          href="../assets/css/relatorios.css">

</head>

<body>

<main class="container form-container">

    <header class="page-header">

        <div>

            <h1>Adicionar relatório</h1>

            <p>
                Cadastre um novo relatório.
            </p>

        </div>

        <a
            href="../Visualizar/relatorios.php"
            class="btn btn-secondary"
        >
            ← Voltar
        </a>

    </header>


    <?php if (!empty($erros)): ?>

        <div class="alert alert-error">

            <strong>Verifique os dados:</strong>

            <ul>

                <?php foreach ($erros as $erro): ?>

                    <li>
                        <?= htmlspecialchars($erro) ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>


    <form
        method="POST"
        enctype="multipart/form-data"
        class="form-card"
    >

        <div class="form-grid">

            <div class="form-group">

                <label for="data_relatorio">
                    Data do relatório
                </label>

                <input
                    type="date"
                    id="data_relatorio"
                    name="data_relatorio"
                    value="<?= htmlspecialchars($data_relatorio) ?>"
                >

            </div>

			<br>
            <div class="form-group">

                <label for="status">
                    Status
                </label>

                <select
                    id="status"
                    name="status"
                >

                    <option value="1"
                        <?= $status === 1 ? 'selected' : '' ?>>
                        Em andamento
                    </option>

                    <option value="2"
                        <?= $status === 2 ? 'selected' : '' ?>>
                        Concluído
                    </option>

                    <option value="3"
                        <?= $status === 3 ? 'selected' : '' ?>>
                        Cancelado
                    </option>

                    <option value="4"
                        <?= $status === 4 ? 'selected' : '' ?>>
                        Atrasado
                    </option>

                </select>

            </div>

			<br>
            <div class="form-group">

                <label for="projeto">
                    Projeto
                </label>

                <select
                    id="projeto"
                    name="projeto"
                >

                    <option value="0">
                        Sem projeto
                    </option>

                    <?php foreach ($projetos as $item): ?>

                        <option
                            value="<?= (int)$item['id_projeto'] ?>"
                            <?= $projeto === (int)$item['id_projeto'] ? 'selected' : '' ?>
                        >

                            <?= htmlspecialchars(
                                $item['nome_projeto']
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>
			<br>

            <div class="form-group">

                <label for="dono">
                    Dono do relatório
                </label>

                <select
                    id="dono"
                    name="dono"
                >

                    <option value="0">
                        Sem dono
                    </option>

                    <?php foreach ($usuarios as $usuario): ?>

                        <option
                            value="<?= (int)$usuario['id_usuario'] ?>"
                            <?= $dono === (int)$usuario['id_usuario'] ? 'selected' : '' ?>
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

			<br>
            <div class="form-group full">

                <label for="relatorio">
                    Arquivo do relatório
                </label>

                <input
                    type="file"
                    id="relatorio"
                    name="relatorio"
                >

                <small class="input-help">
                </small>

            </div>

        </div>

			<br>
        <div class="form-actions">

            <a
                href="../Visualizar/relatorios.php"
                class="btn btn-secondary"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="btn btn-primary"
            >
                Salvar relatório
            </button>

        </div>

    </form>

</main>

</body>

</html>