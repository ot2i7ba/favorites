<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Simple Web-Link-Tracker</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'">
    <meta http-equiv="Referrer-Policy" content="no-referrer">
    <meta http-equiv="Cache-Control" content="no-cache, must-revalidate, no-store">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <!-- Styles -->
    <style>
        .list-item {
            display: flex;
            justify-content: flex-start;
        }
        .timestamp {
            margin-right: 2rem;
        }
    </style>
</head>
<body>
    <?php require_once 'favlogic.php'; ?>
    <!-- List of all stored URLs -->
    <ul>
        <?php foreach ($favorites as $favorite) { ?>
            <li class="list-item">
                <a href="?secret=<?php echo $secret_value; ?>&delete=<?php echo urlencode($favorite['url']); ?>">[DEL]</a>&nbsp;&mdash;&nbsp;
                <span class="timestamp"><?php echo $favorite['timestamp']; ?></span>&nbsp;&vert;&nbsp;
                <a href="<?php echo $favorite['url']; ?>" rel="noopener noreferrer" target="_blank"><?php echo htmlspecialchars($favorite['title'], ENT_QUOTES, 'UTF-8'); ?></a>
            </li>
        <?php } ?>
    </ul>
</body>
</html>
