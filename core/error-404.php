<?php http_response_code(404); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page Not Found | EES</title>
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/logo_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Nunito Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0F172A;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .error-wrap {
            text-align: center;
            max-width: 480px;
            width: 100%;
        }
        .error-logo {
            width: 52px;
            height: auto;
            margin-bottom: 32px;
            filter: brightness(0) invert(1) opacity(.6);
        }
        .error-code {
            font-size: 100px;
            font-weight: 900;
            color: #70AD47;
            line-height: 1;
            margin-bottom: 12px;
            letter-spacing: -4px;
        }
        .error-title {
            font-size: 22px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 12px;
        }
        .error-msg {
            font-size: 14px;
            color: rgba(255,255,255,.45);
            line-height: 1.7;
            margin-bottom: 32px;
        }
        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 28px;
            background: #70AD47;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            transition: background .15s, box-shadow .15s;
        }
        .btn-home:hover {
            background: #5d9638;
            box-shadow: 0 4px 16px rgba(112,173,71,.4);
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="error-wrap">
        <img src="assets/images/logo_icon.png" class="error-logo" alt="EES">
        <div class="error-code">404</div>
        <h1 class="error-title">Page Not Found</h1>
        <p class="error-msg">
            The page you are looking for might have been removed,<br>
            had its name changed, or is temporarily unavailable.
        </p>
        <a href="dashboard" class="btn-home">
            <i class="fa fa-home"></i> Go to Dashboard
        </a>
    </div>
</body>
</html>
