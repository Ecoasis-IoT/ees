<?php
$_versionFile = __DIR__ . '/../../version.json';
$versionData  = is_file($_versionFile) ? json_decode(file_get_contents($_versionFile), true) : [];
$appVersion   = $versionData['version'] ?? '';
?>
<!-- Stubs for mainscripts.bundle.js: theme-switch + theme-high-contrast checkboxes -->
<div aria-hidden="true" style="display:none!important;position:absolute;pointer-events:none;width:0;height:0;overflow:hidden;">
    <div class="theme-switch"><input type="checkbox" tabindex="-1" aria-hidden="true"></div>
    <div class="theme-high-contrast"><input type="checkbox" tabindex="-1" aria-hidden="true"></div>
</div>

<footer id="ees-footer">
    <span>© <span id="ees-footer-year"></span> Ecoasis Ltd. All rights reserved.</span>
    <?php if ($appVersion): ?>
    <span><?= htmlspecialchars($appVersion, ENT_QUOTES, 'UTF-8') ?></span>
    <?php endif; ?>
</footer>
<script>
document.getElementById('ees-footer-year').textContent = new Date().getFullYear();
</script>
