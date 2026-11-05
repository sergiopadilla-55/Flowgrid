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
    exit('Documento inválido.');
}


$stmt = $pdo->prepare("
    SELECT
        id_documento,
        nome_documento,
        arquivo
    FROM documentos
    WHERE id_documento = ?
");

$stmt->execute([$id]);

$documento = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$documento) {
    http_response_code(404);
    exit('Documento não encontrado.');
}


if ($documento['arquivo'] === null) {
    http_response_code(404);
    exit('Este documento não possui um arquivo armazenado.');
}


$arquivo = $documento['arquivo'];


// Tenta descobrir o tipo do arquivo.
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


// Nome utilizado para o download.
$nomeArquivo = trim(
    $documento['nome_documento']
);

if ($nomeArquivo === '') {
    $nomeArquivo = 'documento_' . $id;
}


// Evita caracteres problemáticos no cabeçalho.
$nomeArquivo = preg_replace(
    '/[^\p{L}\p{N}\s._-]/u',
    '',
    $nomeArquivo
);

$nomeArquivo = trim($nomeArquivo);

if ($nomeArquivo === '') {
    $nomeArquivo = 'documento_' . $id;
}


/*
|--------------------------------------------------------------------------
| Envia o arquivo
|--------------------------------------------------------------------------
*/

header('Content-Type: ' . $mime);

header(
    'Content-Disposition: attachment; filename="' .
    str_replace('"', '', $nomeArquivo) .
    '"'
);

header('Content-Length: ' . strlen($arquivo));

header('Cache-Control: private, max-age=0, must-revalidate');

header('Pragma: public');

echo $arquivo;

exit;