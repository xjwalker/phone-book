<?php
header('Access-Control-Allowed-Origin: *');
header('Content-Type: Application/json');

include_once '../config/Database.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$user->id = $_GET['id'];
$r = $user->get();

$r = $r == false ? ['data' => []] : $r;
echo json_encode($r);
