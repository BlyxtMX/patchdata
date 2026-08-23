<?php
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

/* DATOS DE RATHENA / MYSQL
 * Copia estos valores desde conf/inter_athena.conf.
 */
$dbHost = getenv('RO_DB_HOST') ?: '127.0.0.1';
$dbPort = (int) (getenv('RO_DB_PORT') ?: 3306);
$dbName = getenv('RO_DB_NAME') ?: 'rathena';
$dbUser = getenv('RO_DB_USER') ?: 'rathena_user';
$dbPassword = getenv('RO_DB_PASSWORD') ?: '';

/* ESTADO DEL CHAR-SERVER
 * Si Wamp y rAthena estan en la misma PC, conserva 127.0.0.1.
 */
$serverHost = '127.0.0.1';
$serverPort = 6121;
$socketTimeout = 1.5;

$socket = @fsockopen($serverHost, $serverPort, $errorNumber, $errorMessage, $socketTimeout);
$serverOnline = is_resource($socket);
if ($serverOnline) {
    fclose($socket);
}

$playersOnline = null;
try {
    $dsn = 'mysql:host=' . $dbHost . ';port=' . $dbPort . ';dbname=' . $dbName . ';charset=utf8mb4';
    $pdo = new PDO($dsn, $dbUser, $dbPassword, array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 2
    ));
    $query = $pdo->query('SELECT COUNT(DISTINCT `account_id`) AS `total` FROM `char` WHERE `online` = 1');
    $result = $query->fetch();
    $playersOnline = (int) $result['total'];
} catch (Exception $exception) {
    // No se muestra el error para no revelar datos internos de MySQL.
    $playersOnline = null;
}

$stateText = $serverOnline ? 'ONLINE' : 'OFFLINE';
$playersText = $playersOnline === null ? '-- JUGADORES' : $playersOnline . ($playersOnline === 1 ? ' JUGADOR' : ' JUGADORES');
$stateColor = $serverOnline ? '#58e6a9' : '#ff6b79';
$dotColor = $serverOnline ? '#33dc8d' : '#ff4f60';
?>
<!doctype html>
<html lang="es">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="refresh" content="15">
<meta charset="utf-8">
<style>
html,body{width:100%;height:100%;margin:0;overflow:hidden;background:#071427;color:#fff;font-family:Arial,sans-serif}
.box{height:54px;border:2px solid #c99835;background:#071427;text-align:center;box-sizing:border-box;padding-top:5px}
.title{font-size:9px;letter-spacing:2px;color:#90bde9;text-transform:uppercase}
.line{margin-top:3px;white-space:nowrap}
.state{font-size:14px;font-weight:bold;color:<?php echo $stateColor; ?>}
.dot{display:inline-block;width:7px;height:7px;border-radius:7px;margin-right:5px;background:<?php echo $dotColor; ?>}
.separator{display:inline-block;margin:0 8px;color:#c99835}
.players{font-size:11px;font-weight:bold;color:#dcecff}
</style>
</head>
<body>
<div class="box">
    <div class="title">ESTADO DEL SERVIDOR</div>
    <div class="line">
        <span class="state"><span class="dot"></span><?php echo $stateText; ?></span>
        <span class="separator">|</span>
        <span class="players"><?php echo htmlspecialchars($playersText, ENT_QUOTES, 'UTF-8'); ?></span>
    </div>
</div>
</body>
</html>

