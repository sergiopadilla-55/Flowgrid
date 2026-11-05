<?php
$host = "sql209.infinityfree.com";
$banco = "if0_42685142_flowgrid"; 
$usuario = "if0_42685142";
$senha = "kEZaO6qu4Sjo";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // O 'echo' que mostrava a mensagem foi removido daqui!
} catch (PDOException $e) {
    die("Erro crítico de ligação à base de dados: " . $e->getMessage());
}
?>