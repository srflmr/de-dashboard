<?php
$sql = "SELECT COUNT(*) AS total FROM rule";
$result = safeQuery($conn, $sql, "count total rules");
$total = mysqli_fetch_assoc($result)["total"];

$sql = "SELECT status, COUNT(*) AS jumlah FROM rule GROUP BY status";
$result = safeQuery($conn, $sql, "count by status");
$statusData = ["Draft" => 0, "Testing" => 0, "Production" => 0, "Deprecated" => 0];
while ($row = mysqli_fetch_assoc($result)) {
    $statusData[$row["status"]] = $row["jumlah"];
}

$sql = "SELECT severity, COUNT(*) AS jumlah FROM rule GROUP BY severity";
$result = safeQuery($conn, $sql, "count by severity");
$severityData = ["Critical" => 0, "High" => 0, "Medium" => 0, "Low" => 0];
while ($row = mysqli_fetch_assoc($result)) {
    $severityData[$row["severity"]] = $row["jumlah"];
}

$sql = "SELECT r.*, c.customer_name, s.siem_name
    FROM rule r
    LEFT JOIN customer c ON r.customer_id = c.customer_id
    LEFT JOIN siem s ON c.siem_id = s.siem_id
    ORDER BY r.modified_date DESC
    LIMIT 5";
$result = safeQuery($conn, $sql, "recent rules");
?>

<div class="page-title">Dashboard</div>

<div class="section-label">Rules by Status</div>
<div class="stat-cards" style="grid-template-columns: repeat(5, minmax(0, 1fr));">
    <div class="stat-card">
        <div class="stat-card-label">Total Rules</div>
        <div class="stat-card-value"><?php echo $total; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-label">Production</div>
        <div class="stat-card-value" style="color: var(--color-production);"><?php echo $statusData["Production"]; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-label">Testing</div>
        <div class="stat-card-value" style="color: var(--color-testing);"><?php echo $statusData["Testing"]; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-label">Draft</div>
        <div class="stat-card-value" style="color: var(--color-draft);"><?php echo $statusData["Draft"]; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-label">Deprecated</div>
        <div class="stat-card-value" style="color: var(--color-deprecated);"><?php echo $statusData["Deprecated"]; ?></div>
    </div>
</div>

<div class="section-label">Rules by Severity</div>
<div class="stat-cards">
    <div class="stat-card">
        <div class="stat-card-label">Critical</div>
        <div class="stat-card-value" style="color: var(--color-critical);"><?php echo $severityData["Critical"]; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-label">High</div>
        <div class="stat-card-value" style="color: var(--color-high);"><?php echo $severityData["High"]; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-label">Medium</div>
        <div class="stat-card-value" style="color: var(--color-medium);"><?php echo $severityData["Medium"]; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-label">Low</div>
        <div class="stat-card-value" style="color: var(--color-low);"><?php echo $severityData["Low"]; ?></div>
    </div>
</div>

<div class="section-label">Recently Modified</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Rule Name</th>
            <th>Customer</th>
            <th>Platform</th>
            <th>Severity</th>
            <th>Status</th>
            <th>Modified</th>
            <th>Modified By</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)):
            $sevClass = "badge-" . strtolower($row["severity"]);
            $statClass = "badge-" . strtolower($row["status"]);
        ?>
        <tr>
            <td class="cell-bold"><?php echo htmlspecialchars($row["rule_name"]); ?></td>
            <td><?php echo htmlspecialchars($row["customer_name"]); ?></td>
            <td><?php echo htmlspecialchars($row["siem_name"]); ?></td>
            <td><span class="badge <?php echo $sevClass; ?>"><?php echo htmlspecialchars($row["severity"]); ?></span></td>
            <td><span class="badge <?php echo $statClass; ?>"><?php echo htmlspecialchars($row["status"]); ?></span></td>
            <td class="cell-muted"><span class="format-date"><?php echo htmlspecialchars($row["modified_date"]); ?></span></td>
            <td class="cell-muted"><?php echo htmlspecialchars($row["modified_by"]); ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
