<?php
/*
 * [���������Ω.JP �ġ���]
 * �����ȥޥåץڡ���
 *
 * ��������2010/11/20	d.ishikawa	��������
 *
 */

//����å����ͭ���ˤ��롣
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/login_user/index.php] start.");


_Log("[/login_user/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/login_user/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/login_user/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/login_user/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


//ǧ�ڥ����å�----------------------------------------------------------------------start
$loginInfo = null;

//�������󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
//	_Log("[/login_user/index.php] �������󤷤Ƥ��ʤ��ʤΤǥ���������̤�ɽ�����롣");
//	_Log("[/login_user/index.php] end.");
//	//����������̤�ɽ�����롣
//	header("Location: ".URL_LOGIN);
//	exit;
} else {
	//������������������롣
	$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];
//
//	//�ܲ��̤���Ѳ�ǽ�ʸ��¤������å����롣�����ԲĤξ�硢����������̤����ܤ��롣
//	_CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
}
//ǧ�ڥ����å�----------------------------------------------------------------------end



//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- start
_Log("[/login_user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ start");
$tempFile = '../common/temp_html/temp_base2.txt';
_Log("[/login_user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) HTML�ƥ�ץ졼�ȥե����� = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($html !== false && !_IsNull($html)) {
	_Log("[/login_user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/login_user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) �ڼ��ԡ�");
	$html .= "HTML�ƥ�ץ졼�ȥե����������Ǥ��ޤ���\n";
}


$tempSidebarLoginFile = '../common/temp_html/temp_sidebar_login.txt';
_Log("[/login_user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼��������) HTML�ƥ�ץ졼�ȥե����� = '".$tempSidebarLoginFile."'");

$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
	_Log("[/login_user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼��������) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/login_user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼��������) �ڼ��ԡ�");
}

$tempSidebarUserMenuFile = '../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/login_user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) HTML�ƥ�ץ졼�ȥե����� = '".$tempSidebarUserMenuFile."'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
	_Log("[/login_user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/login_user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) �ڼ��ԡ�");
}


$tempMaincontentLoginuserFile = '../common/temp_html/temp_maincontent_login_user.txt';
_Log("[/login_user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�ᥤ�󥳥�ƥ�ĥ�������) HTML�ƥ�ץ졼�ȥե����� = '".$tempMaincontentLoginuserFile."'");

$htmlMaincontentLoginuser = @file_get_contents($tempMaincontentLoginuserFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($htmlMaincontentLoginuser !== false && !_IsNull($htmlMaincontentLoginuser)) {
	_Log("[/login_user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�ᥤ�󥳥�ƥ�ĥ�������) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/login_user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�ᥤ�󥳥�ƥ�ĥ�������) �ڼ��ԡ�");
}




_Log("[/login_user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ end");
//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- end


//�����ȥ����ȥ�
$siteTitle = SITE_TITLE;

//�ڡ��������ȥ�
$pageTitle = PAGE_TITLE_LOGIN_USER;



//���֥���ǥå���
$tabindex = 0;

//DB�򥪡��ץ󤹤롣
$cid = _DB_Open();

//ư��⡼��{1:����/2:��ǧ/3:��λ/4:���顼}
$mode = 1;

//����ɽ�����뤫��hidden���ܤ�ɽ�����뤫��{true:����ɽ�����롣/false:XML���ꡢ���¤ˤ��ɽ��̵ͭ�˽�����}
$allShowFlag = false;

//��å�����
$message = "";
//���顼�ե饰
$errorFlag = false;


//���Ͼ�����Ǽ��������
$info = array();





//ʸ����HTML����ƥ��ƥ����Ѵ����롣
$info = _HtmlSpecialCharsForArray($info);
_Log("[/login_user/index.php] POST(ʸ����HTML����ƥ��ƥ����Ѵ����롣) = '".print_r($info,true)."'");

_Log("[/login_user/index.php] mode = '".$mode."'");






//�����ȥ�����ꤹ�롣
$title = $pageTitle;

//����URL�����ꤹ�롣
$basePath = "..";

//����ƥ�Ĥ����ꤹ�롣
$maincontent = null;
$maincontent .= "\n";

//����URL
$htmlMaincontentLoginuser = str_replace('{base_url}', $basePath, $htmlMaincontentLoginuser);

$maincontent .= $htmlMaincontentLoginuser;

//$maincontent .= _GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);

//������ץȤ����ꤹ�롣
$script = null;

//�����ɥ�˥塼�����ꤹ�롣
$sidebar = null;

//�������󤷤Ƥ��뤫��
if (isset($_SESSION[SID_LOGIN_USER_INFO])) {
	//����URL
	$htmlSidebarUserMenu = str_replace('{base_url}', $basePath, $htmlSidebarUserMenu);
	//��������桼����̾
	$htmlSidebarUserMenu = str_replace('{user_name}', _GetLoginUserNameHtml($loginInfo), $htmlSidebarUserMenu);
	//���ߤ����Ͼ���
	$htmlSidebarUserMenu = str_replace('{company_info}', null, $htmlSidebarUserMenu);

	$sidebar .= $htmlSidebarUserMenu;
} else {
	//����URL
	$htmlSidebarLogin = str_replace('{base_url}', $basePath, $htmlSidebarLogin);

	$sidebar .= $htmlSidebarLogin;
}



//�ѥ󤯤��ꥹ�Ȥ����ꤹ�롣
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_LOGIN_USER, '', PAGE_TITLE_LOGIN_USER, 2);
//�ѥ󤯤��ꥹ�Ȥ�������롣
$breadcrumbs = _GetBreadcrumbs();

//WOOROM�եå�������
$wooromFooter = @file_get_contents("http://www.woorom.com/admin/common/footer/get.php?id=17&server_name=".$_SERVER['SERVER_NAME']."&php_self=".$_SERVER['PHP_SELF']);
if ($wooromFooter === false) {
	$wooromFooter = null;
}



//�ƥ�ץ졼�Ȥ��Խ����롣(ɬ�ײս���ִ����롣)
//�����ȥ�
if (!_IsNull($title)) $title = "[".$title."] ";
$title = $siteTitle." ".$title;
$html = str_replace('{title}', $title, $html);
//�᥿����
$html = str_replace ('{keywords}', PAGE_KEYWORDS_HOME, $html);
$html = str_replace ('{description}', PAGE_DESCRIPTION_HOME, $html);
//����ƥ��
$html = str_replace('{maincontent}', $maincontent, $html);
//�����ɥ�˥塼
$html = str_replace('{sidebar}', $sidebar, $html);
//������ץ�
$html = str_replace('{script}', $script, $html);
//����URL
$html = str_replace('{base_url}', $basePath, $html);
//�ѥ󤯤��ꥹ��
$html = str_replace('{breadcrumbs}', $breadcrumbs, $html);
//WOOROM�եå�������
$html = str_replace('{woorom_footer}', $wooromFooter, $html);


_Log("[/login_user/index.php] end.");
echo $html;

?>