<?php
/*
 * [新★会社設立.JP ツール]
 * 株式会社設立情報登録ページ
 *
 * 更新履歴：2008/12/01	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../../common/include.ini");


//_LogDelete();
//_LogBackup();
_Log("[/user/company/info/index.php] start.");


_Log("[/user/company/info/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/user/company/info/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/user/company/info/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/user/company/info/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


//認証チェック----------------------------------------------------------------------start
$loginInfo = null;

//ログインしているか？
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
	_Log("[/user/index.php] ログインしていないなのでログイン画面を表示する。");
	_Log("[/user/index.php] end.");
	//ログイン画面を表示する。
	header("Location: ".URL_LOGIN);
	exit;
} else {
	//ログイン情報を取得する。
	$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];

	//本画面を使用可能な権限かチェックする。使用不可の場合、ログイン画面に遷移する。
	_CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
}
//認証チェック----------------------------------------------------------------------end



//HTMLテンプレートを読み込む。------------------------------------------------------- start
_Log("[/user/company/info/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ start");
$tempFile = '../../../common/temp_html/temp_base.txt';
_Log("[/user/company/info/index.php] {HTMLテンプレートを読み込み} (基本) HTMLテンプレートファイル = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"が存在する場合、表示する。
if ($html !== false && !_IsNull($html)) {
	_Log("[/user/company/info/index.php] {HTMLテンプレートを読み込み} (基本) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/user/company/info/index.php] {HTMLテンプレートを読み込み} (基本) 【失敗】");
	$html .= "HTMLテンプレートファイルを取得できません。\n";
}


//$tempSidebarLoginFile = '../../../common/temp_html/temp_sidebar_login.txt';
//_Log("[/user/company/info/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) HTMLテンプレートファイル = '".$tempSidebarLoginFile."'");
//
//$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
////"HTML"が存在する場合、表示する。
//if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
//	_Log("[/user/company/info/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【成功】");
//} else {
//	//取得できなかった場合
//	_Log("[/user/company/info/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【失敗】");
//}

$tempSidebarUserMenuFile = '../../../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/user/company/info/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) HTMLテンプレートファイル = '".$tempSidebarUserMenuFile."'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
	_Log("[/user/company/info/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/user/company/info/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【失敗】");
}

_Log("[/user/company/info/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ end");
//HTMLテンプレートを読み込む。------------------------------------------------------- end


//サイトタイトル
$siteTitle = SITE_TITLE;

//ページタイトル
$pageTitle = PAGE_TITLE_COMPANY_INFO;

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


$requestMethod = $_SERVER["REQUEST_METHOD"];

////次へ、戻るボタンが押された場合→GETの処理を行う。
//if ($_POST['next'] != "" || $_POST['back'] != "") {
//	$requestMethod = 'GET';
//
//	//ステップID
//	$step = (isset($_POST['condition']['_step_'])?$_POST['condition']['_step_']:null);
//
//	//次へボタンが押された場合
//	if ($_POST['next'] != "") {
//		if (_IsNull($step)) {
//			$step = 1;
//		} else {
//			$step++;
//		}
//	}
//	//戻るボタンが押された場合
//	elseif ($_POST['back'] != "") {
//		if (_IsNull($step)) {
//			$step = 1;
//		} else {
//			$step--;
//		}
//	}
//
//
//	//ターゲットID
//	$_GET['id'] = (isset($_POST['condition']['_id_'])?$_POST['condition']['_id_']:null);
//	//ステップID
//	$_GET['step'] = $step;
//}


_Log("[/user/company/info/index.php] \$_GET(詰め替え後) = '".print_r($_GET,true)."'");

//パラメーターを取得する。
$xmlName = XML_NAME_CMP;//XMLファイル名を設定する。
$id = null;
$step = null;
$stepId = null;
switch ($requestMethod) {
	case 'POST':
//		//XMLファイル名
//		$xmlName = (isset($_POST['condition']['_xml_name_'])?$_POST['condition']['_xml_name_']:null);
		//ターゲットID
		$id = (isset($_POST['condition']['_id_'])?$_POST['condition']['_id_']:null);
		//ステップID
		$step = (isset($_POST['condition']['_step_'])?$_POST['condition']['_step_']:null);


		_Log("[/user/company/info/index.php] {ログインユーザー権限処理} ユーザーID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/company/info/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."'");


		//権限によって、表示するユーザー情報を制限する。
		switch($loginInfo['usr_auth_id']){
			case AUTH_NON://権限無し

				_Log("[/user/company/info/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."' = '権限無し'");
				_Log("[/user/company/info/index.php] {ログインユーザー権限処理} →自分の株式会社設立情報のみ表示する。");
				_Log("[/user/company/info/index.php] {ログインユーザー権限処理} →会社IDを検索する。");

				$id = null;

				//自分の株式会社設立情報のみ表示する。
				//会社IDを検索する。
				$id = _GetRelationCompanyId($loginInfo['usr_user_id']);

				_Log("[/user/company/info/index.php] {ログインユーザー権限処理} →会社ID = '".$id."'");
				break;
		}


		//入力値を取得する。
		$info = $_POST;
		_Log("[/user/company/info/index.php] POST = '".print_r($info,true)."'");
		//バックスラッシュを取り除く。
		$info = _StripslashesForArray($info);
		_Log("[/user/company/info/index.php] POST(バックスラッシュを取り除く。) = '".print_r($info,true)."'");


		//XMLファイル名、ターゲットIDを上書きする。
		$info['condition']['_xml_name_'] = $xmlName;
		$info['condition']['_id_'] = $id;

		break;
	case 'GET':
//		//XMLファイル名
//		$xmlName = (isset($_GET['xml_name'])?$_GET['xml_name']:null);
		//ターゲットID
		$id = (isset($_GET['id'])?$_GET['id']:null);
		//ステップID
		$step = (isset($_GET['step'])?$_GET['step']:null);

		//遷移元ページ
		$pId = (isset($_GET['p_id'])?$_GET['p_id']:null);


		//初期値を設定する。
		$undeleteOnly4def = false;



		_Log("[/user/company/info/index.php] {ログインユーザー権限処理} ユーザーID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/company/info/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."'");


		//権限によって、表示するユーザー情報を制限する。
		switch($loginInfo['usr_auth_id']){
			case AUTH_NON://権限無し

				_Log("[/user/company/info/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."' = '権限無し'");
				_Log("[/user/company/info/index.php] {ログインユーザー権限処理} →自分の株式会社設立情報のみ表示する。");
				_Log("[/user/company/info/index.php] {ログインユーザー権限処理} →会社IDを検索する。");

				$id = null;
				$undeleteOnly4def = true;

				//自分の株式会社設立情報のみ表示する。
				//会社IDを検索する。
				$id = _GetRelationCompanyId($loginInfo['usr_user_id']);

				_Log("[/user/company/info/index.php] {ログインユーザー権限処理} →会社ID = '".$id."'");

//				//遷移元ページはどこか？
//				switch ($pId) {
//					case PAGE_ID_USER://ユーザーページ
//						break;
//				}
				break;
		}



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

//		//遷移元ページをセッションに保存する。
//		$_SESSION[SID_USER_FROM_PAGE_ID] = $pId;

		break;
}

_Log("[/user/company/info/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '".$_SERVER["REQUEST_METHOD"]."'");
_Log("[/user/company/info/index.php] XMLファイル名 = '".$xmlName."'");
_Log("[/user/company/info/index.php] ターゲットID = '".$id."'");


//会社タイプID="株式会社"を設定する。
$info['update']['tbl_company']['cmp_company_type_id'] = MST_COMPANY_TYPE_ID_CMP;
//ユーザー情報(ログイン情報)を設定する。→DB更新に使用する。
$info['update']['tbl_user'] = $loginInfo;

//初回だけ、未登録のときだけ設定する。
//取締役任期
if (!isset($info['update']['tbl_company']['cmp_term_year']) || _IsNull($info['update']['tbl_company']['cmp_term_year'])) {
	$info['update']['tbl_company']['cmp_term_year'] = 10;
}
//監査役任期
if (!isset($info['update']['tbl_company']['cmp_inspector_term_year']) || _IsNull($info['update']['tbl_company']['cmp_inspector_term_year'])) {
	$info['update']['tbl_company']['cmp_inspector_term_year'] = 4;
}
//※注意：上記の項目が表示される画面以外でも更新される。今後、他の項目を追加するときは要注意。(「発行可能株式の総数」を追加したとき、更新されてしまっていた。)

switch ($step) {
	case 1:
		//株式会社設立情報[商号(会社名)]
		$xmlName = XML_NAME_CMP_NAME;

		$stepId = "cmpn_name";
		break;
	case 2:
		//株式会社設立情報[資本金・事業年度]
		$xmlName = XML_NAME_CMP_CAPITAL;

		$stepId = "cmpn_capital";
		break;
	case 3:
		//株式会社設立情報[本店所在地]
		$xmlName = XML_NAME_CMP_ADDRESS;

		$stepId = "cmpn_address";
		break;
	case 4:
		//株式会社設立情報[事業の目的]
		$xmlName = XML_NAME_CMP_PURPOSE;

		$stepId = "cmpn_purpose";
		break;
	case 5:
		//株式会社設立情報[役員構成・任期]
		$xmlName = XML_NAME_CMP_BOARD_BASE;

		$stepId = "cmpn_board_base";
		break;
	case 6:
		//株式会社設立情報[取締役]
		$xmlName = XML_NAME_CMP_BOARD_NAME;

		$stepId = "cmpn_board_name";
		break;
	case 7:
		//株式会社設立情報[発起人]
		$xmlName = XML_NAME_CMP_PROMOTER;

		$stepId = "cmpn_promoter";
		break;
	case 8:
		//株式会社設立情報[出資金]
		//→出資金は、XML形式のフォームではなく。直接書き出す。
		$xmlName = XML_NAME_CMP_PROMOTER_INVESTMENT;
		//$xmlName = null;

		$stepId = "cmpn_promoter_investment";
		break;
	case 9:
		//株式会社設立情報[入力内容確認]
		$xmlName = XML_NAME_CMP_ALL;

		$stepId = "cmpn_confirm";
		break;
	default:
		//株式会社設立情報[商号(会社名)]
		$xmlName = XML_NAME_CMP_NAME;

		$stepId = "cmpn_name";

		$step = 1;
		break;
}
$info['condition']['_step_'] = $step;

_Log("[/user/company/info/index.php] ステップID = '".$step."'");
_Log("[/user/company/info/index.php] XMLファイル名(ステップID) = '".$xmlName."'");

//戻るボタンが押された場合→すぐ遷移するので、XMLは読み込まない。
if ($_POST['back'] != "") $xmlName = null;

//初期値を設定する。
switch ($xmlName) {
	case XML_NAME_CMP_PROMOTER:
		//株式会社設立情報[発起人]
		//会社_発起人テーブル情報が未設定の場合、会社_役員テーブル情報を初期値として設定する。
		if (!isset($info['update']['tbl_company_promoter'])) {
			if (_IsNull($info['update']['tbl_company_promoter'])) {
				//会社_役員テーブル情報が設定済みの場合
				if (isset($info['update']['tbl_company_board'])) {
					if (!_IsNull($info['update']['tbl_company_board']) && is_array($info['update']['tbl_company_board'])) {
						$bufList = array();
						foreach ($info['update']['tbl_company_board'] as $tcbKey => $tblCompanyBoardInfo) {
							$bufInfo = array();
							$bufInfo['cmp_prm_family_name'] = $tblCompanyBoardInfo['cmp_bod_family_name'];					//発起人名前(姓) ← 役員名前(姓)
							$bufInfo['cmp_prm_first_name'] = $tblCompanyBoardInfo['cmp_bod_first_name'];					//発起人名前(名) ← 役員名前(名)
							$bufInfo['cmp_prm_family_name_kana'] = $tblCompanyBoardInfo['cmp_bod_family_name_kana'];		//発起人名前フリガナ(姓) ← 役員名前フリガナ(姓)
							$bufInfo['cmp_prm_first_name_kana'] = $tblCompanyBoardInfo['cmp_bod_first_name_kana'];			//発起人名前フリガナ(名) ← 役員名前フリガナ(名)
							$bufInfo['cmp_prm_zip1'] = $tblCompanyBoardInfo['cmp_bod_zip1'];								//発起人住所(郵便番号1) ← 役員住所(郵便番号1)
							$bufInfo['cmp_prm_zip2'] = $tblCompanyBoardInfo['cmp_bod_zip2'];								//発起人住所(郵便番号2) ← 役員住所(郵便番号2)
							$bufInfo['cmp_prm_pref_id'] = $tblCompanyBoardInfo['cmp_bod_pref_id'];							//発起人住所(都道府県) ← 役員住所(都道府県)
							$bufInfo['cmp_prm_address1'] = $tblCompanyBoardInfo['cmp_bod_address1'];						//発起人住所(市区町村) ← 役員住所(市区町村)
							$bufInfo['cmp_prm_address2'] = $tblCompanyBoardInfo['cmp_bod_address2'];						//発起人住所(上記以降) ← 役員住所(上記以降)
							$bufList[] = $bufInfo;
						}
						if (count($bufList) > 1) {
							$info['update']['tbl_company_promoter'] = $bufList;
							$message .= "※まだ発起人は登録されていません。\n取締役の情報を仮で表示してあります。\n以下の内容を確認・修正して保存してください。";
						}
					}
				}
			}
		}
		break;
}

//フォーム用にマスタデータを設定する。
//発行可能株式の総数
$mstStockTotalNumList = _GetNumberArray(5000, 30000, 5000);
//登録中の「発行可能株式の総数」の値が上記配列にあるか？無い場合は、追加する。(※過去データ用)
if (isset($info['update']['tbl_company']['cmp_stock_total_num']) && !_IsNull($info['update']['tbl_company']['cmp_stock_total_num'])) {
	if (!isset($mstStockTotalNumList[$info['update']['tbl_company']['cmp_stock_total_num']])) {
		$addList = array(
		'id' => $info['update']['tbl_company']['cmp_stock_total_num']
		,'name' => $info['update']['tbl_company']['cmp_stock_total_num'].' (【仕様変更】5千〜3万株固定 【注意】今の株数から変更すると元に戻せません。)'
		);
		$mstStockTotalNumList[$info['update']['tbl_company']['cmp_stock_total_num']] = $addList;
	}
}

$otherList = array(
'mst_stock_total_num' => $mstStockTotalNumList
);

$xmlList = null;
if (!_IsNull($xmlName)) {
	//XMLを読み込む。
	$xmlFile = "../../../common/form_xml/".$xmlName.".xml";
	_Log("[/user/company/info/index.php] XMLファイル = '".$xmlFile."'");
	$xmlList = _GetXml($xmlFile, $otherList);

	_Log("[/user/company/info/index.php] XMLファイル配列 = '".print_r($xmlList,true)."'");

	switch ($xmlName) {
		case XML_NAME_CMP_ALL:
			//株式会社設立情報[入力内容確認]

			//全てのXMLを読み込む。

			//株式会社設立情報[商号(会社名)]
			$bufXmlFile = "../../../common/form_xml/".XML_NAME_CMP_NAME.".xml";
			_Log("[/user/company/info/index.php] XMLファイル = '".$bufXmlFile."'");
			$bufXmlList = _GetXml($bufXmlFile);
			$xmlList['tbl_company_name'] = $bufXmlList['tbl_company'];

			//株式会社設立情報[資本金・事業年度]
			$bufXmlFile = "../../../common/form_xml/".XML_NAME_CMP_CAPITAL.".xml";
			_Log("[/user/company/info/index.php] XMLファイル = '".$bufXmlFile."'");
			$bufXmlList = _GetXml($bufXmlFile, $otherList);
			$xmlList['tbl_company_capital'] = $bufXmlList['tbl_company'];

			//株式会社設立情報[本店所在地]
			$bufXmlFile = "../../../common/form_xml/".XML_NAME_CMP_ADDRESS.".xml";
			_Log("[/user/company/info/index.php] XMLファイル = '".$bufXmlFile."'");
			$bufXmlList = _GetXml($bufXmlFile);
			$xmlList['tbl_company_address'] = $bufXmlList['tbl_company'];

			//株式会社設立情報[事業の目的]
			$bufXmlFile = "../../../common/form_xml/".XML_NAME_CMP_PURPOSE.".xml";
			_Log("[/user/company/info/index.php] XMLファイル = '".$bufXmlFile."'");
			$bufXmlList = _GetXml($bufXmlFile);
			$xmlList['tbl_company_purpose'] = $bufXmlList['tbl_company_purpose'];

			//株式会社設立情報[役員構成・任期]
			$bufXmlFile = "../../../common/form_xml/".XML_NAME_CMP_BOARD_BASE.".xml";
			_Log("[/user/company/info/index.php] XMLファイル = '".$bufXmlFile."'");
			$bufXmlList = _GetXml($bufXmlFile);
			$xmlList['tbl_company_board_base'] = $bufXmlList['tbl_company'];

			//株式会社設立情報[取締役]
			$bufXmlFile = "../../../common/form_xml/".XML_NAME_CMP_BOARD_NAME.".xml";
			_Log("[/user/company/info/index.php] XMLファイル = '".$bufXmlFile."'");
			$bufXmlList = _GetXml($bufXmlFile);
			$xmlList['tbl_company_board'] = $bufXmlList['tbl_company_board'];

			//株式会社設立情報[発起人]
			$bufXmlFile = "../../../common/form_xml/".XML_NAME_CMP_PROMOTER.".xml";
			_Log("[/user/company/info/index.php] XMLファイル = '".$bufXmlFile."'");
			$bufXmlList = _GetXml($bufXmlFile);
			$xmlList['tbl_company_promoter'] = $bufXmlList['tbl_company_promoter'];

			//株式会社設立情報[出資金]
			$bufXmlFile = "../../../common/form_xml/".XML_NAME_CMP_PROMOTER_INVESTMENT.".xml";
			_Log("[/user/company/info/index.php] XMLファイル = '".$bufXmlFile."'");
			$bufXmlList = _GetXml($bufXmlFile);
			$xmlList['tbl_company_promoter_investment'] = $bufXmlList['tbl_company_promoter_investment'];


			$info['update']['tbl_company_name'] = $info['update']['tbl_company'];
			$info['update']['tbl_company_capital'] = $info['update']['tbl_company'];
			$info['update']['tbl_company_address'] = $info['update']['tbl_company'];
			$info['update']['tbl_company_board_base'] = $info['update']['tbl_company'];


			_Log("[/user/company/info/index.php] XMLファイル配列(全XMLマージ後) = '".print_r($xmlList,true)."'");
			_Log("[/user/company/info/index.php] 株式会社設立情報(全XMLマージ後) = '".print_r($info,true)."'");

			$mode = 2;

			break;
	}
}

//保存ボタン、次へボタンが押された場合
if ($_POST['go'] != "" || $_POST['next'] != "") {
	//入力値チェック
	$message .= _CheackInputAll($xmlList, $info);

	switch ($xmlName) {
		case XML_NAME_CMP_PURPOSE:
			//株式会社設立情報[事業の目的]
			$message .= _CheackInput4CompanyPurpose($xmlList, $info);
			break;
		case XML_NAME_CMP_BOARD_NAME;
			//株式会社設立情報[取締役]
			$message .= _CheackInput4CompanyBoard($xmlList, $info);
			break;
		case XML_NAME_CMP_PROMOTER:
			//株式会社設立情報[発起人]
			$message .= _CheackInput4CompanyPromoter($xmlList, $info);
			break;
		case XML_NAME_CMP_PROMOTER_INVESTMENT:
			//株式会社設立情報[出資金]
			$message .= _CheackInput4CompanyPromoterInvestment($xmlList, $info);

			//株数のチェックをする。
			$stockNumErrorFlag = false;
			$bufTabindex = null;
			$buf = _CreateTableInput4CompanyPromoterInvestment($mode, $xmlList, $info, $bufTabindex, $stockNumErrorFlag);
			if ($stockNumErrorFlag) {
				$message .= "発行株数と引受株数が合っていません。\n";
			}
			break;
		default:
			break;
	}

	if (_IsNull($message)) {
		//エラーが無い場合、登録する。

		//更新・登録をする。(※$infoは最新情報に更新される。)
		$res = _UpdateInfo($info);
		if ($res === false) {
			//エラーが有り場合
			$message = "登録に失敗しました。";
			$errorFlag = true;
		} else {

			//メッセージを設定する。
			$message .= "保存しました。";

			//新規登録の場合、idが採番されるので、設定する。
			$id = $info['condition']['_id_'];


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

//			//完了画面を表示する。
//			$mode = 3;
		}

	} else {
		//エラーが有り場合
		$message = "※入力に誤りがあります。\n".$message;
		$errorFlag = true;
	}

}


$addHref = null;
switch($loginInfo['usr_auth_id']){
	case AUTH_NON://権限無し
		break;
	default:
		if (!_IsNull($id)) {
			$addHref = "&amp;id=".$id;
		}
		break;
}

//次へボタンが押された場合
if ($_POST['next'] != "") {
	if (!$errorFlag) {
		//次のページを表示する。
		$step++;
		header("Location: ./?step=".$step.$addHref);
		exit;
	}
}
//戻るボタンが押された場合
elseif ($_POST['back'] != "") {
	//前のページを表示する。
	$step--;
	header("Location: ./?step=".$step.$addHref);
	exit;
}


//文字をHTMLエンティティに変換する。
$info = _HtmlSpecialCharsForArray($info);
_Log("[/user/company/info/index.php] POST(文字をHTMLエンティティに変換する。) = '".print_r($info,true)."'");

_Log("[/user/company/info/index.php] mode = '".$mode."'");






//タイトルを設定する。
$title = $pageTitle;

//基本URLを設定する。
$basePath = "../../..";

//コンテンツを設定する。
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"../../../img/maincontent/pt_user_company_info.jpg\" title=\"\" alt=\"株式会社設立情報登録\">";
$maincontent .= "</h2>";
$maincontent .= "\n";

//サブメニューを設定する。
$maincontent .= "<ul id=\"cmpn\">";
$maincontent .= "\n";
$maincontent .= "<li id=\"cmpn_name\">";
$maincontent .= "<a href=\"?step=1".$addHref."\">商号<br />(会社名)</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "<li id=\"cmpn_capital\">";
$maincontent .= "<a href=\"?step=2".$addHref."\">資本金<br />事業年度</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "<li id=\"cmpn_address\">";
$maincontent .= "<a href=\"?step=3".$addHref."\">本店<br />所在地</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "<li id=\"cmpn_purpose\">";
$maincontent .= "<a href=\"?step=4".$addHref."\">事業の<br />目的</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "<li id=\"cmpn_board_base\">";
$maincontent .= "<a href=\"?step=5".$addHref."\">役員構成<br />任期</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "<li id=\"cmpn_board_name\">";
$maincontent .= "<a href=\"?step=6".$addHref."\">取締役</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "<li id=\"cmpn_promoter\">";
$maincontent .= "<a href=\"?step=7".$addHref."\">発起人</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "<li id=\"cmpn_promoter_investment\">";
$maincontent .= "<a href=\"?step=8".$addHref."\">出資金</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "<li id=\"cmpn_confirm\">";
$maincontent .= "<a href=\"?step=9".$addHref."\">入力内容<br />確認</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "</ul>";
$maincontent .= "\n";
$maincontent .= "<div id=\"cmpn_exp\">";
$maincontent .= "\n";
$maincontent .= "※メニューからページを移動する場合、入力内容は保存されません。";
$maincontent .= "\n";
$maincontent .= "</div>";
$maincontent .= "\n";

switch ($xmlName) {
	case XML_NAME_CMP_ALL:
		//株式会社設立情報[入力内容確認]
		$maincontent .= "<!--{_message_}-->";
		$maincontent .= "\n";
		break;
}

$maincontent .= _GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);


//スクリプトを設定する。
$script = null;

$addStyle = null;

switch ($xmlName) {
	case XML_NAME_CMP_CAPITAL:
		//株式会社設立情報[資本金・事業年度]

		//スクリプトを設定する。
		$script .= "<script type=\"text/javascript\">";
		$script .= "\n";
		$script .= "<!--";
		$script .= "\n";
		$script .= "window.addEvent('domready', function(){";
		$script .= "\n";

		$script .= "$$('#cmp_business_start_month','#cmp_found_month').addEvent('change', function(e) {";
		$script .= "\n";
		$script .= "calculateMonth();";
		$script .= "\n";
		$script .= "});";
		$script .= "\n";
		$script .= "calculateMonth();";
		$script .= "\n";

		if (!_IsNull(FOUND_DAY_DEADLINE)) {
			$script .= "$$('#cmp_found_year','#cmp_found_month','#cmp_found_day').addEvent('change', function(e) {";
			$script .= "\n";
			$script .= "checkFoundDate();";
			$script .= "\n";
			$script .= "});";
			$script .= "\n";
			$script .= "checkFoundDate();";
			$script .= "\n";
		}

		$script .= "});";
		$script .= "\n";
		$script .= "\n";

		$script .= "function calculateMonth() {";
		$script .= "\n";
		$script .= "var startMonth = $('cmp_business_start_month').value;";
		$script .= "\n";
		$script .= "var foundMonth = $('cmp_found_month').value;";
		$script .= "\n";
		//$script .= "alert('startMonth='+startMonth+'/foundMonth='+foundMonth);";
		//$script .= "\n";
		$script .= "var res = '約XXヶ月';";
		$script .= "\n";
		$script .= "var bgColor = '#ff0';";
		$script .= "\n";
		$script .= "var resMessage = '';";
		$script .= "\n";
		$script .= "if (startMonth != '' && foundMonth != '') {";
		$script .= "\n";
		$script .= "var diff = 12 - (foundMonth - startMonth);";
		$script .= "\n";
		$script .= "if (diff > 12) diff -= 12;";
		$script .= "\n";
		$script .= "res = '約'+diff+'ヶ月';";
		$script .= "\n";
		$script .= "if (diff == 1) {";
		$script .= "\n";
		$script .= "bgColor = '#f00';";
		$script .= "\n";
		$script .= "resMessage = '<br /><br />最初の決算まで1ヶ月を切っています。<br />設立予定日を翌月にするか、事業年度の開始日を1ヶ月前(早く)にしてください。<br />ご理解した上で決算日を設定している場合はこのままお進みください。';";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";
//		$script .= "$('res_month_1').set('text', res);";//ie6でtextが使えない。
		$script .= "$('res_month_1').set('html', res);";
		$script .= "\n";
		$script .= "$('res_month_1').setStyle('background-color', bgColor);";
		$script .= "\n";
//		$script .= "$('res_month_2').set('text', res);";
		$script .= "$('res_month_2').set('html', res);";
		$script .= "\n";
		$script .= "$('res_month_2').setStyle('background-color', bgColor);";
		$script .= "\n";
		$script .= "$('res_month_advice_1').set('html', resMessage);";
		$script .= "\n";
		$script .= "$('res_month_advice_2').set('html', resMessage);";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";

		if (!_IsNull(FOUND_DAY_DEADLINE)) {
			//本日を取得する。
			$deadlineTime = mktime(0, 0, 0, date('n'), date('j') + FOUND_DAY_DEADLINE + 1, date('Y'));
			$deadlineYmd = date('Ymd', $deadlineTime);
			$deadlineYmdMessage = date('Y年m月d日', $deadlineTime);

			$script .= "function checkFoundDate() {";
			$script .= "\n";
			$script .= "var foundDateDeadline = ".$deadlineYmd.";";
			$script .= "\n";
			$script .= "var foundYear = $('cmp_found_year').value;";
			$script .= "\n";
			$script .= "var foundMonth = $('cmp_found_month').value;";
			$script .= "\n";
			$script .= "var foundDay = $('cmp_found_day').value;";
			$script .= "\n";
			$script .= "var foundDate = '';";
			$script .= "\n";
			$script .= "var resMessage = '';";
			$script .= "\n";
			$script .= "var resMessageDeadline = '(".$deadlineYmdMessage."以降を設定してください。)';";
			$script .= "\n";
			$script .= "if (foundYear != '' && foundMonth != '' && foundDay != '') {";
			$script .= "\n";
			$script .= "foundMonth = (foundMonth.length < 2 ? '0'+foundMonth : foundMonth);";
			$script .= "\n";
			$script .= "foundDay = (foundDay.length < 2 ? '0'+foundDay : foundDay);";
			$script .= "\n";
			$script .= "foundDate = foundYear + foundMonth + foundDay;";
			$script .= "\n";
			$script .= "foundDate = Number(foundDate);";
			$script .= "\n";
			$script .= "if (foundDate < foundDateDeadline) {";
			$script .= "\n";
			$script .= "resMessage = '設立年月日は、本日より".FOUND_DAY_DEADLINE."日後以降の日付を入力してください。<br />(既に設立済みの場合は、このままお進みください。)<br /><br />';";
			$script .= "\n";
			$script .= "}";
			$script .= "\n";
			$script .= "}";
			$script .= "\n";
			$script .= "$('res_found_date').set('html', resMessageDeadline);";
			$script .= "\n";
			$script .= "$('res_found_date_advice').set('html', resMessage);";
			$script .= "\n";
			$script .= "}";
			$script .= "\n";
		}

		$script .= "//-->";
		$script .= "\n";
		$script .= "</script>";
		$script .= "\n";


		break;

	case XML_NAME_CMP_BOARD_BASE:
		//株式会社設立情報[役員構成・任期]

		//スクリプトを設定する。
		$script .= "<script type=\"text/javascript\">";
		$script .= "\n";
		$script .= "<!--";
		$script .= "\n";
		$script .= "window.addEvent('domready', function(){";
		$script .= "\n";

//(修正2011/10/25)※1役員構成=「取締役会を設置する　取締役3人以上と監査役1人が必要です。」で「3人以上」となったので、人数を選択できるようにする。
//		$script .= "$$('input.board_formation').addEvent('click', function(e) {";
//		$script .= "\n";
//		//$script .= "alert('name='+this.name+'/value='+this.value+'/checked='+this.checked);";
//		//$script .= "\n";
//		$script .= "showNode('director_num', (this.value == '".MST_BOARD_FORMATION_ID_1_10."'));";
//		$script .= "\n";
//		$script .= "});";
//		$script .= "\n";
//		$script .= "\n";
//
//		$script .= "var value = '';";
//		$script .= "\n";
//
//		$script .= "$$('input.board_formation').each(function(el){";
//		$script .= "\n";
//
//		$script .= "if (el.checked) {";
//		$script .= "\n";
//		$script .= "value = el.value;";
//		$script .= "\n";
//		$script .= "}";
//		$script .= "\n";
//
//		//$script .= "alert('name='+el.name+'/value='+el.value+'/checked='+el.checked);";
//		//$script .= "\n";
//		$script .= "});";
//		$script .= "\n";
//
//		$script .= "showNode('director_num', (value == '".MST_BOARD_FORMATION_ID_1_10."'));";
//		$script .= "\n";
		$script .= "showNode('inspector_num', false);";//監査役人数は、非表示。プログラムで固定で設定する。→確認画面では表示するため、存在している。
		$script .= "\n";

		$script .= "});";
		$script .= "\n";

		$script .= "//-->";
		$script .= "\n";
		$script .= "</script>";
		$script .= "\n";


		break;

	case XML_NAME_CMP_BOARD_NAME:
		//株式会社設立情報[取締役]

		$requiredMessage = null;
		if (_IsNull($info['update']['tbl_company_board']) || !is_array($info['update']['tbl_company_board'])) {
			$requiredMessage .= "「取締役」を登録する場合は、「役員構成」を先に登録してください。「役員構成・任期」ページで登録してください。\n";
		}
		$buf = null;
		if (!_IsNull($requiredMessage)) {
			$buf .= "<div class=\"requiredMessage\">";
			$buf .= nl2br($requiredMessage);
			$buf .= "</div>";
			$buf .= "\n";
		}
		$maincontent = str_replace('{form_info_cmp_board_name}', $buf, $maincontent);
		break;
	case XML_NAME_CMP_PROMOTER_INVESTMENT:
		//株式会社設立情報[出資金]
		$buf = _CreateTableInput4CompanyPromoterInvestment($mode, $xmlList, $info, $tabindex);
		$maincontent = str_replace('{form_info_cmp_promoter_investment}', $buf, $maincontent);
		break;
	case XML_NAME_CMP_ALL:
		//株式会社設立情報[入力内容確認]

		$allErrorFlag = false;

		//株式会社設立情報[取締役]
		$requiredMessage = null;
		if (_IsNull($info['update']['tbl_company_board']) || !is_array($info['update']['tbl_company_board'])) {
			$requiredMessage .= "「取締役」を登録する場合は、「役員構成」を先に登録してください。「役員構成・任期」ページで登録してください。\n";
		}
		$buf = null;
		if (!_IsNull($requiredMessage)) {
			$allErrorFlag = true;
			$buf .= "<div class=\"requiredMessage\">";
			$buf .= nl2br($requiredMessage);
			$buf .= "</div>";
			$buf .= "\n";
		}
		$maincontent = str_replace('{form_info_cmp_board_name}', $buf, $maincontent);

		//株式会社設立情報[出資金]
		$buf = _CreateTableInput4CompanyPromoterInvestment($mode, $xmlList, $info, $tabindex);
		$maincontent = str_replace('{form_info_cmp_promoter_investment}', $buf, $maincontent);
		if (preg_match('/class=\\"requiredMessage\\"/', $buf)) {
			$allErrorFlag = true;
		}

		foreach ($xmlList as $xKey => $xmlInfo) {
			$repKey = null;
			switch ($xKey) {
				case 'tbl_company_name';
					$repKey = '<!--{_form_info_cmp_name_}-->';
					break;
				case 'tbl_company_capital';
					$repKey = '<!--{_form_info_cmp_capital_}-->';
					break;
				case 'tbl_company_address';
					$repKey = '<!--{_form_info_cmp_address_}-->';
					break;
				case 'tbl_company_purpose';
					$repKey = '<!--{_form_info_cmp_purpose_}-->';
					break;
				case 'tbl_company_board_base';
					$repKey = '<!--{_form_info_cmp_board_base_}-->';
					break;
				case 'tbl_company_board';
					$repKey = '<!--{_form_info_cmp_board_name_}-->';
					break;
				case 'tbl_company_promoter';
					$repKey = '<!--{_form_info_cmp_promoter_}-->';
					break;
				case 'tbl_company_promoter_investment';
					$repKey = '<!--{_form_info_cmp_promoter_investment_}-->';
					break;
				default:
					continue 2;
			}

			$bufXmlList = array();
			$bufXmlList[$xKey] = $xmlInfo;
			//入力値チェック
			$bufMessage = null;
			$bufMessage .= _CheackInputAll($bufXmlList, $info);
//			switch ($xKey) {
//				case 'tbl_company_purpose':
//					//株式会社設立情報[事業の目的]
//					$bufMessage .= _CheackInput4CompanyPurpose($bufXmlList, $info);
//					break;
//				case 'tbl_company_promoter':
//					//株式会社設立情報[発起人]
//					$bufMessage .= _CheackInput4CompanyPromoter($bufXmlList, $info);
//					break;
//				case 'tbl_company_promoter_investment':
//					//株式会社設立情報[出資金]
//					$bufMessage .= _CheackInput4CompanyPromoterInvestment($bufXmlList, $info);
//					break;
//				default:
//					break;
//			}
			if (!_IsNull($bufMessage)) {
				$allErrorFlag = true;
				$buf = null;
				$buf .= "<div class=\"requiredMessage\">";
				$buf .= "必須項目に未入力があります。";//.$bufMessage;
				$buf .= "</div>";
				$buf .= "\n";
				$maincontent = str_replace($repKey, $buf, $maincontent);
			}
		}

		$buf = null;
		if ($allErrorFlag) {
			$buf .= "<div class=\"message errorMessage\">";
			$buf .= "\n";
			$buf .= "※入力がまだ済んでいない項目があります。<br />入力内容をご確認ください。";
			$buf .= "\n";
			$buf .= "</div>";
		} else {
			$buf .= "<div class=\"message\">";
			$buf .= "\n";
			$buf .= "入力内容をご確認ください。";
//			$buf .= "<br />";
//			$buf .= "入力内容がよろしければ、次の作業にお進みください。";
//			$buf .= "<br />";
//			$buf .= "次の作業";
//			$buf .= "&nbsp;&nbsp;&gt;&gt;&gt;&nbsp;&nbsp;";
//			$buf .= "<a href=\"../../../seal/\" class=\"guide_link\">「印鑑作成(実印・銀行印等)」</a>";
//			$buf .= "&nbsp;&nbsp;&gt;&gt;&gt;&nbsp;&nbsp;";
//			$buf .= "<a href=\"../article/\" class=\"guide_link\">「定款認証 設定」</a>";
//			$buf .= "&nbsp;&nbsp;&gt;&gt;&gt;&nbsp;&nbsp;";
//			$buf .= "…";
			$buf .= "\n";
			$buf .= "</div>";
		}
		$maincontent = str_replace('<!--{_message_}-->', $buf, $maincontent);


		//確認用画面では非表示にする項目を非表示にする。「削除する」項目など。
		$addStyle .= ".show_confirm {display: none;}";

		break;
	default:
		break;
}

$script .= "<style type=\"text/css\">";
$script .= "\n";
$script .= "<!--";
$script .= "\n";
$script .= "ul#cmpn li#".$stepId." a:link";
$script .= ",ul#cmpn li#".$stepId." a:visited";
$script .= "\n";
$script .= "{height: 32px;color: #3176af;border-bottom: 3px solid #76b0df;}";
$script .= "\n";
$script .= $addStyle;
$script .= "\n";
$script .= "-->";
$script .= "\n";
$script .= "</style>";
$script .= "\n";


//説明用文章を設定する。
$tempExpFile = null;
switch ($xmlName) {
	case XML_NAME_CMP_NAME:
		//株式会社設立情報[商号(会社名)]
		$tempExpFile = '../../../common/temp_html/temp_maincontent_company_exp_01.txt';
		break;
	case XML_NAME_CMP_CAPITAL:
		//株式会社設立情報[資本金・事業年度]
		$tempExpFile = '../../../common/temp_html/temp_maincontent_company_exp_02.txt';
		break;
	case XML_NAME_CMP_PURPOSE:
		//株式会社設立情報[事業の目的]
		$tempExpFile = '../../../common/temp_html/temp_maincontent_company_exp_03.txt';
		break;
	case XML_NAME_CMP_BOARD_NAME:
		//株式会社設立情報[取締役]
		$tempExpFile = '../../../common/temp_html/temp_maincontent_company_exp_04.txt';
		break;
	case XML_NAME_CMP_PROMOTER:
		//株式会社設立情報[発起人]
		$tempExpFile = '../../../common/temp_html/temp_maincontent_company_exp_05.txt';
		break;
	case XML_NAME_CMP_PROMOTER_INVESTMENT:
		//株式会社設立情報[出資金]
		$tempExpFile = '../../../common/temp_html/temp_maincontent_company_exp_06.txt';
		break;
}
_Log("[/user/company/info/index.php] {HTMLテンプレートを読み込み} (説明用文章) HTMLテンプレートファイル = '".$tempExpFile."'");
$htmlExp = null;
if (!_IsNull($tempExpFile)) {
	$htmlExp = @file_get_contents($tempExpFile);
	//"HTML"が存在する場合、表示する。
	if ($htmlExp !== false && !_IsNull($htmlExp)) {
		_Log("[/user/company/info/index.php] {HTMLテンプレートを読み込み} (説明用文章) 【成功】");
	} else {
		//取得できなかった場合
		_Log("[/user/company/info/index.php] {HTMLテンプレートを読み込み} (説明用文章) 【失敗】");
		$htmlExp = null;
	}
}
if (!_IsNull($htmlExp)) {
	$buf = null;
	$buf .= $maincontent;
	$buf .= "\n";
	$buf .= "\n";
	$buf .= "\n";
	$buf .= $htmlExp;

	$maincontent = $buf;
}




//サイドメニューを設定する。
$sidebar = null;

////基本URL
//$htmlSidebarLogin = str_replace('{base_url}', $basePath, $htmlSidebarLogin);
//
//$sidebar .= $htmlSidebarLogin;

//基本URL
$htmlSidebarUserMenu = str_replace('{base_url}', $basePath, $htmlSidebarUserMenu);
//ログインユーザー名
$htmlSidebarUserMenu = str_replace('{user_name}', _GetLoginUserNameHtml($loginInfo), $htmlSidebarUserMenu);
//現在の入力状況
$htmlSidebarUserMenu = str_replace('{company_info}', _GetCompanyInfoHtml($loginInfo), $htmlSidebarUserMenu);

$sidebar .= $htmlSidebarUserMenu;


//パンくずリストを設定する。
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_USER, '', PAGE_TITLE_USER, 2);
_SetBreadcrumbs(PAGE_DIR_COMPANY, '', PAGE_TITLE_COMPANY, 3);
_SetBreadcrumbs(PAGE_DIR_COMPANY_INFO, '', PAGE_TITLE_COMPANY_INFO, 4);
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


_Log("[/user/company/info/index.php] end.");
echo $html;



































/**
 * 会社_発起人_出資額テーブル情報用
 * 入力値のチェックをする。
 *
 * @param	array	$xmlList		XMLを読み込んだ配列
 * @param	array	$info			入力した値が格納されている配列
 * @return	エラーメッセージ
 * @access  public
 * @since
 */
function _CheackInput4CompanyPromoterInvestment($xmlList, &$info) {

	_Log("[_CheackInput4CompanyPromoterInvestment] start.");

	_Log("[_CheackInput4CompanyPromoterInvestment] (param) XMLを読み込んだ配列 = '".print_r($xmlList,true)."'");
	_Log("[_CheackInput4CompanyPromoterInvestment] (param) 入力した値が格納されている配列 = '".print_r($info,true)."'");

	$res = null;
	if (isset($info['update']['tbl_company_promoter_investment'])) {
		if (is_array($info['update']['tbl_company_promoter_investment'])) {

			//テーブルのフィールド情報を取得する。→maxlengthに使用する。
			$colInfo = _DB_GetColumnsInfo('tbl_company_promoter_investment');

			//出資タイプマスタ
			$condition = null;
			$order = null;
			$order .= "lpad(show_order,10,'0')";	//ソート条件=表示順の昇順
			$order .= ",id";						//ソート条件=IDの昇順
			$mstInvestmentTypeList = _DB_GetList('mst_investment_type', $condition, false, $order, 'del_flag', 'id');

			foreach ($info['update']['tbl_company_promoter_investment'] as $cId => $companyList) {
				foreach ($companyList as $pNo => $promoterList) {
					foreach ($promoterList as $tId => $typeList) {

						$investmentTypeName = $mstInvestmentTypeList[$tId]['name'];

						$messageName1 = null;
						$messageName1 .= "出資者".$pNo."人目、「".$investmentTypeName."」出資の ";

						$count = 0;
						$delCount = 0;
						foreach ($typeList['investment_info'] as $iKey => $investmentInfo) {

							$count++;

							$messageName2 = null;
							$messageName2 .= $messageName1;
							if (count($typeList['investment_info']) > 1) {
								$messageName2 .= "".$count."つ目の";
							} else {
								$messageName2 .= "";
							}

							//削除フラグがチェックONの場合、削除するのでエラーチェックを免除する。
							if (isset($investmentInfo['cmp_prm_inv_del_flag']) && $investmentInfo['cmp_prm_inv_del_flag'] == DELETE_FLAG_YES) {
								$delCount++;
								continue;
							}

							foreach ($investmentInfo as $name => $value) {
								//項目名を取得する。
								$label = $xmlList['tbl_company_promoter_investment']['item_label'][$name];

								//「半角」-「全角」を変換する。
								if (!_IsNull($colInfo)) {
									switch ($colInfo[$name]['TypeOnly']) {
										case 'int':
										case 'bigint':
										case 'double':
											//「全角」英数字を「半角」に変換する。
											$value = mb_convert_kana($value, 'a');
											break;
										default:
											//「半角」英数字を「全角」に変換する。'A'
											//「半角カタカナ」を「全角カタカナ」に変換する。'K'
											//濁点付きの文字を一文字に変換する。'V'
											//「半角」スペースを「全角」に変換する。'S'
											$value = mb_convert_kana($value, 'AKVS');
											//変換できてない文字を変換する。(最後のは「潤ｵチルダ」)
											$searchList = array( '"', '\'', '\\', chr(hexdec('7E')));
											$replaceList = array('”', '’', '￥', chr(hexdec('A1')).chr(hexdec('C1')));
											$value = str_replace($searchList, $replaceList, $value);
											break;
									}
									//変換した値を戻す。
									$info['update']['tbl_company_promoter_investment'][$cId][$pNo][$tId]['investment_info'][$iKey][$name] = $value;
								}

								switch ($name) {
									case 'cmp_prm_inv_stock_num':
									case 'cmp_prm_inv_in_kind':
										//必須チェック
										if (_IsNull($value)) {
//											$res .= "出資者".$pNo."人目の".$investmentTypeName."の".$label."".$count."つ目を入力してください。\n";
											$res .= $messageName2.$label."を入力してください。\n";
										}
										break;
								}

								//文字列長チェック
								//テーブルが存在する場合、フィールドのサイズを設定する。
								if (!_IsNull($colInfo)) {
									$maxlength = null;
									if (isset($colInfo[$name]['Size']) && !_IsNull($colInfo[$name]['Size'])) {
										$maxlength = $colInfo[$name]['Size'];
									}
									if (!_IsNull($maxlength)) {
										if (_IsMaxLength($value, $maxlength)) {
//											$res .= "出資者".$pNo."人目の".$investmentTypeName."の".$label."".$count."つ目は、".$maxlength."文字以内で入力してください。(全角文字は2文字として扱っています。)\n";
											$res .= $messageName2.$label."は、".$maxlength."文字以内で入力してください。(全角文字は2文字として扱っています。)\n";
										}
									}
								}

								//半角数字チェック
								if (!_IsNull($colInfo)) {
									switch ($colInfo[$name]['TypeOnly']) {
										case 'int':
										case 'bigint':
											//半角数字＋マイナス(-)チェック
											if (!_IsHalfSizeNumericMinus($value)) {
//												$res .= "出資者".$pNo."人目の".$investmentTypeName."の".$label."".$count."つ目は、半角数字(整数)で入力してください。\n";
												$res .= $messageName2.$label."は、半角数字(整数)で入力してください。\n";
											}
											break;
										case 'double':
											//半角数字＋ドット(.)＋マイナス(-)チェック
											if (!_IsHalfSizeNumericDotMinus($value)) {
//												$res .= "出資者".$pNo."人目の".$investmentTypeName."の".$label."".$count."つ目は、半角数字(実数)で入力してください。\n";
												$res .= $messageName2.$label."は、半角数字(実数)で入力してください。\n";
											}
											break;
									}
								}
							}
						}

						if ($count == $delCount) {
							$res .= $messageName1;
							$res .= "".$xmlList['tbl_company_promoter_investment']['item_label']['cmp_prm_inv_stock_num']."、";
							$res .= "".$xmlList['tbl_company_promoter_investment']['item_label']['cmp_prm_inv_in_kind']."";
							$res .= "を1つは入力してください。";
							$res .= "\n";
						}
					}
				}
			}
		}
	}

	_Log("[_CheackInput4CompanyPromoterInvestment] 結果 = '".$res."'");
	_Log("[_CheackInput4CompanyPromoterInvestment] end.");

	return $res;
}

/**
 * 会社_目的テーブル情報用
 * 入力値のチェックをする。
 *
 * @param	array	$xmlList		XMLを読み込んだ配列
 * @param	array	$info			入力した値が格納されている配列
 * @return	エラーメッセージ
 * @access  public
 * @since
 */
function _CheackInput4CompanyPurpose($xmlList, $info) {

	_Log("[_CheackInput4CompanyPurpose] start.");

	_Log("[_CheackInput4CompanyPurpose] (param) XMLを読み込んだ配列 = '".print_r($xmlList,true)."'");
	_Log("[_CheackInput4CompanyPurpose] (param) 入力した値が格納されている配列 = '".print_r($info,true)."'");

	$res = null;
	if (isset($info['update']['tbl_company_purpose']['purpose_info'])) {
		if (is_array($info['update']['tbl_company_purpose']['purpose_info'])) {

			$count = 0;
			$delCount = 0;
			foreach ($info['update']['tbl_company_purpose']['purpose_info'] as $pKey => $purposeInfo) {
				$count++;
				//削除フラグがチェックONの場合、削除するのでエラーチェックを免除する。
				if (isset($purposeInfo['cmp_pps_del_flag']) && $purposeInfo['cmp_pps_del_flag'] == DELETE_FLAG_YES) {
					$delCount++;
					continue;
				}
			}
			if ($count == $delCount) {
				$res .= "目的を1つは入力してください。";
				$res .= "\n";
			}
		}
	}


	_Log("[_CheackInput4CompanyPurpose] 結果 = '".$res."'");
	_Log("[_CheackInput4CompanyPurpose] end.");

	return $res;
}

/**
 * 会社_発起人テーブル情報用
 * 入力値のチェックをする。
 *
 * @param	array	$xmlList		XMLを読み込んだ配列
 * @param	array	$info			入力した値が格納されている配列
 * @return	エラーメッセージ
 * @access  public
 * @since
 */
function _CheackInput4CompanyPromoter($xmlList, $info) {

	_Log("[_CheackInput4CompanyPromoter] start.");

	_Log("[_CheackInput4CompanyPromoter] (param) XMLを読み込んだ配列 = '".print_r($xmlList,true)."'");
	_Log("[_CheackInput4CompanyPromoter] (param) 入力した値が格納されている配列 = '".print_r($info,true)."'");

	$res = null;
	if (isset($info['update']['tbl_company_promoter'])) {
		if (is_array($info['update']['tbl_company_promoter'])) {

			$count = 0;
			$delCount = 0;
			foreach ($info['update']['tbl_company_promoter'] as $tcpKey => $tblCompanyPromoterInfo) {
				$count++;
				//削除フラグがチェックONの場合、削除するのでエラーチェックを免除する。
				if (isset($tblCompanyPromoterInfo['cmp_prm_del_flag']) && $tblCompanyPromoterInfo['cmp_prm_del_flag'] == DELETE_FLAG_YES) {
					$delCount++;
					continue;
				}
			}
			if ($count == $delCount) {
				$res .= "発起人(出資者)を1人は入力してください。";
				$res .= "\n";
			}
		}
	}


	_Log("[_CheackInput4CompanyPromoter] 結果 = '".$res."'");
	_Log("[_CheackInput4CompanyPromoter] end.");

	return $res;
}

/**
 * 会社_役員テーブル情報用
 * 入力値のチェックをする。
 *
 * @param	array	$xmlList		XMLを読み込んだ配列
 * @param	array	$info			入力した値が格納されている配列
 * @return	エラーメッセージ
 * @access  public
 * @since
 */
function _CheackInput4CompanyBoard($xmlList, $info) {

	_Log("[_CheackInput4CompanyBoard] start.");

	_Log("[_CheackInput4CompanyBoard] (param) XMLを読み込んだ配列 = '".print_r($xmlList,true)."'");
	_Log("[_CheackInput4CompanyBoard] (param) 入力した値が格納されている配列 = '".print_r($info,true)."'");

	$res = null;
	if (isset($info['update']['tbl_company_board'])) {
		if (is_array($info['update']['tbl_company_board'])) {

			$condition = array();
			$condition['cmp_company_id'] = $info['condition']['_id_'];//会社ID
			$undeleteOnly = false;
			$tblCompanyInfo = _DB_GetInfo('tbl_company', $condition, $undeleteOnly, 'cmp_del_flag');
			if (!_IsNull($tblCompanyInfo)) {
				$numList = array();
				foreach ($info['update']['tbl_company_board'] as $tcbKey => $tblCompanyBoardInfo) {
					//役職ID
					if (isset($tblCompanyBoardInfo['cmp_bod_post_id']) && !_IsNull($tblCompanyBoardInfo['cmp_bod_post_id'])) {
						if (isset($numList[$tblCompanyBoardInfo['cmp_bod_post_id']])) {
							$numList[$tblCompanyBoardInfo['cmp_bod_post_id']]++;
						} else {
							$numList[$tblCompanyBoardInfo['cmp_bod_post_id']] = 1;
						}
					}
				}

				_Log("[_CheackInput4CompanyBoard] 役職人数 = '".print_r($numList,true)."'");
				//代表取締役
				$repDirectorNum = 0;
				if (isset($numList[MST_POST_ID_REP_DIRECTOR])) $repDirectorNum = $numList[MST_POST_ID_REP_DIRECTOR];
				//取締役
				$directorNum = 0;
				if (isset($numList[MST_POST_ID_DIRECTOR])) $directorNum = $numList[MST_POST_ID_DIRECTOR];
				//監査役
				$inspectorNum = 0;
				if (isset($numList[MST_POST_ID_INSPECTOR])) $inspectorNum = $numList[MST_POST_ID_INSPECTOR];

				_Log("[_CheackInput4CompanyBoard] (入力値)代表取締役 = '".$repDirectorNum."'");
				_Log("[_CheackInput4CompanyBoard] (入力値)取締役 = '".$directorNum."'");
				_Log("[_CheackInput4CompanyBoard] (入力値)監査役 = '".$inspectorNum."'");

				_Log("[_CheackInput4CompanyBoard] (設定値)取締役 = '".$tblCompanyInfo['cmp_director_num']."'");
				_Log("[_CheackInput4CompanyBoard] (設定値)監査役 = '".$tblCompanyInfo['cmp_inspector_num']."'");
				
				if ($repDirectorNum == 0) {
					$res .= "代表取締役を1人は入力してください。";
					$res .= "\n";
				}
				if (!_IsNull($tblCompanyInfo['cmp_director_num'])) {
					if ($tblCompanyInfo['cmp_director_num'] != ($repDirectorNum + $directorNum)) {
						$res .= "代表取締役と取締役は合計".$tblCompanyInfo['cmp_director_num']."人にしてください。";
						$res .= "\n";
					}
				}
				if (!_IsNull($tblCompanyInfo['cmp_inspector_num'])) {
					if ($tblCompanyInfo['cmp_inspector_num'] != $inspectorNum) {
						$res .= "監査役は合計".$tblCompanyInfo['cmp_inspector_num']."人にしてください。";
						$res .= "\n";
					}
				}
			}
		}
	}

	_Log("[_CheackInput4CompanyBoard] 結果 = '".$res."'");
	_Log("[_CheackInput4CompanyBoard] end.");

	return $res;
}
?>
