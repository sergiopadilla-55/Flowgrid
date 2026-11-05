<?php

session_start();

require_once __DIR__ . '/../config/conexao.php';

/*
|--------------------------------------------------------------------------
| Consulta dos usuários
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        u.id_usuario,
        u.nome_usuario,
        u.user_usuario,
        u.ocupacao,
        u.empresa_cod,
        u.equipe_id,
        e.setor,
        emp.id_empresa
    FROM usuarios u
    LEFT JOIN equipes e
        ON u.equipe_id = e.id_equipe
    LEFT JOIN empresa emp
        ON u.empresa_cod = emp.id_empresa
    ORDER BY u.id_usuario DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Usuários</title>

    <link rel="stylesheet" href="../assets/css/usuarios.css">

</head>

<body>

<main class="container">

    <header class="page-header">

        <div>
            <h1>Usuários</h1>

            <p>
                Gerencie os usuários cadastrados no sistema.
            </p>
        </div>
        
            <a
                href="../dashboard.php"
                class="btn btn-secondary"
            >
                ← Voltar
            </a>
        <a href="../cadastro.php" class="btn btn-primary">
            + Adicionar usuário
        </a>

    </header>


    <section class="table-container">

        <?php if (count($usuarios) > 0): ?>

            <table>

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Usuário</th>
                        <th>Ocupação</th>
                        <th>Equipe</th>
                        <th>Empresa</th>
                        <th>Ações</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($usuarios as $usuario): ?>

                        <tr>

                            <td>
                                #<?= htmlspecialchars($usuario['id_usuario']) ?>
                            </td>

                            <td>
                                <strong>
                                    <?= htmlspecialchars($usuario['nome_usuario']) ?>
                                </strong>
                            </td>

                            <td>
                                <?= htmlspecialchars($usuario['user_usuario']) ?>
                            </td>

                            <td>
                                <?= !empty($usuario['ocupacao'])
                                    ? htmlspecialchars($usuario['ocupacao'])
                                    : 'Não informado'
                                ?>
                            </td>

                            <td>

                                <?php if (!empty($usuario['setor'])): ?>

                                    <span class="badge">
                                        <?= htmlspecialchars($usuario['setor']) ?>
                                    </span>

                                <?php else: ?>

                                    <span class="empty">
                                        Sem equipe
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <?php if (!empty($usuario['empresa_cod'])): ?>

                                    Empresa #<?= htmlspecialchars($usuario['empresa_cod']) ?>

                                <?php else: ?>

                                    <span class="empty">
                                        Sem empresa
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <div class="actions">

                                    <a
                                        href="../editar/usuarios.php?id=<?= $usuario['id_usuario'] ?>"
                                        class="btn btn-edit"
                                    >
                                        Editar
                                    </a>

                                    <a
                                        href="../excluir/usuarios.php?id=<?= $usuario['id_usuario'] ?>"
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

                <h2>Nenhum usuário cadastrado</h2>

                <p>
                    Ainda não existem usuários registrados no sistema.
                </p>

                <a href="../cadastro.php" class="btn btn-primary">
                    Adicionar primeiro usuário
                </a>

            </div>

        <?php endif; ?>

    </section>

</main>

</body>

</html>