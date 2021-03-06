<?php
/*
 * [新★会社設立.JP ツール]
 * PDF作成
 * 委任状
 *
 * 更新履歴：2008/12/01	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../../../common/include.ini");
include_once("../../../../common/libs/fpdf/mbfpdf.php");


_LogDelete();
//_LogBackup();
_Log("[/user/company/pdf/create/ininjo.php] start.");

_Log("[/user/company/pdf/create/ininjo.php] POST = '".print_r($_POST,true)."'");
_Log("[/user/company/pdf/create/ininjo.php] GET = '".print_r($_GET,true)."'");
_Log("[/user/company/pdf/create/ininjo.php] SERVER = '".print_r($_SERVER,true)."'");


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


//DBをオープンする。
$link = _DB_Open();

//エラーメッセージ
$errorList = array();

$inData = null;
switch ($_SERVER["REQUEST_METHOD"]) {
	case 'POST':
		$inData = $_POST;
		break;
	case 'GET':
		$inData = $_GET;
		break;
}

//ユーザーID
$userId = (isset($inData['user_id'])?$inData['user_id']:null);
//会社ID
$companyId = (isset($inData['company_id'])?$inData['company_id']:null);

//作成日
$pdfCreateYear = ((isset($inData['create_year']) && !_IsNull($inData['create_year']))?$inData['create_year']:date('Y'));
$pdfCreateMonth = ((isset($inData['create_month']) && !_IsNull($inData['create_month']))?$inData['create_month']:date('n'));
$pdfCreateDay = ((isset($inData['create_day']) && !_IsNull($inData['create_day']))?$inData['create_day']:date('j'));

//動作モード
$mode = (isset($inData['mode'])?$inData['mode']:null);

//初期値を設定する。
$undeleteOnly4def = false;

//権限によって、表示するユーザー情報を制限する。
switch($loginInfo['usr_auth_id']){
	case AUTH_NON://権限無し

		_Log("[/user/company/pdf/create/ininjo.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."' = '権限無し'");
		_Log("[/user/company/pdf/create/ininjo.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
		_Log("[/user/company/pdf/create/ininjo.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");

		$undeleteOnly4def = true;

		//自分のユーザー情報、会社情報のみ表示する。
		//ユーザーID、会社IDをチェックする。

		//会社IDを検索する。
		$relationCompanyId = _GetRelationCompanyId($loginInfo['usr_user_id']);


		_Log("[/user/company/pdf/create/ininjo.php] {ログインユーザー権限処理} →(ログイン)ユーザーID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/company/pdf/create/ininjo.php] {ログインユーザー権限処理} →(ログイン)会社ID = '".$relationCompanyId."'");
		_Log("[/user/company/pdf/create/ininjo.php] {ログインユーザー権限処理} →(パラメーター)ユーザーID = '".$userId."'");
		_Log("[/user/company/pdf/create/ininjo.php] {ログインユーザー権限処理} →(パラメーター)会社ID = '".$companyId."'");

		if ($userId != $loginInfo['usr_user_id']) $userId = $loginInfo['usr_user_id'];
		if ($companyId != $relationCompanyId) $companyId = $relationCompanyId;

		_Log("[/user/company/pdf/create/ininjo.php] {ログインユーザー権限処理} →(処理対象)ユーザーID = '".$userId."'");
		_Log("[/user/company/pdf/create/ininjo.php] {ログインユーザー権限処理} →(処理対象)会社ID = '".$companyId."'");

		break;
}

//入金チェック
if (!_IsNull($companyId)) {
	if (!_CheckUserStatus($userId, $companyId, MST_SYSTEM_COURSE_ID_CMP)) {
		$errorList[] = "※申し訳ございません。書類の作成(印刷)は、ご利用料金の決済後にご利用が可能となります。";
		$_SESSION[SID_PDF_ERR_MSG] = $errorList;
		//エラー画面を表示する。
		header("Location: ../error.php");
		exit;
	}
}

$companyInfo = null;
if (!_IsNull($companyId)) {
	//会社情報を取得する。
	$companyInfo = _GetCompanyInfo($companyId, $undeleteOnly4def);
}

if (_IsNull($companyInfo)) {
	$errorList[] = "※該当の会社情報が存在しません。";

	$_SESSION[SID_PDF_ERR_MSG] = $errorList;

	//エラー画面を表示する。
	header("Location: ../error.php");
	exit;
}

//登録情報をチェックする。
//動作モード
if (_IsNull($mode)) $errorList[] = "『代理人』を指定してください。";
//代理人
switch ($mode) {
	case PDF_MODE_ININJO_PROMOTER:
		//発起人の一人が代理人として登記申請に行く場合
		//発起人
		if (!isset($inData['cmp_prm_no']) || _IsNull($inData['cmp_prm_no'])) $errorList[] = "『発起人』を選択してください。";
		break;
	case PDF_MODE_ININJO_OTHER:
		//発起人以外の第3者が代理人として登記申請に行く場合
		//氏名
		$errFlag = false;
		if (!isset($inData['family_name']) || _IsNull($inData['family_name'])) $errFlag = true;
		if (!isset($inData['first_name']) || _IsNull($inData['first_name'])) $errFlag = true;
		if ($errFlag)  $errorList[] = "『代理人』の『氏名』を登録してください。";
		//住所
		$errFlag = false;
		if (!isset($inData['pref_id']) || _IsNull($inData['pref_id'])) $errFlag = true;
		if (!isset($inData['address1']) || _IsNull($inData['address1'])) $errFlag = true;
		if ($errFlag)  $errorList[] = "『代理人』の『住所』を登録してください。";
		break;
	default:
		$errorList[] = "『代理人』を指定してください。";
		break;
}
//会社名
if (_IsNull($companyInfo['tbl_company']['cmp_company_name'])) $errorList[] = "『商号(会社名)』を登録してください。";
//発起人
$errFlag = false;
foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {
	if (_IsNull($promoterInfo['cmp_prm_family_name']) || _IsNull($promoterInfo['cmp_prm_first_name'])) {
		$errFlag = true;
		break;
	}
	if (_IsNull($promoterInfo['cmp_prm_pref_id']) || _IsNull($promoterInfo['cmp_prm_address1'])) {
		$errFlag = true;
		break;
	}
}
if ($errFlag) $errorList[] = "『発起人』の『お名前』、『住所』を登録してください。";




if (count($errorList) > 0) {
	//エラー有の場合
	_Log("[/user/company/pdf/create/ininjo.php] end. ERR!");


	$buf = "※PDFを作成するための情報が足りません。『株式会社設立情報登録』画面で、情報を入力してください。又は、『定款認証』画面で、情報を入力してください。";
	array_unshift($errorList, $buf);

	$_SESSION[SID_PDF_ERR_MSG] = $errorList;

	//エラー画面を表示する。
	header("Location: ../error.php");
	exit;
}


//マスタ情報を取得する。
$undeleteOnly = false;
$mstPrefList = _GetMasterList('mst_pref');		//都道府県マスタ
unset($mstPrefList[MST_PREF_ID_OVERSEAS]);


//定数--------------------------------------------start
//フォントサイズを定義する。
//通常
$normalFontSize = 10;

//タイトル
$title = "委　任　状";


//[デバッグ用]
//ボーダー
$border = 0;
//定数--------------------------------------------end


// EUC-JP->SJIS 変換を自動的に行なわせる場合に mbfpdf.php 内の $EUC2SJIS を
// true に修正するか、このように実行時に true に設定しても変換してくます。
//$GLOBALS['EUC2SJIS'] = true;

//PDFのサイズを設定する。デフォルト=FPDF($orientation='P',$unit='mm',$format='A4')
$pdf = new MBFPDF();

//フォントを設定する。
$pdf->AddMBFont(GOTHIC ,'SJIS');
$pdf->AddMBFont(PGOTHIC,'SJIS');
$pdf->AddMBFont(MINCHO ,'SJIS');
$pdf->AddMBFont(PMINCHO,'SJIS');
$pdf->AddMBFont(KOZMIN ,'SJIS');

//マージンを設定する。
$pdf->SetLeftMargin(20);
$pdf->SetRightMargin(20);
$pdf->SetTopMargin(20);


$pdf->SetFont(MINCHO,'',$normalFontSize);

//自動改ページモードをON(true)、ページの下端からの距離（マージン）が2 mmになった場合、改行するように設定する。
$pdf->SetAutoPageBreak(true, 20);

//ドキュメントのタイトルを設定する。
$pdf->SetTitle($title);
//ドキュメントの主題(subject)を設定する。
$pdf->SetSubject($title);



$pdf->AddPage();


$pdf->Ln(30);


//タイトル
$pdf->SetFontSize(18);
$pdf->Cell(0,10,$title,$border,0,"C");
$pdf->Ln(30);


$pdf->SetFontSize(10);


//代理人を設定する。
$agent = null;
switch ($mode) {
	case PDF_MODE_ININJO_PROMOTER:
		//発起人の一人が代理人として登記申請に行く場合
		//選択された発起人情報を取得する。
		$promoterInfo4Agent = null;
		foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {
			if ($promoterInfo['cmp_prm_no'] == $inData['cmp_prm_no']) {
				$promoterInfo4Agent = $promoterInfo;
				break;
			}
		}
		$agent .= "発起人";
		$agent .= " ";
		$agent .= $promoterInfo4Agent['cmp_prm_family_name'];
		$agent .= " ";
		$agent .= $promoterInfo4Agent['cmp_prm_first_name'];
		$agent .= " ";
		$agent .= "（";
		$agent .= "所在地：";
		$agent .= $mstPrefList[$promoterInfo4Agent['cmp_prm_pref_id']]['name'];
		$agent .= $promoterInfo4Agent['cmp_prm_address1'];
		if (!_IsNull($promoterInfo4Agent['cmp_prm_address2'])) {
			//$agent .= " ";
			$agent .= $promoterInfo4Agent['cmp_prm_address2'];
		}
		$agent .= "）";
		break;
	case PDF_MODE_ININJO_OTHER:
		//発起人以外の第3者が代理人として登記申請に行く場合
		$agent .= $inData['family_name'];
		$agent .= " ";
		$agent .= $inData['first_name'];
		$agent .= " ";
		$agent .= "（";
		$agent .= "所在地：";
		$agent .= $mstPrefList[$inData['pref_id']]['name'];
		$agent .= $inData['address1'];
		if (!_IsNull($inData['address2'])) {
			//$agent .= " ";
			$agent .= $inData['address2'];
		}
		$agent .= "）";
		break;
	default:
		break;
}

$buf = null;
$buf .= "私は、";
$buf .= $agent;
$buf .= "を代理人と定め、下記権限を委任する。";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln(20);


$pdf->SetFontSize(13);

$buf = "記";
$pdf->Cell(0,6,$buf,$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(10);

//会社名・法人名
$buf = null;
$buf .= $companyInfo['tbl_company']['cmp_company_name'];
$buf .= "の設立に関し、添付のとおり電磁的記録であるその原始定款を作成する手続等に関する一切の件。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln(20);


//作成日
$pdfCreateYearJp = _ConvertAD2Jp($pdfCreateYear);
$buf = $pdfCreateYearJp."年";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(20,6,$buf,$border,0,"L");
$buf = $pdfCreateMonth."月";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(12,6,$buf,$border,0,"R");
$buf = $pdfCreateDay."日";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(12,6,$buf,$border,0,"R");
$buf = null;
$pdf->Cell(0,6,$buf,$border,0,"R");

$pdf->Ln(20);


//発起人
foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {
	$buf = null;
	$buf .= "発起人 住所";
	$pdf->Cell(30,6,$buf,$border,0,"L");
	$buf = null;
	$buf .= $mstPrefList[$promoterInfo['cmp_prm_pref_id']]['name'];
	$buf .= $promoterInfo['cmp_prm_address1'];
	$buf .= $promoterInfo['cmp_prm_address2'];
	$buf = mb_convert_kana($buf, 'N');
	$pdf->MultiCell(0,6,$buf,$border,"L");

	$buf = null;
	$buf .= "氏名又は名称";
	$pdf->Cell(30,6,$buf,$border,0,"L");
	$buf = null;
	$buf .= $promoterInfo['cmp_prm_family_name'];
	$buf .= " ";
	$buf .= $promoterInfo['cmp_prm_first_name'];
	$pdf->Cell(100,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= "印";
	$pdf->Cell(0,6,$buf,$border,0,"L");

	$pdf->Ln(15);
}











//DBをクローズする。
_DB_Close($link);


//PDFを出力する。
$pdf->Output();

_Log("[/user/company/pdf/create/ininjo.php] end. OK!");



function _mb_str_split($str, $length = 1) {
	if ($length < 1) return false;

	$result = array();

	for ($i = 0; $i < mb_strlen($str); $i += $length) {
		$result[] = mb_substr($str, $i, $length);
	}

	return $result;
}

function _no($no) {
	return mb_convert_kana("第".$no."条", 'N');
}

?>
