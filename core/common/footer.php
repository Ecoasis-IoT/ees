<style>

#footer-sec {
    background-color: #70AD47;
    padding: 20px 50px;
    color: #fff;
    font-size: 15px;
    text-align: center;
}

#footer-sec .footer-version {
    font-size: 12px;
    opacity: 0.8;
    margin-top: 4px;
}

</style>

<?php
$_versionFile = __DIR__ . '/../../version.json';
$versionData  = is_file($_versionFile) ? json_decode(file_get_contents($_versionFile), true) : [];
$appVersion   = $versionData['version'] ?? '';
?>

<div id="footer-sec">
    <div>© Copyright <span id="year"></span> | Designed and Developed by Ecoasis Ltd.</div>
    <?php if ($appVersion): ?>
    <div class="footer-version"><?= htmlspecialchars($appVersion, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
</div>

<!-- To get year -->
<script>
document.getElementById("year").innerHTML = new Date().getFullYear();
</script>