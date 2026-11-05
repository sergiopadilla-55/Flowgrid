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


/*
|--------------------------------------------------------------------------
| Filtros
|--------------------------------------------------------------------------
*/

$filtroStatus = $_GET['status'] ?? '';
$filtroProjeto = (int)($_GET['projeto'] ?? 0);


/*
|--------------------------------------------------------------------------
| Projetos para o filtro
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
| Consulta dos relatórios
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        r.id_relatorio,
        r.data_relatorio,
        r.status,
        r.projeto,
        r.dono,

        CASE
            WHEN r.relatorio IS NULL THEN 0
            ELSE 1
        END AS possui_arquivo,

        p.nome_projeto,

        u.nome_usuario,
        u.user_usuario

    FROM relatorios r

    LEFT JOIN projetos p
        ON r.projeto = p.id_projeto

    LEFT JOIN usuarios u
        ON r.dono = u.id_usuario

    WHERE 1 = 1
";

$parametros = [];


if (
    $filtroStatus !== ''
    &&
    in_array((int)$filtroStatus, [1, 2, 3, 4], true)
) {

    $sql .= " AND r.status = ? ";

    $parametros[] = (int)$filtroStatus;
}


if ($filtroProjeto > 0) {

    $sql .= " AND r.projeto = ? ";

    $parametros[] = $filtroProjeto;
}


$sql .= "
    ORDER BY
        r.data_relatorio DESC,
        r.id_relatorio DESC
";


$stmt = $pdo->prepare($sql);

$stmt->execute($parametros);

$relatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Indicadores
|--------------------------------------------------------------------------
*/

$totalRelatorios = count($relatorios);

$concluidos = 0;
$andamento = 0;
$cancelados = 0;
$atrasados = 0;

foreach ($relatorios as $relatorio) {

    switch ((int)$relatorio['status']) {

        case 1:
            $andamento++;
            break;

        case 2:
            $concluidos++;
            break;

        case 3:
            $cancelados++;
            break;

        case 4:
            $atrasados++;
            break;
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Relatórios</title>

    <link rel="stylesheet"
          href="../assets/css/relatorios.css">

</head>

<body>

<main class="container">

    <!-- =====================================================
         CABEÇALHO
    ====================================================== -->

    <header class="page-header">

        <div>

            <h1>Relatórios</h1>

            <p>
                Gerencie os relatórios dos projetos.
            </p>

        </div>

        <div class="header-actions">

            <a
                href="../Tdashboard.php"
                class="btn btn-secondary"
            >
                ← Voltar
            </a>

            <a
                href="../adicionar/relatorios.php"
                class="btn btn-primary"
            >
                + Adicionar
            </a>

        </div>

    </header>


    <!-- =====================================================
         INDICADORES
    ====================================================== -->

    <section class="metrics">

        <div class="metric-card">

            <span class="metric-title">
                Total de relatórios
            </span>

            <strong>
                <?= $totalRelatorios ?>
            </strong>

        </div>


        <div class="metric-card">

            <span class="metric-title">
                Em andamento
            </span>

            <strong>
                <?= $andamento ?>
            </strong>

        </div>


        <div class="metric-card">

            <span class="metric-title">
                Concluídos
            </span>

            <strong>
                <?= $concluidos ?>
            </strong>

        </div>


        <div class="metric-card">

            <span class="metric-title">
                Pendências
            </span>

            <strong>
                <?= $cancelados + $atrasados ?>
            </strong>

        </div>

    </section>


    <!-- =====================================================
         FILTROS
    ====================================================== -->

    <section class="filters-card">

        <form method="GET">

            <div class="filter-group">

                <label for="status">
                    Status
                </label>

                <select
                    id="status"
                    name="status"
                >

                    <option value="">
                        Todos os status
                    </option>

                    <option
                        value="1"
                        <?= $filtroStatus === '1' ? 'selected' : '' ?>
                    >
                        Em andamento
                    </option>

                    <option
                        value="2"
                        <?= $filtroStatus === '2' ? 'selected' : '' ?>
                    >
                        Concluído
                    </option>

                    <option
                        value="3"
                        <?= $filtroStatus === '3' ? 'selected' : '' ?>
                    >
                        Cancelado
                    </option>

                    <option
                        value="4"
                        <?= $filtroStatus === '4' ? 'selected' : '' ?>
                    >
                        Atrasado
                    </option>

                </select>

            </div>


            <div class="filter-group">

                <label for="projeto">
                    Projeto
                </label>

                <select
                    id="projeto"
                    name="projeto"
                >

                    <option value="0">
                        Todos os projetos
                    </option>

                    <?php foreach ($projetos as $projeto): ?>

                        <option
                            value="<?= (int)$projeto['id_projeto'] ?>"
                            <?= $filtroProjeto === (int)$projeto['id_projeto'] ? 'selected' : '' ?>
                        >

                            <?= htmlspecialchars(
                                $projeto['nome_projeto']
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>
			<br>

            <div class="filter-actions">
                
                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Aplicar filtros
                </button>
				<br>
                <a
                    href="relatorios.php"
                    class="btn btn-secondary"
                >
                    Limpar 
                </a>


            </div>
			<br>
        </form>

    </section>
			<br>

    <!-- =====================================================
         TABELA
    ====================================================== -->

    <?php if (empty($relatorios)): ?>

        <div class="empty-state">
            <br>

            <div class="empty-icon">
                📊
            </div>
			<br>
            <h2>
                Nenhum relatório encontrado
            </h2>
			<br>
            <p>
                Não existem relatórios correspondentes aos filtros selecionados.
            </p>
			<br>
            <a
                href="../adicionar/relatorios.php"
                class="btn btn-primary"
            >
                Adicionar relatório
            </a>

        </div>

    <?php else: ?>
			<br>
        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Data</th>

                        <th>Projeto</th>

                        <th>Dono</th>

                        <th>Status</th>

                        <th>Arquivo</th>

                        <th>Ações</th>

                    </tr>

                </thead>
                <tbody>

                <?php foreach ($relatorios as $relatorio): ?>

                    <tr>

                        <td>
                            #<?= (int)$relatorio['id_relatorio'] ?>
                        </td>

                        <td>

                            <?php if (!empty($relatorio['data_relatorio'])): ?>

                                <?= date(
                                    'd/m/Y',
                                    strtotime($relatorio['data_relatorio'])
                                ) ?>

                            <?php else: ?>

                                —

                            <?php endif; ?>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $relatorio['nome_projeto']
                                ?: 'Sem projeto'
                            ) ?>

                        </td>

                        <td>

                            <?php if (!empty($relatorio['nome_usuario'])): ?>

                                <strong>
                                    <?= htmlspecialchars(
                                        $relatorio['nome_usuario']
                                    ) ?>
                                </strong>

                                <?php if (!empty($relatorio['user_usuario'])): ?>

                                    <span class="username">
                                        @<?= htmlspecialchars(
                                            $relatorio['user_usuario']
                                        ) ?>
                                    </span>

                                <?php endif; ?>

                            <?php else: ?>

                                <span class="muted">
                                    Sem dono
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>

                            <span class="status <?= classeStatus($relatorio['status']) ?>">

                                <?= htmlspecialchars(
                                    nomeStatus($relatorio['status'])
                                ) ?>

                            </span>

                        </td>

                        <td>

                            <?php if ($relatorio['possui_arquivo']): ?>

                                <a
                                    href="../baixar/relatorios.php?id=<?= (int)$relatorio['id_relatorio'] ?>"
                                    class="file-link"
                                >
                                    ↓ Baixar
                                </a>

                            <?php else: ?>

                                <span class="muted">
                                    Sem arquivo
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>

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