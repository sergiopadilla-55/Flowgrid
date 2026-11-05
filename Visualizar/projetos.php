<?php

session_start();

require_once __DIR__ . '/../config/conexao.php';


/*
|--------------------------------------------------------------------------
| Buscar projetos
|--------------------------------------------------------------------------
|
| projetos
|    ↓
| responsavel
|    ↓
| usuarios
|
*/

$sql = "
    SELECT
        p.id_projeto,
        p.nome_projeto,
        p.status_projeto,
        p.responsavel_id,

        u.nome_usuario,
        u.user_usuario

    FROM projetos p

    LEFT JOIN responsavel r
        ON p.responsavel_id = r.responsavel_id

    LEFT JOIN usuarios u
        ON r.usuario_id = u.id_usuario

    ORDER BY p.id_projeto DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Status
|--------------------------------------------------------------------------
*/

function nomeStatus($status)
{
    switch ((int) $status) {

        case 1:
            return 'Em andamento';

        case 2:
            return 'Concluído';

        case 3:
            return 'Cancelado';

        case 4:
            return 'Atrasado';

        default:
            return 'Não definido';
    }
}


function classeStatus($status)
{
    switch ((int) $status) {

        case 1:
            return 'status-andamento';

        case 2:
            return 'status-concluido';

        case 3:
            return 'status-cancelado';

        case 4:
            return 'status-atrasado';

        default:
            return 'status-desconhecido';
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

    <title>Projetos</title>

    <link
        rel="stylesheet"
        href="../assets/css/projetos.css"
    >

</head>

<body>

<main class="container">

    <header class="page-header">

        <div>

            <h1>Projetos</h1>

            <p>
                Visualize e gerencie os projetos cadastrados.
            </p>

        </div>
        
                    <a
                href="../dashboard.php"
                class="btn btn-secondary"
            >
                ← Voltar
            </a>

        <a
            href="../adicionar/projetos.php"
            class="btn btn-primary"
        >
            + Adicionar projeto
        </a>

    </header>


    <section class="table-container">

        <?php if (count($projetos) > 0): ?>

            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Projeto</th>

                        <th>Status</th>

                        <th>Responsável</th>

                        <th>Ações</th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($projetos as $projeto): ?>

                        <tr>

                            <td>

                                #<?= htmlspecialchars(
                                    $projeto['id_projeto']
                                ) ?>

                            </td>


                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $projeto['nome_projeto']
                                        ?? 'Sem nome'
                                    ) ?>

                                </strong>

                            </td>


                            <td>

                                <span
                                    class="status
                                    <?= classeStatus(
                                        $projeto['status_projeto']
                                    ) ?>"
                                >

                                    <?= htmlspecialchars(
                                        nomeStatus(
                                            $projeto['status_projeto']
                                        )
                                    ) ?>

                                </span>

                            </td>


                            <td>

                                <?php if (!empty($projeto['nome_usuario'])): ?>

                                    <div class="responsavel">

                                        <strong>
                                            <?= htmlspecialchars(
                                                $projeto['nome_usuario']
                                            ) ?>
                                        </strong>

                                        <?php if (!empty($projeto['user_usuario'])): ?>

                                            <small>
                                                @<?= htmlspecialchars(
                                                    $projeto['user_usuario']
                                                ) ?>
                                            </small>

                                        <?php endif; ?>

                                    </div>

                                <?php else: ?>

                                    <span class="empty">
                                        Sem responsável
                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <div class="actions">

                                    <a
                                        href="../editar/projetos.php?id=<?= $projeto['id_projeto'] ?>"
                                        class="btn btn-edit"
                                    >
                                        Editar
                                    </a>

                                    <a
                                        href="../excluir/projetos.php?id=<?= $projeto['id_projeto'] ?>"
                                        class="btn btn-delete"
                                    >
                                        Excluir
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="empty-state">

                <h2>Nenhum projeto cadastrado</h2>

                <p>
                    Ainda não existem projetos registrados no sistema.
                </p>

                <a
                    href="../adicionar/projetos.php"
                    class="btn btn-primary"
                >
                    Adicionar primeiro projeto
                </a>

            </div>

        <?php endif; ?>

    </section>

</main>

</body>

</html>