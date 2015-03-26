<?php
//
//ファイル比較
//
require("diff.php"); // ファイル比較

// 変数初期化
$wk_msg = "";
$msg = "";
$count = 0;

//***********************************************
// 受信データをもとに変数の設定
//***********************************************
if ($_SERVER["REQUEST_METHOD"] == "POST") { 
}else{
	require("diff_file.tpl"); // ファイル比較テンプレート呼び出し
	exit;
}

//***********************************************
// ファイル名チェック（英数字、ハイフン、ドット以外エラー）
//***********************************************
for($i = 1; $i <= 2; $i++) {
	$arr_save_name[$i] = "";
	$arr_name[$i] = "";
	// ファイル名が編集されているもののみ処理する
	if (!isset($_FILES["file_name".$i]["name"])) {
		$msg = ERR_S."ファイル１，２とも、ファイル選択は必須です！！！".ERR_E."\n";
		require("diff_file.tpl"); // ファイル比較テンプレート呼び出し
		exit;
	}
	if(isset($_FILES["file_name".$i]["error"])) {
		$arr_save_name[$i] = $_FILES["file_name".$i]["name"]; // ファイル名
		$arr_name[$i] = $_FILES["file_name".$i]["tmp_name"]; // 一時ファイル
		$count++;
	}
}

$wk_msg.= "*****ファイル比較結果*****<br /><br />";
$wk_msg.= "ファイル１ = ".$arr_save_name[1]."<br />";
$wk_msg.= "ファイル２ = ".$arr_save_name[2]."<br />";

if ($count <= 1) {
	$msg = ERR_S."選択したファイルが不正です！！！".ERR_E."\n";
	$msg = $wk_msg.$msg;
	require("diff_file.tpl"); // ファイル比較テンプレート呼び出し
	exit;
}
if (!$msg = diff($arr_name[1], $arr_name[2])) {
	$msg = ERR_S."ファイル１，２の比較に失敗しました！！！".ERR_E."\n";
	$msg = $wk_msg.$msg;
	require("diff_file.tpl"); // ファイル比較テンプレート呼び出し
	exit;
}

$msg = $wk_msg.$msg;
require("diff_file.tpl"); // ファイル比較テンプレート呼び出し
?>
