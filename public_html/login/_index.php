<?php
/*
 * [新★会社設立.JP ツール]
 * ユーザーログインページ
 *
 * 更新履歴：2008/12/01	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/login/index.php] start.");


_Log("[/login/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/login/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/login/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/login/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


//認証チェック----------------------------------------------------------------------start
$loginInfo = null;

//ログインしているか？
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
//	_Log("[/login/index.php] ログインしていないなのでログイン画面を表示する。");
//	_Log("[/login/index.php] end.");
//	//ログイン画面を表示する。
//	header("Location: ".URL_LOGIN);
//	exit;

	//ダミーログイン情報を設定する。→新規登録用。
	$loginInfo['usr_auth_id'] = AUTH_NON;
} else {
	//ログイン情報を取得する。
	$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];

	//本画面を使用可能な権限かチェックする。使用不可の場合、ログイン画面に遷移する。
	_CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
}
//認証チェック----------------------------------------------------------------------end



//HTMLテンプレートを読み込む。------------------------------------------------------- start
_Log("[/login/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ start");
$tempFile = '../common/temp_html/temp_base.txt';
_Log("[/login/index.php] {HTMLテンプレートを読み込み} (基本) HTMLテンプレートファイル = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"が存在する場合、表示する。
if ($html !== false && !_IsNull($html)) {
	_Log("[/login/index.php] {HTMLテンプレートを読み込み} (基本) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/login/index.php] {HTMLテンプレートを読み込み} (基本) 【失敗】");
	$html .= "HTMLテンプレートファイルを取得できません。\n";
}


$tempSidebarLoginFile = '../common/temp_html/temp_sidebar_login.txt';
_Log("[/login/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) HTMLテンプレートファイル = '".$tempSidebarLoginFile."'");

$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
	_Log("[/login/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/login/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【失敗】");
}
_Log("[/login/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ end");
//HTMLテンプレートを読み込む。------------------------------------------------------- end


//サイトタイトル
$siteTitle = SITE_TITLE;

//ページタイトル
$pageTitle = PAGE_TITLE_LOGIN;



//タブインデックス
$tabindex = 0;

//DBをオープンする。
$cid = _DB_Open();

//動作モード{1:入力/2:確認/3:完了/4:エラー}
$mode = 1;

//全て表示するか？hidden項目も表示するか？{true:全て表示する。/false:XML設定、権限による表示有無に従う。}
$allShowFlag = false;

//メッセージ
$message = "";
//エラーフラグ
$errorFlag = false;


//入力情報を格納する配列
$info = array();


//パラメーターを取得する。
$xmlName = XML_NAME_LOGIN;//XMLファイル名を設定する。
$id = null;
switch ($_SERVER["REQUEST_METHOD"]) {
	case 'POST':
		//入力値を取得する。
		$info = $_POST;
		_Log("[/login/index.php] POST = '".print_r($info,true)."'");
		//バックスラッシュを取り除く。
		$info = _StripslashesForArray($info);
		_Log("[/login/index.php] POST(バックスラッシュを取り除く。) = '".print_r($info,true)."'");

		break;
	case 'GET':
		//XMLファイル名、ターゲットIDを初期値に追加する。
		$info['condition']['_xml_name_'] = $xmlName;
		$info['condition']['_id_'] = $id;

		break;
}

_Log("[/login/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '".$_SERVER["REQUEST_METHOD"]."'");
_Log("[/login/index.php] XMLファイル名 = '".$xmlName."'");
_Log("[/login/index.php] ターゲットID = '".$id."'");


//XMLを読み込む。
$xmlFile = "../common/form_xml/".$xmlName.".xml";
_Log("[/login/index.php] XMLファイル = '".$xmlFile."'");
$xmlList = _GetXml($xmlFile);

_Log("[/login/index.php] XMLファイル配列 = '".print_r($xmlList,true)."'");

//別画面のログインボタンが押された場合
if ($_POST['login'] != "" || $_POST['login_x'] != "" || $_POST['login_y'] != "") {
	//入力値を詰め替える。
	$info['update']['tbl_user']['usr_e_mail'] = $info['e_mail'];
	$info['update']['tbl_user']['usr_pass'] = $info['pass'];

	$_POST['confirm'] = "ログイン";
}

//本画面のログインボタンが押された場合
if ($_POST['confirm'] != "") {
	//入力値チェック
	$message .= _CheackInputAll($xmlList, $info);

	$userInfo = null;

	if (_IsNull($message)) {
		//エラーが無い場合、認証チェックを表示する。
		$condition = array();
		$condition = $info['update']['tbl_user'];
		$userList = _DB_GetList('tbl_user', $condition, true, null, 'usr_del_flag');
		if (!_IsNull($userList)) {
			if (count($userList) == 1) {
				//メールアドレス、パスワードで検索すると1件のみ見つかるはず！
				$userInfo = $userList[0];
			} elseif (count($userList) > 1) {
				//複数見つかった場合、データエラー!!!
				_Log("[/login/index.php] {ERROR} ユーザーテーブルに重複データ有!!! ⇒ tbl_use.usr_e_mail='".$info['update']['tbl_user']['usr_e_mail']."' / tbl_use.usr_pass='".$info['update']['tbl_user']['usr_pass']."'", 1);
			}
		}
		if (_IsNull($userInfo)) {
			$message .= "メールアドレスもしくはパスワードが異なります。\n";
		}
	}

	if (_IsNull($message)) {
		//エラーが無い場合、ユーザーページを表示する。
		$mode = 2;

		//会社IDを検索する。
		//ユーザー_会社_関連付テーブルを検索する。
		$undeleteOnly = true;
		$condition = array();
		$condition['usr_cmp_rel_user_id'] = $userInfo['usr_user_id'];	//ユーザーID
		$order = "usr_cmp_rel_company_id";								//ソート順=会社IDの昇順(なんでもいいけど…)
		$tblUserCompanyRelationList = _DB_GetListByAssociative('tbl_user_company_relation', 'usr_cmp_rel_company_id', null, $condition, $undeleteOnly, $order, 'usr_cmp_rel_del_flag');
		$companyId = null;
		$llcId = null;
		if (!_IsNull($tblUserCompanyRelationList)) {
			//会社テーブルを検索する。
			$order = "cmp_company_id desc";									//ソート順=会社IDの降順
			$condition = array();
			$condition['cmp_company_id'] = $tblUserCompanyRelationList;		//会社ID
			$condition['cmp_company_type_id'] = MST_COMPANY_TYPE_ID_CMP;	//会社タイプID="株式会社"
			$tblCompanyList = _DB_GetList('tbl_company', $condition, $undeleteOnly, $order, 'cmp_del_flag');
			if (!_IsNull($tblCompanyList)) {
				//先頭を取得する。
				$companyId = $tblCompanyList[0]['cmp_company_id'];
			}

			$condition = array();
			$condition['cmp_company_id'] = $tblUserCompanyRelationList;		//会社ID
			$condition['cmp_company_type_id'] = MST_COMPANY_TYPE_ID_LLC;	//会社タイプID="合同会社"
			$tblCompanyList = _DB_GetList('tbl_company', $condition, $undeleteOnly, $order, 'cmp_del_flag');
			if (!_IsNull($tblCompanyList)) {
				//先頭を取得する。
				$llcId = $tblCompanyList[0]['cmp_company_id'];
			}
		}
		//編集対象の会社IDとして設定する。
		$_SESSION[SID_LOGIN_USER_COMPANY][MST_COMPANY_TYPE_ID_CMP] = $companyId;
		$_SESSION[SID_LOGIN_USER_COMPANY][MST_COMPANY_TYPE_ID_LLC] = $llcId;

		_Log("[/login/index.php] ログインユーザー情報 = '".print_r($userInfo,true)."'");
		//セッションにログイン情報を設定する。
		$_SESSION[SID_LOGIN_USER_INFO] = $userInfo;
		//ユーザーページを表示する。
		header("Location: ../user/");
		exit;
	} else {
		//エラーが有り場合
		$message = "※入力に誤りがあります。\n".$message;
		$errorFlag = true;
	}
}
//戻るボタンが押された場合
elseif ($_POST['back'] != "") {
}
//送信ボタンが押された場合
elseif ($_POST['go'] != "") {
}



//文字をHTMLエンティティに変換する。
$info = _HtmlSpecialCharsForArray($info);
_Log("[/login/index.php] POST(文字をHTMLエンティティに変換する。) = '".print_r($info,true)."'");

_Log("[/login/index.php] mode = '".$mode."'");






//タイトルを設定する。
$title = $pageTitle;

//基本URLを設定する。
$basePath = "..";

//コンテンツを設定する。
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= $pageTitle;
$maincontent .= "</h2>";
$maincontent .= "\n";

$maincontent .= _GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);

//スクリプトを設定する。
$script = null;

//サイドメニューを設定する。
$sidebar = null;

//基本URL
$htmlSidebarLogin = str_replace('{base_url}', $basePath, $htmlSidebarLogin);

$sidebar .= $htmlSidebarLogin;


//パンくずリストを設定する。
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_LOGIN, '', PAGE_TITLE_LOGIN, 2);
//パンくずリストを取得する。
$breadcrumbs = _GetBreadcrumbs();

//WOOROMフッター管理
$wooromFooter = @file_get_contents("http://www.woorom.com/admin/common/footer/get.php?id=17&server_name=".$_SERVER['SERVER_NAME']."&php_self=".$_SERVER['PHP_SELF']);
if ($wooromFooter === false) {
	$wooromFooter = null;
}



//テンプレートを編集する。(必要箇所を置換する。)
//タイトル
if (!_IsNull($title)) $title = "[".$title."] ";
$title = $siteTitle." ".$title;
$html = str_replace('{title}', $title, $html);
//コンテンツ
$html = str_replace('{maincontent}', $maincontent, $html);
//サイドメニュー
$html = str_replace('{sidebar}', $sidebar, $html);
//スクリプト
$html = str_replace('{script}', $script, $html);
//基本URL
$html = str_replace('{base_url}', $basePath, $html);
//パンくずリスト
$html = str_replace('{breadcrumbs}', $breadcrumbs, $html);
//WOOROMフッター管理
$html = str_replace('{woorom_footer}', $wooromFooter, $html);


_Log("[/login/index.php] end.");
echo $html;

?>
