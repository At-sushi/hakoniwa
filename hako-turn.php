<?php

/*******************************************************************

	���돔�� S.E
	
	- �^�[���X�V�p�t�@�C�� -
	
	hako-turn.php by SERA - 2013/06/01

*******************************************************************/

require 'hako-log.php';
require 'hako-make.php';

//--------------------------------------------------------------------
class MakeJS extends Make {

	//---------------------------------------------------
	// �R�}���h���[�h
	//---------------------------------------------------
	function commandMain($hako, $data) {
		global $init;
		
		$id = $data['ISLANDID'];
		$num = $hako->idToNumber[$id];
		$island = $hako->islands[$num];
		$name = $island['name'];
		
		// �p�X���[�h
		if(!Util::checkPassword($island['password'], $data['PASSWORD'])) {
			// password�ԈႢ
			Error::wrongPassword();
			return;
		}
		// ���[�h�ŕ���
		$command = $island['command'];
		$comary = split(" " , $data['COMARY']);
		
		for($i = 0; $i < $init->commandMax; $i++) {
			$pos = $i * 5;
			$kind   = $comary[$pos];
			$x      = $comary[$pos + 1];
			$y      = $comary[$pos + 2];
			$arg    = $comary[$pos + 3];
			$target = $comary[$pos + 4];
			
			// �R�}���h�o�^
			if($kind == 0) {
				$kind = $init->comDoNothing;
			}
			$command[$i] = array (
				'kind'   => $kind,
				'x'      => $x,
				'y'      => $y,
				'arg'    => $arg,
				'target' => $target
			);
		}
		HtmlSetted::commandAdd();
		
		// �f�[�^�̏����o��
		$island['command'] = $command;
		$hako->islands[$num] = $island;
		$hako->writeIslandsFile($island['id']);
		
		// owner mode��
		$html = new HtmlJS;
		$html->owner($hako, $data);
	}
}

//--------------------------------------------------------------------
class Turn {
	var $log;
	var $rpx;
	var $rpy;
	
	//---------------------------------------------------
	// �^�[���i�s���[�h
	//---------------------------------------------------
	function turnMain(&$hako, $data) {
		global $init;
		
		$this->log = new Log;
		
		// �ŏI�X�V���Ԃ��X�V
		if($init->contUpdate == 1) {
			$uptime = 1;
		} else {
			$uptime = (int)((time() - $hako->islandLastTime) / $init->unitTime);
		}
		$hako->islandLastTime += $init->unitTime * $uptime;
		
		// ���O�t�@�C�������ɂ��炷
		$this->log->slideBackLogFile();
		
		// �^�[���ԍ�
		$hako->islandTurn++;
		$GLOBALS['ISLAND_TURN'] = $hako->islandTurn;
		
		if($hako->islandNumber == 0) {
			// �����Ȃ���΃^�[������ۑ����Ĉȍ~�̏����͏Ȃ�
			// �t�@�C���ɏ����o��
			$hako->writeIslandsFile();
			return;
		}
		// �v���[���g�t�@�C����ǂݍ���(�I��Ώ���)
		$hako->readPresentFile(true);
		
		// ���W�z������
		$randomPoint = Util::makeRandomPointArray();
		$this->rpx = $randomPoint[0];
		$this->rpy = $randomPoint[1];
		
		// ���Ԍ���
		$order = Util::randomArray($hako->islandNumber);
		
		// �D��������
		for($i = 0; $i < $hako->islandNumber; $i++) {
			$this->shipcounter($hako, $hako->islands[$order[$i]]);
		}
		
		// �X�V�O�̏�񃁃�
		for($i = 0; $i < $hako->islandNumber; $i++) {
			// �Ǘ��l�a���蒆�̏ꍇ�X�L�b�v
			if($hako->islands[$order[$i]]['keep']) {
				continue;
			}
			// �l���A�����A�H���A�|�C���g����������
			$hako->islands[$order[$i]]['oldMoney'] = $hako->islands[$order[$i]]['money'];
			$hako->islands[$order[$i]]['oldFood']  = $hako->islands[$order[$i]]['food'];
			$hako->islands[$order[$i]]['oldPop']   = $hako->islands[$order[$i]]['pop'];
			$hako->islands[$order[$i]]['oldPoint'] = $hako->islands[$order[$i]]['point'];
			$this->estimate($hako, $hako->islands[$order[$i]]);
		}
		
		// �����E����
		for($i = 0; $i < $hako->islandNumber; $i++) {
			// �Ǘ��l�a���蒆�̏ꍇ�X�L�b�v
			if($hako->islands[$order[$i]]['keep']) {
				continue;
			}
			$this->income($hako->islands[$order[$i]]);
		}
		
		// �R�}���h����
		for($i = 0; $i < $hako->islandNumber; $i++) {
			// �Ǘ��l�a���蒆�̏ꍇ�X�L�b�v
			if($hako->islands[$order[$i]]['keep']) {
				continue;
			}
			
			// �߂�l1�ɂȂ�܂ŌJ��Ԃ�
			while($this->doCommand($hako, $hako->islands[$order[$i]]) == 0);
			
			// ���n���O (�܂Ƃ߂ă��O�o��)
			if($init->logOmit) {
				$this->logMatome($hako->islands[$order[$i]]);
			}
		}
		
		// ��������ђP�w�b�N�X�ЊQ
		for($i = 0; $i < $hako->islandNumber; $i++) {
			// �Ǘ��l�a���蒆�̏ꍇ�X�L�b�v
			if($hako->islands[$order[$i]]['keep']) {
				continue;
			}
			$this->doEachHex($hako, $hako->islands[$order[$i]]);
		}
		// ���S�̏���
		$remainNumber = $hako->islandNumber;
		
		for($i = 0; $i < $hako->islandNumber; $i++) {
			// �Ǘ��l�a���蒆�̏ꍇ�X�L�b�v
			if($hako->islands[$order[$i]]['keep']) {
				continue;
			}
			$island = $hako->islands[$order[$i]];
			$this->doIslandProcess($hako, $island);
			
			// ���Ŕ���
			if($island['dead'] == 1) {
				$island['pop']   = 0;
				$island['point'] = 0;
				$remainNumber--;
			} elseif((($island['pop'] == 0) || ($island['point'] == 0)) && ($island['isBF'] != 1)) {
				$island['dead'] = 1;
				$remainNumber--;
				// ���Ń��b�Z�[�W
				$tmpid = $island['id'];
				$this->log->dead($tmpid, $island['name']);
				if(is_file("{$init->dirName}/island.{$tmpid}")) {
					unlink("{$init->dirName}/island.{$tmpid}");
				}
			}
			$hako->islands[$order[$i]] = $island;
		}
		
		// �l�����Ƀ\�[�g
		$this->islandSort($hako);
		
		// �^�[���t�Ώۃ^�[����������A���̏���
		if(($hako->islandTurn % $init->turnPrizeUnit) == 0) {
			$island = $hako->islands[0];
			$this->log->prize($island['id'], $island['name'], "{$hako->islandTurn}{$init->prizeName[0]}");
			$hako->islands[0]['prize'] .= "{$hako->islandTurn},";
		}
		// �����J�b�g
		$hako->islandNumber = $remainNumber;
		
		// �D��������
		for($i = 0; $i < $hako->islandNumber; $i++) {
			$this->shipcounter($hako, $hako->islands[$order[$i]]);
		}
		
		for($i = 0; $i < $hako->islandNumber; $i++) {
			$this->estimate($hako, $hako->islands[$order[$i]]);
		}
		
		// �o�b�N�A�b�v�^�[���ł���΁A�����O��rename
		if(!($init->safemode) && (($hako->islandTurn % $init->backupTurn) == 0)) {
			$hako->backUp();
		}
		// �o�b�N�A�b�v�^�[���ł���΁A�Z�[�t���[�h�o�b�N�A�b�v�擾
		if(($init->safemode) && (($hako->islandTurn % $init->backupTurn) == 0)) {
			$hako->safemode_backup();
		}
		// �t�@�C���ɏ����o��
		$hako->writeIslandsFile(-1);
		
		// ���O�����o��
		$this->log->flush();
		
		// �L�^���O����
		$this->log->historyTrim();
	}
	
	//---------------------------------------------------
	// ���O���܂Ƃ߂�
	//---------------------------------------------------
	function logMatome($island) {
		global $init;
		
		$sno = $island['seichi'];
		$point = "";
		if($sno > 0) {
			if($init->logOmit == 1) {
				$sArray = $island['seichipnt'];
				for($i = 0; $i < $sno; $i++) {
					$spnt = $sArray[$i];
					if($spnt == "") {
						break;
					}
					$x = $spnt['x'];
					$y = $spnt['y'];
					$point .= "($x, $y) ";
					if(!(($i+1)%20)) {
						// �S�p�󔒂R��
						$point .= "<br>�@�@�@";
					}
				}
			}
			if($i > 1 || ($init->logOmit != 1)) {
				$point .= "��<strong>{$sno}�P��</strong>";
			}
		}
		if($point != "") {
			if(($init->logOmit == 1) && ($sno > 1)) {
				$this->log->landSucMatome($island['id'], $island['name'], '���n', $point);
			} else {
				$this->log->landSuc($island['id'], $island['name'], '���n', $point);
			}
		}
	}
	
	//---------------------------------------------------
	// �R�}���h�t�F�C�Y
	//---------------------------------------------------
	function doCommand(&$hako, &$island) {
		global $init;
		
		$comArray  = &$island['command'];
		$command   = $comArray[0];
		Util::slideFront(&$comArray, 0);
		$island['command'] = $comArray;
		$kind      = $command['kind'];
		$target    = $command['target'];
		$x         = $command['x'];
		$y         = $command['y'];
		$arg       = $command['arg'];
		$name      = $island['name'];
		$id        = $island['id'];
		$land      = $island['land'];
		$landValue = &$island['landValue'];
		$landKind  = &$land[$x][$y];
		$lv        = $landValue[$x][$y];
		$cost      = $init->comCost[$kind];
		$comName   = $init->comName[$kind];
		$point     = "({$x},{$y})";
		$landName  = $this->landName($landKind, $lv);
		$prize     = &$island['prize'];
		
		if($kind == $init->comDoNothing) {
			//$this->log->doNothing($id, $name, $comName);
			if($island['isBF'] == 1) {
				$island['money'] = $init->maxMoney;
				$island['food'] = $init->maxFood;
			} else {
				$island['money'] += 10;
				$island['absent']++;
				
				// ��������
				if($island['absent'] >= $init->giveupTurn) {
					$comArray[0] = array (
						'kind'   => $init->comGiveup,
						'target' => 0,
						'x'      => 0,
						'y'      => 0,
						'arg'    => 0
					);
					$island['command'] = $comArray;
				}
				return 1;
			}
		}
		$island['command'] = $comArray;
		$island['absent']  = 0;
		
		// �R�X�g�`�F�b�N
		if($cost > 0) {
			// ���̏ꍇ
			if($island['money'] < $cost) {
				$this->log->noMoney($id, $name, $comName);
				return 0;
			}
		} elseif($cost < 0) {
			// �H���E�؍ނ̏ꍇ
			if(($kind == $init->comSell || $kind == $init->comSoukoF) && ($island['food'] < (-$cost))) {
				$this->log->noFood($id, $name, $comName);
				return 0;
			} elseif(($kind == $init->comSellTree) && ($island['item'][20] < (-$cost))) {
				$this->log->noWood($id, $name, $comName);
				return 0;
			}
		}
		$returnMode = 1;
		
		switch($kind) {
			case $init->comPrepare:
			case $init->comPrepare2:
				// ���n�A�n�Ȃ炵
				if (($landKind == $init->landSea) ||
					($landKind == $init->landPoll) ||
					($landKind == $init->landSbase) ||
					($landKind == $init->landSdefence) ||
					($landKind == $init->landSeaSide) ||
					($landKind == $init->landSeaCity) ||
					($landKind == $init->landFroCity) ||
					($landKind == $init->landSfarm) ||
					($landKind == $init->landNursery) ||
					($landKind == $init->landOil) ||
					($landKind == $init->landPort) ||
					($landKind == $init->landMountain) ||
					($landKind == $init->landMonster) ||
					($landKind == $init->landSleeper) ||
					($landKind == $init->landZorasu)) {
					// �C�A���l�A�����y��A�C���n�A�C��h�q�{�݁A�C��s�s
					// �C��s�s�A�C��_��A�{�B��A���c�A�`�A�R�A���b�͐��n�ł��Ȃ�
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				// �΂͐��n�E�n�Ȃ炵�ŋ��ɁA���͐H���ɂȂ�
				if($landKind == $init->landMonument) {
					if((33 < $lv) && ($lv < 40)) {
						// ���ɂȂ�
						$island['money'] += 9999;
					} elseif((39 < $lv) && ($lv < 44)) {
						// �H���ɂȂ�
						$island['food'] += 5000;
					}
				}
				// �ړI�̏ꏊ�𕽒n�ɂ���
				$land[$x][$y] = $init->landPlains;
				$landValue[$x][$y] = 0;
				
				// ���n���O�̂܂Ƃ�
				if($init->logOmit) {
					$sno = $island['seichi'];
					$island['seichi']++;
					
					// ���W����̂܂Ƃ�
					if($init->logOmit == 1) {
						$seichipnt['x'] = $x;
						$seichipnt['y'] = $y;
						$island['seichipnt'][$sno] = $seichipnt;
					}
				} else {
					$this->log->landSuc($id, $name, '���n', $point);
				}
				// �����̗�����
				if(Util::random(100) < 3) {
					$this->log->EggFound($id, $name, $comName, $point);
					$land[$x][$y] = $init->landMonument;
					$landValue[$x][$y] = 40 + Util::random(3);
				}
				// �A�C�e����������
				if(Util::random(100) < 7) {
					// �n�}�P����
					if(($island['tenki'] == 1) && ($island['item'][0] != 1)) {
						$island['item'][0] = 1;
						$this->log->ItemFound($id, $name, $comName, '�����̒n�}');
					} elseif($island['tenki'] == 4) {
						// �V�C�����̂Ƃ�
						if(($island['item'][3] == 1) && ($island['item'][4] != 1)) {
							// �|�`�����L������
							$island['item'][4] = 1;
							$this->log->ItemFound($id, $name, $comName, '��̐l�`');
						} elseif(($island['item'][6] == 1) && ($island['item'][7] == 1) && ($island['item'][8] != 1)) {
							// ��O�̔]����
							$island['item'][8] = 1;
							$this->log->ItemFound($id, $name, $comName, '�]�̌`����������');
						} elseif(($island['item'][9] == 1) && ($island['taiji'] >= 7) && ($island['zin'][2] != 1)) {
							// �V�F�C�h����
							$itemName = "�V�F�C�h";
							$island['zin'][2] = 1;
							$this->log->Zin3Found($id, $name, $comName, '�V�F�C�h');
						}
					} elseif($island['tenki'] == 5) {
						// �V�C����̂Ƃ�
						if(($island['item'][4] == 1) && ($island['item'][5] != 1)) {
							// �n�}�Q����
							$island['item'][5] = 1;
							$this->log->ItemFound($id, $name, $comName, '�����̒n�}');
						} elseif(($island['item'][17] == 1) && ($island['item'][18] != 1)) {
							// �����O����
							$island['item'][18] = 1;
							$this->log->ItemFound($id, $name, $comName, '�����O');
						}
					} elseif(($island['item'][0] == 1) && ($island['zin'][0] != 1)) {
						// �m�[������
						$island['zin'][0] = 1;
						$this->log->ZinFound($id, $name, $comName, '�m�[��');
					}
				}
				// ������������
				$island['money'] -= $cost;
				
				if($kind == $init->comPrepare2) {
					// �n�Ȃ炵
					$island['prepare2']++;
					// �^�[�������
					$returnMode = 0;
				} else {
					// ���n�Ȃ�A�������̉\������
					if($island['zin'][0] == 1) {
						// �m�[���������������m��
						$r = Util::random(500);
					} else {
						$r = Util::random(1000);
					}
					if($r < $init->disMaizo) {
						$v = 100 + Util::random(901);
						$island['money'] += $v;
						$this->log->maizo($id, $name, $comName, $v);
					}
					$returnMode = 1;
				}
				break;
				
			case $init->comReclaim:
				// ���ߗ���
				if(!(($landKind == $init->landSea) && ($lv < 2)) &&
					($landKind != $init->landOil) &&
					($landKind != $init->landPort) &&
					($landKind != $init->landNursery) &&
					($landKind != $init->landSfarm) &&
					($landKind != $init->landSeaSide) &&
					($landKind != $init->landSsyoubou) &&
					($landKind != $init->landSeaCity) &&
					($landKind != $init->landFroCity) &&
					($landKind != $init->landSdefence) &&
					($landKind != $init->landSbase)) {
					// �C�A���l�A�C���n�A���c�A�`�A�C����h���A�C��h�q�{��
					// �C��s�s�A�C��s�s�A�C��_��A�{�B�ꂵ�����ߗ��Ăł��Ȃ�
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				// ����ɗ������邩�`�F�b�N
				$seaCount = Turn::countAround($land, $x, $y, 7, array($init->landSea, $init->landSeaSide, $init->landSeaCity, $init->landFroCity,$init->landOil, $init->landNursery, $init->landSfarm, $init->landPort, $init->landSdefence, $init->landSbase));
				
				if($seaCount == 7) {
					// �S���C�����疄�ߗ��ĕs�\
					$this->log->noLandAround($id, $name, $comName, $point);
					$returnMode = 0;
					break;
				}
				if((($landKind == $init->landSea) && ($lv == 1)) || ($landKind == $init->landSeaSide)) {
					// �󐣂����l�̏ꍇ
					// �ړI�̏ꏊ���r�n�ɂ���
					$land[$x][$y] = $init->landWaste;
					$landValue[$x][$y] = 0;
					$this->log->landSuc($id, $name, $comName, $point);
					if ($landKind != $init->landSeaSide) {
						$island['area']++;
					}
					if($seaCount <= 4) {
						// ����̊C��3�w�b�N�X�ȓ��Ȃ̂ŁA�󐣂ɂ���
						for($i = 1; $i < 7; $i++) {
							$sx = $x + $init->ax[$i];
							$sy = $y + $init->ay[$i];
							// �s�ɂ��ʒu����
							if((($sy % 2) == 0) && (($y % 2) == 1)) {
								$sx--;
							}
							if(($sx < 0) || ($sx >= $init->islandSize) || ($sy < 0) || ($sy >= $init->islandSize)) {
							} else {
								// �͈͓��̏ꍇ
								if($land[$sx][$sy] == $init->landSea) {
									$landValue[$sx][$sy] = 1;
								}
							}
						}
					}
				} else {
					// �C�Ȃ�A�ړI�̏ꏊ��󐣂ɂ���
					$land[$x][$y] = $init->landSea;
					$landValue[$x][$y] = 1;
					$this->log->landSuc($id, $name, $comName, $point);
					
					// �֒f�̏�����
					if((Util::random(100) < 7) && ($island['tenki'] == 2) && ($island['item'][2] != 1)) {
						$island['item'][2] = 1;
						$this->log->ItemFound($id, $name, $comName, '�Âڂ�������');
					}
				}
				// ������������
				$island['money'] -= $cost;
				
				$returnMode = 1;
				break;
				
			case $init->comDestroy:
				// �@��
				if((($landKind == $init->landSea) && ($lv > 1)) ||
					($landKind == $init->landPoll) ||
					($landKind == $init->landOil) ||
					($landKind == $init->landPort) ||
					($landKind == $init->landSeaCity) ||
					($landKind == $init->landFroCity) ||
					($landKind == $init->landSfarm) ||
					($landKind == $init->landNursery) ||
					($landKind == $init->landSbase) ||
					($landKind == $init->landSdefence) ||
					($landKind == $init->landMonster) ||
					($landKind == $init->landSleeper) ||
					($landKind == $init->landZorasu)) {
					// �D���A�����y��A���c�A�`�A�C��s�s�A�C��s�s�A�C��_��A�{�B��
					// �C���n�A�C��h�q�{�݁A���b�A���炷�͌@��ł��Ȃ�
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				if(($landKind == $init->landSea) && ($lv == 0)) {
					// �C�Ȃ�A���c�T��
					// �����z����
					if($arg == 0) {
						$arg = 1;
					}
					$value = min($arg * ($cost), $island['money']);
					$str = "{$value}{$init->unitMoney}";
					$p = round($value / $cost);
					$island['money'] -= $value;
					
					// ���c�����邩����
					if($p > Util::random(100)) {
						// ���c������
						$this->log->oilFound($id, $name, $point, $comName, $str);
						$island['oil']++;
						$land[$x][$y] = $init->landOil;
						$landValue[$x][$y] = 0;
					} else {
						// ���ʌ����ɏI���
						$this->log->oilFail($id, $name, $point, $comName, $str);
					}
					$returnMode = 1;
					break;
				}
				// �ړI�̏ꏊ���C�ɂ���B�R�Ȃ�r�n�ɁB�󐣂Ȃ�C�ɁB
				if($landKind == $init->landMountain) {
					$land[$x][$y] = $init->landWaste;
					$landValue[$x][$y] = 0;
				} elseif($landKind == $init->landSeaCity) {
					$land[$x][$y] = $init->landSea;
					$landValue[$x][$y] = 0;
				} elseif($landKind == $init->landSea) {
					$landValue[$x][$y] = 0;
				} else {
					$land[$x][$y] = $init->landSea;
					$landValue[$x][$y] = 1;
					$island['area']--;
				}
				$this->log->landSuc($id, $name, $comName, $point);
				
				// ������������
				$island['money'] -= $cost;
				
				if((Util::random(100) < 7) && ($island['tenki'] == 2) && ($island['item'][15] != 1)) {
					// �}�i�E�N���X�^������
					$island['item'][15] = 1;
					$this->log->ItemFound($id, $name, $comName, '����߂����');
				}
				$returnMode = 1;
				break;
				
			case $init->comDeForest:
				// ����
				if($landKind != $init->landForest) {
					// �X�ȊO�͔��̂ł��Ȃ�
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				// �ړI�̏ꏊ�𕽒n�ɂ���
				$land[$x][$y] = $init->landPlains;
				$landValue[$x][$y] = 0;
				$this->log->landSuc($id, $name, $comName, $point);
				
				if((Util::random(100) < 7) && ($island['tenki'] == 1)) {
					// �V�C������̂Ƃ�
					if($island['item'][1] != 1) {
						// �m�R�M������
						$island['item'][1] = 1;
						$this->log->ItemFound($id, $name, $comName, '�m�R�M��');
					} elseif(($island['item'][5] == 1) && ($island['zin'][1] != 1)) {
						// �E�B�X�v����
						$island['zin'][1] = 1;
						$this->log->ZinFound($id, $name, $comName, '�E�B�X�v');
					}
				}
				if($island['item'][20] >= $init->maxWood) {
					// �؍ލő�l�𒴂����ꍇ�A���p���𓾂�
					$island['money'] += $init->treeValue * $lv;
				} else {
					// �؍ނ𓾂�
					$island['item'][20] += $lv;
				}
				if($island['item'][1] == 1) {
					$returnMode = 0;
					break;
				}
				$returnMode = 1;
				break;
				
			case $init->comSeaSide:
				// ���l����
				if(($landKind == $init->landSea) && ($lv != 1)) {
					// �󐣈ȊO�͐����ł��Ȃ�
					$this->log->landFail($id, $name, $comName, $landName, $point);
					
					$returnMode = 0;
					break;
				}
				if((($landKind == $init->landSea) && ($lv == 1)) || ($landKind == $init->landSeaSide)) {
					// ����ɗ������邩�`�F�b�N
					$seaCount = Turn::countAround($land, $x, $y, 7, array($init->landSea, $init->landSeaSide, $init->landPort,
					$init->landOil, $init->landNursery, $init->landSbase));
					if($seaCount == 7) {
						$this->log->noLandAround($id, $name, $comName, $point);
						$returnMode = 0;
						break;
					}
					$land[$x][$y] = $init->landSeaSide;
					$landValue[$x][$y] = 0;
				}
				$this->log->LandSuc($id, $name, $comName, $point);
				
				// ������������
				$island['money'] -= $cost;
				
				$returnMode = 1;
				break;
				
			case $init->comPort:
				// �`
				if(!($landKind == $init->landSea && $lv == 1)){
					// �󐣈ȊO�ɂ͌��ݕs��
					$this->log->LandFail($id, $name, $comName, $landName, $point);
					
					$returnMode = 0;
					break;
				}
				$seaCount = Turn::countAround($land, $x, $y, 7, array($init->landSea));
				
				if($seaCount <= 1){
					// ���͂ɍŒ�1Hex�̊C�������ꍇ�����ݕs��
					$this->log->NoSeaAround($id, $name, $comName, $point);
					
					$returnMode = 0;
					break;
				}
				if($seaCount == 7){
					// ���肪�S���C�Ȃ̂ō`�͌��݂ł��Ȃ�
					$this->log->NoLandAround($id, $name, $comName, $point);
					
					$returnMode = 0;
					break;
				}
				$land[$x][$y] = $init->landPort;
				$landValue[$x][$y] = 0;
				$this->log->LandSuc($id, $name, $comName, $point);
				
				// ������������
				$island['money'] -= $cost;
				
				$returnMode = 1;
				break;
				
			case $init->comMakeShip:
				// ���D
				$countPort = Turn::countAround($land, $x, $y, 7, array($init->landPort));
				if($countPort < 1){
					// ����1�w�b�N�X�ɍ`���Ȃ��Ǝ��s
					$this->log->NoPort($id, $name, $comName, $point);
					$returnMode = 0;
					break;
				}
				if(!($landKind == $init->landSea && $lv == 0)){
					// �D��ݒu����ꏊ���C�Ŗ����ꍇ�͎��s
					$this->log->NoSea($id, $name, $comName, $point);
					$returnMode = 0;
					break;
				}
				$ownShip = 0;
				for($i = 0; $i < 10; $i++) {
					$ownShip += $island['ship'][$i];
				}
				if($init->shipMax <= $ownShip){
					// �D���ő及�L�ʂ𒴂��Ă����ꍇ�A�p��
					$this->log->maxShip($id, $name, $comName, $point);
					$returnMode = 0;
					break;
				}
				$land[$x][$y] = $init->landShip;
				$landValue[$x][$y] = Util::navyPack($island['id'], $arg, $init->shipHP[$arg], 0, 0);
				$this->log->LandSuc($id, $name, $init->shipName[$arg]."��".$comName, $point);
				
				// ������������
				$island['money'] -= $cost;
				
				$returnMode = 1;
				break;
				
			case $init->comSendShip:
				// �D�h��
				// �^�[�Q�b�g�擾
				$tn = $hako->idToNumber[$target];
				if($tn != 0 && empty($tn)) {
					// �^�[�Q�b�g�����łɂȂ�
					$this->log->ssNoTarget($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				// ���O����
				$tIsland    = $hako->islands[$tn];
				$tId        = $tIsland['id'];
				$tName      = $tIsland['name'];
				$tLand      = $tIsland['land'];
				$tLandValue = $tIsland['landValue'];
				
				$ship = Util::navyUnpack($landValue[$x][$y]);
				
				// ���s����
				if($land[$x][$y] != $init->landShip) {
					// �Ώۂ��D���ȊO�̏ꍇ
					$this->log->landFail($id, $name, $comName, "�D���ȊO�̒n�`", $point);
					$returnMode = 0;
					break;
				} elseif($ship[1] >= 10) {
					// �Ώۂ��C���D�̏ꍇ
					$this->log->landFail($id, $name, $comName, $init->shipName[$ship[1]], $point);
					$returnMode = 0;
					break;
				} elseif($ship[0] != $island['id']) {
					// �Ώۂ������̑D���̏ꍇ
					$this->log->landFail($id, $name, $comName, "���������̑D��", $point);
					$returnMode = 0;
					break;
				} elseif($tId == $island['id']) {
					// �h���悪�����̂��ߒ��~
					$this->log->shipFail($id, $name, $comName, "�h���悪����");
					$returnMode = 0;
					break;
				}
				
				// �h���n�_�����߂�
				for ($i = 0; $i < $init->pointNumber; $i++) {
					$bx = $this->rpx[$i];
					$by = $this->rpy[$i];
					if(($tLand[$bx][$by] == $init->landSea) && ($tLandValue[$bx][$by] == 0)){
						break;
					}
				}
				// �h����
				$tLand[$bx][$by]      = $init->landShip;
				$tLandValue[$bx][$by] = $lv;
				// �h����
				$land[$x][$y]      = $init->landSea;
				$landValue[$x][$y] = 0;
				
				// �h�����O
				$this->log->shipSend($id, $tId, $name, $init->shipName[$ship[1]], "({$x}, {$y})", $tName);
				
				$tIsland['land']      = $tLand;
				$tIsland['landValue'] = $tLandValue;
				$hako->islands[$tn]   = $tIsland;
				
				// ������������
				$island['money'] -= $cost;
				
				$returnMode = 1;
				break;
				
			case $init->comReturnShip:
				// �D�A��
				// �^�[�Q�b�g�擾
				$tn = $hako->idToNumber[$target];
				if($tn != 0 && empty($tn)) {
					// �^�[�Q�b�g�����łɂȂ�
					$this->log->ssNoTarget($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				// ���O����
				$tIsland    = $hako->islands[$tn];
				$tId        = $tIsland['id'];
				$tName      = $tIsland['name'];
				$tLand      = $tIsland['land'];
				$tLandValue = $tIsland['landValue'];
				$ship = Util::navyUnpack($tLandValue[$x][$y]);
				
				// ���s����
				if($tLand[$x][$y] != $init->landShip) {
					// �Ώۂ��D���ȊO�̏ꍇ
					$this->log->landFail($id, $name, $comName, "�D���ȊO�̒n�`", $point);
					$returnMode = 0;
					break;
				} elseif($ship[1] >= 10) {
					// �Ώۂ��C���D�̏ꍇ
					$this->log->landFail($id, $name, $comName, $init->shipName[$ship[1]], $point);
					$returnMode = 0;
					break;
				} elseif($ship[0] != $island['id']) {
					// �Ώۂ������̑D���̏ꍇ
					$this->log->landFail($id, $name, $comName, "���������̑D��", $point);
					$returnMode = 0;
					break;
				} elseif($tId == $island['id']) {
					// ���łɎ����ɋA�ҍς݂̏ꍇ
					$this->log->shipFail($id, $name, $comName, "�Ώۂ̑D�������łɋA�ҍς�");
					$returnMode = 0;
					break;
				}
				
				if($ship[1] == 2 && ($ship[1] > 0 || $ship[4] > 0)) {
					// �A�Ҏ��ɊC��T���D�̍�������
					$treasure = $ship[3] * 1000 + $ship[4] * 100;
					$tLandValue[$x][$y] = Util::navyPack($ship[0], $ship[1], $ship[2], 0, 0);
					$island['money'] += $treasure;
					$this->log->RecoveryTreasure($id, $name, $init->shipName[$ship[1]], $treasure);
				}
				
				// �h���n�_�����߂�
				for ($i = 0; $i < $init->pointNumber; $i++) {
					$bx = $this->rpx[$i];
					$by = $this->rpy[$i];
					if(($land[$bx][$by] == $init->landSea) && ($landValue[$bx][$by] == 0)){
						break;
					}
				}
				// �A�Ґ�i�����j
				$land[$bx][$by]      = $init->landShip;
				$landValue[$bx][$by] = $tLandValue[$x][$y];
				// �h����i�����j
				$tLand[$x][$y]      = $init->landSea;
				$tLandValue[$x][$y] = 0;
				
				// �A�҃��O
				$this->log->shipReturn($id, $tId, $name, $init->shipName[$ship[1]], "({$x}, {$y})", $tName);
				
				$tIsland['land']      = $tLand;
				$tIsland['landValue'] = $tLandValue;
				$hako->islands[$tn]   = $tIsland;
				
				// ������������
				$island['money'] -= $cost;
				
				$returnMode = 1;
				break;
				
			case $init->comShipBack:
				// �D�j��
				$ship = Util::navyUnpack($landValue[$x][$y]);
				
				// ���s����
				if($land[$x][$y] != $init->landShip) {
					// �Ώۂ��D���ȊO�̏ꍇ
					$this->log->landFail($id, $name, $comName, "�D���ȊO�̒n�`", $point);
					$returnMode = 0;
					break;
				} elseif($landKind == $init->landShip && $ship[1] >= 10) {
					// �Ώۂ��C���D�̏ꍇ
					$this->log->landFail($id, $name, $comName, $init->shipName[$ship[1]], $point);
					$returnMode = 0;
					break;
				} elseif($landKind == $init->landShip && $ship[0] != $island['id']) {
					// �Ώۂ������̑D���̏ꍇ
					$this->log->landFail($id, $name, $comName, "���������̑D��", $point);
					$returnMode = 0;
					break;
				}
				$land[$x][$y] = $init->landSea;
				$landValue[$x][$y] = 0;
				$this->log->ComeBack($id, $name, $comName, $init->shipName[$ship[1]], $point);
				
				// ������������
				$island['money'] -= $cost;
				
				$returnMode = 1;
				break;
				
			case $init->comPlant:
			case $init->comFarm:
			case $init->comNursery:
			case $init->comFactory:
			case $init->comHatuden:
			case $init->comCommerce:
			case $init->comBase:
			case $init->comMyhome:
			case $init->comSoukoM:
			case $init->comSoukoF:
			case $init->comHikidasi:
			case $init->comMonument:
			case $init->comNewtown:
			case $init->comHaribote:
			case $init->comDbase:
			case $init->comRail:
			case $init->comStat:
			case $init->comPark:
			case $init->comSeaResort:
			case $init->comFusya:
			case $init->comSyoubou:
			case $init->comSoccer:
				// �n�㌚�݌n
				if(!(($landKind == $init->landPlains) ||
					($landKind == $init->landTown) ||
					(($landKind == $init->landMyhome) && ($kind == $init->comMyhome)) ||
					(($landKind == $init->landSoukoM) && ($kind == $init->comSoukoM)) ||
					(($landKind == $init->landSoukoF) && ($kind == $init->comSoukoF)) ||
					(($landKind == $init->landSoukoM || $landKind == $init->landSoukoF) && ($kind == $init->comHikidasi)) ||
					(($landKind == $init->landMonument) && ($kind == $init->comMonument)) ||
					(($landKind == $init->landFarm) && ($kind == $init->comFarm)) ||
					(($landKind == $init->landlandSea) && ($lv == 1) && ($kind == $init->comNursery)) ||
					(($landKind == $init->landNursery) && ($kind == $init->comNursery)) ||
					(($landKind == $init->landFactory) && ($kind == $init->comFactory)) ||
					(($landKind == $init->landHatuden) && ($kind == $init->comHatuden)) ||
					(($landKind == $init->landCommerce) && ($kind == $init->comCommerce)) ||
					(($landKind == $init->landSoccer) && ($kind == $init->comSoccer)) ||
					(($landKind == $init->landRail) && ($kind == $init->comRail)) ||
					(($landKind == $init->landStat) && ($kind == $init->comStat)) ||
					(($landKind == $init->landPark) && ($kind == $init->comPark)) ||
					(($landKind == $init->landSeaResort) && ($kind == $init->comSeaResort)) ||
					(($landKind == $init->landFusya) && ($kind == $init->comFusya)) ||
					(($landKind == $init->landSyoubou) && ($kind == $init->comSyoubou)) ||
					(($landKind == $init->landDefence) && ($kind == $init->comDbase)))) {
					// �s�K���Ȓn�`
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				
				// ��ނŕ���
				switch($kind) {
					case $init->comPlant:
						// �ړI�̏ꏊ��X�ɂ���B
						$land[$x][$y] = $init->landForest;
						// �؂͍Œ�P��
						$landValue[$x][$y] = 1;
						$this->log->PBSuc($id, $name, $comName, $point);
						
						if(Util::random(100) < 7) {
							if($island['item'][10] != 1) {
								// �A���}�Ӕ���
								$island['item'][10] = 1;
								$this->log->ItemFound($id, $name, $comName, '�A���}��');
							} elseif(($island['item'][10] == 1) && ($island['item'][11] != 1)) {
								// ���[�ؔ���
								$island['item'][11] = 1;
								$this->log->ItemFound($id, $name, $comName, '���[��');
							} elseif(($island['item'][11] == 1) && ($island['item'][12] != 1)) {
								// �c�ؔ���
								$island['item'][12] = 1;
								$this->log->ItemFound($id, $name, $comName, '�c��');
							} elseif(($island['item'][12] == 1) && ($island['tenki'] == 3) && ($island['zin'][3] != 1)) {
								// �h���A�[�h����
								$island['zin'][3] = 1;
								$this->log->ZinFound($id, $name, $comName, '�h���A�[�h');
							}
						}
						break;
						
					case $init->comBase:
						// �ړI�̏ꏊ���~�T�C����n�ɂ���B
						$land[$x][$y] = $init->landBase;
						// �o���l0
						$landValue[$x][$y] = 0;
						$this->log->PBSuc($id, $name, $comName, $point);
						
						if((Util::random(100) < 7) && ($island['item'][6] != 1)) {
							// �Ȋw������
							$island['item'][6] = 1;
							$this->log->ItemFound($id, $name, $comName, '������ȏ���');
						}
						break;
						
					case $init->comHaribote:
						// �ړI�̏ꏊ���n���{�e�ɂ���
						$land[$x][$y] = $init->landHaribote;
						$landValue[$x][$y] = 0;
						$this->log->hariSuc($id, $name, $comName, $init->comName[$init->comDbase], $point);
						break;
						
					case $init->comNewtown:
						// �ړI�̏ꏊ���j���[�^�E���ɂ���
						$land[$x][$y] = $init->landNewtown;
						$landValue[$x][$y] = 1;
						$this->log->landSuc($id, $name, $comName, $point);
						break;
						
					case $init->comSoccer:
						// �ړI�̏ꏊ���X�^�W�A���ɂ���
						if($island['soccer'] > 0){
							// �X�^�W�A���͓��ɂP�����������Ȃ�
							$this->log->IsFail($id, $name, $comName, '�X�^�W�A��');
							
							$returnMode = 0;
							break;
						}
						$land[$x][$y] = $init->landSoccer;
						$landValue[$x][$y] = 0;
						$this->log->LandSuc($id, $name, $comName, $point);
						break;
						
					case $init->comRail:
						// �ړI�̏ꏊ����H�ɂ���
						if($arg > 8) {
							$arg = 8;
						}
						$land[$x][$y] = $init->landRail;
						$landValue[$x][$y] = $arg;
						$this->log->LandSuc($id, $name, $comName, $point);
						break;
						
					case $init->comStat:
						// �ړI�̏ꏊ���w�ɂ���
						$land[$x][$y] = $init->landStat;
						$landValue[$x][$y] = 0;
						$island['stat']++;
						$this->log->LandSuc($id, $name, $comName, $point);
						break;
						
					case $init->comPark:
						// �ړI�̏ꏊ��V���n�ɂ���
						$land[$x][$y] = $init->landPark;
						if($arg > 4) {
							$arg = 4;
						}
						$landValue[$x][$y] = $arg;
						$island['park']++;
						$this->log->LandSuc($id, $name, $comName, $point);
						break;
						
					case $init->comFusya:
						// �ړI�̏ꏊ�𕗎Ԃɂ���
						$land[$x][$y] = $init->landFusya;
						$landValue[$x][$y] = 0;
						$this->log->LandSuc($id, $name, $comName, $point);
						break;
						
					case $init->comSyoubou:
						// �ړI�̏ꏊ�����h���ɂ���
						$land[$x][$y] = $init->landSyoubou;
						$landValue[$x][$y] = 0;
						$this->log->LandSuc($id, $name, $comName, $point);
						break;
						
					case $init->comFarm:
						// �_��
						if($landKind == $init->landFarm) {
							// ���łɔ_��̏ꍇ
							$landValue[$x][$y] += 2; // �K�� + 2000�l
							if($landValue[$x][$y] > 50) {
								$landValue[$x][$y] = 50; // �ő� 50000�l
							}
						} else {
							// �ړI�̏ꏊ��_���
							$land[$x][$y] = $init->landFarm;
							$landValue[$x][$y] = 10; // �K�� = 10000�l
						}
						$this->log->landSuc($id, $name, $comName, $point);
						
						if((Util::random(100) < 7) && ($island['tenki'] == 1) && ($island['zin'][3] == 1)) {
							if($island['item'][16] != 1) {
								// �_�앨�}�Ӕ���
								$island['item'][16] = 1;
								$this->log->ItemFound($id, $name, $comName, '�_�앨�}��');
							} elseif(($island['item'][16] == 1) && ($island['zin'][5] != 1)) {
								// �W������
								$island['zin'][5] = 1;
								$this->log->Zin6Found($id, $name, $comName, '�W��');
							}
						}
						break;
						
					case $init->comNursery:
						// �{�B��
						if($landKind == $init->landNursery) {
							// ���łɗ{�B��̏ꍇ
							$landValue[$x][$y] += 2; // �K�� + 2000�l
							if($landValue[$x][$y] > 50) {
								$landValue[$x][$y] = 50; // �ő� 50000�l
							}
						} elseif(($landKind == $init->landSea) && ($lv == 1)) {
							// �ړI�̏ꏊ��{�B���
							$land[$x][$y] = $init->landNursery;
							$landValue[$x][$y] = 10; // �K�� = 10000�l
						} else {
							// �s�K���Ȓn�`
							$this->log->landFail($id, $name, $comName, $landName, $point);
							return 0;
						}
						$this->log->landSuc($id, $name, $comName, $point);
						break;
						
					case $init->comFactory:
						// �H��
						if($landKind == $init->landFactory) {
							// ���łɍH��̏ꍇ
							$landValue[$x][$y] += 10; // �K�� + 10000�l
							if($landValue[$x][$y] > 200) {
								$landValue[$x][$y] = 200; // �ő� 200000�l
							}
						} else {
							// �ړI�̏ꏊ���H���
							$land[$x][$y] = $init->landFactory;
							$landValue[$x][$y] = 30; // �K�� = 30000�l
						}
						$this->log->landSuc($id, $name, $comName, $point);
						break;
						
					case $init->comHatuden:
						// ���d��
						if($landKind == $init->landHatuden) {
							// ���łɔ��d���̏ꍇ
							$landValue[$x][$y] += 40; // �K�� + 40000kw
							if($landValue[$x][$y] > 250) {
								$landValue[$x][$y] = 250; // �ő� 250000kw
							}
						} else {
							// �ړI�̏ꏊ�𔭓d����
							$land[$x][$y] = $init->landHatuden;
							$landValue[$x][$y] = 40; // �K�� = 40000kw
						}
						$this->log->landSuc($id, $name, $comName, $point);
						
						if(Util::random(100) < 7) {
							if(($island['tenki'] == 1) && ($island['item'][13] != 1)) {
								// ���w������
								$this->log->ItemFound($id, $name, $comName, '������ȏ���');
								$island['item'][13] = 1;
							} elseif(($island['tenki'] == 3) && ($island['item'][14] != 1)) {
								// �Z�p������
								$this->log->ItemFound($id, $name, $comName, '������ȏ���');
								$island['item'][14] = 1;
							} elseif(($island['tenki'] == 4) && ($island['item'][15] == 1) && ($island['zin'][4] != 1)) {
								// ���i����
								$this->log->Zin5Found($id, $name, $comName, '���i');
								$island['zin'][4] = 1;
							}
						}
						break;
						
					case $init->comCommerce:
						// ���ƃr��
						if($landKind == $init->landCommerce) {
							// ���łɏ��ƃr���̏ꍇ
							$landValue[$x][$y] += 20; // �K�� + 20000�l
							if($landValue[$x][$y] > 250) {
								$landValue[$x][$y] = 250; // �ő� 250000�l
							}
						} else {
							// �ړI�̏ꏊ�����ƃr����
							$land[$x][$y] = $init->landCommerce;
							$landValue[$x][$y] = 30; // �K�� = 30000�l
						}
						$this->log->landSuc($id, $name, $comName, $point);
						
						if(Util::random(100) < 7) {
							if($island['item'][17] != 1) {
								// �o�Ϗ�����
								$island['item'][17] = 1;
								$this->log->ItemFound($id, $name, $comName, '������ȏ���');
							} elseif((($landKind == $init->landCommerce) > 0) && ($island['item'][19] == 1) && ($island['zin'][6] != 1)) {
								// �T���}���_�[����
								$island['zin'][6] = 1;
								$this->log->ZinFound($id, $name, $comName, '�T���}���_�[');
							}
						}
						break;
						
					case $init->comSeaResort:
						// �C�̉�
						if (Turn::countAround($land, $x, $y, 19, array($init->landSeaResort))) {
							// ���͂Q�w�b�N�X�ɊC�̉Ƃ�����
							$this->log->LandFail($id, $name, $comName, '�C�̉Ƃ̋߂�', $point);
							
							$returnMode = 0;
							break 2;
						} else {
							// ���͂Q�w�b�N�X�ɊC�̉Ƃ��Ȃ�
							$land[$x][$y] = $init->landSeaResort;
							$landValue[$x][$y] = 0;
							
							$this->log->LandSuc($id, $name, $comName, $point);
						}
						break;
						
					case $init->comMyhome:
						// �����
						if(!($island['home'])) {
							$landValue[$x][$y] = 0;
						}
						$cost = ($landValue[$x][$y] + 1) * $cost;
						if($island['item'][20] < ($landValue[$x][$y] + 1) * 200) {
							// �؍ނ�����Ȃ�
							$this->log->noWood($id, $name, $comName);
							$returnMode = 0;
							break 2;
						}
						
						if($island['money'] < $cost) {
							// �����`�F�b�N
							$island['money'] += $cost; // �ԋ�
							
							$this->log->noMoney($id, $name, $comName);
							
							$returnMode = 0;
							break 2;
						}
						if($landKind == $init->landMyhome) {
							// ���łɎ���̏ꍇ
							$landValue[$x][$y] += 1; // �K�� + 1�l
							if($landValue[$x][$y] >= 11) {
								$returnMode = 0;
								break;
							}
							$this->log->landSuc($id, $name, '���t�H�[��', $point);
						} else {
							// �ړI�̏ꏊ���}�C�z�[����
							if($island['home'] > 0) {
								// ���łɃ}�C�z�[��������
								$this->log->IsFail($id, $name, $comName, '�}�C�z�[��');
								
								$returnMode = 0;
								break 2;
							}
							$land[$x][$y] = $init->landMyhome;
							$landValue[$x][$y] = 1; // �K�� = 1�l
							
							$this->log->landSuc($id, $name, $comName, $point);
						}
						// �؍ނ���������
						$island['item'][20] -= $landValue[$x][$y] * 100;
						break;
						
					case $init->comSoukoM:
						$flagm = 1;
					case $init->comSoukoF:
						// �q�Ɍ���
						if($arg == 0) {
							$flags = 1;
							$arg = 1;
						}
						// �Z�L�����e�B�ƒ��~���Z�o
						$sec = (int)($landValue[$x][$y] / 100);
						$tyo = $landValue[$x][$y] % 100;
						if($tyo == 99 && $flags != 1) {
							$str = "�q�ɂ���t������";
							$cost = 0;
							$this->log->SoukoMax($id, $name, $comName, $point, $str);
							return 0;
							break;
						} elseif($sec == 10 && $flags == 1) {
							$str = "�q�ɂ̃Z�L�����e�B���x�����ő�l�ɒB���Ă���";
							$cost = 0;
							$this->log->SoukoMax($id, $name, $comName, $point, $str);
							return 0;
							break;
						}
						if($flagm == 1) {
							$arg = min($arg, (int)($island['money'] / $cost));
							$ryo = $cost * $arg;
							$cost = $ryo;
							$str = "({$ryo}{$init->unitMoney})";
						} else {
							$arg = min($arg, (int)($island['food'] / -$cost));
							$ryo = -$cost * $arg;
							$island['food'] -= $ryo;
							$cost = 0;
							$str = "({$ryo}{$init->unitFood})";
						}
						if($landKind == $init->landSoukoM || $landKind == $init->landSoukoF) {
							// ���łɑq�ɂ̏ꍇ
							if($flags == 1) {
								$arg = 0;
								$sec += 1;
								if($sec > 10) {
									$sec = 10;
								}
								$str ="(�Z�L�����e�B����)";
							} else {
								$tyo += $arg;
								if($tyo > 99) {
									$tyo = 99;
								}
							}
						} else {
							// �ړI�̏ꏊ��q�ɂ�
							if($flagm == 1) {
								$land[$x][$y] = $init->landSoukoM;
							} else {
								$land[$x][$y] = $init->landSoukoF;
							}
							
							if($flags == 1) {
								$arg = 0;
								$sec = 1;
								$str ="(�Z�L�����e�B����)";
							}
							$tyo = $arg;
						}
						$landValue[$x][$y] = $sec * 100 + $tyo;
						$this->log->Souko($id, $name, $comName, $point, $str);
						break;
						
					case $init->comHikidasi:
						// �q�Ɉ����o��
						if($arg == 0) {
							$arg = 1;
						}
						if($landKind == $init->landSoukoM) {
							$flagm = 1;
						} else {
							$flagm = 0;
						}
						// �Z�L�����e�B�ƒ��~���Z�o
						$sec = (int)($landValue[$x][$y] / 100);
						$tyo = $landValue[$x][$y] % 100;
						if($arg > $tyo) {
							$arg = $tyo;
						}
						if($flagm == 1) {
							$arg = min($arg, (int)($island['money'] / $cost));
							$cost *= $arg;
							$ryo = 1000 * $arg;
							$island['money'] += $ryo;
							$str = "({$ryo}{$init->unitMoney})";
						} else {
							$arg = min($arg, (int)($island['food'] / $cost));
							$ryo = 1000 * $arg;
							$island['food'] += $ryo - $cost * $arg;
							$cost = 0;
							$str = "({$ryo}{$init->unitFood})";
						}
						$tyo -= $arg;
						if($tyo < 0) {
							$tyo = 0;
						}
						$landValue[$x][$y] = $sec * 100 + $tyo;
						$this->log->Souko($id, $name, $comName, $point, $str);
						$returnMode = 0;
						break;
						
					case $init->comDbase:
						// �h�q�{��
						if($landKind == $init->landDefence) {
							// ���łɖh�q�{�݂̏ꍇ
							$landValue[$x][$y] = 0; // �������u�Z�b�g
							$this->log->bombSet($id, $name, $landName, $point);
						} else {
							// �ړI�̏ꏊ��h�q�{�݂�
							$land[$x][$y] = $init->landDefence;
							if ($arg == 0) {
								$arg = 1;
							} elseif ($arg > $init->dBaseHP) {
								$arg = $init->dBaseHP;
							}
							$value = min($arg * ($cost), $island['money']);
							$p = floor($value / $cost);
							$cost = $value;
							$landValue[$x][$y] = $p;
							$this->log->landSuc($id, $name, $comName, $point);
						}
						if((Util::random(100) < 7) && ($island['item'][7] != 1)) {
							// �Z�p������
							$island['item'][7] = 1;
							$this->log->ItemFound($id, $name, $comName, '������ȏ���');
						}
						$returnMode = 1;
						break;
						
					case $init->comMonument:
						// �L�O��
						if($landKind == $init->landMonument) {
							// ���łɋL�O��̏ꍇ
							// �^�[�Q�b�g�擾
							$tn = $hako->idToNumber[$target];
							if($tn !== 0 && empty($tn)) {
								// �^�[�Q�b�g�����łɂȂ�
								// �������킸�ɒ��~
								$returnMode = 0;
								break 2;
							}
							if($hako->islands[$tn]['keep']) {
								// �ڕW�̓����Ǘ��l�a���蒆�̂��ߎ��s��������Ȃ�
								$this->log->CheckKP($id, $name, $comName);
								$returnMode = 0;
								break 2;
							}
							if((($hako->islandTurn - $island['starturn']) < $init->noMissile) || (($hako->islandTurn - $hako->islands[$tn]['starturn']) < $init->noMissile)) {
								// ���s���^�[�����o�߂������H
								$this->log->Forbidden($id, $name, $comName);
								$returnMode = 0;
								break 2;
							}
							$hako->islands[$tn]['bigmissile']++;
							
							// ���̏ꏊ�͍r�n��
							$land[$x][$y] = $init->landWaste;
							$landValue[$x][$y] = 0;
							$this->log->monFly($id, $name, $landName, $point);
						} else {
							// �ړI�̏ꏊ���L�O���
							$land[$x][$y] = $init->landMonument;
							if($arg >= $init->monumentNumber) {
								$arg = 0;
							}
							$landValue[$x][$y] = $arg;
							$this->log->landSuc($id, $name, $comName, $point);
						}
						break;
				}
				// ������������
				$island['money'] -= $cost;
				
				// �񐔕t���Ȃ�A�R�}���h��߂�
				if (($kind == $init->comFarm) ||
					($kind == $init->comSfarm) ||
					($kind == $init->comNursery) ||
					($kind == $init->comFactory) ||
					($kind == $init->comHatuden) ||
					($kind == $init->comCommerce)) {
					if($arg > 1) {
						$arg--;
						Util::slideBack($comArray, 0);
						$comArray[0] = array (
							'kind'   => $kind,
							'target' => $target,
							'x'      => $x,
							'y'      => $y,
							'arg'    => $arg
						);
					}
				}
				$returnMode = 1;
				break;
				
				// �����܂Œn�㌚�݌n
				
			case $init->comMountain:
				// �̌@��
				if($landKind != $init->landMountain) {
					// �R�ȊO�ɂ͍��Ȃ�
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				$landValue[$x][$y] += 5; // �K�� + 5000�l
				if($landValue[$x][$y] > 200) {
					$landValue[$x][$y] = 200; // �ő� 200000�l
				}
				$this->log->landSuc($id, $name, $comName, $point);
				if((Util::random(100) < 7) && ($island['tenki'] == 3) &&
					($island['item'][2] == 1) && ($island['item'][3] != 1)) {
					// �}�X�N����
					$island['item'][3] = 1;
					$this->log->ItemFound($id, $name, $comName, '�s�C���ȃ}�X�N');
				}
				// ������������
				$island['money'] -= $cost;
				if($arg > 1) {
					$arg--;
					Util::slideBack(&$comArray, 0);
					$comArray[0] = array (
						'kind'   => $kind,
						'target' => $target,
						'x'      => $x,
						'y'      => $y,
						'arg'    => $arg,
					);
				}
				$returnMode = 1;
				break;
				
			case $init->comSfarm:
				// �C��_��
				if($landKind == $init->landSfarm) {
					// ���łɔ_��̏ꍇ
					$landValue[$x][$y] += 2; // �K�� + 2000�l
					if($landValue[$x][$y] > 30) {
						$landValue[$x][$y] = 30; // �ő� 30000�l
					}
				} elseif(($landKind != $init->landSea) || ($lv != 0)) {
					// �C�ȊO�ɂ͍��Ȃ�
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				} else {
					// �ړI�̏ꏊ��_���
					$land[$x][$y] = $init->landSfarm;
					$landValue[$x][$y] = 10; // �K�� = 10000�l
				}
				$this->log->landSuc($id, $name, $comName, $point);
				
				// ������������
				$island['money'] -= $cost;
				if($arg > 1) {
					$arg--;
					Util::slideBack(&$comArray, 0);
					$comArray[0] = array (
						'kind'   => $kind,
						'target' => $target,
						'x'      => $x,
						'y'      => $y,
						'arg'    => $arg,
					);
				}
				$returnMode = 1;
				break;
				
			case $init->comSeaCity:
				//�C��s�s
				if(($landKind != $init->landSea) || ($lv != 0)) {
					// �C�ȊO�ɂ͍��Ȃ�
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				$cntL = Turn::countAround($land, $x, $y, 7, array($init->landSea));
				$cntS = Turn::countAroundValue($island, $x, $y, $init->landSea, 1, 7);
				
				if($cntL == 0 && $cntS == 0) {
					// ���n�A�󐣂̂ǂ�������͂ɂȂ�
					if($cntL == 0) {
						// ���n���Ȃ��̂Œ��~
						$this->log->NoLandAround($id, $name, $comName, $point);
					} else {
						// �󐣂��Ȃ��̂Œ��~
						$this->log->NoShoalAround($id, $name, $comName, $point);
					}
					$returnMode = 0;
					break;
				}
				if ($arg == 77) {
					// �C��s�s�ɂ���
					$land[$x][$y] = $init->landFroCity;
					$landValue[$x][$y] = 1; // �����l��
				} else {
					$land[$x][$y] = $init->landSeaCity;
					$landValue[$x][$y] = 5; // �����l��
				}
				$this->log->landSuc($id, $name, $comName, $point);
				
				// ������������
				$island['money'] -= $cost;
				
				$returnMode = 1;
				break;
				
			case $init->comSdbase:
				// �C��h�q�{��
				if(($landKind != $init->landSea) || ($lv != 0)){
					// �C�ȊO�ɂ͍��Ȃ�
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				// �ړI�̏ꏊ��h�q�{�݂�
				$land[$x][$y] = $init->landSdefence;
				
				if ($arg == 0) {
					$arg = 1;
				} elseif ($arg > $init->sdBaseHP) {
					$arg = $init->sdBaseHP;
				}
				$value = min($arg * ($cost), $island['money']);
				$p = round($value / $cost);
				$landValue[$x][$y] = $p;
				$this->log->landSuc($id, $name, $comName, $point);
				
				if((Util::random(100) < 7) && ($island['item'][7] != 1)) {
					// �Z�p������
					$island['item'][7] = 1;
					$this->log->ItemFound($id, $name, $comName, '������ȏ���');
				}
				// ������������
				$island['money'] -= $value;
				
				$returnMode = 1;
				break;
				
			case $init->comSbase:
				// �C���n
				if(($landKind != $init->landSea) || ($lv != 0)){
					// �C�ȊO�ɂ͍��Ȃ�
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				$land[$x][$y] = $init->landSbase;
				$landValue[$x][$y] = 0; // �o���l0
				$this->log->landSuc($id, $name, $comName, '(?, ?)');
				
				if((Util::random(100) < 7) && ($island['item'][6] != 1)) {
					// �Ȋw������
					$island['item'][6] = 1;
					$this->log->ItemFound($id, $name, $comName, '������ȏ���');
				}
				// ������������
				$island['money'] -= $cost;
				
				$returnMode = 1;
				break;
				
			case $init->comSsyoubou:
				// �ړI�̏ꏊ���C����h���ɂ���
				if(($landKind != $init->landSea) || ($lv != 0)){
					// �C�ȊO�ɂ͍��Ȃ�
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				$land[$x][$y] = $init->landSsyoubou;
				$landValue[$x][$y] = 0;
				$this->log->LandSuc($id, $name, $comName, $point);
				
				// ������������
				$island['money'] -= $cost;
				
				$returnMode = 1;
				break;
				
			case $init->comProcity:
				// �h�Гs�s
				if(($landKind != $init->landTown) || ($lv != 100)){
					// ���ȊO�ɂ͍��Ȃ�
					$this->log->landFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				$land[$x][$y] = $init->landProcity;
				$landValue[$x][$y] = 100; // �o���l0
				$this->log->landSuc($id, $name, $comName, $point);
				
				// ������������
				$island['money'] -= $cost;
				
				$returnMode = 1;
				break;
				
			case $init->comBoku;
				// �l�̈��z��
				if($landKind != $init->landProcity) {
					$this->log->BokuFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				$townCount = Turn::countAround($land, $x, $y, 19, array($init->landTown));
				
				if($townCount == 0) {
					$this->log->noTownAround($id, $name, $comName, $point);
					$returnMode = 0;
					break;
				}
				$landValue[$x][$y] += 10; // �K�� + 1000�l
				if($landValue[$x][$y] > 250) {
					$landValue[$x][$y] = 250; // �ő� 25000�l
				}
				for($i = 1; $i < 19; $i++) {
					$sx = $x + $init->ax[$i];
					$sy = $y + $init->ay[$i];
					if($land[$sx][$sy] == $init->landTown){
						$landValue[$sx][$sy] -= round(20/$townCount);
						if($landValue[$sx][$sy] <= 0) {
							// ���n�ɖ߂�
							$land[$sx][$sy] = $init->landPlains;
							$landValue[$sx][$sy] = 0;
							
							continue;
						}
					}
				}
				$this->log->landSuc($id, $name, $comName, $point);
				
				// ������������
				$island['money'] -= $cost;
				if($arg > 1) {
					$arg--;
					Util::slideBack(&$comArray, 0);
					$comArray[0] = array (
						'kind'   => $kind,
						'target' => $target,
						'x'      => $x,
						'y'      => $y,
						'arg'    => $arg,
					);
				}
				$returnMode = 1;
				break;
				
			case $init->comBigtown:
				// ���㉻
				if(!(($landKind == $init->landNewtown) && ($lv >= 150))){
					// �j���[�^�E���ȊO�ɂ͍��Ȃ�
					$this->log->JoFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				$townCount = Turn::countAround($land, $x, $y, 19, array($init->landTown, $init->landNewtown, $init->landBigtown));
				
				if($townCount < 16) {
					// �S���C�����疄�ߗ��ĕs�\
					$this->log->JoFail($id, $name, $comName, $landName, $point);
					$returnMode = 0;
					break;
				}
				$land[$x][$y] = $init->landBigtown;
				$this->log->landSuc($id, $name, $comName, $point);
				
				// ������������
				$island['money'] -= $cost;
				
				$returnMode = 1;
				break;
				
			case $init->comEisei;
				// �l�H�q���ł��グ
				if($arg > 5) {
					$arg = 0;
				}
				$value = ($arg + 1) * $cost;
				// �C��, �ϑ�, �}��, �R��, �h�q, �C��
				$rocket = array(1, 1, 2, 2, 3, 4);
				$tech   = array(10, 40, 100, 250, 300, 500);
				$failp  = array(700, 500, 600, 400, 200, 3000);
				$failq  = array(100, 100, 10, 10, 10, 1);
				
				if($island['m23'] < $rocket[$arg]) {
					// ���P�b�g��$rocket�ȏ�Ȃ��ƃ_��
					$this->log->NoAny($id, $name, $comName, "���P�b�g������Ȃ�");
					$returnMode = 0;
					break;
				} elseif($island['rena'] < $tech[$arg]) {
					// �R���Z�pLv$tech�ȏ�Ȃ��ƃ_��
					$this->log->NoAny($id, $name, $comName, "�R���Z�p������Ȃ�");
					$returnMode = 0;
					break;
				} elseif($island['money'] < $value) {
					$this->log->NoAny($id, $name, $comName, "�����s����");
					$returnMode = 0;
					break;
				} elseif(Util::random(10000) > $failp[$arg] + $failq[$arg] * $island['rena']) {
					$this->log->Eiseifail($id, $name, $comName, $point);
					
					// ������������
					$island['money'] -= $value;
					
					$returnMode = 1;
					break;
				}
				$island['eisei'][$arg] = ($arg == 5) ? 250 : 100;
				$this->log->Eiseisuc($id, $name, $init->EiseiName[$arg], "�̑ł��グ");
				
				// ������������
				$island['money'] -= $value;
				
				$returnMode = 1;
				break;
				
			case $init->comEiseimente;
				// �l�H�q���ŏC��
				if($arg > 5) {
					$arg = 0;
				}
				if($island['eisei'][$arg] > 0) {
					$island['eisei'][$arg] = 150;
					$this->log->Eiseisuc($id, $name, $init->EiseiName[$arg], "�̏C��");
				} else {
					$this->log->NoAny($id, $name, $comName, "�w��̐l�H�q�����Ȃ�");
					$returnMode = 0;
					break;
				}
				// ������������
				$island['money'] -= $cost;
				
				$returnMode = 1;
				break;
				
			case $init->comEiseiAtt;
				// �q���j��C
				if($island['enehusoku'] < 0) {
					// �d�͕s��
					$this->log->Enehusoku($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				
				// �^�[�Q�b�g�擾
				$tn = $hako->idToNumber[$target];
				if($tn !== 0 && empty($tn)) {
					// �^�[�Q�b�g�����łɂȂ�
					$this->log->msNoTarget($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if($hako->islands[$tn]['keep']) {
					// �ڕW�̓����Ǘ��l�a���蒆�̂��ߎ��s��������Ȃ�
					$this->log->CheckKP($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				// ���O����
				if($arg > 5) {
					$arg = 0;
				}
				$tIsland = &$hako->islands[$tn];
				$tName = &$tIsland['name'];
				
				if($island['eisei'][5] > 0 || $island['eisei'][3] > 0) {
					// �C���M�����[���R���q��������ꍇ
					$eName = $init->EiseiName[$arg];
					$p = ($island['eisei'][5] >= 1) ? 110 : 70;
					if($tIsland['eisei'][$arg] > 0) {
						if(Util::random(100) < $p - 10 * $arg) {
							$tIsland['eisei'][$arg] = 0;
							$this->log->EiseiAtts($id, $tId, $name, $tName, $comName, $eName);
						} else {
							$this->log->EiseiAttf($id, $tId, $name, $tName, $comName, $eName);
						}
					} else {
						$this->log->NoAny($id, $name, $comName, "�w��̐l�H�q�����Ȃ�");
						$returnMode = 0;
						break;
					}
					$nkind = ($island['eisei'][5] >= 1) ? '5' : '3';
					$island['eisei'][$nkind] -= 30;
					
					if($island['eisei'][$nkind] < 1) {
						$island['eisei'][$nkind] = 0;
						$this->log->EiseiEnd($id, $name, ($island['eisei'][5] >= 1) ? $init->EiseiName[5] : $init->EiseiName[3]);
					}
				} else {
					// �C���M�����[���R���q�����Ȃ��ꍇ
					$this->log->NoAny($id, $name, $comName, "�K�v�Ȑl�H�q�����Ȃ�");
					$returnMode = 0;
					break;
				}
				// ������������
				$island['money'] -= $cost;
				
				$returnMode = 1;
				break;
				
			case $init->comEiseiLzr:
				// �q�����[�U�[
				if($island['enehusoku'] < 0) {
					// �d�͕s��
					$this->log->Enehusoku($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				// �^�[�Q�b�g�擾
				$tn = $hako->idToNumber[$target];
				if($tn != 0 && empty($tn)) {
					// �^�[�Q�b�g�����łɂȂ�
					$this->log->msNoTarget($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if($hako->islands[$tn]['keep']) {
					// �ڕW�̓����Ǘ��l�a���蒆�̂��ߎ��s��������Ȃ�
					$this->log->CheckKP($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				// ���O����
				$tIsland    = &$hako->islands[$tn];
				$tName      = &$tIsland['name'];
				$tLand      = &$tIsland['land'];
				$tLandValue = &$tIsland['landValue'];
				
				if((($hako->islandTurn - $island['starturn']) < $init->noMissile) || (($hako->islandTurn - $tIsland['starturn']) < $init->noMissile)) {
					// ���s���^�[�����o�߂������H
					$this->log->Forbidden($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				// ���e�_�̒n�`���Z�o
				$tL     = $tLand[$x][$y];
				$tLv    = $tLandValue[$x][$y];
				$tLname = $this->landName($tL, $tLv);
				$tPoint = "({$x}, {$y})";
				
				if($island['id'] == $tIsland['id']) {
					$tLand[$x][$y] = &$land[$x][$y];
				}
				if($island['eisei'][5] > 0 || $island['eisei'][3] > 0) {
					// �C���M�����[���R���q��������ꍇ
					if((($tL == $init->landSea) && ($tLv < 2)) || ($tL == $init->landSeaCity) || 
						($tL == $init->landSbase) || ($tL == $init->landSdefence) || ($tL == $init->landMountain)) {
						// ���ʂ̂Ȃ��n�`
						$this->log->EiseiLzr($id, $target, $name, $tName, $comName, $tLname, $tPoint, "�g�����Ȃ�܂����B");
					} elseif((($tL == $init->landSea) && ($tLv >= 2)) || ($tL == $init->landOil) || ($tL == $init->landZorasu) || ($tL == $init->landFroCity)) {
						// �D�Ɩ��c�Ƃ��炷�ƊC��s�s�͊C�ɂȂ�
						$this->log->EiseiLzr($id, $target, $name, $tName, $comName, $tLname, $tPoint, "�Ă������܂����B");
						$tLand[$x][$y] = $init->landSea;
						$tLandValue[$x][$y] = 0;
					} elseif(($tL == $init->landNursery) || ($tL == $init->landSeaSide) || ($tL == $init->landPort)) {
						// �{�B��ƍ��l�ƍ`�͐󐣂ɂȂ�
						$this->log->EiseiLzr($id, $target, $name, $tName, $comName, $tLname, $tPoint, "�Ă������܂����B");
						$tLand[$x][$y] = $init->landSea;
						$tLandValue[$x][$y] = 1;
					} else {
						// ���̑��͍r�n��
						$this->log->EiseiLzr($id, $target, $name, $tName, $comName, $tLname, $tPoint, "�Ă������܂����B");
						$tLand[$x][$y] = $init->landWaste;
						$tLandValue[$x][$y] = 1;
					}
					$eName = $init->EiseiName[$arg];
					$p = ($island['eisei'][5] >= 1) ? 110 : 70;
					$nkind = ($island['eisei'][5] >= 1) ? '5' : '3';
					$island['eisei'][$nkind] -= (($island['eisei'][5] >= 1) ? 5 : 10);
				} else {
					// �C���M�����[���R���q�����Ȃ��ꍇ
					$this->log->NoAny($id, $name, $comName, "�K�v�Ȑl�H�q�����Ȃ�");
					$returnMode = 0;
					break;
				}
				// ������������
				$island['money'] -= $cost;
				
				$returnMode = 1;
				break;
				
			case $init->comMissileNM:
			case $init->comMissilePP:
			case $init->comMissileST:
			case $init->comMissileBT:
			case $init->comMissileSP:
			case $init->comMissileLD:
			case $init->comMissileLU:
				// �~�T�C���n
				if((($island['tenki'] == 4) || ($island['tenki'] == 5)) && ($island['zin'][1] != 1)){
					// ���E��̓��͑łĂȂ�
					$this->log->msNoTenki($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if($island['enehusoku'] < 0) {
					// �d�͕s��
					$this->log->Enehusoku($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				$flag = 0;
				do {
					if(($arg == 0) || ($arg > $island['fire'])) {
						// 0�̏ꍇ�͌��Ă邾��
						$arg = $island['fire'];
					}
					$comp = $arg;
					// �^�[�Q�b�g�擾
					$tn = $hako->idToNumber[$target];
					if($tn !== 0 && empty($tn)) {
						// �^�[�Q�b�g�����łɂȂ�
						$this->log->msNoTarget($id, $name, $comName);
						$returnMode = 0;
						break 2;
					}
					if($hako->islands[$tn]['keep']) {
						// �ڕW�̓����Ǘ��l�a���蒆�̂��ߎ��s��������Ȃ�
						$this->log->CheckKP($id, $name, $comName);
						$returnMode = 0;
						break 2;
					}
					// ���O����
					$tIsland    = &$hako->islands[$tn];
					$tName      = &$tIsland['name'];
					$tLand      = &$tIsland['land'];
					$tLandValue = &$tIsland['landValue'];
					
					if((($hako->islandTurn - $island['starturn']) < $init->noMissile) || (($hako->islandTurn - $tIsland['starturn']) < $init->noMissile)) {
						// ���s���^�[�����o�߂������H
						$this->log->Forbidden($id, $name, $comName);
						$returnMode = 0;
						break 2;
					}
					// ��̐�
					$boat = 0;
					
					// �~�T�C���̓���
					$missiles = 0; // ���ː�
					$missileA = 0; // �͈͊O�A���ʂȂ��A�r�n
					$missileB = 0; // �󒆔��j
					$missileC = 0; // �d�����A�}��
					$missileD = 0; // ���b����
					$missileE = 0; // ��͌}��
					
					// �덷
					if(($kind == $init->comMissilePP) || ($kind == $init->comMissileBT) || ($kind == $init->comMissileSP)) {
						$err = 7;
					} else {
						$err = 19;
					}
					$bx = $by = 0;
					// �����s���邩�w�萔�ɑ���邩��n�S�������܂Ń��[�v
					while(($arg > 0) && ($island['money'] >= $cost)) {
						// ��n��������܂Ń��[�v
						while($count < $init->pointNumber) {
							$bx = $this->rpx[$count];
							$by = $this->rpy[$count];
							if(($land[$bx][$by] == $init->landBase) || ($land[$bx][$by] == $init->landSbase)) {
								break;
							}
							$count++;
						}
						if($count >= $init->pointNumber) {
							// ������Ȃ������炻���܂�
							break;
						}
						// �Œ���n���������̂ŁAflag�𗧂Ă�
						$flag = 1;
						
						// ��n�̃��x�����Z�o
						$level = Util::expToLevel($land[$bx][$by], $landValue[$bx][$by]);
						
						// ��n���Ń��[�v
						while(($level > 0) && ($arg > 0) && ($island['money'] > $cost)) {
							// �������̂��m��Ȃ̂ŁA�e�l�����Ղ�����
							$level--;
							$arg--;
							$island['money'] -= $cost;
							$missiles++;
							
							// ���e�_�Z�o
							$r = Util::random($err);
							$tx = $x + $init->ax[$r];
							$ty = $y + $init->ay[$r];
							if((($ty % 2) == 0) && (($y % 2) == 1)) {
								$tx--;
							}
							// ���e�_�͈͓��O�`�F�b�N
							if(($tx < 0) || ($tx >= $init->islandSize) || ($ty < 0) || ($ty >= $init->islandSize)) {
								// �͈͊O
								$missileA++;
								continue;
							}
							// ���e�_�̒n�`���Z�o
							$tL     = $tLand[$tx][$ty];
							$tLv    = $tLandValue[$tx][$ty];
							$tLname = $this->landName($tL, $tLv);
							$tPoint = "({$tx}, {$ty})";
							
							// �h�q�{�ݔ���
							$defence = 0;
							if($defenceHex[$id][$tx][$ty] == 1) {
								$defence = 1;
							} elseif($defenceHex[$id][$tx][$ty] == -1) {
								$defence = 0;
							} else {
								if(($tL == $init->landDefence) || ($tL == $init->landSdefence) || ($tL == $init->landProcity)) {
									// �h�q�{�݂ɖ���
									if(($tLv > 1) &&
										(($kind == $init->comMissileNM) ||
										($kind == $init->comMissilePP) ||
										($kind == $init->comMissileST))) {
										// �h�q�{�݂̑ϋv�͂�������
										$tLv --;
									} elseif($kind == $init->comMissileSP) {
										break;
									} else {
										// �ϋv�͂��P���A���̃~�T�C�������Ȃ�A�h�q�{�ݔj��
										$tLv = 0;
										// �t���O���N���A
										for($i = 0; $i < 19; $i++) {
											$sx = $tx + $init->ax[$i];
											$sy = $ty + $init->ay[$i];
											// �s�ɂ��ʒu����
											if((($sy % 2) == 0) && (($ty % 2) == 1)) {
												$sx--;
											}
											if(($sx < 0) || ($sx >= $init->islandSize) || ($sy < 0) || ($sy >= $init->islandSize)) {
												// �͈͊O�̏ꍇ�������Ȃ�
											} else {
												// �͈͓��̏ꍇ
												$defenceHex[$id][$sx][$sy] = 0;
											}
										}
									}
								} elseif(Turn::countAround($tLand, $tx, $ty, 19, array($init->landDefence, $init->landSdefence)) + 
									Turn::countAround($tLand, $tx, $ty, 7, array($init->landProcity))) {
									$defenceHex[$id][$tx][$ty] = 1;
									$defence = 1;
								} else {
									$defenceHex[$id][$tx][$ty] = -1;
									$defence = 0;
								}
							}
							if($defence == 1) {
								// �󒆔��j
								$missileB++;
								continue;
							}
							if($island['id'] != $tIsland['id']) {
								// �h�q�q��������ꍇ
								if($tIsland['eisei'][4] && (Util::random(5000) < $tIsland['rena'])) {
									$tIsland['eisei'][4] -= 2;
									if($tIsland['eisei'][4] < 1) {
										$tIsland['eisei'][4] = 0;
										$this->log->EiseiEnd($target, $tName, $init->EiseiName[4]);
									}
									$missileB++;
									continue;
								}
							}
							// �u���ʂȂ��vhex���ŏ��ɔ���
							if (($kind != $init->comMissileLU) && // �n�`���N�e�łȂ�
								((($tL == $init->landSea) && ($tLv == 0))|| // �[���C
								(((($tL == $init->landSea) && ($tLv < 2)) || // �C�܂��́E�E�E
								(($tL == $init->landPoll) && ($kind != $init->comMissileBT)) || // �����y��܂��́E�E�E
								($tL == $init->landSbase) || // �C���n�܂��́E�E�E
								(($tL == $init->landProcity) && 
								(Turn::countAround($tLand, $tx, $ty, 19, array($init->landDefence, $init->landSdefence)))) || // �h�Гs�s�܂��́E�E�E
								($tL == $init->landSeaCity) || // �C��s�s�܂��́E�E�E
								($tL == $init->landMountain)) // �R�ŁE�E�E
								&& ($kind != $init->comMissileLD)))) { // ���j�e�ȊO
								// �C���n�̏ꍇ�A�C�̃t��
								if($tL == $init->landSbase) {
									$tL = $init->landSea;
								}
								$tLname = $this->landName($tL, $tLv);
								$missileA++;
								continue;
							}
							// �e�̎�ނŕ���
							if($kind == $init->comMissileLD) {
								// ���n�j��e
								switch($tL) {
									case $init->landMountain:
										// �R(�r�n�ɂȂ�)
										$this->log->msLDMountain($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										
										// �r�n�ɂȂ�
										$tLand[$tx][$ty] = $init->landWaste;
										$tLandValue[$tx][$ty] = 0;
										continue 2;
										
									case $init->landSbase:
									case $init->landSdefence:
									case $init->landSeaCity:
									case $init->landFroCity:
									case $init->landSsyoubou:
									case $init->landSfarm:
										// �C���n�A�C��s�s�A�C��s�s�A�C����h���A�C��h�q�{�݁A�C��_��
										$this->log->msLDSbase($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										break;
										
									case $init->landMonster:
									case $init->landSleeper:
									case $init->landZorasu:
										// ���b
										$this->log->msLDMonster($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										break;
										
									case $init->landSea:
										// ��
										$this->log->msLDSea1($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										break;
										
									case $init->landSeaSide:
										// ���l�Ȃ琅�v
										$this->log->msLDSea1($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										$tLand[$tx][$ty] = $init->landSea;
										$tIsland['area']--;
										$tLandValue[$tx][$ty] = 1;
										break;
										
									default:
										// ���̑�
										$this->log->msLDLand($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
								}
								// �o���l
								if(($tL == $init->landTown) || ($tL == $init->landSeaCity) || ($tL == $init->landFroCity) || 
									($tL == $init->landNewtown) || ($tL == $init->landBigtown)) {
									if(($land[$bx][$by] == $init->landBase) ||
										($land[$bx][$by] == $init->landSbase)) {
										// �܂���n�̏ꍇ�̂�
										$landValue[$bx][$by] += round($tLv / 20);
										if($landValue[$bx][$by] > $init->maxExpPoint) {
											$landValue[$bx][$by] = $init->maxExpPoint;
										}
									}
								}
								// �󐣂ɂȂ�
								$tLand[$tx][$ty] = $init->landSea;
								$tIsland['area']--;
								$tLandValue[$tx][$ty] = 1;
								
								// �ł����c�A�󐣁A�C���n�A�C��s�s�A�C����h���A�C��_��A�C��h�q�{�݂�������C
								if(($tL == $init->landOil) ||
									($tL == $init->landSea) ||
									($tL == $init->landSeaCity) ||
									($tL == $init->landSsyoubou) ||
									($tL == $init->landSfarm) ||
									($tL == $init->landSdefence) ||
									($tL == $init->landSbase) ||
									($tL == $init->landZorasu)) {
									$tLandValue[$tx][$ty] = 0;
								}
								// �ł��{�B�ꂾ�������
								if($tL == $init->landNursery) {
									$tLandValue[$tx][$ty] = 1;
								}
							} elseif($kind == $init->comMissileLU) {
								// �n�`���N�e
								switch($tL) {
									case $init->landMountain:
										// �R
										continue;
										
									case $init->landSbase:
									case $init->landSdefence:
									case $init->landSeaCity:
									case $init->landFroCity:
									case $init->landSsyoubou:
									case $init->landSfarm:
									case $init->landZorasu:
										// �C���n�A�C��s�s�A�C��s�s�A�C����h���A�C��h�q�{�݁A�C��_��A���炷
										$this->log->msLUSbase($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										$tLand[$tx][$ty] = $init->landSea;
										$tLandValue[$tx][$ty] = 1;
										continue;
										
									case $init->landSea:
										// �C�̏ꍇ
										if ($tLv == 1) {
											// �r�n�ɂȂ�
											$this->log->msLUSea1($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
											$tLand[$tx][$ty] = $init->landWaste;
											$tLandValue[$tx][$ty] = 1;
											$tIsland['area']++;
											if($seaCount <= 4) {
												// ����̊C��3�w�b�N�X�ȓ��Ȃ̂ŁA�󐣂ɂ���
												for($i = 1; $i < 7; $i++) {
													$sx = $x + $init->ax[$i];
													$sy = $y + $init->ax[$i];
													// �s�ɂ��ʒu����
													if((($sy % 2) == 0) && (($y % 2) == 1)) {
														$sx--;
													}
													if(!(($sx < 0) || ($sx >= $init->islandSize) || ($sy < 0) || ($sy >= $init->islandSize))) {
														// �͈͓��̏ꍇ
														if($tLand[$sx][$sy] == $init->landSea) {
															$tLandValue[$sx][$sy] = 1;
														}
													}
												}
											}
										} else {
											// �󐣂ɂȂ�
											$this->log->msLUSea0($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
											$tLandValue[$tx][$ty] = 1;
										}
										continue;
										
									case $init->landMonster:
									case $init->landSleeper:
										// ���b
										$missileD++;
										// �R�ɂȂ�
										$tLand[$tx][$ty] = $init->landMountain;
										$tLandValue[$tx][$ty] = 0;
										$this->log->msLUMonster($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										continue;
										
									default:
										// ���̑�
										$this->log->msLULand($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										// �o���l
										if($tL == $init->landTown) {
											if(($land[$bx][$by] == $init->landBase) || ($land[$bx][$by] == $init->landSbase)) {
												// �܂���n�̏ꍇ�̂�
												$landValue[$bx][$by] += round($tLv / 20);
												if($landValue[$bx][$by] > $init->maxExpPoint) {
													$landValue[$bx][$by] = $init->maxExpPoint;
												}
											}
										}
										// �R�ɂȂ�
										$tLand[$tx][$ty] = $init->landMountain;
										$tLandValue[$tx][$ty] = 0;
								}
								continue;
							} elseif($kind != $init->comMissileSP) {
								// ���̑��~�T�C��
								if($tL == $init->landWaste) {
									// �r�n
									if($kind == $init->comMissileBT) {
										$this->log->msPollution($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									}else{
										$missileA++;
									}
								} elseif(($tL == $init->landMonster) || ($tL == $init->landSleeper)) {
									// ���b
									$monsSpec = Util::monsterSpec($tLv);
									$special = $init->monsterSpecial[$monsSpec['kind']];
									
									// �d����?
									if((($special & 0x4) && (($hako->islandTurn % 2) == 1)) || (($special & 0x10) && (($hako->islandTurn % 2) == 0))) {
										// �d����
										if($kind == $init->comMissileST) {
											// �X�e���X
											$this->log->msMonNoDamageS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										} else {
											// �ʏ�e
											$this->log->msMonNoDamage($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										}
										$missileC++;
										continue;
									} else {
										// �d��������Ȃ�
										if(($special & 0x100) && (Util::random(100) < 30)) {
											// �~�T�C���@�����Ƃ�
											if($kind == $init->comMissileST) {
												$this->log->msMonsCaughtS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
											} else {
												$this->log->msMonsCaught($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
											}
											$missileC++;
											continue;
										}
										if(($kind == $init->comMissileBT) && (Util::random(100) < 10)) {
											// �o�C�I�~�T�C���œˑR�ψ�
											$kind = Util::random($init->monsterNumber);
											$lv = $kind * 100 + $init->monsterBHP[$kind] + Util::random($init->monsterDHP[$kind]);
											$tLand[$tx][$ty] = $init->landMonster;
											$tLandValue[$tx][$ty] = $lv;
											$this->log->msMutation($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										}
										if($monsSpec['hp'] == 1) {
											// ���b���Ƃ߂�
											if(($land[$bx][$by] == $init->landBase) || ($land[$bx][$by] == $init->landSbase)) {
												// �o���l
												$landValue[$bx][$by] += $init->monsterExp[$monsSpec['kind']];
												if($landValue[$bx][$by] > $init->maxExpPoint) {
													$landValue[$bx][$by] = $init->maxExpPoint;
												}
											}
											$missileD++;
											
											if((Util::random(100) < 7) && ($island['item'][8] == 1) && ($island['item'][9] != 1)) {
												// �}�X�^�[�\�[�h����
												$island['item'][9] = 1;
												$this->log->SwordFound($id, $name, $tLname);
											}
											// ����
											$value = $init->monsterValue[$monsSpec['kind']];
											if($value > 0) {
												if(($id != $target) && ($target != 1)) {
													$tIsland['money'] += (int)($value / 2);
													$island['money'] += (int)($value / 2);
												} else {
													$tIsland['money'] += $value;
												}
											}
											if($kind == $init->comMissileST) {
												// �X�e���X
												$this->log->msMonMoneyS($id, $target, $tLname, $value);
												$this->log->msMonsKillS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
											} else {
												// �ʏ�
												$this->log->msMonMoney($id, $target, $tLname, $value);
												$this->log->msMonsKill($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
											}
											// ���b�ގ���
											$island['taiji']++;
											
											// �܊֌W
											// $prize = $island['prize'];
											list($flags, $monsters, $turns) = split(",", $prize, 3);
											$v = 1 << $monsSpec['kind'];
											$monsters |= $v;
											
											if ((!($flags & 512)) && $island['taiji'] == 100) {
												// 100�C�ގ��őf�l������
												$flags |= 512;
												$this->log->prize($id, $name, $init->prizeName[10]);
											} elseif ((!($flags & 1024)) && $island['taiji'] == 300) {
												// 300�C�ގ��œ�����
												$flags |= 1024;
												$this->log->prize($id, $name, $init->prizeName[11]);
											} elseif ((!($flags & 2048)) && $island['taiji'] == 500) {
												// 500�C�ގ��Œ�������
												$flags |= 2048;
												$this->log->prize($id, $name, $init->prizeName[12]);
											} elseif ((!($flags & 4096)) && $island['taiji'] == 700) {
												// 700�C�ގ��ŋ��ɓ�����
												$flags |= 4096;
												$this->log->prize($id, $name, $init->prizeName[13]);
											} elseif ((!($flags & 8192)) && $island['taiji'] == 1000) {
												// 1000�C�ގ��œ�����
												$flags |= 8192;
												$this->log->prize($id, $name, $init->prizeName[14]);
											}
											$prize = "{$flags},{$monsters},{$turns}";
											// $island['prize'] = "{$flags},{$monsters},{$turns}";
											
											// �r��n�ɂȂ�
											$tLand[$tx][$ty] = $init->landWaste;
											$tLandValue[$tx][$ty] = 1; // ���e�_
											continue;
										} else {
											// ���b�����Ă�
											if($kind == $init->comMissileST) {
												// �X�e���X
												$this->log->msMonsterS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
											} else {
												// �ʏ�
												$this->log->msMonster($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
											}
											// HP��1����
											$tLandValue[$tx][$ty]--;
											$missileD++;
											continue;
										}
									}
								} elseif($tL == $init->landShip) {
									// �D��
									$ship = Util::navyUnpack($tLv);
									if(($ship[1] == 3) && (Util::random(1000) < $init->shipIntercept)) {
										// ��̓~�T�C���}��
										$missileE++;
										continue;
									}
									if(($ship[1] == 2 || $ship[1] == 3) && ($ship[2] > 20)) {
										// �C��T���D�܂��͐�͂̏ꍇ
										$tLname = $init->shipName[$ship[1]];
										$tLname .= "�i{$this->islands[$ship[0]]['name']}�������j";
										if($kind == $init->comMissileST) {
											// �X�e���X
											$this->log->msGensyoS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										} else {
											// �ʏ�
											$this->log->msGensyo($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										}
										$ship[2] -= 2;
										$tLandValue[$tx][$ty] = Util::navyPack($ship[0], $ship[1], $ship[2], $ship[3], $ship[4]);
									} else {
										$tLand[$tx][$ty] = $init->landSea;
										$tLandValue[$tx][$ty] = 0;
									}
								} elseif(($tL == $init->landDefence || $tL == $init->landSdefence) && ($tLv > 1)) {
									// �h�q�{�݁i�K�͌����j
									if($kind == $init->comMissileST) {
										// �X�e���X
										$this->log->msGensyoS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									} else {
										// �ʏ�
										$this->log->msGensyo($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									}
									$tLandValue[$tx][$ty] = $tLv;
								} elseif((($tL == $init->landFarm) && ($tLv > 25)) || (($tL == $init->landSfarm) && ($tLv > 20))) {
									// �_��A�C��_��i�K�͌����j
									if($kind == $init->comMissileST) {
										// �X�e���X
										$this->log->msGensyoS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									} else {
										// �ʏ�
										$this->log->msGensyo($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									}
									$tLandValue[$tx][$ty] -= 5;
								} elseif((($tL == $init->landFactory) && ($tLv > 100)) ||
									(($tL == $init->landHatuden) && ($tLv > 500)) ||
									(($tL == $init->landCommerce) && ($tLv > 150)) ||
									(($tL == $init->landProcity) && ($tLv >= 160))) {
									// �H��A���d���A���ƃr���A�h�Гs�s�i�K�͌����j
									if($kind == $init->comMissileST) {
										// �X�e���X
										$this->log->msGensyoS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									} else {
										// �ʏ�
										$this->log->msGensyo($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									}
									$tLandValue[$tx][$ty] -= 20;
								} elseif(($tL == $init->landNursery) || ($tL == $init->landSeaSide)) {
									// �{�B��A���l���������
									if($kind == $init->comMissileST) {
										// �X�e���X
										$this->log->msNormalS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									} else {
										// �ʏ�
										$this->log->msNormal($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									}
									$tLand[$tx][$ty] = $init->landSea;
									$tLandValue[$tx][$ty] = 1;
								} elseif(($tL == $init->landShip) || ($tL == $init->landFroCity) ||
									($tL == $init->landOil) || ($tL == $init->landSdefence) ||
									($tL == $init->landSsyoubou) || ($tL == $init->landSfarm) || ($tL == $init->landZorasu)) {
									// �D�A�C��s�s�A���c�A�C��h�q�{�݁A�C����h���A�C��_�ꂾ������C
									if($kind == $init->comMissileST) {
										// �X�e���X
										$this->log->msNormalS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									} else {
										// �ʏ�
										$this->log->msNormal($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									}
									$tLand[$tx][$ty] = $init->landSea;
									$tLandValue[$tx][$ty] = 0;
								} else {
									// �ʏ�n�`
									if($kind == $init->comMissileST) {
										// �X�e���X
										$this->log->msNormalS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										// �r�n�ɂȂ�
										$tLand[$tx][$ty] = $init->landWaste;
										$tLandValue[$tx][$ty] = 1; // ���e�_
									} elseif($kind == $init->comMissileBT) {
										// �o�C�I�~�T�C���̎��͉���
										if(($tL == $init->landPoll) && ($tLandValue[$tx][$ty] < 3)) {
											$tLandValue[$tx][$ty]++;
										} elseif($tL != $init->landPoll) {
											// �����y��ɂȂ�
											$tLand[$tx][$ty] = $init->landPoll;
											$tLandValue[$tx][$ty] = Util::random(2) + 1;
										}
										$this->log->msPollution($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
									} else {
										// �ʏ�
										$this->log->msNormal($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
										// �r�n�ɂȂ�
										$tLand[$tx][$ty] = $init->landWaste;
										$tLandValue[$tx][$ty] = 1; // ���e�_
									}
								}
								// �o���l
								if(($tL == $init->landTown) || ($tL == $init->landSeaCity) || ($tL == $init->landFroCity) ||
									($tL == $init->landNewtown) || ($tL == $init->landBigtown)) {
									if(($land[$bx][$by] == $init->landBase) || ($land[$bx][$by] == $init->landSbase)) {
										$landValue[$bx][$by] += round($tLv / 20);
										$boat += $tLv; // �ʏ�~�T�C���Ȃ̂œ�Ƀv���X
										if($landValue[$bx][$by] > $init->maxExpPoint) {
											$landValue[$bx][$by] = $init->maxExpPoint;
										}
									}
								}
							} else {
								if(($tL == $init->landMonster) && (Util::random(100) < 20)) {
									// �ߊl�ɐ���
									$tLand[$tx][$ty] = $init->landSleeper;
									
									$this->log->MsSleeper($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
								} else {
									$missileA++;
								}
							}
						}
						// �J�E���g���₵�Ƃ�
						$count++;
					}
					// �~�T�C�����O
					if($missiles > 0){
						if($kind == $init->comMissileST) {
							// �X�e���X
							$this->log->mslogS($id, $target, $name, $tName, $comName, $point, $missiles, $missileA, $missileB, $missileC, $missileD, $missileE);
						} else {
							// �ʏ�
							$this->log->mslog($id, $target, $name, $tName, $comName, $point, $missiles, $missileA, $missileB, $missileC, $missileD, $missileE);
						}
					}
					if($flag == 0) {
						// ��n��������������ꍇ
						$this->log->msNoBase($id, $name, $comName);
						$returnMode = 0;
						break;
					}
					$tIsland['land'] = $tLand;
					$tIsland['landValue'] = $tLandValue;
					unset($hako->islands[$tn]);
					$hako->islands[$tn] = $tIsland;
					
					// �����
					$boat = round($boat / 2);
					if(($boat > 0) && ($id != $target) && ($kind != $init->comMissileST)) {
						// ��Y��
						$achive = 0; // ���B�
						for($i = 0; ($i < $init->pointNumber && $boat > 0); $i++) {
							$bx = $this->rpx[$i];
							$by = $this->rpy[$i];
							if(($land[$bx][$by] == $init->landTown) || ($land[$bx][$by] == $init->landSeaCity) ||
								($land[$bx][$by] == $init->landFroCity)) {
								// ���̏ꍇ
								$lv = $landValue[$bx][$by];
								if($boat > 50) {
									$lv += 50;
									$boat -= 50;
									$achive += 50;
								} else {
									$lv += $boat;
									$achive += $boat;
									$boat = 0;
								}
								if($lv > 200) {
									$boat += ($lv - 200);
									$achive -= ($lv - 200);
									$lv = 200;
								}
								$landValue[$bx][$by] = $lv;
							} elseif($land[$bx][$by] == $init->landPlains) {
								// ���n�̏ꍇ
								$land[$bx][$by] = $init->landTown;;
								if($boat > 10) {
									$landValue[$bx][$by] = 5;
									$boat -= 10;
									$achive += 10;
								} elseif($boat > 5) {
									$landValue[$bx][$by] = $boat - 5;
									$achive += $boat;
									$boat = 0;
								}
							}
							if($boat <= 0) {
								break;
							}
						}
						if($achive > 0) {
							// �����ł����������ꍇ�A���O��f��
							$this->log->msBoatPeople($id, $name, $achive);
							
							// ��̐�����萔�ȏ�Ȃ�A���a�܂̉\������
							if($achive >= 200) {
								$prize = $island['prize'];
								list($flags, $monsters, $turns) = split(",", $prize, 3);
								if((!($flags & 8)) && $achive >= 200){
									$flags |= 8;
									$this->log->prize($id, $name, $init->prizeName[4]);
								} elseif((!($flags & 16)) && $achive > 500){
									$flags |= 16;
									$this->log->prize($id, $name, $init->prizeName[5]);
								} elseif((!($flags & 32)) && $achive > 800){
									$flags |= 32;
									$this->log->prize($id, $name, $init->prizeName[6]);
								}
								$island['prize'] = "{$flags},{$monsters},{$turns}";
							}
						}
					}
					$command = $comArray[0];
					$kind    = $command['kind'];
					if((($kind == $init->comMissileNM) || // �����~�T�C���n�Ȃ�...
						($kind == $init->comMissilePP) ||
						($kind == $init->comMissileST) ||
						($kind == $init->comMissileBT) ||
						($kind == $init->comMissileSP) ||
						($kind == $init->comMissileLD) ||
						($kind == $init->comMissileLU)) &&
						($init->multiMissiles)) {
						$island['fire'] -= $comp;
						$cost = $init->comCost[$kind];
						
						if($island['fire'] < 1) {
							// �ő唭�ː��𒴂����ꍇ
							$this->log->msMaxOver($id, $name, $comName);
							$returnMode = 0;
							break;
						}
						if (($island['fire'] > 0) && ($island['money'] >= $cost)) { // ���Ȃ��Ƃ�1���͌��Ă�
							Util::slideFront(&$comArray, 0);
							$island['command'] = $comArray;
							$kind = $command['kind'];
							$target   = $command['target'];
							$x        = $command['x'];
							$y        = $command['y'];
							$arg      = $command['arg'];
							$comName  = $init->comName[$kind];
							$point    = "({$x},{$y})";
							$landName = $this->landName($landKind, $lv);
						} else {
							break;
						}
					} else if($kind == $init->comMissileSM) {
						Util::slideFront(&$comArray, 0);
						break;
					} else {
						break;
					}
				} while ($island['fire'] > 0);
				$returnMode = 1;
				break;
				
			case $init->comSendMonster:
				// ���b�h��
				// �^�[�Q�b�g�擾
				$tn = $hako->idToNumber[$target];
				$tIsland = $hako->islands[$tn];
				$tName = $tIsland['name'];
				
				if($tn !== 0 && empty($tn)) {
					// �^�[�Q�b�g�����łɂȂ�
					$this->log->msNoTarget($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if($tIsland['keep']) {
					// �ڕW�̓����Ǘ��l�a���蒆�̂��ߎ��s��������Ȃ�
					$this->log->CheckKP($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if((($hako->islandTurn - $island['starturn']) < $init->noMissile) || (($hako->islandTurn - $tIsland['starturn']) < $init->noMissile) || ($island['zin'][2] != 1)) {
					// ���s���^�[�����o�߂������H
					$this->log->Forbidden($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				// ���b�Z�[�W
				$this->log->monsSend($id, $target, $name, $tName);
				$tIsland['monstersend']++;
				$hako->islands[$tn] = $tIsland;
				
				// ������������
				$island['money'] -= $cost;
				
				$returnMode = 1;
				break;
				
			case $init->comSendSleeper:
				// ���b�A��
				// �^�[�Q�b�g�擾
				$tn = &$hako->idToNumber[$target];
				$tIsland = &$hako->islands[$tn];
				$tName = &$tIsland['name'];
				
				if($tn != 0 && empty($tn)) {
					// �^�[�Q�b�g�����łɂȂ�
					$this->log->msNoTarget($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if($tIsland['keep']) {
					// �ڕW�̓����Ǘ��l�a���蒆�̂��ߎ��s��������Ȃ�
					$this->log->CheckKP($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if((($hako->islandTurn - $island['starturn']) < $init->noMissile) || (($hako->islandTurn - $tIsland['starturn']) < $init->noMissile)) {
					// ���s���^�[�����o�߂������H
					$this->log->Forbidden($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				// �������̉��b�����邩���ׂ�
				$tLand = &$tIsland['land'];
				$tLandValue = &$tIsland['landValue'];
				
				if($landKind != $init->landSleeper) {
					// �������̉��b�����Ȃ�
					$this->log->MonsNoSleeper($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				// ���b�Z�[�W
				$this->log->monsSendSleeper($id, $target, $name, $tName, $landName);
				
				// �ǂ��Ɍ���邩���߂�
				for($i = 0; $i < $init->pointNumber; $i++) {
					$bx = $this->rpx[$i];
					$by = $this->rpy[$i];
					if($tLand[$bx][$by] == $init->landTown) {
						// �n�`��
						$lName = &$this->landName($init->landTown, $tLandValue[$bx][$by]);
						
						// ���̃w�b�N�X�����b��
						$tLand[$bx][$by] = $init->landMonster;
						$tLandValue[$bx][$by] = $lv;
						
						// �������̉��b���r��n��
						$land[$x][$y] = $init->landWaste;
						$landValue[$x][$y] = 0;
						
						// ���b���
						$monsSpec = Util::monsterSpec($lv);
						$mName    = $monsSpec['name'];
						
						// ���b�Z�[�W
						$this->log->monsCome($target, $tName, $mName, "({$bx}, {$by})", $lName);
						break;
					}
				}
				// ������������
				$island['money'] -= $cost;
				
				$returnMode = 1;
				break;
				
			case $init->comOffense:
				// �U���͋���
				if($island['soccer'] <= 0){
					$this->log->SoccerFail($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if(Util::random(100) < 60) {
					$island['kougeki'] += Util::random(3) + 1;
				}
				$this->log->SoccerSuc($id, $name, $comName);
				
				if($arg > 1) {
					$arg--;
					Util::slideBack(&$comArray, 0);
					$comArray[0] = array (
						'kind'   => $kind,
						'target' => $target,
						'x'      => $x,
						'y'      => $y,
						'arg'    => $arg,
					);
				}
				// ������������
				$island['money'] -= $cost;
				
				$returnMode = 1;
				break;
				
			case $init->comDefense:
				// ����͋���
				if($island['soccer'] <= 0){
					$this->log->SoccerFail($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if(Util::random(100) < 60) {
					$island['bougyo'] += Util::random(3) + 1;
				}
				$this->log->SoccerSuc($id, $name, $comName);
				
				if($arg > 1) {
					$arg--;
					Util::slideBack(&$comArray, 0);
					$comArray[0] = array (
						'kind'   => $kind,
						'target' => $target,
						'x'      => $x,
						'y'      => $y,
						'arg'    => $arg,
					);
				}
				// ������������
				$island['money'] -= $cost;
				
				$returnMode = 1;
				break;
				
			case $init->comPractice:
				// �������K
				if($island['soccer'] <= 0){
					$this->log->SoccerFail($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if(Util::random(100) < 60) {
					$island['bougyo'] += Util::random(3) + 1;
					$island['kougeki'] += Util::random(3) + 1;
				}
				$this->log->SoccerSuc($id, $name, $comName);
				
				if($arg > 1) {
					$arg--;
					Util::slideBack(&$comArray, 0);
					$comArray[0] = array (
						'kind'   => $kind,
						'target' => $target,
						'x'      => $x,
						'y'      => $y,
						'arg'    => $arg,
					);
				}
				// ������������
				$island['money'] -= $cost;
				
				$returnMode = 1;
				break;
				
			case $init->comPlaygame:
				// �𗬎���
				if($island['soccer'] <= 0) {
					$this->log->SoccerFail($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if($id == $target) {
					$this->log->SoccerFail2($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				// �^�[�Q�b�g�擾
				$tn = $hako->idToNumber[$target];
				$tIsland = &$hako->islands[$tn];
				$tName   = $tIsland['name'];
				$tLand   = $tIsland['land'];
				$tLandValue = $tIsland['landValue'];
				
				if($tn !== 0 && empty($tn)) {
					// �^�[�Q�b�g�����łɂȂ�
					$this->log->msNoTarget($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if($tIsland['soccer'] <= 0) {
					$this->log->GameFail($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if($tIsland['keep']) {
					// �ڕW�̓����Ǘ��l�a���蒆�̂��ߎ��s��������Ȃ�
					$this->log->CheckKP($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if(($island['kougeki'] > $tIsland['kougeki']) && ($island['bougyo'] > $tIsland['bougyo'])) {
					// �U���́A����͂Ƃ��ɏ�
					$da = Util::random(7) + 3;
					$db = Util::random(5) + 3;
					$ba = Util::random(7);
					$bb = Util::random(5);
					$it = ($da - $bb);
					$tt = ($db - $ba);
					if($it < 0) { $it = 0; }
					if($tt < 0) { $tt = 0; }
					if($it > $tt) {
						// ����
						$island['kachi'] ++;
						$tIsland['make'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(5) + 3;
						$island['bougyo'] += Util::random(5) + 3;
						$tIsland['kougeki'] += Util::random(3) + 1;
						$tIsland['bougyo'] += Util::random(3) + 1;
						$this->log->GameWin($id, $tId, $name, $tName, $comName, $it, $tt);
					} elseif($it < $tt) {
						// ����
						$island['make'] ++;
						$tIsland['kachi'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(3) + 1;
						$island['bougyo'] += Util::random(3) + 1;
						$tIsland['kougeki'] += Util::random(5) + 3;
						$tIsland['bougyo'] += Util::random(5) + 3;
						$this->log->GameLose($id, $tId, $name, $tName, $comName, $it, $tt);
					} elseif($it == $tt) {
						// ��������
						$island['hikiwake'] ++;
						$tIsland['hikiwake'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(3) + 1;
						$island['bougyo'] += Util::random(3) + 1;
						$tIsland['kougeki'] += Util::random(3) + 1;
						$tIsland['bougyo'] += Util::random(3) + 1;
						$this->log->GameDraw($id, $tId, $name, $tName, $comName, $it, $tt);
					}
				} elseif(($island['kougeki'] > $tIsland['kougeki']) && ($island['bougyo'] < $tIsland['bougyo'])) {
					// �U���͂͏�A����͉͂�
					$da = Util::random(7) + 3;
					$db = Util::random(5) + 3;
					$ba = Util::random(5);
					$bb = Util::random(7);
					$it = ($da - $bb);
					$tt = ($db - $ba);
					if($it < 0) { $it = 0; }
					if($tt < 0) { $tt = 0; }
					if($it > $tt) {
						// ����
						$island['kachi'] ++;
						$tIsland['make'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(5) + 3;
						$island['bougyo'] += Util::random(5) + 3;
						$tIsland['kougeki'] += Util::random(3) + 1;
						$tIsland['bougyo'] += Util::random(3) + 1;
						$this->log->GameWin($id, $tId, $name, $tName, $comName, $it, $tt);
					} elseif($it < $tt) {
						// ����
						$island['make'] ++;
						$tIsland['kachi'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(3) + 1;
						$island['bougyo'] += Util::random(3) + 1;
						$tIsland['kougeki'] += Util::random(5) + 3;
						$tIsland['bougyo'] += Util::random(5) + 3;
						$this->log->GameLose($id, $tId, $name, $tName, $comName, $it, $tt);
					} elseif($it == $tt) {
						// ��������
						$island['hikiwake'] ++;
						$tIsland['hikiwake'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(3) + 1;
						$island['bougyo'] += Util::random(3) + 1;
						$tIsland['kougeki'] += Util::random(3) + 1;
						$tIsland['bougyo'] += Util::random(3) + 1;
						$this->log->GameDraw($id, $tId, $name, $tName, $comName, $it, $tt);
					}
				} elseif(($island['kougeki'] < $tIsland['kougeki']) && ($island['bougyo'] > $tIsland['bougyo'])) {
					// �U���͉͂��A����͂͏�
					$da = Util::random(5) + 3;
					$db = Util::random(7) + 3;
					$ba = Util::random(7);
					$bb = Util::random(5);
					$it = ($da - $bb);
					$tt = ($db - $ba);
					if($it < 0) { $it = 0; }
					if($tt < 0) { $tt = 0; }
					if($it > $tt) {
						// ����
						$island['kachi'] ++;
						$tIsland['make'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(5) + 3;
						$island['bougyo'] += Util::random(5) + 3;
						$tIsland['kougeki'] += Util::random(3) + 1;
						$tIsland['bougyo'] += Util::random(3) + 1;
						$this->log->GameWin($id, $tId, $name, $tName, $comName, $it, $tt);
					} elseif($it < $tt) {
						// ����
						$island['make'] ++;
						$tIsland['kachi'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(3) + 1;
						$island['bougyo'] += Util::random(3) + 1;
						$tIsland['kougeki'] += Util::random(5) + 3;
						$tIsland['bougyo'] += Util::random(5) + 3;
						$this->log->GameLose($id, $tId, $name, $tName, $comName, $it, $tt);
					} elseif($it == $tt) {
						// ��������
						$island['hikiwake'] ++;
						$tIsland['hikiwake'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(3) + 1;
						$island['bougyo'] += Util::random(3) + 1;
						$tIsland['kougeki'] += Util::random(3) + 1;
						$tIsland['bougyo'] += Util::random(3) + 1;
						$this->log->GameDraw($id, $tId, $name, $tName, $comName, $it, $tt);
					}
				} elseif(($island['kougeki'] < $tIsland['kougeki']) && ($island['bougyo'] < $tIsland['bougyo'])) {
					// �U���́A����͂Ƃ��ɉ�
					$da = Util::random(5) + 3;
					$db = Util::random(7) + 3;
					$ba = Util::random(5);
					$bb = Util::random(7);
					$it = ($da - $bb);
					$tt = ($db - $ba);
					if($it < 0) { $it = 0; }
					if($tt < 0) { $tt = 0; }
					if($it > $tt) {
						// ����
						$island['kachi'] ++;
						$tIsland['make'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(5) + 3;
						$island['bougyo'] += Util::random(5) + 3;
						$tIsland['kougeki'] += Util::random(3) + 1;
						$tIsland['bougyo'] += Util::random(3) + 1;
						$this->log->GameWin($id, $tId, $name, $tName, $comName, $it, $tt);
					} elseif($it < $tt) {
						// ����
						$island['make'] ++;
						$tIsland['kachi'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(3) + 1;
						$island['bougyo'] += Util::random(3) + 1;
						$tIsland['kougeki'] += Util::random(5) + 3;
						$tIsland['bougyo'] += Util::random(5) + 3;
						$this->log->GameLose($id, $tId, $name, $tName, $comName, $it, $tt);
					} elseif($it == $tt) {
						// ��������
						$island['hikiwake'] ++;
						$tIsland['hikiwake'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(3) + 1;
						$island['bougyo'] += Util::random(3) + 1;
						$tIsland['kougeki'] += Util::random(3) + 1;
						$tIsland['bougyo'] += Util::random(3) + 1;
						$this->log->GameDraw($id, $tId, $name, $tName, $comName, $it, $tt);
					}
				} elseif(($island['kougeki'] == $tIsland['kougeki']) && ($island['bougyo'] == $tIsland['bougyo'])) {
					// �U���́A����͂Ƃ��ɂ�������
					$da = Util::random(5) + 3;
					$db = Util::random(5) + 3;
					$ba = Util::random(5);
					$bb = Util::random(5);
					$it = ($da - $bb);
					$tt = ($db - $ba);
					if($it < 0) { $it = 0; }
					if($tt < 0) { $tt = 0; }
					if($it > $tt) {
						// ����
						$island['kachi'] ++;
						$tIsland['make'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(5) + 3;
						$island['bougyo'] += Util::random(5) + 3;
						$tIsland['kougeki'] += Util::random(3) + 1;
						$tIsland['bougyo'] += Util::random(3) + 1;
						$this->log->GameWin($id, $tId, $name, $tName, $comName, $it, $tt);
					} elseif($it < $tt) {
						// ����
						$island['make'] ++;
						$tIsland['kachi'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(3) + 1;
						$island['bougyo'] += Util::random(3) + 1;
						$tIsland['kougeki'] += Util::random(5) + 3;
						$tIsland['bougyo'] += Util::random(5) + 3;
						$this->log->GameLose($id, $tId, $name, $tName, $comName, $it, $tt);
					} elseif($it == $tt) {
						// ��������
						$island['hikiwake'] ++;
						$tIsland['hikiwake'] ++;
						$island['tokuten'] += $it;
						$tIsland['tokuten'] += $tt;
						$island['shitten'] += $tt;
						$tIsland['shitten'] += $it;
						$island['kougeki'] += Util::random(3) + 1;
						$island['bougyo'] += Util::random(3) + 1;
						$tIsland['kougeki'] += Util::random(3) + 1;
						$tIsland['bougyo'] += Util::random(3) + 1;
						$this->log->GameDraw($id, $tId, $name, $tName, $comName, $it, $tt);
					}
				}
				$island['shiai'] ++;
				$tIsland['shiai'] ++;
				
				// ������������
				$island['money'] -= $cost;
				
				$returnMode = 1;
				break;
				
			case $init->comSell:
				// �A�o�ʌ���
				if($arg == 0) { $arg = 1; }
				$value = min($arg * (-$cost), $island['food']);
				$unit = $init->unitFood;
				// �A�o���O
				$this->log->sell($id, $name, $comName, $value, $unit);
				$island['food'] -=  $value;
				$island['money'] += ($value / 10);
				
				$returnMode = 0;
				break;
				
			case $init->comSellTree:
				// �A�o�ʌ���
				if($arg == 0) { $arg = 1; }
				$value = min($arg * (-$cost), $island['item'][20]);
				$unit = $init->unitTree;
				// �A�o���O
				$this->log->sell($id, $name, $comName, $value, $unit);
				$island['item'][20] -=  $value;
				$island['money'] += $value * $init->treeValue;
				
				$returnMode = 0;
				break;
				
			case $init->comFood:
			case $init->comMoney:
				// �����n
				// �^�[�Q�b�g�擾
				$tn = $hako->idToNumber[$target];
				$tIsland = &$hako->islands[$tn];
				$tName = $tIsland['name'];
				
				if($tn !== 0 && empty($tn)) {
					// �^�[�Q�b�g�����łɂȂ�
					$this->log->msNoTarget($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if($tIsland['keep']) {
					// �ڕW�̓����Ǘ��l�a���蒆�̂��ߎ��s��������Ȃ�
					$this->log->CheckKP($id, $name, $comName);
					
					$returnMode = 0;
					break;
				}
				if((($hako->islandTurn - $island['starturn']) < $init->noAssist) || (($hako->islandTurn - $tIsland['starturn']) < $init->noAssist)) {
					// ���s���^�[�����o�߂������H
					$this->log->Forbidden($id, $name, $comName);
					
					$returnMode = 0;
					break;
				}
				// �����ʌ���
				if($arg == 0) { $arg = 1; }
				
				if($cost < 0) {
					$value = min($arg * (-$cost), $island['food']);
					$str = "{$value}{$init->unitFood}";
				} else {
					$value = min($arg * ($cost), $island['money']);
					$str = "{$value}{$init->unitMoney}";
				}
				// �������O
				$this->log->aid($id, $target, $name, $tName, $comName, $str);
				
				if($cost < 0) {
					$island['food'] -= $value;
					$tIsland['food'] += $value;
				} else {
					$island['money'] -= $value;
					$tIsland['money'] += $value;
				}
				$hako->islands[$tn] = $tIsland;
				$returnMode = 0;
				break;
				
			case $init->comLot:
				// �󂭂��w��
				if($island['lot'] > 30){
					// �󂭂�����
					$this->log->noLot($id, $name, $comName);
					$returnMode = 0;
					break;
				}
				if($arg == 0) { $arg = 1; }
				if($arg > 30) { $arg = 30; }
				
				$value = min($arg * ($cost), $island['money']);
				$str = "{$value}{$init->unitMoney}";
				$p = round($value / $cost);
				$island['lot'] += $p;
				
				// �w�����O
				$this->log->buyLot($id, $name, $comName, $str);
				
				// ������������
				$island['money'] -= $value;
				
				$returnMode =  1;
				break;
				
			case $init->comPropaganda:
				// �U�v����
				$island['propaganda'] = 1;
				$island['money'] -= $cost;
				$this->log->propaganda($id, $name, $comName);
				
				if($arg > 1) {
					$arg--;
					Util::slideBack(&$comArray, 0);
					$comArray[0] = array (
						'kind'   => $kind,
						'target' => $target,
						'x'      => $x,
						'y'      => $y,
						'arg'    => $arg,
					);
				}
				$returnMode = 1;
				break;
				
			case $init->comGiveup:
				// ����
				$this->log->giveup($id, $name);
				$island['dead'] = 1;
				unlink("{$init->dirName}/island.{$id}");
				$returnMode = 1;
				break;
		}
		// �ύX���ꂽ�\���̂���ϐ��������߂�
		// $hako->islands[$hako->idToNumber[$id]] = $island;
		// ���㏈��
		unset($island['prize']);
		unset($island['land']);
		unset($island['landValue']);
		unset($island['command']);
		$island['prize'] = $prize;
		$island['land'] = $land;
		$island['landValue'] = $landValue;
		$island['command'] = $comArray;
		
		return $returnMode;
	}
	
	//---------------------------------------------------
	// ��������ђP�w�b�N�X�ЊQ
	//---------------------------------------------------
	function doEachHex(&$hako, &$island) {
		global $init;
		
		// ���o�l
		$name = $island['name'];
		$id = $island['id'];
		$land = $island['land'];
		$landValue = $island['landValue'];
		$oilFlag = $island['oil'];
		
		// ������l���̃^�l�l
		$addpop  = 10; // ���A��
		$addpop2 = 0;  // �s�s
		
		if($island['food'] <= 0) {
			// �H���s��
			$addpop = -30;
		} elseif(($island['ship'][10] + $island['ship'][11] + $island['ship'][12] + $island['ship'][13] + $island['ship'][14]) > 0) {
			// �C���D���o�v���͐������Ȃ�
			$addpop = 0;
		} elseif($island['park'] > 0) {
			// �V���n������Ɛl���W�܂�
			$addpop  += 10;
			$addpop2 += 1;
		} elseif($island['propaganda'] == 1) {
			// �U�v������
			$addpop = 30;
			$addpop2 = 3;
		}
		$monsterMove = array();
		
		// ���[�v
		for($i = 0; $i < $init->pointNumber; $i++) {
			$x = $this->rpx[$i];
			$y = $this->rpy[$i];
			$landKind = $land[$x][$y];
			$lv = $landValue[$x][$y];
			
			switch($landKind) {
				case $init->landWaste:
					//�r�n
					if ($island['isBF'] == 1) {
						$land[$x][$y] = $init->landPlains;
						$landValue[$x][$y] = 0;
					}
					break;
					
				case $init->landTown:
				case $init->landSeaCity:
					// ���n
					if($addpop < 0) {
						// �s��
						$lv -= (Util::random(-$addpop) + 1);
						if(($lv <= 0) && ($landKind == $init->landSeaCity)) {
							// �C�ɖ߂�
							$land[$x][$y] = $init->landSea;
							$landValue[$x][$y] = 0;
							continue;
						} elseif(($lv <= 0) && ($landKind == $init->landTown)) {
							$land[$x][$y] = $init->landPlains;
							$landValue[$x][$y] = 0;
							continue;
						}
					} else {
						// ����
						if($lv < 100) {
							$lv += Util::random($addpop) + 1;
							if($lv > 100) {
								$lv = 100;
							}
						} else {
							// �s�s�ɂȂ�Ɛ����x��
							if($addpop2 > 0) {
								$lv += Util::random($addpop2) + 1;
							}
						}
					}
					if($lv > 250) {
						$lv = 250;
					}
					$landValue[$x][$y] = $lv;
					break;
					
				case $init->landNewtown:
					// �j���[�^�E���n
					$townCount = Turn::countAround($land, $x, $y, 19, array($init->landTown, $init->landNewtown, $init->landBigtown));
					if($townCount > 17) {
						if(Util::random(1000) < 3) {
							if($lv > 200) {
								$land[$x][$y] = $init->landBigtown;
							}
						}
					}
					if($addpop < 0) {
						// �s��
						$lv -= (Util::random(-$addpop) + 1);
						if($lv <= 0) {
							// ���n�ɖ߂�
							$land[$x][$y] = $init->landPlains;
							$landValue[$x][$y] = 0;
							continue;
						}
					} else {
						// ����
						if($lv < 100) {
							$lv += Util::random($addpop) + 1;
							if($lv > 100) {
								$lv = 100;
							}
						} else {
							// �s�s�ɂȂ�Ɛ����x��
							if($addpop2 > 0) {
								$lv += Util::random($addpop2) + 1;
							}
						}
					}
					if($lv > 300) {
						$lv = 300;
					}
					$landValue[$x][$y] = $lv;
					break;
					
				case $init->landBigtown:
					// ����s�s�n
					if($addpop < 0) {
						// �s��
						$lv -= (Util::random(-$addpop) + 1);
						if($lv <= 0) {
							// ���n�ɖ߂�
							$land[$x][$y] = $init->landPlains;
							$landValue[$x][$y] = 0;
							continue;
						}
					} else {
						// ����
						if($lv < 200) {
							$lv += Util::random($addpop) + 1;
							if($lv > 200) {
								$lv = 200;
							}
						} else {
							// �s�s�ɂȂ�Ɛ����x��
							if($addpop2 > 0) {
								$lv += Util::random($addpop2) + 1;
							}
						}
					}
					if($lv > 500) {
						$lv = 500;
					}
					$landValue[$x][$y] = $lv;
					break;
					
				case $init->landPlains:
					// ���n
					if ($island['isBF'] == 1) { // BF����ɑ�����
						$land[$x][$y] = $init->landTown;
						$landValue[$x][$y] = 10;
					} elseif(Util::random(5) == 0) {
						// ����ɔ_��A��������΁A���������ɂȂ�
						if($this->countGrow($land, $landValue, $x, $y)){
							$land[$x][$y] = $init->landTown;
							$landValue[$x][$y] = 1;
							if(Util::random(1000) < 75) {
								$land[$x][$y] = $init->landNewtown;
								$landValue[$x][$y] = 1;
							}
						}
					}
					break;
					
				case $init->landPoll:
					// �����y��
					if(Util::random(3) == 0) {
						// ������
						$land[$x][$y] = $init->landPoll;
						$landValue[$x][$y]--;
						if(($landKind == $init->landPoll) && ($landValue[$x][$y] == 0)) {
							// �����򉻂��ꕽ�n�ɂȂ�
							$land[$x][$y] = $init->landPlains;
						}
					}
					break;
					
				case $init->landProcity:
					// �h�Гs�s
					if($addpop < 0) {
						// �s��
						$lv -= (Util::random(-$addpop) + 1);
						if($lv <= 0) {
							// ���n�ɖ߂�
							$land[$x][$y] = $init->landPlains;
							$landValue[$x][$y] = 0;
							continue;
						}
					} else {
						// ����
						if($lv < 100) {
							$lv += Util::random($addpop) + 1;
							if($lv > 100) {
								$lv = 100;
							}
						} else {
							// �s�s�ɂȂ�Ɛ����x��
							if($addpop2 > 0) {
								$lv += Util::random($addpop2) + 1;
							}
						}
					}
					if($lv > 200) {
						$lv = 200;
					}
					$landValue[$x][$y] = $lv;
					break;
					
				case $init->landFroCity:
					// �C��s�s
					if($addpop < 0) {
						// �s��
						$lv -= (Util::random(-$addpop) + 1);
						if($lv <= 0) {
							// �C�ɖ߂�
							$land[$x][$y] = $init->landSea;
							$landValue[$x][$y] = 0;
						}
					} else {
						// ����
						if($lv < 100) {
							$lv += Util::random($addpop) + 1;
							if($lv > 100) {
								$lv = 100;
							}
						} else {
							// �s�s�ɂȂ�Ɛ����x��
							if($addpop2 > 0) {
								$lv += Util::random($addpop2) + 1;
							}
						}
					}
					if($lv > 250) {
						$lv = 250;
					}
					// ��������������
					for($fro = 0; $fro < 3; $fro++) {
						$d = Util::random(6) + 1;
						$sx = $x + $init->ax[$d];
						$sy = $y + $init->ay[$d];
						// �s�ɂ��ʒu����
						if((($sy % 2) == 0) && (($y % 2) == 1)) {
							$sx--;
						}
						// �͈͊O����
						if(($sx < 0) || ($sx >= $init->islandSize) ||
							($sy < 0) || ($sy >= $init->islandSize)) {
							continue;
						}
						// �C���������Ȃ�
						if(($land[$sx][$sy] == $init->landSea) && ($landValue[$sx][$sy] == 0)) {
							break;
						}
					}
					if($fro == 3) {
						// �����Ȃ�����
						break;
					}
					// �ړ�
					$land[$sx][$sy] = $land[$x][$y];
					$landValue[$sx][$sy] = $lv;
					
					// ���Ƌ����ʒu���C��
					$land[$x][$y] = $init->landSea;
					$landValue[$x][$y] = 0;
					break;
					
				case $init->landForest:
					// �X
					if($lv < 200) {
						// �؂𑝂₷
						if($island['zin'][3] == 1) {
							$landValue[$x][$y] += 2;
						} else {
							$landValue[$x][$y]++;
						}
					}
					break;
					
				case $init->landCommerce:
					// ���ƃr��
					if(Util::random(1000) < $init->disSto) {
						// �X�g���C�L
						$landValue[$x][$y] -= 5;
						if($landValue[$x][$y] <= 0){
							$land[$x][$y] = $init->landCommerce;
							$landValue[$x][$y] = 0;
						}
						$this->log->Sto($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
					}
					break;
					
				case $init->landMonument:
					// �L�O��
					$lv = $landValue[$x][$y];
					$lName = $this->landName($landKind, $lv);
					
					if(($lv == 5) || ($lv == 6) || ($lv == 21) || ($lv == 24) || ($lv == 32)) {
						if(util::random(100) < 5) {
							// ���y�Y
							$value = 1+ Util::random(49);
							if ($value > 0) {
								$island['money'] += $value;
								$str = "{$value}{$init->unitMoney}";
								$this->log->Miyage($id, $name, $lName, "($x,$y)", $str);
								break;
							}
						}
					} elseif(($lv == 1) || ($lv == 7) || ($lv == 33)) {
						if(util::random(100) < 5) {
							// ���n
							$value = round($island['pop'] / 100) * 10 + Util::random(11);
							// �l���P���l���Ƃ�1000�g���̎��n
							if ($value > 0) {
								$island['food'] += $value;
								$str = "{$value}{$init->unitFood}";
								$this->log->Syukaku($id, $name, $lName, "($x,$y)", $str);
								break;
							}
						}
					} elseif($lv == 15) {
						if(util::random(100) < 5) {
							// ��s��
							$land[$x][$y] = $init->landBank;
							$landValue[$x][$y] = 1;
							// ���b�Z�[�W
							$this->log->Bank($id, $name, $lName, "($x,$y)");
							break;
						}
					} elseif(($lv == 40) || ($lv == 41) || ($lv == 42) || ($lv == 43)) {
						if(util::random(100) < 1) {
							// ���z��
							$kind = Util::random($init->monsterLevel1) + 1;
							$lv = $kind * 100
								+ $init->monsterBHP[$kind] + Util::random($init->monsterDHP[$kind]);
							// ���̃w�b�N�X�����b��
							$land[$x][$y] = $init->landMonster;
							$landValue[$x][$y] = $lv;
							// ���b���
							$monsSpec = Util::monsterSpec($lv);
							// ���b�Z�[�W
							$this->log->EggBomb($id, $name, $mName, "($x,$y)", $lName);
							break;
						}
					}
					break;
					
				case $init->landSeaResort:
					// �C�̉�
					$nt = Turn::countAround($land, $x, $y, 19, array($init->landTown)); // ����2�w�b�N�X�̐l��
					$ns = Turn::countAround($land, $x, $y, 19, array($init->landSeaSide)); // ����2�w�b�N�X�̍��l���e�l��
					// ���v�̌v�Z
					if ($nt > 0) {
						$value = $ns / $nt;
					}
					$value = (int)($lv * $value * $nt);
					if ($value > 0) {
						$island['money'] += $value;
						// �������O
						$str = "{$value}{$init->unitMoney}";
						$this->log->oilMoney($id, $name, $this->landName($landKind, $lv), "($x,$y)", $str);
					}
					if($lv < 30) {
						// �C�̉�
						$n = 1;
					} elseif($lv < 100) {
						// ���h
						$n = 2;
					} else {
						// ���]�[�g�z�e��
						$n = 4;
					}
					$lv += (int)(Util::random($nt / $n) * (($nt < $ns) ? -1 : 1));
					if ($lv < 1) {
						$lv = 1;
					} elseif ($lv > 200) {
						$lv = 200;
					}
					$landValue[$x][$y] = $lv;
					break;
					
				case $init->landDefence:
					if($lv == 0) {
						// �h�q�{�ݎ���
						$lName = $this->landName($landKind, $lv);
						$this->log->bombFire($id, $name, $lName, "($x, $y)");
						// �L���Q���[�`��
						$this->wideDamage($id, $name, &$land, &$landValue, $x, $y);
					}
					break;
					
				case $init->landHatuden:
					// ���d��
					$lName = $this->landName($landKind, $lv);
					if(Util::random(100000) < $landValue[$x][$y]) {
						// �����g�_�E��
						$land[$x][$y] = $init->landSea;
						$landValue[$x][$y] = 0;
						$this->log->CrushElector($id, $name, $lName, "($x, $y)");
					}
					break;
					
				case $init->landSoukoM:
				case $init->landSoukoF:
					// �q��
					$lName = $this->landName($landKind, $lv);
					
					// �Z�L�����e�B�ƒ��~���Z�o
					$sec = (int)($landValue[$x][$y] / 100);
					$tyo = $landValue[$x][$y] % 100;
					
					if(Util::random(100) < (10 - $sec)) {
						// ����
						$tyo = (int)($tyo / 100 * Util::random(100));
						$sec--;
						if($sec < 0) {
							$sec = 0;
						}
						$landValue[$x][$y] = $sec * 100 + $tyo;
						$this->log->SoukoLupin($id, $name, $lName, "($x, $y)");
					}
					break;
					
				case $init->landOil:
					// �C����c
					$lName = $this->landName($landKind, $lv);
					$value = $init->oilMoney;
					$island['money'] += $value;
					$island['oilincome'] += $value;
					
					// �͊�����
					if(Util::random(1000) < $init->oilRatio) {
						// �͊�
						$land[$x][$y] = $init->landSea;
						$landValue[$x][$y] = 0;
						$this->log->oilEnd($id, $name, $lName, "($x, $y)");
					}
					break;
					
				case $init->landBank:
					// ��s
					$island['bank']++;
					break;
					
				case $init->landSoccer:
					// �X�^�W�A��
					$lName = $this->landName($landKind, $lv);
					$value = $island['team'];
					
					if($value > 200) {
						$value = 200;
					}
					$island['money'] += $value;
					$str = "{$value}{$init->unitMoney}";
					// �������O
					if ($value > 0) {
						$this->log->oilMoney($id, $name, $lName, "($x, $y)", $str);
					}
					break;
					
				case $init->landPark:
					// �V���n
					$lName = $this->landName($landKind, $lv);
					//$value = floor($island['pop'] / 50); // �l���T��l���ƂɂP���~�̎���
					//���v�͐l�������ƂƂ��ɉ��΂��X��
					//�l���̕�������1�`2�{ ex 1��=10�`20���~ 100��=100�`200���~
					$value = floor(sqrt($island['pop'])*((Util::random(100)/100)+1));
					$island['money'] += $value;
					$str = "{$value}{$init->unitMoney}";
					
					//�������O
					if ($value > 0) {
						$this->log->ParkMoney($id, $name, $lName, "($x,$y)", $str);
					}
					//�C�x���g����
					if(Util::random(100) < 30) {
						// ���^�[�� 30% �̊m���ŃC�x���g����������
						//�V���n�̃C�x���g
						$value2=$value;
						
						//�H������
						$value = floor($island['pop'] * $init->eatenFood / 2); // �K��H������̔�������
						$island['food'] -= $value;
						$str = "{$value}{$init->unitFood}";
						
						if ($value > 0) {
							$this->log->ParkEvent($id, $name, $lName, "($x,$y)", $str);
						}
						//�C�x���g�̎��x
						$value = floor((Util::random(200) - 100)/100 * $value2);//�}�C�i�X100%�`�v���X100%
						$island['money'] += $value;
						if ($value > 0) {
							$str = "{$value}{$init->unitMoney}";
							$this->log->ParkEventLuck($id, $name, $lName, "($x,$y)", $str);
						}
						if ($value < 0) {
							$value = -$value;
							$str = "{$value}{$init->unitMoney}";
							$this->log->ParkEventLoss($id, $name, $lName, "($x,$y)", $str);
						}
					}
					// �V�z������
					if(Util::random(100) < 5) {
						// �{�݂��V�z���������ߕ�
						$land[$x][$y] = $init->landPlains;
						$landValue[$x][$y] = 0;
						$this->log->ParkEnd($id, $name, $lName, "($x,$y)");
					}
					break;
					
				case $init->landPort:
					// �`
					$lName = $this->landName($landKind, $lv);
					$seaCount = Turn::countAround($land, $x, $y, 7, array($init->landSea));
					if($seaCount == 0 || $seaCount == 6){
						// ���͂ɍŒ�1Hex�̊C�������ꍇ�A��
						// ���͂ɍŒ�1Hex�̗��n�������ꍇ�A��
						$land[$x][$y] = $init->landSea;
						$landValue[$x][$y] = 1;
						$this->log->ClosedPort($id, $name, $lName, "($x,$y)");
					}
					break;
					
				case $init->landTrain:
					// �d��
					if($TrainMove[$x][$y] == 1) {
						// ���łɓ�������
						break;
					}
					// ��������������
					for($t = 0; $t < 3; $t++) {
						$d = Util::random(6) + 1;
						$sx = $x + $init->ax[$d];
						$sy = $y + $init->ay[$d];
						// �s�ɂ��ʒu����
						if((($sy % 2) == 0) && (($y % 2) == 1)) {
							$sx--;
						}
						// �͈͊O����
						if(($sx < 0) || ($sx >= $init->islandSize) ||
							($sy < 0) || ($sy >= $init->islandSize)) {
							continue;
						}
						// ���H���������Ȃ�
						if($land[$sx][$sy] == $init->landRail) {
							break;
						}
					}
					if($t == 3) {
						// �����Ȃ�����
						break;
					}
					$l = $land[$sx][$sy];
					$lv = $landValue[$sx][$sy];
					$lName = $this->landName($l, $lv);
					$point = "({$sx}, {$sy})";
					
					// �ړ�
					$land[$sx][$sy] = $land[$x][$y];
					
					// ���Ƌ����ʒu����H��
					$land[$x][$y] = $init->landRail;
					
					// �ړ����݃t���O�A�Z�b�g
					$TrainMove[$sx][$sy] = 1;
					break;
					
				case $init->landZorasu:
					// �C���b
					if($ZorasuMove[$x][$y] == 1) {
						// ���łɓ�������
						break;
					}
					// ��������������
					for($j = 0; $j < 3; $j++) {
						$d = Util::random(6) + 1;
						$sx = $x + $init->ax[$d];
						$sy = $y + $init->ay[$d];
						// �s�ɂ��ʒu����
						if((($sy % 2) == 0) && (($y % 2) == 1)) {
							$sx--;
						}
						// �͈͊O����
						if(($sx < 0) || ($sx >= $init->islandSize) ||
							($sy < 0) || ($sy >= $init->islandSize)) {
							continue;
						}
						// �C�A�D���A�C��A�C�h�A�C��s�s�A�C��s�s�A�C����h���A�C��_��A���c
						if(($land[$sx][$sy] == $init->landSea) ||
							($land[$sx][$sy] == $init->landShip) ||
							($land[$sx][$sy] == $init->landSbase) ||
							($land[$sx][$sy] == $init->landSdefence) ||
							($land[$sx][$sy] == $init->landSeaCity) ||
							($land[$sx][$sy] == $init->landFroCity) ||
							($land[$sx][$sy] == $init->landSsyoubou) ||
							($land[$sx][$sy] == $init->landSfarm) ||
							($land[$sx][$sy] == $init->landOil)) {
							break;
						}
					}
					if($j == 3) {
						// �����Ȃ�����
						break;
					}
					// ��������̒n�`�ɂ�胁�b�Z�[�W
					$l = $land[$sx][$sy];
					$lv = $landValue[$sx][$sy];
					$lName = $this->landName($l, $lv);
					$point = "({$sx}, {$sy})";
					if($land[$sx][$sy] != $init->landSea) {
						$this->log->ZorasuMove($id, $name, $lName, $point);
					}
					// �ړ�
					$land[$sx][$sy] = $land[$x][$y];
					$landValue[$sx][$sy] = $landValue[$x][$y];
					
					// ���Ƌ����ʒu���C��
					$land[$x][$y] = $init->landSea;
					$landValue[$x][$y] = 0;
					
					// �ړ����݃t���O�A�Z�b�g
					$ZorasuMove[$sx][$sy] = 1;
					break;
					
				case $init->landMonster:
					// ���b
					if($monsterMove[$x][$y] == 2) {
						// ���łɓ�������
						break;
					}
					// �e�v�f�̎��o��
					$monsSpec = Util::monsterSpec($landValue[$x][$y]);
					$special  = $init->monsterSpecial[$monsSpec['kind']];
					$mName = $monsSpec['name'];
					
					// ���b�̗͉̑�
					if(($monsSpec['hp'] < $init->monsterBHP[$monsSpec['kind']]) && (Util::random(100) < 20)) {
						$landValue[$x][$y]++;
					}
					
					if((Turn::countAroundValue($island, $x, $y, $init->landProcity, 200, 7)) && ($monsSpec['kind'] != 26)) {
						// ����1Hex�ɕʂ̉��b������ꍇ�A���̉��b���U������
						// �Ώۂ̉��b���|��čr�n�ɂȂ�
						$land[$x][$y] = $init->landWaste;
						$landValue[$x][$y] = 0;
						$this->log->BariaAttack($id, $name, $lName, "($x,$y)", $mName, $tPoint);
						
						// ����
						$value = $init->monsterValue[$monsSpec['kind']];
						if($value > 0) {
							$island['money'] += $value;
							$this->log->msMonMoney($id, $target, $mName, $value);
						}
						break;
					}
					// �d����?
					if((($special & 0x4) && (($hako->islandTurn % 2) == 1)) ||
						(($special & 0x10) && (($hako->islandTurn % 2) == 0))) {
						// �d����
						break;
					}
					if($special & 0x20) {
						// ���Ԃ��Ăԉ��b
						if ((Util::random(100) < 5) && ($pop >= $init->disMonsBorder1)) {
							// ���b�o��
							$pop = $island['pop'];
							$this->log->monsCall($id, $name, $mName, "({$x}, {$y})");
							if ($pop >= $init->disMonsBorder5) {
								// level5�܂�
								$kind = Util::random($init->monsterLevel5) + 1;
							} elseif($pop >= $init->disMonsBorder4) {
								// level4�̂�
								$kind = Util::random($init->monsterLevel4) + 1;
							} elseif($pop >= $init->disMonsBorder3) {
								// level3�̂�
								$kind = Util::random($init->monsterLevel3) + 1;
							} elseif($pop >= $init->disMonsBorder2) {
								// level2�̂�
								$kind = Util::random($init->monsterLevel2) + 1;
							} else {
								// level1�̂�
								$kind = Util::random($init->monsterLevel1) + 1;
							}
							// lv�̒l�����߂�
							$lv = $kind * 100
								+ $init->monsterBHP[$kind] + Util::random($init->monsterDHP[$kind]);
							// �ǂ��Ɍ���邩���߂�
							for($i = 0; $i < $init->pointNumber; $i++) {
								$bx = $this->rpx[$i];
								$by = $this->rpy[$i];
								if($land[$bx][$by] == $init->landTown) {
									// �n�`��
									$lName = $this->landName($init->landTown, $landValue[$bx][$by]);
									// ���̃w�b�N�X�����b��
									$land[$bx][$by] = $init->landMonster;
									$landValue[$bx][$by] = $lv;
									// ���b���
									$monsSpec = Util::monsterSpec($lv);
									// ���b�Z�[�W
									$this->log->monsCome($id, $name, $mName, "({$bx}, {$by})", $lName);
									break;
								}
							}
						}
					}
					// ���[�v������b
					if ($special & 0x40) {
						$r = mt_rand(0,100);
						if ($r < 20) { // 20%
							// ���[�v����
							$tg;
							$tIsland = $island;
							$r = mt_rand(0,100);
							if ($r < 50) { // 50%
								// ���[�v���铇�����߂�
								$tg = Util::random($hako->islandNumber);
								$tIsland = $hako->islands[$tg];
								if((($hako->islandTurn - $tIsland['starturn']) < $init->noAssist) && ($tIsland['isBF'] != 1)) {
									// ���S�Ҋ��Ԓ��̓��ɂ̓��[�v���Ȃ��i�����փ��[�v�j
									$tIsland = $island;
								}
							}
							$tId   = $tIsland['id'];
							$tName = $tIsland['name'];
							
							// ���[�v�n�_�����߂�
							$tLand      = $tIsland['land'];
							$tLandValue = $tIsland['landValue'];
							for ($w = 0; $w < $init->pointNumber; $w++) {
								$bx = $this->rpx[$w];
								$by = $this->rpy[$w];
								// �C�A�D���A�C��A�C�h�A�C��s�s�A�C��s�s�A�C����h���A�{�B��A���c�A�`�A���b�A�R�A���炷�A�L�O��ȊO
								if(($tLand[$bx][$by] != $init->landSea) &&
									($tLand[$bx][$by] != $init->landShip) &&
									($tLand[$bx][$by] != $init->landSbase) &&
									($tLand[$bx][$by] != $init->landSdefence) &&
									($tLand[$bx][$by] != $init->landSeaCity) &&
									($tLand[$bx][$by] != $init->landFroCity) &&
									($tLand[$bx][$by] != $init->landSsyoubou) &&
									($tLand[$bx][$by] != $init->landSfarm) &&
									($tLand[$bx][$by] != $init->landNursery) &&
									($tLand[$bx][$by] != $init->landOil) &&
									($tLand[$bx][$by] != $init->landPort) &&
									($tLand[$bx][$by] != $init->landMountain) &&
									($tLand[$bx][$by] != $init->landMonument) &&
									($tLand[$bx][$by] != $init->landZorasu) &&
									($tLand[$bx][$by] != $init->landSleeper) &&
									($tLand[$bx][$by] != $init->landMonster)) {
									break;
								}
							}
							// ���[�v�I
							$this->log->monsWarp($id, $tId, $name, $mName, "({$x}, {$y})", $tName);
							$this->log->monsCome($tId, $tName, $mName, "($bx, $by)", $this->landName($tLand[$bx][$by], $tLandValue[$bx][$by]));
							
							if($id == $tId) {
								$land[$bx][$by]       = $init->landMonster;
								$landValue[$bx][$by]  = $lv;
							} else {
								$tLand[$bx][$by]      = $init->landMonster;
								$tLandValue[$bx][$by] = $lv;
							}
							$monsterMove[$bx][$bx] = 2;
							$land[$x][$y]      = $init->landWaste;
							$landValue[$x][$y] = 0;
							
							if($id != $tId) {
								// �^�[�Q�b�g���قȂ�ꍇ�́A�l��߂�
								$tIsland['land']      = $tLand;
								$tIsland['landValue'] = $tLandValue;
								$hako->islands[$tg]   = $tIsland;
							}
							break;
						} else {
							// ���[�v���Ȃ�
						}
					}
					if ($special & 0x400) {
						// �m���ɂȂ�Ƒ唚��
						if ($monsSpec['hp'] <= 1) { // �c��̗͂P�Ȃ�
							$point = "({$x}, {$y})";
							// �m���ɂȂ����̂Ŕ�������
							$this->log->MonsExplosion($id, $name, $point, $mName);
							// �L���Q���[�`��
							$this->wideDamage($id, $name, &$land, &$landValue, $x, $y);
							break;
						}
					}
					if ($special & 0x1000) {
						// �o�����͂����𑝂₵�Ă����
						$point = "({$x}, {$y})";
						$money = (Util::random(100) + 1); // 1���~�`100���~
						$island['money'] += $money;
						$str = "{$money}{$init->unitMoney}";
						$this->log->MonsMoney($id, $name, $mName, $point, "$str");
					}
					if ($special & 0x2000) {
						// �o�����͐H���𑝂₵�Ă����
						$point = "({$x}, {$y})";
						$food  = (Util::random(10) + 1); // 1000�g���`10000�g��
						$island['food'] += $food;
						$str = "{$food}{$init->unitFood}";
						
						$this->log->MonsFood($id, $name, $mName, $point, "$str");
					}
					if ($special & 0x4000) {
						// �o�����͂��������炵�Ă��܂�
						$point = "({$x}, {$y})";
						$money = (Util::random(100) + 1); // 1���~�`100���~
						$island['money'] -= $money;
						$str = "{$money}{$init->unitMoney}";
						$this->log->MonsMoney2($id, $name, $mName, $point, "$str");
					}
					if ($special & 0x10000) {
						// �o�����͐H���𕅂点�Ă��܂�
						$point = "({$x}, {$y})";
						$food  = (Util::random(10) + 1); // 1000�g���`10000�g��
						$island['food'] -= $food;
						$str = "{$food}{$init->unitFood}";
						$this->log->MonsFood2($id, $name, $mName, $point, "$str");
					}
					// ��������������
					for($j = 0; $j < 3; $j++) {
						$d = Util::random(6) + 1;
						if($special & 0x200){
							// ��s�ړ��\��
							$d = Util::random(12) + 7;
						}
						$sx = $x + $init->ax[$d];
						$sy = $y + $init->ay[$d];
						// �s�ɂ��ʒu����
						if((($sy % 2) == 0) && (($y % 2) == 1)) {
							$sx--;
						}
						// �͈͊O����
						if(($sx < 0) || ($sx >= $init->islandSize) ||
							($sy < 0) || ($sy >= $init->islandSize)) {
							continue;
						}
						// �C�A�D���A�C��A�C�h�A�C��s�s�A�C��s�s�A�C����h���A�{�B��A���c�A�`�A���b�A�R�A���炷�A�L�O��ȊO
						if(($land[$sx][$sy] != $init->landSea) &&
							($land[$sx][$sy] != $init->landShip) &&
							($land[$sx][$sy] != $init->landSbase) &&
							($land[$sx][$sy] != $init->landSdefence) &&
							($land[$sx][$sy] != $init->landSeaCity) &&
							($land[$sx][$sy] != $init->landFroCity) &&
							($land[$sx][$sy] != $init->landSsyoubou) &&
							($land[$sx][$sy] != $init->landSfarm) &&
							($land[$sx][$sy] != $init->landNursery) &&
							($land[$sx][$sy] != $init->landOil) &&
							($land[$sx][$sy] != $init->landPort) &&
							($land[$sx][$sy] != $init->landMountain) &&
							($land[$sx][$sy] != $init->landMonument) &&
							($land[$sx][$sy] != $init->landZorasu) &&
							($land[$sx][$sy] != $init->landSleeper) &&
							($land[$sx][$sy] != $init->landMonster)) {
							break;
						}
					}
					if($j == 3) {
						// �����Ȃ�����
						break;
					}
					// ��������̒n�`�ɂ�胁�b�Z�[�W
					$l = $land[$sx][$sy];
					$lv = $landValue[$sx][$sy];
					$lName = $this->landName($l, $lv);
					$point = "({$sx}, {$sy})";
					
					// �ړ�
					$land[$sx][$sy] = $land[$x][$y];
					$landValue[$sx][$sy] = $landValue[$x][$y];
					
					if (($special & 0x20000) && (Util::random(100) < 30)) { // ����m��30%
						// ���􂷂���b
						// ���Ƌ����ʒu�����b��
						$land[$bx][$by] = $init->landMonster;
						$landValue[$bx][$by] = $lv;
						// ���b���
						$monsSpec = Util::monsterSpec($lv);
						// ���b�Z�[�W
						$this->log->monsBunretu($id, $name, $lName, $point, $mName);
					} else {
						// ���Ƌ����ʒu���r�n��
						$land[$x][$y] = $init->landWaste;
						$landValue[$x][$y] = 0;
					}
					// �ړ��ς݃t���O
					if($init->monsterSpecial[$monsSpec['kind']] & 0x2) {
						// �ړ��ς݃t���O�͗��ĂȂ�
					} elseif($init->monsterSpecial[$monsSpec['kind']] & 0x1) {
						// �������b
						$monsterMove[$sx][$sy] = $monsterMove[$x][$y] + 1;
					} else {
						// ���ʂ̉��b
						$monsterMove[$sx][$sy] = 2;
					}
					if(($l == $init->landDefence) && ($init->dBaseAuto == 1)) {
						// �h�q�{�݂𓥂�
						$this->log->monsMoveDefence($id, $name, $lName, $point, $mName);
						
						// �L���Q���[�`��
						$this->wideDamage($id, $name, &$land, &$landValue, $sx, $sy);
					} else {
						// �s���悪�r�n�ɂȂ�
						if($island['isBF'] != 1)
						$this->log->monsMove($id, $name, $lName, $point, $mName);
					}
					break;
					
				case $init->landSleeper:
					// �ߊl���b
					// �e�v�f�̎��o��
					$monsSpec = Util::monsterSpec($landValue[$x][$y]);
					$special  = $init->monsterSpecial[$monsSpec['kind']];
					$mName    = $monsSpec['name'];
					if(Util::random(1000) < $monsSpec['hp'] * 10) {
						// (���b�̗̑� * 10)% �̊m���ŕߊl����
						$point = "({$x}, {$y})";
						$land[$x][$y] = $init->landMonster; // �ߊl����
						$this->log->MonsWakeup($id, $name, $lName, $point, $mName);
					}
					break;
					
				case $init->landShip:
					// �D��
					if($shipMove[$x][$y] != 1){
						//�D���܂������Ă��Ȃ���
						$ship = Util::navyUnpack($landValue[$x][$y]);
						$lName = $init->shipName[$ship[1]];
						$tLname .= "�i{$this->islands[$ship[0]]['name']}�������j";
						
						$tn = $hako->idToNumber[$ship[0]];
						$tIsland = &$hako->islands[$tn];
						$tName = $hako->idToName[$ship[0]];
						
						if($init->shipCost[$ship[1]] > $tIsland['money'] && $ship[0] != 0) {
							// �ێ���𕥂��Ȃ��Ȃ�C�̑����ƂȂ�
							$this->log->ShipRelease($id, $ship[0], $name, $tName, "($x,$y)", $init->shipName[$ship[1]]);
							$land[$x][$y] = $init->landSea;
							$landValue[$x][$y] = 0;
							break;
						}
						
						if($ship[1] == 2) {
							// �C��T���D
							$cntTreasure = Turn::countAroundValue($island, $x, $y, $init->landSea, 100, 7);
							if($cntTreasure) {
								// ����1�w�b�N�X�ȓ��ɍ��󂠂�
								for($s1 = 0; $s1 < 7; $s1++) {
									$sx = $x + $init->ax[$s1];
									$sy = $y + $init->ay[$s1];
									// �s�ɂ��ʒu����
									if((($sy % 2) == 0) && (($y % 2) == 1)) {
										$sx--;
									}
									if(($sx < 0) || ($sx >= $init->islandSize) || ($sy < 0) || ($sy >= $init->islandSize)) {
										// �͈͊O�̏ꍇ�������Ȃ�
										continue;
									} else {
										// �͈͓��̏ꍇ
										if($land[$sx][$sy] == $init->landSea && $landValue[$sx][$sy] >= 100) {
											// ���󔭌�
											if($ship[0] == $island['id']) {
												// ���������ł���΂����ɍ�����
												$island['money'] += $landValue[$sx][$sy];
											} else {
												// ���������ł���ΐύڂ��ċA�҂���܂ŉ�����Ȃ�
												$ship[3] = round($landValue[$sx][$sy] / 1000);
												$ship[4] = round(($landValue[$sx][$sy] - $ship[3] * 1000) /100);
												$landValue[$x][$y] = Util::navyPack($ship[0], $ship[1], $ship[2], $ship[3], $ship[4]);
											}
											$tName = $hako->idToName[$ship[0]];
											$this->log->FindTreasure($id, $ship[0], $name, $tName, "($x,$y)", $init->shipName[$ship[1]], $landValue[$sx][$sy]);
											
											// ���󂪂������n�`�͊C�ɂȂ�
											$land[$sx][$sy] = $init->landSea;
											$landValue[$sx][$sy] = 0;
											break;
										}
									}
								}
							}
						} elseif($ship[1] == 3) {
							// ���
							if($island['monster'] > 0 && $ship[4] != intval($hako->islandTurn) % 10) {
								// ���b���o�����Ă���A���݂̃^�[���Ŗ��U���̏ꍇ
								for($s2 = 0; $s2 < $init->pointNumber; $s2++) {
									$sx = $this->rpx[$s2];
									$sy = $this->rpy[$s2];
									if($land[$sx][$sy] == $init->landMonster || $land[$sx][$sy] == $init->landSleeper) {
										// �ΏۂƂȂ���b�̊e�v�f���o��
										$monsSpec = Util::monsterSpec($landValue[$sx][$sy]);
										$tLv = $landValue[$sx][$sy];
										$tspecial  = $init->monsterSpecial[$monsSpec['tkind']];
										$tmonsName = $monsSpec['name'];
										// �d����?
										if((($special & 0x4) && (($hako->islandTurn % 2) == 1)) ||
											(($special & 0x10) && (($hako->islandTurn % 2) == 0))) {
											// �d�����Ȃ���ʂȂ�
											break;
										}
										if($monsSpec['hp'] > 1){
											// �Ώۂ̗̑͂����炷
											$landValue[$sx][$sy]--;
										} else {
											// �Ώۂ̉��b���|��čr�n�ɂȂ�
											$land[$sx][$sy] = $init->landWaste;
											$landValue[$sx][$sy] = 0;
											
											// ����
											$value = $init->monsterValue[$monsSpec['kind']];
											if($value > 0) {
												$island['money'] += $value;
												$this->log->msMonMoney($id, $target, $tmonsName, $value);
											}
										}
										$tName = $hako->idToName[$ship[0]];
										$this->log->SenkanMissile($id, $ship[0], $name, $tName, $lName, "($x,$y)", "($sx,$sy)", $tmonsName);
										break;
									}
								}
								// 1�^�[����1�x�����U���ł��Ȃ�
								$ship[4] = intval($hako->islandTurn) % 10;
							} else {
							}
							// �C���D���o�����Ă����ꍇ�U������
							$cntViking = Turn::countAround($land, $x, $y, 19, array($init->landShip));
							if($cntViking && $ship[4] != intval($hako->islandTurn) % 10) {
								//����2�w�b�N�X�ȓ��ɑD������
								for($s3 = 0; $s3 < 19; $s3++) {
									$sx = $x + $init->ax[$s3];
									$sy = $y + $init->ay[$s3];
									// �s�ɂ��ʒu����
									if((($sy % 2) == 0) && (($y % 2) == 1)) {
										$sx--;
									}
									if(($sx < 0) || ($sx >= $init->islandSize) || ($sy < 0) || ($sy >= $init->islandSize)) {
										// �͈͊O�̏ꍇ�������Ȃ�
										continue;
									} else {
										// �͈͓��̏ꍇ
										if($land[$sx][$sy] == $init->landShip) {
											$tShip = Util::navyUnpack($landValue[$sx][$sy]);
											$tName = $hako->idToName[$ship[0]];
											$tshipName = $init->shipName[$tShip[1]];
											if($tShip[1] >= 10) {
												// �C���D�������ꍇ�U������
												$tShip[2] -= 2;
												if($tShip[2] <= 0) {
													// �C���D�𒾖v������
													$land[$sx][$sy] = $init->landSea;
													$this->log->SenkanAttack($id, $ship[0], $name, $tName, $init->shipName[$ship[1]], "($x,$y)", "($sx,$sy)", $tshipName);
													$this->log->BattleSinking($id, $tShip[0], $name, $tshipName, "($sx,$sy)");
													// 30%�̊m���ō���ɂȂ�
													$treasure = $tShip[3] * 1000 + $tShip[4] * 100;
													if(Util::random(100) < 30 && $treasure > 0) {
														$landValue[$sx][$sy] = $treasure;
														$this->log->VikingTreasure($id, $name, "($sx,$sy)");
													} else {
														$landValue[$sx][$sy] = 0;
													}
												} else {
													// �C���D�Ƀ_���[�W�^����
													$landValue[$sx][$sy] = Util::navyPack($tShip[0], $tShip[1], $tShip[2], $tShip[3], $tShip[4]);
													$this->log->SenkanAttack($id, $ship[0], $name, $tName, $init->shipName[$ship[1]], "($x,$y)", "($sx,$sy)", $tshipName);
												}
												break;
											}
										}
									}
								}
								// 1�^�[����1�x�����U���ł��Ȃ�
								$ship[4] = intval($hako->islandTurn) % 10;
							}
						} elseif($ship[1] >= 10) {
							// �C���D
							if(Util::random(1000) < $init->disVikingRob) {
								// ���D
								$vMoney = round(Util::random($island['money'])/10);
								$vFood  = round(Util::random($island['food'])/10);
								$island['money'] -= $vMoney;
								$island['food'] -= $vFood;
								$this->log->RobViking($island['id'], $island['name'], "($x,$y)", $init->shipName[$ship[1]], $vMoney, $vFood);
								
								// ������
								$treasure = $ship[3] * 1000 + $ship[4] * 100;
								$treasure += $vMoney;
								$ship[3] = $treasure / 1000;
								$ship[4] = ($treasure - $ship[1] * 1000) / 100;
								if($ship[3] > 32) $ship[3] = 32;
								// �C���D�X�e�[�^�X�X�V
								$landValue[$x][$y] = Util::navyPack($ship[0], $ship[1], $ship[2], $ship[3], $ship[4]);
							}
							// �U��
							$cntShip = Turn::countAround($land, $x, $y, 19, array($init->landPort, $init->landShip, $init->landFroCity));
							if($cntShip) {
								//����2�w�b�N�X�ȓ��ɍ`�܂��͑D���܂��͊C��s�s����
								if(Util::random(1000) < $init->disVikingAttack) {
									// �C���D�̏P��
									for($s4 = 0; $s4 < 19; $s4++) {
										$sx = $x + $init->ax[$s4];
										$sy = $y + $init->ay[$s4];
										// �s�ɂ��ʒu����
										if((($sy % 2) == 0) && (($y % 2) == 1)) {
											$sx--;
										}
										if(($sx < 0) || ($sx >= $init->islandSize) || ($sy < 0) || ($sy >= $init->islandSize)) {
											// �͈͊O�̏ꍇ�������Ȃ�
											continue;
										} else {
											// �͈͓��̏ꍇ
											if($land[$sx][$sy] == $init->landPort) {
												// �`�̏ꍇ�󐣂ɂȂ�
												$land[$sx][$sy] = $init->landSea;
												$landValue[$sx][$sy] = 1;
												$this->log->BattleSinking($id, 0, $name, $this->landName($init->landPort, 1), "($sx,$sy)");
												$this->log->VikingAttack($id, $id, $name, $name, $init->shipName[$ship[1]], "($x,$y)", "($sx,$sy)", $this->landName($init->landPort, 1));
												break;
											} elseif($land[$sx][$sy] == $init->landShip) {
												// �D���̏ꍇ
												$tShip = Util::navyUnpack($landValue[$sx][$sy]);
												$tName = $hako->idToName[$tShip[0]];
												$tshipName = $init->shipName[$tShip[1]];
												if($tShip[1] < 10) {
													// �C���D�̍U��
													$tShip[2] -= ($init->disVikingMinAtc + Util::random($init->disVikingMaxAtc - $init->disVikingMinAtc));
													if($tShip[2] <= 0) {
														// �D�����v
														$land[$sx][$sy] = $init->landSea;
														$landValue[$sx][$sy] = 0;
														$this->log->ShipSinking($id, $tShip[0], $name, $tName, $tshipName, "($sx,$sy)");
														break;
													} else {
														// �D���_���[�W
														$landValue[$sx][$sy] = Util::navyPack($tShip[0], $tShip[1], $tShip[2], $tShip[3], $tShip[4]);
														$this->log->VikingAttack($id, $tShip[0], $name, $tName, $init->shipName[$ship[1]], "($x,$y)", "($sx,$sy)", $tshipName);
														break;
													}
												}
											} elseif($land[$sx][$sy] == $init->landFroCity) {
												// �C��s�s�̏ꍇ�C�ɂȂ�
												$land[$sx][$sy] = $init->landSea;
												$landValue[$sx][$sy] = 0;
												$this->log->BattleSinking($id, 0, $name, $this->landName($init->landFroCity, 0), "($sx,$sy)");
												$this->log->VikingAttack($id, $id, $name, $name, $init->shipName[$ship[1]], "($x,$y)", "($sx,$sy)", $this->landName($init->landFroCity, 0));
												break;
											}
										}
									}
								}
							}
							if(Util::random(1000) < $init->disVikingAway) {
								// �C���D ����
								$land[$x][$y] = $init->landSea;
								$landValue[$x][$y] = 0;
								$this->log->VikingAway($id, $name, "($x,$y)");
								break;
							}
						}
						
						if ($landValue[$x][$y] != 0){
							// �D���܂����݂��Ă�����
							// ��������������
							for($s5 = 0; $s5 < 3; $s5++) {
								$d = Util::random(6) + 1;
								$sx = $x + $init->ax[$d];
								$sy = $y + $init->ay[$d];
								// �s�ɂ��ʒu����
								if((($sy % 2) == 0) && (($y % 2) == 1)) {
									$sx--;
								}
								// �͈͊O����
								if(($sx < 0) || ($sx >= $init->islandSize) ||
									($sy < 0) || ($sy >= $init->islandSize)) {
									continue;
								}
								// �C�ł���΁A��������������
								if(($land[$sx][$sy] == $init->landSea) && ($landValue[$sx][$sy] < 1)){
									break;
								}
							}
							if($s5 == 3) {
								// �����Ȃ�����
							} else {
								// �ړ�
								$land[$sx][$sy] = $land[$x][$y];
								$landValue[$sx][$sy] = Util::navyPack($ship[0], $ship[1], $ship[2], $ship[3], $ship[4]);
								if ($ship[1] == 2) {
									if((Util::random(100) < 7) && ($island['tenki'] == 1) &&
										($island['item'][18] == 1) && ($island['item'][19] != 1)) {
										// ���b�h�_�C������
										$island['item'][19] = 1;
										$this->log->RedFound($id, $name, '�Ԃ����');
									}
									// ���c������
									if (Util::random(100) < 3) {
										$lName = $init->shipName[$ship[1]];
										$island['oil']++;
										$land[$x][$y] = $init->landOil;
										$landValue[$x][$y] = 0;
										$this->log->tansakuoil($id, $name, $lName, $point);
									} else {
										// ���Ƌ����ʒu���C��
										$land[$x][$y] = $init->landSea;
										$landValue[$x][$y] = 0;
									}
								} else {
									// ���Ƌ����ʒu���C��
									$land[$x][$y] = $init->landSea;
									$landValue[$x][$y] = 0;
								}
								// �ړ��ς݃t���O
								if(Util::random(2)){
									$shipMove[$sx][$sy] = 1;
								}
							}
						}
					}
					break;
			}
			// ���ł�$init->landTown��case���Ŏg���Ă���̂�switch��ʂɗp��
			switch($landKind) {
				case $init->landTown:
				case $init->landHaribote:
				case $init->landFactory:
				case $init->landHatuden:
				case $init->landPark:
				case $init->landSeaResort:
				case $init->landSyoubou:
				case $init->landSsyoubou:
				case $init->landSeaCity:
				case $init->landFroCity:
				case $init->landNewtown:
				case $init->landBigtown:
					// �΍Д���
					if (Turn::countAround($land, $x, $y, 19, array($init->landSyoubou, $init->landSsyoubou)) > 0) {
						break;
					}
					if ((($landKind == $init->landSeaResort) && ($lv <= 30)) ||
						($landKind == $init->landFactory && ($lv >= 100)) ||
						($landKind == $init->landHatuden && ($lv >= 100)) ||
						($landKind == $init->landTown && ($lv <= 30))) {
						break;
					}
					if(Util::random(1000) < $init->disFire - (int)($island['eisei'][0] / 20)) {
						// ���͂̐X�ƋL�O��𐔂���
						if(Turn::countAround($land, $x, $y, 7, array($init->landForest, $init->landProcity, $init->landFusya, $init->landMonument)) == 0) {
							// ���������ꍇ�A�΍Ђŉ��
							$l = $land[$x][$y];
							$lv = $landValue[$x][$y];
							$point = "({$x}, {$y})";
							$lName = $this->landName($l, $lv);
							if(($landKind == $init->landNewtown) || ($landKind == $init->landBigtown)) {
								// �j���[�^�E���A����s�s�̏ꍇ
								$landValue[$x][$y] -= Util::random(100) + 50;
								$this->log->firenot($id, $name, $lName, $point);
								if($landValue[$x][$y] <= 0) {
									$land[$x][$y] = $init->landWaste;
									$landValue[$x][$y] = 0;
									$this->log->fire($id, $name, $lName, $point);
								}
							} elseif(($landKind == $init->landSeaCity) || ($landKind == $init->landFroCity)) {
								$land[$x][$y] = $init->landSea;
								$landValue[$x][$y] = 0;
								$this->log->fire($id, $name, $lName, $point);
							} else {
								$land[$x][$y] = $init->landWaste;
								$landValue[$x][$y] = 0;
								$this->log->fire($id, $name, $lName, $point);
							}
						}
					}
					break;
			}
		}
		// �ύX���ꂽ�\���̂���ϐ��������߂�
		$island['land'] = $land;
		$island['landValue'] = $landValue;
	}
	
	//---------------------------------------------------
	// ���S��
	//---------------------------------------------------
	function doIslandProcess($hako, &$island) {
		global $init;
	    
		// ���o�l
		$name = $island['name'];
		$id = $island['id'];
		$land = $island['land'];
		$landValue = $island['landValue'];
		
		// �������O
	    if($island['oilincome'] > 0) {
			$this->log->oilMoney($id, $name, "�C����c", "", "���z{$island['oilincome']}{$init->unitMoney}");
		}
		// �������O
		if($island['bank'] > 0) {
			$value = (int)($island['money'] * 0.005);
			$island['money'] += $value;
			$this->log->oilMoney($id, $name, "��s", "", "���z{$value}{$init->unitMoney}");
		}
		// �V�C����
		if($island['tenki'] > 0) {
			if(Util::random(100) < 5) {
				$island['tenki'] = 5;
			} elseif(Util::random(100) < 10) {
				$island['tenki'] = 4;
			} elseif(Util::random(100) < 15) {
				$island['tenki'] = 3;
			} elseif(Util::random(100) < 20) {
				$island['tenki'] = 2;
			} else {
				$island['tenki'] = 1;
			}
		} else {
			$island['tenki'] = 1;
		}
		
		// ���Ƃ蔻��
		if((Util::random(1000) < $init->disTenki) && ($island['tenki'] == 1)) {
			// ���Ƃ蔭��
			$this->log->Hideri($id, $name);
			for($i = 0; $i < $init->pointNumber; $i++) {
				$x = $this->rpx[$i];
				$y = $this->rpy[$i];
				$landKind = $land[$x][$y];
				$lv = $landValue[$x][$y];
				if(($landKind == $init->landTown) && ($landValue[$x][$y] > 100)) {
					// �l��������
					$people = (Util::random(2) + 1);
					$landValue[$x][$y] -= $people;
				}
			}
		}
		
		// �ɂ킩�J����
		if((Util::random(1000) < $init->disTenki) && ($island['tenki'] == 3)) {
			// �ɂ킩�J����
			$this->log->Niwakaame($id, $name);
			for($i = 0; $i < $init->pointNumber; $i++) {
				$x = $this->rpx[$i];
				$y = $this->rpy[$i];
				$landKind = $land[$x][$y];
				$lv = $landValue[$x][$y];
				if($landKind == $init->landForest) {
					// �؂�������
					$tree = (Util::random(5) + 1);
					$landValue[$x][$y] += $tree;
					if($landValue[$x][$y] > 200) {
						$landValue[$x][$y] = 200;
					}
				}
			}
		}
		
		// �󂭂�����
		if(($hako->islandTurn % $init->lottery) == 0) {
			if((Util::random(500) < $island['lot']) && ($island['lot'] > 0)) {
				// �����܂ɓ��I���邩�H
				$syo   = Util::random(2) + 1;
				$value = $init->lotmoney / $syo;
				$island['money'] += $value;
				$str = "{$value}{$init->unitMoney}";
				// �������O
				$this->log->LotteryMoney($id, $name, $str, $syo);
			}
			// �󂭂��̖������Z�b�g
			$island['lot'] = 0;
		}
		
		// �\������
		$unemployed   = ($island['pop'] - ($island['farm'] + $island['factory'] + $island['commerce'] + $island['mountain'] + $island['hatuden']) * 10) / $island['pop'] * 100;
		if (($island['isBF'] != 1) && (Util::random(1000) < $unemployed) && ($unemployed > $init->disPoo) && ($island['pop'] >= $init->disPooPop)) {
			// �\������
			$this->log->pooriot($id, $name);
			for($i = 0; $i < $init->pointNumber; $i++) {
				$x = $this->rpx[$i];
				$y = $this->rpy[$i];
				$landKind = $land[$x][$y];
				$lv = $landValue[$x][$y];
				if(($landKind == $init->landTown) ||
					($landKind == $init->landSeaCity) ||
					($landKind == $init->landNewtown) ||
					($landKind == $init->landBigtown) ||
					($landKind == $init->landProcity) ||
					($landKind == $init->landFroCity)) {
					// 1/4�Ől��������
					if(Util::random(4) == 0) {
						$landValue[$x][$y] -= Util::random($unemployed);
						if ($landValue[$x][$y]  > 0) {
							// �l����
							$this->log->riotDamage1($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
						} else {
							// ���
							if ($landKind == $init->landSeaCity || $landKind == $init->landFroCity) {
								$land[$x][$y] = $init->landSea;
							} else {
								$land[$x][$y] = $init->landWaste;
							}
							$landValue[$x][$y] = 0;
							$this->log->riotDamage2($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
						}
					}
				}
			}
		}
		
		// �n�k����
		if ((Util::random(1000) < (($island['prepare2'] + 1) * $init->disEarthquake) - (int)($island['eisei'][1] / 15))
			|| ($island['present']['item'] == 1)) {
			// �n�k����
			$this->log->earthquake($id, $name);
			for($i = 0; $i < $init->pointNumber; $i++) {
				$x = $this->rpx[$i];
				$y = $this->rpy[$i];
				$landKind = $land[$x][$y];
				$lv = $landValue[$x][$y];
				if((($landKind == $init->landTown) && ($lv >= 100)) ||
					(($landKind == $init->landProcity) && ($lv < 130)) ||
					(($landKind == $init->landSfarm) && ($lv < 20)) ||
					(($landKind == $init->landFactory) && ($lv < 100)) ||
					(($landKind == $init->landHatuden) && ($lv < 100)) ||
					($landKind == $init->landHaribote) ||
					($landKind == $init->landSeaResort) ||
					($landKind == $init->landSeaSide) ||
					($landKind == $init->landSeaCity) ||
					($landKind == $init->landFroCity)) {
					// 1/4�ŉ��
					if(Util::random(4) == 0) {
						$this->log->eQDamage($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
						if(($landKind == $init->landSeaCity) || ($landKind == $init->landFroCity) ||
							($landKind == $init->landSfarm)) {
							$land[$x][$y] = $init->landSea;
						} else {
							$land[$x][$y] = $init->landWaste;
						}
						$landValue[$x][$y] = 0;
					}
				}
				if((($landKind == $init->landBigtown) && ($lv >= 100)) ||
				(($landKind == $init->landNewtown) && ($lv >= 100))) {
					// 1/3�ŉ��
					if(Util::random(3) == 0) {
						$this->log->eQDamagenot($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
						$landValue[$x][$y] -= Util::random(100) + 50;
					}
					if($landValue[$x][$y] <= 0) {
						$land[$x][$y] = $init->landWaste;
						$landValue[$x][$y] = 0;
						$this->log->eQDamage($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
						continue;
					}
				}
			}
		}
		
		// �H���s��
		if($island['food'] <= 0) {
			// �s�����b�Z�[�W
			$this->log->starve($id, $name);
			$island['food'] = 0;
			for($i = 0; $i < $init->pointNumber; $i++) {
				$x = $this->rpx[$i];
				$y = $this->rpy[$i];
				$landKind = $land[$x][$y];
				$lv = $landValue[$x][$y];
				if(($landKind == $init->landFarm) ||
					($landKind == $init->landSfarm) ||
					($landKind == $init->landFactory) ||
					($landKind == $init->landCommerce) ||
					($landKind == $init->landHatuden) ||
					($landKind == $init->landBase) ||
					($landKind == $init->landDefence)) {
					// 1/4�ŉ��
					if(Util::random(4) == 0) {
						$this->log->svDamage($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
						$land[$x][$y] = $init->landWaste;
						$landValue[$x][$y] = 0;
						// �ł��{�B��Ȃ��
						if($landKind == $init->landNursery) {
							$land[$x][$y] = $init->landSea;
							$landValue[$x][$y] = 1;
						} elseif($landKind == $init->landSfarm) {
							$land[$x][$y] = $init->landSea;
							$landValue[$x][$y] = 0;
						}
					}
				}
			}
		}
		
		// ���ʔ���
		if(Util::random(1000) < $init->disRunAground1){
			for($i = 0; $i < $init->pointNumber; $i++) {
				$x = $this->rpx[$i];
				$y = $this->rpy[$i];
				$landKind = $land[$x][$y];
				$lv = $landValue[$x][$y];
				if(($landKind == $init->landShip) && (Util::random(1000) < $init->disRunAground2)){
					$this->log->RunAground($id, $name, $this->landName($landKind, $lv), "($x,$y)");
					$land[$x][$y] = $init->landSea;
					$landValue[$x][$y] = 0;
				}
			}
		}
		
		// �C���D����
		$ownShip = 0;
		for($i = 0; $i < 10; $i++) {
			$ownShip += $island['ship'][$i];
		}
		if(Util::random(1000) < $init->disViking * $ownShip){
			// �ǂ��Ɍ���邩���߂�
			for($i = 0; $i < $init->pointNumber; $i++) {
				$x = $this->rpx[$i];
				$y = $this->rpy[$i];
				$landKind = $land[$x][$y];
				$lv = $landValue[$x][$y];
				if(($landKind == $init->landSea) && ($lv == 0)) {
					// �C���D�o��
					$land[$x][$y] = $init->landShip;
					$landValue[$x][$y] = Util::navyPack(0, 10, $init->shipHP[10], 0, 0);
					$this->log->VikingCome($id, $name, "($x,$y)");
					break;
				}
			}
		}
		
		// �d�Ԕ���
		if((Util::random(1000) < $init->disTrain) && ($island['stat'] >= 2) &&
			($island['train'] < $island['stat']) && ($island['pop'] >= 2000)) {
			// �ǂ��Ɍ���邩���߂�
			for($i = 0; $i < $init->pointNumber; $i++) {
				$bx = $this->rpx[$i];
				$by = $this->rpy[$i];
				$landKind = $land[$bx][$by];
				$lv = $landValue[$bx][$by];
				if($landKind == $init->landRail) {
					// �d�ԓo��
					$land[$bx][$by] = $init->landTrain;
					break;
				}
			}
		}
		
		// ���炷����
		if($island['money'] >= 10000) {
			$smo = Util::random(800);
		} else {
			$smo = Util::random(1000);
		}
		if(($smo < $init->disZorasu) && ($island['taiji'] >= 50) && ($island['pop'] >= 2000)) {
			// �ǂ��Ɍ���邩���߂�
			for($i = 0; $i < $init->pointNumber; $i++) {
				$bx = $this->rpx[$i];
				$by = $this->rpy[$i];
				$landKind = $land[$bx][$by];
				$lv = $landValue[$bx][$by];
				if(($landKind == $init->landSea) && ($lv == 0)) {
					// ���炷�o��
					$land[$bx][$by] = $init->landZorasu;
					$landValue[$bx][$by] = Util::random(200);
					
					$this->log->ZorasuCome($id, $name, "($x,$y)");
					break;
				}
			}
		}
		
		// �Ôg����
		if ((Util::random(1000) < $init->disTsunami - (int)($island['eisei'][1] / 15))
			|| ($island['present']['item'] == 2)) {
			// �Ôg����
			$this->log->tsunami($id, $name);
			
			for($i = 0; $i < $init->pointNumber; $i++) {
				$x = $this->rpx[$i];
				$y = $this->rpy[$i];
				$landKind = $land[$x][$y];
				$lv = $landValue[$x][$y];
				if(($landKind == $init->landTown) ||
					(($landKind == $init->landProcity) && ($lv < 110)) ||
					($landKind == $init->landNewtown) ||
					($landKind == $init->landBigtown) ||
					(($landKind == $init->landFarm) && ($lv < 25)) ||
					($landKind == $init->landNursery) ||
					($landKind == $init->landFactory) ||
					($landKind == $init->landHatuden) ||
					($landKind == $init->landBase) ||
					($landKind == $init->landDefence) ||
					($landKind == $init->landSeaSide)  ||
					($landKind == $init->landSeaResort)||
					($landKind == $init->landPort)     ||
					($landKind == $init->landShip)   ||
					($landKind == $init->landHaribote)) {
					// 1d12 <= (���͂̊C - 1) �ŕ���
					if(Util::random(12) <
						(Turn::countAround($land, $x, $y, 7, array($init->landOil, $init->landSbase, $init->landSea)) - 1)) {
						$this->log->tsunamiDamage($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
						if (($landKind == $init->landSeaSide)||
							($landKind == $init->landNursery)||
							($landKind == $init->landPort)){
							//���l���{�B�ꂩ�`�Ȃ�󐣂�
							$land[$x][$y] = $init->landSea;
							$landValue[$x][$y] = 1;
						} elseif($landKind == $init->landShip){
							//�D�Ȃ琅�v�A�C��
							$land[$x][$y] = $init->landSea;
							$landValue[$x][$y] = 0;
						} else {
							$land[$x][$y] = $init->landWaste;
							$landValue[$x][$y] = 0;
						}
					}
				}
			}
		}
		
		// ���b����
		if($island['isBF'] == 1) {
			$r = Util::random(500);
			$pop = $island['pop'];
		} else {
			$r = Util::random(10000);
			$pop = $island['pop'];
		}
		$isMons = (($island['present']['item'] == 3) && ($pop >= $init->disMonsBorder1));
		
		do{
			if((($r < ($init->disMonster * $island['area'])) &&
				($pop >= $init->disMonsBorder1)) || ($isMons) || ($island['monstersend'] > 0)) {
				// ���b�o��
				// ��ނ����߂�
				if($island['monstersend'] > 0) {
					// �l��
					$kind = 0;
					$island['monstersend']--;
				} elseif($pop >= $init->disMonsBorder5) {
					// level5�܂�
					$kind = Util::random($init->monsterLevel5) + 1;
				} elseif($pop >= $init->disMonsBorder4) {
					// level4�܂�
					$kind = Util::random($init->monsterLevel4) + 1;
				} elseif($pop >= $init->disMonsBorder3) {
					// level3�܂�
					$kind = Util::random($init->monsterLevel3) + 1;
				} elseif($pop >= $init->disMonsBorder2) {
					// level2�܂�
					$kind = Util::random($init->monsterLevel2) + 1;
				} else {
					// level1�̂�
					$kind = Util::random($init->monsterLevel1) + 1;
				}
				// lv�̒l�����߂�
				$lv = $kind * 100
					+ $init->monsterBHP[$kind] + Util::random($init->monsterDHP[$kind]);
				
				// �ǂ��Ɍ���邩���߂�
				for($i = 0; $i < $init->pointNumber; $i++) {
					$bx = $this->rpx[$i];
					$by = $this->rpy[$i];
					if(($land[$bx][$by] == $init->landTown) ||
						($land[$bx][$by] == $init->landBigtown) ||
						($land[$bx][$by] == $init->landNewtown)) {
						// �n�`��
						$lName = $this->landName($init->landTown, $landValue[$bx][$by]);
						// ���̃w�b�N�X�����b��
						$land[$bx][$by] = $init->landMonster;
						$landValue[$bx][$by] = $lv;
						// ���b���
						$monsSpec = Util::monsterSpec($lv);
						$mName = $monsSpec['name'];
						// ���b�Z�[�W
						$this->log->monsCome($id, $name, $mName, "({$bx}, {$by})", $lName);
						break;
					}
				}
			}
		} while($island['monstersend'] > 0);
		
		// �n�Ւ�������
		if(($island['area'] > $init->disFallBorder) && 
			((Util::random(1000) < $init->disFalldown) && ($island['isBF'] != 1) || 
			($island['present']['item'] == 4))) {
			$this->log->falldown($id, $name);
			for($i = 0; $i < $init->pointNumber; $i++) {
				$x = $this->rpx[$i];
				$y = $this->rpy[$i];
				$landKind = $land[$x][$y];
				$lv = $landValue[$x][$y];
				if(($landKind != $init->landSea) &&
					($landKind != $init->landSbase) &&
					($landKind != $init->landSdefence) &&
					($landKind != $init->landSfarm) &&
					($landKind != $init->landOil) &&
					($landKind != $init->landMountain)) {
					// ���͂ɊC������΁A�l��-1��
					if(Turn::countAround($land, $x, $y, 7, array($init->landSea, $init->landSbase))) {
						$land[$x][$y] = -1;
						$landValue[$x][$y] = 0;
						$this->log->falldownLand($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
					}
				}
			}
			
			for($i = 0; $i < $init->pointNumber; $i++) {
				$x = $this->rpx[$i];
				$y = $this->rpy[$i];
				$landKind = $land[$x][$y];
				if($landKind == -1) {
					// -1�ɂȂ��Ă��鏊��󐣂�
					$land[$x][$y] = $init->landSea;
					$landValue[$x][$y] = 1;
				} elseif ($landKind == $init->landSea) {
					// �󐣂͊C��
					$landValue[$x][$y] = 0;
				}
			}
		}
		
		// �䕗����
		if ((Util::random(1000) < ($init->disTyphoon - (int)($island['eisei'][0] / 10))) && (($island['tenki'] == 2) || ($island['tenki'] == 3))
			|| ($island['present']['item'] == 5)) {
			// �䕗����
			$this->log->typhoon($id, $name);
			for($i = 0; $i < $init->pointNumber; $i++) {
				$x = $this->rpx[$i];
				$y = $this->rpy[$i];
				$landKind = $land[$x][$y];
				$lv = $landValue[$x][$y];
				if((($landKind == $init->landFarm) && ($lv < 25)) ||
					(($landKind == $init->landSfarm) && ($lv < 20)) ||
					($landKind == $init->landNursery) ||
					($landKind == $init->landSeaSide) ||
					($landKind == $init->landHaribote)) {
					// 1d12 <= (6 - ���͂̐X) �ŕ���
					if(Util::random(12) <
						(6 - Turn::countAround($land, $x, $y, 7, array($init->landForest, $init->landFusy, $init->landMonument)))) {
						$this->log->typhoonDamage($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
						if (($landKind == $init->landSeaSide)||($landKind == $init->landNursery)){
							//���l���{�B��Ȃ�͐�
							$land[$x][$y] = $init->landSea;
							$landValue[$x][$y] = 1;
						} elseif ($landKind == $init->landSfarm) {
							$land[$x][$y] = $init->landSea;
							$landValue[$x][$y] = 0;
						} else {
							//���̑��͕��n��
							$land[$x][$y] = $init->landPlains;
							$landValue[$x][$y] = 0;
						}
					}
				}
			}
		}
		
		// ����覐Δ���
		if (((Util::random(1000) < ($init->disHugeMeteo - (int)($island['eisei'][2] / 50))) && ($island['id'] != 1))
			|| ($island['present']['item'] == 6)) {
			// ����
			if ( $island['present']['item'] == 6 ) {
				$x = $island['present']['px'];
				$y = $island['present']['py'];
			} else {
				$x = Util::random($init->islandSize);
				$y = Util::random($init->islandSize);
			}
			$landKind = $land[$x][$y];
			$lv = $landValue[$x][$y];
			$point = "({$x}, {$y})";
			// ���b�Z�[�W
			$this->log->hugeMeteo($id, $name, $point);
			// �L���Q���[�`��
			$this->wideDamage($id, $name, &$land, &$landValue, $x, $y);
		}
		
		// ����~�T�C������
		while($island['bigmissile'] > 0) {
			$island['bigmissile']--;
			// �Ƒ��̗�
			for($i = 0; $i < $init->pointNumber; $i++) {
				$x = $this->rpx[$i];
				$y = $this->rpy[$i];
				$landKind = $land[$x][$y];
				$lv = $landValue[$x][$y];
				if(($landKind == $init->landMyhome) && (Util::random(100) < ($lv * 10))) {
					// ����������Ƃ�
					$power = 1;
					if($lv > 1) {
						// �Ƒ����P�l����
						$landValue[$x][$y]--;
						$this->log->kazokuPower($id, $name, "�Ƒ��̗�");
						break;
					} else {
						// �S�Łc
						$land[$x][$y] = $init->landWaste;
						$landValue[$x][$y] = 0;
						
						$this->log->kazokuPower($id, $name, "�Ɛg�̒��");
						break;
					}
				}
			}
			
			if($power != 1) {
				// ����
				$x = Util::random($init->islandSize);
				$y = Util::random($init->islandSize);
				$landKind = $land[$x][$y];
				$lv = $landValue[$x][$y];
				$point = "({$x}, {$y})";
				// ���b�Z�[�W
				$this->log->monDamage($id, $name, $point);
				// �L���Q���[�`��
				$this->wideDamage($id, $name, &$land, &$landValue, $x, $y);
			}
		}
		
		// 覐Δ���
		if ((Util::random(1000) < ($init->disMeteo - (int)($island['eisei'][2] / 40)))
			|| ($island['present']['item'] == 7)) {
			$first = 1;
			while((Util::random(2) == 0) || ($first == 1)) {
				$first = 0;
				// ����
				if (($island['present']['item'] == 7) && ($first == 1)) {
					$x = $island['present']['px'];
					$y = $island['present']['py'];
				} else {
					$x = Util::random($init->islandSize);
					$y = Util::random($init->islandSize);
				}
				$first = 0;
				$landKind = $land[$x][$y];
				$lv = $landValue[$x][$y];
				$point = "({$x}, {$y})";
				
				if(($landKind == $init->landSea) && ($lv == 0)){
					// �C�|�`��
					$this->log->meteoSea($id, $name, $this->landName($landKind, $lv), $point);
				} elseif($landKind == $init->landMountain) {
					// �R�j��
					$this->log->meteoMountain($id, $name, $this->landName($landKind, $lv), $point);
					$land[$x][$y] = $init->landWaste;
					
					$landValue[$x][$y] = 0;
					continue;
				} elseif(($landKind == $init->landSbase) || ($landKind == $init->landSfarm) ||
					($landKind == $init->landSeaCity) || ($landKind == $init->landFroCity) ||
					($landKind == $init->landSdefence)) {
					$this->log->meteoSbase($id, $name, $this->landName($landKind, $lv), $point);
				} elseif(($landKind == $init->landMonster) || ($landKind == $init->landSleeper)) {
					$this->log->meteoMonster($id, $name, $this->landName($landKind, $lv), $point);
				} elseif($landKind == $init->landSea) {
					// ��
					$this->log->meteoSea1($id, $name, $this->landName($landKind, $lv), $point);
				} else {
					$this->log->meteoNormal($id, $name, $this->landName($landKind, $lv), $point);
				}
				$land[$x][$y] = $init->landSea;
				$landValue[$x][$y] = 0;
			}
		}
		
		// ���Δ���
		if ((Util::random(1000) < ($init->disEruption - (int)($island['eisei'][1] / 40)))
			|| ($island['present']['item'] == 8)) {
			if ( $island['present']['item'] == 8 ) {
				$x = $island['present']['px'];
				$y = $island['present']['py'];
			} else {
				$x = Util::random($init->islandSize);
				$y = Util::random($init->islandSize);
			}
			$landKind = $land[$x][$y];
			$lv = $landValue[$x][$y];
			$point = "({$x}, {$y})";
			$this->log->eruption($id, $name, $this->landName($landKind, $lv), $point);
			$land[$x][$y] = $init->landMountain;
			$landValue[$x][$y] = 0;
			
			for($i = 1; $i < 7; $i++) {
				$sx = $x + $init->ax[$i];
				$sy = $y + $init->ay[$i];
				// �s�ɂ��ʒu����
				if((($sy % 2) == 0) && (($y % 2) == 1)) {
					$sx--;
				}
				$landKind = $land[$sx][$sy];
				$lv = $landValue[$sx][$sy];
				$point = "({$sx}, {$sy})";
				
				if(($sx < 0) || ($sx >= $init->islandSize) ||
					($sy < 0) || ($sy >= $init->islandSize)) {
				} else {
					// �͈͓��̏ꍇ
					$landKind = $land[$sx][$sy];
					$lv = $landValue[$sx][$sy];
					$point = "({$sx}, {$sy})";
						if(($landKind == $init->landSea) ||
						($landKind == $init->landOil) ||
						($landKind == $init->landSeaCity) ||
						($landKind == $init->landFroCity) ||
						($landKind == $init->landSsyoubou) ||
						($landKind == $init->landSfarm) ||
						($landKind == $init->landSdefence) ||
						($landKind == $init->landSbase)) {
						// �C�̏ꍇ
						if($lv == 1) {
							// ��
							$this->log->eruptionSea1($id, $name, $this->landName($landKind, $lv), $point);
						} else {
							$land[$sx][$sy] = $init->landSea;
							$landValue[$sx][$sy] = 1;
							
							$this->log->eruptionSea($id, $name, $this->landName($landKind, $lv), $point);
							continue;
						}
					} elseif(($landKind == $init->landMountain) ||
						($landKind == $init->landMonster) ||
						($landKind == $init->landSleeper) ||
						($landKind == $init->landWaste)) {
						continue;
					} else {
						// ����ȊO�̏ꍇ
						$this->log->eruptionNormal($id, $name, $this->landName($landKind, $lv), $point);
					}
					$land[$sx][$sy] = $init->landWaste;
					$landValue[$sx][$sy] = 0;
				}
			}
		}
		
		// �l�H�q���G�l���M�[����
		for($i = 0; $i < 6; $i++) {
			if($island['eisei'][$i]) {
				$island['eisei'][$i] -= Util::random(2);
				if($island['eisei'][$i] < 1) {
					$island['eisei'][$i] = 0;
					$this->log->EiseiEnd($id, $name, $init->EiseiName[$i]);
				}
			}
		}
		
		// �ύX���ꂽ�\���̂���ϐ��������߂�
		$island['land'] = $land;
		$island['landValue'] = $landValue;
		
		$island['gold'] = $island['money'] - $island['oldMoney'];
		$island['rice'] = $island['food'] - $island['oldFood'];
		
		// �H�������ӂ�Ă��犷��
		if($island['food'] > $init->maxFood) {
			$island['money'] += round(($island['food'] - $init->maxFood) / 10);
			$island['food'] = $init->maxFood;
		}
		// �������ӂ�Ă���؂�̂�
		if($island['money'] > $init->maxMoney) {
			$island['money'] = $init->maxMoney;
		}
		// �e��̒l���v�Z
		Turn::estimate($hako, $island);
		
		// �ɉh�A�Г��
		$pop = $island['pop'];
		$damage = $island['oldPop'] - $pop;
		$prize = $island['prize'];
		list($flags, $monsters, $turns) = split(",", $prize, 3);
		$island['peop'] = $island['pop'] - $island['oldPop'];
		$island['pots'] = $island['point'] - $island['oldPoint'];
		
		// �ɉh��
		if((!($flags & 1)) &&  $pop >= 3000){
			$flags |= 1;
			$this->log->prize($id, $name, $init->prizeName[1]);
		} elseif((!($flags & 2)) &&  $pop >= 5000){
			$flags |= 2;
			$this->log->prize($id, $name, $init->prizeName[2]);
		} elseif((!($flags & 4)) &&  $pop >= 10000){
			$flags |= 4;
			$this->log->prize($id, $name, $init->prizeName[3]);
		}
		// �Г��
		if((!($flags & 64)) &&  $damage >= 500){
			$flags |= 64;
			$this->log->prize($id, $name, $init->prizeName[7]);
		} elseif((!($flags & 128)) &&  $damage >= 1000){
			$flags |= 128;
			$this->log->prize($id, $name, $init->prizeName[8]);
		} elseif((!($flags & 256)) &&  $damage >= 2000){
			$flags |= 256;
			$this->log->prize($id, $name, $init->prizeName[9]);
		}
		$island['prize'] = "{$flags},{$monsters},{$turns}";
	}
	
	//---------------------------------------------------
	// ���͂̒��A�_�ꂪ���邩����
	//---------------------------------------------------
	function countGrow($land, $landValue, $x, $y) {
		global $init;
		
		for($i = 1; $i < 7; $i++) {
			$sx = $x + $init->ax[$i];
			$sy = $y + $init->ay[$i];
			// �s�ɂ��ʒu����
			if((($sy % 2) == 0) && (($y % 2) == 1)) {
				$sx--;
			}
			if(($sx < 0) || ($sx >= $init->islandSize) ||
				($sy < 0) || ($sy >= $init->islandSize)) {
			} else {
				// �͈͓��̏ꍇ
				if(($land[$sx][$sy] == $init->landTown) ||
					($land[$sx][$sy] == $init->landProcity) ||
					($land[$sx][$sy] == $init->landNewtown) ||
					($land[$sx][$sy] == $init->landBigtown) ||
					($land[$sx][$sy] == $init->landFarm)) {
					if($landValue[$sx][$sy] != 1) {
						return true;
					}
				}
			}
		}
		return false;
	}
	
	//---------------------------------------------------
	// �L���Q���[�`��
	//---------------------------------------------------
	function wideDamage($id, $name, $land, $landValue, $x, $y) {
		global $init;
		
		for($i = 0; $i < 19; $i++) {
			$sx = $x + $init->ax[$i];
			$sy = $y + $init->ay[$i];
			// �s�ɂ��ʒu����
			if((($sy % 2) == 0) && (($y % 2) == 1)) {
				$sx--;
			}
			$landKind = $land[$sx][$sy];
			$lv = $landValue[$sx][$sy];
			$landName = $this->landName($landKind, $lv);
			$point = "({$sx}, {$sy})";
			// �͈͊O����
			if(($sx < 0) || ($sx >= $init->islandSize) ||
				($sy < 0) || ($sy >= $init->islandSize)) {
				continue;
			}
			// �͈͂ɂ�镪��
			if($i < 7) {
				// ���S�A�����1�w�b�N�X
				if($landKind == $init->landSea) {
					$landValue[$sx][$sy] = 0;
					continue;
				} elseif(($landKind == $init->landSbase) ||
					($landKind == $init->landSeaSide) ||
					($landKind == $init->landSdefence) ||
					($landKind == $init->landSeaCity) ||
					($landKind == $init->landFroCity) ||
					($landKind == $init->landSsyoubou) ||
					($landKind == $init->landSfarm) ||
					($landKind == $init->landZorasu) ||
					($landKind == $init->landOil)) {
					$land[$sx][$sy] = $init->landSea;
					$landValue[$sx][$sy] = 0;
					$this->log->wideDamageSea2($id, $name, $landName, $point);
				} else {
					if(($landKind == $init->landMonster) || ($landKind == $init->landSleeper)) {
						$this->log->wideDamageMonsterSea($id, $name, $landName, $point);
					} else {
						$this->log->wideDamageSea($id, $name, $landName, $point);
					}
					$land[$sx][$sy] = $init->landSea;
					if($i == 0) {
						// �C
						$landValue[$sx][$sy] = 0;
					} else {
						// ��
						$landValue[$sx][$sy] = 1;
					}
				}
			} else {
				// 2�w�b�N�X
				if(($landKind == $init->landSea) ||
					($landKind == $init->landSeaSide) ||
					($landKind == $init->landSeaCity) ||
					($landKind == $init->landFroCity) ||
					($landKind == $init->landSsyoubou) ||
					($landKind == $init->landSfarm) ||
					($landKind == $init->landZorasu) ||
					($landKind == $init->landOil) ||
					($landKind == $init->landSdefence) ||
					($landKind == $init->landWaste) ||
					($landKind == $init->landMountain) ||
					($landKind == $init->landSbase)) {
					continue;
				} elseif(($landKind == $init->landMonster) || ($landKind == $init->landSleeper)) {
					$land[$sx][$sy] = $init->landWaste;
					$landValue[$sx][$sy] = 0;
					$this->log->wideDamageMonster($id, $name, $landName, $point);
				} else {
					$land[$sx][$sy] = $init->landWaste;
					$landValue[$sx][$sy] = 0;
					$this->log->wideDamageWaste($id, $name, $landName, $point);
				}
			}
		}
	}
	
	//---------------------------------------------------
	// �l�����Ń\�[�g
	//---------------------------------------------------
	function islandSort(&$hako) {
		global $init;
		usort($hako->islands, 'popComp');
	}
	
	//---------------------------------------------------
	// �����A����t�F�C�Y
	//---------------------------------------------------
	function income(&$island) {
		global $init;
		
		$pop = $island['pop'];
		$farm = $island['farm'] * 10;
		$factory = $island['factory'];
		$commerce = $island['commerce'];
		$mountain = $island['mountain'];
		$hatuden = $island['hatuden'];
		
		// �H��A�̌@��A���Ƃ͔��d�ʂ��֌W
		$enesyouhi = round($pop / 100 + $factory * 2/3 + $commerce * 1/3 + $mountain * 1/4);
		$work = min(round($enesyouhi), ($factory + $commerce + $mountain));
		
		// ����
		if($pop > $farm) {
			// �_�Ƃ�������肪�]��ꍇ
			if((Util::random(1000) < $init->disTenki) && ($island['tenki'] == 4)) {
				// ��d����
				if($island['zin'][5] == 1) {
					// �W��������
					$island['food'] += $farm * 2; // �S����ǎd��
				} else {
					$island['food'] += $farm; // �S����ǎd��
				}
				$this->log->Teiden($island['id'], $island['name']);
			} else {
				if($island['zin'][5] == 1) {
					// �W��������
					$island['food'] += $farm * 2; // �_��t���ғ�
		        } else {
					$island['food'] += $farm; // �_��t���ғ�
				}
				if($island['zin'][6] == 1) {
					// �T���}���_�[������
					$island['money'] += (min(round(($pop - $farm) / 10), $work)) * 2;
				} else {
					$island['money'] += min(round(($pop - $farm) / 10), $work);
				}
			}
		} else {
			// �_�Ƃ����Ŏ��t�̏ꍇ
			$island['food'] += $pop; // �S����ǎd��
		}
		if ( $island['present']['item'] == 0 ) {
			if ( $island['present']['px'] != 0 ) {
				$island['money'] += $island['present']['px'];
				$this->log->presentMoney($island['id'], $island['name'], $island['present']['px']);
			}
			if ( $island['present']['py'] != 0 ) {
				$island['food'] += $island['present']['py'];
				$this->log->presentFood($island['id'], $island['name'], $island['present']['py']);
			}
		}
		// �H������
		$island['food'] = round($island['food'] - $pop * $init->eatenFood);
		
		// �D
		$shipCost = 0;
		for($i = 0; $i < 10; $i++) {
			$shipCost += $init->shipCost[$i] * $island['ship'][$i];
		}
		$island['money'] -= $shipCost;
		if($island['port'] > 0){
			$island['money'] += $init->shipIncom * $island['ship'][0];
			$island['food']  += $init->shipFood  * $island['ship'][1];
		}
		if($island['money'] < 0) $island['money'] = 0;
		if($island['food'] < 0) $island['food']  = 0 ;
	}
	
	//---------------------------------------------------
	// �D����������
	//---------------------------------------------------
	function shipcounter($hako, &$island) {
		global $init;
		
		// �D����������
		for($i = 0; $i < 15; $i++) {
			$island['ship'][$i] = 0;
		}
	}
	//---------------------------------------------------
	// �l�����̑��̒l���Z�o
	//---------------------------------------------------
	function estimate($hako, &$island) {
		// estimate(&$island) �̂悤�Ɏg�p
		global $init;
		
		$land = $island['land'];
		$landValue = $island['landValue'];
		
		$area      = 0;
		$pop       = 0;
		$farm      = 0;
		$factory   = 0;
		$commerce  = 0;
		$mountain  = 0;
		$hatuden   = 0;
		$home      = 0;
		$monster   = 0;
		$port      = 0;
		$oil       = 0;
		$soccer    = 0;
		$park      = 0;
		$stat      = 0;
		$train     = 0;
		$bank      = 0;
		$m23       = 0;
		$fire = $rena = $base = 0;
		
		// ������
		for($y = 0; $y < $init->islandSize; $y++) {
			for($x = 0; $x < $init->islandSize; $x++) {
				$kind = $land[$x][$y];
				$value = $landValue[$x][$y];
				if($kind == $init->landShip){
					$ship = Util::navyUnpack($value);
					if($ship[0] != 0) {
						$tn = $hako->idToNumber[$ship[0]];
						$tIsland = &$hako->islands[$tn];
						$tIsland['ship'][$ship[1]]++;
					} else {
						$island['ship'][$ship[1]]++;
					}
				}
				if($kind == $init->landOil) {
					$oil++;
				}
				if($kind == $init->landSbase) {
					$base += 3;
					$fire += Util::expToLevel($kind, $value);
				}
				if($kind == $init->landSdefence) {
					$base += $value;
				}
				if(($kind != $init->landSea) &&
					($kind != $init->landShip) &&
					($kind != $init->landSbase) &&
					($kind != $init->landSdefence) &&
					($kind != $init->landSsyoubou) &&
					($kind != $init->landOil)) {
					if(($kind != $init->landNursery) && ($kind != $init->landSeaCity) && ($kind != $init->landFroCity) &&
						($kind != $init->landSfarm) && ($kind != $init->landZorasu)) {
						$area++;
					}
					switch($kind) {
						case $init->landTown:
						case $init->landSeaCity:
						case $init->landFroCity:
						case $init->landProcity:
							// ��
							$base++;
							$pop += $value;
							break;
							
						case $init->landNewtown:
							// �j���[�^�E��
							$pop += $value;
							$nwork =  (int)($value/15);
							$commerce += $nwork;
							break;
							
						case $init->landBigtown:
							// ����s�s
							$pop += $value;
							$mwork =  (int)($value/20);
							$lwork =  (int)($value/30);
							$farm += $mwork;
							$commerce += $lwork;
							break;
							
						case $init->landFarm:
							// �_��
							if(Turn::countAround($land, $x, $y, 19, array($init->landFusya))){
								// ����2�փN�X�ɕ��Ԃ������2�{�̋K�͂�
								$farm += $value * 2;
							}else{
								$farm += $value;
							}
							break;
							
						case $init->landSfarm:
							// �C��_��
							$farm += $value;
							break;
							
						case $init->landNursery:
							// �{�B��
							$farm += $value;
							break;
							
						case $init->landFactory:
							// �H��
							$factory += $value;
							break;
							
						case $init->landCommerce:
							// ����
							$commerce += $value;
							break;
							
						case $init->landMountain:
							// �R
							$mountain += $value;
							break;
							
						case $init->landHatuden:
							// ���d��
							if($island['zin'][4] == 1) {
								// ���i����
								$hatuden += $value * 2;
							} else {
								$hatuden += $value;
							}
							break;
							
						case $init->landBase:
							// �~�T�C��
							$base += 2;
							$fire += Util::expToLevel($kind, $value);
							break;
							
						case $init->landMonster:
						case $init->landSleeper:
							// ���b
							$monster++;
							break;
							
						case $init->landZorasu:
							// ���炷
							$hatuden += $value;
							break;
							
						case $init->landPort:
							// �`
							$port++;
							break;
							
						case $init->landStat:
							// �w
							$stat++;
							break;
							
						case $init->landTrain:
							// �d��
							$train++;
							break;
							
						case $init->landSoccer:
							// �X�^�W�A��
							$soccer++;
							break;
							
						case $init->landPark:
							// �V���n
							$park++;
							break;
							
						case $init->landBank:
							// ��s
							$bank++;
							break;
							
						case $init->landMonument:
							// �L�O��
							if($value == 23) {
								$m23++;
							}
							break;
							
						case $init->landMyhome:
							// �}�C�z�[��
							$home++;
							break;
					}
				}
			}
		}
		// ���
		$island['pop']      = $pop;
		$island['area']     = $area;
		$island['farm']     = $farm;
		$island['factory']  = $factory;
		$island['commerce'] = $commerce;
		$island['mountain'] = $mountain;
		$island['hatuden']  = $hatuden;
		$island['home']     = $home;
		$island['oil']      = $oil;
		$island['monster']  = $monster;
		$island['port']     = $port;
		$island['stat']     = $stat;
		$island['train']    = $train;
		$island['soccer']   = $soccer;
		$island['park']     = $park;
		$island['bank']     = $bank;
		$island['m23']      = $m23;
		$island['fire']     = $fire;
		$island['rena']     = $fire + $base;
		
		// �d�͏����
		if(($island['pop'] - $island['farm']) <= 0 || ($island['factory'] + $island['commerce'] + $island['mountain']) <= 0) {
			$island['enesyouhi'] = 0;
		} elseif($island['factory'] + $island['commerce'] + $island['mountain'] > 0) {
			$island['enesyouhi'] = min(round($island['pop'] - $island['farm']), ($island['factory'] * 2/3 + $island['commerce'] * 1/3 + $island['mountain'] * 1/4));
		}
		// �d�͉ߕs����
		$island['enehusoku'] = $island['hatuden'] - $island['enesyouhi'];
		
		if($island['soccer'] == 0) {
			$island['kachi'] = $island['make'] = $island['hikiwake'] = $island['kougeki'] = $island['bougyo'] = $island['tokuten'] = $island['shitten'] = 0;
		}
		$island['team'] = $island['kachi']*2 - $island['make']*2 + $island['hikiwake'] + $island['kougeki'] + $island['bougyo'] + $island['tokuten'] - $island['shitten'];
		
		if($island['pop'] == 0) {
			$island['point'] = 0;
		} else {
			if($island['isBF'] == 1) {
				$island['point'] = 100;
			} else {
				$island['point'] = ($island['pop']*15 + $island['money'] + $island['food'] + $island['farm']*2 + $island['factory'] + $island['commerce']*1.2 + $island['mountain']*2 + $island['hatuden']*3 + $island['team'] + $island['area']*5 + $island['taiji']*5 + $island['fire']*10 + $island['monster']*5)*10;
			}
		}
		$island['seichi'] = 0;
	}
	
	//---------------------------------------------------
	// �͈͓��̒n�`�𐔂���
	//---------------------------------------------------
	function countAround($land, $x, $y, $range, $kind) {
		global $init;
		
		// �͈͓��̒n�`�𐔂���
		$count = 0;
		$sea = 0;
		$list = array();
		reset($kind);
		while (list(, $value) = each($kind)) {
			$list[$value] = 1;
		}
		for($i = 0; $i < $range; $i++) {
			$sx = $x + $init->ax[$i];
			$sy = $y + $init->ay[$i];
			// �s�ɂ��ʒu����
			if((($sy % 2) == 0) && (($y % 2) == 1)) {
				$sx--;
			}
			if(($sx < 0) || ($sx >= $init->islandSize) ||
				($sy < 0) || ($sy >= $init->islandSize)) {
				// �͈͊O�̏ꍇ
				// �C�ɉ��Z
				$sea++;
			} elseif($list[$land[$sx][$sy]]) {
				// �͈͓��̏ꍇ
				$count++;
			}
		}
		if($list[$init->landSea]) {
			$count += $sea;
		}
		return $count;
	}
	//---------------------------------------------------
	// �͈͓��̒n�`�{�l�ŃJ�E���g
	//---------------------------------------------------
	function countAroundValue($island, $x, $y, $kind, $lv, $range) {
		global $init;
		
		$land = $island['land'];
		$landValue = $island['landValue'];
		$count = 0;
		
		for($i = 0; $i < $range; $i++) {
			$sx = $x + $init->ax[$i];
			$sy = $y + $init->ay[$i];
			// �s�ɂ��ʒu����
			if((($sy % 2) == 0) && (($y % 2) == 1)) {
				$sx--;
			}
			if(($sx < 0) || ($sx >= $init->islandSize) ||
				($sy < 0) || ($sy >= $init->islandSize)) {
				// �͈͊O�̏ꍇ
			} else {
				// �͈͓��̏ꍇ
				if($land[$sx][$sy] == $kind && $landValue[$sx][$sy] >= $lv) {
					$count++;
				}
			}
		}
		return $count;
	}
	
	//---------------------------------------------------
	// �n�`�̌Ăѕ�
	//---------------------------------------------------
	function landName($land, $lv) {
		global $init;
		
		switch($land) {
			case $init->landSea:
				if($lv == 1) {
					return '��';
				} else {
					return '�C';
				}
				
			case $init->landShip:
				// �D��
				$ship = Util::navyUnpack($lv);
				return $init->shipName[$ship[1]];
				
			case $init->landPort:
				return '�`';
				
			case $init->landRail:
				return '���H';
				
			case $init->landStat:
				return '�w';
				
			case $init->landTrain:
				return '�d��';
				
			case $init->landZorasu:
				return '���炷';
				
			case $init->landSeaSide:
				return '���l';
				
			case $init->landSeaResort:
				// �C�̉�
				$n;
				if($lv < 30) {
					$n = '�C�̉�';
				} elseif($lv < 100) {
					$n = '���h';
				} else {
					$n = '���]�[�g�z�e��';
				}
				return $n;
				
			case $init->landWaste:
				return '�r�n';
				
			case $init->landPoll:
				return '�����y��';
				
			case $init->landPlains:
				return '���n';
				
			case $init->landTown:
				if($lv < 30) {
					return '��';
				} elseif($lv < 100) {
					return '��';
				} elseif($lv < 200) {
					return '�s�s';
				} else {
					return '��s�s';
				}
				
			case $init->landProcity:
				return '�h�Гs�s';
				
			case $init->landNewtown:
				return '�j���[�^�E��';
				
			case $init->landBigtown:
				return '����s�s';
				
			case $init->landForest:
				return '�X';
				
			case $init->landFarm:
				return '�_��';
				
			case $init->landSfarm:
				return '�C��_��';
				
			case $init->landNursery:
				return '�{�B��';
				
			case $init->landFactory:
				return '�H��';
				
			case $init->landCommerce:
				return '���ƃr��';
				
			case $init->landHatuden:
				return '���d��';
				
			case $init->landBank:
				return '��s';
				
			case $init->landBase:
				return '�~�T�C����n';
				
			case $init->landDefence:
				return '�h�q�{��';
				
			case $init->landSdefence:
				return '�C��h�q�{��';
				
			case $init->landMountain:
				return '�R';
				
			case $init->landMonster:
			case $init->landSleeper:
				$monsSpec = Util::monsterSpec($lv);
				return $monsSpec['name'];
				
			case $init->landSbase:
				return '�C���n';
				
			case $init->landSeaCity:
				return '�C��s�s';
				
			case $init->landFroCity:
				return '�C��s�s';
				
			case $init->landOil:
				return '�C����c';
				
			case $init->landMyhome:
				return '�}�C�z�[��';
				
			case $init->landSoukoM:
				return '����';
				
			case $init->landSoukoF:
				return '�H����';
				
			case $init->landMonument:
				return $init->monumentName[$lv];
				
			case $init->landHaribote:
				return '�n���{�e';
				
			case $init->landSoccer:
				return '�X�^�W�A��';
				
			case $init->landPark:
				return '�V���n';
				
			case $init->landFusya:
				return '����';
				
			case $init->landSyoubou:
				return '���h��';
				
			case $init->landSsyoubou:
				return '�C����h��';
				
		}
	}
}

//---------------------------------------------------
// �|�C���g���r
//---------------------------------------------------
function popComp($x, $y) {
	if ($x['point'] == 0) {
		return 1;
	}
	if ($y['point'] == 0) {
		return -1;
	}
	if ($x['isBF']) {
		if (!($y['isBF'])) {
			return 1;
		}
	}
	if ($y['isBF']) {
		if (!($x['isBF'])) {
			return -1;
		}
	}
	if($x['point'] == $y['point']) {
		return 0;
	}
	return ($x['point'] > $y['point']) ? -1 : 1;
}

?>
