<?php

/*******************************************************************

	���돔�� S.E
	
	- �e�탆�[�e�B���e�B��`�p�t�@�C�� -
	
	hako-util.php by SERA - 2012/07/08

*******************************************************************/

//--------------------------------------------------------------------
class Util {
	//---------------------------------------------------
	// �����̕\��
	//---------------------------------------------------
	function aboutMoney($money = 0) {
		global $init;
		
		if($init->moneyMode) {
			if($money < 500) {
				return "����500{$init->unitMoney}����";
			} else {
				return "����" . round($money / 1000) . "000" . $init->unitMoney;
			}
		} else {
			return $money . $init->unitMoney;
		}
	}
	
	//---------------------------------------------------
	// �o���n����~�T�C����n���x�����Z�o
	//---------------------------------------------------
	function expToLevel($kind, $exp) {
		global $init;
		
		if($kind == $init->landBase) {
			// �~�T�C����n
			for($i = $init->maxBaseLevel; $i > 1; $i--) {
				if($exp >= $init->baseLevelUp[$i - 2]) {
					return $i;
				}
			}
			return 1;
		} else {
			// �C���n
			for($i = $init->maxSBaseLevel; $i > 1; $i--) {
				if($exp >= $init->sBaseLevelUp[$i - 2]) {
					return $i;
				}
			}
			return 1;
		}
	}
	
	//---------------------------------------------------
	// ���b�̎�ށE���O�E�̗͂��Z�o
	//---------------------------------------------------
	function monsterSpec($lv) {
		global $init;
		
		// ���
		$kind = (int)($lv / 100);
		// ���O
		$name = $init->monsterName[$kind];
		// �̗�
		$hp = $lv - ($kind * 100);
		return array ( 'kind' => $kind, 'name' => $name, 'hp' => $hp );
	}
	//---------------------------------------------------
	// ���̖��O����ԍ����Z�o
	//---------------------------------------------------
	function  nameToNumber($hako, $name) {
		// �S������T��
		for($i = 0; $i < $hako->islandNumber; $i++) {
			if(strcmp($name, "{$hako->islands[$i]['name']}") == 0) {
				return $i;
			}
		}
		// ������Ȃ������ꍇ
		return -1;
	}
	
	//---------------------------------------------------
	// ������Ԃ�
	//---------------------------------------------------
	function islandName($island, $ally, $idToAllyNumber) {
		$name = '';
		foreach ($island['allyId'] as $id) {
			$i = $idToAllyNumber[$id];
			$mark  = $ally[$i]['mark'];
			$color = $ally[$i]['color'];
			$name .= '<FONT COLOR="' . $color . '"><B>' . $mark . '</B></FONT> ';
		}
		$name .= $island['name'] . "��";
		
		return ($name);
	}
	//---------------------------------------------------
	// �p�X���[�h�`�F�b�N
	//---------------------------------------------------
	function checkPassword($p1 = "", $p2 = "") {
		global $init;
		
		// null�`�F�b�N
		if(empty($p2)) {
			return false;
		}
		if(file_exists("{$init->passwordFile}")) {
			$fp = fopen("{$init->passwordFile}", "r");
			$masterPassword = chop(fgets($fp, READ_LINE));
			fclose($fp);
		}
		// �}�X�^�[�p�X���[�h�`�F�b�N
		if(strcmp($masterPassword, crypt($p2, 'ma')) == 0) {
			return true;
		}
		if(strcmp($p1, Util::encode($p2)) == 0) {
			return true;
		}
		return false;
	}
	
	function checkSpecialPassword($p = "") {
		global $init;
		
		// null�`�F�b�N
		if(empty($p)) {
			return false;
		}
		if(file_exists("{$init->passwordFile}")) {
			$fp = fopen("{$init->passwordFile}", "r");
			$masterPassword = chop(fgets($fp, READ_LINE));
			$specialPassword = chop(fgets($fp, READ_LINE));
			fclose($fp);
		}
		// ����p�X���[�h�`�F�b�N
		if(strcmp($specialPassword, crypt($p, 'sp')) == 0) {
			return true;
		}
		return false;
	}
	
	//---------------------------------------------------
	// �p�X���[�h�̃G���R�[�h
	//---------------------------------------------------
	function encode($s) {
		global $init;
		
		if($init->cryptOn) {
			return crypt($s, 'h2');
		} else {
			return $s;
		}
	}
	
	//---------------------------------------------------
	// 0 �` num -1 �̗�������
	//---------------------------------------------------
	function random($num = 0) {
		if($num <= 1) {
			return 0;
		}
		return mt_rand(0, $num - 1);
	}
	
	//---------------------------------------------------
	// ���[�J���f���̃��b�Z�[�W����O�ɂ��炷
	//---------------------------------------------------
	function slideBackLbbsMessage(&$lbbs, $num) {
		global $init;
		
		array_splice($lbbs, $num, 1);
		$lbbs[$init->lbbsMax - 1] = '0>>0>>';
	}
	//---------------------------------------------------
	// ���[�J���f���̃��b�Z�[�W������ɂ��炷
	//---------------------------------------------------
	function slideLbbsMessage(&$lbbs) {
		array_pop($lbbs);
		array_unshift($lbbs, $lbbs[0]);
	}
	
	//---------------------------------------------------
	// �����_���ȍ��W�𐶐�
	//---------------------------------------------------
	function makeRandomPointArray() {
		global $init;
		
		$rx = $ry = array();
		for($i = 0; $i < $init->islandSize; $i++)
		for($j = 0; $j < $init->islandSize; $j++)
		$rx[$i * $init->islandSize + $j] = $j;
		
		for($i = 0; $i < $init->islandSize; $i++)
		for($j = 0; $j < $init->islandSize; $j++)
		$ry[$j * $init->islandSize + $i] = $j;
		
		for($i = $init->pointNumber; --$i;) {
			$j = Util::random($i + 1);
			if($i != $j) {
				$tmp = $rx[$i];
				$rx[$i] = $rx[$j];
				$rx[$j] = $tmp;
				$tmp = $ry[$i];
				$ry[$i] = $ry[$j];
				$ry[$j] = $tmp;
			}
		}
		return array($rx, $ry);
	}
	
	//---------------------------------------------------
	// �����_���ȓ��̏����𐶐�
	//---------------------------------------------------
	function randomArray($n = 1) {
		// �����l
		for($i = 0; $i < $n; $i++) {
			$list[$i] = $i;
		}
		// �V���b�t��
		for($i = 0; $i < $n; $i++) {
			$j = Util::random($n - 1);
			if($i != $j) {
				$tmp = $list[$i];
				$list[$i] = $list[$j];
				$list[$j] = $tmp;
			}
		}
		return $list;
	}
	
	//---------------------------------------------------
	// �R�}���h��O�ɂ��炷
	//---------------------------------------------------
	function slideFront(&$command, $number = 0) {
		global $init;
		
		// ���ꂼ�ꂸ�炷
		array_splice($command, $number, 1);
		
		// �Ō�Ɏ����J��
		$command[$init->commandMax - 1] = array (
			'kind'   => $init->comDoNothing,
			'target' => 0,
			'x'      => 0,
			'y'      => 0,
			'arg'    => 0
		);
	}
	
	//---------------------------------------------------
	// �R�}���h����ɂ��炷
	//---------------------------------------------------
	function slideBack(&$command, $number = 0) {
		global $init;
		
		// ���ꂼ�ꂸ�炷
		if($number == count($command) - 1) {
			return;
		}
		for($i = $init->commandMax - 1; $i >= $number; $i--) {
			$command[$i] = $command[$i - 1];
		}
	}
	
	function euc_convert($arg) {
		// �����R�[�h��EUC-JP�ɕϊ����ĕԂ�
		// ������̕����R�[�h�𔻕�
		$code = i18n_discover_encoding("$arg");
		// ��EUC-JP�̏ꍇ�̂�EUC-JP�ɕϊ�
		if ( $code != "EUC-JP" ) {
			$arg = i18n_convert("$arg","EUC-JP");
		}
		return $arg;
	}
	
	function sjis_convert($arg) {
		// �����R�[�h��SHIFT_JIS�ɕϊ����ĕԂ�
		// ������̕����R�[�h�𔻕�
		$code = i18n_discover_encoding("$arg");
		// ��SHIFT_JIS�̏ꍇ�̂�SHIFT_JIS�ɕϊ�
		if ( $code != "SJIS" ) {
			$arg = i18n_convert("$arg","SJIS");
		}
		return $arg;
	}
	
	//---------------------------------------------------
	// �D����Unpack
	//---------------------------------------------------
	function navyUnpack($lv) {
		global $init;
		
		// bit �Ӗ�
		//-----------
		//  7  ��ID
		//  4  ���
		//  4  �ϋv��
		//  5  �o���l
		//  4  �t���O
		// 24  ���v
		
		$flag = $lv & 0x0f; $lv >>= 4;
		$exp  = $lv & 0x1f; $lv >>= 5;
		$hp   = $lv & 0x0f; $lv >>= 4;
		$kind = $lv & 0x0f; $lv >>= 4;
		$id   = $lv;
		
		return array($id, $kind, $hp, $exp, $flag);
	}
	
	//---------------------------------------------------
	// �D����Pack
	//---------------------------------------------------
	function navyPack($id, $kind, $hp, $exp, $flag) {
		global $init;
		
		// bit �Ӗ�
		//-----------
		//  7  ��ID
		//  4  ���
		//  4  �ϋv��
		//  5  �o���l
		//  4  �t���O
		// 24  ���v
		
		$lv = 0;
		$lv |= $id   & 0x7f; $lv <<= 4;
		$lv |= $kind & 0x0f; $lv <<= 4;
		$lv |= $hp   & 0x0f; $lv <<= 5;
		$lv |= $exp  & 0x1f; $lv <<= 4;
		$lv |= $flag & 0x0f;
		
		return $lv;
	}
	
	//---------------------------------------------------
	// �t�@�C�������b�N����
	//---------------------------------------------------
	function lock() {
		global $init;
		
		$fp = fopen("{$init->dirName}/lock.dat", "w");
		
		for($count = 0; $count < LOCK_RETRY_COUNT; $count++) {
			if(flock($fp, LOCK_EX)) {
				// ���b�N����
				return $fp;
			}
			// ��莞��sleep���A���b�N�����������̂�҂�
			// ��������sleep���邱�ƂŁA���b�N�����x���Փ˂��Ȃ��悤�ɂ���
			usleep((LOCK_RETRY_INTERVAL - mt_rand(0, 300)) * 1000);
		}
		// ���b�N���s
		fclose($fp);
		Error::lockFail();
		return FALSE;
	}
	//---------------------------------------------------
	// �t�@�C�����A�����b�N����
	//---------------------------------------------------
	function unlock($fp) {
		flock($fp, LOCK_UN);
		fclose($fp);
	}
}

?>
