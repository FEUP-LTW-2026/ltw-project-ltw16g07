<?php

$db_path = __DIR__ . '/../database/fithub.db';

try {
    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("Erro na conexão à base de dados: " . $e->getMessage());
}
