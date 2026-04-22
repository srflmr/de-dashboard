<?php
// Copy this file to db_config.php and fill in your credentials.
ini_set("display_errors", 0);
ini_set("log_errors", 1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

session_start([
    "cookie_httponly" => true,
    "cookie_secure"   => $isHttps,
    "cookie_samesite" => "Strict",
    "use_strict_mode" => true,
]);

$dbhost = "localhost";
$dbuser = "USERNAME";
$dbpass = "PASSWORD";
$dbname = "DATABASE_NAME";

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$conn) {
    error_log("DB Connection Error: " . mysqli_connect_error());
    die("<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Error</title></head><body><p>Connection error. Please try again.</p></body></html>");
}
mysqli_set_charset($conn, "utf8mb4");

include_once "includes/functions.php";
?>
