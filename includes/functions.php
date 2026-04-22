<?php

function scriptNonce() {
    return isset($GLOBALS['nonce']) ? ' nonce="' . $GLOBALS['nonce'] . '"' : '';
}

function sanitizeString($input, $maxLength = 255) {
    $input = trim($input);
    $input = strip_tags($input);
    if (strlen($input) > $maxLength) {
        $input = substr($input, 0, $maxLength);
    }
    return $input;
}

function validateEnum($value, $allowed) {
    if (in_array($value, $allowed)) {
        return $value;
    }
    return $allowed[0];
}

function validateDate($date) {
    if ($date == "") {
        return date("Y-m-d");
    }
    $d = DateTime::createFromFormat("Y-m-d", $date);
    if ($d && $d->format("Y-m-d") === $date) {
        return $date;
    }
    return date("Y-m-d");
}

function validateDateOrEmpty($date) {
    if ($date == "") {
        return "";
    }
    $d = DateTime::createFromFormat("Y-m-d", $date);
    if ($d && $d->format("Y-m-d") === $date) {
        return $date;
    }
    return "";
}

function generateToken() {
    if (!isset($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

function validateToken($token) {
    return isset($_SESSION["csrf_token"]) && hash_equals($_SESSION["csrf_token"], $token);
}

function requireValidPostToken() {
    $token = isset($_POST["csrf_token"]) ? $_POST["csrf_token"] : "";
    if (!validateToken($token)) {
        echo "
        <script" . scriptNonce() . ">
            alert('" . addslashes("Invalid request.") . "');
            history.back();
        </script>";
        exit;
    }
}

function queryError($conn, $context = "", $e = null) {
    $detail = ($e instanceof Throwable) ? $e->getMessage() : mysqli_error($conn);
    error_log("MySQL Error [$context]: " . $detail);
    echo "
    <script" . scriptNonce() . ">
        alert('" . addslashes("A system error occurred. Please try again.") . "');
        history.back();
    </script>";
    exit;
}

function alertAndBack($message) {
    echo "
    <script" . scriptNonce() . ">
        alert('" . addslashes($message) . "');
        history.back();
    </script>";
    exit;
}

function safeQuery($conn, $sql, $context = "") {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        queryError($conn, $context);
    }
    return $result;
}

function safePrepare($conn, $sql, $context = "") {
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        queryError($conn, $context);
    }
    return $stmt;
}

function alertAndRedirect($message, $url) {
    echo "
    <script" . scriptNonce() . ">
        alert('" . addslashes($message) . "');
        window.location.href = '" . addslashes($url) . "';
    </script>";
    exit;
}

function redirectTo($url) {
    if (headers_sent()) {
        echo "<script" . scriptNonce() . ">window.location.href='" . addslashes($url) . "';</script>";
    } else {
        $statusCode = (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "POST") ? 303 : 302;
        header("Location: " . $url, true, $statusCode);
    }
    exit;
}

function getReturnToUrl($fallbackUrl = "index.php") {
    $returnTo = "";

    if (isset($_POST["return_to"])) {
        $returnTo = $_POST["return_to"];
    } elseif (isset($_GET["return_to"])) {
        $returnTo = $_GET["return_to"];
    }

    $returnTo = trim($returnTo);
    if ($returnTo == "") {
        return $fallbackUrl;
    }

    $returnTo = str_replace(["\r", "\n"], "", $returnTo);

    if (strpos($returnTo, "http://") === 0 || strpos($returnTo, "https://") === 0 || strpos($returnTo, "//") === 0) {
        return $fallbackUrl;
    }

    if ($returnTo[0] == "?") {
        return "index.php" . $returnTo;
    }

    if (strpos($returnTo, "index.php") === 0) {
        return $returnTo;
    }

    if ($returnTo[0] == "/") {
        $qPos = strpos($returnTo, "?");
        if ($qPos !== false) {
            return "index.php?" . substr($returnTo, $qPos + 1);
        }
    }

    return $fallbackUrl;
}
?>