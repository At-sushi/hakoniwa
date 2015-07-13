<?php

/*******************************************************************

	���돔�� S.E
	
	- ���a����Ǘ��p�t�@�C�� -
	
	hako-keep.php by SERA - 2012/07/23

*******************************************************************/

require 'jcode.phps';
require 'config.php';
require 'hako-cgi.php';
require 'hako-file.php';
require 'hako-html.php';

define("READ_LINE", 1024);
$init = new Init;
$THIS_FILE = $init->baseDir . "/hako-keep.php";
$MAIN_FILE = $init->baseDir . "/hako-main.php";

//--------------------------------------------------------------------
class HTMLKP extends HTML {
	function main($data, $hako) {
		global $init;
		
		print <<<END
<CENTER><a href="{$init->baseDir}/hako-main.php"><span class="big">�g�b�v�֖߂�</span></a></CENTER>
<h1 class="title">{$init->title}<br>���a����Ǘ��c�[��</h1>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<h2>�Ǘ��l�a����ɕύX</h2><br>
<br>
<select name="ISLANDID">
$hako->islandListNoKP
</select>
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="hidden" name="mode" value="TOKP">
<input type="submit" value="�Ǘ��l�a����ɕύX">
</form>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<h2>�Ǘ��l�a���������</h2><br>
<br>
<select name="ISLANDID">
$hako->islandListKP
</select>
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="hidden" name="mode" value="FROMKP">
<input type="submit" value="�Ǘ��l�a���������">
</form>
END;
	}
}

class Hako extends HakoIO {
	var $islandListNoKP;	// ���ʂ̓����X�g
	var $islandListKP;	// �Ǘ��l�a���蓇���X�g
	
	function init($cgi) {
		$this->readIslandsFile($cgi);
		$this->islandListNoKP = "<option value=\"0\"></option>\n";
		$this->islandListKP = "<option value=\"0\"></option>\n";
		for($i = 0; $i < $this->islandNumber; $i++) {
			$name = $this->islands[$i]['name'];
			$id = $this->islands[$i]['id'];
			$keep = $this->islands[$i]['keep'];
			if($keep == 1) {
				$this->islandListKP .= "<option value=\"$id\">${name}��</option>\n";
			} else {
				$this->islandListNoKP .= "<option value=\"$id\">${name}��</option>\n";
			}
		}
	}
}

class Main {
	var $mode;
	var $dataSet = array();
	
	function execute() {
		$html = new HTMLKP;
		$cgi = new Cgi;
		$hako =& new Hako;
		$this->parseInputData();
		$hako->init($this);
		$cgi->getCookies();
		$html->header($cgi->dataSet);
		
		switch($this->mode) {
			case "TOKP":
				if($this->passCheck()) {
					$this->toMode($this->dataSet['ISLANDID'], $hako);
					$hako->init($this);
				}
				$html->main($this->dataSet, $hako);
				break;
				
			case "FROMKP":
				if($this->passCheck()) {
					$this->fromMode($this->dataSet['ISLANDID'], $hako);
					$hako->init($this);
				}
				$html->main($this->dataSet, $hako);
				break;
				
			case "enter":
			default:
				if($this->passCheck()) {
					$html->main($this->dataSet, $hako);
				}
				break;
		}
		$html->footer();
	}
	
	function parseInputData() {
		$this->mode = $_POST['mode'];
		if(!empty($_POST)) {
			while(list($name, $value) = each($_POST)) {
				// ���p�J�i������ΑS�p�ɕϊ����ĕԂ�
				JcodeConvert($value, 0, 2);
				$value = str_replace(",", "", $value);
				$this->dataSet["{$name}"] = $value;
			}
		}
	}
	
	function toMode($id, &$hako) {
		global $init;
		
		if ($id) {
			$num = $hako->idToNumber[$id];
			if (!$hako->islands[$num]['keep']) {
				$hako->islands[$num]['keep'] = 1;
				$hako->islandNumberKP++;
				//require 'hako-turn.php';
				//Turn::islandSort($hako);
				$hako->writeIslandsFile();
			}
		}
	}
	
	function fromMode($id, &$hako) {
		global $init;
		
		if ($id) {
			$num = $hako->idToNumber[$id];
			if ($hako->islands[$num]['keep']) {
				$hako->islands[$num]['keep'] = 0;
				$hako->islandNumberKP--;
				//require 'hako-turn.php';
				//Turn::islandSort($hako);
				$hako->writeIslandsFile();
			}
		}
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