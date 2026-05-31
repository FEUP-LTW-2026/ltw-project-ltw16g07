<?php

// __DIR__ já aponta para a pasta config/, por isso basta acrescentar o nome do ficheiro!
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

if (!function_exists('paginate')) {
    function paginate(PDO $db, string $count_sql, string $select_sql, array $params, int $per_page = 10): array
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $stmt = $db->prepare($count_sql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $total_pages = max(1, (int) ceil($total / $per_page));
        $page = min($page, $total_pages);
        $offset = ($page - 1) * $per_page;

        $stmt = $db->prepare($select_sql . " LIMIT $per_page OFFSET $offset");
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'items'        => $items,
            'current_page' => $page,
            'total_pages'  => $total_pages,
            'total'        => $total,
            'per_page'     => $per_page,
        ];
    }
}

if (!function_exists('build_pagination_query_string')) {
    function build_pagination_query_string(array $exclude = ['page']): string
    {
        $parts = [];
        foreach ($_GET as $key => $value) {
            if (!in_array($key, $exclude) && $value !== '') {
                $parts[] = urlencode($key) . '=' . urlencode($value);
            }
        }
        return implode('&', $parts);
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