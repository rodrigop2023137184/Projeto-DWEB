<?php

$host = 'localhost';
$usuario = 'CRC'; 
$password = 'crc'; 
$database = 'crcdatabase'; 

$conn = new mysqli($host, $usuario, $password, $database);


if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}


$conn->set_charset("utf8mb4");
?>