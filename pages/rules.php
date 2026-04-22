<?php
$p = isset($_GET["p"]) ? $_GET["p"] : "";
$currentReturnTo = "index.php?act=rules";
if (isset($_SERVER["REQUEST_URI"])) {
    $qPos = strpos($_SERVER["REQUEST_URI"], "?");
    if ($qPos !== false) {
        $currentReturnTo = "index.php?" . substr($_SERVER["REQUEST_URI"], $qPos + 1);
    }
}

if ($p == "del_rule") {
    requireValidPostToken();
    $returnTo = getReturnToUrl("index.php?act=rules");
    $ruleId = intval($_POST["rule_id"] ?? 0);
    $sql = "DELETE FROM rule WHERE rule_id=?";
    $stmt = safePrepare($conn, $sql, "delete rule");
    mysqli_stmt_bind_param($stmt, "i", $ruleId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    redirectTo($returnTo);
}

$tactics = [
    "Reconnaissance", "Resource Development", "Initial Access", "Execution",
    "Persistence", "Privilege Escalation", "Defense Evasion", "Credential Access",
    "Discovery", "Lateral Movement", "Collection", "Command and Control",
    "Exfiltration", "Impact"
];

$search          = isset($_GET["search"]) ? sanitizeString($_GET["search"], 200) : "";
$f_customer      = isset($_GET["customer"]) ? $_GET["customer"] : "";
$f_siem          = isset($_GET["siem"]) ? $_GET["siem"] : "";
$f_tactic        = isset($_GET["tactic"]) ? validateEnum($_GET["tactic"], array_merge([""], $tactics)) : "";
$f_technique     = isset($_GET["technique"]) ? sanitizeString($_GET["technique"], 100) : "";
$f_severity      = isset($_GET["severity"]) ? validateEnum($_GET["severity"], ["", "Critical", "High", "Medium", "Low"]) : "";
$f_status        = isset($_GET["status"]) ? validateEnum($_GET["status"], ["", "Draft", "Testing", "Production", "Deprecated"]) : "";
$f_created_from  = isset($_GET["created_from"]) ? validateDateOrEmpty($_GET["created_from"]) : "";
$f_created_to    = isset($_GET["created_to"]) ? validateDateOrEmpty($_GET["created_to"]) : "";
$f_modified_from = isset($_GET["modified_from"]) ? validateDateOrEmpty($_GET["modified_from"]) : "";
$f_modified_to   = isset($_GET["modified_to"]) ? validateDateOrEmpty($_GET["modified_to"]) : "";
$f_created_by    = isset($_GET["created_by"]) ? sanitizeString($_GET["created_by"], 100) : "";
$f_modified_by   = isset($_GET["modified_by"]) ? sanitizeString($_GET["modified_by"], 100) : "";

$where = [];
$params = [];
$types = "";

if ($search != "") {
    $where[] = "(r.rule_name LIKE ? OR r.mitre_technique LIKE ?)";
    $searchParam = "%" . $search . "%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}
if ($f_customer != "") {
    $where[] = "r.customer_id = ?";
    $custParam = intval($f_customer);
    $params[] = $custParam;
    $types .= "i";
}
if ($f_siem != "") {
    $where[] = "c.siem_id = ?";
    $siemParam = intval($f_siem);
    $params[] = $siemParam;
    $types .= "i";
}
if ($f_tactic != "") {
    $where[] = "r.mitre_tactic = ?";
    $params[] = $f_tactic;
    $types .= "s";
}
if ($f_technique != "") {
    $where[] = "r.mitre_technique LIKE ?";
    $params[] = "%" . $f_technique . "%";
    $types .= "s";
}
if ($f_severity != "") {
    $where[] = "r.severity = ?";
    $params[] = $f_severity;
    $types .= "s";
}
if ($f_status != "") {
    $where[] = "r.status = ?";
    $params[] = $f_status;
    $types .= "s";
}
if ($f_created_from != "") {
    $where[] = "r.created_date >= ?";
    $params[] = $f_created_from;
    $types .= "s";
}
if ($f_created_to != "") {
    $where[] = "r.created_date <= ?";
    $params[] = $f_created_to;
    $types .= "s";
}
if ($f_modified_from != "") {
    $where[] = "r.modified_date >= ?";
    $params[] = $f_modified_from;
    $types .= "s";
}
if ($f_modified_to != "") {
    $where[] = "r.modified_date <= ?";
    $params[] = $f_modified_to;
    $types .= "s";
}
if ($f_created_by != "") {
    $where[] = "r.created_by LIKE ?";
    $params[] = "%" . $f_created_by . "%";
    $types .= "s";
}
if ($f_modified_by != "") {
    $where[] = "r.modified_by LIKE ?";
    $params[] = "%" . $f_modified_by . "%";
    $types .= "s";
}

$whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Count total rules before applying filters.
$sql = "SELECT COUNT(*) AS total FROM rule";
$result = safeQuery($conn, $sql, "count rules");
$total = mysqli_fetch_assoc($result)["total"];

// Main filtered query (standard variable naming: $sql, $result).
$sql = "SELECT r.*, c.customer_name, s.siem_name
    FROM rule r
    LEFT JOIN customer c ON r.customer_id = c.customer_id
    LEFT JOIN siem s ON c.siem_id = s.siem_id
    $whereClause
    ORDER BY r.modified_date DESC";

$stmt = safePrepare($conn, $sql, "filter rules");
if ($types != "") {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$rowCount = mysqli_num_rows($result);

// Multi-use result sets reused with mysqli_data_seek().
$customers = safeQuery($conn, "SELECT * FROM customer ORDER BY customer_name", "customer list");
$siems = safeQuery($conn, "SELECT * FROM siem ORDER BY siem_name", "siem list");

$hasFilters = (
    $search != "" || $f_customer != "" || $f_siem != "" ||
    $f_tactic != "" || $f_technique != "" || $f_severity != "" || $f_status != "" ||
    $f_created_from != "" || $f_created_to != "" ||
    $f_modified_from != "" || $f_modified_to != "" ||
    $f_created_by != "" || $f_modified_by != ""
);
?>

<div class="top-bar">
    <h1 class="page-title">Detection Rules</h1>
    <a href="index.php?act=rule_form&return_to=<?php echo urlencode($currentReturnTo); ?>" class="btn btn-primary">
        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Rule
    </a>
</div>

<form method="GET" action="index.php" id="filterForm">
    <input type="hidden" name="act" value="rules">

    <div class="search-wrap">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="search" class="search-input" placeholder="Search by rule name, technique, or keyword..." value="<?php echo htmlspecialchars($search); ?>">
    </div>

    <div class="filter-panel">
        <!-- Primary dropdown filters -->
        <div class="filter-row">
            <select name="customer" class="filter-chip <?php echo ($f_customer != "") ? "active" : ""; ?>">
                <option value="">All Customers</option>
                <?php mysqli_data_seek($customers, 0); while ($c = mysqli_fetch_assoc($customers)): ?>
                <option value="<?php echo $c["customer_id"]; ?>" <?php echo ($f_customer == $c["customer_id"]) ? "selected" : ""; ?>><?php echo htmlspecialchars($c["customer_name"]); ?></option>
                <?php endwhile; ?>
            </select>

            <select name="siem" class="filter-chip <?php echo ($f_siem != "") ? "active" : ""; ?>">
                <option value="">All SIEMs</option>
                <?php mysqli_data_seek($siems, 0); while ($s = mysqli_fetch_assoc($siems)): ?>
                <option value="<?php echo $s["siem_id"]; ?>" <?php echo ($f_siem == $s["siem_id"]) ? "selected" : ""; ?>><?php echo htmlspecialchars($s["siem_name"]); ?></option>
                <?php endwhile; ?>
            </select>

            <select name="tactic" class="filter-chip <?php echo ($f_tactic != "") ? "active" : ""; ?>">
                <option value="">All Tactics</option>
                <?php foreach ($tactics as $t): ?>
                <option value="<?php echo $t; ?>" <?php echo ($f_tactic == $t) ? "selected" : ""; ?>><?php echo $t; ?></option>
                <?php endforeach; ?>
            </select>

            <select name="severity" class="filter-chip <?php echo ($f_severity != "") ? "active" : ""; ?>">
                <option value="">Severity</option>
                <option value="Critical" <?php echo ($f_severity == "Critical") ? "selected" : ""; ?>>Critical</option>
                <option value="High" <?php echo ($f_severity == "High") ? "selected" : ""; ?>>High</option>
                <option value="Medium" <?php echo ($f_severity == "Medium") ? "selected" : ""; ?>>Medium</option>
                <option value="Low" <?php echo ($f_severity == "Low") ? "selected" : ""; ?>>Low</option>
            </select>

            <select name="status" class="filter-chip <?php echo ($f_status != "") ? "active" : ""; ?>">
                <option value="">Status</option>
                <option value="Draft" <?php echo ($f_status == "Draft") ? "selected" : ""; ?>>Draft</option>
                <option value="Testing" <?php echo ($f_status == "Testing") ? "selected" : ""; ?>>Testing</option>
                <option value="Production" <?php echo ($f_status == "Production") ? "selected" : ""; ?>>Production</option>
                <option value="Deprecated" <?php echo ($f_status == "Deprecated") ? "selected" : ""; ?>>Deprecated</option>
            </select>
        </div>

        <!-- Date range filters -->
        <div class="filter-row-dates">
            <div class="filter-date-group">
                <label>Created From</label>
                <div class="filter-date-range">
                    <input type="date" name="created_from" class="filter-date-input <?php echo ($f_created_from != "") ? "active" : ""; ?>" value="<?php echo htmlspecialchars($f_created_from); ?>">
                    <span>→</span>
                    <input type="date" name="created_to" class="filter-date-input <?php echo ($f_created_to != "") ? "active" : ""; ?>" value="<?php echo htmlspecialchars($f_created_to); ?>">
                </div>
            </div>

            <div class="filter-date-group">
                <label>Modified From</label>
                <div class="filter-date-range">
                    <input type="date" name="modified_from" class="filter-date-input <?php echo ($f_modified_from != "") ? "active" : ""; ?>" value="<?php echo htmlspecialchars($f_modified_from); ?>">
                    <span>→</span>
                    <input type="date" name="modified_to" class="filter-date-input <?php echo ($f_modified_to != "") ? "active" : ""; ?>" value="<?php echo htmlspecialchars($f_modified_to); ?>">
                </div>
            </div>

            <div class="filter-date-group">
                <label>Created By</label>
                <input type="text" name="created_by" class="filter-text-input <?php echo ($f_created_by != "") ? "active" : ""; ?>" placeholder="Created By" value="<?php echo htmlspecialchars($f_created_by); ?>">
            </div>

            <div class="filter-date-group">
                <label>Modified By</label>
                <input type="text" name="modified_by" class="filter-text-input <?php echo ($f_modified_by != "") ? "active" : ""; ?>" placeholder="Modified By" value="<?php echo htmlspecialchars($f_modified_by); ?>">
            </div>

            <div class="filter-date-group">
                <label>MITRE Technique</label>
                <input type="text" name="technique" class="filter-text-input <?php echo ($f_technique != "") ? "active" : ""; ?>" placeholder="Technique (e.g. T1078)" value="<?php echo htmlspecialchars($f_technique); ?>">
            </div>
        </div>

        <!-- Actions -->
        <div class="filter-row-actions">
            <button type="submit" class="btn btn-secondary">Filter</button>
            <?php if ($hasFilters): ?>
            <a href="index.php?act=rules" class="clear-link">Clear All</a>
            <?php endif; ?>
        </div>
    </div>
</form>

<?php if ($hasFilters): ?>
<div class="active-filters">
    <?php if ($search != ""): ?>
    <span class="filter-tag"><span class="tag-label">Search:</span> <?php echo htmlspecialchars($search); ?></span>
    <?php endif; ?>
    <?php if ($f_customer != ""): ?>
    <?php
        $cRow = null;
        mysqli_data_seek($customers, 0);
        while ($cr = mysqli_fetch_assoc($customers)) { if ($cr["customer_id"] == $f_customer) { $cRow = $cr; break; } }
    ?>
    <span class="filter-tag"><span class="tag-label">Customer:</span> <?php echo $cRow ? htmlspecialchars($cRow["customer_name"]) : $f_customer; ?></span>
    <?php endif; ?>
    <?php if ($f_siem != ""): ?>
    <?php
        $sRow = null;
        mysqli_data_seek($siems, 0);
        while ($sr = mysqli_fetch_assoc($siems)) { if ($sr["siem_id"] == $f_siem) { $sRow = $sr; break; } }
    ?>
    <span class="filter-tag"><span class="tag-label">SIEM:</span> <?php echo $sRow ? htmlspecialchars($sRow["siem_name"]) : $f_siem; ?></span>
    <?php endif; ?>
    <?php if ($f_tactic != ""): ?>
    <span class="filter-tag"><span class="tag-label">Tactic:</span> <?php echo htmlspecialchars($f_tactic); ?></span>
    <?php endif; ?>
    <?php if ($f_technique != ""): ?>
    <span class="filter-tag"><span class="tag-label">Technique:</span> <?php echo htmlspecialchars($f_technique); ?></span>
    <?php endif; ?>
    <?php if ($f_severity != ""): ?>
    <span class="filter-tag"><span class="tag-label">Severity:</span> <?php echo htmlspecialchars($f_severity); ?></span>
    <?php endif; ?>
    <?php if ($f_status != ""): ?>
    <span class="filter-tag"><span class="tag-label">Status:</span> <?php echo htmlspecialchars($f_status); ?></span>
    <?php endif; ?>
    <?php if ($f_created_from != "" || $f_created_to != ""): ?>
    <span class="filter-tag"><span class="tag-label">Created:</span> <?php echo htmlspecialchars($f_created_from); ?> &ndash; <?php echo htmlspecialchars($f_created_to); ?></span>
    <?php endif; ?>
    <?php if ($f_modified_from != "" || $f_modified_to != ""): ?>
    <span class="filter-tag"><span class="tag-label">Modified:</span> <?php echo htmlspecialchars($f_modified_from); ?> &ndash; <?php echo htmlspecialchars($f_modified_to); ?></span>
    <?php endif; ?>
    <?php if ($f_created_by != ""): ?>
    <span class="filter-tag"><span class="tag-label">Created By:</span> <?php echo htmlspecialchars($f_created_by); ?></span>
    <?php endif; ?>
    <?php if ($f_modified_by != ""): ?>
    <span class="filter-tag"><span class="tag-label">Modified By:</span> <?php echo htmlspecialchars($f_modified_by); ?></span>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="result-meta">
    <span><?php echo sprintf("Showing %d of %d rules", $rowCount, $total); ?></span>
    <span>Sorted by Modified Date</span>
</div>

<div class="table-scroll">
<table class="data-table rules-table">
    <thead>
        <tr>
            <th style="width: 40px;">No</th>
            <th>Rule Name</th>
            <th class="col-wide">Customer</th>
            <th class="col-wide">Platform</th>
            <th>Tactic</th>
            <th>Technique</th>
            <th>Severity</th>
            <th>Status</th>
            <th class="col-date">Created</th>
            <th class="col-date">Modified</th>
            <th class="col-user">Created By</th>
            <th class="col-user">Modified By</th>
            <th style="width: 72px;"></th>
        </tr>
    </thead>
    <tbody>
        <?php if ($rowCount == 0) {
            echo "
            <tr>
                    <td colspan='13' style='text-align: center; color: var(--color-text-secondary); padding: 24px;'>" . "No rules found" . "</td>
            </tr>";
        } else {
            $rowNum = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                $sevClass = "badge-" . strtolower($row["severity"]);
                $statClass = "badge-" . strtolower($row["status"]);

                echo "
                <tr>
                    <td class='cell-muted'>$rowNum</td>
                    <td class='cell-bold'>" . htmlspecialchars($row["rule_name"]) . "</td>
                    <td class='col-wide'>" . htmlspecialchars($row["customer_name"]) . "</td>
                    <td class='col-wide'>" . htmlspecialchars($row["siem_name"]) . "</td>
                    <td class='cell-muted'>" . htmlspecialchars($row["mitre_tactic"]) . "</td>
                    <td class='cell-muted'>" . htmlspecialchars($row["mitre_technique"]) . "</td>
                    <td><span class='badge $sevClass'>" . htmlspecialchars($row["severity"]) . "</span></td>
                    <td><span class='badge $statClass'>" . htmlspecialchars($row["status"]) . "</span></td>
                    <td class='cell-muted col-date'><span class='format-date'>" . htmlspecialchars($row["created_date"]) . "</span></td>
                    <td class='cell-muted col-date'><span class='format-date'>" . htmlspecialchars($row["modified_date"]) . "</span></td>
                    <td class='cell-muted col-user'>" . htmlspecialchars($row["created_by"]) . "</td>
                    <td class='cell-muted col-user'>" . htmlspecialchars($row["modified_by"]) . "</td>
                    <td>
                        <div class='action-icons'>
                            <a href='index.php?act=rule_form&id=" . $row["rule_id"] . "&return_to=" . urlencode($currentReturnTo) . "' class='action-icon' title='Edit'>
                                <svg viewBox='0 0 24 24'><path d='M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7'/><path d='M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z'/></svg>
                            </a>
                            <form method='POST' action='index.php?act=rules&p=del_rule' style='display:inline;' data-confirm='" . htmlspecialchars($row["rule_name"]) . "'>
                                <input type='hidden' name='csrf_token' value='" . generateToken() . "'>
                                <input type='hidden' name='rule_id' value='" . $row["rule_id"] . "'>
                                <input type='hidden' name='return_to' value='" . htmlspecialchars($currentReturnTo) . "'>
                                <button type='submit' class='action-icon' title='Delete'>
                                    <svg viewBox='0 0 24 24'><path d='M3 6h18'/><path d='M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2'/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>";
                $rowNum++;
            }
        } ?>
    </tbody>
</table>
</div>
<?php mysqli_stmt_close($stmt); ?>
