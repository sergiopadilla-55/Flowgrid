<?php

session_start();

require_once __DIR__ . '/../config/conexao.php';

if (empty($_SESSION['id_usuario'])) {
    header('Location: ../index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Funções auxiliares
|--------------------------------------------------------------------------
*/

function nomeStatus($status)
{
    return match ((int)$status) {
        1 => 'Em andamento',
        2 => 'Concluído',
        3 => 'Cancelado',
        4 => 'Atrasado',
        default => 'Desconhecido'
    };
}

function classeStatus($status)
{
    return match ((int)$status) {
        1 => 'status-andamento',
        2 => 'status-concluido',
        3 => 'status-cancelado',
        4 => 'status-atrasado',
        default => 'status-desconhecido'
    };
}

function nomePrioridade($prioridade)
{
    return match ((int)$prioridade) {
        1 => 'Baixa',
        2 => 'Média',
        3 => 'Alta',
        4 => 'Urgente',
        default => 'Não definida'
    };
}

function classePrioridade($prioridade)
{
    return match ((int)$prioridade) {
        1 => 'prioridade-baixa',
        2 => 'prioridade-media',
        3 => 'prioridade-alta',
        4 => 'prioridade-urgente',
        default => 'prioridade-desconhecida'
    };
}

/*
|--------------------------------------------------------------------------
| Busca dos documentos
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        d.id_documento,
        d.nome_documento,
        d.prioridade,
        d.data_inicio,
        d.data_entrega,
        d.status_documento,
        d.descricao,
        d.responsavel,
        d.projeto,
        CASE
            WHEN d.arquivo IS NULL THEN 0
            ELSE 1
        END AS possui_arquivo,

        u.nome_usuario AS nome_responsavel,
        p.nome_projeto

    FROM documentos d

    LEFT JOIN responsavel r
        ON d.responsavel = r.responsavel_id

    LEFT JOIN usuarios u
        ON r.usuario_id = u.id_usuario

    LEFT JOIN projetos p
        ON d.projeto = p.id_projeto

    ORDER BY d.id_documento DESC
";

$stmt = $pdo->query($sql);
$documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Documentos</title>

    <link rel="stylesheet"
          href="../assets/css/documentos.css">

</head>

<body>

<main class="container">

    <header class="page-header">

        <div>

            <h1>Documentos</h1>

            <p>
                Gerencie os documentos relacionados aos projetos.
            </p>

        </div>

        <div class="header-actions">

            <a href="../dashboard.php"
               class="btn btn-secondary">
                ← Voltar
            </a>

            <a href="../adicionar/documentos.php"
               class="btn btn-primary">
                + Adicionar
            </a>

        </div>

    </header>


    <?php if (empty($documentos)): ?>

        <div class="empty-state">

            <div class="empty-icon">📄</div>

            <h2>Nenhum documento cadastrado</h2>

            <p>
                Ainda não existem documentos cadastrados no sistema.
            </p>

            <a href="../adicionar/documentos.php"
               class="btn btn-primary">
                Adicionar documento
            </a>

        </div>

    <?php else: ?>

        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Documento</th>

                        <th>Projeto</th>

                        <th>Responsável</th>

                        <th>Prioridade</th>

                        <th>Entrega</th>

                        <th>Status</th>

                        <th>Arquivo</th>

                        <th>Ações</th>

                    </tr>

                </thead>

                <tbody>

                <?php foreach ($documentos as $documento): ?>

                    <tr>

                        <td>
                            #<?= htmlspecialchars($documento['id_documento']) ?>
                        </td>

                        <td>

                            <strong>
                                <?= htmlspecialchars(
                                    $documento['nome_documento'] ?: 'Sem nome'
                                ) ?>
                            </strong>

                            <?php if (!empty($documento['descricao'])): ?>

                                <span class="descricao">

                                    <?= htmlspecialchars(
                                        mb_strimwidth(
                                            $documento['descricao'],
                                            0,
                                            80,
                                            '...'
                                        )
                                    ) ?>

                                </span>

                            <?php endif; ?>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $documento['nome_projeto']
                                ?: 'Sem projeto'
                            ) ?>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $documento['nome_responsavel']
                                ?: 'Sem responsável'
                            ) ?>

                        </td>

                        <td>

                            <span class="prioridade <?= classePrioridade($documento['prioridade']) ?>">

                                <?= htmlspecialchars(
                                    nomePrioridade($documento['prioridade'])
                                ) ?>

                            </span>

                        </td>

                        <td>

                            <?php

                            if (!empty($documento['data_entrega'])) {

                                echo date(
                                    'd/m/Y',
                                    strtotime($documento['data_entrega'])
                                );

                            } else {

                                echo '—';

                            }

                            ?>

                        </td>

                        <td>

                            <span class="status <?= classeStatus($documento['status_documento']) ?>">

                                <?= htmlspecialchars(
                                    nomeStatus($documento['status_documento'])
                                ) ?>

                            </span>

                        </td>

                        <td>

                            <?php if ($documento['possui_arquivo']): ?>

                                <a
                                    href="../baixar/documentos.php?id=<?= (int)$documento['id_documento'] ?>"
                                    class="file-link"
                                >
                                    ↓ Baixar
                                </a>

                            <?php else: ?>

                                <span class="sem-arquivo">
                                    Sem arquivo
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>

                            <div class="actions">

                                <a
                                    href="../editar/documentos.php?id=<?= (int)$documento['id_documento'] ?>"
                                    class="action-edit"
                                >
                                    Editar
                                </a>

                                <a
                                    href="../excluir/documentos.php?id=<?= (int)$documento['id_documento'] ?>"
                                    class="action-delete"
                                >
                                    Excluir
                                </a>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</main>

</body>

</html>