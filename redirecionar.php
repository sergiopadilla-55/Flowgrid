<?php

session_start();

require_once __DIR__ . '/../config/conexao.php';

/*
|--------------------------------------------------------------------------
| Verifica se o formulário foi enviado
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Recebe os dados do formulário
|--------------------------------------------------------------------------
*/

$usuario = trim($_POST['usuario'] ?? '');
$senha   = $_POST['senha'] ?? '';


/*
|--------------------------------------------------------------------------
| Validação básica
|--------------------------------------------------------------------------
*/

if ($usuario === '' || $senha === '') {
    header('Location: index.php?erro=preencha');
    exit;
}


/*
|--------------------------------------------------------------------------
| Busca o usuário no banco
|--------------------------------------------------------------------------
*/

try {

    $sql = "SELECT
                id_usuario,
                nome_usuario,
                user_usuario,
                senha_usuario,
                ocupacao,
                empresa_cod,
                equipe_id
            FROM usuarios
            WHERE user_usuario = :usuario
            LIMIT 1";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':usuario' => $usuario
    ]);

    $usuarioBanco = $stmt->fetch(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | Verifica usuário e senha
    |--------------------------------------------------------------------------
    */

    if (!$usuarioBanco || !password_verify($senha, $usuarioBanco['senha_usuario'])) {

        header('Location: index.php?erro=login');
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Regenera o ID da sessão
    |--------------------------------------------------------------------------
    */

    session_regenerate_id(true);


    /*
    |--------------------------------------------------------------------------
    | Cria os dados da sessão
    |--------------------------------------------------------------------------
    */

    $_SESSION['id_usuario']   = $usuarioBanco['id_usuario'];
    $_SESSION['nome_usuario'] = $usuarioBanco['nome_usuario'];
    $_SESSION['user_usuario'] = $usuarioBanco['user_usuario'];
    $_SESSION['ocupacao']     = $usuarioBanco['ocupacao'];
    $_SESSION['empresa_cod']  = $usuarioBanco['empresa_cod'];
    $_SESSION['equipe_id']    = $usuarioBanco['equipe_id'];


    /*
    |--------------------------------------------------------------------------
    | Redirecionamento de acordo com a ocupação
    |--------------------------------------------------------------------------
    */

    switch ((int) $usuarioBanco['ocupacao']) {

        case 1:
            // Administrador
            header('Location: dashboard.php');
            exit;


        case 2:
            // Gerente
            header('Location: Gdashboard.php');
            exit;


        case 3:
            // Usuário comum
            header('Location: Tdashboard.php');
            exit;


        default:
            // Ocupação inválida
            session_unset();
            session_destroy();

            header('Location: index.php?erro=ocupacao');
            exit;
    }


} catch (PDOException $e) {

    /*
    |--------------------------------------------------------------------------
    | Erro de banco de dados
    |--------------------------------------------------------------------------
    */

    header('Location: index.php?erro=banco');
    exit;
}
```
