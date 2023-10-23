<?php 
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require 'vendor/autoload.php';
Flight::register('db', 'PDO', array($_ENV['MYSQL_DB_URL']), function($db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
});

Flight::route('POST /auth', function() {

    $db = Flight::db();
    $email = Flight::request()->data->email;
    $password = Flight::request()->data->password;
    $query = $db->prepare('SELECT * FROM users WHERE email = :email AND password = :password');
    $array = [
        "error" => "We could not validate your identity",
        "status" => "error"
    ];

    if ($query->execute([':email' => $email, ':password' => $password])) {
        $user = $query->fetch(PDO::FETCH_ASSOC);
        $now = strtotime("now");
        $key = $_ENV['JWT_SECRET_KEY'];
        $payload = [
            'exp' => $now + 3600,
            'data' => $user['id']
        ];
    
        $jwt = JWT::encode($payload, $key, 'HS256');
        $array = ["token" => $jwt];
    }

    Flight::json($array);    
});


?>