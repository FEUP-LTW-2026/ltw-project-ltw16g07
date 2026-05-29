<?php

$db_path = __DIR__ . '/../database/fithub.db';

try {
    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("Erro na conexão à base de dados: " . $e->getMessage());
}



//BLINDAGEM 1: PROTEÇÃO CONTRA XSS 

if (!function_exists('h')) {
    function h($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}


//BLINDAGEM 2: SISTEMA ANTI-CSRF DETALHADO


// Garante que a sessão está ativa para ler/gravar tokens
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cria um token CSRF único se ainda não existir na sessão
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Função para imprimir o input oculto nos formulários HTML
if (!function_exists('csrf_input')) {
    function csrf_input() {
        echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    }
}

// Função para validar o token enviado em requisições POST
if (!function_exists('validate_csrf')) {
    function validate_csrf() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token_recebido = $_POST['csrf_token'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'], $token_recebido)) {
                // Bloqueia imediatamente o ataque silencioso
                http_response_code(403);
                die("Erro de Segurança: Validação CSRF falhou. Pedido recusado.");
            }
        }
    }
}