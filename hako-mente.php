<?php
namespace Hakoniwa\Admin;

/**
 * Re:箱庭諸島SE - メンテナンス用ファイル -
 * @copyright 箱庭諸島 ver2.30
 * @since 箱庭諸島 S.E ver23_r09 by SERA
 * @author hiro <@hiro0218>
 */

require_once 'config.php';
require_once MODELPATH.'/hako-cgi.php';
require_once PRESENTER.'/hako-html.php';
require_once CONTROLLERPATH.'/admin/mente.php';

$init  = new \Init;
$start = new Maintenance\Mente;
