<?php

/*******************************************************************

	���돔�� S.E
	
	- �A�N�Z�X��͗p�t�@�C�� -
	
	hako-axes.php by SERA - 2012/06/29

*******************************************************************/

require 'config.php';
require 'hako-cgi.php';
require 'hako-html.php';

define("READ_LINE", 1024);
$init = new Init;
$THIS_FILE = $init->baseDir . "/hako-axes.php";

//--------------------------------------------------------------------
class HtmlMente extends HTML {
	function enter() {
		global $init;
		
		print <<<END
<h1 class="title">{$init->title}<br>�A�N�Z�X���O�{����</h1>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<strong>�p�X���[�h�F</strong>
<input type="password" size="32" maxlength="32" name="PASSWORD">
<input type="hidden" name="mode" value="enter">
<input type="submit" value="��������">
</form>
END;
	}
	
	function main($data) {
		global $init;
		print "<CENTER><a href=\"{$init->baseDir}/hako-main.php\"><span class=\"big\">�g�b�v�֖߂�</span></a></CENTER>\n";
		print "<h1 class=\"title\">{$init->title}<br>�A�N�Z�X���O�{����</h1>\n";
		$this->dataPrint($data);
	}
	
	// �\�����[�h
	function dataPrint($data, $suf = "") {
		global $init;
		
		print "<HR>";
		print <<<END
<br>
<h2>�A�N�Z�X���O</h2>
<form action="#">
<input type="button" value="�I�[�g�t�B���^�\��" onclick="Button_DispFilter(this, 'DATA-TABLE')" onkeypress="Button_DispFilter(this, 'DATA-TABLE')">
<table id="DATA-TABLE">
<thead>
<tr class="NumberCell">
<td scope="row"><input type="button" tabindex="1" onclick="g_cSortTable.Button_Sort('DATA-TABLE', [0])" onkeypress="g_cSortTable.Button_Sort('DATA-TABLE', [0])" value="���O�C����������"></td>
<td scope="row"><input type="button" tabindex="2" onclick="g_cSortTable.Button_Sort('DATA-TABLE', [1, 0])" onkeypress="g_cSortTable.Button_Sort('DATA-TABLE', [1, 0])" value="���h�c"></td>
<td scope="row"><input type="button" tabindex="3" onclick="g_cSortTable.Button_Sort('DATA-TABLE', [2, 0])" onkeypress="g_cSortTable.Button_Sort('DATA-TABLE', [2, 0])" value="���̖��O"></td>
<td scope="row"><input type="button" tabindex="4" onclick="g_cSortTable.Button_Sort('DATA-TABLE', [3, 0])" onkeypress="g_cSortTable.Button_Sort('DATA-TABLE', [3, 0])" value="�h�o���"></td>
<td scope="row"><input type="button" tabindex="5" onclick="g_cSortTable.Button_Sort('DATA-TABLE', [4, 0])" onkeypress="g_cSortTable.Button_Sort('DATA-TABLE', [4, 0])" value="�z�X�g���"></td>
</tr>
</thead>
<tbody>
END;
		// �t�@�C����ǂݍ��ݐ�p�ŃI�[�v������
		$fp = fopen("{$init->dirName}/{$init->logname}", 'r');
		
		// �I�[�ɒB����܂Ń��[�v
		while (!feof($fp)) {
			// �t�@�C�������s�ǂݍ���
			$line = fgets($fp);
			if($line !== FALSE) {
				$line = substr_replace($line, ",<center>", 32, 1);
				$wpos = strpos($line, ',', 33);
				$line = substr_replace($line, "</center>,", $wpos, 1);
				$num  = preg_replace( "/,/", "</TD><TD>", $line);
				print "<TR>\n";
				print "<TD scope=\"col\">{$num}</TD>\n";
				print "</TR>\n";
			}
		}
		fclose($fp);
		print "</tbody>\n</table>\n</form>";
	}
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
				$value = str_replace(",", "", $value);
				$this->dataSet["{$name}"] = $value;
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
