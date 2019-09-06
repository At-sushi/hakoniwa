<?php

declare(strict_types=1);

namespace Hakoniwa;

require_once "hako-init-default.php";
require_once "hako-init.php";

/**
 * LaunchTest (Assert)
 * [NOTE]
 * autoload より先に居ないとチェック走らす意味がないので
 * ここまではrequire地獄
 */
final class LaunchTest extends \Hakoniwa\Init
{
    private $needExts = [
        "mbstring",
        "curl", // composer周りで使ってる
        "pdo", // composer周りで使ってる
        "dom" // composer周りで使ってる
    ];
    private function HasExtension(string $extName): bool
    {
        return extension_loaded($extName);
    }

    private function check_extension_loaded(): void
    {
        $noHaveExt = "";
        foreach ($this->needExts as $ext) {
            if (!$this->HasExtension($ext)) {
                $noHaveExt .= ", $ext";
                //-> - php.iniから`$ext.so`/`php_$ext.dll`を有効にする
                //   - `php-$ext`みたいな名前のパッケージをインストールする
            }
        }
        if ($noHaveExt !== "") {
            $noHaveExt = substr($noHaveExt, 2);

            throw new \Error("動作に必要なPHPモジュール`$noHaveExt`が読み込まれていません。");
        }
    }

    private function check_version(): void
    {
        if (version_compare(PHP_VERSION, "7.1.0", "<=")) {
            throw new \Error("動作に必要なPHPのバージョン条件（>= 7.1.0）を満たしていません。\nPHP_VERSION: `".PHP_VERSION."`");
        }
    }

    private function check_license(): void
    {
        if (!file_exists(ROOT."/LICENSE")) {
            throw new \Exception("ディレクトリ`".ROOT."`に`LICENSE`ファイルがありません。");
        }

        $hash_local = hash_file("sha256", ROOT."/LICENSE");
        $hash_remote = hash_file("sha256", "https://www.gnu.org/licenses/agpl-3.0.txt");
        if (!hash_equals($hash_local, $hash_remote)) {
            throw new \Exception("ライセンスファイルの内容が間違っているか、何らかの変更が認められます。\nプログラムの再インストールを推奨します。");
        }
    }

    public function __construct()
    {
        try {
            $this->check_version();
            $this->check_extension_loaded();
            $this->check_license();
        } catch (\Throwable $e) {
            if (php_sapi_name() === "cli") {
                echo "[LaunchTest::ERR] ".$e->getMessage()."\n";
            } else {
                $this->view_head();
                $this->print_nl2br($e->getMessage());
                $this->view_foot();
                die;
            }
        }
    }

    private function view_head(): void
    {
        echo <<<END
<!doctype html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>$this->title 動作テスト</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.3/semantic.min.css" integrity="sha256-ncjDAd2/rm/vaNTqp7Kk96MfSeHACtbiDU9NWKqNuCI=" crossorigin="anonymous" />
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha256-3edrmyuQ0w65f8gfBsqowzjJe2iM6n0nKciPUp8y+7E=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.3/semantic.min.js" integrity="sha256-0gIvTkdsp0OOeV8tx3UTdi3ehppGwZbzQMvZJYhbzsE=" crossorigin="anonymous"></script>
<style type="text/css">body{background-color:#dadada;}body > .grid{height:100%;}.column{max-width:78vw;}</style>
</head>
<body>
<div class="ui middle aligned center aligned grid">
    <div class="column">
        <h2 class="ui teal header">
            <div class="content"><i class="red exclamation triangle icon"></i> $this->title 動作テスト</div>
        </h2>
        <div class="ui stacked left aligned segment">
            <p>ご利用の環境にて以下のエラーが発生したため、動作を停止しました。<br>お手数ですが、エラー内容を添えてサーバー管理者にお問合せください。</p>
        </div>

END;
    }
    private function view_foot(): void
    {
        echo <<<END
        <div id="©" class="ui stacked right aligned segment">
            <style>#©>*{color:#777}#© a{text-decoration:underline!important}</style>
            <p><a href="https://www.github.com/sotalbireo/hakoniwa" target="_blank">"hakoniwa"</a> © 2016 Sotalbireo, <a href="https://cgi-game-preservations.org/" target="_blank">CGI Game Preservations Org.</a><br>Licensed by <a href="https://www.gnu.org/licenses/agpl.html" target="_blank">AGPL v3.0.</a></p>
        </div>
    </div>
</div>
</body>
</html>

END;
    }

    public function print_nl2br($str): void
    {
        echo "<div class=\"ui stacked left aligned segment\">", nl2br($str, false), "</div>\n";
    }

    public function print_debug(...$args): void
    {
        $f = $this->HasExtension("Xdebug");
        foreach ($args as $arg) {
            if (!$f) {
                echo "<pre>";
            }
            var_dump($arg);
            if (!$f) {
                echo "</pre>\n";
            }
        }
    }
}

new LaunchTest;
