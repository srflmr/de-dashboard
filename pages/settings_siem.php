<?php
$returnTo = getReturnToUrl("index.php?act=settings&tab=siem");

// Create SIEM platform
if ($p == "s1") {
    requireValidPostToken();
    $siem_name = sanitizeString($_POST["siem_name"] ?? "", 50);

    if ($siem_name == "") {
        alertAndBack("Platform name is required.");
    }

    $sql = "INSERT INTO siem (siem_name) VALUES (?)";
    $stmt = safePrepare($conn, $sql, "insert siem");
    mysqli_stmt_bind_param($stmt, "s", $siem_name);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    redirectTo($returnTo);
}


// Update SIEM platform
elseif ($p == "s2") {
    requireValidPostToken();
    $siem_id = intval($_POST["siem_id"] ?? 0);
    $siem_name = sanitizeString($_POST["siem_name"] ?? "", 50);

    if ($siem_name == "") {
        alertAndBack("Platform name is required.");
    }

    $sql = "UPDATE siem SET siem_name=? WHERE siem_id=?";
    $stmt = safePrepare($conn, $sql, "update siem");
    mysqli_stmt_bind_param($stmt, "si", $siem_name, $siem_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    redirectTo($returnTo);
}


// Delete SIEM platform
elseif ($p == "del_siem") {
    requireValidPostToken();
    $siem_id = intval($_POST["siem_id"] ?? 0);

    $sql = "DELETE FROM siem WHERE siem_id=?";
    $stmt = safePrepare($conn, $sql, "delete siem");
    mysqli_stmt_bind_param($stmt, "i", $siem_id);
    try {
        mysqli_stmt_execute($stmt);
    } catch (mysqli_sql_exception $e) {
        if ((int)$e->getCode() === 1451) {
            mysqli_stmt_close($stmt);
            alertAndRedirect("This SIEM platform cannot be deleted because it is still used by one or more customers.", $returnTo);
        }
        queryError($conn, "execute delete siem", $e);
    }
    mysqli_stmt_close($stmt);

    redirectTo($returnTo);
}
?>

<div class="top-bar">
    <h2 class="page-title">SIEM Platforms</h2>
    <button class="btn btn-primary" data-toggle="siem-add-form">
        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Platform
    </button>
</div>

<div class="inline-form" id="siem-add-form">
    <form method="POST" action="index.php?act=settings&tab=siem&p=s1" data-validate="siem">
        <input type="hidden" name="csrf_token" value="<?php echo generateToken(); ?>">
        <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($returnTo); ?>">
        <div class="form-group" style="margin-bottom: 12px;">
            <label>Platform Name</label>
            <input type="text" name="siem_name" placeholder="e.g. Elastic, Splunk..." required maxlength="50">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-secondary" data-toggle="siem-add-form">Cancel</button>
        </div>
    </form>
</div>

<?php if ($p == "edit_siem"):
    $editId = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
    if ($editId <= 0) {
        redirectTo($returnTo);
    }
    $sql = "SELECT * FROM siem WHERE siem_id=?";
    $stmt = safePrepare($conn, $sql, "select siem edit");
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
    <form method="POST" action="index.php?act=settings&tab=siem&p=s2" data-validate="siem">
        <input type="hidden" name="csrf_token" value="<?php echo generateToken(); ?>">
        <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($returnTo); ?>">
        <input type="hidden" name="siem_id" value="<?php echo $row["siem_id"]; ?>">
        <div class="form-group" style="margin-bottom: 12px;">
            <label>Platform Name</label>
            <input type="text" name="siem_name" value="<?php echo htmlspecialchars($row["siem_name"]); ?>" required maxlength="50">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="<?php echo htmlspecialchars($returnTo); ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php endif; ?>

<?php
$sql = "SELECT * FROM siem ORDER BY siem_id ASC";
$result = safeQuery($conn, $sql, "select all siem");
$rowCount = mysqli_num_rows($result);
?>

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 48px;">No</th>
            <th>Platform Name</th>
            <th style="width: 90px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($rowCount == 0) {
            echo "
            <tr>
                <td colspan='3' style='text-align: center; color: var(--color-text-secondary);'>" . "No data available" . "</td>
            </tr>";
        } else {
            $rowNum = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                echo "
                <tr>
                    <td class='cell-muted'>$rowNum</td>
                    <td>" . htmlspecialchars($row["siem_name"]) . "</td>
                    <td>
                        <div class='action-icons'>
                            <a href='index.php?act=settings&tab=siem&p=edit_siem&id=" . $row["siem_id"] . "&return_to=" . urlencode($returnTo) . "' class='action-icon' title='Edit'>
                                <svg viewBox='0 0 24 24'><path d='M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7'/><path d='M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z'/></svg>
                            </a>
                            <form method='POST' action='index.php?act=settings&tab=siem&p=del_siem' style='display:inline;' data-confirm='" . htmlspecialchars($row["siem_name"]) . "'>
                                <input type='hidden' name='csrf_token' value='" . generateToken() . "'>
                                <input type='hidden' name='siem_id' value='" . $row["siem_id"] . "'>
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
