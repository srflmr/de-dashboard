<aside class="sidebar">
    <div class="sidebar-logo">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
        <span>Detection<br>Engineering</span>
    </div>

    <!-- Quick Navigate -->
    <div class="sidebar-section" style="margin-top: 4px;">Quick Navigate</div>
    <a href="index.html" class="sidebar-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        Home
    </a>

    <!-- Main -->
    <div class="sidebar-section" style="margin-top: 12px;">Main</div>
    <a href="index.php" class="sidebar-item <?php echo ($act === "") ? "active" : ""; ?>">
        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        Dashboard
    </a>
    <a href="index.php?act=rules" class="sidebar-item <?php echo ($act === "rules" || $act === "rule_form") ? "active" : ""; ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
        Detection Rules
    </a>

    <!-- Resources & Support -->
    <div class="sidebar-section" style="margin-top: 12px;">Resources &amp; Support</div>
    <a href="docs.html" class="sidebar-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        Documentation
    </a>

    <div class="sidebar-spacer"></div>

    <!-- Configuration -->
    <div class="sidebar-section">Configuration</div>
    <a href="index.php?act=settings" class="sidebar-item <?php echo ($act === "settings") ? "active" : ""; ?>" style="margin-bottom: 12px;">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        Settings
    </a>

    <div class="sidebar-footer" style="border-top: 1px solid var(--color-border); padding-top: 16px;">
        <?php echo sprintf("© %d Detection Engineering", date("Y")); ?>
    </div>
</aside>

<script nonce="<?php echo $nonce; ?>">
var appLang = {
    confirmDelete:    "<?php echo addslashes("Are you sure you want to delete \"%s\"?"); ?>",
    requiredCustomer: "<?php echo addslashes("Customer is required."); ?>",
    requiredRuleName: "<?php echo addslashes("Rule name is required."); ?>",
    maxRuleName:      "<?php echo addslashes("Rule name must not exceed 200 characters."); ?>",
    requiredPlatform:      "<?php echo addslashes("Platform name is required."); ?>",
    requiredCustName:      "<?php echo addslashes("Customer name is required."); ?>",
    requiredCustSiem:      "<?php echo addslashes("SIEM platform is required."); ?>",
    requiredDetectionLogic:"<?php echo addslashes("Detection logic is required."); ?>",
    requiredCreatedBy:     "<?php echo addslashes("Created By is required."); ?>",
    requiredModifiedBy:    "<?php echo addslashes("Modified By is required."); ?>"
};
</script>
