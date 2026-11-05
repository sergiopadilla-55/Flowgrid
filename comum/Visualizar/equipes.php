<?php

session_start();

require_once __DIR__ . '/../config/conexao.php';


/*
|--------------------------------------------------------------------------
| Buscar equipes
|--------------------------------------------------------------------------
|
| COUNT() também contabiliza quantos usuários estão associados
| a cada equipe.
|
*/

$sql = "
    SELECT
        e.id_equipe,
        e.setor,
        e.descricao_equipe,
        COUNT(u.id_usuario) AS total_usuarios

    FROM equipes e

    LEFT JOIN usuarios u
        ON u.equipe_id = e.id_equipe

    GROUP BY
        e.id_equipe,
        e.setor,
        e.descricao_equipe

    ORDER BY e.id_equipe DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$equipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Equipes</title>

    <link rel="stylesheet" href="../assets/css/equipes.css">

</head>

<body>

<main class="container">

    <header class="page-header">

        <div>

            <h1>Equipes</h1>

            <p>
                Visualize e gerencie as equipes cadastradas no sistema.
            </p>

        </div>
                    <a
                href="../Tdashboard.php"
                class="btn btn-secondary"
            >
                ← Voltar
            </a>

    </header>


    <section class="table-container">

        <?php if (count($equipes) > 0): ?>

            <table>

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Setor</th>
                        <th>Descrição</th>
                        <th>Usuários</th>
                        <th>Ações</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($equipes as $equipe): ?>

                        <tr>

                            <td>
                                #<?= htmlspecialchars(
                                    $equipe['id_equipe']
                                ) ?>
                            </td>

                            <td>

                                <strong>
                                    <?= htmlspecialchars(
                                        $equipe['setor']
                                    ) ?>
                                </strong>

                            </td>

                            <td>

                                <?php if (!empty($equipe['descricao_equipe'])): ?>

                                    <?= nl2br(
                                        htmlspecialchars(
                                            $equipe['descricao_equipe']
                                        )
                                    ) ?>

                                <?php else: ?>

                                    <span class="empty">
                                        Sem descrição
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <span class="badge">

                                    <?= htmlspecialchars(
                                        $equipe['total_usuarios']
                                    ) ?>

                                    <?= $equipe['total_usuarios'] == 1
                                        ? 'usuário'
                                        : 'usuários'
                                    ?>

                                </span>

                            </td>

                            <td>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        <?php else: ?>

        <?php endif; ?>

    </section>

</main>

</body>

</html>