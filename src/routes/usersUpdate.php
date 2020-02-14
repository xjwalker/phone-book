<?php

header('Access-Control-Allowed-Origin: *');
header('Content-Type: Application/json');

include_once '../config/Database.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$user->id = $_POST['id'];
$user->first_name = $_POST['first_name'];
$user->last_name = $_POST['last_name'];

$r = $user->update();

$r = $r == false ? ['data' => []] : $r;
echo json_encode($r);