<?php

require "vendor/autoload.php"; 

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

include "database.php";



$jwt_secret = '35da42520b3ec0c78cc6f413276e90b4f7b4dacce12dcd74d8a705045b026883';


$method = $_SERVER["REQUEST_METHOD"];
$request = explode("/", trim($_SERVER["PATH_INFO"], "/"));


$resource = $request[0];



header("Content-Type: application/json");


function generate_jwt($username, $secret)
{
    $payload = [
        "iss" => "localhost",
        "aud" => "localhost",
        "iat" => time(),
        "exp" => time() + 3600,
        "username" => $username,
    ];
    return JWT::encode($payload, $secret, "HS256");
}



if ($resource === "register") {
    if ($method === "POST") {
        $input = json_decode(file_get_contents("php://input"), true);

        if (
            !empty($input["name"]) &&
            !empty($input["email"]) &&
            !empty($input["password"])
        ) {
            $name = $input["name"];
            $email = $input["email"];
            $password = md5($input["password"]);
            $query =
                "SELECT COUNT(*) as count FROM users WHERE email_id  = '" .
                $email .
                "' ";

            $result_c = mysqli_query($conn, $query);
            $row_c = mysqli_fetch_assoc($result_c);
            if ($row_c["count"] == 0) {
                $sql_l =
                    "INSERT INTO users (full_name,email_id ,password,created_on) VALUES ('" .
                    $name .
                    "','" .
                    $email .
                    "','" .
                    $password .
                    "',NOW())";
                if (!$conn->query($sql_l)) {
                    http_response_code(401);
                    echo json_encode([
                        "status" => "error",
                        "msg" => "something went wrong.",
                    ]);
                    exit();
                } else {
                    http_response_code(200);
                    echo json_encode([
                        "status" => "ok",
                        "msg" => "Regusterated Successfully.",
                    ]);
                    exit();
                }
            } else {
                http_response_code(401);
                echo json_encode([
                    "status" => "error",
                    "msg" => "This Email Id already exist.",
                ]);
            }
        } else {
            http_response_code(401);
            echo json_encode(["message" => "Please Requird Field"]);
        }
    } else {
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
    }
    exit();
}


if ($resource === "auth") {
    if ($method === "POST") {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!empty($input["email"]) && !empty($input["password"])) {
            $email = $input["email"];

            $md5password = md5($input["password"]);
            $query =
                "SELECT * FROM users WHERE email_id  = '" .
                $email .
                "' AND password  = '" .
                $md5password .
                "'";

            $result_c = mysqli_query($conn, $query);

            if ($result_c->num_rows > 0) {
                $row_c = mysqli_fetch_assoc($result_c);
                $token = generate_jwt(
                    $row_c["email_id"],
                    $GLOBALS["jwt_secret"]
                );
                echo json_encode(["token" => $token]);
                exit();
            }
			else {
				http_response_code(401);
				echo json_encode(["message" => "Invalid credentials"]);
			}
        } else {
            http_response_code(401);
            echo json_encode(["message" => "Invalid credentials"]);
        }
    } else {
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
    }
    exit();
}



