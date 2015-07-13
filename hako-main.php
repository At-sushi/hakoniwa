<?php

/*******************************************************************

	���돔�� S.E
	
	- ���C���t�@�C�� -
	
	hako-main.php by SERA - 2013/05/19

*******************************************************************/

require 'jcode.phps';
require 'config.php';
require 'hako-cgi.php';
require 'hako-file.php';
require 'hako-html.php';
require 'hako-turn.php';
require 'hako-util.php';

$init = new Init;

define("READ_LINE", 1024);
$THIS_FILE = $init->baseDir . "/hako-main.php";
$BACK_TO_TOP = "<A HREF=\"{$THIS_FILE}?\">{$init->tagBig_}�g�b�v�֖߂�{$init->_tagBig}</A>";
$ISLAND_TURN; // �^�[����

$PRODUCT_VERSION = '20130519';

//--------------------------------------------------------------------
class Hako extends HakoIO {
	var $islandList;    // �����X�g
	var $targetList;    // �^�[�Q�b�g�̓����X�g
	var $defaultTarget; // �ڕW�⑫�p�^�[�Q�b�g
	
	function readIslands(&$cgi) {
		global $init;
		
		$m = $this->readIslandsFile($cgi);
		$this->islandList = $this->getIslandList($cgi->dataSet['defaultID']);
		if($init->targetIsland == 1) {
			// �ڕW�̓� ���L�̓����I�����ꂽ���X�g
			$this->targetList = $this->islandList;
		} else {
			// ���ʂ�TOP�̓����I�����ꂽ��Ԃ̃��X�g
			$this->targetList = $this->getIslandList($cgi->dataSet['defaultTarget']);
		}
		return $m;
	}
	//---------------------------------------------------
	// �����X�g����
	//---------------------------------------------------
	function getIslandList($select = 0) {
		global $init;
		
		$list = "";
		for($i = 0; $i < $this->islandNumber; $i++) {
			if($init->allyUse) {
				$name = Util::islandName($this->islands[$i], $this->ally, $this->idToAllyNumber); // �����}�[�N��ǉ�
			} else {
				$name = $this->islands[$i]['name'];
			}
			$id = $this->islands[$i]['id'];
			
			// �U���ڕW�����炩���ߎ����̓��ɂ���
			if(empty($this->defaultTarget)) {
				$this->defaultTarget = $id;
			}
			
			if($id == $select) {
				$s = "selected";
			} else {
				$s = "";
			}
			if($init->allyUse) {
				$list .= "<option value=\"$id\" $s>{$name}</option>\n"; // �����}�[�N��ǉ�
			} else {
				$list .= "<option value=\"$id\" $s>{$name}��</option>\n";
			}
		}
		return $list;
	}
	//---------------------------------------------------
	// �܂Ɋւ��郊�X�g�𐶐�
	//---------------------------------------------------
	function getPrizeList($prize) {
		global $init;
		list($flags, $monsters, $turns) = split(",", $prize, 3);
		
		$turns = split(",", $turns);
		$prizeList = "";
		// �^�[���t
		$max = -1;
		$nameList = "";
		if($turns[0] != "") {
			for($k = 0; $k < count($turns) - 1; $k++) {
				$nameList .= "[{$turns[$k]}] ";
				$max = $k;
			}
		}
		if($max != -1) {
			$prizeList .= "<img src=\"prize0.gif\" alt=\"$nameList\" title=\"$nameList\" width=\"16\" height=\"16\"> ";
		}
		// ��
		$f = 1;
		for($k = 1; $k < count($init->prizeName); $k++) {
			if($flags & $f) {
				$prizeList .= "<img src=\"prize{$k}.gif\" alt=\"{$init->prizeName[$k]}\" title=\"{$init->prizeName[$k]}\" width=\"16\" height=\"16\"> ";
			}
			$f = $f << 1;
		}
		// �|�������b���X�g
		$f = 1;
		$max = -1;
		$nameList = "";
		for($k = 0; $k < $init->monsterNumber; $k++) {
			if($monsters & $f) {
				$nameList .= "[{$init->monsterName[$k]}] ";
				$max = $k;
			}
			$f = $f << 1;
		}
		if($max != -1) {
			$prizeList .= "<img src=\"monster{$max}.gif\" alt=\"{$nameList}\" title=\"{$nameList}\" width=\"16\" height=\"16\"> ";
		}
		return $prizeList;
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
					// ����
					$image = 'land17.gif';
					$naviTitle = '�C';
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
					$treasure = $ship[3] * 1000 + $ship[4] * 100;
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
				$naviText = "{$lv}{$init->unitPop}/�E��{$lwork}0{$init->unitPop}/�_��{$mwork}0{$init->unitPop}";
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

//--------------------------------------------------------------------
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
			Util::unlock($lock);
			exit();
		}
		$lock = Util::lock($fp);
		if(FALSE == $lock) {
			exit;
		}
		$cgi->setCookies();
		$cgi->lastModified();

		if($cgi->dataSet['DEVELOPEMODE'] == "java") {
			$html = new HtmlJS;
			$com = new MakeJS;
		} else {
			$html = new HtmlMap;
			$com = new Make;
		}
		switch($cgi->mode) {
			case "turn":
				$turn = new Turn;
				$html = new HtmlTop;
				$html->header($cgi->dataSet);
				$turn->turnMain($hako, $cgi->dataSet); 
				$html->main($hako, $cgi->dataSet); // �^�[��������ATOP�y�[�Wopen
				$html->footer();
				break;
				
			case "owner":
				$html->header($cgi->dataSet);
				$html->owner($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "command":
				$html->header($cgi->dataSet);
				$com->commandMain($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "new":
				$html->header($cgi->dataSet);
				$com->newIsland($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "comment":
				$html->header($cgi->dataSet);
				$com->commentMain($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "print":
				$html->header($cgi->dataSet);
				$html->visitor($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "targetView":
				$html->header($cgi->dataSet);
				$html->printTarget($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "change":
				$html->header($cgi->dataSet);
				$com->changeMain($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "ChangeOwnerName":
				$html->header($cgi->dataSet);
				$com->changeOwnerName($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "lbbs":
				$lbbs = new Make;
				$html->header($cgi->dataSet);
				$lbbs->localBbsMain($hako, $cgi->dataSet);
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
			case "conf":
				$html = new HtmlTop;
				$html->header($cgi->dataSet);
				$html->regist($hako, $cgi->dataSet);
				$html->footer();
				break;
				
			case "log":
				$html = new HtmlTop;
				$html->header($cgi->dataSet);
				$html->log();
				$html->footer();
				break;
				
			default: 
				$html = new HtmlTop;
				$html->header($cgi->dataSet);
				$html->main($hako, $cgi->dataSet);
				$html->footer();
		}
		Util::unlock($lock);
		exit();
	}
}

$start = new Main;
$start->execute();

?>
