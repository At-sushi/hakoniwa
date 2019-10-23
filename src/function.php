<?php

declare(strict_types=1);


/**
 * @param string $property getenv() の第一引数
 * @param string|null $default $_ENV[$property] がなかったときに返す値（default: null）
 * @return string|null
 */
function env(string $property, $default = null)
{
    $env = getenv($property);
    return $env !== false ? $env : $default;
}



function ls(string $dir): array
{
    $directory = new \DirectoryIterator($dir);
    $ls = ["dir" => [], "file" => []];
    foreach ($directory as $fileinfo) {
        if (!$fileinfo->isDot()) {
            $ls[$fileinfo->getType()][] = $fileinfo->getPathname();
        }
    }

    return $ls;
}



function ls_R(string $dir, array $exclude_prefix = []): array
{
    $rii =  new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator(
            $dir,
            \FilesystemIterator::SKIP_DOTS
            | \FilesystemIterator::KEY_AS_PATHNAME
            | \FilesystemIterator::CURRENT_AS_PATHNAME
        ),
        \RecursiveIteratorIterator::LEAVES_ONLY
    );
    $dir = realpath($dir);

    $filelist = [];
    foreach ($rii as $key => $value) {
        $rel = mb_substr(realpath($key), mb_strlen($dir));
        if (startsWith($rel, $exclude_prefix)) {
            $filelist[$rel] = $value;
        }
    }

    return $filelist;
}



function println(...$strs): void
{
    foreach ($strs as $str) {
        echo $str;
    }
    echo PHP_EOL;
}



/**
 * 文字列 $str が $prefix から始まるかどうかを返す
 * @param  string $str    検索対象
 * @param  string|string[]  $prefix 検索したい文字列・文字列の配列
 * @return bool
 */
function startsWith(string $str, $prefix): bool
{
    $type = gettype($prefix);
    if ($type === "string") {
        return mb_substr($str, 0, mb_strlen($prefix)) === $prefix;
    } elseif ($type === "array") {
        return 0 !== count(array_filter($prefix, function ($v) use ($str) {
            return mb_substr($str, 0, mb_strlen($v)) === $v;
        }));
    }
    trigger_error("Arguments #1 require type of `String[] | String` (Actual `{$type}`)", E_USER_WARNING);
}
