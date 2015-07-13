<?php

/*******************************************************************

	���돔�� S.E
	
	- ���ҏW�p�t�@�C�� -
	
	hako-edit.php by SERA - 2013/06/02

*******************************************************************/

require 'jcode.phps';
require 'config.php';
require 'hako-file.php';
require 'hako-html.php';
require 'hako-util.php';

$init = new Init;

define("READ_LINE", 1024);
$THIS_FILE = $init->baseDir . "/hako-edit.php";
$BACK_TO_TOP = "<A HREF=\"JavaScript:void(0);\" onClick=\"document.TOP.submit();return false;\">{$init->tagBig_}�g�b�v�֖߂�{$init->_tagBig}</A>";

//----------------------------------------------------------------------
class Hako extends HakoIO {

	function readIslands(&$cgi) {
		global $init;
		
		$m = $this->readIslandsFile($cgi);
		return $m;
	}
	
	//---------------------------------------------------
	// �n�`�Ɋւ���f�[�^����
	//---------------------------------------------------
	function landString($l, $lv, $x, $y, $mode, $comStr) {
		global $init;
		$point = "({$x},{$y})";
		$naviExp = "''";
		
		if($x < $init->islandSize / 2) {
			$naviPos = 0;
		} else {
			$naviPos = 1;
		}
		switch($l) {
			case $init->landSea:
				if($lv == 0) {
					// �C
					$image = 'land0.gif';
					$naviTitle = '�C';
				} elseif($lv == 1) {
					// ��
					$image = 'land14.gif';
					$naviTitle = '��';
				} else {
					// ����H
					$image = 'land17.gif';
					$naviTitle = '�C';
					$naviText = "{$lv}";
				}
				break;
				
			case $init->landSeaCity:
				// �C��s�s
				$image = 'SeaCity.gif';
				$naviTitle = '�C��s�s';
				$naviText = "{$lv}{$init->unitPop}";
				break;
				
			case $init->landFroCity:
				// �C��s�s
				$image = 'FroCity.gif';
				$naviTitle = '�C��s�s';
				$naviText = "{$lv}{$init->unitPop}";
				break;
				
			case $init->landPort:
				// �`
				$image = 'port.gif';
				$naviTitle = '�`';
				break;
				
			case $init->landShip:
				// �D��
				$ship = Util::navyUnpack($lv);
				$owner = $this->idToName[$ship[0]]; // ����
				$naviTitle = "{$init->shipName[$ship[1]]}"; // �D���̎��
				$hp = round(100 - $ship[2] / $init->shipHP[$ship[1]] * 100); // �j����
				if($ship[1] <= 1) {
					// �A���D�A���D
					$naviText = "{$owner}������";
				} elseif($ship[1] == 2) {
					// �C��T���D
					$treasure = $ship[3] * 1000 + $ship[4] * 100;
					if($treasure > 0) {
						$naviText = "{$owner}������<br>�j�����F{$hp}%<br>{$treasure}���~�����̍���ύ�";
					} else {
						$naviText = "{$owner}������";
					}
				} elseif($ship[1] < 10) {
					$naviText = "{$owner}������<br>�j�����F{$hp}%";
				} else {
					// �C���D
					$naviText = "�j�����F{$hp}%";
				}
				$image = "ship{$ship[1]}.gif"; // �D���摜
				break;
				
			case $init->landRail:
				// ���H
				$image = "rail{$lv}.gif";
				$naviTitle = '���H';
				break;
				
			case $init->landStat:
				// �w
				$image = 'stat.gif';
				$naviTitle = '�w';
				break;
				
			case $init->landTrain:
				// �d��
				$image = "train{$lv}.gif";
				$naviTitle = '�d��';
				break;
				
			case $init->landZorasu:
				// �C���b
				$image = 'zorasu.gif';
				$naviTitle = '���炷';
				break;
				
			case $init->landSeaSide:
				// �C��
				$image = 'sunahama.gif';
				$naviTitle = '���l';
				break;
				
			case $init->landSeaResort:
				// �C�̉�
				if($lv < 30) {
					$image = 'umi1.gif';
					$naviTitle = '�C�̉�';
				} else if($lv < 100) {
					$image = 'umi2.gif';
					$naviTitle = '���h';
				} else {
					$image = 'umi3.gif';
					$naviTitle = '���]�[�g�z�e��';
				}
				$naviText = "����:{$lv}{$init->unitPop} <br>";
				break;
				
			case $init->landSoccer:
				// �X�^�W�A��
				$image = 'stadium.gif';
				$naviTitle = '�X�^�W�A��';
				break;
				
			case $init->landPark:
				// �V���n
				$image = "park{$lv}.gif";
				$naviTitle = '�V���n';
				break;
				
			case $init->landFusya:
				// ����
				$image = 'fusya.gif';
				$naviTitle = '����';
				break;
				
			case $init->landSyoubou:
				// ���h��
				$image = 'syoubou.gif';
				$naviTitle = '���h��';
				break;
				
			case $init->landSsyoubou:
				// �C����h��
				$image = 'syoubou2.gif';
				$naviTitle = '�C����h��';
				break;
				
			case $init->landNursery:
				// �{�B��
				$image = 'Nursery.gif';
				$naviTitle = '�{�B��';
				$naviText = "{$lv}0{$init->unitPop}�K��";
				break;
				
			case $init->landWaste:
				// �r�n
				if($lv == 1) {
					$image = 'land13.gif'; // ���e�_
				} else {
					$image = 'land1.gif';
				}
				$naviTitle = '�r�n';
				break;
				
			case $init->landPlains:
				// ���n
				$image = 'land2.gif';
				$naviTitle = '���n';
				break;
				
			case $init->landPoll:
				// �����y��
				$image = 'poll.gif';
				$naviTitle = '�����y��';
				$naviText = "�������x��{$lv}";
				break;
				
			case $init->landForest:
				// �X
				if($mode == 1) {
					$image = 'land6.gif';
					$naviText= "${lv}{$init->unitTree}";
				} else {
					// �ό��҂̏ꍇ�͖؂̖{���B��
					$image = 'land6.gif';
				}
				$naviTitle = '�X';
				break;
				
			case $init->landTown:
				// ��
				$p; $n;
				if($lv < 30) {
					$p = 3;
					$naviTitle = '��';
				} else if($lv < 100) {
					$p = 4;
					$naviTitle = '��';
				} else if($lv < 200) {
					$p = 5;
					$naviTitle = '�s�s';
				} else {
					$p = 52;
					$naviTitle = '��s�s';
				}
				$image = "land{$p}.gif";
				$naviText = "{$lv}{$init->unitPop}";
				break;
				
			case $init->landProcity:
				// �h�Гs�s
				if($lv < 110) {
					$naviTitle = '�h�Гs�s�����N�d';
				} else if($lv < 130) {
					$naviTitle = '�h�Гs�s�����N�c';
				} else if($lv < 160) {
					$naviTitle = '�h�Гs�s�����N�b';
				} else if($lv < 200) {
					$naviTitle = '�h�Гs�s�����N�a';
				} else {
					$naviTitle = '�h�Гs�s�����N�`';
				}
				$image = "bousai.gif";
				$naviText = "{$lv}{$init->unitPop}";
				break;
				
			case $init->landNewtown:
				// �j���[�^�E��
				$level = Util::expToLevel($l, $lv);
				$nwork = (int)($lv/15);
				$image = 'new.gif';
				$naviTitle = '�j���[�^�E��';
				$naviText = "{$lv}{$init->unitPop}/�E��{$nwork}0{$init->unitPop}";
				break;
				
			case $init->landBigtown:
				// ����s�s
				$level = Util::expToLevel($l, $lv);
				$mwork = (int)($lv/20);
				$lwork = (int)($lv/30);
				$image = 'big.gif';
				$naviTitle = '����s�s';
				$naviText = "{$lv}{$init->unitPop}/�E��{$mwork}0{$init->unitPop}/�_��{$lwork}0{$init->unitPop}";
				break;
				
			case $init->landFarm:
				// �_��
				$image = 'land7.gif';
				$naviTitle = '�_��';
				$naviText = "{$lv}0{$init->unitPop}�K��";
				if($lv > 25) {
					// �h�[���^�_��
					$image = 'land71.gif';
					$naviTitle = '�h�[���^�_��';
				}
				break;
				
			case $init->landSfarm:
				// �C��_��
				$image = 'land72.gif';
				$naviTitle = '�C��_��';
				$naviText = "{$lv}0{$init->unitPop}�K��";
				break;
				
			case $init->landFactory:
				// �H��
				$image = 'land8.gif';
				$naviTitle = '�H��';
				$naviText = "{$lv}0{$init->unitPop}�K��";
				if($lv > 100) {
					// ��H��
					$image = 'land82.gif';
					$naviTitle = '��H��';
				}
				break;
				
			case $init->landCommerce:
				// ���ƃr��
				$image = 'commerce.gif';
				$naviTitle = '���ƃr��';
				$naviText = "{$lv}0{$init->unitPop}�K��";
				if($lv > 150) {
					// �{�Ѓr��
					$image = 'commerce2.gif';
					$naviTitle = '�{�Ѓr��';
				}
				break;
				
			case $init->landHatuden:
				// ���d��
				$image = 'hatuden.gif';
				$naviTitle = '���d��';
				$naviText = "{$lv}000kw";
				if($lv > 100) {
					// ��^���d��
					$image = 'hatuden2.gif';
					$naviTitle = '��^���d��';
				}
				break;
				
			case $init->landBank:
				// ��s
				$image = 'bank.gif';
				$naviTitle = '��s';
				break;
				
			case $init->landBase:
				if($mode == 0 || $mode == 2) {
					// �ό��҂̏ꍇ�͐X�̂ӂ�
					$image = 'land6.gif';
					$naviTitle = '�X';
				} else {
					// �~�T�C����n
					$level = Util::expToLevel($l, $lv);
					$image = 'land9.gif';
					$naviTitle = '�~�T�C����n';
					$naviText = "���x�� ${level} / �o���l {$lv}";
				}
				break;
			case $init->landSbase:
				// �C���n
				if($mode == 0 || $mode == 2) {
					// �ό��҂̏ꍇ�͊C�̂ӂ�
					$image = 'land0.gif';
					$naviTitle = '�C';
				} else {
					$level = Util::expToLevel($l, $lv);
					$image = 'land12.gif';
					$naviTitle = '�C���n';
					$naviText = "���x�� ${level} / �o���l {$lv}";
				}
				break;
				
			case $init->landDefence:
				// �h�q�{��
				if($mode == 0 || $mode == 2) {
					$image = 'land10.gif';
					$naviTitle = '�h�q�{��';
				} else {
					$image = 'land10.gif';
					$naviTitle = '�h�q�{��';
					$naviText = "�ϋv�� {$lv}";
				}
				break;
				
			case $init->landHaribote:
				// �n���{�e
				$image = 'land10.gif';
				if($mode == 0 || $mode == 2) {
					// �ό��҂̏ꍇ�͖h�q�{�݂̂ӂ�
					$naviTitle = '�h�q�{��';
				} else {
					$naviTitle = '�n���{�e';
				}
				break;
				
			case $init->landSdefence:
				// �C��h�q�{��
				if($mode == 0 || $mode == 2) {
					$image = 'land102.gif';
					$naviTitle = '�C��h�q�{��';
				} else {
					$image = 'land102.gif';
					$naviTitle = '�C��h�q�{��';
					$naviText = "�ϋv�� {$lv}";
				}
				break;
				
			case $init->landOil:
				// �C����c
				$image = 'land16.gif';
				$naviTitle = '�C����c';
				break;
				
			case $init->landMountain:
				// �R
				if($lv > 0) {
					$image = 'land15.gif';
					$naviTitle = '�̌@��';
					$naviText = "{$lv}0{$init->unitPop}�K��";
				} else {
					$image = 'land11.gif';
					$naviTitle = '�R';
				}
				break;
				
			case $init->landMyhome:
				// ����
				$image = "home{$lv}.gif";
				$naviTitle = '�}�C�z�[��';
				$naviText = "{$lv}�l�Ƒ�";
				break;
				
			case $init->landSoukoM:
				$flagm = 1;
			case $init->landSoukoF:
				// �q��
				if($flagm == 1) {
					$naviTitle = '����';
				} else {
					$naviTitle = '�H����';
				}
				$image = "souko.gif";
				$sec = (int)($lv / 100);
				$tyo = $lv % 100;
				if($l == $init->landSoukoM) {
					if($tyo == 0) {
						$naviText = "�Z�L�����e�B�F{$sec}�A�����F�Ȃ�";
					} else {
						$naviText = "�Z�L�����e�B�F{$sec}�A�����F{$tyo}000{$init->unitMoney}";
					}
				} else {
					if($tyo == 0) {
						$naviText = "�Z�L�����e�B�F{$sec}�A���H�F�Ȃ�";
					} else {
						$naviText = "�Z�L�����e�B�F{$sec}�A���H�F{$tyo}000{$init->unitFood}";
					}
				}
				break;
				
			case $init->landMonument:
				// �L�O��
				$image = "monument{$lv}.gif";
				$naviTitle = '�L�O��';
				$naviText = $init->monumentName[$lv];
				break;
				
			case $init->landMonster:
			case $init->landSleeper:
				// ���b
				$monsSpec = Util::monsterSpec($lv);
				$spec = $monsSpec['kind'];
				$special = $init->monsterSpecial[$spec];
				$image = "monster{$spec}.gif";
				if($l == $init->landSleeper) {
					$naviTitle = '���b�i�������j';
				} else {
					$naviTitle = '���b';
				}
				
				// �d����?
				if((($special & 0x4) && (($this->islandTurn % 2) == 1)) ||
					 (($special & 0x10) && (($this->islandTurn % 2) == 0))) {
					// �d����
					$image = $init->monsterImage[$monsSpec['kind']];
				}
				$naviText = "���b{$monsSpec['name']}(�̗�{$monsSpec['hp']})";
		}
		
		if($mode == 1 || $mode == 2) {
			print "<a href=\"javascript: void(0);\" onclick=\"ps($x,$y)\">";
			$naviText = "{$comStr}\\n{$naviText}";
		}
		print "<img src=\"{$image}\" width=\"32\" height=\"32\" alt=\"{$point} {$naviTitle} {$comStr}\" onMouseOver=\"Navi({$naviPos},'{$image}', '{$naviTitle}', '{$point}', '{$naviText}', {$naviExp});\" onMouseOut=\"NaviClose(); return false\">";
		
		// ���W�ݒ��
		if($mode == 1 || $mode == 2) {
			print "</a>";
		}
	}
}

//----------------------------------------------------------------------
class Cgi {
	var $mode = "";
	var $dataSet = array();
	//---------------------------------------------------
	// POST�AGET�̃f�[�^���擾
	//---------------------------------------------------
	function parseInputData() {
		global $init;

		$this->mode = $_POST['mode'];
		if(!empty($_POST)) {
			while(list($name, $value) = each($_POST)) {
				$value = str_replace(",", "", $value);
				$value = JcodeConvert($value, 0, 2);
				$value = HANtoZEN_SJIS($value);
				if($init->stripslashes == true) {
					$this->dataSet["{$name}"] = stripslashes($value);
				} else {
					$this->dataSet["{$name}"] = $value;
				}
			}
			if(!empty($_POST['Sight'])) {
				$this->dataSet['ISLANDID'] = $_POST['Sight'];
			}
		}
	}
	
	//---------------------------------------------------
	// COOKIE���擾
	//---------------------------------------------------
	function getCookies() {
		if(!empty($_COOKIE)) {
			while(list($name, $value) = each($_COOKIE)) {
				switch($name) {
					case "POINTX":
						$this->dataSet['defaultX'] = $value;
						break;
					case "POINTY":
						$this->dataSet['defaultY'] = $value;
						break;
					case "LAND":
						$this->dataSet['defaultLAND'] = $value;
						break;
					case "MONSTER":
						$this->dataSet['defaultMONSTER'] = $value;
						break;
					case "SHIP":
						$this->dataSet['defaultSHIP'] = $value;
						break;
					case "LEVEL":
						$this->dataSet['defaultLEVEL'] = $value;
						break;
					case "SKIN":
						$this->dataSet['defaultSkin'] = $value;
						break;
					case "IMG":
						$this->dataSet['defaultImg'] = $value;
						break;
				}
			}
		}
	}
	
	//---------------------------------------------------
	// COOKIE�𐶐�
	//---------------------------------------------------
	function setCookies() {
		$time = time() + 30; // ���� + 30�b�L��
		
		// Cookie�̐ݒ� & POST�œ��͂��ꂽ�f�[�^�ŁACookie����擾�����f�[�^���X�V
		if($this->dataSet['POINTX']) {
			setcookie("POINTX",$this->dataSet['POINTX'], $time);
			$this->dataSet['defaultX'] = $this->dataSet['POINTX'];
		}
		if($this->dataSet['POINTY']) {
			setcookie("POINTY",$this->dataSet['POINTY'], $time);
			$this->dataSet['defaultY'] = $this->dataSet['POINTY'];
		}
		if($this->dataSet['LAND']) {
			setcookie("LAND",$this->dataSet['LAND'], $time);
			$this->dataSet['defaultLAND'] = $this->dataSet['LAND'];
		}
		if($this->dataSet['MONSTER']) {
			setcookie("MONSTER",$this->dataSet['MONSTER'], $time);
			$this->dataSet['defaultMONSTER'] = $this->dataSet['MONSTER'];
		}
		if($this->dataSet['SHIP']) {
			setcookie("SHIP",$this->dataSet['SHIP'], $time);
			$this->dataSet['defaultSHIP'] = $this->dataSet['SHIP'];
		}
		if($this->dataSet['LEVEL']) {
			setcookie("LEVEL",$this->dataSet['LEVEL'], $time);
			$this->dataSet['defaultLEVEL'] = $this->dataSet['LEVEL'];
		}
		if($this->dataSet['SKIN']) {
			setcookie("SKIN",$this->dataSet['SKIN'], $time);
			$this->dataSet['defaultSkin'] = $this->dataSet['SKIN'];
		}
		if($this->dataSet['IMG']) {
			setcookie("IMG",$this->dataSet['IMG'], $time);
			$this->dataSet['defaultImg'] = $this->dataSet['IMG'];
		}
	}
}

//----------------------------------------------------------------------
class Edit {
	//---------------------------------------------------
	// TOP �\���i�p�X���[�h���́j
	//---------------------------------------------------
	function enter() {
		global $init;
		
		print <<<END
<h1 class="title">{$init->title}<br>�}�b�v�E�G�f�B�^</h1>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<strong>�p�X���[�h�F</strong>
<input type="password" size="32" maxlength="32" name="PASSWORD">
<input type="hidden" name="mode" value="list">
<input type="submit" value="�ꗗ��">
</form>
END;
	}
	
	//---------------------------------------------------
	// ���̈ꗗ��\��
	//---------------------------------------------------
	function main($hako, $data) {
		global $init;
		
		// �p�X���[�h
		if(!Util::checkPassword("", $data['PASSWORD'])) {
			// password�ԈႢ
			Error::wrongPassword();
			return;
		}
		print "<CENTER><a href=\"{$init->baseDir}/hako-main.php\"><span class=\"big\">�g�b�v�֖߂�</span></a></CENTER>\n";
		print "<h1 class=\"title\">{$init->title}<br>�}�b�v�E�G�f�B�^</h1>\n";
		print <<<END
<h2 class='Turn'>�^�[��$hako->islandTurn</h2>
<hr>
<div ID="IslandView">
<h2>�����̏�</h2>
<p>
���̖��O���N���b�N����ƁA<strong>�}�b�v</strong>���\������܂��B
</p>
<table border="1">
<tr>
<th {$init->bgTitleCell}>{$init->tagTH_}����{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}��{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}�l��{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}�ʐ�{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}����{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}�H��{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}�_��K��{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}�H��K��{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}�̌@��K��{$init->_tagTH}</th>
</tr>
END;
		// �\�����e�́A�Ǘ��җp�̓��e
		for($i = 0; $i < $hako->islandNumber; $i++) {
			$island = $hako->islands[$i];
			$j = ($island['isBF']) ? '��' : $i + 1;
			$id = $island['id'];
			$pop = $island['pop'] . $init->unitPop;
			$area = $island['area'] . $init->unitArea;
			$money = $island['money'] . $init->unitMoney;
			$food = $island['food'] . $init->unitFood;
			$farm = ($island['farm'] <= 0) ? "�ۗL����" : $island['farm'] * 10 . $init->unitPop;
			$factory = ($island['factory'] <= 0) ? "�ۗL����" : $island['factory'] * 10 . $init->unitPop;
			$mountain = ($island['mountain'] <= 0) ? "�ۗL����" : $island['mountain'] * 10 . $init->unitPop;
			$comment = $island['comment'];
			$comment_turn = $island['comment_turn'];
			$monster = '';
			if($island['monster'] > 0) {
				$monster = "<strong class=\"monster\">[���b{$island['monster']}��]</strong>";
			}
			$name = "";
			if($island['absent'] == 0) {
				$name = "{$init->tagName_}{$island['name']}��{$init->_tagName}";
			} else {
				$name = "{$init->tagName2_}{$island['name']}��({$island['absent']}){$init->_tagName2}";
			}
			if(!empty($island['owner'])) {
				$owner = $island['owner'];
			} else {
				$owner = "�R�����g";
			}
			if($init->commentNew > 0 && ($comment_turn + $init->commentNew) > $hako->islandTurn) {
				$comment .= " <span class=\"new\">New</span>";
			}
			if($hako->islandNumber - $i == $hako->islandNumberBF) {
				print "</table>\n</div>\n";
				print "<BR>\n";
				print "<hr>\n\n";
				print "<div ID=\"IslandView\">\n";
				print "<h2>Battle Field�̏�</h2>\n";
				
				print <<<END
<table border="1">
<tr>
<th {$init->bgTitleCell}>{$init->tagTH_}����{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}��{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}�l��{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}�ʐ�{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}����{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}�H��{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}�_��K��{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}�H��K��{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}�̌@��K��{$init->_tagTH}</th>
</tr>
END;
			}
			print "<tr>\n";
			print "<th {$init->bgNumberCell} rowspan=\"2\">{$init->tagNumber_}$j{$init->_tagNumber}</th>\n";
			print "<td {$init->bgNameCell} rowspan=\"2\"><a href=\"JavaScript:void(0);\" onClick=\"document.MAP{$id}.submit();return false;\">{$name}</a> {$monster}<br>\n{$prize}</td>\n";
			print <<<END
<form name="MAP{$id}" action="{$GLOBALS['THIS_FILE']}" method="post">
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="hidden" name="mode" value="map">
<input type="hidden" name="Sight" value="{$id}">
</form>
END;
			print "<td {$init->bgInfoCell}>$pop</td>\n";
			print "<td {$init->bgInfoCell}>$area</td>\n";
			print "<td {$init->bgInfoCell}>$money</td>\n";
			print "<td {$init->bgInfoCell}>$food</td>\n";
			print "<td {$init->bgInfoCell}>$farm</td>\n";
			print "<td {$init->bgInfoCell}>$factory</td>\n";
			print "<td {$init->bgInfoCell}>$mountain</td>\n";
			print "</tr>\n";
			print "<tr>\n";
			print "<td {$init->bgCommentCell} colspan=\"7\">{$init->tagTH_}{$owner}�F{$init->_tagTH}$comment</td>\n";
			print "</tr>\n";
		}
		print "</table>\n</div>\n";
	}
	//---------------------------------------------------
	// �}�b�v�G�f�B�^�̕\��
	//---------------------------------------------------
	function editMap($hako, $data) {
		global $init;
		
		// �p�X���[�h
		if(!Util::checkPassword("", $data['PASSWORD'])) {
			// password�ԈႢ
			Error::wrongPassword();
			return;
		}
		$html = new HtmlMap;
		$id = $data['ISLANDID'];
		$number = $hako->idToNumber[$id];
		$island = $hako->islands[$number];
		
		// �n�`���X�g�𐶐�
		$landList = array (
			"$init->landSea",
			"$init->landSeaSide",
			"$init->landWaste",
			"$init->landPoll",
			"$init->landPlains",
			"$init->landForest",
			"$init->landTown",
			"$init->landProcity",
			"$init->landNewtown",
			"$init->landBigtown",
			"$init->landSeaCity",
			"$init->landFroCity",
			"$init->landPort",
			"$init->landShip",
			"$init->landRail",
			"$init->landStat",
			"$init->landTrain",
			"$init->landFusya",
			"$init->landSyoubou",
			"$init->landSsyoubou",
			"$init->landFarm",
			"$init->landSfarm",
			"$init->landNursery",
			"$init->landFactory",
			"$init->landCommerce",
			"$init->landMountain",
			"$init->landHatuden",
			"$init->landBase",
			"$init->landHaribote",
			"$init->landDefence",
			"$init->landSbase",
			"$init->landSdefence",
			"$init->landMyhome",
			"$init->landSoukoM",
			"$init->landSoukoF",
			"$init->landMonument",
			"$init->landSoccer",
			"$init->landPark",
			"$init->landSeaResort",
			"$init->landOil",
			"$init->landBank",
			"$init->landMonster",
			"$init->landSleeper",
			"$init->landZorasu"
		);
			
		// �n�`���X�g���̂𐶐�
		$landName = array (
			"�C�A��",
			"���l",
			"�r�n",
			"�����y��",
			"���n",
			"�X",
			"���A���A�s�s",
			"�h�Гs�s",
			"�j���[�^�E��",
			"����s�s",
			"�C��s�s",
			"�C��s�s",
			"�`",
			"�D��",
			"���H",
			"�w",
			"�d��",
			"����",
			"���h��",
			"�C����h��",
			"�_��",
			"�C��_��",
			"�{�B��",
			"�H��",
			"���ƃr��",
			"�R�A�̌@��",
			"���d��",
			"�~�T�C����n",
			"�n���{�e",
			"�h�q�{��",
			"�C���n",
			"�C��h�q�{��",
			"�}�C�z�[��",
			"����",
			"�H����",
			"�L�O��",
			"�X�^�W�A��",
			"�V���n",
			"�C�̉�",
			"�C����c",
			"��s",
			"���b",
			"���b�i�������j",
			"���炷"
		);
		print <<<END
<script type="text/javascript">
<!--
function ps(x, y, ld, lv) {
	document.InputPlan.POINTX.options[x].selected = true;
	document.InputPlan.POINTY.options[y].selected = true;
	document.InputPlan.LAND.options[ld].selected = true;
	
	if(ld == {$init->landMonster} || ld == {$init->landSleeper}) {
		mn = Math.floor(lv / 10);
		lv = lv - mn * 10;
		document.InputPlan.MONSTER.options[mn].selected = true;
		document.InputPlan.LEVEL.options[lv].selected = true;
	} else {
		document.InputPlan.LEVEL.options[lv].selected = true;
	}
	return true;
}
//-->
</script>
<div align="center">
{$init->tagBig_}{$init->tagName_}{$island['name']}��{$init->_tagName}�}�b�v�E�G�f�B�^{$init->_tagBig}<br>
{$GLOBALS['BACK_TO_TOP']}
</div>

<form name="TOP" action="{$GLOBALS['THIS_FILE']}" method="post">
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<input type="hidden" name="mode" value="list">
</form>
END;
		// ���̏���\��
		$html->islandInfo($island, $number, 1);
		
		// ��������\��
		print <<<END
<div align="center">
<table border="1">
<tr valign="top">
<td {$init->bgCommandCell}>
<b>���x���ɂ���</b>
<ul>
<li><b>�C�A��</b><br>���x�� 0 �̂Ƃ��C<br>1 �̂Ƃ���<br>����ȊO �̂Ƃ�����
<li><b>�r�n</b><br>���x�� 1 �̂Ƃ����e�_
<li><b>���A���A�s�s</b><br>���x�� 30 ��������<br>���x�� 100 ��������<br>���x�� 200 �������s�s
<li><b>�~�T�C����n</b><br>�o���l
<li><b>�R�A�̌@��</b><br>���x�� 1 �ȏ�̂Ƃ��̌@��
<li><b>���b</b><br>�e���b�̍ő僌�x���𒴂���<br>�ݒ�͂ł��܂���
<li><b>�C���n</b><br>�o���l
</ul>

</td>
<td {$init->bgMapCell}>
END;
		// �n�`�o��
		$html->islandMap($hako, $island, 1);
		
		// �G�f�B�^�̈�̕\��
		print <<<END
</td>
<td {$init->bgInputCell}>
<div align="center">
<form action="{$GLOBALS['THIS_FILE']}" method="post" name="InputPlan">
<input type="hidden" name="mode" value="regist">
<input type="hidden" name="ISLANDID" value="{$island['id']}">
<input type="hidden" name="PASSWORD" value="{$data['PASSWORD']}">
<strong>�}�b�v�E�G�f�B�^</strong><br>
<hr>
<strong>���W(</strong>
<select name="POINTX">
END;
		for($i = 0; $i < $init->islandSize; $i++) {
			if($i == $data['defaultX']) {
				print "<option value=\"{$i}\" selected>{$i}</option>\n";
			} else {
				print "<option value=\"{$i}\">{$i}</option>\n";
			}
		}
		print "</select>, <select name=\"POINTY\">";
		for($i = 0; $i < $init->islandSize; $i++) {
			if($i == $data['defaultY']) {
				print "<option value=\"{$i}\" selected>{$i}</option>\n";
			} else {
				print "<option value=\"{$i}\">{$i}</option>\n";
			}
		}
		print <<<END
</select><strong>)</strong>
<hr>
<strong>�n�`</strong>
<select name="LAND">
END;
		for($i = 0; $i < count($landList); $i++) {
			if($landList[$i] == $data['defaultLAND']) {
				print "<option value=\"{$landList[$i]}\" selected>{$landName[$i]}</option>\n";
			} else {
				print "<option value=\"{$landList[$i]}\">{$landName[$i]}</option>\n";
			}
		}
		print <<<END
</select>
<hr>
<strong>���b�̎��</strong>
<select name="MONSTER">
END;
		for($i = 0; $i < $init->monsterNumber; $i++) {
			if($i == $data['defaultMONSTER']) {
				print "<option value=\"{$i}\" selected>{$init->monsterName[$i]}</option>\n";
			} else {
				print "<option value=\"{$i}\">{$init->monsterName[$i]}</option>\n";
			}
		}
		print <<<END
</select>
<hr>
<strong>�D���̎��</strong>
<select name="SHIP">
END;
		for($i = 0; $i < 15; $i++) {
			if($init->shipName[$i] != "") {
				if($i == $data['defaultSHIP']) {
					print "<option value=\"{$i}\" selected>{$init->shipName[$i]}</option>\n";
				} else {
						print "<option value=\"{$i}\">{$init->shipName[$i]}</option>\n";
				}
			}
		}
		print <<<END
</select>
<hr>
<strong>���x��</strong>
<input type="number" min="0" max="1048575" size="8" maxlength="7" name="LEVEL" value="{$data['defaultLEVEL']}">
<hr>
<input type="submit" value="�o�^">
</form>
</div>

<ul>
<li>�o�^����Ƃ��͏\�����ӊ肢�܂��B
<li>�f�[�^��j�󂷂�ꍇ������܂��B
<li>�o�b�N�A�b�v���s���Ă���<br>�s���l�ɂ��܂��傤�B
<li>�n�`�f�[�^��ύX����݂̂ŁA<br>���̃f�[�^�͕ύX����܂���B<br>
�^�[���X�V�ő��̃f�[�^��<br>���f����܂��B
</ul>

</td>
</tr>
</table>
</div>
END;
	}
	//---------------------------------------------------
	// �n�`�̓o�^
	//---------------------------------------------------
	function regist($hako, $data) {
		global $init;
		
		// �p�X���[�h
		if(!Util::checkPassword("", $data['PASSWORD'])) {
			// password�ԈႢ
			Error::wrongPassword();
			return;
		}
		
		$id = $data['ISLANDID'];
		$number = $hako->idToNumber[$id];
		$island = $hako->islands[$number];
		$land = &$island['land'];
		$landValue = &$island['landValue'];
		$x = $data['POINTX'];
		$y = $data['POINTY'];
		$ld = $data['LAND'];
		$mons = $data['MONSTER'];
		$ships = $data['SHIP'];
		$level = $data['LEVEL'];
		
		if($ld == $init->landMonster || $ld == $init->landSleeper) {
			// ���b�̃��x���ݒ�
			$BHP = $init->monsterBHP[$mons];
			if($init->monsterDHP[$mons] > 0) {
				$DHP = Util::random($init->monsterDHP[$mons] - 1);
			} else {
				$DHP = Util::random($init->monsterDHP[$mons]);
			}
			$level = $BHP + $DHP;
			$level = $mons * 100 + $level;
		} elseif($ld == $init->landShip) {
			// �D���̃��x���ݒ�
			$level = Util::navyPack($id, $ships, $init->shipHP[$ships], 0, 0);
		}
		
		// �X�V�f�[�^�ݒ�
		$land[$x][$y] = $ld;
		$landValue[$x][$y] = $level;
		
		// �}�b�v�f�[�^�X�V
		$hako->writeLand($id, $island);
		
		// �ݒ肵���l��߂�
		$hako->islands[$number] = $island;
		
		print "{$init->tagBig_}�n�`��ύX���܂���{$init->_tagBig}<hr>\n";
		
		// �}�b�v�G�f�B�^�̕\����
		$this->editMap($hako, $data);
	}
}

//----------------------------------------------------------------------
class Main {

	function execute() {
		$hako = new Hako;
		$cgi = new Cgi;
		$cgi->parseInputData();
		$cgi->getCookies();
		if(!$hako->readIslands($cgi)) {
			HTML::header($cgi->dataSet);
			Error::noDataFile();
			HTML::footer();
			exit();
		}
		$cgi->setCookies();
		$edit = new Edit;
		
		switch($cgi->mode) {
			case "enter":
				$html = new HtmlTop;
				$html->header($cgi->dataSet);
				$edit->main($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "list":
				$html = new HtmlTop;
				$html->header($cgi->dataSet);
				$edit->main($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "map":
				$html = new HtmlTop;
				$html->header($cgi->dataSet);
				$edit->editMap($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "regist":
				$html = new HtmlTop;
				$html->header($cgi->dataSet);
				$edit->regist($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "skin":
				$html = new HtmlSetted;
				$html->header($cgi->dataSet);
				$html->setSkin();
				$html->footer();
				break;
				
			case "imgset":
				$html = new HtmlSetted;
				$html->header($cgi->dataSet);
				$html->setImg();
				$html->footer();
				break;
				
			default:
				$html = new HtmlTop;
				$html->header($cgi->dataSet);
				$edit->enter();
				$html->footer();
		}
		exit();
	}
}

$start = new Main;
$start->execute();

?>
