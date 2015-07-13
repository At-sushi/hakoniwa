<?php

/*******************************************************************

	���돔�� S.E
	
	- ���O�o�͗p�t�@�C�� -
	
	hako-log.php by SERA - 2013/06/01

*******************************************************************/

//--------------------------------------------------------------------
class LogIO {
	var $logPool = array();
	var $secretLogPool = array();
	var $lateLogPool = array();
	
	//---------------------------------------------------
	// ���O�t�@�C�������ɂ��炷
	//---------------------------------------------------
	function slideBackLogFile() {
		global $init;
		for($i = $init->logMax - 1; $i >= 0; $i--) {
			$j = $i + 1;
			$s = "{$init->dirName}/hakojima.log{$i}";
			$d = "{$init->dirName}/hakojima.log{$j}";
			if(is_file($s)) {
				if(is_file($d)) {
					unlink($d);
				}
				rename($s, $d);
			}
		}
	}
	//---------------------------------------------------
	// �ŋ߂̏o�������o��
	//---------------------------------------------------
	function logFilePrint($num = 0, $id = 0, $mode = 0) {
		global $init;
		$fileName = $init->dirName . "/hakojima.log" . $num;
		if(!is_file($fileName)) {
			return;
		}
		$fp = fopen($fileName, "r");
		$row = 1;
		
		while($line = chop(fgets($fp, READ_LINE))) {
			list($m, $turn, $id1, $id2, $message) = split(",", $line, 5);
			if($m == 1) {
				if(($mode == 0) || ($id1 != $id)) {
					continue;
				}
				$m = "<strong>(�@��)</strong>";
			} else {
				$m = "";
			}
			if($id != 0) {
				if(($id != $id1) && ($id != $id2)) {
					continue;
				}
			}
			if($row == 1) {
				print "{$init->tagNumber_}----�y�^�[��{$turn}�̏o�����z ------------------------------------------------------------------{$init->_tagNumber}<br>\n";
				$row++;
			}
			print "{$message}<br>\n";
		}
		fclose($fp);
	}
	//---------------------------------------------------
	// �����̋L�^���o��
	//---------------------------------------------------
	function historyPrint() {
		global $init;
		$fileName = $init->dirName . "/hakojima.his";
		if(!is_file($fileName)) {
			return;
		}
		$fp = fopen($fileName, "r");
		$history = array();
		$k = 0;
		while($line = chop(fgets($fp, READ_LINE))) {
			array_push($history, $line);
			$k++;
		}
		for($i = 0; $i < $k; $i++) {
			list($turn, $his) = split(",", array_pop($history), 2);
			print "{$init->tagNumber_}�^�[��{$turn}{$init->_tagNumber}�F$his<br>\n";
		}
	}
	//---------------------------------------------------
	// �����̋L�^��ۑ�
	//---------------------------------------------------
	function history($str) {
		global $init;
		
		$fileName = "{$init->dirName}/hakojima.his";
		
		if(!is_file($fileName)) {
			touch($fileName);
		}
		$fp = fopen($fileName, "a");
		fputs($fp, "{$GLOBALS['ISLAND_TURN']},{$str}\n");
		fclose($fp);
		// chmod($fileName, 0666);
	}
	//---------------------------------------------------
	// �����̋L�^���O����
	//---------------------------------------------------
	function historyTrim() {
		global $init;
		$fileName = "{$init->dirName}/hakojima.his";
		if(is_file($fileName)) {
			$fp = fopen($fileName, "r");
			
			$line = array();
			while($l = chop(fgets($fp, READ_LINE))) {
				array_push($line, $l);
				$count++;
			}
			fclose($fp);
			if($count > $init->historyMax) {
				if(!is_file($fileName)) {
					touch($fileName);
				}
				$fp = fopen($fileName, "w");
				for($i = ($count - $init->historyMax); $i < $count; $i++) {
					fputs($fp, "{$line[$i]}\n");
				}
				fclose($fp);
				// chmod($fileName, 0666);
			}
		}
	}
	//---------------------------------------------------
	// ���O
	//---------------------------------------------------
	function out($str, $id = "", $tid = "") {
		array_push($this->logPool, "0,{$GLOBALS['ISLAND_TURN']},{$id},{$tid},{$str}");
	}
	//---------------------------------------------------
	// �@�����O
	//---------------------------------------------------
	function secret($str, $id = "", $tid = "") {
		array_push($this->secretLogPool,"1,{$GLOBALS['ISLAND_TURN']},{$id},{$tid},{$str}");
	}
	//---------------------------------------------------
	// �x�����O
	//---------------------------------------------------
	function late($str, $id = "", $tid = "") {
		array_push($this->lateLogPool,"0,{$GLOBALS['ISLAND_TURN']},{$id},{$tid},{$str}");
	}
	//---------------------------------------------------
	// ���O�����o��
	//---------------------------------------------------
	function flush() {
		global $init;
		
		$fileName = "{$init->dirName}/hakojima.log0";
		
		if(!is_file($fileName)) {
			touch($fileName);
		}
		$fp = fopen($fileName, "w");
		
		// �S���t���ɂ��ď����o��
		if(!empty($this->secretLogPool)) {
			for($i = count($this->secretLogPool) - 1; $i >= 0; $i--) {
				fputs($fp, "{$this->secretLogPool[$i]}\n");
			}
		}
		if(!empty($this->lateLogPool)) {
			for($i = count($this->lateLogPool) - 1; $i >= 0; $i--) {
				fputs($fp, "{$this->lateLogPool[$i]}\n");
			}
		}
		if(!empty($this->logPool)) {
			for($i = count($this->logPool) - 1; $i >= 0; $i--) {
				fputs($fp, "{$this->logPool[$i]}\n");
			}
		}
		fclose($fp);
		// chmod($fileName, 0666);
	}
	//---------------------------------------------------
	// ���m�点���o��
	//---------------------------------------------------
	function infoPrint() {
		global $init;
		
		if($init->infoFile == "") {
			return;
		}
		$fileName = "{$init->infoFile}";
		if(!is_file($fileName)) {
			return;
		}
		$fp = fopen($fileName, "r");
		while($line = fgets($fp, READ_LINE)) {
			$line = chop($line);
			print "{$line}<br>\n";
		}
		fclose($fp);
	}
}

//--------------------------------------------------------------------
class Log extends LogIO {
	function discover($id, $name) {
		global $init;
		$this->history("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�����������B");
	}
	function changeName($name1, $name2) {
		global $init;
		$this->history("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name1}��</A>{$init->_tagName}�A���̂�<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name2}��</A>{$init->_tagName}�ɕύX����B");
	}
	// �������v���[���g
	function presentMoney($id, $name, $value) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ɁA����<strong>{$value}{$init->unitMoney}</strong>���v���[���g���܂����B", $id);
	}
	// �H�����v���[���g
	function presentFood($id, $name, $value) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ɁA�H��<strong>{$value}{$init->unitFood}</strong>���v���[���g���܂����B", $id);
	}
	// ���
	function prize($id, $name, $pName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��<strong>$pName</strong>����܂��܂����B",$id);
		$this->history("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�A<strong>$pName</strong>�����");
	}
	// ����
	function dead($id, $name) {
		global $init;
		$this->out("{$init->tagName_}${name}��{$init->_tagName}����l�����Ȃ��Ȃ�A<strong>���l��</strong>�ɂȂ�܂����B", $id);
		$this->history("{$init->tagName_}${name}��{$init->_tagName}�A�l�����Ȃ��Ȃ�<strong>���l��</strong>�ƂȂ�B");
	}
	// ���̋����폜
	function deleteIsland($id, $name) {
		global $init;
		$this->history("{$init->tagName_}{$name}��{$init->_tagName}�ɁA����喾�_��<strong>�V�����~��</strong><span class=attention>�C�̒��ɖv��</span>�܂����B");
	}
	function doNothing($id, $name, $comName) {
		//global $init;
		//$this->out("{$init->tagName_}{$name}��{$init->_tagName}��{$init->tagComName_}${comName}{$init->_tagComName}���s���܂����B",$id);
	}
	// ��������Ȃ�
	function noMoney($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A�����s���̂��ߒ��~����܂����B",$id);
	}
	// �H������Ȃ�
	function noFood($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A���~�H���s���̂��ߒ��~����܂����B",$id);
	}
	// �؍ޑ���Ȃ�
	function noWood($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A�؍ޕs���̂��ߒ��~����܂����B",$id);
	}
	// �q������Ȃ�
	function NoAny($id, $name, $comName, $str) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A{$str}���ߒ��~����܂����B",$id);
	}
	// �Ώےn�`�̎�ނɂ�鎸�s
	function landFail($id, $name, $comName, $kind, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A�\��n��{$init->tagName_}{$point}{$init->_tagName}��<strong>{$kind}</strong>���������ߒ��~����܂����B",$id);
	}
	// �Ώےn�`�̏����ɂ�鎸�s
	function JoFail($id, $name, $comName, $kind, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A�\��n��{$init->tagName_}{$point}{$init->_tagName}�������𖞂����Ă��Ȃ�<strong>{$kind}</strong>���������ߒ��~����܂����B",$id);
	}
	// �s�s�̎�ނɂ�鎸�s
	function BokuFail($id, $name, $comName, $kind, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A�\��n��{$init->tagName_}{$point}{$init->_tagName}�������𖞂������s�s�łȂ��������ߒ��~����܂����B",$id);
	}
	// ����ɒ����Ȃ��Ď��s
	function NoTownAround($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A�\��n��{$init->tagName_}{$point}{$init->_tagName}�̎��ӂɐl�������Ȃ��������ߒ��~����܂����B",$id);
	}
	// ����
	function landSuc($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��{$init->tagComName_}{$comName}{$init->_tagComName}���s���܂����B",$id);
	}
	// �q�Ɋ֌W
	function Souko($id, $name, $comName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��{$init->tagComName_}{$comName}{$init->_tagComName}<strong>{$str}</strong>���s���܂����B",$id);
	}
	// �q�Ɋ֌W
	function SoukoMax($id, $name, $comName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A�\��n��{$init->tagName_}{$point}{$init->_tagName}��<strong>{$str}</strong>���ߒ��~����܂����B",$id);
	}
	// �q�Ɋ֌W
	function SoukoLupin($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>��{$init->tagDisaster_}��������N�������悤�ł��I�I{$init->_tagDisaster}",$id);
	}
	// ���n�n���O�܂Ƃ�
	function landSucMatome($id, $name, $comName, $point) {
		global $init;
		$this->out("<strong>��</strong> {$init->tagName_}{$point}{$init->_tagName}",$id);
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��{$init->tagComName_}{$comName}{$init->_tagComName}���s���܂����B",$id);
	}
	// ������
	function maizo($id, $name, $comName, $value) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ł�{$init->tagComName_}{$comName}{$init->_tagComName}���ɁA<strong>{$value}{$init->unitMoney}���̖�����</strong>����������܂����B",$id);
	}
	function noLandAround($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A�\��n��{$init->tagName_}{$point}{$init->_tagName}�̎��ӂɗ��n���Ȃ��������ߒ��~����܂����B",$id);
	}
	// ������
	function EggFound($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��{$init->tagComName_}{$comName}{$init->_tagComName}���ɁA<strong>�����̗�</strong>�𔭌����܂����B",$id);
	}
	// ���z��
	function EggBomb($id, $name, $mName, $point, $lName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��{$lName}����<strong>���b{$mName}</strong>�����܂�܂����B",$id);
	}
	// ���y�Y
	function Miyage($id, $name, $lName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}���̂��y�Y������</strong>����<strong>{$value}{$str}</strong>���̎���������܂����B",$id);
	}
	// ���n
	function Syukaku($id, $name, $lName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>�������炵���L��ɂ��A�����<strong>{$str}</strong>���̐H�������n����܂����B",$id);
	}
	// ��s��
	function Bank($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>����s�ɂȂ�܂����B",$id);
	}
	// �q���ł��グ����
	function Eiseisuc($id, $name, $kind, $str) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��{$init->tagComName_}{$kind}{$str}{$init->_tagComName}�ɐ������܂����B",$id);
	}
	// �q������
	function Eiseifail($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��{$init->tagComName_}{$comName}{$init->_tagComName}���s���܂������ł��グ��{$init->tagDisaster_}���s{$init->_tagDisaster}�����悤�ł��B",$id);
	}
	// �q���j�󐬌�
	function EiseiAtts($id, $tId, $name, $tName, $comName, $tEiseiname) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��{$init->_tagName}</A>��<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}��</A>{$init->_tagName}�Ɍ�����{$init->tagComName_}{$comName}{$init->_tagComName}���s���A<strong>{$tEiseiname}</strong>�ɖ����B<strong>$tEiseiname</strong>�͐Ռ`���Ȃ�������т܂����B",$id, $tId);
	}
	// �q���j�󎸔s
	function EiseiAttf($id, $tId, $name, $tName, $comName, $tEiseiname) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}��</A>{$init->_tagName}��<strong>{$tEiseiname}</strong>�Ɍ�����{$init->tagComName_}{$comName}{$init->_tagComName}���s���܂������A���ɂ����������F���̔ޕ��ւƔ�ы����Ă��܂��܂����B",$id, $tId);
	}
	// �q�����[�U�[
	function EiseiLzr($id, $tId, $name, $tName, $comName, $tLname, $point, $str) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��{$init->_tagName}</A>��<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}��</A>{$point}{$init->_tagName}�Ɍ�����{$init->tagComName_}{$comName}{$init->_tagComName}���s���A<strong>{$tLname}</strong>�ɖ����B��т�{$str}",$id, $tId);
	}
	// ���c����
	function oilFound($id, $name, $point, $comName, $str) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$str}</strong>�̗\�Z��������{$init->tagComName_}{$comName}{$init->_tagComName}���s���A<strong>���c���@�蓖�Ă��܂���</strong>�B",$id);
	}
	// ���c�����Ȃ炸
	function oilFail($id, $name, $point, $comName, $str) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$str}</strong>�̗\�Z��������{$init->tagComName_}{$comName}{$init->_tagComName}���s���܂������A���c�͌�����܂���ł����B",$id);
	}
	// �h�q�{�݁A�����Z�b�g
	function bombSet($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>��<strong>�������u���Z�b�g</strong>����܂����B",$id);
	}
	// �h�q�{�݁A�����쓮
	function bombFire($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>�A{$init->tagDisaster_}�������u�쓮�I�I{$init->_tagDisaster}",$id);
	}
	// �����g�_�E������
	function CrushElector($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>�ŁA{$init->tagDisaster_}�����g�_�E�������I�I{$init->_tagDisaster}��т����v���܂����B",$id);
	}
	// ��d����
	function Teiden($id, $name) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŁA{$init->tagDisaster_}��d�����I�I{$init->_tagDisaster}",$id);
	}
	// ���Ƃ蔭��
	function Hideri($id, $name) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŁA{$init->tagDisaster_}���Ƃ肪����{$init->_tagDisaster}�A�s�s���̐l�����������܂����B",$id);
	}
	// �ɂ킩�J����
	function Niwakaame($id, $name) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŁA{$init->tagDisaster_}�ɂ킩�J{$init->_tagDisaster}���~��A�X�������܂����B",$id);
	}
	// �A��or�~�T�C����n
	function PBSuc($id, $name, $comName, $point) {
		global $init;
		$this->secret("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��{$init->tagComName_}{$comName}{$init->_tagComName}���s���܂����B",$id);
		$this->out("������Ȃ����A{$init->tagName_}{$name}��{$init->_tagName}��<strong>�X</strong>���������悤�ł��B",$id);
	}
	// �n���{�e
	function hariSuc($id, $name, $comName, $comName2, $point) {
		global $init;
		$this->secret("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��{$init->tagComName_}{$comName}{$init->_tagComName}���s���܂����B",$id);
		$this->landSuc($id, $name, $comName2, $point);
	}
	// �L�O��A����
	function monFly($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>��<strong>�����ƂƂ��ɔ�ї����܂���</strong>�B",$id);
	}
	// ���s���^�[��
	function Forbidden($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A���s��������܂���ł����B",$id);
	}
	// �Ǘ��l�a���蒆�̂��ߋ�����Ȃ�
	function CheckKP($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A�ڕW�̓����Ǘ��l�a���蒆�̂��ߎ��s��������܂���ł����B",$id);
	}
	// �d�͕s��
	function Enehusoku($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A�d�͕s���̂��ߒ��~����܂����B",$id);
	}
	// �~�T�C�����Ƃ��Ƃ������V�C������
	function msNoTenki($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A���V��̂��ߒ��~����܂����B",$id);
	}
	// �~�T�C�����Ƃ��Ƃ���(or ���b�h�����悤�Ƃ���)���^�[�Q�b�g�����Ȃ�
	function msNoTarget($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A�ڕW�̓��ɐl����������Ȃ����ߒ��~����܂����B",$id);
	}
	// �~�T�C�����Ƃ��Ƃ�������n���Ȃ�
	function msNoBase($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A<strong>�~�T�C���ݔ���ۗL���Ă��Ȃ�</strong>���߂Ɏ��s�ł��܂���ł����B",$id);
	}
	// �~�T�C�����Ƃ��Ƃ������ő唭�ː��𒴂���
	function msMaxOver($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A<strong>�ő唭�ː��𒴂���</strong>���߂Ɏ��s�ł��܂���ł����B",$id);
	}
	// �X�e���X�~�T�C�����O
	function mslogS($id, $tId, $name, $tName, $comName, $point, $missiles, $missileA, $missileB, $missileC, $missileD, $missileE) {
		global $init;
		$missileBE = $missileB + $missileE;
		$missileH = $missiles - $missileA - $missileC - $missileBE;
		$this->secret("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}��</A>{$point}{$init->_tagName}�n�_�Ɍ�����{$init->tagComName_}{$missiles}��{$init->_tagComName}��{$init->tagComName_}{$comName}{$init->_tagComName}���s���܂����B(�L��{$missileH}��/���b����{$missileD}��/���b����{$missileC}��/�h�q{$missileBE}��/����{$missileA}��)",$id, $tId);
		$this->late("<strong>���҂�</strong>��<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}��</A>{$point}{$init->_tagName}�n�_�Ɍ�����{$init->tagComName_}{$missiles}��{$init->_tagComName}��{$init->tagComName_}{$comName}{$init->_tagComName}���s���܂����B(�L��{$missileH}��/���b����{$missileD}��/���b����{$missileC}��/�h�q{$missileBE}��/����{$missileA}��)",$tId);
	}
	// ���̑��~�T�C�����O
	function mslog($id, $tId, $name, $tName, $comName, $point, $missiles, $missileA, $missileB, $missileC, $missileD, $missileE) {
		global $init;
		$missileBE = $missileB + $missileE;
		$missileH = $missiles - $missileA - $missileC - $missileBE;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}��</A>{$point}{$init->_tagName}�n�_�Ɍ�����{$init->tagComName_}{$missiles}��{$init->_tagComName}��{$init->tagComName_}{$comName}{$init->_tagComName}���s���܂����B(�L��{$missileH}��/���b����{$missileD}��/���b����{$missileC}��/�h�q{$missileBE}��/����{$missileA}��)",$id, $tId);
	}
	// ���n�j��e�A�R�ɖ���
	function msLDMountain($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}��<strong>{$tLname}</strong>�ɖ����B<strong>{$tLname}</strong>�͏�����сA�r�n�Ɖ����܂����B",$id, $tId);
	}
	// ���n�j��e�A�C���n�ɖ���
	function msLDSbase($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}�ɒ����㔚���A���n�_�ɂ�����<strong>{$tLname}</strong>�͐Ռ`���Ȃ�������т܂����B",$id, $tId);
	}
	// ���n�j��e�A���b�ɖ���
	function msLDMonster($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}�ɒ��e�������B���n��<strong>���b{$tLname}</strong>����Ƃ����v���܂����B",$id, $tId);
	}
	// ���n�j��e�A�󐣂ɖ���
	function msLDSea1($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}��<strong>{$tLname}</strong>�ɒ��e�B�C�ꂪ�������܂����B",$id, $tId);
	}
	// ���n�j��e�A���̑��̒n�`�ɖ���
	function msLDLand($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}��<strong>{$tLname}</strong>�ɒ��e�B���n�͐��v���܂����B",$id, $tId);
	}
	// �n�`���N�e�A�C���n�ɖ���
	function msLUSbase($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}�ɒ����㔚���A���n�_�ɂ�����<strong>{$tLname}</strong>�͐󐣂ɖ��܂�܂����B",$id, $tId);
	}
	// �n�`���N�e�A�[���C�ɖ���
	function msLUSea0($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}��<strong>{$tLname}</strong>�ɒ����B�C�ꂪ���N���󐣂ƂȂ�܂����B",$id, $tId);
	}
	// �n�`���N�e�A�󐣂ɖ���
	function msLUSea1($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}��<strong>{$tLname}</strong>�ɒ��e�B�C�ꂪ���N���r�n�ƂȂ�܂����B",$id, $tId);
	}
	// �n�`���N�e�A���b�ɖ���
	function msLUMonster($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}�ɒ��e�B���n�͗��N���R�ƂȂ�A<strong>���b{$tLname}</strong>�͐����߂ƂȂ�܂����B",$id, $tId);
	}
	// �n�`���N�e�A���̑��̒n�`�ɖ���
	function msLULand($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}��<strong>{$tLname}</strong>�ɒ��e�B���n�͗��N���R�ƂȂ�܂����B",$id, $tId);
	}
	// �o�C�I�~�T�C�����e�A����
	function msPollution($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}��<strong>{$tLname}</strong>�ɒ��e�B��т���������܂����B",$id, $tId);
	}
	// �X�e���X�~�T�C���A���b�ɖ����A�d�����ɂĖ���
	function msMonNoDamageS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->secret("-{$tPoint}��<strong>���b{$tLname}</strong>�ɖ����A�������d����Ԃ��������ߌ��ʂ�����܂���ł����B",$id, $tId);
		$this->out("-{$tPoint}��<strong>���b{$tLname}</strong>�ɖ����A�������d����Ԃ��������ߌ��ʂ�����܂���ł����B",$tId);
	}
	// �ʏ�~�T�C���A���b�ɖ����A�d�����ɂĖ���
	function msMonNoDamage($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}��<strong>���b{$tLname}</strong>�ɖ����A�������d����Ԃ��������ߌ��ʂ�����܂���ł����B",$id, $tId);
	}
	// �X�e���X�~�T�C�������������b�ɒ@�����Ƃ����
	function msMonsCaughtS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->secret("-{$tPoint}��<strong>���b{$tLname}</strong>�ɒ@�����Ƃ���܂����B",$id, $tId);
	$this->late("-{$tPoint}��<strong>���b{$tLname}</strong>�ɒ@�����Ƃ���܂����B",$tId);
	}
	// �ʏ�~�T�C�������������b�ɒ@�����Ƃ����
	function msMonsCaught($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}��<strong>���b{$tLname}</strong>�ɒ@�����Ƃ���܂����B",$id, $tId);
	}
	// �X�e���X�~�T�C���A���b�ɖ����A�E��
	function msMonsKillS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->secret("-{$tPoint}��<strong>���b{$tLname}</strong>�ɖ����B<strong>���b{$tLname}</strong>�͗͐s���A�|��܂����B",$id, $tId);
		$this->late("-{$tPoint}��<strong>���b{$tLname}</strong>�ɖ����B<strong>���b{$tLname}</strong>�͗͐s���A�|��܂����B", $tId);
	}
	// �ʏ�~�T�C���A���b�ɖ����A�E��
	function msMonsKill($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}��<strong>���b{$tLname}</strong>�ɖ����B<strong>���b{$tLname}</strong>�͗͐s���A�|��܂����B",$id, $tId);
	}
	// ���b�̎��́i�X�e���X�j
	function msMonMoneyS($id, $tId, $tLname, $value) {
		global $init;
		$this->secret("-<strong>���b{$tLname}</strong>�̎c�[�ɂ́A<strong>{$value}{$init->unitMoney}</strong>�̒l���t���܂����B",$id, $tId);
		$this->late("-<strong>���b{$tLname}</strong>�̎c�[�ɂ́A<strong>{$value}{$init->unitMoney}</strong>�̒l���t���܂����B",$tId);
	}
	// ���b�̎��́i�ʏ�j
	function msMonMoney($id, $tId, $tLname, $value) {
		global $init;
		$this->out("-<strong>���b{$tLname}</strong>�̎c�[�ɂ́A<strong>{$value}{$init->unitMoney}</strong>�̒l���t���܂����B",$id, $tId);
	}
	// �X�e���X�~�T�C���A���b�ɖ����A�_���[�W
	function msMonsterS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->secret("-{$tPoint}��<strong>���b{$tLname}</strong>�ɖ����B<strong>���b{$tLname}</strong>�͋ꂵ�����ə��K���܂����B",$id, $tId);
		$this->late("-{$tPoint}��<strong>���b{$tLname}</strong>�ɖ����B<strong>���b{$tLname}</strong>�͋ꂵ�����ə��K���܂����B",$tId);
	}
	// �o�C�I�~�T�C���A���b�ɖ����A�ˑR�ψ�
	function msMutation($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}��<strong>���b{$tLname}</strong>�ɖ����B<strong>���b{$tLname}</strong>�ɓˑR�ψق������܂����B",$id, $tId);
	}
	// �Ö��e�����b�ɖ���
	function MsSleeper($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��<strong>���b{$tLname}</strong>�͍Ö��e�ɂ���Ė����Ă��܂����悤�ł��B",$id, $tId);
	}
	// �������̉��b�Ƀ~�T�C������
	function MsWakeup($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�Ŗ����Ă���<strong>���b{$tLname}</strong>�Ƀ~�T�C���������A<strong>���b{$tLname}</strong>�͖ڂ��o�܂��܂����B",$id, $tId);
	}
	// �������̉��b���ڊo�߂�
	function MonsWakeup($id, $name, $lName, $point, $mName) {
		global $init;
			$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�Ŗ����Ă���<strong>���b{$mName}</strong>�͖ڂ��o�܂��܂����B",$id);
	}
	// �ʏ�~�T�C���A���b�ɖ����A�_���[�W
	function msMonster($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}��<strong>���b{$tLname}</strong>�ɖ����B<strong>���b{$tLname}</strong>�͋ꂵ�����ə��K���܂����B",$id, $tId);
	}
	// �X�e���X�~�T�C���ʏ�n�`�ɖ���
	function msNormalS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->secret("-{$tPoint}��<strong>{$tLname}</strong>�ɖ����A��т���ł��܂����B",$id, $tId);
		$this->late("-{$tPoint}��<strong>{$tLname}</strong>�ɖ����A��т���ł��܂����B",$tId);
	}
	// �ʏ�~�T�C���ʏ�n�`�ɖ���
	function msNormal($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}��<strong>{$tLname}</strong>�ɖ����A��т���ł��܂����B",$id, $tId);
	}
	// �X�e���X�~�T�C���K�͌���
	function msGensyoS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->secret("-{$tPoint}��<strong>{$tLname}</strong>�ɖ����A�K�͂��������܂����B",$id, $tId);
		$this->late("-{$tPoint}��<strong>{$tLname}</strong>�ɖ����A�K�͂��������܂����B",$tId);
	}
	// �ʏ�~�T�C���K�͌���
	function msGensyo($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}��<strong>{$tLname}</strong>�ɖ����A�K�͂��������܂����B",$id, $tId);
	}
	// �ʏ�~�T�C���h�q�{�݂ɖ���
	function msDefence($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->out("-{$tPoint}��<strong>{$tLname}</strong>�ɖ������܂�������Q�͂���܂���ł����B",$id, $tId);
	}
	// �X�e���X�~�T�C���h�q�{�݂ɖ���
	function msDefenceS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
		global $init;
		$this->secret("-{$tPoint}��<strong>{$tLname}</strong>�ɖ������܂�������Q�͂���܂���ł����B",$id, $tId);
	$this->late("-{$tPoint}��<strong>{$tLname}</strong>�ɖ������܂�������Q�͂���܂���ł����B",$tId);
	}
	// �~�T�C�������
	function msBoatPeople($id, $name, $achive) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ɂǂ�����Ƃ��Ȃ�<strong>{$achive}{$init->unitPop}���̓</strong>���Y�����܂����B<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�͉����󂯓��ꂽ�悤�ł��B",$id);
	}
	// ���b�h��
	function monsSend($id, $tId, $name, $tName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��<strong>�l�����b</strong>�������B<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}��</A>{$init->_tagName}�֑��肱�݂܂����B",$id, $tId);
	}
	// �q�����ŁH�I
	function EiseiEnd($id, $name, $tEiseiname) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��<strong>{$tEiseiname}</strong>��{$init->tagDisaster_}����{$init->_tagDisaster}�����悤�ł��I�I",$id);
	}
	// ��́A���b�ɍU��
	function SenkanMissile($id, $tId, $name, $tName, $lName, $point, $tPoint, $tmonsName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}��</A>{$init->_tagName}<strong>{$lName}</strong>�����e���~�T�C���𔭎˂��A{$tPoint}��<strong>{$tmonsName}</strong>�ɖ������܂����B",$id, $tId);
	}
	// ���b������
	function BariaAttack($id, $name, $lName, $point, $mName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>���b{$mName}</strong>�����͂ȗ͏�ɉ����ׂ���܂����B",$id);
	}
	// ���b�A���Ɏ��s
	function MonsNoSleeper($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A�������̉��b�����Ȃ��������ߒ��~����܂����B",$id);
	}
	// ���b�A��
	function monsSendSleeper($id, $tId, $name, $tName, $lName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�Ŗ����Ă���<strong>���b{$lName}</strong>���A<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}��</A>{$init->_tagName}�֑��肱�܂�܂����B",$id, $tId);
	}
	// �A�o
	function sell($id, $name, $comName, $value, $unit) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��<strong>{$value}{$unit}</strong>��{$init->tagComName_}{$comName}{$init->_tagComName}���s���܂����B",$id);
	}
	// ����
	function aid($id, $tId, $name, $tName, $comName, $str) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}��</A>{$init->_tagName}��<strong>{$str}</strong>��{$init->tagComName_}{$comName}{$init->_tagComName}���s���܂����B",$id, $tId);
	}
	// �U�v����
	function propaganda($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��{$init->tagComName_}{$comName}{$init->_tagComName}���s���܂����B",$id);
	}
	// ����
	function giveup($id, $name) {
		global $init;
		$this->out("{$init->tagName_}{$name}��{$init->_tagName}�͕�������A<strong>���l��</strong>�ɂȂ�܂����B",$id);
		$this->history("{$init->tagName_}{$name}��{$init->_tagName}�A��������<strong>���l��</strong>�ƂȂ�B");
	}
	// ���c����̎���
	function oilMoney($id, $name, $lName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>����A<strong>{$str}</strong>�̎��v���オ��܂����B",$id);
	}
	// ���c�͊�
	function oilEnd($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>�͌͊������悤�ł��B",$id);
	}
	// �󂭂��w��
	function buyLot($id, $name, $comName, $str) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��<strong>{$str}</strong>����{$init->tagComName_}{$comName}{$init->_tagComName}���s���܂����B",$id);
	}
	// �󂭂�����
	function noLot($id, $name, $comName) {
		global $init;
		$this->out("<strong>�󂭂������̂���</strong>�A<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�́A{$init->tagComName_}{$comName}{$init->_tagComName}���o���܂���ł����B",$id);
	}
	// �󂭂�����
	function LotteryMoney($id, $name, $str, $syo) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��<strong>�󂭂�{$syo}����</strong>�ɓ��I�I<strong>{$str}</strong>�̓��I�����󂯎��܂����B",$id);
	}
	// �V���n����̎���
	function ParkMoney($id, $name, $lName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<B>{$lName}</B>����A<B>{$str}</B>�̎��v���オ��܂����B",$id);
	}
	// �V���n�̃C�x���g
	function ParkEvent($id, $name, $lName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<B>{$lName}</B>�ŃC�x���g���J�Â���A<B>{$str}</B>�̐H���������܂����B",$id);
	}
	// �V���n�̃C�x���g����
	function ParkEventLuck($id, $name, $lName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<B>{$lName}</B>�ŊJ�Â��ꂽ�C�x���g����������<B>{$str}</B>�̎��v���オ��܂����B",$id);
	}
	// �V���n�̃C�x���g����
	function ParkEventLoss($id, $name, $lName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<B>{$lName}</B>�ŊJ�Â��ꂽ�C�x���g�����s����<B>{$str}</B>�̑������ł܂����B",$id);
	}
	// �V���n����
	function ParkEnd($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<B>{$lName}</B>�͎{�݂��V�����������ߕ��ƂȂ�܂����B",$id);
	}
	// ���b�A�h�q�{�݂𓥂�
	function monsMoveDefence($id, $name, $lName, $point, $mName) {
		global $init;
		$this->out("<strong>���b{$mName}</strong>��<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>�֓��B�A<strong>{$lName}�̎������u���쓮�I�I</strong>",$id);
	}
	// ���b����������
	function MonsExplosion($id, $name, $point, $mName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>���b{$mName}</strong>��<strong>�唚��</strong>���N�����܂����I",$id);
	}
	// ���b����
	function monsBunretu($id, $name, $lName, $point, $mName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>��<strong>���b{$mName}</strong>�����􂵂܂����B",$id);
	}
	// ���b����
	function monsMove($id, $name, $lName, $point, $mName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>��<strong>���b{$mName}</strong>�ɓ��ݍr�炳��܂����B",$id);
	}
	// ���炷����
	function ZorasuMove($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>��<strong>���炷</strong>�ɔj�󂳂�܂����B",$id);
	}
	// �΍�
	function fire($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>��{$init->tagDisaster_}�΍�{$init->_tagDisaster}�ɂ���ł��܂����B",$id);
	}
	// �΍Ж���
	function firenot($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>��{$init->tagDisaster_}�΍�{$init->_tagDisaster}�ɂ���Q���󂯂܂����B",$id);
	}
	// �L���Q�A�C�̌���
	function wideDamageSea2($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>�͐Ռ`���Ȃ��Ȃ�܂����B",$id);
	}
	// �L���Q�A���b���v
	function wideDamageMonsterSea($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}�̗��n��<strong>���b{$lName}</strong>����Ƃ����v���܂����B",$id);
	}
	// �L���Q�A���v
	function wideDamageSea($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>��<strong>���v</strong>���܂����B",$id);
	}
	// �L���Q�A���b
	function wideDamageMonster($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>���b{$lName}</strong>�͏�����т܂����B",$id);
	}
	// �L���Q�A�r�n
	function wideDamageWaste($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>�͈�u�ɂ���<strong>�r�n</strong>�Ɖ����܂����B",$id);
	}
	// �n�k����
	function earthquake($id, $name) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ő�K�͂�{$init->tagDisaster_}�n�k{$init->_tagDisaster}�������I�I",$id);
	}
	// �n�k��Q
	function eQDamage($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>��{$init->tagDisaster_}�n�k{$init->_tagDisaster}�ɂ���ł��܂����B",$id);
	}
	// �n�k��Q����
	function eQDamagenot($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>��{$init->tagDisaster_}�n�k{$init->_tagDisaster}�ɂ���Q���󂯂܂����B",$id);
	}
	// �Q��
	function starve($id, $name) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��{$init->tagDisaster_}�H�����s��{$init->_tagDisaster}���Ă��܂��I�I",$id);
	}
	// �\������
	function pooriot($id, $name) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��<strong>���Ɨ������ɂ��</strong>{$init->tagDisaster_}�\��{$init->_tagDisaster}�������I�I",$id);
	}
	// �\����Q�i�l�����j
	function riotDamage1($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>��{$init->tagDisaster_}�\��{$init->_tagDisaster}�ɂ�莀���҂������o���͗l�ł��B",$id);
	}
	// �\����Q�i��Łj
	function riotDamage2($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>��{$init->tagDisaster_}�\��{$init->_tagDisaster}�ɂ���ł��܂����B",$id);
	}
	// �H���s����Q
	function svDamage($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>��<strong>�H�������߂ďZ�����E��</strong>�B<strong>{$lName}</strong>�͉�ł��܂����B",$id);
	}
	// �Ôg����
	function tsunami($id, $name) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�t�߂�{$init->tagDisaster_}�Ôg{$init->_tagDisaster}�����I�I",$id);
	}
	// �Ôg��Q
	function tsunamiDamage($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>��{$init->tagDisaster_}�Ôg{$init->_tagDisaster}�ɂ����󂵂܂����B",$id);
	}
	// ���b����
	function monsCome($id, $name, $mName, $point, $lName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��<strong>���b{$mName}</strong>�o���I�I{$init->tagName_}{$point}{$init->_tagName}��<strong>{$lName}</strong>�����ݍr�炳��܂����B",$id);
	}
	// �D�h������
	function shipSend($id, $tId, $name, $sName, $point, $tName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>��{$point}{$init->_tagName}��<strong>{$sName}</strong>��<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}��</A>{$init->_tagName}��{$init->tagComName_}�h��{$init->_tagComName}���܂����B",$id, $tId);
	}
	// �D�A�҂���
	function shipReturn($id, $tId, $name, $sName, $point, $tName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}��</A>{$init->_tagName}{$point}��<strong>{$sName}</strong>��{$init->tagComName_}�A��{$init->_tagComName}�����܂����B",$id, $tId);
	}
	// ������
	function RecoveryTreasure($id, $name, $sName, $value) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��<strong>{$sName}</strong>����������<strong>{$value}���~����</strong>��{$init->tagDisaster_}����{$init->_tagDisaster}��������܂����B",$id);
	}
	// �D���s
	function shipFail($id, $name, $comName, $kind) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A<strong>{$kind}</strong>���������ߒ��~����܂����B",$id, $tId);
	}
	// ���炷����
	function ZorasuCome($id, $name, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��<strong>���炷</strong>�o���I�I",$id);
	}
	// ���b�Ă΂��
	function monsCall($id, $name, $mName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>���b{$mName}</strong>���V�Ɍ������ę��K���܂����I",$id);
	}
	// ���b���[�v
	function monsWarp($id, $tId, $name, $mName, $point, $tName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>���b{$mName}</strong>��<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}��</A>{$init->_tagName}�Ƀ��[�v���܂����I",$id, $tId);
	}
	// ���b�ɂ�鎑������
	function MonsMoney($id, $name, $mName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>���b{$mName}</strong>��<strong>{$str}</strong>�̋����΂�T���܂����B",$id);
	}
	// ���b�ɂ��H������
	function MonsFood($id, $name, $mName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>���b{$mName}</strong>���T���U�炵���h�{�����Ղ�E���R�̉e���ŁA�H����<strong>{$str}</strong>���Y����܂����B",$id);
	}
	// ���b�ɂ�鎑������
	function MonsMoney2($id, $name, $mName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>���b{$mName}</strong>�ɂ���āA���̎���<strong>{$str}</strong>�����D����܂����B",$id);
	}
	// ���b�ɂ��H������
	function MonsFood2($id, $name, $mName, $point, $str) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>���b{$mName}</strong>���T���U�炵�����L�Y���E���R�̉e���ŁA�H����<strong>{$str}</strong>���s���܂����B",$id);
	}
	// �n�Ւ�������
	function falldown($id, $name) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��{$init->tagDisaster_}�n�Ւ���{$init->_tagDisaster}���������܂����I�I",$id);
	}
	// �n�Ւ�����Q
	function falldownLand($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>�͊C�̒��֒��݂܂����B",$id);
	}
	// �䕗����
	function typhoon($id, $name) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��{$init->tagDisaster_}�䕗{$init->_tagDisaster}�㗤�I�I",$id);
	}
	// �䕗��Q
	function typhoonDamage($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>��{$init->tagDisaster_}�䕗{$init->_tagDisaster}�Ŕ�΂���܂����B",$id);
	}
	// �X�g���C�L
	function Sto($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>�Ј���{$init->tagDisaster_}�X�g���C�L{$init->_tagDisaster}���N����<strong>���ƋK��</strong>�����������͗l�ł��B",$id);
	}
	// 覐΁A���̑�
	function hugeMeteo($id, $name, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}�n�_��{$init->tagDisaster_}����覐�{$init->_tagDisaster}�������I�I",$id);
	}
	// �L�O��A����
	function monDamage($id, $name, $point) {
		global $init;
		$this->out("<strong>�����ƂĂ��Ȃ�����</strong>��<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}�n�_�ɗ������܂����I�I",$id);
	}
	// �Ƒ��̗�
	function kazokuPower($id, $name, $power) {
		global $init;
		$this->out("<strong>�����ƂĂ��Ȃ�����</strong>��<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ɐڋ߁I<strong>{$power}�����I</strong>���̊�@�͖Ƃꂽ���A{$init->tagDisaster_}�P�l�̋]����{$init->_tagDisaster}���o�Ă��܂��܂����c�B",$id);
	}
	// 覐΁A�C
	function meteoSea($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>��{$init->tagDisaster_}覐�{$init->_tagDisaster}���������܂����B",$id);
	}
	// 覐΁A�R
	function meteoMountain($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>��{$init->tagDisaster_}覐�{$init->_tagDisaster}�������A<strong>{$lName}</strong>�͏�����т܂����B",$id);
	}
	// 覐΁A�C���n
	function meteoSbase($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>��{$init->tagDisaster_}覐�{$init->_tagDisaster}�������A<strong>{$lName}</strong>�͕��󂵂܂����B",$id);
	}
	// 覐΁A���b
	function meteoMonster($id, $name, $lName, $point) {
		global $init;
		$this->out("<strong>���b{$lName}</strong>������<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}�n�_��{$init->tagDisaster_}覐�{$init->_tagDisaster}�������A���n��<strong>���b{$lName}</strong>����Ƃ����v���܂����B",$id);
	}
	// 覐΁A��
	function meteoSea1($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}�n�_��{$init->tagDisaster_}覐�{$init->_tagDisaster}�������A�C�ꂪ�������܂����B",$id);
	}
	// 覐΁A���̑�
	function meteoNormal($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}�n�_��<strong>{$lName}</strong>��{$init->tagDisaster_}覐�{$init->_tagDisaster}�������A��т����v���܂����B",$id);
	}
	// ����
	function eruption($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}�n�_��{$init->tagDisaster_}�ΎR������{$init->_tagDisaster}�A<strong>�R</strong>���o���܂����B",$id);
	}
	// ���΁A��
	function eruptionSea1($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}�n�_��<strong>{$lName}</strong>�́A{$init->tagDisaster_}����{$init->_tagDisaster}�̉e���ŗ��n�ɂȂ�܂����B",$id);
	}
	// ���΁A�Cor�C��
	function eruptionSea($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}�n�_��<strong>{$lName}</strong>�́A{$init->tagDisaster_}����{$init->_tagDisaster}�̉e���ŊC�ꂪ���N�A�󐣂ɂȂ�܂����B",$id);
	}
	// ���΁A���̑�
	function eruptionNormal($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}�n�_��<strong>{$lName}</strong>�́A{$init->tagDisaster_}����{$init->_tagDisaster}�̉e���ŉ�ł��܂����B",$id);
	}
	// �C��T���̖��c
	function tansakuoil($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>�����c�𔭌��I",$id);
	}
	// ����ɊC���Ȃ��Ď��s
	function NoSeaAround($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A�\��n��{$init->tagName_}{$point}{$init->_tagName}�̎��ӂɊC���Ȃ��������ߒ��~����܂����B",$id);
	}
	// ����ɐ󐣂��Ȃ��Ď��s
	function NoShoalAround($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A�\��n��{$init->tagName_}{$point}{$init->_tagName}�̎��ӂɐ󐣂��Ȃ��������ߒ��~����܂����B",$id);
	}
	// �C���Ȃ��Ď��s
	function NoSea($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A�\��n���C�łȂ��������ߒ��~����܂����B",$id);
	}
	// �`���Ȃ��̂ŁA���D���s
	function NoPort($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A���ӂ�<b>�`</b>���Ȃ��������ߒ��~����܂����B",$id);
	}
	// �D�j��
	function ComeBack($id, $name, $comName, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<strong>{$lName}</strong>��{$init->tagComName_}{$comName}{$init->_tagComName}���܂����B",$id);
	}
	// �D�̍ő及�L��
	function maxShip($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A<strong>�D�̍ő及�L�����Ɉᔽ���Ă��܂�</strong>���ߋ�����܂���ł����B",$id);
	}
	// �`��
	function ClosedPort($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<B>{$lName}</B>�͕������悤�ł��B",$id);
	}
	// �����s���̂��ߑD������
	function shipRelease($id, $tId, $name, $tName, $point, $tshipName) {
		global $init;
		$this->late("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}������</A>{$init->_tagName}<b>{$tshipName}</b>�́A�����s���̂��ߔj������܂����B",$id, $tId);
	}
	// �C���D����
	function VikingCome($id, $name, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<B>�C���D</B>�o���I�I",$id);
	}
	// �C���D����
	function VikingAway($id, $name, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}����<B>�C���D</B>���ǂ����ɋ����Ă����܂����B",$id);
	}
	// �C���D�U��
	function VikingAttack($id, $tId, $name, $tName, $lName, $point, $tPoint, $tshipName) {
		global $init;
		$this->late("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<b>{$lName}</b>��{$tPoint}��<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}��</A>{$init->_tagName}<B>{$tshipName}</B>���U�����܂����B",$id, $tId);
	}
	// ��͍U��
	function SenkanAttack($id, $tId, $name, $tName, $lName, $point, $tpoint, $tshipName) {
		global $init;
		$this->late("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}��</A>{$init->_tagName}<b>{$lName}</b>��{$tpoint}��<B>{$tshipName}</B>���U�����܂����B",$id, $tId);
	}
	// �C�풾�v
	function BattleSinking($id, $tId, $name, $lName, $point) {
		global $init;
		$this->late("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<b>{$lName}</b>�͒��v���܂����B",$id, $tId);
	}
	// �D�����v
	function ShipSinking($id, $tId, $name, $tName, $lName, $point) {
		global $init;
		$this->late("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}��</A>{$init->_tagName}<b>{$lName}</b>�͒��v���܂����B",$id, $tId);
	}
	// �C���D�̍���
	function VikingTreasure($id, $name, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��{$init->tagDisaster_}���󂪖����Ă���{$init->_tagDisaster}�Ɖ\����Ă��܂��B",$id);
	}
	// ���󔭌�
	function FindTreasure($id, $tId, $name, $tName, $point, $tshipName, $value) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}��</A>{$init->_tagName}<B>{$tshipName}</B>��<b>{$value}���~����</b>��{$init->tagDisaster_}����{$init->_tagDisaster}�𔭌����܂����B",$id);
	}
	// �C���D�A���D
	function RobViking($id, $name, $point, $tshipName, $money, $food) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<b>{$tshipName}</b>��<b>{$money}{$init->unitMoney}</b>�̋���<b>{$food}{$init->unitFood}</b>�̐H�������D���Ă����܂����B",$id);
	}
	// �D����
	function RunAground($id, $name, $lName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$point}{$init->_tagName}��<b>$lName</b>��{$init->tagDisaster_}����{$init->_tagDisaster}���܂����B",$id);
	}
	// ��̓X�e���X�~�T�C���}��
	function msInterceptS($id, $tId, $name, $tName, $comName, $point, $missileE) {
		global $init;
		$this->secret("-{$init->tagName_}{$missileE}��{$init->_tagName}��<strong>���</strong>�ɂ���Č}�����ꂽ�悤�ł��B",$id, $tId);
		$this->late("-{$init->tagName_}{$missileE}��{$init->_tagName}��<strong>���</strong>�ɂ���Č}�����ꂽ�悤�ł��B",$tId);
	}
	// ��͒ʏ�~�T�C���}��
	function msIntercept($id, $tId, $name, $tName, $comName, $point, $missileE) {
		global $init;
		$this->out("-{$init->tagName_}{$missileE}��{$init->_tagName}��<strong>���</strong>�ɂ���Č}�����ꂽ�悤�ł��B",$id, $tId);
	}
	// �A�C�e���T�����O�J�n
	// �A�C�e������
	function ItemFound($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ł�{$init->tagComName_}{$comName}{$init->_tagComName}���ɁA<strong>{$point}</strong>����������܂����B",$id);
	}
	// �}�X�^�[�\�[�h����
	function SwordFound($id, $name, $mName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��<strong>���b{$mName}</strong>�̎c�[����V���؂��ῂ��M�����삯������I<strong>�}�X�^�[�\�[�h</strong>����������܂����B",$id);
	}
	// ���b�h�_�C������
	function RedFound($id, $name, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��<strong>�C��T���D</strong>��<strong>{$point}</strong>�𔭌����܂����B",$id);
	}
	// �W������
	function ZinFound($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ł�{$init->tagComName_}{$comName}{$init->_tagComName}���ɁA<strong>{$point}</strong>��߂܂��܂����B",$id);
	}
	// �E�B�X�v����
	function Zin3Found($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ł�{$init->tagComName_}{$comName}{$init->_tagComName}���ɁA<strong>{$point}</strong>���P�����Ă��܂����I<strong>�}�X�^�[�\�[�h</strong>��U�肩�����A����<strong>{$point}</strong>��߂܂��܂����B",$id);
	}
	// ���i����
	function Zin5Found($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ł�{$init->tagComName_}{$comName}{$init->_tagComName}���ɁA���ƂƂ��ɁA<strong>�}�i�E�N���X�^��</strong>���P���B���̔����̒�����<strong>{$point}</strong>������܂����B",$id);
	}
	// �W������
	function Zin6Found($id, $name, $comName, $point) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ł�{$init->tagComName_}{$comName}{$init->_tagComName}���ɁA�y�̒�����<strong>{$point}</strong>�𔭌��I<strong>{$point}</strong>��߂܂��܂����B",$id);
	}
	// ���łɂ���
	function IsFail($id, $name, $comName, $land) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A���ł�<strong>{$land}</strong>�����邽�ߒ��~����܂����B",$id);
	}
	// �T�b�J�[����
	function SoccerSuc($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��{$init->tagComName_}{$comName}{$init->_tagComName}�����{����܂����B",$id);
	}
	// �T�b�J�[���s
	function SoccerFail($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A<strong>�X�^�W�A��</strong>�������������ߎ��s�o���܂���ł����B",$id);
	}
	// �T�b�J�[���s�Q
	function SoccerFail2($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A<strong>�ΐ푊��</strong>������ɑI������Ă��Ȃ��������ߎ��s�o���܂���ł����B",$id);
	}
	// �������s
	function GameFail($id, $name, $comName) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}�ŗ\�肳��Ă���{$init->tagComName_}{$comName}{$init->_tagComName}�́A���蓇��<strong>�X�^�W�A��</strong>�������������ߎ��s�o���܂���ł����B",$id);
	}
	// ��������
	function GameWin($id, $tId, $name, $tName, $comName, $it, $tt) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}��</A>{$init->_tagName}��{$init->tagComName_}{$comName}{$init->_tagComName}���s���A<strong>{$it}�_��{$tt}�_</strong>�ŏ������܂����B",$id, $tId);
	}
	// �����s��
	function GameLose($id, $tId, $name, $tName, $comName, $it, $tt) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}��</A>{$init->_tagName}��{$init->tagComName_}{$comName}{$init->_tagComName}���s���A<strong>{$it}�_��{$tt}�_</strong>�Ŕs�ނ��܂����B",$id, $tId);
	}
	// ������������
	function GameDraw($id, $tId, $name, $tName, $comName, $it, $tt) {
		global $init;
		$this->out("<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$id}\">{$init->tagName_}{$name}��</A>{$init->_tagName}��<A href=\"{$GLOBALS['THIS_FILE']}?Sight={$tId}\">{$init->tagName_}{$tName}��</A>{$init->_tagName}��{$init->tagComName_}{$comName}{$init->_tagComName}���s���A<strong>{$it}�_��{$tt}�_</strong>�ň��������܂����B",$id, $tId);
	}
}

?>
