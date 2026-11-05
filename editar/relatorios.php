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
    SELECT *
    FROM relatorios
    WHERE id_relatorio = ?
");

$stmt->execute([$id]);

$relatorio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$relatorio) {
    header('Location: ../Visualizar/relatorios.php');
    exit;
}


$data_relatorio = $relatorio['data_relatorio'];
$status = (int)$relatorio['status'];
$projeto = (int)($relatorio['projeto'] ?? 0);
$dono = (int)($relatorio['dono'] ?? 0);

$erros = [];


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
| Atualização
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data_relatorio = $_POST['data_relatorio'] ?? '';
    $status = (int)($_POST['status'] ?? 1);
    $projeto = (int)($_POST['projeto'] ?? 0);
    $dono = (int)($_POST['dono'] ?? 0);


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
    | Novo arquivo
    |--------------------------------------------------------------------------
    */

    $novoArquivo = null;
    $trocarArquivo = false;

    if (
        isset($_FILES['relatorio'])
        &&
        $_FILES['relatorio']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['relatorio']['error'] !== UPLOAD_ERR_OK) {

            $erros[] =
                'Erro ao enviar o novo relatório.';

        } else {

            $novoArquivo = file_get_contents(
                $_FILES['relatorio']['tmp_name']
            );

            if ($novoArquivo === false) {

                $erros[] =
                    'Não foi possível ler o novo arquivo.';

            } else {

                $trocarArquivo = true;
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
            | Atualiza mantendo arquivo
            |--------------------------------------------------------------------------
            */

            if (!$trocarArquivo) {

                $stmt = $pdo->prepare("
                    UPDATE relatorios
                    SET
                        data_relatorio = ?,
                        status = ?,
                        projeto = ?,
                        dono = ?
                    WHERE id_relatorio = ?
                ");

                $stmt->execute([
                    $data_relatorio !== ''
                        ? $data_relatorio
                        : null,
                    $status,
                    $projetoId,
                    $donoId,
                    $id
                ]);

            } else {

                /*
                |--------------------------------------------------------------------------
                | Atualiza substituindo arquivo
                |--------------------------------------------------------------------------
                */

                $stmt = $pdo->prepare("
                    UPDATE relatorios
                    SET
                        data_relatorio = ?,
                        relatorio = ?,
                        status = ?,
                        projeto = ?,
                        dono = ?
                    WHERE id_relatorio = ?
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

                $stmt->bindValue(
                    2,
                    $novoArquivo,
                    PDO::PARAM_LOB
                );

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

                $stmt->bindValue(
                    6,
                    $id,
                    PDO::PARAM_INT
                );

                $stmt->execute();
            }


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

    <title>Editar relatório</title>

    <link rel="stylesheet"
          href="../assets/css/relatorios.css">

</head>

<body>

<main class="container form-container">

    <header class="page-header">

        <div>

            <h1>Editar relatório</h1>

            <p>
                Altere as informações do relatório.
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
                    value="<?= htmlspecialchars($data_relatorio ?? '') ?>"
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
            <div class="file-current">

                <strong>
                    Arquivo atual
                </strong>

                <?php if (!empty($relatorio['relatorio'])): ?>

                    <a
                        href="../baixar/relatorios.php?id=<?= $id ?>"
                        class="file-link"
                    >
                        ↓ Baixar relatório atual
                    </a>

                    <p>
                        Selecionar outro arquivo substituirá o atual.
                    </p>

                <?php else: ?>

                    <span class="muted">
                        Nenhum arquivo armazenado.
                    </span>

                <?php endif; ?>

            </div>

			<br>
            <div class="form-group full">

                <label for="relatorio">
                    Substituir arquivo
                </label>

                <input
                    type="file"
                    id="relatorio"
                    name="relatorio"
                >

                <small class="input-help">
                    Deixe vazio para manter o arquivo atual.
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
                Salvar alterações
            </button>

        </div>

    </form>

</main>

</body>

</html>