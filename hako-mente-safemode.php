<?php

/*******************************************************************

	���돔�� S.E
	
	- �����e�i���X�i�Z�[�t���[�h�j�p�t�@�C�� -
	
	hako-mente-safemode.php by SERA - 2012/05/07

*******************************************************************/

require 'jcode.phps';
require 'config.php';
require 'hako-cgi.php';
require 'hako-html.php';

define("READ_LINE", 1024);
$init = new Init;
$THIS_FILE = $init->baseDir . "/hako-mente-safemode.php";

class HtmlMente extends HTML {
	function enter() {
		global $init;
		
		print "<CENTER><a href=\"{$init->baseDir}/hako-main.php\"><span class=\"big\">�g�b�v�֖߂�</span></a></CENTER>\n";
		print "<h1 class=\"title\">{$init->title}<br>�����e�i���X�c�[��</h1>";
		if(file_exists("{$init->passwordFile}")) {
			print <<<END
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<strong>�p�X���[�h�F</strong>
<input type="password" size="32" maxlength="32" name="PASSWORD">
<input type="hidden" name="mode" value="enter">
<input type="submit" value="�����e�i���X">
END;
		} else {
			print <<<END
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<H2>�}�X�^�p�X���[�h�Ɠ���p�X���[�h�����߂Ă��������B</H2>
<P>�����̓~�X��h�����߂ɁA���ꂼ��Q�񂸂��͂��Ă��������B</P>
<B>�}�X�^�p�X���[�h�F</B><BR>
(1) <INPUT type="password" name="MPASS1" value="$mpass1">&nbsp;&nbsp;(2) <INPUT type="password" name="MPASS2" value="$mpass2"><BR>
<BR>
<B>����p�X���[�h�F</B><BR>
(1) <INPUT type="password" name="SPASS1" value="$spass1">&nbsp;&nbsp;(2) <INPUT type="password" name="SPASS2" value="$spass2"><BR>
<BR>
<input type="hidden" name="mode" value="setup">
<INPUT type="submit" value="�p�X���[�h��ݒ肷��">
END;
		}
		print "</form>\n";
	}
	
	function main($data) {
		global $init;
		
		print "<CENTER><a href=\"{$init->baseDir}/hako-main.php\"><span class=\"big\">�g�b�v�֖߂�</span></a></CENTER>\n";
		print "<h1 class=\"title\">{$init->title}<br>�����e�i���X�c�[��</h1>\n";
		// �f�[�^�ۑ��p�f�B���N�g���̑��݃`�F�b�N
		if(!is_dir("{$init->dirName}")) {
			print "{$init->tagBig_}�f�[�^�ۑ��p�̃f�B���N�g�������݂��܂���{$init->_tagBig}";
			HTML::footer();
			exit;
		}
		// �f�[�^�ۑ��p�f�B���N�g���̃p�[�~�b�V�����`�F�b�N
		if(!is_writeable("{$init->dirName}") || !is_readable("{$init->dirName}")) {
			print "{$init->tagBig_}�f�[�^�ۑ��p�̃f�B���N�g���̃p�[�~�b�V�������s���ł��B�p�[�~�b�V������0777���̒l�ɐݒ肵�Ă��������B{$init->_tagBig}";
			HTML::footer();
			exit;
		}
		if(is_file("{$init->dirName}/hakojima.dat")) {
			$this->dataPrint($data);
		} else {
			print "<hr>\n";
			print "<form action=\"{$GLOBALS['THIS_FILE']}\" method=\"post\">\n";
			print "<input type=\"hidden\" name=\"PASSWORD\" value=\"{$data['PASSWORD']}\">\n";
			print "<input type=\"hidden\" name=\"mode\" value=\"NEW\">\n";
			print "<input type=\"submit\" value=\"�V�����f�[�^�����\">\n";
			print "</form>\n";
		}
		// �o�b�N�A�b�v�f�[�^
		$dir = opendir("./");
		while($dn = readdir($dir)) {
			if(preg_match("/{$init->dirName}\.bak(.*)$/", $dn, $suf)) {
				if (is_file("{$init->dirName}.bak{$suf[1]}/hakojima.dat")) {
					$this->dataPrint($data, $suf[1]);
				}
			}
		}
		closedir($dir);
	}
	
	// �\�����[�h
	function dataPrint($data, $suf = "") {
		global $init;
		
		print "<HR>";
		if(strcmp($suf, "") == 0) {
			$fp = fopen("{$init->dirName}/hakojima.dat", "r");
			print "<h2>�����f�[�^</h2>\n";
		} else {
			$fp = fopen("{$init->dirName}.bak{$suf}/hakojima.dat", "r");
			print "<h2>�o�b�N�A�b�v{$suf}</h2>\n";
		}
		$lastTurn = chop(fgets($fp, READ_LINE));
		$lastTime = chop(fgets($fp, READ_LINE));
		fclose($fp);
		$timeString = timeToString($lastTime);
		
		print <<<END
<strong>�^�[��$lastTurn</strong><br>
<strong>�ŏI�X�V����</strong>:$timeString<br>
<strong>�ŏI�X�V����(�b���\\��)</strong>:1970�N1��1������$lastTime �b<br>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="hidden" name="mode" value="DELETE">
<input type="hidden" name="NUMBER" value="{$suf}">
<input type="submit" value="���̃f�[�^���폜">
</form>
END;
		if(strcmp($suf, "") == 0) {
			$time = localtime($lastTime, TRUE);
			$time['tm_year'] += 1900;
			$time['tm_mon']++;
			print <<<END
<h2>�ŏI�X�V���Ԃ̕ύX</h2>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="hidden" name="mode" value="NTIME">
<input type="hidden" name="NUMBER" value="{$suf}">
<input type="text" size="4" name="YEAR" value="{$time['tm_year']}">�N
<input type="text" size="2" name="MON" value="{$time['tm_mon']}">��
<input type="text" size="2" name="DATE" value="{$time['tm_mday']}">��
<input type="text" size="2" name="HOUR" value="{$time['tm_hour']}">��
<input type="text" size="2" name="MIN" value="{$time['tm_min']}">��
<input type="text" size="2" name="NSEC" value="{$time['tm_sec']}">�b
<input type="submit" value="�ύX">
</form>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="hidden" name="mode" value="STIME">
<input type="hidden" name="NUMBER" value="{$suf}">
1970�N1��1������<input type="text" size="32" name="SSEC" value="$lastTime">�b
<input type="submit" value="�b�w��ŕύX">
</form>
END;
		} else {
			print <<<END
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="hidden" name="NUMBER" value="{$suf}">
<input type="hidden" name="mode" value="CURRENT">
<input type="submit" value="���̃f�[�^��������">
</form>
END;
		}
	}
}

function timeToString($t) {
	$time = localtime($t, TRUE);
	$time['tm_year'] += 1900;
	$time['tm_mon']++;
	return "{$time['tm_year']}�N {$time['tm_mon']}�� {$time['tm_mday']}�� {$time['tm_hour']}�� {$time['tm_min']}�� {$time['tm_sec']}�b";
}

class Main {
	var $mode;
	var $dataSet = array();
	function execute() {
		$html = new HtmlMente;
		$cgi = new Cgi;
		$this->parseInputData();
		$cgi->getCookies();
		$html->header($cgi->dataSet);
		
		switch($this->mode) {
			case "NEW":
				if($this->passCheck()) {
					$this->newMode();
				}
				$html->main($this->dataSet);
				break;
				
			case "CURRENT":
				if($this->passCheck()) {
					$this->currentMode($this->dataSet['NUMBER']);
				}
				$html->main($this->dataSet);
				break;
				
			case "DELETE":
				if($this->passCheck()) {
					$this->delMode($this->dataSet['NUMBER']);
				}
				$html->main($this->dataSet);
				break;
				
			case "NTIME":
				if($this->passCheck()) {
					$this->timeMode();
				}
				$html->main($this->dataSet);
				break;
				
			case "STIME":
				if($this->passCheck()) {
					$this->stimeMode($this->dataSet['SSEC']);
				}
				$html->main($this->dataSet);
				break;
				
			case "setup":
				$this->setupMode();
				$html->enter();
				break;
				
			case "enter":
				if($this->passCheck()) {
					$html->main($this->dataSet);
				}
				break;
				
			default:
				$html->enter();
				break;
		}
		$html->footer();
	}
	
	function parseInputData() {
		$this->mode = $_POST['mode'];
		if(!empty($_POST)) {
			while(list($name, $value) = each($_POST)) {
				// $value = Util::sjis_convert($value);
				// ���p�J�i������ΑS�p�ɕϊ����ĕԂ�
				// $value = i18n_ja_jp_hantozen($value,"KHV");
				JcodeConvert($value, 0, 2);
				$value = str_replace(",", "", $value);
				$this->dataSet["{$name}"] = $value;
			}
		}
	}
	
	function newMode() {
		global $init;
		
		// mkdir($init->dirName, $init->dirMode);
		// ���݂̎��Ԃ��擾
		$now = time();
		$now = $now - ($now % ($init->unitTime));
		$fileName = "{$init->dirName}/hakojima.dat";
		touch($fileName);
		$fp = fopen($fileName, "w");
		fputs($fp, "1\n");
		fputs($fp, "{$now}\n");
		fputs($fp, "0\n");
		fputs($fp, "1\n");
		fclose($fp);
		
		// �����t�@�C������
		$fileName = "{$init->dirName}/ally.dat";
		$fp = fopen($fileName, "w");
		fclose($fp);
		
		// �A�N�Z�X���O����
		$fileName = "{$init->dirName}/{$init->logname}";
		$fp = fopen($fileName, "w");
		fclose($fp);
		
		// .htaccess����
		$fileName = "{$init->dirName}/.htaccess";
		$fp = fopen($fileName, "w");
		fputs($fp, "Options -Indexes");
		fclose($fp);
	}
	
	function delMode($id) {
		global $init;
		
		if(strcmp($id, "") == 0) {
			$dirName = "{$init->dirName}";
		} else {
			$dirName = "{$init->dirName}.bak{$id}";
		}
		$this->rmTree($dirName);
	}
	
	function timeMode() {
		$year = $this->dataSet['YEAR'];
		$day = $this->dataSet['DATE'];
		$mon = $this->dataSet['MON'];
		$hour = $this->dataSet['HOUR'];
		$min = $this->dataSet['MIN'];
		$sec = $this->dataSet['NSEC'];
		$ctSec = mktime($hour, $min, $sec, $mon, $day, $year);
		$this->stimeMode($ctSec);
	}
	
	function stimeMode($sec) {
		global $init;
		
		$fileName = "{$init->dirName}/hakojima.dat";
		$fp = fopen($fileName, "r+");
		$buffer = array();
		while($line = fgets($fp, READ_LINE)) {
			array_push($buffer, $line);
		}
		$buffer[1] = "{$sec}\n";
		fseek($fp, 0);
		while($line = array_shift($buffer)) {
			fputs($fp, $line);
		}
		fclose($fp);
	}
	
	function currentMode($id) {
		global $init;
		
		$this->rmTree("{$init->dirName}");
		// mkdir("{$init->dirName}", $init->dirMode);
		$dir = opendir("{$init->dirName}.bak{$id}/");
		while($fileName = readdir($dir)) {
			if(!(strcmp($fileName, ".") == 0 || strcmp($fileName, "..") == 0))
				copy("{$init->dirName}.bak{$id}/{$fileName}", "{$init->dirName}/{$fileName}");
		} 
		closedir($dir);
	}
	
	function rmTree($dirName) {
		if(is_dir("{$dirName}")) {
			$dir = opendir("{$dirName}/");
			while($fileName = readdir($dir)) {
				if(!(strcmp($fileName, ".") == 0 || strcmp($fileName, "..") == 0))
					unlink("{$dirName}/{$fileName}");
			}
			closedir($dir);
			// rmdir($dirName);
		}
	}
	
	function setupMode() {
		global $init;
		
		if(empty($this->dataSet['MPASS1']) || empty($this->dataSet['MPASS2']) || strcmp($this->dataSet['MPASS1'], $this->dataSet['MPASS2'])) {
			print "<h2>�}�X�^�p�X���[�h�����͂���Ă��Ȃ����Ԉ���Ă��܂�</h2>\n";
			return 0;
		} else if(empty($this->dataSet['SPASS1']) || empty($this->dataSet['SPASS2']) || strcmp($this->dataSet['SPASS1'], $this->dataSet['SPASS2'])) {
			print "<h2>����p�X���[�h�����͂���Ă��Ȃ����Ԉ���Ă��܂�</h2>\n";
			return 0;
		}
		$masterPassword = crypt($this->dataSet['MPASS1'], 'ma');
		$specialPassword = crypt($this->dataSet['SPASS1'], 'sp');
		$fp = fopen("{$init->passwordFile}", "w");
		fputs($fp, "$masterPassword\n");
		fputs($fp, "$specialPassword\n");
		fclose($fp);
	}
	
	function passCheck() {
		global $init;
		
		if(file_exists("{$init->passwordFile}")) {
			$fp = fopen("{$init->passwordFile}", "r");
			$masterPassword = chop(fgets($fp, READ_LINE));
			fclose($fp);
		}
		if(strcmp(crypt($this->dataSet['PASSWORD'], 'ma'), $masterPassword) == 0) {
			return 1;
		} else {
			print "<h2>�p�X���[�h���Ⴂ�܂��B</h2>\n";
			return 0;
		}
	}
}

$start = new Main();
$start->execute();

?>
