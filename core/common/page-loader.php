<?php
// Session must be started before any output.
// auth.php always runs before this file, so this is a safety net only.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo '
<div class="page-loader-wrapper text-center">
    <div class="loader">
        <div class="mt-4"><img src="assets/images/ecoasis_logo.jpg" width="130" height="auto" alt="Ecoasis"></div>
        <p class="mt-2">Please wait...</p>
    </div>
</div>
';
