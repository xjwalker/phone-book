<?php
header('Access-Control-Allowed-Origin: *');
header('Content-Type: Application/json');

include_once '../config/Database.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$user->id = $_GET['id'];
$user->first_name = $_GET['last_name'];
$user->last_name = $_GET['last_name'];
$user->emails = $_GET['email'];
$user->phone_numbers = $_GET['phone_number'];

$r = $user->get();

$r = $r == false ? ['data' => []] : $r;
echo json_encode($r);
