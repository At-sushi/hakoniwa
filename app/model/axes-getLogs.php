<?php
header('Content-Type: application/octet-stream');
header('X-Content-Type-Options: nosniff');
// ini_set('display_errors', 1);
// error_reporting(E_ALL);



require_once realpath(dirname(__FILE__, 3)).DIRECTORY_SEPARATOR.'config.php';

$init = new Init();

$filePath = DOCROOT.DIRECTORY_SEPARATOR.$init->dirName.DIRECTORY_SEPARATOR.$init->logname;

$file = file_exists($filePath)? file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : false;
$return = [];
if ($file!==false) {
    foreach ($file as $value) {
        list($datetime, $islId, $islName, $deptIp, $deptName) = explode(',', $value);
        list($date, $time) = explode(' ', $datetime);

        $deptIp = "127.0.0.1";
        $deptName = "(Test mode)";

        $return["data"][] = compact('date', 'time', 'islId', 'islName', 'deptIp', 'deptName');
    }
} else {
    $return["error"] = [
        "code"    => "400",
        "message" => "ファイルの受信に失敗しました。管理者にお問い合わせください。"
    ];
}

echo(json_encode($return));
