<?php

/*******************************************************************

	���돔�� S.E
	
	- �V�K�쐬�p�t�@�C�� -
	
	hako-make.php by SERA - 2013/05/19

*******************************************************************/

//--------------------------------------------------------------------
class Make {
	//---------------------------------------------------
	// ���̐V�K�쐬���[�h
	//---------------------------------------------------
	function newIsland($hako, $data) {
		global $init;
		
		$log = new Log;
		if($hako->islandNumber >= $init->maxIsland) {
			Error::newIslandFull();
			return;
		}
		if(empty($data['ISLANDNAME'])) {
			Error::newIslandNoName();
			return;
		}
		// ���O���������`�F�b�N
		if(ereg("[,?()<>$]", $data['ISLANDNAME']) || strcmp($data['ISLANDNAME'], "���l") == 0) {
			Error::newIslandBadName();
			return;
		}
		// ���O�̏d���`�F�b�N
		if(Util::nameToNumber($hako, $data['ISLANDNAME']) != -1) {
			Error::newIslandAlready();
			return;
		}
		// �p�X���[�h�̑��ݔ���
		if(empty($data['PASSWORD'])) {
			Error::newIslandNoPassword();
			return;
		}
		if(strcmp($data['PASSWORD'], $data['PASSWORD2']) != 0) {
			Error::wrongPassword();
			return;
		}
		// �V�������̔ԍ������߂�
		$newNumber = $hako->islandNumber;
		$hako->islandNumber++;
		$hako->islandNumberNoBF++;
		$hako->islandNumberNoKP++;
		$island = $this->makeNewIsland();
		
		// ���̔ԍ��̎g���܂킵
		$safety = 0;
		while(isset($hako->idToNumber[$hako->islandNextID])) {
			$hako->islandNextID++;
			if($hako->islandNextID > 250) $hako->islandNextID = 1;
			$safety++;
			if($safety == 250) break;
		}
		
		// �e��̒l��ݒ�
		$island['name'] = htmlspecialchars($data['ISLANDNAME']);
		$island['owner'] = htmlspecialchars($data['OWNERNAME']);
		$island['id'] = $hako->islandNextID;
		$hako->islandNextID++;
		$island['starturn'] = $hako->islandTurn;
		$island['isBF'] = $island['keep'] = 0;
		$island['absent'] = $init->giveupTurn - 3;
		$island['comment'] = '(���o�^)';
		$island['comment_turn'] = $hako->islandTurn;
		$island['password'] = Util::encode($data['PASSWORD']);
		$island['tenki'] = 1;
		$island['team'] = $island['shiai'] = $island['kachi'] = $island['make'] = $island['hikiwake'] = $island['kougeki'] = $island['bougyo'] = $island['tokuten'] = $island['shitten'] = 0;
		
		Turn::estimate($hako, $island);
		if ( $hako->islandNumberBF > 0 ) {
			for ( $i = 0; $i < $hako->islandNumberBF; $i++ ) {
				$hako->islands[$newNumber - $i] = $hako->islands[$newNumber - $i - 1];
			}
			$hako->islands[$newNumber - $hako->islandNumberBF] = $island;
		} else {
			$hako->islands[$newNumber] = $island;
		}
		$hako->writeIslandsFile($island['id']);
		$log->discover($island['id'], $island['name']);
		$htmlMap = new HtmlMap;
		$htmlMap->newIslandHead($island['name']);
		$htmlMap->islandInfo($island, $newNumber);
		$htmlMap->islandMap($hako, $island, 1, $data);
		
	}
	//---------------------------------------------------
	// �V���������쐬����
	//---------------------------------------------------
	function makeNewIsland() {
		global $init;
		
		$command = array();
		// �����R�}���h����
		for($i = 0; $i < $init->commandMax; $i++) {
			$command[$i] = array (
				'kind'   => $init->comDoNothing,
				'target' => 0,
				'x'      => 0,
				'y'      => 0,
				'arg'    => 0,
			);
		}
		$lbbs = "";
		// �����f������
		for($i = 0; $i < $init->lbbsMax; $i++) {
			$lbbs[$i] = "0>>0>>";
		}
		$land = array();
		$landValue = array();
		
		if ($init->initialLand) {
			// �������f�[�^�t�@�C���g�p���[�h
			// ��{�`���쐬
			$fp_i = fopen($init->initialLand, "r");
			$offset = 7; // ��΂̃f�[�^����������
			for($y = 0; $y < $init->islandSize; $y++) {
				$line = chop(fgets($fp_i, READ_LINE));
				for($x = 0; $x < $init->islandSize; $x++) {
					$l = substr($line, $x * $offset, 2);
					$v = substr($line, $x * $offset + 2, 5);
					$land[$x][$y] = hexdec($l);
					$landValue[$x][$y] = hexdec($v);
				}
			}
			fclose($fp_i);
		} else if ($init->initialSize) {
			// �����ʐϓ��ꃂ�[�h
			// ��{�`���쐬
			for($y = 0; $y < $init->islandSize; $y++) {
				for($x = 0; $x < $init->islandSize; $x++) {
					$land[$x][$y] = $init->landSea;
					$landValue[$x][$y] = 0;
				}
			}
			
			// 4*4�ɍr�n��z�u
			$center = $init->islandSize / 2 - 1;
			for($y = $center -1; $y < $center + 3; $y++) {
				for($x = $center - 1; $x < $center + 3; $x++) {
					$land[$x][$y] = $init->landWaste;
				}
			}
			// ���������̖ʐόŒ�
			$size = 16;
			
			// 8*8�͈͓��ɗ��n�𑝐B
			while($size < $init->initialSize) {
				$x = Util::random(8) + $center - 3;
				$y = Util::random(8) + $center - 3;
				if(Turn::countAround($land, $x, $y, 7, array($init->landSea)) != 7) {
					// ����ɗ��n������ꍇ�A�󐣂ɂ���
					// �󐣂͍r�n�ɂ���
					// �r�n�͕��n�ɂ���
					if($land[$x][$y] == $init->landSea) {
						if($landValue[$x][$y] == 1) {
							$land[$x][$y] = $init->landPlains;
							$landValue[$x][$y] = 0;
							$size++;
						} else {
							if($land[$x][$y] == $init->landWaste) {
								$land[$x][$y] = $init->landPlains;
								$landValue[$x][$y] = 0;
							} else {
								if($landValue[$x][$y] == 1) {
									$land[$x][$y] = $init->landWaste;
									$landValue[$x][$y] = 0;
								} else {
									$landValue[$x][$y] = 1;
								}
							}
						}
					}
				}
			}
			// �X�����
			$count = 0;
			while($count < 4) {
				// �����_�����W
				$x = Util::random(4) + $center - 1;
				$y = Util::random(4) + $center - 1;
				
				// ���������łɐX�łȂ���΁A�X�����
				if($land[$x][$y] != $init->landForest) {
					$land[$x][$y] = $init->landForest;
					$landValue[$x][$y] = 5; // �ŏ���500�{
					$count++;
				}
			}
			$count = 0;
			while($count < 2) {
				// �����_�����W
				$x = Util::random(4) + $center - 1;
				$y = Util::random(4) + $center - 1;
				
				// �������X�����łȂ���΁A�������
				if(($land[$x][$y] != $init->landTown) &&
					 ($land[$x][$y] != $init->landForest)) {
					$land[$x][$y] = $init->landTown;
					$landValue[$x][$y] = 5; // �ŏ���500�l
					$count++;
				}
			}
			// �R�����
			$count = 0;
			while($count < 1) {
				// �����_�����W
				$x = Util::random(4) + $center - 1;
				$y = Util::random(4) + $center - 1;
				
				// �������X�����łȂ���΁A�������
				if(($land[$x][$y] != $init->landTown) &&
					 ($land[$x][$y] != $init->landForest)) {
					$land[$x][$y] = $init->landMountain;
					$landValue[$x][$y] = 0; // �ŏ��͍̌@��Ȃ�
					$count++;
				}
			}
			// ��n�����
			$count = 0;
			while($count < 1) {
				// �����_�����W
				$x = Util::random(4) + $center - 1;
				$y = Util::random(4) + $center - 1;
				
				// �������X�������R�łȂ���΁A��n
				if(($land[$x][$y] != $init->landTown) &&
					 ($land[$x][$y] != $init->landForest) &&
					 ($land[$x][$y] != $init->landMountain)) {
					$land[$x][$y] = $init->landBase;
					$landValue[$x][$y] = 0;
					$count++;
				}
			}
		} else {
			// �ʏ탂�[�h
			// ��{�`���쐬
			for($y = 0; $y < $init->islandSize; $y++) {
				for($x = 0; $x < $init->islandSize; $x++) {
					$land[$x][$y] = $init->landSea;
					$landValue[$x][$y] = 0;
				}
			}
			// 4*4�ɍr�n��z�u
			$center = $init->islandSize / 2 - 1;
			for($y = $center -1; $y < $center + 3; $y++) {
				for($x = $center - 1; $x < $center + 3; $x++) {
					$land[$x][$y] = $init->landWaste;
				}
			}
			// 8*8�͈͓��ɗ��n�𑝐B
			for($i = 0; $i < 120; $i++) {
				$x = Util::random(8) + $center - 3;
				$y = Util::random(8) + $center - 3;
				if(Turn::countAround($land, $x, $y, 7, array($init->landSea)) != 7) {
					// ����ɗ��n������ꍇ�A�󐣂ɂ���
					// �󐣂͍r�n�ɂ���
					// �r�n�͕��n�ɂ���
					if($land[$x][$y] == $init->landWaste) {
						$land[$x][$y] = $init->landPlains;
						$landValue[$x][$y] = 0;
					} else {
						if($landValue[$x][$y] == 1) {
							$land[$x][$y] = $init->landWaste;
							$landValue[$x][$y] = 0;
						} else {
							$landValue[$x][$y] = 1;
						}
					}
				}
			}
			// �X�����
			$count = 0;
			while($count < 4) {
				// �����_�����W
				$x = Util::random(4) + $center - 1;
				$y = Util::random(4) + $center - 1;
				
				// ���������łɐX�łȂ���΁A�X�����
				if($land[$x][$y] != $init->landForest) {
					$land[$x][$y] = $init->landForest;
					$landValue[$x][$y] = 5; // �ŏ���500�{
					$count++;
				}
			}
			$count = 0;
			while($count < 2) {
				// �����_�����W
				$x = Util::random(4) + $center - 1;
				$y = Util::random(4) + $center - 1;
				
				// �������X�����łȂ���΁A�������
				if(($land[$x][$y] != $init->landTown) &&
					 ($land[$x][$y] != $init->landForest)) {
					$land[$x][$y] = $init->landTown;
					$landValue[$x][$y] = 5; // �ŏ���500�l
					$count++;
				}
			}
			// �R�����
			$count = 0;
			while($count < 1) {
				// �����_�����W
				$x = Util::random(4) + $center - 1;
				$y = Util::random(4) + $center - 1;
				
				// �������X�����łȂ���΁A�������
				if(($land[$x][$y] != $init->landTown) &&
					 ($land[$x][$y] != $init->landForest)) {
					$land[$x][$y] = $init->landMountain;
					$landValue[$x][$y] = 0; // �ŏ��͍̌@��Ȃ�
					$count++;
				}
			}
			// ��n�����
			$count = 0;
			while($count < 1) {
				// �����_�����W
				$x = Util::random(4) + $center - 1;
				$y = Util::random(4) + $center - 1;
				
				// �������X�������R�łȂ���΁A��n
				if(($land[$x][$y] != $init->landTown) &&
					 ($land[$x][$y] != $init->landForest) &&
					 ($land[$x][$y] != $init->landMountain)) {
					$land[$x][$y] = $init->landBase;
					$landValue[$x][$y] = 0;
					$count++;
				}
			}
		}
		return array (
			'money'     => $init->initialMoney,
			'food'      => $init->initialFood,
			'land'      => $land,
			'landValue' => $landValue,
			'command'   => $command,
			'lbbs'      => $lbbs,
			'prize'     => '0,0,',
			'taiji'     => 0,
		);
	}
	
	//---------------------------------------------------
	// �R�����g�X�V
	//---------------------------------------------------
	function commentMain($hako, $data) {
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
		// ���b�Z�[�W���X�V
		$island['comment'] = htmlspecialchars($data['MESSAGE']);
		$island['comment_turn'] = $hako->islandTurn;
		$hako->islands[$num] = $island;
		
		// �f�[�^�̏����o��
		$hako->writeIslandsFile();
		
		// �R�����g�X�V���b�Z�[�W
		HtmlSetted::Comment();
		
		// owner mode��
		if($data['DEVELOPEMODE'] == "cgi") {
			$html = new HtmlMap;
		} else {
			$html = new HtmlJS;
		}
		$html->owner($hako, $data);
	}
	
	//---------------------------------------------------
	// ���[�J���f�����[�h
	//---------------------------------------------------
	function localBbsMain($hako, $data) {
		global $init;
		
		$id = $data['ISLANDID'];
		$num = $hako->idToNumber[$id];
		$island = $hako->islands[$num];
		$name = $island['name'];
		$speaker = "0>";
		
		// �Ȃ������̓����Ȃ��ꍇ
		if($num != 0 && empty($num)) {
			Error::problem();
			return;
		}
		// �폜���[�h����Ȃ��Ė��O�����b�Z�[�W���Ȃ��ꍇ
		if(empty($data['DEL'])) {
			if(empty($data['LBBSNAME']) || (empty($data['LBBSMESSAGE']))) {
				Error::lbbsNoMessage();
				return;
			}
		}
		// �ό��҃��[�h����Ȃ����̓p�X���[�h�`�F�b�N
		if($data['lbbsMode'] == 1) {
			if(!Util::checkPassword($island['password'], $data['PASSWORD'])) {
				// password�ԈႢ
				Error::wrongPassword();
				return;
			}
			// �I�[�i�[����ݒ�
			$HlbbsName = $island['owner'];
		} else if (empty($data['DEL'])) {
			// �ό��҃��[�h
			if ($data['LBBSTYPE'] != 'ANON') {
				// ���J�Ƌɔ�
				// id���瓇�ԍ����擾
				$sNum = $hako->idToNumber[$data['ISLANDID2']];
				$sIsland = $hako->islands[$sNum];
				
				// �Ȃ������̓����Ȃ��ꍇ
				if($sNum != 0 && empty($sNum)) {
					Error::problem();
					return;
				}
				// �p�X���[�h�`�F�b�N
				if(!Util::checkPassword($sIsland['password'], $data['PASSWORD'])) {
					// password�ԈႢ
					Error::wrongPassword();
					return;
				}
				// �I�[�i�[����ݒ�
				$HlbbsName = $sIsland['owner'];
				
				// �ʐM��p�𕥂�
				if($data['LBBSTYPE'] == 'PUBLIC') {
					$cost = $init->lbbsMoneyPublic;
				} else {
					$cost = $init->lbbsMoneySecret;
				}
				if($sIsland['money'] < $cost) {
					// ��p�s��
					Error::lbbsNoMoney();
					return;
				}
				$sIsland['money'] -= $cost;
				$hako->islands[$sNum] = $sIsland;
			}
			// �����҂��L������
			if($data['LBBSTYPE'] != 'ANON') {
				// ���J�Ƌɔ�
				$speaker = $sIsland['name'] . '��,' . $data['ISLANDID2'];
			} else {
				// ����
				$speaker = getenv('REMOTE_HOST');
				if($speaker == '') {
					$speaker = getenv('REMOTE_ADDR');
				}
			}
			if($data['LBBSTYPE'] != 'SECRET') {
				// ���J�Ɠ���
				$speaker = "0>$speaker";
			} else {
				// �ɔ�
				$speaker = "1>$speaker";
			}
		} else {
			// �ό��ҍ폜���[�h
			// id���瓇�ԍ����擾
			$sNum = $hako->idToNumber[$data['ISLANDID2']];
			$sIsland = $hako->islands[$sNum];
			
			// �Ȃ������̓����Ȃ��ꍇ
			if($sNum != 0 && empty($sNum)) {
				Error::problem();
				return;
			}
			// �p�X���[�h�`�F�b�N
			if(!Util::checkPassword($sIsland['password'], $data['PASSWORD'])) {
				// password�ԈႢ
				Error::wrongPassword();
				return;
			}
		}
		$lbbs = $island['lbbs'];
		
		// ���[�h�ŕ���
		if(!empty($data['DEL'])) {
			if($data['lbbsMode'] == 0) {
				list($secret, $sTemp, $mode, $turn, $message, $color) = split(">", $lbbs[$data['NUMBER']]);
				list($sName, $sId) = split(",", $sTemp);
				if($sId != $data['ISLANDID2']) {
					// ID�ԈႢ
					Error::wrongID();
					return;
				}
			}
			// �폜���[�h
			// ���b�Z�[�W��O�ɂ��炷
			Util::slideBackLbbsMessage($lbbs, $data['NUMBER']);
			HtmlSetted::lbbsDelete();
		} else {
			// �L�����[�h
			Util::slideLbbsMessage($lbbs);
			
			// ���b�Z�[�W��������
			if($data['lbbsMode'] == 0) {
				$message = '0';
			} else {
				$message = '1';
			}
			$bbs_name = "{$hako->islandTurn}�F" . htmlspecialchars($data['LBBSNAME']);
			$bbs_message = htmlspecialchars($data['LBBSMESSAGE']);
			$lbbs[0] = "{$speaker}>{$message}>{$bbs_name}>{$bbs_message}>{$data['LBBSCOLOR']}";
			
			HtmlSetted::lbbsAdd();
		}
		$island['lbbs'] = $lbbs;
		$hako->islands[$num] = $island;
		
		// �f�[�^�����o��
		$hako->writeIslandsFile($id);
		
		if($data['DEVELOPEMODE'] == "cgi") {
			$html = new HtmlMap;
		} else {
			$html = new HtmlJS;
		}
		// ���Ƃ̃��[�h��
		if($data['lbbsMode'] == 0) {
			$html->visitor($hako, $data);
		} else {
			$html->owner($hako, $data);
		}
	}
	
	//---------------------------------------------------
	// ���ύX���[�h
	//---------------------------------------------------
	function changeMain($hako, $data) {
		global $init;
		$log = new Log;
		
		$id = $data['ISLANDID'];
		$num = $hako->idToNumber[$id];
		$island = $hako->islands[$num];
		$name = $island['name'];
		
		// �p�X���[�h�`�F�b�N
		if(Util::checkSpecialPassword($data['OLDPASS'])) {
			// ����p�X���[�h
			if(preg_match("/^���l$/", $data['ISLANDNAME'])) {
				// ���̋����폜
				$this->deleteIsland($hako, $data);
				HtmlSetted::deleteIsland($name);
				return;
			} else {
				$island['money'] = $init->maxMoney;
				$island['food'] = $init->maxFood;
			}
		} elseif(!Util::checkPassword($island['password'], $data['OLDPASS'])) {
			// password�ԈႢ
			Error::wrongPassword();
			return;
		}
		// �m�F�p�p�X���[�h
		if(strcmp($data['PASSWORD'], $data['PASSWORD2']) != 0) {
			// password�ԈႢ
			Error::wrongPassword();
			return;
		}
		if(!empty($data['ISLANDNAME'])) {
			// ���O�ύX�̏ꍇ
			// ���O���������`�F�b�N
			if(ereg("[,?()<>$]", $data['ISLANDNAME']) || strcmp($data['ISLANDNAME'], "���l") == 0) {
				Error::newIslandBadName();
				return;
			}
			// ���O�̏d���`�F�b�N
			if(Util::nameToNumber($hako, $data['ISLANDNAME']) != -1) {
				Error::newIslandAlready();
				return;
			}
			if($island['money'] < $init->costChangeName) {
				// ��������Ȃ�
				Error::changeNoMoney();
				return;
			}
			// ���
			if(!Util::checkSpecialPassword($data['OLDPASS'])) {
				$island['money'] -= $init->costChangeName;
			}
			// ���O��ύX
			$log->changeName($island['name'], $data['ISLANDNAME']);
			$island['name'] = $data['ISLANDNAME'];
			$flag = 1;
		}
		// password�ύX�̏ꍇ
		if(!empty($data['PASSWORD'])) {
			// �p�X���[�h��ύX
			$island['password'] = Util::encode($data['PASSWORD']);
			$flag = 1;
		}
		if(($flag == 0) && (strcmp($data['PASSWORD'], $data['PASSWORD2']) != 0)) {
			// �ǂ�����ύX����Ă��Ȃ�
			Error::changeNothing();
			return;
		}
		$hako->islands[$num] = $island;
		// �f�[�^�����o��
		$hako->writeIslandsFile($id);
		
		// �ύX����
		HtmlSetted::change();
	}
	
	//---------------------------------------------------
	// �I�[�i���ύX���[�h
	//---------------------------------------------------
	function changeOwnerName($hako, $data) {
		global $init;
		
		$id = $data['ISLANDID'];
		$num = $hako->idToNumber[$id];
		$island = $hako->islands[$num];
		
		// �p�X���[�h�`�F�b�N
		if(Util::checkSpecialPassword($data['OLDPASS'])) {
			// ����p�X���[�h
			$island['money'] = $init->maxMoney;
			$island['food'] = $init->maxFood;
		} elseif(!Util::checkPassword($island['password'], $data['OLDPASS'])) {
			// password�ԈႢ
			Error::wrongPassword();
			return;
		}
		$island['owner'] = htmlspecialchars($data['OWNERNAME']);
		$hako->islands[$num] = $island;
		// �f�[�^�����o��
		$hako->writeIslandsFile($id);
		
		// �ύX����
		HtmlSetted::change();
	}
	
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
		
		if(strcmp($data['COMMANDMODE'], 'delete') == 0) {
			Util::slideFront($command, $data['NUMBER']);
			HtmlSetted::commandDelete();
		} elseif(($data['COMMAND'] == $init->comAutoPrepare) ||
				 ($data['COMMAND'] == $init->comAutoPrepare2)) {
			// �t�����n�A�t���n�Ȃ炵
			// ���W�z������
			$r = Util::makeRandomPointArray();
			$rpx = $r[0];
			$rpy = $r[1];
			$land = $island['land'];
			// �R�}���h�̎�ތ���
			$kind = $init->comPrepare;
			if($data['COMMAND'] == $init->comAutoPrepare2) {
				$kind = $init->comPrepare2;
			}
			$i = $data['NUMBER'];
			$j = 0;
			while(($j < $init->pointNumber) && ($i < $init->commandMax)) {
				$x = $rpx[$j];
				$y = $rpy[$j];
				if($land[$x][$y] == $init->landWaste) {
					Util::slideBack($command, $data['NUMBER']);
					$command[$data['NUMBER']] = array (
						'kind'   => $kind,
						'target' => 0,
						'x'      => $x,
						'y'      => $y,
						'arg'    => 0,
					);
					$i++;
				}
				$j++;
			}
			HtmlSetted::commandAdd();
		} elseif($data['COMMAND'] == $init->comAutoDelete) {
			// �S����
			for($i = 0; $i < $init->commandMax; $i++) {
				Util::slideFront($command, 0);
			}
			HtmlSetted::commandDelete();
		} else {
			if(strcmp($data['COMMANDMODE'], 'insert') == 0) {
				Util::slideBack($command, $data['NUMBER']);
			}
			HtmlSetted::commandAdd();
			// �R�}���h��o�^
			$command[$data['NUMBER']] = array (
				'kind'   => $data['COMMAND'],
				'target' => $data['TARGETID'],
				'x'      => $data['POINTX'],
				'y'      => $data['POINTY'],
				'arg'    => $data['AMOUNT'],
			);
		}
		// �f�[�^�̏����o��
		$island['command'] = $command;
		$hako->islands[$num] = $island;
		$hako->writeIslandsFile($island['id']);
		
		// owner mode��
		$html = new HtmlMap;
		$html->owner($hako, $data);
	}
	
	//---------------------------------------------------
	// ���̋����폜
	//---------------------------------------------------
	function deleteIsland($hako, $data) {
		global $init;
		
		$log = new Log;
		$id = $data['ISLANDID'];
		$num = $hako->idToNumber[$id];
		$island = $hako->islands[$num];
		
		// ���e�[�u���̑���
		$island['point'] = 0;
		$island['pop'] = 0;
		$island['dead'] = 1;
		$tmpid = $island['id'];
		$log->deleteIsland($tmpid, $island['name']);
		if(is_file("{$init->dirName}/island.{$tmpid}")) {
			unlink("{$init->dirName}/island.{$tmpid}");
		}
		// ���C���f�[�^�̑���
		$hako->islands[$num] = $island;
		Turn::islandSort($hako); // �폜���铇���ŉ��ʂɈړ�
		$hako->islandNumber -= 1; // �ŉ��ʍ폜
		
		// �f�[�^�����o��
		$hako->writeIslandsFile($id);
	}
}

?>
