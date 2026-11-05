<?php

session_start();
require_once __DIR__ . '/config/conexao.php';
require_once __DIR__ . '/config/configuracoes.php';

  if (empty($_SESSION['id_usuario'])) {
      header('Location: index.php');
      exit;
  }

$nomeUsuario     = $_SESSION['nome_usuario'] ?? 'Gerente';
$ocupacaoUsuario = $_SESSION['ocupacao']     ?? 'Gerente Geral';

/**
 * MAPEAMENTO DE STATUS
 * O banco não tem uma tabela de domínio para status, então os códigos
 * numéricos são mapeados aqui. Ajuste os rótulos/códigos se o seu
 * sistema usar valores diferentes.
 */
$statusProjetoLabels = [
    1 => 'Em andamento',
    2 => 'Concluído',
    3 => 'Cancelado',
    4 => 'Atrasado',
];

$statusDocumentoLabels = [
    1 => 'Pendente',
    2 => 'Em andamento',
    3 => 'Concluído',
    4 => 'Atrasado',
];

/* ---------------------------------------------------------------
 * CARDS DO TOPO
 * ------------------------------------------------------------- */
$totalProjetos        = scalarQuery($pdo, 'SELECT COUNT(*) FROM projetos');
$totalUsuarios        = scalarQuery($pdo, 'SELECT COUNT(*) FROM usuarios');
$totalDocumentos      = scalarQuery($pdo, 'SELECT COUNT(*) FROM documentos');
$projetosFinalizados  = scalarQuery($pdo, 'SELECT COUNT(*) FROM projetos WHERE status_projeto = ?', [2]);
$projetosAtrasados    = scalarQuery($pdo, 'SELECT COUNT(*) FROM projetos WHERE status_projeto = ?', [4]);
$documentosHoje       = scalarQuery($pdo, 'SELECT COUNT(*) FROM documentos WHERE data_inicio = CURDATE()');

/* Badges de crescimento — só ficam disponíveis se a coluna criado_em existir.
   Veja database/migracoes_opcionais.sql para adicioná-la. */
$crescimentoProjetos = null;
if (columnExists($pdo, 'projetos', 'criado_em')) {
    $mesAtual    = scalarQuery($pdo, 'SELECT COUNT(*) FROM projetos WHERE MONTH(criado_em) = MONTH(CURDATE()) AND YEAR(criado_em) = YEAR(CURDATE())');
    $mesAnterior = scalarQuery($pdo, 'SELECT COUNT(*) FROM projetos WHERE MONTH(criado_em) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(criado_em) = YEAR(CURDATE() - INTERVAL 1 MONTH)');
    $crescimentoProjetos = $mesAnterior > 0
        ? (int) round((($mesAtual - $mesAnterior) / $mesAnterior) * 100)
        : ($mesAtual > 0 ? 100 : 0);
}

$usuariosNovos = null;
if (columnExists($pdo, 'usuarios', 'criado_em')) {
    $usuariosNovos = scalarQuery($pdo, 'SELECT COUNT(*) FROM usuarios WHERE criado_em >= (NOW() - INTERVAL 7 DAY)');
}

/* ---------------------------------------------------------------
 * GRÁFICOS
 * ------------------------------------------------------------- */
// "Desempenho dos Projetos" -> distribuição de projetos por status
$distribuicaoProjetos = statusDistribution($pdo, 'projetos', 'status_projeto', $statusProjetoLabels);

// "Status Geral" -> distribuição de documentos por status
$distribuicaoDocumentos = statusDistribution($pdo, 'documentos', 'status_documento', $statusDocumentoLabels);

$jsProjetosLabels = json_encode(array_keys($distribuicaoProjetos), JSON_UNESCAPED_UNICODE);
$jsProjetosValores = json_encode(array_values($distribuicaoProjetos));
$jsDocLabels = json_encode(array_keys($distribuicaoDocumentos), JSON_UNESCAPED_UNICODE);
$jsDocValores = json_encode(array_values($distribuicaoDocumentos));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | Gerenciador de Projetos</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm fixed-top" id="menuNav">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="dashboard.php">
      <i class="bi bi-kanban-fill me-2"></i>FlowGrid
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menuNavbar">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link active" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="Visualizar/projeto.php"><i class="bi bi-folder2-open"></i> Projetos</a></li>
        <li class="nav-item"><a class="nav-link" href="Visualizar/equipe.php"><i class="bi bi-people-fill"></i> Equipes</a></li>
        <li class="nav-item"><a class="nav-link" href="Visualizar/documento.php"><i class="bi bi-file-earmark-text"></i> Documentos</a></li>
        <li class="nav-item"><a class="nav-link" href="Visualizar/relatorio.php"><i class="bi bi-bar-chart-fill"></i> Relatórios</a></li>
        <li class="nav-item"><a class="nav-link text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="main" id="main">

  <div class="topbar">

    <div class="d-flex align-items-center">
      <img src="https://ui-avatars.com/api/?name=<?= urlencode($nomeUsuario) ?>&background=2563eb&color=fff" class="rounded-circle" width="45">
      <div class="ms-3">
        <strong><?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?></strong><br>
        <small class="text-muted"><?= htmlspecialchars($ocupacaoUsuario, ENT_QUOTES, 'UTF-8') ?></small>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-3 mb-4">
      <div class="card-dashboard animate__animated animate__fadeInUp">
        <div class="card-icon"><i class="bi bi-folder-fill"></i></div>
        <h6>Total de Projetos</h6>
        <h3><?= $totalProjetos ?></h3>
        <?php if ($crescimentoProjetos !== null): ?>
          <span class="badge <?= $crescimentoProjetos >= 0 ? 'badge-up' : 'badge-danger' ?>">
            <?= $crescimentoProjetos >= 0 ? '+' : '' ?><?= $crescimentoProjetos ?>% este mês
          </span>
        <?php else: ?>
          <span class="badge badge-info">Atualizado agora</span>
        <?php endif; ?>
      </div>
    </div>

    <div class="col-lg-3 mb-4">
      <div class="card-dashboard animate__animated animate__fadeInUp">
        <div class="card-icon"><i class="bi bi-people-fill"></i></div>
        <h6>Usuários</h6>
        <h3><?= $totalUsuarios ?></h3>
        <?php if ($usuariosNovos !== null): ?>
          <span class="badge badge-info"><?= $usuariosNovos ?> novos</span>
        <?php else: ?>
          <span class="badge badge-info">Atualizado agora</span>
        <?php endif; ?>
      </div>
    </div>

    <div class="col-lg-3 mb-4">
      <div class="card-dashboard animate__animated animate__fadeInUp">
        <div class="card-icon"><i class="bi bi-file-earmark-text"></i></div>
        <h6>Documentos</h6>
        <h3><?= $totalDocumentos ?></h3>
        <span class="badge badge-warning"><?= $documentosHoje ?> hoje</span>
      </div>
    </div>

    <div class="col-lg-3 mb-4">
      <div class="card-dashboard animate__animated animate__fadeInUp">
        <div class="card-icon"><i class="bi bi-check-circle-fill"></i></div>
        <h6>Projetos Finalizados</h6>
        <h3><?= $projetosFinalizados ?></h3>
        <span class="badge badge-danger"><?= $projetosAtrasados ?> atrasados</span>
      </div>
    </div>
  </div>

  <h3 class="section-title">Visão Geral</h3>

  <div class="row">
    <div class="col-lg-8">
      <div class="widget">
        <h5>Desempenho dos Projetos</h5>
        <div id="graficoProjetos" style="height:350px;"></div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="widget">
        <h5>Status Geral</h5>
        <div id="graficoStatus" style="height:350px;"></div>
      </div>
    </div>
  </div>

  <footer class="text-center mt-4 mb-2 text-muted">FlowGrid © 2026</footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
<script>
  const projetosLabels  = <?= $jsProjetosLabels ?>;
  const projetosValores = <?= $jsProjetosValores ?>;
  const docLabels       = <?= $jsDocLabels ?>;
  const docValores      = <?= $jsDocValores ?>;

  new ApexCharts(document.querySelector('#graficoProjetos'), {
    chart: { type: 'bar', height: 350, toolbar: { show: false } },
    series: [{ name: 'Projetos', data: projetosValores }],
    xaxis: { categories: projetosLabels },
    colors: ['#2563eb'],
    plotOptions: { bar: { borderRadius: 6, columnWidth: '45%' } },
    dataLabels: { enabled: false }
  }).render();

  new ApexCharts(document.querySelector('#graficoStatus'), {
    chart: { type: 'donut', height: 350 },
    series: docValores,
    labels: docLabels,
    colors: ['#f59e0b', '#2563eb', '#22c55e', '#ef4444'],
    legend: { position: 'bottom' }
  }).render();
</script>

</body>
</html>