<?php
$returnTo = getReturnToUrl("index.php?act=settings&tab=customer");
if ($p == "s3") {
    requireValidPostToken();
    $customer_name = sanitizeString($_POST["customer_name"] ?? "", 100);
    $siem_id = intval($_POST["siem_id"] ?? 0);

    if ($customer_name == "") {
        alertAndBack("Customer name is required.");
    }
    if ($siem_id <= 0) {
        alertAndBack("SIEM platform is required.");
    }

    $sql = "INSERT INTO customer (customer_name, siem_id) VALUES (?, ?)";
    $stmt = safePrepare($conn, $sql, "insert customer");
    mysqli_stmt_bind_param($stmt, "si", $customer_name, $siem_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    redirectTo($returnTo);
}

elseif ($p == "s4") {
    requireValidPostToken();
    $customer_id = intval($_POST["customer_id"] ?? 0);
    $customer_name = sanitizeString($_POST["customer_name"] ?? "", 100);
    $siem_id = intval($_POST["siem_id"] ?? 0);

    if ($customer_name == "") {
        alertAndBack("Customer name is required.");
    }
    if ($siem_id <= 0) {
        alertAndBack("SIEM platform is required.");
    }

    $sql = "UPDATE customer SET customer_name=?, siem_id=? WHERE customer_id=?";
    $stmt = safePrepare($conn, $sql, "update customer");
    mysqli_stmt_bind_param($stmt, "sii", $customer_name, $siem_id, $customer_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    redirectTo($returnTo);
}

elseif ($p == "del_cust") {
    requireValidPostToken();
    $customer_id = intval($_POST["customer_id"] ?? 0);

    $sql = "DELETE FROM customer WHERE customer_id=?";
    $stmt = safePrepare($conn, $sql, "delete customer");
    mysqli_stmt_bind_param($stmt, "i", $customer_id);
    try {
        mysqli_stmt_execute($stmt);
    } catch (mysqli_sql_exception $e) {
        if ((int)$e->getCode() === 1451) {
            mysqli_stmt_close($stmt);
            alertAndRedirect("This customer cannot be deleted because it is still used by one or more detection rules.", $returnTo);
        }
        queryError($conn, "execute delete customer", $e);
    }
    mysqli_stmt_close($stmt);

    redirectTo($returnTo);
}

$siem_list = safeQuery($conn, "SELECT * FROM siem ORDER BY siem_name ASC", "select siem list");
?>

<div class="top-bar">
    <h2 class="page-title">Customers</h2>
    <button class="btn btn-primary" data-toggle="cust-add-form">
        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Customer
    </button>
</div>

<div class="inline-form" id="cust-add-form">
    <form method="POST" action="index.php?act=settings&tab=customer&p=s3" data-validate="customer">
        <input type="hidden" name="csrf_token" value="<?php echo generateToken(); ?>">
        <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($returnTo); ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Customer Name</label>
                <input type="text" name="customer_name" placeholder="e.g. Acme Corp" required maxlength="100">
            </div>
            <div class="form-group">
                <label>SIEM Platform</label>
                <select name="siem_id" required>
                    <option value="">Select platform...</option>
                    <?php
                    mysqli_data_seek($siem_list, 0);
                    while ($s = mysqli_fetch_assoc($siem_list)) {
                        echo "<option value='" . $s["siem_id"] . "'>" . htmlspecialchars($s["siem_name"]) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-secondary" data-toggle="cust-add-form">Cancel</button>
        </div>
    </form>
</div>

<?php if ($p == "edit_cust"):
    $editId = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
    if ($editId <= 0) {
        redirectTo($returnTo);
    }
    $stmt = safePrepare($conn, "SELECT * FROM customer WHERE customer_id=?", "select customer edit");
    mysqli_stmt_bind_param($stmt, "i", $editId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$row) {
        redirectTo($returnTo);
    }
?>
<div class="inline-form show">
    <form method="POST" action="index.php?act=settings&tab=customer&p=s4" data-validate="customer">
        <input type="hidden" name="csrf_token" value="<?php echo generateToken(); ?>">
        <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($returnTo); ?>">
        <input type="hidden" name="customer_id" value="<?php echo $row["customer_id"]; ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Customer Name</label>
                <input type="text" name="customer_name" value="<?php echo htmlspecialchars($row["customer_name"]); ?>" required maxlength="100">
            </div>
            <div class="form-group">
                <label>SIEM Platform</label>
                <select name="siem_id" required>
                    <option value="">Select platform...</option>
                    <?php
                    mysqli_data_seek($siem_list, 0);
                    while ($s = mysqli_fetch_assoc($siem_list)) {
                        $selected = ($s["siem_id"] == $row["siem_id"]) ? "selected" : "";
                        echo "<option value='" . $s["siem_id"] . "' $selected>" . htmlspecialchars($s["siem_name"]) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="<?php echo htmlspecialchars($returnTo); ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php endif; ?>

<?php
$sql = "SELECT c.*, s.siem_name FROM customer c LEFT JOIN siem s ON c.siem_id = s.siem_id ORDER BY c.customer_id ASC";
$result = safeQuery($conn, $sql, "select all customers");
$rowCount = mysqli_num_rows($result);
?>

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 48px;">No</th>
            <th>Customer Name</th>
            <th>SIEM Platform</th>
            <th style="width: 90px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($rowCount == 0) {
            echo "
            <tr>
                <td colspan='4' style='text-align: center; color: var(--color-text-secondary);'>" . "No data available" . "</td>
            </tr>";
        } else {
            $rowNum = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                echo "
                <tr>
                    <td class='cell-muted'>$rowNum</td>
                    <td>" . htmlspecialchars($row["customer_name"]) . "</td>
                    <td><span class='badge badge-draft'>" . htmlspecialchars($row["siem_name"]) . "</span></td>
                    <td>
                        <div class='action-icons'>
                            <a href='index.php?act=settings&tab=customer&p=edit_cust&id=" . $row["customer_id"] . "&return_to=" . urlencode($returnTo) . "' class='action-icon' title='Edit'>
                                <svg viewBox='0 0 24 24'><path d='M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7'/><path d='M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z'/></svg>
                            </a>
                            <form method='POST' action='index.php?act=settings&tab=customer&p=del_cust' style='display:inline;' data-confirm='" . htmlspecialchars($row["customer_name"]) . "'>
                                <input type='hidden' name='csrf_token' value='" . generateToken() . "'>
                                <input type='hidden' name='customer_id' value='" . $row["customer_id"] . "'>
                                <input type='hidden' name='return_to' value='" . htmlspecialchars($returnTo) . "'>
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
