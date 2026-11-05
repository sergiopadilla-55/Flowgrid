<?php

session_start();

require_once __DIR__ . '/../config/conexao.php';

if (empty($_SESSION['id_usuario'])) {
    header('Location: ../index.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    exit('Relatório inválido.');
}


$stmt = $pdo->prepare("
    SELECT
        id_relatorio,
        data_relatorio,
        relatorio
    FROM relatorios
    WHERE id_relatorio = ?
");

$stmt->execute([$id]);

$relatorio = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$relatorio) {
    http_response_code(404);
    exit('Relatório não encontrado.');
}


if ($relatorio['relatorio'] === null) {
    http_response_code(404);
    exit('Este relatório não possui um arquivo armazenado.');
}


$arquivo = $relatorio['relatorio'];


/*
|--------------------------------------------------------------------------
| Detecta MIME
|--------------------------------------------------------------------------
*/

$mime = 'application/octet-stream';

if (function_exists('finfo_open')) {

    $finfo = finfo_open(FILEINFO_MIME_TYPE);

    if ($finfo) {

        $tipoDetectado = finfo_buffer(
            $finfo,
            $arquivo
        );

        if ($tipoDetectado) {
            $mime = $tipoDetectado;
        }

        finfo_close($finfo);
    }
}


/*
|--------------------------------------------------------------------------
| Nome do arquivo
|--------------------------------------------------------------------------
*/

$nomeArquivo = 'relatorio_' . $id;

if (!empty($relatorio['data_relatorio'])) {

    $nomeArquivo .= '_' .
        date(
            'Y-m-d',
            strtotime($relatorio['data_relatorio'])
        );
}


/*
|--------------------------------------------------------------------------
| Download
|--------------------------------------------------------------------------
*/

header('Content-Type: ' . $mime);

header(
    'Content-Disposition: attachment; filename="' .
    $nomeArquivo .
    '"'
);

header(
    'Content-Length: ' .
    strlen($arquivo)
);

header(
    'Cache-Control: private, max-age=0, must-revalidate'
);

header('Pragma: public');

echo $arquivo;

exit;