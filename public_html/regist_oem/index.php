<?php
/*
 * [新★会社設立.JP ツール]
 * ユーザー登録ページ
 * OEM・代理店制度用
 *
 * 更新履歴：2013/02/12	d.ishikawa	新規作成 (/regist/index.phpをコピーして作成した。)
 *
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/regist/index.php] start.");


_Log("[/regist/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/regist/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/regist/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/regist/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


//認証チェック----------------------------------------------------------------------start
$loginInfo = null;

//このページは、新規登録のみになった。
//ダミーログイン情報を設定する。→新規登録用。
$loginInfo['usr_auth_id'] = AUTH_NON;

////ログインしているか？
//if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
////	_Log("[/regist/index.php] ログインしていないなのでログイン画面を表示する。");
////	_Log("[/regist/index.php] end.");
////	//ログイン画面を表示する。
////	header("Location: ".URL_LOGIN);
////	exit;
//
//	//ダミーログイン情報を設定する。→新規登録用。
//	$loginInfo['usr_auth_id'] = AUTH_NON;
//} else {
//	//ログイン情報を取得する。
//	$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];
//
//	//本画面を使用可能な権限かチェックする。使用不可の場合、ログイン画面に遷移する。
//	_CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
//}
//認証チェック----------------------------------------------------------------------end



//HTMLテンプレートを読み込む。------------------------------------------------------- start
_Log("[/regist/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ start");
$tempFile = '../common/temp_html/temp_base.txt';
_Log("[/regist/index.php] {HTMLテンプレートを読み込み} (基本) HTMLテンプレートファイル = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"が存在する場合、表示する。
if ($html !== false && !_IsNull($html)) {
	_Log("[/regist/index.php] {HTMLテンプレートを読み込み} (基本) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/regist/index.php] {HTMLテンプレートを読み込み} (基本) 【失敗】");
	$html .= "HTMLテンプレートファイルを取得できません。\n";
}


$tempSidebarLoginFile = '../common/temp_html/temp_sidebar_login.txt';
_Log("[/regist/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) HTMLテンプレートファイル = '".$tempSidebarLoginFile."'");

$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
	_Log("[/regist/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/regist/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【失敗】");
}

$tempSidebarUserMenuFile = '../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/regist/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) HTMLテンプレートファイル = '".$tempSidebarUserMenuFile."'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
	_Log("[/regist/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/regist/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【失敗】");
}

_Log("[/regist/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ end");
//HTMLテンプレートを読み込む。------------------------------------------------------- end


//サイトタイトル
$siteTitle = SITE_TITLE;

//ページタイトル
$pageTitle = null;
////ログインしているか？
//if (isset($_SESSION[SID_LOGIN_USER_INFO])) {
//	$pageTitle = PAGE_TITLE_REGIST_LOGIN;
//} else {
//	$pageTitle = PAGE_TITLE_REGIST;
//}
$pageTitle = PAGE_TITLE_REGIST_OEM;

//クライアント様メールアドレス
$clientMail = COMPANY_E_MAIL;
//マスター用メールアドレス
$masterMailList = $_COMPANY_MASTER_MAIL_LIST;

//テスト用
if (false) {
//if (true) {
	//クライアント様メールアドレス
	$clientMail = "ishikawa@woorom.com";
	//マスター用メールアドレス
	//「,」でくぎって送信先を追加して下さい。
	$masterMailList = array("ishikawa@woorom.com", "ishikawa@woorom.com");
}







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
$xmlName = XML_NAME_USER_OEM;//XMLファイル名を設定する。
$id = null;
switch ($_SERVER["REQUEST_METHOD"]) {
	case 'POST':
//		//XMLファイル名
//		$xmlName = (isset($_POST['condition']['_xml_name_'])?$_POST['condition']['_xml_name_']:null);
		//ターゲットID
		$id = (isset($_POST['condition']['_id_'])?$_POST['condition']['_id_']:null);

		//入力値を取得する。
		$info = $_POST;
		_Log("[/regist/index.php] POST = '".print_r($info,true)."'");
		//バックスラッシュを取り除く。
		$info = _StripslashesForArray($info);
		_Log("[/regist/index.php] POST(バックスラッシュを取り除く。) = '".print_r($info,true)."'");

		//「半角カタカナ」を「全角カタカナ」に変換する。→メールで半角カナが文字化けするので。
		$info =_Mb_Convert_KanaForArray($info);
		_Log("[/user/pay/index.php] POST(「半角カタカナ」を「全角カタカナ」に変換する。) = '".print_r($info,true)."'");


		break;
	case 'GET':
//		//XMLファイル名
//		$xmlName = (isset($_GET['xml_name'])?$_GET['xml_name']:null);
		//ターゲットID
		$id = (isset($_GET['id'])?$_GET['id']:null);

		//遷移元ページ
		$pId = (isset($_GET['p_id'])?$_GET['p_id']:null);



//		//権限によって、表示するユーザー情報を制限する。
//		switch($loginInfo['usr_auth_id']){
//			case AUTH_NON://権限無し
//
//				$id = null;
//
//				//遷移元ページはどこか？
//				switch ($pId) {
//					case PAGE_ID_USER://ユーザーページ
//						//自分のユーザー情報のみ表示する。
//						if (isset($loginInfo['usr_user_id']) && !_IsNull($loginInfo['usr_user_id'])) {
//							$id = $loginInfo['usr_user_id'];
//						}
//						break;
//				}
//
//				//ログインしているか？
//				if (isset($_SESSION[SID_LOGIN_USER_INFO])) {
//					//自分のユーザー情報のみ表示する。
//					if (isset($loginInfo['usr_user_id']) && !_IsNull($loginInfo['usr_user_id'])) {
//						$id = $loginInfo['usr_user_id'];
//					}
//				}
//				break;
//		}
		$id = null;

		//初期値を設定する。
		$undeleteOnly4def = false;

		$info['update'] = _GetDefaultInfo($xmlName, $id, $undeleteOnly4def);

		//XMLファイル名、ターゲットIDを初期値に追加する。
		$info['condition']['_xml_name_'] = $xmlName;
		$info['condition']['_id_'] = $id;


//		//設定されている場合=更新の場合
//		if (isset($_GET['id'])) {
//			//動作モードをセッションに保存する。動作モード="他画面経由の表示"
//			$_SESSION[SID_INFO_MODE] = MST_MODE_FROM_OTHER;
//		} else {
//			//動作モードをセッションに保存する。動作モード="単独表示"
//			$_SESSION[SID_INFO_MODE] = MST_MODE_FROM_MENU;
//		}
//

		//遷移元ページをセッションに保存する。
		$_SESSION[SID_USER_FROM_PAGE_ID] = $pId;

		break;
}

_Log("[/regist/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '".$_SERVER["REQUEST_METHOD"]."'");
_Log("[/regist/index.php] XMLファイル名 = '".$xmlName."'");
_Log("[/regist/index.php] ターゲットID = '".$id."'");


//プランマスタ
$condition4Mst = null;
$undeleteOnly4Mst = true;
$order4Mst = "lpad(show_order,10,'0'),id";
$mstPlanList = _DB_GetList('mst_plan', $condition4Mst, $undeleteOnly4Mst, $order4Mst, 'del_flag', 'id');
if (!_IsNull($mstPlanList)) {
	//通常プランを削除する。
	unset($mstPlanList[MST_PLAN_ID_NORMAL]);
}

$otherList = array(
	'mst_plan' => $mstPlanList
);


//XMLを読み込む。
$xmlFile = "../common/form_xml/".$xmlName.".xml";
_Log("[/regist/index.php] XMLファイル = '".$xmlFile."'");
$xmlList = _GetXml($xmlFile, $otherList);

_Log("[/regist/index.php] XMLファイル配列 = '".print_r($xmlList,true)."'");

//ユーザーIDが指定されている場合、更新なので、「利用規約」項目を削除する。
if (!_IsNull($id)) {
	$xmlList = _DeleteXmlByTagAndValue($xmlList, 'item_id', 'usr_rule');
	_Log("[/regist/index.php] XMLファイル配列(「利用規約」項目を削除後) = '".print_r($xmlList,true)."'");
}


//確認ボタンが押された場合
if ($_POST['confirm'] != "") {
	//入力値チェック
	$message .= _CheackInputAll($xmlList, $info);

	//メールアドレスの重複チェック
	if (isset($info['update']['tbl_user']['usr_e_mail']) && !_IsNull($info['update']['tbl_user']['usr_e_mail'])) {
		$condition4email = array();
		$condition4email['usr_e_mail'] = $info['update']['tbl_user']['usr_e_mail'];
		$bufList = _DB_GetList('tbl_user', $condition4email, true, null, 'usr_del_flag', 'usr_user_id');
		if (!_IsNull($bufList)) {
			//ユーザーIDが設定済みの場合、検索結果から自分自身のデータを削除する。
			if (isset($info['update']['tbl_user']['usr_user_id']) && !_IsNull($info['update']['tbl_user']['usr_user_id'])) {
				unset($bufList[$info['update']['tbl_user']['usr_user_id']]);
			}
			if (count($bufList) > 0) {
				$message .= "メールアドレスは既に登録済みです。\n";
			}
		}
	}

	if (_IsNull($message)) {
		//エラーが無い場合、確認画面を表示する。
		$mode = 2;

		//$message .= "※入力内容を確認して、「更新」ボタンを押してください。";
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

	//メールアドレスの重複チェック(再チェック)
	if (isset($info['update']['tbl_user']['usr_e_mail']) && !_IsNull($info['update']['tbl_user']['usr_e_mail'])) {
		$condition4email = array();
		$condition4email['usr_e_mail'] = $info['update']['tbl_user']['usr_e_mail'];
		$bufList = _DB_GetList('tbl_user', $condition4email, true, null, 'usr_del_flag', 'usr_user_id');
		if (!_IsNull($bufList)) {
			//ユーザーIDが設定済みの場合、検索結果から自分自身のデータを削除する。
			if (isset($info['update']['tbl_user']['usr_user_id']) && !_IsNull($info['update']['tbl_user']['usr_user_id'])) {
				unset($bufList[$info['update']['tbl_user']['usr_user_id']]);
			}
			if (count($bufList) > 0) {
				$message .= "メールアドレスは既に登録済みです。\n";
			}
		}
	}

	if (_IsNull($message)) {
		//エラーが無い場合、登録する。

		//新規登録か？
		$newFlag = false;
		if (_IsNull($info['condition']['_id_'])) $newFlag = true;


		//更新・登録をする。(※$infoは最新情報に更新される。)
		$res = _UpdateInfo($info);
		if ($res === false) {
			//エラーが有り場合
			$message = "登録に失敗しました。";
			$errorFlag = true;
		} else {

			if ($newFlag) {
				//メール本文の共通部分を設定する。
				$body = null;
				$body .= _CreateMailAll($xmlList, $info);//※この時点では、$infoに「利用規約」の入力値は削除されている。→メールには使えない。

				_Log("[/regist/index.php] メール本文(_CreateMailAll) = '".$body."'");

				$body .= "\n";
				$body .= "\n";
				$body .= "\n";
				$body .= "\n";

				$body .= "--------------------------------------------------------\n";
				$body .= $siteTitle."\n";
				if (!_IsNull(COMPANY_NAME)) $body .= COMPANY_NAME."\n";
				if (!_IsNull(COMPANY_ZIP)) $body .= COMPANY_ZIP."\n";
				if (!_IsNull(COMPANY_ADDRESS)) $body .= COMPANY_ADDRESS."\n";
				if (!_IsNull(COMPANY_TEL)) $body .= "TEL：".COMPANY_TEL."\n";
				if (!_IsNull(COMPANY_FAX)) $body .= "FAX：".COMPANY_FAX."\n";
				$body .= "E-mail：".$clientMail." \n";
				if (!_IsNull(COMPANY_BUSINESS_HOURS)) $body .= "営業時間：".COMPANY_BUSINESS_HOURS."\n";
				$body .= "--------------------------------------------------------\n\n";

				$body .= "登録日時：".date("Y年n月j日 H時i分")."\n";
				$body .= $_SERVER["REMOTE_ADDR"]."\n";

				//管理者用メール本文を設定する。
				$adminBody = "";
				//$adminBody .= $siteTitle." \n";
				//$adminBody .= "\n";
				$adminBody .= "**************************************************************************************\n";
				$adminBody .= "『".$siteTitle."』にユーザー登録がありました。\n";
				$adminBody .= "**************************************************************************************\n";
				$adminBody .= "\n";
				$adminBody .= $body;

				$adminBody .= "\n";
				$adminBody .= "\n";
				$adminBody .= "postpostpostpostpostpostpostpostpostpostpostpostpostpostpost\n";
				$adminBody .= "\n";
				$adminBody .= _GetPostAddress($info);



				//お客様用メール本文を設定する。
				$customerBody = "";
				$customerBody .= $info['update']['tbl_user']['usr_family_name']." ".$info['update']['tbl_user']['usr_first_name']." 様\n";
				$customerBody .= "\n";
				$customerBody .= "**************************************************************************************\n";
				$customerBody .= "この度は、『".$siteTitle."』にユーザー登録していただきありがとうございました。\n";
				$customerBody .= "確認のため、下記にお客様のご登録の内容をお知らせいたします。\n";
				$customerBody .= "**************************************************************************************\n";
				$customerBody .= "\n";
				$customerBody .= $body;


				//管理者用タイトルを設定する。
				$adminTitle = "[".$siteTitle."] ユーザー登録(OEM・代理店制度) (".$info['update']['tbl_user']['usr_family_name']." ".$info['update']['tbl_user']['usr_first_name']." 様)";
				//お客様用タイトルを設定する。
				$customerTitle = "[".$siteTitle."] ユーザー登録(OEM・代理店制度)ありがとうございました";

				mb_language("Japanese");
				
				$parameter = "-f ".$clientMail;

				//メール送信
				//お客様に送信する。
				$rcd = mb_send_mail($info['update']['tbl_user']['usr_e_mail'], $customerTitle, $customerBody, "from:".$clientMail, $parameter);

				//クライアントに送信する。
				$rcd = mb_send_mail($clientMail, $adminTitle, $adminBody, "from:".$info['update']['tbl_user']['usr_e_mail']);

				//マスターに送信する。
				foreach($masterMailList as $masterMail){
					$rcd = mb_send_mail($masterMail, $adminTitle, $adminBody, "from:".$info['update']['tbl_user']['usr_e_mail']);
				}


				//メッセージを設定する。
				$message .= $info['update']['tbl_user']['usr_family_name']."&nbsp;".$info['update']['tbl_user']['usr_first_name'];
				$message .= "&nbsp;様";
				$message .= "\n";
				$message .= "\n";
				$message .= "この度は、『".$siteTitle."』にユーザー登録していただきありがとうございました。";
				$message .= "\n";
				$message .= "お客様のメールアドレス宛てにご登録内容の「確認メール」が自動送信されました。";
				$message .= "\n";
				$message .= "\n";
//				$message .= "※「確認メール」が届かない場合は、メールアドレスがご登録ミスの可能性がありますので、";
//				$message .= "\n";
//				$message .= "&nbsp;&nbsp;&nbsp;お手数ですが&nbsp;";
				$message .= "メールが届かない場合は、お手数ですが&nbsp;";
				$message .= "<a href=\"mailto:".$clientMail."\">".$clientMail."</a>";
				$message .= "&nbsp;までメールでお問い合わせください。";

				$message .= "\n";
				$message .= "\n";
				$message .= "\n";
				$message .= "<a href=\"../login/\" class=\"guide_link\">ログインはこちらから&nbsp;&nbsp;&gt;&gt;&gt;</a>";


				//申込番号にユーザーIDを設定する。
				$a8No = $info['update']['tbl_user']['usr_user_id'];
				$message .= "<img src=\"https://px.a8.net/cgi-bin/a8fly/sales?pid=s00000004712020&so=".$a8No."&si=1.1.1.a8\" width=\"1\" height=\"1\">";

				//Yahooリスティングコンバージョン測定タグ (2012/11/29追加)
				$message .= "<!-- Yahoo Code for &#26032;&#20250;&#31038;&#35373;&#31435;&#12367;&#12435; Conversion Page -->";
				$message .= "<script type=\"text/javascript\">";
				$message .= "/* <![CDATA[ */";
				$message .= "var yahoo_conversion_id = 1000024393;";
				$message .= "var yahoo_conversion_label = \"zKY4CKjqhAUQsKjEygM\";";
				$message .= "var yahoo_conversion_value = 0;";
				$message .= "/* ]]> */";
				$message .= "</script>";
				$message .= "<script type=\"text/javascript\" src=\"http://i.yimg.jp/images/listing/tool/cv/conversion.js\">";
				$message .= "</script>";
				$message .= "<noscript>";
				$message .= "<div style=\"display:inline;\">";
				$message .= "<img height=\"1\" width=\"1\" style=\"border-style:none;\" alt=\"\" src=\"http://b91.yahoo.co.jp/pagead/conversion/1000024393/?value=0&amp;label=zKY4CKjqhAUQsKjEygM&amp;guid=ON&amp;script=0&amp;disvt=true\"/>";
				$message .= "</div>";
				$message .= "</noscript>";
			} else {
				//メッセージを設定する。
				$message .= "更新しました。";


				//自分のユーザー情報を更新した場合、セッションのログイン情報を上書きする。
				if ($info['condition']['_id_'] == $loginInfo['usr_user_id']) {
					_Log("[/regist/index.php] セッション情報 \$_SESSION (ログイン情報更新前) = '".print_r($_SESSION,true)."'");
					$_SESSION[SID_LOGIN_USER_INFO] = $info['update']['tbl_user'];
					_Log("[/regist/index.php] セッション情報 \$_SESSION (ログイン情報更新後) = '".print_r($_SESSION,true)."'");
				}
			}






	//		//動作モード="他画面経由の表示"の場合、戻るリンクを表示する。
	//		if ($_SESSION[SID_INFO_MODE] == MST_MODE_FROM_OTHER) {
	//
	//			switch ($xmlName) {
	//				case XML_NAME_ITEM:
	//					//商品情報
	//					$message .= "<a href=\"../item/?back\" title=\"商品一覧に戻る\">[商品一覧に戻る]</a>\n";
	//					break;
	//				case XML_NAME_BOTTLE_IMAGE:
	//					//ボトル画像情報
	//					$message .= "";
	//					break;
	//				case XML_NAME_DESIGN_IMAGE:
	//					//彫刻パターン画像情報
	//					$message .= "";
	//					break;
	//				case XML_NAME_CHARACTER_J_IMAGE:
	//					//彫刻文字(和字)画像情報
	//					$message .= "";
	//					break;
	//				case XML_NAME_CHARACTER_E_IMAGE:
	//					//彫刻文字(英字)画像情報
	//					$message .= "";
	//					break;
	//				case XML_NAME_INQ:
	//					//問合せ情報
	//					switch ($_SESSION[SID_INFO_FROM_PAGE_ID]) {
	//						case PAGE_ID_INQ_PRICE:
	//							$message .= "<a href=\"../inquiry_price/?back\" title=\"請求額一覧に戻る\">[請求額一覧に戻る]</a>\n";
	//							break;
	//						default:
	//							$message .= "<a href=\"../inquiry/?back\" title=\"問合せ一覧に戻る\">[問合せ一覧に戻る]</a>\n";
	//							break;
	//					}
	//					break;
	//			}
	//
	//		}

			//完了画面を表示する。
			$mode = 3;
		}

	} else {
		//エラーが有り場合
		$message = "※入力に誤りがあります。\n".$message;
		$errorFlag = true;
	}

}



//文字をHTMLエンティティに変換する。
$info = _HtmlSpecialCharsForArray($info);
_Log("[/regist/index.php] POST(文字をHTMLエンティティに変換する。) = '".print_r($info,true)."'");

_Log("[/regist/index.php] mode = '".$mode."'");






//タイトルを設定する。
$title = $pageTitle;

//基本URLを設定する。
$basePath = "..";

//コンテンツを設定する。
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"../img/maincontent/pt_regist.jpg\" title=\"\" alt=\"ご利用申込み\">";
$maincontent .= "</h2>";
$maincontent .= "\n";

$maincontent .= _GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);

//スクリプトを設定する。
$script = null;

//サイドメニューを設定する。
$sidebar = null;

//if (isset($_SESSION[SID_USER_FROM_PAGE_ID]) && !_IsNull($_SESSION[SID_USER_FROM_PAGE_ID])) {
//ログインしているか？
if (isset($_SESSION[SID_LOGIN_USER_INFO])) {
	//基本URL
	$htmlSidebarUserMenu = str_replace('{base_url}', $basePath, $htmlSidebarUserMenu);
	//ログインユーザー名
	$htmlSidebarUserMenu = str_replace('{user_name}', _GetLoginUserNameHtml($_SESSION[SID_LOGIN_USER_INFO]), $htmlSidebarUserMenu);
	//現在の入力状況
	$htmlSidebarUserMenu = str_replace('{company_info}', null, $htmlSidebarUserMenu);

	$sidebar .= $htmlSidebarUserMenu;
} else {
	//基本URL
	$htmlSidebarLogin = str_replace('{base_url}', $basePath, $htmlSidebarLogin);

	$sidebar .= $htmlSidebarLogin;
}


//パンくずリストを設定する。
////if (isset($_SESSION[SID_USER_FROM_PAGE_ID]) && !_IsNull($_SESSION[SID_USER_FROM_PAGE_ID])) {
//if (isset($_SESSION[SID_LOGIN_USER_INFO])) {
//	_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
//	_SetBreadcrumbs(PAGE_DIR_USER, '', PAGE_TITLE_USER, 2);
//	_SetBreadcrumbs(PAGE_DIR_REGIST, '', PAGE_TITLE_REGIST_LOGIN, 3);
//} else {
//	_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
//	_SetBreadcrumbs(PAGE_DIR_REGIST, '', PAGE_TITLE_REGIST, 2);
//}
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_REGIST_OEM, '', PAGE_TITLE_REGIST_OEM, 2);

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


_Log("[/regist/index.php] end.");
echo $html;




















function _GetPostAddress($info) {

	$condition = array();
	$condition['id'] = $info['update']['tbl_user']['usr_pref_id'];
	$mstPrefInfo = _DB_GetInfo('mst_pref', $condition, true, 'del_flag');

	$name = $info['update']['tbl_user']['usr_family_name'].$info['update']['tbl_user']['usr_first_name'];
	$name = str_replace(' ', '', $name);
	$name = str_replace('　', '', $name);
	$zip = $info['update']['tbl_user']['usr_zip1']."-".$info['update']['tbl_user']['usr_zip2'];
	$address = $mstPrefInfo['name'].$info['update']['tbl_user']['usr_address1'].$info['update']['tbl_user']['usr_address2'];
	$tel = $info['update']['tbl_user']['usr_tel1']."-".$info['update']['tbl_user']['usr_tel2']."-".$info['update']['tbl_user']['usr_tel3'];


	//お届け希望日
	$deliverDate = null;
	//お届け希望時間帯
	$deliverTime = null;
//	switch ($info['update']['tbl_user']['time_assign']) {
//		case '午前中';
//			$deliverTime = '01';
//			break;
//		case '12：00〜14：00';
//			$deliverTime = '12';
//			break;
//		case '14：00〜16：00';
//			$deliverTime = '14';
//			break;
//		case '16：00〜18：00';
//			$deliverTime = '16';
//			break;
//		case '18：00〜21：00';
//			$deliverTime = '04';
//			break;
//	}

	//総合計を設定する。
	$totalPrice = null;
	$totalPriceTax = null;

	$cmpZip = "104-0061";
	$cmpAddress = "東京都中央区銀座1丁目15-7マック銀座ビル503";
	$cmpName = "行政書士小山尚文事務所";
	$cmpTel = "03-3564-1156";


	$res = null;
	$res .= $deliverDate;
	$res .= "\t";
	$res .= $deliverTime;
	$res .= "\t";
	$res .= $zip;
	$res .= "\t";
	$res .= $address;
	$res .= "\t";
	$res .= $name;
	$res .= "\t";
	$res .= $tel;
	$res .= "\t";
	$res .= "\t";
	$res .= $cmpZip;
	$res .= "\t";
	$res .= $cmpAddress;
	$res .= "\t";
	$res .= $cmpName;
	$res .= "\t";
	$res .= $cmpTel;
	$res .= "\t";
	$res .= $totalPrice;
	$res .= "\t";
	$res .= $totalPriceTax;

	return $res;
}


?>
