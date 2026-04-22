<?php
$p = isset($_GET["p"]) ? $_GET["p"] : "";
$returnTo = getReturnToUrl("index.php?act=rules");
$editMode = isset($_GET["id"]) && $_GET["id"] !== "";
$ruleData = null;

$allowedSeverity = ["Low", "Medium", "High", "Critical"];
$allowedStatus = ["Draft", "Testing", "Production", "Deprecated"];
$tactics = [
    "Reconnaissance", "Resource Development", "Initial Access", "Execution",
    "Persistence", "Privilege Escalation", "Defense Evasion", "Credential Access",
    "Discovery", "Lateral Movement", "Collection", "Command and Control",
    "Exfiltration", "Impact"
];

if ($editMode) {
    $ruleId = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
    if ($ruleId <= 0) {
        redirectTo($returnTo);
    }
    $stmt = safePrepare($conn, "SELECT * FROM rule WHERE rule_id=?", "select rule edit");
    mysqli_stmt_bind_param($stmt, "i", $ruleId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $ruleData = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$ruleData) {
        redirectTo($returnTo);
    }
}

if ($p == "save_rule") {
    requireValidPostToken();

    $customer_id     = intval($_POST["customer_id"] ?? 0);
    $rule_name       = sanitizeString($_POST["rule_name"] ?? "", 200);
    $mitre_tactic    = validateEnum($_POST["mitre_tactic"] ?? "", array_merge([""], $tactics));
    $mitre_technique = sanitizeString($_POST["mitre_technique"] ?? "", 100);
    $severity        = validateEnum($_POST["severity"] ?? "", $allowedSeverity);
    $detection_logic = trim($_POST["detection_logic"] ?? "");
    $status          = validateEnum($_POST["status"] ?? "", $allowedStatus);
    $created_date    = validateDate($_POST["created_date"] ?? "");
    $modified_date   = validateDate($_POST["modified_date"] ?? "");
    $created_by      = sanitizeString($_POST["created_by"] ?? "", 100);
    $modified_by     = sanitizeString($_POST["modified_by"] ?? "", 100);

    if ($rule_name == "" || $customer_id == 0) {
        alertAndBack("Rule name and customer are required.");
    }
    if ($detection_logic === "") {
        alertAndBack("Detection logic is required.");
    }
    if ($created_by === "") {
        alertAndBack("Created By is required.");
    }
    if ($modified_by === "") {
        alertAndBack("Modified By is required.");
    }

    if ($editMode) {
        $ruleId = intval($_POST["rule_id"] ?? 0);
        $sql = "UPDATE rule SET customer_id=?, rule_name=?, mitre_tactic=?, mitre_technique=?, severity=?, detection_logic=?, status=?, created_date=?, modified_date=?, created_by=?, modified_by=? WHERE rule_id=?";
        $stmt = safePrepare($conn, $sql, "update rule");
        mysqli_stmt_bind_param($stmt, "issssssssssi", $customer_id, $rule_name, $mitre_tactic, $mitre_technique, $severity, $detection_logic, $status, $created_date, $modified_date, $created_by, $modified_by, $ruleId);
    } else {
        $sql = "INSERT INTO rule (customer_id, rule_name, mitre_tactic, mitre_technique, severity, detection_logic, status, created_date, modified_date, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = safePrepare($conn, $sql, "insert rule");
        mysqli_stmt_bind_param($stmt, "issssssssss", $customer_id, $rule_name, $mitre_tactic, $mitre_technique, $severity, $detection_logic, $status, $created_date, $modified_date, $created_by, $modified_by);
    }

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    redirectTo($returnTo);
}

$customers = safeQuery($conn, "SELECT * FROM customer ORDER BY customer_name", "customer list form");
?>

<div class="breadcrumb">
    <a href="<?php echo htmlspecialchars($returnTo); ?>">Detection Rules</a>
    <span class="separator">/</span>
    <?php echo $editMode ? "Edit Rule" : "Add New Rule"; ?>
</div>

<div class="page-title"><?php echo $editMode ? "Edit Detection Rule" : "Add Detection Rule"; ?></div>

<form method="POST" action="index.php?act=rule_form&p=save_rule<?php echo $editMode ? "&id=" . $ruleData["rule_id"] : ""; ?>" id="ruleForm" data-validate="rule">
    <input type="hidden" name="csrf_token" value="<?php echo generateToken(); ?>">
    <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($returnTo); ?>">
    <?php if ($editMode): ?>
    <input type="hidden" name="rule_id" value="<?php echo $ruleData["rule_id"]; ?>">
    <?php endif; ?>

    <div class="form-grid">
        <div class="form-group">
            <label>Customer *</label>
            <select name="customer_id" id="customer_id" required>
                <option value="">Select customer...</option>
                <?php while ($c = mysqli_fetch_assoc($customers)): ?>
                <option value="<?php echo $c["customer_id"]; ?>" <?php echo ($ruleData && $ruleData["customer_id"] == $c["customer_id"]) ? "selected" : ""; ?>>
                    <?php echo htmlspecialchars($c["customer_name"]); ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Rule Name *</label>
            <input type="text" name="rule_name" id="rule_name" maxlength="200" required value="<?php echo $ruleData ? htmlspecialchars($ruleData["rule_name"]) : ""; ?>">
        </div>

        <div class="form-group">
            <label>MITRE Tactic</label>
            <select name="mitre_tactic">
                <option value="">Select tactic...</option>
                <?php foreach ($tactics as $t): ?>
                <option value="<?php echo $t; ?>" <?php echo ($ruleData && $ruleData["mitre_tactic"] == $t) ? "selected" : ""; ?>><?php echo $t; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>MITRE Technique</label>
            <input type="text" name="mitre_technique" placeholder="Technique (e.g. T1078)" maxlength="100" value="<?php echo $ruleData ? htmlspecialchars($ruleData["mitre_technique"]) : ""; ?>">
        </div>

        <div class="form-group">
            <label>Severity</label>
            <select name="severity">
                <?php foreach ($allowedSeverity as $sev): ?>
                <option value="<?php echo $sev; ?>" <?php echo ($ruleData && $ruleData["severity"] == $sev) ? "selected" : ""; ?>><?php echo $sev; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <?php foreach ($allowedStatus as $stat): ?>
                <option value="<?php echo $stat; ?>" <?php echo ($ruleData && $ruleData["status"] == $stat) ? "selected" : ""; ?>><?php echo $stat; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group full-width">
            <label>Detection Logic *</label>
            <textarea name="detection_logic" required><?php echo $ruleData ? htmlspecialchars($ruleData["detection_logic"]) : ""; ?></textarea>
        </div>

        <div class="form-group">
            <label>Created Date</label>
            <input type="date" name="created_date" value="<?php echo htmlspecialchars($ruleData ? $ruleData["created_date"] : date("Y-m-d")); ?>">
        </div>
        <div class="form-group">
            <label>Modified Date</label>
            <input type="date" name="modified_date" value="<?php echo htmlspecialchars($ruleData ? $ruleData["modified_date"] : date("Y-m-d")); ?>">
        </div>
        <div class="form-group">
            <label>Created By *</label>
            <input type="text" name="created_by" maxlength="100" required value="<?php echo $ruleData ? htmlspecialchars($ruleData["created_by"]) : ""; ?>">
        </div>
        <div class="form-group">
            <label>Modified By *</label>
            <input type="text" name="modified_by" maxlength="100" required value="<?php echo $ruleData ? htmlspecialchars($ruleData["modified_by"]) : ""; ?>">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?php echo $editMode ? "Update rule" : "Save rule"; ?></button>
            <a href="<?php echo htmlspecialchars($returnTo); ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</form>
