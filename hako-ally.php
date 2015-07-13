<?php

/*******************************************************************

	���돔�� S.E
	
	- �����Ǘ��p�t�@�C�� -
	
	hako-ally.php by SERA - 2012/04/03

*******************************************************************/

require 'jcode.phps';
require 'config.php';
require 'hako-cgi.php';
require 'hako-html.php';

define("READ_LINE", 1024);
$init = new Init;
$GAME_TOP = $init->baseDir . "/hako-main.php";
$THIS_FILE = $init->baseDir . "/hako-ally.php";
$BACK_TO_TOP = "<a href=\"{$GAME_TOP}?\">{$init->tagBig_}�g�b�v�֖߂�{$init->_tagBig}</a>�A<A HREF=\"{$THIS_FILE}?\">{$init->tagBig_}�����g�b�v�֖߂�{$init->_tagBig}</A>";

//------------------------------------------------------------
//
//------------------------------------------------------------
class HtmlAlly extends HTML {
	//--------------------------------------------------
	// �������
	//--------------------------------------------------
	function allyTop($hako, $data) {
		global $init;
		
		print "<CENTER><a href=\"{$init->baseDir}/hako-main.php\"><span class=\"big\">�g�b�v�֖߂�</span></a></CENTER>\n";
		print "<h1 class=\"title\">{$init->title}<br>�����Ǘ��c�[��</h1>\n";
		
		if($init->allyUse) {
			print <<<END
<input type="button" value="�����̌����E�ύX�E���U�E�����E�E�ނ͂����炩��" onClick="JavaScript:location.replace('{$GLOBALS['THIS_FILE']}?JoinA=1')">
<h2>�e�����̏�</h2>
END;
		}
		$this->allyInfo($hako);
	}
	//--------------------------------------------------
	// �����̏�
	//--------------------------------------------------
	function allyInfo($hako, $num = 0) {
		global $init;
		
		print <<<END
��L���́A����������<b>���l��</b>�ɂ��Z�o���ꂽ���̂ł��B
<div id="IslandView">
<table border="1">
<tr>
<th {$init->bgTitleCell}>{$init->tagTH_}����{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}����{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}�}�[�N{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}���̐�{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}���l��{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}��L��{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}�_��K��{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}�H��K��{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}���ƋK��{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}�̌@��K��{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}���d���K��{$init->_tagTH}</th>
</tr>
END;
		for($i=0; $i<$hako->allyNumber; $i++) {
			if($num && ($i != $hako->idToAllyNumber[$num])) {
				continue;
			}
			$ally = $hako->ally[$i];
			$j = $i + 1;
			$pop = $farm = $factory = $commerce = $mountain = $hatuden = $missiles = 0;
			for($k=0; $k<$ally['number']; $k++) {
				$id = $ally['memberId'][$k];
				$island = $hako->islands[$hako->idToNumber[$id]];
				$pop      += $island['pop'];
				$farm     += $island['farm'];
				$factory  += $island['factory'];
				$commerce += $island['commerce'];
				$mountain += $island['mountain'];
				$hatuden  += $island['hatuden'];
			}
			
			$name = ($num) ? "{$init->tagName_}{$ally['name']}{$init->_tagName}" : "<a href=\"{$GLOBALS['THIS_FILE']}?AmiOfAlly={$ally['id']}\">{$ally['name']}</a>";
			$pop   = $pop . $init->unitPop;
			$farm  = ($farm <= 0) ? "�ۗL����" : $farm * 10 . $init->unitPop;
			$factory  = ($factory <= 0) ? "�ۗL����" : $factory * 10 . $init->unitPop;
			$commerce  = ($commerce <= 0) ? "�ۗL����" : $commerce * 10 . $init->unitPop;
			$mountain = ($mountain <= 0) ? "�ۗL����" : $mountain * 10 . $init->unitPop;
			$hatuden  = ($hatuden <= 0) ? "0kw" : $hatuden * 1000 . kw;
			
			print <<<END
<tr>
<th {$init->bgNumberCell} rowspan=2>{$init->tagNumber_}$j{$init->_tagNumber}</th>
<td {$init->bgNameCell} rowspan=2>{$name}</td>
<td {$init->bgMarkCell}><b><font color="{$ally['color']}">{$ally['mark']}</font></b></td>
<td {$init->bgInfoCell}>{$ally['number']}��</td>
<td {$init->bgInfoCell}>{$pop}</td>
<td {$init->bgInfoCell}>{$ally['occupation']}%</td>
<td {$init->bgInfoCell}>{$farm}</td>
<td {$init->bgInfoCell}>{$factory}</td>
<td {$init->bgInfoCell}>{$commerce}</td>
<td {$init->bgInfoCell}>{$mountain}</td>
<td {$init->bgInfoCell}>{$hatuden}</td>
</tr>
<tr>
<td {$init->bgCommentCell} colspan=9>{$init->tagTH_}<a href="{$GLOBALS['THIS_FILE']}?Allypact={$ally['id']}">{$ally['oName']}</a>�F{$init->_tagTH}{$ally['comment']}</td>
</tr>
END;
		}
		print "</table>\n";
		print "</div>\n";
		print "<b>��</b>�����̖��O���N���b�N����Ɓu�����̏��v���ցA���哇�̖��O���Ɓu�R�����g�ύX�v���ֈړ����܂��B\n";
	}
	//--------------------------------------------------
	// �����̏��
	//--------------------------------------------------
	function amityOfAlly($hako, $data) {
		global $init;
		
		$num = $data['ALLYID'];
		$ally = $hako->ally[$hako->idToAllyNumber[$num]];
		$allyName = "<FONT COLOR=\"{$ally['color']}\"><B>{$ally['mark']}</B></FONT>{$ally['name']}";
		
		print <<<END
<DIV align='center'>
{$init->tagBig_}{$init->tagName_}{$allyName}{$init->_tagName}�̏��{$init->_tagBig}<br>
{$GLOBALS['BACK_TO_TOP']}</DIV><BR>
<DIV ID='campInfo'>
END;
		// �����󋵂̕\��
		if($ally['number']) {
			$this->allyInfo($hako, $num);
		}
		// ���b�Z�[�W�E����̕\��
		if($ally['message'] != '') {
			$allyTitle = $ally['title'];
			if($allyTitle == '') {
				$allyTitle = '���傩��̃��b�Z�[�W';
			}
			$allyMessage = $ally['message'];
			if($init->autoLink) {
				$allyMessage = ereg_replace("(^|[^=\"'])(http://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)", "\\1<a href=\"\\2\" target=\"_top\">\\2</a>", $allyMessage);
			}
			print <<<END
<HR>
<TABLE BORDER width=80%>
<TR><TH {$init->bgTitleCell}>{$init->tagTH_}$allyTitle{$init->_tagTH}</TH></TR>
<TR><TD {$init->bgCommentCell}><blockquote>$allyMessage</blockquote></TD></TR>
</TABLE>
END;
		}
        // �����o�[�ꗗ�̕\��
		print <<<END
<HR>
<TABLE BORDER><TR>
<TH {$init->bgTitleCell}>{$init->tagTH_}����{$init->_tagTH}</TH>
<TH {$init->bgTitleCell}>{$init->tagTH_}��{$init->_tagTH}</TH>
<TH {$init->bgTitleCell}>{$init->tagTH_}�l��{$init->_tagTH}</TH>
<TH {$init->bgTitleCell}>{$init->tagTH_}�ʐ�{$init->_tagTH}</TH>
<TH {$init->bgTitleCell}>{$init->tagTH_}����{$init->_tagTH}</TH>
<TH {$init->bgTitleCell}>{$init->tagTH_}�H��{$init->_tagTH}</TH>
<TH {$init->bgTitleCell}>{$init->tagTH_}�_��K��{$init->_tagTH}</TH>
<TH {$init->bgTitleCell}>{$init->tagTH_}�H��K��{$init->_tagTH}</TH>
<th {$init->bgTitleCell}>{$init->tagTH_}���ƋK��{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}�̌@��K��{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}���d���K��{$init->_tagTH}</th>
</TR>
END;
		if(!$ally['number']) {
			print "<TR><TH colspan=12>�������Ă��铇������܂���I</TH></TR>";
		}
		foreach ($ally['memberId'] as $id) {
			$number = $hako->idToNumber[$id];
			if(!($number > -1)) continue;
			$island = $hako->islands[$number];
			$money = Util::aboutMoney($island['money']);
			$farm = $island['farm'];
			$factory = $island['factory'];
			$commerce = $island['commerce'];
			$mountain = $island['mountain'];
			$hatuden = $island['hatuden'];
            $ranking = $number + 1;
			$name = Util::islandName($island, $hako->ally, $hako->idToAllyNumber);
			if($island['absent']  == 0) {
				$name = "{$init->tagName_}<a href=\"{$init->baseDir}/hako-main.php?Sight={$island['id']}\">{$name}{$init->_tagName}</a>";
			} else {
				$name = "{$init->tagName2_}<a href=\"{$init->baseDir}/hako-main.php?Sight={$island['id']}\">{$name}</a>({$island['absent']}){$init->_tagName2}";
			}
			$farm = ($farm == 0) ? "�ۗL����" : "{$farm}0$init->unitPop";
			$factory = ($factory == 0) ? "�ۗL����" : "{$factory}0$init->unitPop";
			$commerce  = ($commerce == 0) ? "�ۗL����" : "{$commerce}0$init->unitPop";
			$mountain = ($mountain == 0) ? "�ۗL����" : "{$mountain}0$init->unitPop";
			$hatuden  = ($hatuden == 0) ? "0kw" : "{$hatuden}000kw";
			
			print <<<END
<TR>
<TH {$init->bgNumberCell}>{$init->tagNumber_}$ranking{$init->_tagNumber}</TH>
<TD {$init->bgNameCell}>$name</TD>
<TD {$init->bgInfoCell}>{$island['pop']}$init->unitPop</TD>
<TD {$init->bgInfoCell}>{$island['area']}$init->unitArea</TD>
<TD {$init->bgInfoCell}>$money</TD>
<TD {$init->bgInfoCell}>{$island['food']}$init->unitFood</TD>
<TD {$init->bgInfoCell}>$farm</TD>
<TD {$init->bgInfoCell}>$factory</TD>
<TD {$init->bgInfoCell}>$commerce</TD>
<TD {$init->bgInfoCell}>$mountain</TD>
<TD {$init->bgInfoCell}>$hatuden</TD>
</TR>
END;
		}
		print "</TABLE>\n";
    }
	//--------------------------------------------------
	// �����R�����g�̕ύX
	//--------------------------------------------------
	function tempAllyPactPage($hako, $data) {
		global $init;
		
		$num = $data['ALLYID'];
		$ally = $hako->ally[$hako->idToAllyNumber[$num]];
		$allyMessage = $ally['message'];
		
		$allyMessage = str_replace("<br>", "\n", $allyMessage);
		$allyMessage = str_replace("&amp;", "&", $allyMessage);
		$allyMessage = str_replace("&lt;", "<", $allyMessage);
		$allyMessage = str_replace("&gt;", ">", $allyMessage);
		$allyMessage = str_replace("&quot;", "\"", $allyMessage);
		$allyMessage = str_replace("&#039;", "'", $allyMessage);
		
		print <<<END
<DIV align='center'>
{$init->tagBig_}�R�����g�ύX�i{$init->tagName_}{$ally['name']}{$init->_tagName}�j{$init->_tagBig}<br>
{$GLOBALS['BACK_TO_TOP']}</DIV><BR>

<DIV ID='changeInfo'>
<table border=0 width=50%><tr><td class="M">
<FORM action="{$GLOBALS['THIS_FILE']}" method="POST">
<B>����p�X���[�h�́H</B><BR>
<INPUT TYPE="password" NAME="Allypact" VALUE="{$data['defaultPassword']}" SIZE=32 MAXLENGTH=32 class=f>
<INPUT TYPE="hidden"  NAME="ALLYID" VALUE="{$ally['id']}">
<INPUT TYPE="submit" VALUE="���M" NAME="AllypactButton"><BR>
<B>�R�����g</B><small>(�S�p{$init->lengthAllyComment}���܂ŁF�g�b�v�y�[�W�́u�e�����̏󋵁v���ɕ\������܂�)</small><BR>
<INPUT TYPE="text" NAME="ALLYCOMMENT"  VALUE="{$ally['comment']}" SIZE=100 MAXLENGTH=50><BR>
<BR>
<B>���b�Z�[�W�E����Ȃ�</B><small>(�u�����̏��v���̏�ɕ\������܂�)</small><BR>
�^�C�g��<small>(�S�p{$init->lengthAllyTitle}���܂�)</small><BR>
<INPUT TYPE="text" NAME="ALLYTITLE"  VALUE="{$ally['title']}" SIZE=100 MAXLENGTH=50><BR>
���b�Z�[�W<small>(�S�p{$init->lengthAllyMessage}���܂�)</small><BR>
<TEXTAREA COLS=50 ROWS=16 NAME="ALLYMESSAGE" WRAP="soft">{$allyMessage}</TEXTAREA>
<BR>
�u�^�C�g���v���󗓂ɂ���Ɓw���傩��̃��b�Z�[�W�x�Ƃ����^�C�g���ɂȂ�܂��B<BR>
�u���b�Z�[�W�v���󗓂ɂ���Ɓu�����̏��v���ɂ͉����\������Ȃ��Ȃ�܂��B
</FORM>
</td></tr></table>
</DIV>
END;
	}
	//--------------------------------------------------
	// �����̌����E�ύX�E���U�E�����E�E��
	//--------------------------------------------------
	function newAllyTop($hako, $data) {
		global $init;
		
		$adminMode = 0;
		if(Util::checkPassword("", $data['defaultPassword'])) {
			// �Ǘ��҂̔���́A���َq�̃p�X���[�h�A����̕ύX��
			$adminMode = 1;
		} elseif(!$init->allyUse) {
			$this->allyTop($hako, $data);
		}
		
		$jsIslandList = '';
		for($i=0; $i<$hako->islandNumber; $i++) {
			$name = $hako->islands[$i]['name'];
			$name = preg_replace("/'/", "\'", $name);
			$id = $hako->islands[$i]['id'];
			$jsIslandList .= "island[$id] = '$name';\n";
		}
		$n = $hako->idToAllyNumber[$data['defaultID']];
		if($n == '') {
			$allyname = '';
			$defaultMark = $hako->ally[0];
			$defaultAllyId = '';
		} else {
			$allyname = $hako->ally[$n]['name'];
			$allyname = preg_replace("/'/", "\'", $allyname);
			$defaultMark = $hako->ally[$n]['mark'];
			$defaultAllyId = $hako->ally[$n]['id'];
		}
		$defaultMark = '';
		foreach ($init->allyMark as $aMark) {
			$s = '';
			if($aMark == $defaultMark) $s = ' SELECTED';
			$markList .= "<OPTION VALUE=\"$aMark\"$s>$aMark\n";
		}
		$hx = array(0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F);
		for($i=1; $i<7; $i++) {
			if($n == '') {
				$allycolor[$i] = '0';
			} else {
				$allycolor[$i] = substr($hako->ally[$n]['color'], $i, 1);
			}
			for($j=0; $j<16; $j++) {
				$s = '';
				if($hx[$j] == $allycolor[$i]) $s = ' SELECTED';
				$colorList[$i] .= "<OPTION VALUE=\"{$hx[$j]}\"$s>{$hx[$j]}\n";
			}
		}
		$max = 201;
		if($hako->allyNumber) {
			$jsAllyList = "ally = [";
			$jsAllyIdList = "allyID = [";
			$jsAllyMarkList = "allyMark = [";
			$jsAllyColorList = "allyColor = [";
			for($i=0; $i<count($hako->ally); $i++) {
				$s = "";
				if($hako->ally[$i]['id'] == $defaultAllyId) $s = ' SELECTED';
				$allyList .= "<OPTION VALUE=\"$i\"$s>{$hako->ally[$i]['name']}\n";
				$jsAllyList .= "'{$hako->ally[$i]['name']}'";
				$jsAllyIdList .= "{$hako->ally[$i]['id']}";
				$jsAllyMarkList .= "'{$hako->ally[$i]['mark']}'";
				$jsAllyColorList .= "[";
				for($j=0; $j<6; $j++) {
					$jsAllyColorList .= '\'' . substr($hako->ally[$i]['color'], $j, 1) . '\'';
					if($j < 5) $jsAllyColorList .= ',';
				}
				$jsAllyColorList .= "]";
				if($i < count($hako->ally)) {
					$jsAllyList .= ",\n";
					$jsAllyIdList .= ",\n";
					$jsAllyMarkList .= ",\n";
					$jsAllyColorList .= ",\n";
				}
				if($max <= $hako->ally[$i]['id']) $max = $hako->ally[$i]['id'] + 1;
			}
			$jsAllyList .= "];\n";
			$jsAllyIdList .= "];\n";
			$jsAllyMarkList .= "];\n";
			$jsAllyColorList .= "];\n";
		}
		$str1 = $adminMode ? '(�����e�i���X)' : $init->allyJoinComUse ? '' : '�E�����E�E��';
		$str2 = $adminMode ? '' : 'onChange="colorPack()" onClick="colorPack()"';
		$makeCost = $init->costMakeAlly ? "{$init->costMakeAlly}{$init->unitMoney}" : '����';
		$keepCost = $init->costKeepAlly ? "{$init->costKeepAlly}{$init->unitMoney}" : '����';
		$joinCost = $init->comCost[$init->comAlly] ? "{$init->comCost[$init->comAlly]}{$init->unitMoney}" : '����';
		$joinStr = $init->allyJoinComUse ? '' : "�����E�E�ނ̍ۂ̔�p�́A{$init->tagMoney_}$joinCost{$init->_tagMoney}�ł��B<BR>";
		$str3 = $adminMode ? "����p�X���[�h�́H�i�K�{�j<BR>
<INPUT TYPE=\"password\" NAME=\"OLDPASS\" VALUE=\"{$data['defaultPassword']}\" SIZE=32 MAXLENGTH=32 class=f><BR>����" : "<span class='attention'>(����)</span><BR>
�����̌����E�ύX�̔�p�́A{$init->tagMoney_}{$makeCost}{$init->_tagMoney}�ł��B<BR>
�܂��A���^�[���K�v�Ƃ����ێ����{$init->tagMoney_}$keepCost{$init->_tagMoney}�ł��B<BR>
�i�ێ���͓����ɏ������铇�ŋϓ��ɕ��S���邱�ƂɂȂ�܂��j<BR>
{$joinStr}
</P>
���Ȃ��̓��́H�i�K�{�j<BR>
<SELECT NAME=\"ISLANDID\" {$str2}>
{$hako->islandList}
</SELECT><BR>���Ȃ�";
		$str0 = ($adminMode || ($init->allyUse == 1)) ? '�����E' : '';
		print <<<END
<DIV align='center'>
{$init->tagBig_}������{$str0}�ύX�E���U{$str1}{$init->_tagBig}<br>
{$GLOBALS['BACK_TO_TOP']}</DIV><BR>

<DIV ID='changeInfo'>
<table border=0 width=50%><tr><td class="M"><P>
<FORM name="AcForm" action="{$GLOBALS['THIS_FILE']}" method="POST">
{$str3}
�̃p�X���[�h�́H�i�K�{�j<BR>
<INPUT TYPE="password" NAME="PASSWORD" SIZE=32 MAXLENGTH=32 class=f>
END;
		if($hako->allyNumber) {
			$str4 = $adminMode ? '�E�����E�ύX' : $init->allyJoinComUse ? '' : '�E�����E�E��';
			$str5 = ($adminMode || $init->allyJoinComUse) ? '' : '<INPUT TYPE="submit" VALUE="�����E�E��" NAME="JoinAllyButton">';
			print <<<END
<BR>
<BR><B><FONT SIZE=4>�m���U{$str4}�n</FONT></B>
<BR>�ǂ̓����ł����H<BR>
<SELECT NAME="ALLYNUMBER" onChange="allyPack()" onClick="allyPack()">
{$allyList}
</SELECT>
<BR>
<INPUT TYPE="submit" VALUE="���U" NAME="DeleteAllyButton">
{$str5}
<BR>
END;
		}
		$str7 = $adminMode ? "���哇�̕ύX(��̃��j���[�œ�����I��)<BR> or �����̐V�K�쐬(��̃��j���[�͖���)<BR><SELECT NAME=\"ALLYID\"><OPTION VALUE=\"$max\">�V�K�쐬\n{$hako->islandList}</SELECT><BR>" : "<BR><B><FONT SIZE=4>�m{$str0}�ύX�n</FONT></B><BR>";
		print <<<END
<BR>
{$str7}
�����̖��O�i�ύX�j<small>(�S�p{$init->lengthAllyName}���܂�)</small><BR>
<INPUT TYPE="text" NAME="ALLYNAME" VALUE="$allyname" SIZE=32 MAXLENGTH=32><BR>
�}�[�N�i�ύX�j<BR>
<SELECT NAME="MARK" onChange=colorPack() onClick=colorPack()>
{$markList}
</SELECT>
<ilayer name="PARENT_CTBL" width="100%" height="100%">
   <layer name="CTBL" width="200"></layer>
   <span id="CTBL"></span>
</ilayer>
<BR>
�}�[�N�̐F�R�[�h�i�ύX�j<BR><TABLE BORDER=0><TR>
<TD align='center'>RED</TD>
<TD align='center'>GREEN</TD>
<TD align='center'>BLUE</TD>
</TR><TR>
<TD><SELECT NAME="COLOR1" onChange=colorPack() onClick=colorPack()>
{$colorList[1]}</SELECT><SELECT NAME="COLOR2" onChange=colorPack() onClick=colorPack()>
{$colorList[2]}</SELECT></TD>
<TD><SELECT NAME="COLOR3" onChange=colorPack() onClick=colorPack()>
{$colorList[3]}</SELECT><SELECT NAME="COLOR4" onChange=colorPack() onClick=colorPack()>
{$colorList[4]}</SELECT></TD>
<TD><SELECT NAME="COLOR5" onChange=colorPack() onClick=colorPack()>
{$colorList[5]}</SELECT><SELECT NAME="COLOR6" onChange=colorPack() onClick=colorPack()>
{$colorList[6]}</SELECT></TD>
</TR></TABLE>
<INPUT TYPE="submit" VALUE="����(�ύX)" NAME="NewAllyButton">
<SCRIPT language="JavaScript">
<!--
END;
		if(!$adminMode) {
			print <<<END
function colorPack() {
	var island = new Array(128);
	{$jsIslandList}
	a = document.AcForm.COLOR1.value;
	b = document.AcForm.COLOR2.value;
	c = document.AcForm.COLOR3.value;
	d = document.AcForm.COLOR4.value;
	e = document.AcForm.COLOR5.value;
	f = document.AcForm.COLOR6.value;
	mark = document.AcForm.MARK.value;
	number = document.AcForm.ISLANDID.value;
	
	str = "#" + a + b + c + d + e + f;
	// document.AcForm.AcColorValue.value = str;
	str = '�\���T���v���F�w<B><span class="number"><FONT color="' + str +'">' + mark + '</FONT></B>'
		+ island[number] + '��</span>�x';
	
	if(document.getElementById){
		document.getElementById("CTBL").innerHTML = str;
	} else if(document.all){
		el = document.all("CTBL");
		el.innerHTML = str;
	} else if(document.layers) {
		lay = document.layers["PARENT_CTBL"].document.layers["CTBL"];
		lay.document.open();
		lay.document.write(str);
		lay.document.close();
	}
	return true;
}
function allyPack() {
	{$jsAllyList}
	{$jsAllyMarkList}
	{$jsAllyColorList}
	document.AcForm.ALLYNAME.value = ally[document.AcForm.ALLYNUMBER.value];
	document.AcForm.MARK.value     = allyMark[document.AcForm.ALLYNUMBER.value];
	document.AcForm.COLOR1.value   = allyColor[document.AcForm.ALLYNUMBER.value][0];
	document.AcForm.COLOR2.value   = allyColor[document.AcForm.ALLYNUMBER.value][1];
	document.AcForm.COLOR3.value   = allyColor[document.AcForm.ALLYNUMBER.value][2];
	document.AcForm.COLOR4.value   = allyColor[document.AcForm.ALLYNUMBER.value][3];
	document.AcForm.COLOR5.value   = allyColor[document.AcForm.ALLYNUMBER.value][4];
	document.AcForm.COLOR6.value   = allyColor[document.AcForm.ALLYNUMBER.value][5];
	colorPack();
	return true;
}
END;
		} else {
			print <<<END

function colorPack() {
	var island = new Array(128);
	{$jsIslandList}
	a = document.AcForm.COLOR1.value;
	b = document.AcForm.COLOR2.value;
	c = document.AcForm.COLOR3.value;
	d = document.AcForm.COLOR4.value;
	e = document.AcForm.COLOR5.value;
	f = document.AcForm.COLOR6.value;
	mark = document.AcForm.MARK.value;
	
	str = "#" + a + b + c + d + e + f;
	// document.AcForm.AcColorValue.value = str;
	str = '�\���T���v���F�w<B><span class="number"><FONT color="' + str +'">' + mark + '</FONT></B>'
		+ '����Ղ铇</span>�x';
	if(document.getElementById){
		document.getElementById("CTBL").innerHTML = str;
	} else if(document.all){
		el = document.all("CTBL");
		el.innerHTML = str;
	} else if(document.layers) {
		lay = document.layers["PARENT_CTBL"].document.layers["CTBL"];
		lay.document.open();
		lay.document.write(str);
		lay.document.close();
	}
	
	return true;
}

function allyPack() {
	{$jsAllyList}
	{$jsAllyIdList}
	{$jsAllyMarkList}
	{$jsAllyColorList}
	document.AcForm.ALLYID.value   = allyID[document.AcForm.ALLYNUMBER.value];
	document.AcForm.ALLYNAME.value = ally[document.AcForm.ALLYNUMBER.value];
	document.AcForm.MARK.value     = allyMark[document.AcForm.ALLYNUMBER.value];
	document.AcForm.COLOR1.value   = allyColor[document.AcForm.ALLYNUMBER.value][0];
	document.AcForm.COLOR2.value   = allyColor[document.AcForm.ALLYNUMBER.value][1];
	document.AcForm.COLOR3.value   = allyColor[document.AcForm.ALLYNUMBER.value][2];
	document.AcForm.COLOR4.value   = allyColor[document.AcForm.ALLYNUMBER.value][3];
	document.AcForm.COLOR5.value   = allyColor[document.AcForm.ALLYNUMBER.value][4];
	document.AcForm.COLOR6.value   = allyColor[document.AcForm.ALLYNUMBER.value][5];
	colorPack();
	return true;
}
END;
		}
		print <<<END
colorPack();
//-->
</script>
</form>
</td></tr></table></div>
END;
	}
}
//------------------------------------------------------------
//
//------------------------------------------------------------
class AllySetted extends HtmlAlly {
	// ����R�����g�ύX����
	function allyPactOK($name) {
		global $init;
		print "{$init->tagBig_}{$name}�̃R�����g��ύX���܂����B{$init->_tagBig}<br>{$GLOBALS['BACK_TO_TOP']}\n";
	}
	// �����f�[�^�̍č\��
	function allyDataUp() {
		global $init;
		print "{$init->tagBig_}�����f�[�^���č\�����܂����B{$init->_tagBig}<br>{$GLOBALS['BACK_TO_TOP']}\n";
	}
}
//------------------------------------------------------------
//
//------------------------------------------------------------
class AllyError {
	// ���łɂ��̖��O�̓���������ꍇ
	function newAllyAlready() {
		global $init;
		print "{$init->tagBig_}���̓����Ȃ炷�łɌ�������Ă��܂��B{$init->_tagBig}<br>{$GLOBALS['BACK_TO_TOP']}\n";
	}
	// ���łɂ��̃}�[�N�̓���������ꍇ
	function markAllyAlready() {
		global $init;
		print "{$init->tagBig_}���̃}�[�N�͂��łɎg�p����Ă��܂��B{$init->_tagBig}<br>{$GLOBALS['BACK_TO_TOP']}\n";
	}
	// �ʂ̓������������Ă���
	function leaderAlready() {
		global $init;
		print "{$init->tagBig_}����́A�����̓����ȊO�ɂ͉����ł��܂���B{$init->_tagBig}<br>{$GLOBALS['BACK_TO_TOP']}\n";
	}
	// �ʂ̓����ɉ������Ă���
	function otherAlready() {
		global $init;
		print "{$init->tagBig_}�ЂƂ̓����ɂ��������ł��܂���B{$init->_tagBig}<br>{$GLOBALS['BACK_TO_TOP']}\n";
	}
	// �������肸
	function noMoney() {
		global $init;
		print "{$init->tagBig_}�����s���ł�(/_<�B){$init->_tagBig}<br>{$GLOBALS['BACK_TO_TOP']}\n";
	}
	// ID�`�F�b�N�ɂЂ�������
	function wrongAlly() {
		global $init;
		print "{$init->tagBig_}���Ȃ��͖���ł͂Ȃ��Ǝv���B{$init->_tagBig}<br>{$GLOBALS['BACK_TO_TOP']}\n";
	}
	// �V�K�œ������Ȃ��ꍇ
	function newAllyNoName() {
		global $init;
		print "{$init->tagBig_}�����ɂ��閼�O���K�v�ł��B{$init->_tagBig}<br>{$GLOBALS['BACK_TO_TOP']}\n";
	}
	// �Ǘ��҈ȊO�����s��
	function newAllyForbbiden() {
		global $init;
		print "{$init->tagBig_}�\���󂠂�܂���A��t�𒆎~���Ă��܂��B{$init->_tagBig}<br>{$GLOBALS['BACK_TO_TOP']}\n";
	}
}

//------------------------------------------------------------
//
//------------------------------------------------------------
class MakeAlly {
	//--------------------------------------------------
	// �����E�ύX���C��
	//--------------------------------------------------
	function makeAllyMain($hako, $data) {
		global $init;
		
		$currentID = $data['ISLANDID'];
		$allyID = $data['ALLYID'];
		$currentAnumber = $data['ALLYNUMBER'];
		$allyName = htmlspecialchars($data['ALLYNAME']);
		$allyMark = $data['MARK'];
		$allyColor = "{$data['COLOR1']}{$data['COLOR2']}{$data['COLOR3']}{$data['COLOR4']}{$data['COLOR5']}{$data['COLOR6']}";
		$adminMode = 0;
		// �p�X���[�h�`�F�b�N
		if(Util::checkPassword("", $data['OLDPASS'])) {
			$adminMode = 1;
			if($allyID > 200) {
				$max = $allyID;
				if($hako->allyNumber) {
					for($i=0; $i < count($hako->ally); $i++) {
						if($max <= $hako->ally[$i]['id']) {
							$max = $hako->ally[$i]['id'] + 1;
						}
					}
				}
				$currentID = $max;
			} else {
				$currentID = $hako->ally[$currentAnumber]['id'];
			}
		}
		if(!$init->allyUse && !$adminMode) {
			AllyError::newAllyForbbiden();
			return;
		}
		// �����������邩�`�F�b�N
		if($allyName == '') {
			AllyError::newAllyNoName();
			return;
		}
		// ���������������`�F�b�N
		if(preg_match("/[,\?\(\)\<\>\$]|^���l|^���v$/", $allyName)) {
			// �g���Ȃ����O
			Error::newIslandBadName();
			return;
		}
		// ���O�̏d���`�F�b�N
		$currentNumber = $hako->idToNumber[$currentID];
		if(!($adminMode && ($allyID == '') && ($allyID < 200)) &&
			((Util::nameToNumber($hako, $allyName) != -1) ||
			((Util::aNameToId($hako, $allyName) != -1) && (Util::aNameToId($hako, $allyName) != $currentID)))) {
			// ���łɌ�������
			AllyError::newAllyAlready();
			return;
		}
		// �}�[�N�̏d���`�F�b�N
		if(!($adminMode && ($allyID == '') && ($allyID < 200)) &&
			((Util::aMarkToId($hako, $allyMark) != -1) && (Util::aMarkToId($hako, $allyMark) != $currentID))) {
			// ���łɎg�p����
			AllyError::markAllyAlready();
			return;
		}
		// password�̔���
		$island = $hako->islands[$currentNumber];
		if(!$adminMode && !Util::checkPassword($island['password'], $data['PASSWORD'])) {
			// password�ԈႢ
			Error::wrongPassword();
			return;
		}
		if(!$adminMode && $island['money'] < $init->costMakeAlly) {
			AllyError::noMoney();
			return;
		}
		$n = $hako->idToAllyNumber[$currentID];
		if($n != '') {
			if($adminMode && ($allyID != '') && ($allyID < 200)) {
				$allyMember = $hako->ally[$n]['memberId'];
				$aIsland = $hako->islands[$hako->idToNumber[$allyID]];
				$flag = 0;
				foreach ($allyMember as $id) {
					if($id == $allyID) {
						$flag = 1;
						break;
					}
				}
				if(!$flag) {
					if($aIsland['allyId'][0] == '') {
						$flag = 2;
					}
				}
				if(!$flag) {
					print "{$init->tagBig_}�ύX�ł��܂���B{$init->_tagBig}<br>{$GLOBALS['BACK_TO_TOP']}\n";
					return;
				}
				$hako->ally[$n]['id']       = $allyID;
				$hako->ally[$n]['oName']    = $aIsland['name'];
				if($flag == 2) {
					$hako->ally[$n]['password'] = $aIsland['password'];
					$hako->ally[$n]['score']    = $aIsland['pop'];
					$hako->ally[$n]['number'] ++;
					array_push($hako->ally[$n]['memberId'], $aIsland['id']);
					array_push($aIsland['allyId'], $aIsland['id']);
				}
			} else {
				// ���łɌ������݂Ȃ�ύX
			}
		} else {
			// ���̓��̓����ɓ����Ă���ꍇ�́A�����ł��Ȃ�
			$flag = 0;
			for($i = 0; $i < $hako->allyNumber; $i++) {
				$allyMember = $hako->ally[$i]['memberId'];
				foreach ($allyMember as $id) {
					if($id == $currentID) {
						$flag = 1;
						break;
					}
				}
				if($flag) {
					break;
				}
			}
			if($flag) {
				AllyError::otherAlready();
				return;
			}
			if(($init->allyUse == 2) && !$adminMode && !Util::checkPassword("", $data['PASSWORD'])) {
				AllyError::newAllyForbbiden();
				return;
			}
			// �V�K
			$n = $hako->allyNumber;
			$hako->ally[$n]['id']           = $currentID;
			$memberId = array();
			if($allyID < 200) {
				$hako->ally[$n]['oName']    = $island['name'] . "��";
				$hako->ally[$n]['password'] = $island['password'];
				$hako->ally[$n]['number']   = 1;
				$memberId[0]                = $currentID;
				$hako->ally[$n]['score']    = $island['pop'];
			} else {
				$hako->ally[$n]['oName']    = '';
				$hako->ally[$n]['password'] = Util::encode($data['PASSWORD']);
				$hako->ally[$n]['number']   = 0;
				$hako->ally[$n]['score']    = 0;
			}
			$hako->ally[$n]['occupation']   = 0;
			$hako->ally[$n]['memberId']     = $memberId;
			$island['allyId']               = $memberId;
			$ext = array(0,);
			$hako->ally[$n]['ext']          = $ext;
			$hako->idToAllyNumber[$currentID] = $n;
			$hako->allyNumber++;
		}
		
		// �����̊e��̒l��ݒ�
		$hako->ally[$n]['name']     = $allyName;
		$hako->ally[$n]['mark']     = $allyMark;
		$hako->ally[$n]['color']    = "$allyColor";
		
		// ��p����������
		if(!$adminMode) {
			$island['money'] -= $init->costMakeAlly;
		}
		// �f�[�^�i�[���
		$hako->islands[$currentNumber] = $island;
		
		// �f�[�^�����o��
		Util::allyOccupy($hako);
		Util::allySort($hako);
		$hako->writeAllyFile();
		
		// �g�b�v��
		$html = new HtmlAlly;
		$html->allyTop($hako, $data);
	}
	//--------------------------------------------------
	// ���U
	//--------------------------------------------------
	function deleteAllyMain($hako, $data) {
		global $init;
		
		$currentID = $data['ISLANDID'];
		$currentAnumber = $data['ALLYNUMBER'];
		$currentNumber = $hako->idToNumber[$currentID];
		$island = $hako->islands[$currentNumber];
		$n = $hako->idToAllyNumber[$currentID];
		$adminMode = 0;
		
		// �p�X���[�h�`�F�b�N
		if(Util::checkPassword("", $data['OLDPASS'])) {
			$n = $currentAnumber;
			$currentID = $hako->ally[$n]['id'];
			$adminMode = 1;
		} else {
			// password�̔���
			if(!(Util::checkPassword($island['password'], $data['PASSWORD']))) {
				// �� Password �ԈႢ
				Error::wrongPassword();
				return;
			}
			if(!(Util::checkPassword($hako->ally[$n]['password'], $data['PASSWORD']))) {
				// ���� Password �ԈႢ
				Error::wrongPassword();
				return;
			}
			// �O�̂���ID���`�F�b�N
			if($hako->ally[$n]['id'] != $currentID) {
				AllyError::wrongAlly();
				return;
			}
		}
		$allyMember = $hako->ally[$n]['memberId'];
		
		if($adminMode && (($allyMember[0] != '') || ($n == ''))){
			print "{$init->tagBig_}�폜�ł��܂���B{$init->_tagBig}<br>{$GLOBALS['BACK_TO_TOP']}\n";
			return;
		}
		foreach ($allyMember as $id) {
			$island = $hako->islands[$hako->idToNumber[$id]];
			$newId = array();
			foreach ($island['allyId'] as $aId) {
				if($aId != $currentID) {
					array_push($newId, $aId);
				}
			}
			$island['allyId'] = $newId;
		}
		$hako->ally[$n]['dead'] = 1;
		$hako->idToAllyNumber[$currentID] = '';
		$hako->allyNumber --;
		
		// �f�[�^�i�[���
		$hako->islands[$currentNumber] = $island;
		
		// �f�[�^�����o��
		Util::allyOccupy($hako);
		Util::allySort($hako);
		$hako->writeAllyFile();
		
		// �g�b�v��
		$html = new HtmlAlly;
		$html->allyTop($hako, $data);
	}
	//--------------------------------------------------
	// �����E�E��
	//--------------------------------------------------
	function joinAllyMain($hako, $data) {
		global $init;
		
		$currentID = $data['ISLANDID'];
		$currentAnumber = $data['ALLYNUMBER'];
		$currentNumber = $hako->idToNumber[$currentID];
		$island = $hako->islands[$currentNumber];
		
		// �p�X���[�h�`�F�b�N
		if(!(Util::checkPassword($island['password'], $data['PASSWORD']))) {
			// password�ԈႢ
			Error::wrongPassword();
			return;
		}
		
		// ����`�F�b�N
		if($hako->idToAllyNumber[$currentID]) {
			AllyError::leaderAlready();
			return;
		}
		// ���������`�F�b�N
		$ally = $hako->ally[$currentAnumber];
		if($init->allyJoinOne && ($island['allyId'][0] != '') && ($island['allyId'][0] != $ally['id'])) {
			AllyError::otherAlready();
			return;
		}
		
		$allyMember = $ally['memberId'];
		$newAllyMember = array();
		$flag = 0;
		
		foreach ($allyMember as $id) {
			if(!($hako->idToNumber[$id] > -1)) {
			} elseif($id == $currentID) {
				$flag = 1;
			} else {
				array_push($newAllyMember, $id);
			}
		}
		
		if($flag) {
			// �E��
			$newAlly = array();
			foreach ($island['allyId'] as $id) {
				if($id != $ally['id']) {
					array_push($newAlly, $id);
				}
			}
			$island['allyId'] = $newAlly;
			$ally['score'] -= $island['pop'];
			$ally['number'] --;
		} else {
			// ����
			array_push($newAllyMember, $currentID);
			array_push($island['allyId'], $ally['id']);
			$ally['score'] += $island['pop'];
			$ally['number'] ++;
		}
		$island['money'] -= $init->comCost[$init->comAlly];
		$ally['memberId'] = $newAllyMember;
		
		// �f�[�^�i�[���
		$hako->islands[$currentNumber] = $island;
		$hako->ally[$currentAnumber] = $ally;
		
		// �f�[�^�����o��
		Util::allyOccupy($hako);
		Util::allySort($hako);
		$hako->writeAllyFile();
		
		// �g�b�v��
		$html = new HtmlAlly;
		$html->allyTop($hako, $data);
	}
	//--------------------------------------------------
	// ����R�����g���[�h
	//--------------------------------------------------
	function allyPactMain($hako, $data) {
		$ally = $hako->ally[$hako->idToAllyNumber[$data['ALLYID']]];
		
		if(Util::checkPassword($ally['password'], $data['Allypact'])) {
			$ally['comment'] = Util::htmlEscape($data['ALLYCOMMENT']);
			$ally['title'] = Util::htmlEscape($data['ALLYTITLE']);
			$ally['message'] = Util::htmlEscape($data['ALLYMESSAGE'], 1);
			
			$hako->ally[$hako->idToAllyNumber[$data['ALLYID']]] = $ally;
			// �f�[�^�����o��
			$hako->writeAllyFile();
			
			// �ύX����
			AllySetted::allyPactOK($ally['name']);
		} else {
			// password�ԈႢ
			Error::wrongPassword();
			return;
		}
	}
	//--------------------------------------------------
	// ����f�[�^�Ƃ̃f�[�^��������
	//--------------------------------------------------
	function allyReComp(&$hako) {
		$rt1 = $this->allyDelete($hako);    // ����s�݂ɂ�蓯���f�[�^����폜
		$rt2 = $this->allyMemberDel($hako);    // �����A���l���𓯖��f�[�^����폜
		$rt3 = $this->allyPopComp($hako);    // �l���̍ďW�v�i�^�[�������ɑg�ݍ���ł��Ȃ����߁j
		
		if($rt1 || $rt2 || $rt3) {
			// �f�[�^�����o��
			Util::allyOccupy($hako);
			Util::allySort($hako);
			$hako->writeAllyFile();
			
			// ���b�Z�[�W�o��
			AllySetted::allyDataUp();
			return 1;
		}
		return 0;
	}
	//--------------------------------------------------
	// ����s�݂ɂ�蓯���f�[�^����폜
	//--------------------------------------------------
	function allyDelete(&$hako) {
		$count = 0;
		for($i=0; $i<$hako->allyNumber; $i++) {
			$id = $hako->ally[$i]['id'];
			if(!($hako->idToNumber[$id] > -1)) {
				// �z�񂩂�폜
				$hako->ally[$i]['dead'] = 1;
				$hako->idToAllyNumber[$id] = '';
				$count ++;
			}
		}
		
		if($count) {
			$hako->allyNumber -= $count;
			if($hako->allyNumber < 0) {
				$hako->allyNumber = 0;
			}
			// �f�[�^�i�[���
			$hako->islands[$currentNumber] = $island;
			return 1;
		}
		return 0;
	}
	//--------------------------------------------------
	// �����A���l���𓯖��f�[�^����폜
	//--------------------------------------------------
	function allyMemberDel(&$hako) {
		$flg = 0;
		for($i=0; $i<$hako->allyNumber; $i++) {
			$count = 0;
			$allyMember = $hako->ally[$i]['memberId'];
			$newAllyMember = array();
			foreach ($allyMember as $id) {
				if($hako->idToNumber[$id] > -1) {
					array_push($newAllyMember, $id);
					$count ++;
				}
			}
			if($count != $hako->ally[$i]['number']) {
				$hako->ally[$i]['memberId'] = $newAllyMember;
				$hako->ally[$i]['number'] = $count;
				$flg = 1;
			}
		}
		if($flg) {
			return 1;
		}
		return 0;
    }
	//--------------------------------------------------
	// �l���̍ďW�v�i�^�[���ɑg�ݍ��߂Ώ����s�v�j
	//--------------------------------------------------
	function allyPopComp(&$hako) {
		$flg = 0;
		for($i=0; $i<$hako->allyNumber; $i++) {
			$score = 0;
			$allyMember = $hako->ally[$i]['memberId'];
			foreach ($allyMember as $id) {
				$island = $hako->islands[$hako->idToNumber[$id]];
				$score += $island['pop'];
			}
			if($score != $hako->ally[$i]['score']) {
				$hako->ally[$i]['score'] = $score;
				$flg = 1;
			}
		}
		if($flg) {
			return 1;
		}
		return 0;
	}
}
//------------------------------------------------------------
// Ally
//------------------------------------------------------------
class Ally extends AllyIO {
	var $islandList;    // �����X�g
	var $targetList;    // �^�[�Q�b�g�̓����X�g
	var $defaultTarget;    // �ڕW�⑫�p�^�[�Q�b�g
	
	//--------------------------------------------------
	//
	//--------------------------------------------------
	function readIslands(&$cgi) {
		global $init;
		
		$m = $this->readIslandsFile();
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
	//--------------------------------------------------
	// �����X�g����
	//--------------------------------------------------
	function getIslandList($select = 0) {
		global $init;
		
		$list = "";
		for($i = 0; $i < $this->islandNumber; $i++) {
			if($init->allyUse) {
				$name = Util::islandName($this->islands[$i], $this->ally, $this->idToAllyNumber); // �����}�[�N��ǉ�
			} else {
				$name = $this->islands[$i]['name'];
			}
			$id   = $this->islands[$i]['id'];
			
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
}
//------------------------------------------------------------
// AllyIO
//------------------------------------------------------------
class AllyIO {
	var $islandTurn;     // �^�[����
	var $islandLastTime; // �ŏI�X�V����
	var $islandNumber;   // ���̑���
	var $islandNextID;   // ���Ɋ��蓖�Ă铇ID
	var $islands;        // �S���̏����i�[
	var $idToNumber;
	var $allyNumber;     // �����̑���
	var $ally;           // �e�����̏����i�[
	var $idToAllyNumber; // ����
	
	//--------------------------------------------------
	// �����f�[�^�ǂ݂���
	//--------------------------------------------------
	function readAllyFile() {
		global $init;
		
		$fileName = "{$init->dirName}/{$init->allyData}";
		if(!is_file($fileName)) {
			return false;
		}
		$fp = fopen($fileName, "r");
		Util::lockr($fp);
		$this->allyNumber   = chop(fgets($fp, READ_LINE));
		if($this->allyNumber == '') {
			$this->allyNumber = 0;
		}
		for($i = 0; $i < $this->allyNumber; $i++) {
			$this->ally[$i] = $this->readAlly($fp);
			$this->idToAllyNumber[$this->ally[$i]['id']] = $i;
		}
		// �������Ă��铯����ID���i�[
		for($i = 0; $i < $this->allyNumber; $i++) {
			$member = $this->ally[$i]['memberId'];
			$j = 0;
			foreach ($member as $id) {
				$n = $this->idToNumber[$id];
				if(!($n > -1)) {
					continue;
				}
				array_push($this->islands[$n]['allyId'], $this->ally[$i]['id']);
			}
		}
		Util::unlock($fp);
		fclose($fp);
		return true;
	}
	//--------------------------------------------------
	// �����ЂƂǂ݂���
	//--------------------------------------------------
	function readAlly($fp) {
		$name       = chop(fgets($fp, READ_LINE));
		$mark       = chop(fgets($fp, READ_LINE));
		$color      = chop(fgets($fp, READ_LINE));
		$id         = chop(fgets($fp, READ_LINE));
		$ownerName  = chop(fgets($fp, READ_LINE));
		$password   = chop(fgets($fp, READ_LINE));
		$score      = chop(fgets($fp, READ_LINE));
		$number     = chop(fgets($fp, READ_LINE));
		$occupation = chop(fgets($fp, READ_LINE));
		$tmp        = chop(fgets($fp, READ_LINE));
		$allymember = split(",", $tmp);
		$tmp        = chop(fgets($fp, READ_LINE));
		$ext        = split(",", $tmp);                // �g���̈�
		$comment    = chop(fgets($fp, READ_LINE));
		$title      = chop(fgets($fp, READ_LINE));
		list($title, $message) = split("<>", $title);
		
		return array(
			'name'       => $name,
			'mark'       => $mark,
			'color'      => $color,
			'id'         => $id,
			'oName'      => $ownerName,
			'password'   => $password,
			'score'      => $score,
			'number'     => $number,
			'occupation' => $occupation,
			'memberId'   => $allymember,
			'ext'        => $ext,
			'comment'    => $comment,
			'title'      => $title,
			'message'    => $message,
		);
	}
	//--------------------------------------------------
	// �����f�[�^��������
	//--------------------------------------------------
	function writeAllyFile() {
		global $init;
		
		$fileName = "{$init->dirName}/{$init->allyData}";
		if(!is_file($fileName)) {
			touch($fileName);
		}
		$fp = fopen($fileName, "w");
		Util::lockw($fp);
		fputs($fp, $this->allyNumber . "\n");
		
		for($i = 0; $i < $this->allyNumber; $i++) {
			$this->writeAlly($fp, $this->ally[$i]);
		}
		Util::unlock($fp);
		fclose($fp);
		return true;
	}
	//--------------------------------------------------
	// �����ЂƂ�������
	//--------------------------------------------------
	function writeAlly($fp, $ally) {
		fputs($fp, $ally['name'] . "\n");
		fputs($fp, $ally['mark'] . "\n");
		fputs($fp, $ally['color'] . "\n");
		fputs($fp, $ally['id'] . "\n");
		fputs($fp, $ally['oName'] . "\n");
		fputs($fp, $ally['password'] . "\n");
		fputs($fp, $ally['score'] . "\n");
		fputs($fp, $ally['number'] . "\n");
		fputs($fp, $ally['occupation'] . "\n");
		$allymember = join(",", $ally['memberId']);
		fputs($fp, $allymember . "\n");
		$ext = join(",", $ally['ext']);
		fputs($fp, $ext . "\n");
		fputs($fp, $ally['comment'] . "\n");
		fputs($fp, $ally['title'] . '<>' . $ally['message'] . "\n");
	}
	//---------------------------------------------------
	// �S���f�[�^��ǂݍ���
	//---------------------------------------------------
	function readIslandsFile() {
		global $init;
		
		$fileName = "{$init->dirName}/hakojima.dat";
		if(!is_file($fileName)) {
			return false;
		}
		$fp = fopen($fileName, "r");
		Util::lockr($fp);
		$this->islandTurn     = chop(fgets($fp, READ_LINE));
		$this->islandLastTime = chop(fgets($fp, READ_LINE));
		$this->islandNumber   = chop(fgets($fp, READ_LINE));
		$this->islandNextID   = chop(fgets($fp, READ_LINE));
		
		for($i = 0; $i < $this->islandNumber; $i++) {
			$this->islands[$i] = $this->readIsland($fp);
			$this->idToNumber[$this->islands[$i]['id']] = $i;
			$this->islands[$i]['allyId'] = array();
		}
		Util::unlock($fp);
		fclose($fp);
		
		if($init->allyUse) {
			$this->readAllyFile();
		}
		return true;
	}
	//---------------------------------------------------
	// ���ЂƂǂݍ���
	//---------------------------------------------------
	function readIsland($fp) {
		$name     = chop(fgets($fp, READ_LINE));
		list($name, $owner, $monster, $port, $passenger, $fishingboat, $tansaku, $senkan, $viking) = split(",", $name);
		$id       = chop(fgets($fp, READ_LINE));
		list($id, $starturn) = split(",", $id);
		$prize    = chop(fgets($fp, READ_LINE));
		$absent   = chop(fgets($fp, READ_LINE));
		$comment  = chop(fgets($fp, READ_LINE));
		list($comment, $comment_turn) = split(",", $comment);
		$password = chop(fgets($fp, READ_LINE));
		$point    = chop(fgets($fp, READ_LINE));
		list($point, $pots) = split(",", $point);
		$eisei    = chop(fgets($fp, READ_LINE));
		list($eisei0, $eisei1, $eisei2, $eisei3, $eisei4, $eisei5) = split(",", $eisei);
		$zin      = chop(fgets($fp, READ_LINE));
		list($zin0, $zin1, $zin2, $zin3, $zin4, $zin5, $zin6) = split(",", $zin);
		$item     = chop(fgets($fp, READ_LINE));
		list($item0, $item1, $item2, $item3, $item4, $item5, $item6, $item7, $item8, $item9, $item10, $item11, $item12, $item13, $item14, $item15, $item16, $item17, $item18, $item19) = split(",", $item);
		$money    = chop(fgets($fp, READ_LINE));
		list($money, $lot, $gold) = split(",", $money);
		$food     = chop(fgets($fp, READ_LINE));
		list($food, $rice) = split(",", $food);
		$pop      = chop(fgets($fp, READ_LINE));
		list($pop, $peop) = split(",", $pop);
		$area     = chop(fgets($fp, READ_LINE));
		$job      = chop(fgets($fp, READ_LINE));
		list($farm, $factory, $commerce, $mountain, $hatuden) = split(",", $job);
		$power    = chop(fgets($fp, READ_LINE));
		list($taiji, $rena, $fire) = split(",", $power);
		$tenki    = chop(fgets($fp, READ_LINE));
		$soccer   = chop(fgets($fp, READ_LINE));
		list($soccer, $team, $shiai, $kachi, $make, $hikiwake, $kougeki, $bougyo, $tokuten, $shitten) = split(",", $soccer);
		
		return array(
			'name'         => $name,
			'owner'        => $owner,
			'id'           => $id,
			'starturn'     => $starturn,
			'prize'        => $prize,
			'absent'       => $absent,
			'comment'      => $comment,
			'comment_turn' => $comment_turn,
			'password'     => $password,
			'point'        => $point,
			'pots'         => $pots,
			'money'        => $money,
			'lot'          => $lot,
			'gold'         => $gold,
			'food'         => $food,
			'rice'         => $rice,
			'pop'          => $pop,
			'peop'         => $peop,
			'area'         => $area,
			'farm'         => $farm,
			'factory'      => $factory,
			'commerce'     => $commerce,
			'mountain'     => $mountain,
			'hatuden'      => $hatuden,
			'monster'      => $monster,
			'taiji'        => $taiji,
			'rena'         => $rena,
			'fire'         => $fire,
			'tenki'        => $tenki,
			'soccer'       => $soccer,
			'team'         => $team,
			'shiai'        => $shiai,
			'kachi'        => $kachi,
			'make'         => $make,
			'hikiwake'     => $hikiwake,
			'kougeki'      => $kougeki,
			'bougyo'       => $bougyo,
			'tokuten'      => $tokuten,
			'shitten'      => $shitten,
			'land'         => $land,
			'landValue'    => $landValue,
			'command'      => $command,
			'lbbs'         => $lbbs,
			'port'         => $port,
			'ship'         => array('passenger' => $passenger, 'fishingboat' => $fishingboat, 'tansaku' => $tansaku, 'senkan' => $senkan, 'viking' => $viking),
			'eisei'        => array(0 => $eisei0, 1 => $eisei1, 2 => $eisei2, 3 => $eisei3, 4 => $eisei4, 5 => $eisei5),
			'zin'          => array(0 => $zin0, 1 => $zin1, 2 => $zin2, 3 => $zin3, 4 => $zin4, 5 => $zin5, 6 => $zin6),
			'item'         => array(0 => $item0, 1 => $item1, 2 => $item2, 3 => $item3, 4 => $item4, 5 => $item5, 6 => $item6, 7 => $item7, 8 => $item8, 9 => $item9, 10 => $item10, 11 => $item11, 12 => $item12, 13 => $item13, 14 => $item14, 15 => $item15, 16 => $item16, 17 => $item17, 18 => $item18, 19 => $item19),
		);
	}
}

//------------------------------------------------------------
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
	// �����̐�L���̌v�Z
	//---------------------------------------------------
	function allyOccupy(&$hako) {
		$totalScore = 0;
		
		for($i=0; $i<$hako->allyNumber; $i++) {
			$totalScore += $hako->ally[$i]['score'];
		}
		for($i=0; $i<$hako->allyNumber; $i++) {
			if($totalScore != 0) {
				$hako->ally[$i]['occupation'] = (int)($hako->ally[$i]['score'] / $totalScore * 100);
			} else {
				$hako->ally[$i]['occupation'] = (int)(100 / $hako->allyNumber);
			}
		}
		return;
	}
	
	//---------------------------------------------------
	// �l�����Ƀ\�[�g(�����o�[�W����)
	//---------------------------------------------------
	function allySort(&$hako) {
		usort($hako->ally, 'scoreComp');
	}
	
	//---------------------------------------------------
	// ���̖��O����ԍ����Z�o
	//---------------------------------------------------
	function nameToNumber($hako, $name) {
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
	// �����̖��O����ID�𓾂�
	//---------------------------------------------------
	function aNameToId($hako, $name) {
		// �S������T��
		for($i = 0; $i < $hako->allyNumber; $i++) {
			if($hako->ally[$i]['name'] == $name) {
				return $hako->ally[$i]['id'];
			}
		}
		// ������Ȃ������ꍇ
		return -1;
	}
	
	//---------------------------------------------------
	// �����̃}�[�N����ID�𓾂�
	//---------------------------------------------------
	function aMarkToId($hako, $mark) {
		// �S������T��
		for($i = 0; $i < $hako->allyNumber; $i++) {
			if($hako->ally[$i]['mark'] == $mark) {
				return $hako->ally[$i]['id'];
			}
		}
		// ������Ȃ������ꍇ
		return -1;
	}
	
	//---------------------------------------------------
	// �G�X�P�[�v�����̏���
	//---------------------------------------------------
	function htmlEscape($s, $mode = 0) {
		$s = htmlspecialchars($s);
		$s = str_replace('"','&quot;', $s);
		$s = str_replace("'","&#039;", $s);
		
		if ($mode) {
			$s = str_replace("\r\n", "<br>", $s);
			$s = str_replace("\r", "<br>", $s);
			$s = str_replace("\n", "<br>", $s);
			$s = ereg_replace("(<br>){5,}", "<br>", $s); // ��ʉ��s�΍�
		}
		return $s;
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
	// �t�@�C�������b�N����(�������ݎ�)
	//---------------------------------------------------
	function lockw($fp) {
		set_file_buffer($fp, 0);
		if(!flock($fp, LOCK_EX)) {
			Error::lockFail();
		}
		rewind($fp);
	}
	
	//---------------------------------------------------
	// �t�@�C�������b�N����(�ǂݍ��ݎ�)
	//---------------------------------------------------
	function lockr($fp) {
		set_file_buffer($fp, 0);
		if(!flock($fp, LOCK_SH)) {
			Error::lockFail();
		}
		rewind($fp);
	}
	
	//---------------------------------------------------
	// �t�@�C�����A�����b�N����
	//---------------------------------------------------
	function unlock($fp) {
		flock($fp, LOCK_UN);
	}
}


//------------------------------------------------------------
// ���C������
//------------------------------------------------------------
class Main {
	var $mode;
	var $dataSet = array();
	//--------------------------------------------------
	// ���[�h����
	//--------------------------------------------------
	function execute() {
		global $init;
		
		$ally = new Ally;
		$cgi = new Cgi;
		
		$this->parseInputData();
		$cgi->getCookies();
		
		if(!$ally->readIslands($cgi)) {
			HTML::header($cgi->dataSet);
			Error::noDataFile();
			HTML::footer();
			exit();
		}
		$cgi->setCookies();
		$cgi->lastModified();
		
		$html = new HtmlAlly;
		$com = new MakeAlly;
		$html->header($cgi->dataSet);
		switch($this->mode) {
			case "JoinA":
				// �����̌����E�ύX�E���U�E�����E�E��
				$html->newAllyTop($ally, $this->dataSet);
				break;
				
			case "newally":
				// �����̌����E�ύX
				$com->makeAllyMain($ally, $this->dataSet);
				break;
				
			case "delally":
				// �����̉��U
				$com->deleteAllyMain($ally, $this->dataSet);
				break;
				
			case "inoutally":
				// �����̉����E�E��
				$com->joinAllyMain($ally, $this->dataSet);
				break;
				
			case "Allypact":
				// �R�����g�̕ύX
				$html->tempAllyPactPage($ally, $this->dataSet);
				break;
				
			case "AllypactUp":
				// �R�����g�̍X�V
				$com->allyPactMain($ally, $this->dataSet);
				break;
				
			case "AmiOfAlly":
				// �����̏��
				$html->amityOfAlly($ally, $this->dataSet);
				break;
				
			default:
				// ����f�[�^�Ƃ̃f�[�^���������i�^�[�������ɑg�ݍ���ł��Ȃ����߁j
				if($com->allyReComp($ally)) {
					break;
				}
				$html->allyTop($ally, $this->dataSet);
			break;
		}
		$html->footer();
	}
	//---------------------------------------------------
	// POST�AGET�̃f�[�^���擾
	//---------------------------------------------------
	function parseInputData() {
		global $init;
		
		$this->mode = $_POST['mode'];
		if(!empty($_POST)) {
			while(list($name, $value) = each($_POST)) {
				$value = str_replace(",", "", $value);
				JcodeConvert($value, 0, 2);
				$value = HANtoZEN_SJIS($value);
				
				if($init->stripslashes == true) {
					$this->dataSet["{$name}"] = stripslashes($value);
				} else {
					$this->dataSet["{$name}"] = $value;
				}
			}
			if($this->dataSet['Allypact']) {
				$this->mode = "AllypactUp";
			}
			if(array_key_exists('NewAllyButton', $_POST)) {
				$this->mode = "newally";
			}
			if(array_key_exists('DeleteAllyButton', $_POST)) {
				$this->mode = "delally";
			}
			if(array_key_exists('JoinAllyButton', $_POST)) {
				$this->mode = "inoutally";
			}
		}
		if(!empty($_GET['AmiOfAlly'])) {
			$this->mode = "AmiOfAlly";
			$this->dataSet['ALLYID'] = $_GET['AmiOfAlly'];
		}
		if(!empty($_GET['Allypact'])) {
			$this->mode = "Allypact";
			$this->dataSet['ALLYID'] = $_GET['Allypact'];
		}
		if(!empty($_GET['JoinA'])) {
			$this->mode = "JoinA";
			$this->dataSet['ALLYID'] = $_GET['JoinA'];
		}
	}
}

$start = new Main();
$start->execute();

// �l�����r�A�����ꗗ�p
function scoreComp($x, $y) {
	if($x['dead'] == 1) {
		// ���Ńt���O�������Ă���Ό���
		return +1;
	}
	if($y['dead'] == 1) {
		return -1;
	}
	if($x['score'] == $y['score']) {
		return 0;
	}
	return ($x['score'] > $y['score']) ? -1 : +1;
}

?>
