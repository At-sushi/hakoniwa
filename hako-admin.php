<?php

/*******************************************************************

	���돔�� S.E
	
	- �Ǘ��҃��[�h�p�t�@�C�� -
	
	hako-admin.php by SERA - 2013/06/29

*******************************************************************/

require 'config.php';
require 'hako-cgi.php';
require 'hako-html.php';

define("READ_LINE", 1024);
$init = new Init;
$THIS_FILE = $init->baseDir . "/hako-main.php";

class HtmlEntrance extends HTML {
	function enter($urllist, $menulist) {
		global $init;
		print <<<END
<script type="text/javascript" language="JavaScript">
<!-- 
function go(obj) {
	if(obj.menulist.value) {
		obj.action = obj.menulist.value;
	}
}
-->
</script>

<CENTER><a href="{$init->baseDir}/hako-main.php"><span class="big">�g�b�v�֖߂�</span></a></CENTER>
<h1 class="title">{$init->title}<br>�Ǘ��������</h1>
<hr>
<TABLE BORDER=0 width="100%">
<TR valign="top">
<TD width="50%" class="M">
<div id="AdminEnter">
<h2>�Ǘ�����</h2>
<form method="post" onSubmit="go(this)">
<strong>�p�X���[�h�F</strong>
<input type="password" size="32" maxlength="32" name="PASSWORD">
<input type="hidden" name="mode" value="enter">
<select name="menulist">
END;
		for ( $i = 0; $i < count($urllist); $i++ ) {
			if ($i == 0) {
				print "<option value=\"{$init->baseDir}{$urllist[$i]}\" selected=\"selected\">{$menulist[$i]}</option>\n";
			} else {
				print "<option value=\"{$init->baseDir}{$urllist[$i]}\">{$menulist[$i]}</option>\n";
			}
		}
		print "</select>\n";
		print "<input type=\"submit\" value=\"�Ǘ�����\">\n";
		print "</form>\n";
		print <<<END
</TD>
<TD width="50%" class="M">
END;
		print "<div id=\"HistoryLog\">\n";
		print "<h2>���돔��S.E�z�z������̂��m�点</h2>\n";
		print "<DIV style=\"overflow:auto; height:{$init->divHeight}px;\">\n";
		$fileName = "http://hakoniwa.symphonic-net.com/info/hakose_info.txt";
		$fp = @fopen($fileName, "r");
		while($line = @fgets($fp, READ_LINE)) {
			$line = chop($line);
			print "{$line}<br>\n";
		}
		@fclose($fp);
		print "</div></div>\n";
		print <<<END
</TD>
</TR>
</TABLE>
<BR>
END;
	}
}

class Main {
	var $urllist = array();
	var $menulist = array();
	
	function Main() {
		$this->urllist = array( ini_get('safe_mode') ? '/hako-mente-safemode.php' : '/hako-mente.php', '/hako-axes.php', '/hako-keep.php', '/hako-present.php', '/hako-edit.php', '/hako-bfctrl.php');
		$this->menulist = array('�f�[�^�Ǘ�','�A�N�Z�X���O�{��','���a����Ǘ�','�v���[���g','�}�b�v�G�f�B�^','BattleField�Ǘ�');
	}
	
	function execute() {
		$html = new HtmlEntrance;
		$cgi = new Cgi;
		$cgi->getCookies();
		$html->header($cgi->dataSet);
		$html->enter($this->urllist, $this->menulist);
		$html->footer();
	}
}

$start = new Main();
$start->execute();

?>
