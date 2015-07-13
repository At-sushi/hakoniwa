<?php

/*******************************************************************

	���돔�� S.E

	- �����ݒ�p�t�@�C�� -
	
	config.php by SERA - 2013/07/06

*******************************************************************/

define("GZIP", false);  // true: GZIP ���k�]�����g�p  false: �g�p���Ȃ�
define("DEBUG", false); // true: �f�o�b�O false: �ʏ�
define("LOCK_RETRY_COUNT", 10);		// �t�@�C�����b�N�����̃��g���C��
define("LOCK_RETRY_INTERVAL", 1000);// �ă��b�N�������{�܂ł̎���(�~���b)�B�Œ�ł�500���炢���w��

//--------------------------------------------------------------------
class Init {
	// �e��ݒ�l
	
	//---------------------------------------------------
	// �v���O�����t�@�C���Ɋւ���ݒ�
	//---------------------------------------------------
	// �v���O������u���f�B���N�g��
	var $baseDir = "http://localhost/php";
	
	// �摜��u���f�B���N�g��
	var $imgDir  = "http://localhost/php/img";
	// ���[�J���ݒ�p�摜
	var $imgPack = "http://localhost/php/img.zip";
	// ���[�J���ݒ�����y�[�W
	var $imgExp  = "http://localhost/php/local.html";
	// ���[�J���ݒ苭�� YES:1, No:0
	var $setImg  = 0;
	
	// CSS�t�@�C����u���f�B���N�g��
	var $cssDir  = "http://localhost/php/css";
	// CSS���X�g
	var $cssList = array('SkyBlue.css', 'Autumn.css', 'Black.css', 'Blue.css', 'Verdure.css', 'Monotone.css', 'Notebook.css', 'Onepiece.css', 'Tropical.css','Green.css');
	
	// �f�[�^�f�B���N�g���̖��O�i�K���ύX���Ă��������j
	var $dirName = "data";
	// �f�B���N�g���쐬���̃p�[�~�V����
	var $dirMode = 0777;
	
	//�p�X���[�h�̈Í��� true: �Í����Afalse: �Í������Ȃ�
	var $cryptOn      = true; 
	// �p�X���[�h�E�t�@�C��
	var $passwordFile = "password.php";
	
	// �A�N�Z�X���O�t�@�C���̖��O
	var $logname = "ip.csv";
	// �A�N�Z�X���O�ő�L�^���R�[�h��
	var $axesmax = 300;
	
	//---------------------------------------------------
	// �Q�[���S�ʂɊւ���ݒ�
	//---------------------------------------------------
	// �Q�[���^�C�g��
	var $title        = "���돔�� S.E";
	var $adminName    = "�Ǘ��l�̖��O";
	var $adminEmail   = "�����A�h";
	var $urlBbs       = "�f���̃A�h���X";
	var $urlTopPage   = "�g�b�v�y�[�W�̃A�h���X";
	var $urlManu      = "�}�j���A���̃A�h���X";
	
	// 1�^�[�������b��
	var $unitTime     = 10800; // 3���ԁi����ȏ�Z�����邱�Ƃ̓I�X�X���o���܂���j
	
	// �^�[���X�V���̘A���X�V�������邩�H(0:���Ȃ��A1:����)
	var $contUpdate   = 0; // 1�ɂ���ƕ��ב΍�ɂȂ�܂�
	
	// ���̍ő吔�i�ő�250���܂Łj
	var $maxIsland    = 30; // ����ȏ㑝�₷�ƃo�O�������₷���Ȃ�܂�
	
	// ���̑傫��
	var $islandSize   = 12; // �n���݂����ɍL�����ăf�[�^���Ă��m��܂���
	
	// ��������
	var $initialMoney = 1000;
	// �����H��
	var $initialFood  = 100;
	// �����ʐρi�ݒ肵�Ȃ��ꍇ�́A0�j
	var $initialSize  = 0;
	// �������f�[�^�i�g�p���Ȃ��ꍇ��""�A�g�p����ꍇ��"island.txt"�Ƃ��ē��f�[�^�t�@�C��������Ă��������j
	var $initialLand  = "";
	
	// �����ő�l
	var $maxMoney     = 99999; // �o�����X�I�ɂ��̂��炢���Ó�����
	// �H���ő�l
	var $maxFood      = 99999;
	// �؍ލő�l
	var $maxWood      = 10000;
	
	// �V�K���̓o�^���[�h (0:�ʏ�A1:�Ǘ��l)
	var $registMode   = 0;
	// �Ǘ��l���[�h
	var $adminMode;
	
	// ���׌v�����邩�H(0:���Ȃ��A1:����)
	var $performance  = 1;
	var $CPU_start;
	
	//---------------------------------------------------
	// �o�b�N�A�b�v�Ɋւ���ݒ�
	//---------------------------------------------------
	// �Z�[�t���[�h�Ȃ�1�������łȂ��Ȃ�0��ݒ肵�Ă�������
	var $safemode    = 1;
	// �o�b�N�A�b�v�����^�[�������Ɏ�邩
	var $backupTurn  = 6;
	// �o�b�N�A�b�v�����񕪎c����
	var $backupTimes = 5;
	
	//---------------------------------------------------
	// �\���Ɋւ���ݒ�
	//---------------------------------------------------
	// TOP�y�[�W�Ɉ�x�ɕ\�����铇�̐�(0�Ȃ�S���\��)
	var $islandListRange =10;
	
	// �����\�����[�h
	var $moneyMode  = true; // true: 100�̈ʂŎl�̌ܓ�, false: ���̂܂�
	// �g�b�v�y�[�W�ɕ\�����郍�O�̃^�[����
	var $logTopTurn = 4;
	// ���O�t�@�C���ێ��^�[����
	var $logMax     = 8;
	// ���n���O���P�{�����邩�H(0:���Ȃ� 1:���W���� 2:���W�Ȃ�)
	var $logOmit    = 1;
	
	// �������O�ێ��s��
	var $historyMax = 10;
	
	// ���m�点
	var $infoFile   = "info.txt";
	// �L���\�����̍ő�̍����B
	var $divHeight  = 150;
	
	// �����R�}���h�������̓^�[����
	var $giveupTurn = 30;
	
	// �R�}���h���͌��E��
	var $commandMax = 30;
	
	//---------------------------------------------------
	// ���[�J���f���̐ݒ�
	//---------------------------------------------------
	// ���[�J���f���s�����g�p���邩�ǂ���(false:�g�p���Ȃ��Atrue:�g�p����)
	var $useBbs    = true;
	// ���[�J���f���s��
	var $lbbsMax   = 5;
	
	// ���[�J���f���ւ̓��������������邩(false:�֎~�Atrue:����)
	var $lbbsAnon        = false;
	// ���[�J���f���̔����ɔ����҂̓�����\�����邩(false:�\�����Ȃ��Atrue:�\������)
	var $lbbsSpeaker     = true;
	
	// �����̃��[�J���f���ɔ������邽�߂̔�p(0:����)
	var $lbbsMoneyPublic = 0; // ���J
	var $lbbsMoneySecret = 100; // �ɔ�
	
	//---------------------------------------------------
	// �e��P�ʂ̐ݒ�
	//---------------------------------------------------
	// �l���̒P��
	var $unitPop     = "00�l";
	// �H���̒P��
	var $unitFood    = "00�g��";
	// �L���̒P��
	var $unitArea    = "00����";
	// �؂̐��̒P��
	var $unitTree    = "00�{";
	// �����̒P��
	var $unitMoney   = "���~";
	// ���b�̒P��
	var $unitMonster = "�C";
	
	// �؂̒P�ʓ�����̔��l
	var $treeValue   = 10;
	
	// ���O�ύX�̃R�X�g
	var $costChangeName = 500;
	
	// �l��1�P�ʂ�����̐H�����
	var $eatenFood   = 0.2;
	
	// ���c�̎���
	var $oilMoney    = 1000;
	// ���c�̌͊��m��
	var $oilRatio    = 40;
	
	// ���^�[�����ɕ󂭂��̒��I���s���邩�H
	var $lottery     = 50;
	// ���I��
	var $lotmoney    = 30000;
	
	//---------------------------------------------------
	// �����Ɋւ���ݒ�
	//---------------------------------------------------
	// �����쐬�������邩�H(0:���Ȃ��A1:����A2:�Ǘ��҂̂�)
	var $allyUse     = 1;
	
	// �ЂƂ̓����ɂ��������ł��Ȃ��悤�ɂ��邩�H(0:���Ȃ��A1:����)
	var $allyJoinOne = 1;
	
	// �����f�[�^�̊Ǘ��t�@�C��
	var $allyData    = 'ally.dat';
	
	// �����̃}�[�N
	var $allyMark = array(
		'�A','�C','�D','�G','�J',
		'�U','�W','�Y','�^','�_',
		'�`','�q','��','��','��',
		'��','��','��','��','��',
		'��','��','��','��','��',
		'��','��','��','��','��',
		'��','��','��','��','��',
		'��','��','��','��','��',
		'��','��','�W','�Y',
	);
	
	// ���͕������̐��� (�S�p�������Ŏw��) ���ۂ́A<input> ���� MAXLENGTH �𒼂ɏC�����Ă��������B (;^_^A
	var $lengthAllyName    = 15;   // �����̖��O
	var $lengthAllyComment = 40;   // �u�e�����̏󋵁v���ɕ\������閿��̃R�����g
	var $lengthAllyTitle   = 30;   // �u�����̏��v���̏�ɕ\������閿�僁�b�Z�[�W�̃^�C�g��
	var $lengthAllyMessage = 1500; // �u�����̏��v���̏�ɕ\������閿�僁�b�Z�[�W
	
	// �X�^�C���V�[�g�����ς��Ă��Ȃ��̂ŁA�����ɋL�q
	var $tagMoney_  = '<span style="color:#999933; font-weight:bold;">';
	var $_tagMoney  = '</span>';
	
	// �R�����g�̎��������N (0:���Ȃ� 1:����)
	var $autoLink   = 1;
	
	// �ȉ��́A�\���֘A�Ŏg�p���Ă��邾���ŁA���ۂ̋@�\��L���Ă��܂���A����Ȃ�����Ŏ����\�ł��B
	
	// �����E�E�ނ��R�}���h�ōs���悤�ɂ���H(0:���Ȃ��A1:����)
	var $allyJoinComUse = 0;
	
	// �����ɉ������邱�ƂŒʏ�ЊQ�����m���������H(0:���Ȃ�)
	// �ΏۂƂȂ�ЊQ�F�n�k�A�Ôg�A�䕗�A覐΁A����覐΁A����
	var $allyDisDown  = 0;    // �ݒ肷��ꍇ�A�ʏ펞�ɑ΂���{����ݒ�B(��)0.5�Ȃ甼���B2�Ȃ�{��(^^;;;
	var $costMakeAlly = 1000; // �����̌����E�ύX�ɂ������p
	var $costKeepAlly = 500;  // �����̈ێ���(�������Ă��铇�ŋϓ��ɕ��S)
	
	//---------------------------------------------------
	// �R���Ɋւ���ݒ�
	//---------------------------------------------------
	// �~�T�C�����ˋ֎~�^�[��
	var $noMissile     = 20; // ������O�ɂ͎��s��������Ȃ�
	// �����֎~�^�[��
	var $noAssist      = 50; // ������O�ɂ͎��s��������Ȃ�
	
	// �����n�_�ւ̃~�T�C�����˂��\�ɂ��邩�H1:Yes 0:No
	var $multiMissiles = 1;
	
	// �~�T�C����n
	// �o���l�̍ő�l
	var $maxExpPoint   = 200; // �������A�ő�ł�255�܂�
	
	// ���x���̍ő�l
	var $maxBaseLevel  = 5; // �~�T�C����n
	var $maxSBaseLevel = 3; // �C���n
	
	// �o���l�������Ń��x���A�b�v��
	var $baseLevelUp   = array(20, 60, 120, 200); // �~�T�C����n
	var $sBaseLevelUp  = array(50, 200); // �C���n
	
	// �h�q�{�݂̍ő�ϋv��
	var $dBaseHP       = 5;
	// �C��h�q�{�݂̍ő�ϋv��
	var $sdBaseHP      = 3;
	// �h�q�{�݂����b�ɓ��܂ꂽ����������Ȃ�1�A���Ȃ��Ȃ�0
	var $dBaseAuto     = 1;
	
	// �ڕW�̓� ���L�̓����I�����ꂽ��ԂŃ��X�g�𐶐� 1�A���ʂ�TOP�̓��Ȃ�0
	// �~�T�C���̌�˂������ꍇ�ȂǂɎg�p����Ɨǂ���������Ȃ�
	var $targetIsland  = 1;
	
	//---------------------------------------------------
	// �D���Ɋւ���ݒ�
	//---------------------------------------------------
	// �D�̍ő及�L��
	var $shipMax  = 5;
	
	// �D���̎�ށi���D�Ώۂ̑D���j
	var $shipKind = 4; // �ő�15
	
	// �D���̖��O�i10�ȍ~�͍ЊQ�D���ƒ�`�j
	var $shipName = array (
		'�A���D',         # 0
		'���D',           # 1
		'�C��T���D',     # 2
		'���',           # 3
		'',               # 4
		'',               # 5
		'',               # 6
		'',               # 7
		'',               # 8
		'',               # 9
		'�C���D',         # 10
		'',               # 11
		'',               # 12
		'',               # 13
		''                # 14
		);
	
	// �D���ێ���
	var $shipCost = array(100, 200, 300, 500, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
	
	// �D���̗́i�ő�15�j
	var $shipHP   = array(1, 2, 3, 10, 1, 1, 1, 1, 1, 1, 10, 1, 1, 1, 1);
	
	// �D���o���l�̍ő�l�i�ő�ł�255�܂Łj
	var $shipEX   = 100;
	
	// ���x���̍ő�l
	var $shipLv   = 5;
	
	// �o���l�������Ń��x���A�b�v��
	var $shipLevelUp   = array(10, 30, 60, 100);
	
	// �D���ݒ�l�i�m���F�ݒ�l x 0.1%�j
	var $shipIncom          =  200; // �A���D����
	var $shipFood           =  100; // ���D�̐H������
	var $shipIntercept      =  200; // ��͂��~�T�C�����}������m��
	var $disRunAground1     =   10; // ���ʊm��  ���ʏ����ɓ��邽�߂̊m��
	var $disRunAground2     =   10; // ���ʊm��  �D �ʂ̔���
	var $disZorasu          =   30; // ���炷 �o���m��
	var $disViking          =   10; // �C���D �o���m�� �D�P������i��������D�����Ă΂��̕��m��UP�j
	var $disVikingAway      =   30; // �C���D ����m��
	var $disVikingRob       =   50; // �C���D���D
	var $disVikingAttack    =  500; // �C�����U�����Ă���m��
	var $disVikingMinAtc    =    1; // �C���D���^����Œ�_���[�W
	var $disVikingMaxAtc    =    3; // �C���D���^����ő�_���[�W
	
	//---------------------------------------------------
	// �ЊQ�Ɋւ���ݒ�i�m���F�ݒ�l x 0.1%�j
	//---------------------------------------------------
	var $disEarthquake =   5;  // �n�k
	var $disTsunami    =  10;  // �Ôg
	var $disTyphoon    =  20;  // �䕗
	var $disMeteo      =  15;  // 覐�
	var $disHugeMeteo  =   3;  // ����覐�
	var $disEruption   =   5;  // ����
	var $disFire       =  10;  // �΍�
	var $disMaizo      =  30;  // ������
	var $disSto        =  10;  // �X�g���C�L
	var $disTenki      =  30;  // �V�C
	var $disTrain      = 300;  // �d��
	var $disPoo        =  30;  // ���Ɩ\��
	var $disPooPop     = 500;  // �\������������Œ�l���i50000�l�j
	
	// �n�Ւ���
	var $disFallBorder = 100; // ���S���E�̍L��(Hex��)
	var $disFalldown   = 30;  // ���̍L���𒴂����ꍇ�̊m��
	
	//---------------------------------------------------
	// ���b�Ɋւ���ݒ�
	//---------------------------------------------------
	var $disMonsBorder1 = 2000;  // �l���1(���b���x��1)
	var $disMonsBorder2 = 4000;  // �l���2(���b���x��2)
	var $disMonsBorder3 = 6000;  // �l���3(���b���x��3)
	var $disMonsBorder4 = 8000;  // �l���4(���b���x��4)
	var $disMonsBorder5 = 10000; // �l���5(���b���x��5)
	var $disMonster     = 2.5;   // �P�ʖʐς�����̏o����(0.01%�P��)
	
	var $monsterLevel1  = 4;     // �T���W���܂�
	var $monsterLevel2  = 9;     // ���̂�S�[�X�g�܂�
	var $monsterLevel3  = 15;    // �������Ɓi���j�܂�
	var $monsterLevel4  = 23;    // �}�����̂�܂�
	var $monsterLevel5  = 26;    // �C���x�[�_�[�܂�
	
	var $monsterNumber  = 27;    // ���b�̎��
	// ���b�̖��O
	var $monsterName = array (
		'���J���̂�',         # 0
		'���̂�i���j',       # 1
		'���̂�i���j',       # 2
		'�T���W���i���j',     # 3
		'�T���W���i���j',     # 4
		'���b�h���̂�i���j', # 5
		'���b�h���̂�i���j', # 6
		'�_�[�N���̂�i���j', # 7
		'�_�[�N���̂�i���j', # 8
		'���̂�S�[�X�g',     # 9
		'�N�W���i���j',       # 10
		'�N�W���i���j',       # 11
		'���[�v���̂�',       # 12
		'�����[',             # 13
		'�C�i�b�V���i���j',   # 14
		'�������Ɓi���j',     # 15
		'�������Ɓi���j',     # 16
		'�O���[�^�[�����[',   # 17
		'�C�i�b�V���i���j',   # 18
		'�L���O���̂�i���j', # 19
		'�L���O���̂�i���j', # 20
		'�������i���j',       # 21
		'�������i���j',       # 22
		'�}�����̂�',         # 23
		'�n�[�g���̂�',       # 24
		'�P���̂�',           # 25
		'�C���x�[�_�[',       # 26
	);
	// ���b�̉摜(�d����)
	var $monsterImage   = array ('', '', '', 'kouka.gif', 'kouka.gif', '', '', '', '', '', 'kouka.gif', 'kouka.gif', '', 'kouka1.gif', '', 'kouka3.gif', 'kouka3.gif', 'kouka2.gif', '', '', '', '', '', '', '', '');
	
	// �Œ�̗́A�̗͂̕��A����\�́A�o���l�A���̂̒l�i
	var $monsterBHP     = array(10, 1, 1, 1, 1, 2, 3, 2, 2, 2, 3, 3, 9, 5, 4, 4, 3, 5, 9, 4, 5, 6, 6, 7, 8, 5, 99);
	var $monsterDHP     = array( 0, 2, 1, 2, 1, 2, 2, 2, 1, 1, 2, 2, 0, 1, 2, 1, 2, 2, 0, 3, 2, 2, 2, 2, 1, 0, 0);
	var $monsterSpecial = array(0x0, 0x0, 0x0, 0x4, 0x4, 0x1, 0x1, 0x120, 0x20, 0x2, 0x11, 0x10, 0x40, 0x4, 0x200, 0x20000, 0x410, 0x5, 0x240, 0x1020, 0x2020, 0x4400, 0x10100, 0x101, 0x21, 0x2121, 0x42);
	var $monsterExp     = array(20, 6, 5, 7, 6, 9, 8, 17, 12, 10, 10, 9, 30, 13, 15, 20, 25, 22, 40, 45, 43, 50, 50, 48, 60, 100, 200);
	var $monsterValue   = array(1000, 300, 200, 400, 300, 600, 500, 900, 700, 600, 800, 700, 2000, 900, 1000, 500, 1800, 1200, 2500, 3000, 2700, 5000, 4000, 3500, 7000, 10000, 50000);
	// ����\�͂̓��e�́A(�e�\�͂� 1bit �Ɋ��蓖�Ă�)
	// 0x0 ���ɂȂ�
	// 0x1 ��������(�ő�2�����邭)
	// 0x2 �����ƂĂ�����(�ő剽�����邭���s��)
	// 0x4 ��^�[���͍d��
	// 0x10 �����^�[���͍d��
	// 0x20 ���Ԃ��Ă�
	// 0x40 ���[�v����
	// 0x100 �~�T�C���@�����Ƃ�
	// 0x200 ��s�ړ��\��
	// 0x400 �m���ɂȂ�Ƒ唚��
	// 0x1000 �����₷
	// 0x2000 �H�����₷
	// 0x4000 �����炷
	// 0x10000 �H�����炷
	// 0x20000 ���􂷂�
	
	//---------------------------------------------------
	// �܂Ɋւ���ݒ�
	//---------------------------------------------------
	// �^�[���t�����^�[�����ɏo����
	var $turnPrizeUnit = 100;
	// �܂̖��O
	var $prizeName = array (
		'�^�[���t', '�ɉh��', '���ɉh��', '���ɔɉh��', '���a��', '�����a��', '���ɕ��a��', '�Г��', '���Г��', '���ɍГ��', '�f�l���b������', '���b������', '�����b������', '���ɉ��b������', '���b��������',
	);
	
	//---------------------------------------------------
	// �L�O��Ɋւ���ݒ�
	//---------------------------------------------------
	// ����ނ��邩
	var $monumentNumber = 54;
	// ���O
	var $monumentName = array (
		'��̔�', '�_�̔�', '�z�̔�', '���̔�', '���a�̔�', '�L���b�X����', '���m���X', '����', '�킢�̔�', '���X�J��', '����', '���[�[�t', '����', '����', '����', '������', '���A�C', '�n���V', '�o�b�O', '���ݔ�', '�_�[�N���̂瑜', '�e�g����', '�͂˂͂ޑ�', '���P�b�g', '�s���~�b�h', '�A�T�K�I', '�`���[���b�v', '�`���[���b�v', '����', '�T�{�e��', '��l��', '�����w', '�_�a', '�_��', '�Ő�', '�n��', '�X��', '����', '����', '����', '��', '��', '��', '��', '�Ñ���', '�T���^�N���[�X', '��ꂽ�N����', '�e���̌���', '��', '������', '���', '�N���X�}�X�c���[2001', '�Ⴄ����', '�K���̏��_��'
	);
	
	//---------------------------------------------------
	// �l�H�q���Ɋւ���ݒ�
	//---------------------------------------------------
	// ����ނ��邩
	var $EiseiNumber = 6;
	// ���O
	var $EiseiName = array (
		'�C�ۉq��', '�ϑ��q��', '�}���q��', '�R���q��', '�h�q�q��', '�C���M�����['
	);
	
	//---------------------------------------------------
	// �W���Ɋւ���ݒ�
	//---------------------------------------------------
	// ����ނ��邩
	var $ZinNumber = 7;
	// ���O
	var $ZinName = array (
		'�m�[��', '�E�B�X�v', '�V�F�C�h', '�h���A�[�h', '���i', '�W��', '�T���}���_�['
	);
	
	//---------------------------------------------------
	// �A�C�e���Ɋւ���ݒ�
	//---------------------------------------------------
	// ����ނ��邩
	var $ItemNumber = 21;
	// ���O
	var $ItemName = array (
		'�n�}�P', '�m�R�M��', '�֒f�̏�', '�}�X�N', '�|�`�����L��', '�n�}�Q', '�Ȋw��', '������', '��O�̔]', '�}�X�^�[�\�[�h', '�A���}��', '���[�y', '�c��', '���w��', '�Z�p��', '�}�i�E�N���X�^��', '�_�앨�}��', '�o�Ϗ�', '�����O', '���b�h�_�C��', '�؍�'
	);
	
	/********************
		�O���֌W
	********************/
	// �傫������
	var $tagBig_       = '<span class="big">';
	var $_tagBig       = '</span>';
	// ���̖��O�Ȃ�
	var $tagName_      = '<span class="islName">';
	var $_tagName      = '</span>';
	// �����Ȃ������̖��O
	var $tagName2_     = '<span class="islName2">';
	var $_tagName2     = '</span>';
	// ���ʂ̔ԍ��Ȃ�
	var $tagNumber_    = '<span class="number">';
	var $_tagNumber    = '</span>';
	// ���ʕ\�ɂ����錩����
	var $tagTH_        = '<span class="head">';
	var $_tagTH        = '</span>';
	// �J���v��̖��O
	var $tagComName_   = '<span class="command">';
	var $_tagComName   = '</span>';
	// �ЊQ
	var $tagDisaster_  = '<span class="disaster">';
	var $_tagDisaster  = '</span>';
	// ���ʕ\�A�Z���̑���
	var $bgTitleCell   = 'class="TitleCell"';   // ���ʕ\���o��
	var $bgNumberCell  = 'class="NumberCell"';  // ���ʕ\����
	var $bgNameCell    = 'class="NameCell"';    // ���ʕ\���̖��O
	var $bgInfoCell    = 'class="InfoCell"';    // ���ʕ\���̏��
	var $bgMarkCell    = 'class="MarkCell"';    // �����̃}�[�N
	var $bgCommentCell = 'class="CommentCell"'; // ���ʕ\�R�����g��
	var $bgInputCell   = 'class="InputCell"';   // �J���v��t�H�[��
	var $bgMapCell     = 'class="MapCell"';     // �J���v��n�}
	var $bgCommandCell = 'class="CommandCell"'; // �J���v����͍ς݌v��
	
	/********************
		�n�`�ԍ�
	********************/
	var $landSea       =  0; // �C
	var $landWaste     =  1; // �r�n
	var $landPlains    =  2; // ���n
	var $landTown      =  3; // ���n
	var $landForest    =  4; // �X
	var $landFarm      =  5; // �_��
	var $landFactory   =  6; // �H��
	var $landBase      =  7; // �~�T�C����n
	var $landDefence   =  8; // �h�q�{��
	var $landMountain  =  9; // �R
	var $landMonster   = 10; // ���b
	var $landSbase     = 11; // �C���n
	var $landOil       = 12; // �C����c
	var $landMonument  = 13; // �L�O��
	var $landHaribote  = 14; // �n���{�e
	var $landPark      = 15; // �V���n
	var $landFusya     = 16; // ����
	var $landSyoubou   = 17; // ���h��
	var $landNursery   = 18; // �{�B��
	var $landSeaSide   = 19; // �C��(���l)
	var $landSeaResort = 20; // �C�̉�
	var $landCommerce  = 21; // ���ƃr��
	var $landPort      = 22; // �`
	var $landSeaCity   = 23; // �C��s�s
	var $landSdefence  = 24; // �C��h�q�{��
	var $landSfarm     = 25; // �C��_��
	var $landSsyoubou  = 26; // �C����h��
	var $landHatuden   = 27; // ���d��
	var $landBank      = 28; // ��s
	var $landPoll      = 29; // �����y��
	var $landProcity   = 30; // �h�Гs�s
	var $landZorasu    = 31; // ���炷
	var $landSoccer    = 32; // �X�^�W�A��
	var $landRail      = 33; // ���H
	var $landStat      = 34; // �w
	var $landTrain     = 35; // �d��
	var $landSleeper   = 36; // ���b�i�������j
	var $landNewtown   = 37; // �j���[�^�E��
	var $landBigtown   = 38; // ����s�s
	var $landMyhome    = 39; // ����
	var $landFroCity   = 40; // �C��s�s
	var $landSoukoM    = 41; // ����
	var $landSoukoF    = 42; // �H����
	var $landShip      = 43; // �D��
	
	/********************
		�R�}���h
	********************/
	// �R�}���h����
	// ���̃R�}���h���������́A�������͌n�̃R�}���h�͐ݒ肵�Ȃ��ŉ������B
	var $commandDivido = 
		array(
			'�J��,0,10',      // �v��ԍ�00�`10
			'����,11,25',     // �v��ԍ�11�`20
			'����2,26,50',    // �v��ԍ�21�`30
			'�T�b�J�[,51,60', // �v��ԍ�51�`60
			'�U��1,61,70',    // �v��ԍ�61�`80
			'�U��2,71,80',    // �v��ԍ�61�`80
			'�^�c,81,90'      // �v��ԍ�81�`90
		);
	// ���ӁF�X�y�[�X�͓���Ȃ��悤��
	// ���� '�J��,0,10',   # �v��ԍ�00�`10
	// �~�� '�J��, 0,10', # �v��ԍ�00�`10
	
	var $commandTotal = 68; // �R�}���h�̎��
	
	// ����
	var $comList;
	
	// ���n�n
	var $comPrepare      = 01; // ���n
	var $comPrepare2     = 02; // �n�Ȃ炵
	var $comReclaim      = 03; // ���ߗ���
	var $comDestroy      = 04; // �@��
	var $comDeForest     = 05; // ����
	
	// ���n
	var $comPlant        = 11; // �A��
	var $comSeaSide      = 12; // ���l����
	var $comFarm         = 13; // �_�ꐮ��
	var $comSfarm        = 14; // �C��_�ꐮ��
	var $comNursery      = 15; // �{�B��ݒu
	var $comFactory      = 16; // �H�ꌚ��
	var $comCommerce     = 17; // ���ƃr������
	var $comMountain     = 18; // �̌@�ꐮ��
	var $comHatuden      = 19; // ���d��
	var $comBase         = 20; // �~�T�C����n����
	var $comDbase        = 21; // �h�q�{�݌���
	var $comSdbase       = 22; // �C��h�q�{��
	var $comSbase        = 23; // �C���n����
	var $comMonument     = 24; // �L�O�茚��
	var $comHaribote     = 25; // �n���{�e�ݒu
	var $comFusya        = 26; // ���Ԑݒu
	var $comSyoubou      = 27; // ���h������
	var $comSsyoubou     = 28; // �C����h��
	var $comPort         = 29; // �`����
	var $comMakeShip     = 30; // ���D
	var $comSendShip     = 31; // �D�h��
	var $comReturnShip   = 32; // �D�h��
	var $comShipBack     = 33; // �D�j��
	var $comSeaResort    = 34; // �C�̉ƌ���
	var $comPark         = 35; // �V���n����
	var $comSoccer       = 36; // �X�^�W�A������
	var $comRail         = 37; // ���H�~��
	var $comStat         = 38; // �w����
	var $comSeaCity      = 39; // �C��s�s����
	var $comProcity      = 40; // �h�Гs�s
	var $comNewtown      = 41; // �j���[�^�E������
	var $comBigtown      = 42; // ����s�s����
	var $comMyhome       = 43; // �����
	var $comSoukoM       = 44; // ����
	var $comSoukoF       = 45; // �H����
	
	// �T�b�J�[�n
	var $comOffense      = 51; // �U���͋���
	var $comDefense      = 52; // ����͋���
	var $comPractice     = 53; // �������K
	var $comPlaygame     = 54; // �𗬎���
	
	// ���ˌn
	var $comMissileNM    = 61; // �~�T�C������
	var $comMissilePP    = 62; // PP�~�T�C������
	var $comMissileST    = 63; // ST�~�T�C������
	var $comMissileBT    = 64; // BT�~�T�C������
	var $comMissileSP    = 65; // �Ö��e����
	var $comMissileLD    = 66; // ���n�j��e����
	var $comMissileLU    = 67; // �n�`���N�e����
	var $comMissileSM    = 68; // �~�T�C�������~��
	var $comEisei        = 69; // �l�H�q������
	var $comEiseimente   = 70; // �l�H�q�����C��
	var $comEiseiAtt     = 71; // �l�H�q���j��
	var $comEiseiLzr     = 72; // �q�����[�U�[
	var $comSendMonster  = 73; // ���b�h��
	var $comSendSleeper  = 74; // ���b�A��
	
	// �^�c�n
	var $comDoNothing    = 81; // �����J��
	var $comSell         = 82; // �H���A�o
	var $comSellTree     = 83; // �؍ޗA�o
	var $comMoney        = 84; // ��������
	var $comFood         = 85; // �H������
	var $comLot          = 86; // �󂭂��w��
	var $comPropaganda   = 87; // �U�v����
	var $comBoku         = 88; // �l�̈��z��
	var $comHikidasi     = 89; // �q�Ɉ����o��
	var $comGiveup       = 90; // ���̕���
	
	// �������͌n
	var $comAutoPrepare  = 91; // �t�����n
	var $comAutoPrepare2 = 92; // �t���n�Ȃ炵
	var $comAutoDelete   = 93; // �S�R�}���h����
	
	var $comName;
	var $comCost;
	
	// ���̍��W��
	var $pointNumber;
	
	// ����2�w�b�N�X�̍��W
	var $ax = array(0, 1, 1, 1, 0,-1, 0, 1, 2, 2, 2, 1, 0,-1,-1,-2,-1,-1, 0);
	var $ay = array(0,-1, 0, 1, 1, 0,-1,-2,-1, 0, 1, 2, 2, 2, 1, 0,-1,-2,-2);
	
	// �R�����g�ȂǂɁA�\\��̂悤��\������ɒǉ������
	var $stripslashes;
	
	function setVariable() {
		$this->pointNumber = $this->islandSize * $this->islandSize;
		$this->comList = array(
			$this->comPrepare,
			$this->comPrepare2,
			$this->comReclaim,
			$this->comDestroy,
			$this->comDeForest,
			$this->comPlant,
			$this->comSeaSide,
			$this->comFarm,
			$this->comSfarm,
			$this->comNursery,
			$this->comFactory,
			$this->comCommerce,
			$this->comMountain,
			$this->comHatuden,
			$this->comBase,
			$this->comDbase,
			$this->comSbase,
			$this->comSdbase,
			$this->comMonument,
			$this->comHaribote,
			$this->comFusya,
			$this->comSyoubou,
			$this->comSsyoubou,
			$this->comPort,
			$this->comMakeShip,
			$this->comSendShip,
			$this->comReturnShip,
			$this->comShipBack,
			$this->comSeaResort,
			$this->comPark,
			$this->comSoccer,
			$this->comRail,
			$this->comStat,
			$this->comSeaCity,
			$this->comProcity,
			$this->comNewtown,
			$this->comBigtown,
			$this->comMyhome,
			$this->comSoukoM,
			$this->comSoukoF,
			$this->comMissileNM,
			$this->comMissilePP,
			$this->comMissileST,
			$this->comMissileBT,
			$this->comMissileSP,
			$this->comMissileLD,
			$this->comMissileLU,
			$this->comMissileSM,
			$this->comEisei,
			$this->comEiseimente,
			$this->comEiseiAtt,
			$this->comEiseiLzr,
			$this->comSendMonster,
			$this->comSendSleeper,
			$this->comOffense,
			$this->comDefense,
			$this->comPractice,
			$this->comPlaygame,
			$this->comDoNothing,
			$this->comSell,
			$this->comSellTree,
			$this->comMoney,
			$this->comFood,
			$this->comLot,
			$this->comPropaganda,
			$this->comBoku,
			$this->comHikidasi,
			$this->comGiveup,
			$this->comAutoPrepare,
			$this->comAutoPrepare2,
			$this->comAutoDelete,
		);
		
		// �v��̖��O�ƒl�i
		$this->comName[$this->comPrepare]      = '���n';
		$this->comCost[$this->comPrepare]      = 5;
		$this->comName[$this->comPrepare2]     = '�n�Ȃ炵';
		$this->comCost[$this->comPrepare2]     = 100;
		$this->comName[$this->comReclaim]      = '���ߗ���';
		$this->comCost[$this->comReclaim]      = 150;
		$this->comName[$this->comDestroy]      = '�@��';
		$this->comCost[$this->comDestroy]      = 200;
		$this->comName[$this->comDeForest]     = '����';
		$this->comCost[$this->comDeForest]     = 0;
		$this->comName[$this->comPlant]        = '�A��';
		$this->comCost[$this->comPlant]        = 50;
		$this->comName[$this->comSeaSide]      = '���l����';
		$this->comCost[$this->comSeaSide]      = 100;
		$this->comName[$this->comFarm]         = '�_�ꐮ��';
		$this->comCost[$this->comFarm]         = 20;
		$this->comName[$this->comSfarm]        = '�C��_�ꐮ��';
		$this->comCost[$this->comSfarm]        = 500;
		$this->comName[$this->comNursery]      = '�{�B��ݒu';
		$this->comCost[$this->comNursery]      = 20;
		$this->comName[$this->comFactory]      = '�H�ꌚ��';
		$this->comCost[$this->comFactory]      = 100;
		$this->comName[$this->comCommerce]     = '���ƃr������';
		$this->comCost[$this->comCommerce]     = 500;
		$this->comName[$this->comMountain]     = '�̌@�ꐮ��';
		$this->comCost[$this->comMountain]     = 300;
		$this->comName[$this->comHatuden]      = '���d������';
		$this->comCost[$this->comHatuden]      = 300;
		$this->comName[$this->comPort]         = '�`����';
		$this->comCost[$this->comPort]         = 1500;
		$this->comName[$this->comMakeShip]     = '���D';
		$this->comCost[$this->comMakeShip]     = 500;
		$this->comName[$this->comSendShip]     = '�D�h��';
		$this->comCost[$this->comSendShip]     = 200;
		$this->comName[$this->comReturnShip]   = '�D�A��';
		$this->comCost[$this->comReturnShip]   = 200;
		$this->comName[$this->comShipBack]     = '�D�j��';
		$this->comCost[$this->comShipBack]     = 500;
		$this->comName[$this->comRail]         = '���H�~��';
		$this->comCost[$this->comRail]         = 100;
		$this->comName[$this->comStat]         = '�w����';
		$this->comCost[$this->comStat]         = 500;
		$this->comName[$this->comSoccer]       = '�X�^�W�A������';
		$this->comCost[$this->comSoccer]       = 1000;
		$this->comName[$this->comPark]         = '�V���n����';
		$this->comCost[$this->comPark]         = 700;
		$this->comName[$this->comSeaResort]    = '�C�̉ƌ���';
		$this->comCost[$this->comSeaResort]    = 100;
		$this->comName[$this->comFusya]        = '���Ԍ���';
		$this->comCost[$this->comFusya]        = 1000;
		$this->comName[$this->comSyoubou]      = '���h������';
		$this->comCost[$this->comSyoubou]      = 600;
		$this->comName[$this->comSsyoubou]     = '�C����h������';
		$this->comCost[$this->comSsyoubou]     = 1000;
		$this->comName[$this->comBase]         = '�~�T�C����n����';
		$this->comCost[$this->comBase]         = 300;
		$this->comName[$this->comDbase]        = '�h�q�{�݌���';
		$this->comCost[$this->comDbase]        = 800;
		$this->comName[$this->comSbase]        = '�C���n����';
		$this->comCost[$this->comSbase]        = 8000;
		$this->comName[$this->comSdbase]       = '�C��h�q�{�݌���';
		$this->comCost[$this->comSdbase]       = 1000;
		$this->comName[$this->comSeaCity]      = '�C��s�s����';
		$this->comCost[$this->comSeaCity]      = 3000;
		$this->comName[$this->comProcity]      = '�h�Гs�s��';
		$this->comCost[$this->comProcity]      = 3000;
		$this->comName[$this->comNewtown]      = '�j���[�^�E������';
		$this->comCost[$this->comNewtown]      = 1000;
		$this->comName[$this->comBigtown]      = '����s�s����';
		$this->comCost[$this->comBigtown]      = 10000;
		$this->comName[$this->comMyhome]       = '�����';
		$this->comCost[$this->comMyhome]       = 8000;
		$this->comName[$this->comSoukoM]       = '���Ɍ���';
		$this->comCost[$this->comSoukoM]       = 1000;
		$this->comName[$this->comSoukoF]       = '�H���Ɍ���';
		$this->comCost[$this->comSoukoF]       = -1000;
		$this->comName[$this->comMonument]     = '�L�O�茚��';
		$this->comCost[$this->comMonument]     = 9999;
		$this->comName[$this->comHaribote]     = '�n���{�e�ݒu';
		$this->comCost[$this->comHaribote]     = 1;
		$this->comName[$this->comMissileNM]    = '�~�T�C������';
		$this->comCost[$this->comMissileNM]    = 20;
		$this->comName[$this->comMissilePP]    = 'PP�~�T�C������';
		$this->comCost[$this->comMissilePP]    = 50;
		$this->comName[$this->comMissileST]    = 'ST�~�T�C������';
		$this->comCost[$this->comMissileST]    = 100;
		$this->comName[$this->comMissileBT]    = 'BT�~�T�C������';
		$this->comCost[$this->comMissileBT]    = 300;
		$this->comName[$this->comMissileSP]    = '�Ö��e����';
		$this->comCost[$this->comMissileSP]    = 100;
		$this->comName[$this->comMissileLD]    = '���n�j��e����';
		$this->comCost[$this->comMissileLD]    = 500;
		$this->comName[$this->comMissileLU]    = '�n�`���N�e����';
		$this->comCost[$this->comMissileLU]    = 500;
		$this->comName[$this->comMissileSM]    = '�~�T�C�������~��';
		$this->comCost[$this->comMissileSM]    = 0;
		$this->comName[$this->comEisei]        = '�l�H�q���ł��グ';
		$this->comCost[$this->comEisei]        = 9999;
		$this->comName[$this->comEiseimente]   = '�l�H�q���C��';
		$this->comCost[$this->comEiseimente]   = 5000;
		$this->comName[$this->comEiseiAtt]     = '�q���j��C����';
		$this->comCost[$this->comEiseiAtt]     = 30000;
		$this->comName[$this->comEiseiLzr]     = '�q�����[�U�[����';
		$this->comCost[$this->comEiseiLzr]     = 20000;
		$this->comName[$this->comSendMonster]  = '���b�h��';
		$this->comCost[$this->comSendMonster]  = 3000;
		$this->comName[$this->comSendSleeper]  = '���b�A��';
		$this->comCost[$this->comSendSleeper]  = 1500;
		$this->comName[$this->comOffense]      = '�U���͋���';
		$this->comCost[$this->comOffense]      = 300;
		$this->comName[$this->comDefense]      = '����͋���';
		$this->comCost[$this->comDefense]      = 300;
		$this->comName[$this->comPractice]     = '�������K';
		$this->comCost[$this->comPractice]     = 500;
		$this->comName[$this->comPlaygame]     = '�𗬎���';
		$this->comCost[$this->comPlaygame]     = 500;
		$this->comName[$this->comDoNothing]    = '�����J��';
		$this->comCost[$this->comDoNothing]    = 0;
		$this->comName[$this->comSell]         = '�H���A�o';
		$this->comCost[$this->comSell]         = -100;
		$this->comName[$this->comSellTree]     = '�؍ޗA�o';
		$this->comCost[$this->comSellTree]     = -10;
		$this->comName[$this->comMoney]        = '��������';
		$this->comCost[$this->comMoney]        = 100;
		$this->comName[$this->comFood]         = '�H������';
		$this->comCost[$this->comFood]         = -100;
		$this->comName[$this->comLot]          = '�󂭂��w��';
		$this->comCost[$this->comLot]          = 300;
		$this->comName[$this->comPropaganda]   = '�U�v����';
		$this->comCost[$this->comPropaganda]   = 1000;
		$this->comName[$this->comBoku]         = '�l�̈��z��';
		$this->comCost[$this->comBoku]         = 1000;
		$this->comName[$this->comHikidasi]     = '�q�Ɉ����o��';
		$this->comCost[$this->comHikidasi]     = 100;
		$this->comName[$this->comGiveup]       = '���̕���';
		$this->comCost[$this->comGiveup]       = 0;
		$this->comName[$this->comAutoPrepare]  = '���n��������';
		$this->comCost[$this->comAutoPrepare]  = 0;
		$this->comName[$this->comAutoPrepare2] = '�n�Ȃ炵��������';
		$this->comCost[$this->comAutoPrepare2] = 0;
		$this->comName[$this->comAutoDelete]   = '�S�v��𔒎��P��';
		$this->comCost[$this->comAutoDelete]   = 0;
	}
	
	function Init() {
		$this->CPU_start = microtime();
		$this->setVariable();
		mt_srand(time());
		// ���{���Ԃɂ��킹��
		// �C�O�̃T�[�o�ɐݒu����ꍇ�͎��̍s�ɂ���//���͂����B
		// putenv("TZ=JST-9");
		// �\\��̂悤��\������ɒǉ������
		$this->stripslashes = get_magic_quotes_gpc();
	}
}
?>
