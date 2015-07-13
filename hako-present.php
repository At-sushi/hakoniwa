<?php

/*******************************************************************

	���돔�� S.E
	
	- �v���[���g��`�p�t�@�C�� -
	
	hako-present.php by SERA - 2012/04/03

*******************************************************************/

require 'jcode.phps';
require 'config.php';
require 'hako-cgi.php';
require 'hako-file.php';
require 'hako-html.php';
require 'hako-util.php';

define("READ_LINE", 1024);
$init = new Init;
$THIS_FILE = $init->baseDir . "/hako-present.php";
$MAIN_FILE = $init->baseDir . "/hako-main.php";

//--------------------------------------------------------------------
class HtmlPresent extends HTML {
	function enter() {
		global $init;
		
		print <<<END
<CENTER><a href="{$init->baseDir}/hako-main.php"><span class="big">�g�b�v�֖߂�</span></a></CENTER>
<h1 class="title">{$init->title}<br>�v���[���g�c�[��</h1>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<strong>�p�X���[�h�F</strong>
<input type="password" size="32" maxlength="32" name="PASSWORD">
<input type="hidden" name="mode" value="enter">
<input type="submit" value="�����e�i���X">
</form>
END;
	}
	
	function main($data, $hako) {
		global $init;
		
		$width = $init->islandSize * 32 + 50;
		$height = $init->islandSize * 32 + 100;
		$defaultTarget = ($init->targetIsland == 1) ? $island['id'] : $hako->defaultTarget;
		
		print <<<END
<script type="text/javascript">
<!--
var w;
var p = 0;

function settarget(part){
	p = part.options[part.selectedIndex].value;
}

function targetopen() {
	w = window.open("{$GLOBALS['MAIN_FILE']}?target=" + p, "","width={$width},height={$height},scrollbars=1,resizable=1,toolbar=1,menubar=1,location=1,directories=0,status=1");
}
//-->
</script>
<CENTER><a href="{$init->baseDir}/hako-main.php"><span class="big">�g�b�v�֖߂�</span></a></CENTER>
<h1 class="title">{$init->title}<br>�v���[���g�c�[��</h1>

<h2>�Ǘ��l����̃v���[���g</h2>
<p>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<select name="ISLANDID">
$hako->islandList
</select>�ɁA
�����F<input type="text" size="10" name="MONEY" value="0">{$init->unitMoney}�A
�H���F<input type="text" size="10" name="FOOD" value="0">{$init->unitFood}�� 
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="hidden" name="mode" value="PRESENT">
<input type="submit" value="�v���[���g����">
</form>
</p>
<h2>�Ǘ��l����̍ЊQ�v���[���g&hearts;</h2>
<p>
<form action="{$GLOBALS['THIS_FILE']}" method="post" name="InputPlan">
<select name="ISLANDID" onchange="settarget(this);">
$hako->islandList
</select>�́A(
<select name="POINTX">
END;
		print "<option value=\"0\" selected>0</option>\n";
		for($i = 1; $i < $init->islandSize; $i++) {
			print "<option value=\"{$i}\">{$i}</option>\n";
		}
		print "</select>, <select name=\"POINTY\">";
		print "<option value=\"0\" selected>0</option>\n";
		for($i = 1; $i < $init->islandSize; $i++) {
			print "<option value=\"{$i}\">{$i}</option>\n";
		}
		print <<<END
</select> )�ɁA
<select name="PUNISH">
<option VALUE="0">�L�����Z��</option>
<option VALUE="1">�n�k</option>
<option VALUE="2">�Ôg</option>
<option VALUE="3">���b</option>
<option VALUE="4">�n�Ւ���</option>
<option VALUE="5">�䕗</option>
<option VALUE="6">����覐΁�</option>
<option VALUE="7">覐΁�</option>
<option VALUE="8">���΁�</option>
</select>�� 
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="hidden" name="mode" value="PUNISH">
<input type="submit" value="�v���[���g�����Ⴄ"><br>
<input type="button" value="�ڕW�ߑ�" onClick="javascript: targetopen();">
</form>
</p>
<h2>���݂̃v���[���g���X�g</h2>
END;
		for ($i=0; $i < $hako->islandNumber; $i++) {
			$present =&$hako->islands[$i]['present'];
			$name =&$hako->islands[$i]['name'];
			if ( $present['item'] == 0 ) {
				if ( $present['px'] != 0 ) {
					$money = $present['px'] . $init->unitMoney;
					print "{$init->tagName_}{$name}��{$init->_tagName}��<strong>{$money}</strong>�̎���<br>\n";
				}
				if ( $present['py'] != 0 ) {
					$food = $present['py'] . $init->unitFood;
					print "{$init->tagName_}{$name}��{$init->_tagName}��<strong>{$food}</strong>�̐H��<br>\n";
				}
			} elseif ( $present['item'] > 0 ) {
				$items = array ('�n�k','�Ôg','���b','�n�Ւ���','�䕗','����覐�','覐�','����');
				$item = $items[$present['item'] - 1];
				if ( $present['item'] < 9 ) {
					$point = ($present['item'] < 6) ? '' : '(' . $present['px'] . ',' . $present['py'] . ')';
					print "{$init->tagName_}{$name}��{$point}{$init->_tagName}��{$init->tagDisaster_}{$item}{$init->_tagDisaster}<br>\n";
				}
			}
		}
	}
}

class Hako extends HakoIO {
	var $islandList;  // �����X�g
	
	function init($cgi) {
		$this->readIslandsFile($cgi);
		$this->readPresentFile();
		
		$this->islandList = "<option value=\"0\"></option>\n";
		for($i = 0; $i < ( $this->islandNumber ); $i++) {
			$name = $this->islands[$i]['name'];
			$id = $this->islands[$i]['id'];
			$this->islandList .= "<option value=\"$id\">${name}��</option>\n";
		}
	}
}

class Main {
	var $mode;
	var $dataSet = array();
	
	function execute() {
		$html = new HtmlPresent;
		$hako =& new Hako;
		$cgi = new Cgi;
		$this->parseInputData();
		$hako->init($this);
		$cgi->getCookies();
		$html->header($cgi->dataSet);
		
		switch($this->mode) {
			case "PRESENT":
				if($this->passCheck()) {
					$this->present($this->dataSet, $hako);
				}
				$html->main($this->dataSet, $hako);
				break;
				
			case "PUNISH":
				if($this->passCheck()) {
					$this->punish($this->dataSet, $hako);
				}
				$html->main($this->dataSet, $hako);
				break;
				
			case "enter":
				if($this->passCheck()) {
					$html->main($this->dataSet, $hako);
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
				// ���p�J�i������ΑS�p�ɕϊ����ĕԂ�
				JcodeConvert($value, 0, 2);
				$value = str_replace(",", "", $value);
				$this->dataSet["{$name}"] = $value;
			}
		}
	}
	
	function present($data, &$hako) {
		global $init;
		
		if ($data['ISLANDID']) {
			$num = $hako->idToNumber[$data['ISLANDID']];
			$hako->islands[$num]['present']['item'] = 0;
			$hako->islands[$num]['present']['px'] = $data['MONEY'];
			$hako->islands[$num]['present']['py'] = $data['FOOD'];
			$hako->writePresentFile();
		}
	}
	
	function punish($data, &$hako) {
		global $init;
		
		if ($data['ISLANDID']) {
			$punish =& $data['PUNISH'];
			if (( $punish >= 0) && ( $punish <= 8 )) {
				$num = $hako->idToNumber[$data['ISLANDID']];
				$hako->islands[$num]['present']['item'] = $punish;
				$hako->islands[$num]['present']['px'] = ( $punish < 6 ) ? 0 : $data['POINTX'];
				$hako->islands[$num]['present']['py'] = ( $punish < 6 ) ? 0 : $data['POINTY'];
				$hako->writePresentFile();
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
