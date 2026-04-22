<?php
$tab = isset($_GET["tab"]) ? $_GET["tab"] : "siem";
$p = isset($_GET["p"]) ? $_GET["p"] : "";
?>

<div class="breadcrumb">
    <a href="index.php?act=settings">Settings</a>
    <span class="separator">/</span>
    <?php echo ($tab === "customer") ? "Customers" : "SIEM Platforms"; ?>
</div>

<div class="settings-tabs">
    <a href="index.php?act=settings&tab=siem" class="settings-tab <?php echo ($tab === "siem") ? "active" : ""; ?>">SIEM Platforms</a>
    <a href="index.php?act=settings&tab=customer" class="settings-tab <?php echo ($tab === "customer") ? "active" : ""; ?>">Customers</a>
</div>

<?php
if ($tab === "customer") {
    include "pages/settings_customer.php";
} else {
    include "pages/settings_siem.php";
}
?>