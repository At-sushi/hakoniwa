<?php

namespace Hakoniwa\Admin\Maintenance;

require_once MODELPATH.'/admin.php';

/**
 * 箱庭諸島 S.E
 * @author hiro <@hiro0218>
 */

class Mente extends \Admin
{
    public function __construct()
    {
        $html = new \HtmlMente;
        $cgi = new \Cgi;
        $this->parseInputData();
        $cgi->getCookies();

        $this->dataSet['PASSWORD'] = $this->dataSet['PASSWORD'] ?? '';
        $html->header();

        switch ($this->mode) {
            case "NEW":
                if (\Util::checkPassword('', $this->dataSet['PASSWORD'])) {
                    $this->newMode();
                }
                $html->main($this->dataSet);

                break;

            case "CURRENT":
                if (\Util::checkPassword('', $this->dataSet['PASSWORD'])) {
                    $this->currentMode($this->dataSet['NUMBER']);
                }
                $html->main($this->dataSet);

                break;

            case "DELETE":
                if (\Util::checkPassword('', $this->dataSet['PASSWORD'])) {
                    $this->delMode($this->dataSet['NUMBER']);
                }
                $html->main($this->dataSet);

                break;

            case "NTIME":
                if (\Util::checkPassword('', $this->dataSet['PASSWORD'])) {
                    $this->timeMode();
                }
                $html->main($this->dataSet);

                break;

            case "STIME":
                if (\Util::checkPassword('', $this->dataSet['PASSWORD'])) {
                    $this->stimeMode($this->dataSet['SSEC']);
                }
                $html->main($this->dataSet);

                break;

            case "setup":
                $this->setupMode();
                $html->enter();

                break;

            case "enter":
                if (\Util::checkPassword('', $this->dataSet['PASSWORD'])) {
                    $html->main($this->dataSet);
                }

                break;

            default:
                $html->enter();

                break;
        }

        if (!\Util::checkPassword('', $this->dataSet['PASSWORD'])) {
            \HakoError::wrongPassword();
        }
        $html->footer();
    }

    public function newMode()
    {
        global $init;

        $now = $_SERVER['REQUEST_TIME'];
        $now -= $now % $init->unitTime;

        // 総合データファイル
        $fileName = $init->dirName.'/hakojima.dat';
        $txt = <<<EOM
1
$now
0
1

EOM;
        file_put_contents($fileName, $txt);

        // 同盟ファイル生成
        touch($init->dirName.'/ally.dat');

        // アクセスログ生成
        touch($init->dirName.'/'.$init->logname);

        // .htaccess生成
        $fileName = $init->dirName.'/.htaccess';
        $fp = fopen($fileName, "w");
        fputs($fp, "Options -Indexes\n");
        fclose($fp);
        chmod($fileName, 0644);
    }



    public function delMode($id)
    {
        global $init;

        $dirName = strcmp($id, "") == 0 ? $init->dirName : $init->dirName.".bak$id";
        $this->rmTree($dirName);
    }

    public function timeMode()
    {
        $date = $this->dataSet['date'].' '.$this->dataSet['time'];
        $date = date_parse_from_format('Y-m-d H:i', $date);
        if (!checkdate($date['month'], $date['day'], $date['year'])) {
            throw new Exception("指定された日付が不正", 1);
        }
        $toSetDate = mktime($date['hour'], $date['minute'], 0, $date['month'], $date['day'], $date['year']);
        $this->stimeMode($toSetDate);
    }

    public function stimeMode($sec)
    {
        global $init;

        $fileName = $init->dirName.'/hakojima.dat';
        $fp = fopen($fileName, "r+");
        $buffer = [];
        while (false !== ($line = fgets($fp, READ_LINE))) {
            array_push($buffer, $line);
        }
        $buffer[1] = "$sec\n";
        fseek($fp, 0);
        while (null !== ($line = array_shift($buffer))) {
            fputs($fp, $line);
        }
        fclose($fp);
    }

    public function currentMode($id)
    {
        global $init;

        $this->rmTree($init->dirName);
        $dir = opendir($init->dirName.".bak{$id}/");
        while (false !== ($fileName = readdir($dir))) {
            if ($fileName != "." && $fileName != "..") {
                copy($init->dirName.".bak{$id}/".$fileName, $init->dirName.'/'.$fileName);
            }
        }
        closedir($dir);
    }

    /**
     * 引数にとったディレクトリの中身をすべて削除する
     * @param  string $dirName 子ファイルを削除したいディレクトリ
     * @return void
     */
    public function rmTree($dirName)
    {
        if (is_dir($dirName)) {
            $dir = opendir($dirName.'/');
            while (false !== ($fileName = readdir($dir))) {
                if ($fileName != "." && $fileName != "..") {
                    unlink($dirName.'/'.$fileName);
                }
            }
            closedir($dir);
        }
    }

    public function setupMode()
    {
        global $init;

        function isValidPasswd($passwd1='', $passwd2='')
        {
            return $passwd1!=='' && $passwd2!=='' && strcmp($passwd1, $passwd2)===0;
        }

        if (!isValidPasswd($this->dataSet['MPASS1'], $this->dataSet['MPASS2'])) {
            HakoError::wrongMasterPassword();

            return;
        } elseif (!isValidPasswd($this->dataSet['SPASS1'], $this->dataSet['SPASS2'])) {
            HakoError::wrongSpecialPassword();

            return;
        }
        if (isValidPasswd($this->dataSet['MPASS1'], $this->dataSet['SPASS1'])) {
            \HakoError::necessaryBeSetAnotherPassword();

            return;
        }

        $masterPasswd  = \Util::encode($this->dataSet['MPASS1'], false);
        $specialPasswd = \Util::encode($this->dataSet['SPASS1'], false);
        $fp = fopen($init->passwordFile, "w");
        fputs($fp, "$masterPasswd\n");
        fputs($fp, "$specialPasswd\n");
        fclose($fp);
    }
}
