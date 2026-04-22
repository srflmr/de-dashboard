<?php
include_once "db_config.php";

$nonce = base64_encode(random_bytes(16));

header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-" . $nonce . "'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; form-action 'self'; base-uri 'self'; object-src 'none';");

$act = isset($_GET["act"]) ? $_GET["act"] : "";

$requestMethod = isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : "GET";
if ($requestMethod === "POST") {
    $p = isset($_GET["p"]) ? $_GET["p"] : "";
    $tab = isset($_GET["tab"]) ? $_GET["tab"] : "siem";

    if ($act === "settings") {
        if ($tab === "siem" && ($p === "s1" || $p === "s2" || $p === "del_siem")) {
            include "pages/settings_siem.php";
        } elseif ($tab === "customer" && ($p === "s3" || $p === "s4" || $p === "del_cust")) {
            include "pages/settings_customer.php";
        }
    } elseif ($act === "rule_form" && $p === "save_rule") {
        include "pages/rule_form.php";
    } elseif ($act === "rules" && $p === "del_rule") {
        include "pages/rules.php";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php
        if ($act === "rules" || $act === "rule_form") echo "Detection Rules - DE Console";
        elseif ($act === "settings") echo "Settings - DE Console";
        else echo "Dashboard - DE Console";
    ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
</head>
<body>
    <div class="app-layout">

        <?php include_once "includes/header.php"; ?>

        <main class="main-content">
            <?php
            if ($act === "rules") {
                include "pages/rules.php";
            } elseif ($act === "rule_form") {
                include "pages/rule_form.php";
            } elseif ($act === "settings") {
                include "pages/settings.php";
            } else {
                include "pages/dashboard.php";
            }
            ?>
        </main>

    </div>

    <script src="js/app.js"></script>
</body>
</html>