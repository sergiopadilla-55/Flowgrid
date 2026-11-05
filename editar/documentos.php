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


/*
|--------------------------------------------------------------------------
| Busca documento
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        d.*,
        r.usuario_id
    FROM documentos d
    LEFT JOIN responsavel r
        ON d.responsavel = r.responsavel_id
    WHERE d.id_documento = ?
");

$stmt->execute([$id]);

$documento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$documento) {
    header('Location: ../Visualizar/documentos.php');
    exit;
}


$erros = [];

$nome_documento = $documento['nome_documento'];
$prioridade = (int)$documento['prioridade'];
$data_inicio = $documento['data_inicio'];
$data_entrega = $documento['data_entrega'];
$status_documento = (int)$documento['status_documento'];
$descricao = $documento['descricao'];
$responsavel_usuario = (int)($documento['usuario_id'] ?? 0);
$projeto = (int)($documento['projeto'] ?? 0);


/*
|--------------------------------------------------------------------------
| Usuários
|--------------------------------------------------------------------------
*/

$stmtUsuarios = $pdo->query("
    SELECT
        id_usuario,
        nome_usuario,
        user_usuario
    FROM usuarios
    ORDER BY nome_usuario ASC
");

$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Projetos
|--------------------------------------------------------------------------
*/

$stmtProjetos = $pdo->query("
    SELECT
        id_projeto,
        nome_projeto
    FROM projetos
    ORDER BY nome_projeto ASC
");

$projetos = $stmtProjetos->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Atualização
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome_documento = trim($_POST['nome_documento'] ?? '');
    $prioridade = (int)($_POST['prioridade'] ?? 1);
    $data_inicio = $_POST['data_inicio'] ?? '';
    $data_entrega = $_POST['data_entrega'] ?? '';
    $status_documento = (int)($_POST['status_documento'] ?? 1);
    $descricao = trim($_POST['descricao'] ?? '');
    $responsavel_usuario = (int)($_POST['responsavel_usuario'] ?? 0);
    $projeto = (int)($_POST['projeto'] ?? 0);


    /*
    |--------------------------------------------------------------------------
    | Validações
    |--------------------------------------------------------------------------
    */

    if ($nome_documento === '') {
        $erros[] = 'Informe o nome do documento.';
    }

    if (!in_array($prioridade, [1, 2, 3, 4], true)) {
        $erros[] = 'Prioridade inválida.';
    }

    if (!in_array($status_documento, [1, 2, 3, 4], true)) {
        $erros[] = 'Status inválido.';
    }


    /*
    |--------------------------------------------------------------------------
    | Novo arquivo
    |--------------------------------------------------------------------------
    */

    $novoArquivo = null;

    if (
        isset($_FILES['arquivo'])
        &&
        $_FILES['arquivo']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {

            $erros[] = 'Erro ao enviar o novo arquivo.';

        } else {

            $novoArquivo = file_get_contents(
                $_FILES['arquivo']['tmp_name']
            );

            if ($novoArquivo === false) {
                $erros[] = 'Não foi possível ler o novo arquivo.';
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
            | Responsável
            |--------------------------------------------------------------------------
            */

            $responsavelId = null;

            if ($responsavel_usuario > 0) {

                $stmt = $pdo->prepare("
                    SELECT id_usuario
                    FROM usuarios
                    WHERE id_usuario = ?
                ");

                $stmt->execute([
                    $responsavel_usuario
                ]);

                if (!$stmt->fetch()) {

                    throw new Exception(
                        'O usuário selecionado não existe.'
                    );
                }


                $stmt = $pdo->prepare("
                    SELECT responsavel_id
                    FROM responsavel
                    WHERE usuario_id = ?
                    LIMIT 1
                ");

                $stmt->execute([
                    $responsavel_usuario
                ]);

                $responsavelExistente = $stmt->fetch(
                    PDO::FETCH_ASSOC
                );


                if ($responsavelExistente) {

                    $responsavelId =
                        $responsavelExistente['responsavel_id'];

                } else {

                    $stmt = $pdo->prepare("
                        INSERT INTO responsavel (
                            usuario_id
                        )
                        VALUES (?)
                    ");

                    $stmt->execute([
                        $responsavel_usuario
                    ]);

                    $responsavelId =
                        $pdo->lastInsertId();
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Atualização sem trocar arquivo
            |--------------------------------------------------------------------------
            */

            if ($novoArquivo === null) {

                $sql = "
                    UPDATE documentos
                    SET
                        nome_documento = ?,
                        prioridade = ?,
                        data_inicio = ?,
                        data_entrega = ?,
                        status_documento = ?,
                        descricao = ?,
                        responsavel = ?,
                        projeto = ?
                    WHERE id_documento = ?
                ";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    $nome_documento,
                    $prioridade,
                    $data_inicio !== ''
                        ? $data_inicio
                        : null,
                    $data_entrega !== ''
                        ? $data_entrega
                        : null,
                    $status_documento,
                    $descricao !== ''
                        ? $descricao
                        : null,
                    $responsavelId,
                    $projetoId,
                    $id
                ]);

            } else {

                /*
                |--------------------------------------------------------------------------
                | Atualização trocando arquivo
                |--------------------------------------------------------------------------
                */

                $sql = "
                    UPDATE documentos
                    SET
                        nome_documento = ?,
                        prioridade = ?,
                        data_inicio = ?,
                        data_entrega = ?,
                        status_documento = ?,
                        descricao = ?,
                        arquivo = ?,
                        responsavel = ?,
                        projeto = ?
                    WHERE id_documento = ?
                ";

                $stmt = $pdo->prepare($sql);

                $stmt->bindValue(
                    1,
                    $nome_documento,
                    PDO::PARAM_STR
                );

                $stmt->bindValue(
                    2,
                    $prioridade,
                    PDO::PARAM_INT
                );

                $stmt->bindValue(
                    3,
                    $data_inicio !== ''
                        ? $data_inicio
                        : null,
                    $data_inicio !== ''
                        ? PDO::PARAM_STR
                        : PDO::PARAM_NULL
                );

                $stmt->bindValue(
                    4,
                    $data_entrega !== ''
                        ? $data_entrega
                        : null,
                    $data_entrega !== ''
                        ? PDO::PARAM_STR
                        : PDO::PARAM_NULL
                );

                $stmt->bindValue(
                    5,
                    $status_documento,
                    PDO::PARAM_INT
                );

                $stmt->bindValue(
                    6,
                    $descricao !== ''
                        ? $descricao
                        : null,
                    $descricao !== ''
                        ? PDO::PARAM_STR
                        : PDO::PARAM_NULL
                );

                $stmt->bindValue(
                    7,
                    $novoArquivo,
                    PDO::PARAM_LOB
                );

                $stmt->bindValue(
                    8,
                    $responsavelId,
                    $responsavelId !== null
                        ? PDO::PARAM_INT
                        : PDO::PARAM_NULL
                );

                $stmt->bindValue(
                    9,
                    $projetoId,
                    $projetoId !== null
                        ? PDO::PARAM_INT
                        : PDO::PARAM_NULL
                );

                $stmt->bindValue(
                    10,
                    $id,
                    PDO::PARAM_INT
                );

                $stmt->execute();
            }


            $pdo->commit();

            header('Location: ../Visualizar/documentos.php');
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

    <title>Editar documento</title>

    <link rel="stylesheet"
          href="../assets/css/documentos.css">

</head>

<body>

<main class="container form-container">

    <div class="page-header">

        <div>

            <h1>Editar documento</h1>

            <p>
                Altere as informações do documento.
            </p>

        </div>

        <a href="../Visualizar/documentos.php"
           class="btn btn-secondary">
            ← Voltar
        </a>

    </div>


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

            <div class="form-group full">

                <label for="nome_documento">
                    Nome do documento *
                </label>

                <input
                    type="text"
                    id="nome_documento"
                    name="nome_documento"
                    maxlength="100"
                    required
                    value="<?= htmlspecialchars($nome_documento) ?>"
                >

            </div>


            <div class="form-group">

                <label for="prioridade">
                    Prioridade
                </label>

                <select
                    id="prioridade"
                    name="prioridade"
                >

                    <option value="1"
                        <?= $prioridade === 1 ? 'selected' : '' ?>>
                        Baixa
                    </option>

                    <option value="2"
                        <?= $prioridade === 2 ? 'selected' : '' ?>>
                        Média
                    </option>

                    <option value="3"
                        <?= $prioridade === 3 ? 'selected' : '' ?>>
                        Alta
                    </option>

                    <option value="4"
                        <?= $prioridade === 4 ? 'selected' : '' ?>>
                        Urgente
                    </option>

                </select>

            </div>


            <div class="form-group">

                <label for="status_documento">
                    Status
                </label>

                <select
                    id="status_documento"
                    name="status_documento"
                >

                    <option value="1"
                        <?= $status_documento === 1 ? 'selected' : '' ?>>
                        Em andamento
                    </option>

                    <option value="2"
                        <?= $status_documento === 2 ? 'selected' : '' ?>>
                        Concluído
                    </option>

                    <option value="3"
                        <?= $status_documento === 3 ? 'selected' : '' ?>>
                        Cancelado
                    </option>

                    <option value="4"
                        <?= $status_documento === 4 ? 'selected' : '' ?>>
                        Atrasado
                    </option>

                </select>

            </div>


            <div class="form-group">

                <label for="data_inicio">
                    Data de início
                </label>

                <input
                    type="date"
                    id="data_inicio"
                    name="data_inicio"
                    value="<?= htmlspecialchars($data_inicio ?? '') ?>"
                >

            </div>


            <div class="form-group">

                <label for="data_entrega">
                    Data de entrega
                </label>

                <input
                    type="date"
                    id="data_entrega"
                    name="data_entrega"
                    value="<?= htmlspecialchars($data_entrega ?? '') ?>"
                >

            </div>


            <div class="form-group">

                <label for="projeto">
                    Projeto
                </label>

                <select
                    id="projeto"
                    name="projeto"
                >

                    <option value="0">
                        Nenhum projeto
                    </option>

                    <?php foreach ($projetos as $item): ?>

                        <option
                            value="<?= (int)$item['id_projeto'] ?>"
                            <?= $projeto == $item['id_projeto'] ? 'selected' : '' ?>
                        >

                            <?= htmlspecialchars($item['nome_projeto']) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-group">

                <label for="responsavel_usuario">
                    Responsável
                </label>

                <select
                    id="responsavel_usuario"
                    name="responsavel_usuario"
                >

                    <option value="0">
                        Sem responsável
                    </option>

                    <?php foreach ($usuarios as $usuario): ?>

                        <option
                            value="<?= (int)$usuario['id_usuario'] ?>"
                            <?= $responsavel_usuario == $usuario['id_usuario'] ? 'selected' : '' ?>
                        >

                            <?= htmlspecialchars($usuario['nome_usuario']) ?>

                            <?php if (!empty($usuario['user_usuario'])): ?>

                                (@<?= htmlspecialchars($usuario['user_usuario']) ?>)

                            <?php endif; ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-group full">

                <label for="descricao">
                    Descrição
                </label>

                <textarea
                    id="descricao"
                    name="descricao"
                    maxlength="2000"
                    rows="6"
                ><?= htmlspecialchars($descricao ?? '') ?></textarea>

            </div>


            <div class="file-current">

                <strong>Arquivo atual:</strong>

                <?php if (!empty($documento['arquivo'])): ?>

                    <a
                        href="../baixar/projetos.php?id=<?= $id ?>"
                        class="file-link"
                    >
                        ↓ Baixar arquivo atual
                    </a>

                    <p>
                        Escolher outro arquivo irá substituir o arquivo atual.
                    </p>

                <?php else: ?>

                    <span>
                        Nenhum arquivo armazenado.
                    </span>

                <?php endif; ?>

            </div>


            <div class="form-group full">

                <label for="arquivo">
                    Substituir arquivo
                </label>

                <input
                    type="file"
                    id="arquivo"
                    name="arquivo"
                >

                <small class="input-help">
                    Deixe vazio para manter o arquivo atual.
                </small>

            </div>

        </div>


        <div class="form-actions">

            <a
                href="../Visualizar/documentos.php"
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