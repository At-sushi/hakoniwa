<?php declare(strict_types=1);

require __DIR__."/../_/getPostApi.php";
require __DIR__."/../../config.php";



function rimraf(string $path): bool
{
    return (new class {
        use \Hakoniwa\Model\FileIO {
            rimraf as public;
        }
    })->rimraf($path);
}

$data["exist"] = is_dir($INPUT["dir"]);
$data["result"] = $data["exist"] ? rimraf($INPUT["dir"]) : false;



header("Content-Type:application/json;charset=utf-8");
echo json_encode($data ?? []);
exit;
