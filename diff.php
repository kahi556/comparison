<?php
/**
 * テキストファイルの比較関数
 */

//----------------------------------------------------
// ファイルの比較
// @param string  $file1 比較対象ファイル名
// @param string  $file2 比較対象ファイル名
// @return string 比較表示HTML
//----------------------------------------------------
function diff($file1, $file2) {
	define('LSTAT_NOTMODIFIED',0);
	define('LSTAT_ADDITEM',1);
	define('LSTAT_DELITEM',2);
	define('LSTAT_UPDATE',3);
	
	define('COL_ALL','D3D3D3');
	define('COL_ADD','87CEEB');
	define('COL_DEL','FFD700');
	define('COL_EQ','90EE90');
	
	// 変数初期化
	$arr_src_file = array();
	$arr_diff = array();
	$line_src = 1;
	$line_dst = 1;
	$src = 0;
	$dst = 0;
	$prev_end_src = 0;
	$prev_lstat = LSTAT_NOTMODIFIED;
	$cnt_add = 0;
	$cnt_del = 0;
	$msg = "";
	$flg_zero = false;
	
	//DIFF作成（unified）
	$tmp = tempnam(sys_get_temp_dir(),'diff');
	touch($tmp);
	$cmd = 'diff -u '.escapeshellarg($file1).' '.escapeshellarg($file2).' >'.escapeshellarg($tmp);
	exec($cmd, $arr, $ret);
	
	// 比較元配列データ作成
	$fp1 = fopen($file1, 'r');
	if ($fp1) {
		if (flock($fp1, LOCK_SH)) {
			while (!feof($fp1)) {
				$arr_src_file[] = fgets($fp1);
			}
			
			flock($fp1, LOCK_UN);
		}else{
			print('ファイルロックに失敗しました');
			return false;
		}
	}
	fclose($fp1);
	
	// 差分配列データ作成
	$fp2 = fopen($tmp, 'r');
	if ($fp2) {
		if (flock($fp2, LOCK_SH)) {
			while (!feof($fp2)) {
				$arr_diff[] = fgets($fp2);
			}
			
			flock($fp2, LOCK_UN);
		}else{
			print('ファイルロックに失敗しました');
			return false;
		}
	}
	fclose($fp2);
	$i = count($arr_diff) - 1;
	$arr_diff[$i] = "";
	
	// 差分データを元にリスト作成
	$wk_count = count($arr_diff);
	for($i = 0; $i < $wk_count; $i++) {
		//もしもファイル情報が残っていた場合
		if (preg_match('{^(\+\+\+|---)}',$arr_diff[$i]) > 0) {
			//使用済み削除
			unset($arr_diff[$i]);
			continue;
		}
		//行変更情報
		if (preg_match('{^@@}',$arr_diff[$i]) > 0) {
			$start_src = 0;
			$end_src = 0;
			$nums = preg_split('{ }',trim($arr_diff[$i],'@ -'));
			$nums_b = preg_split('{,}',$nums[0]);
			$start_src = $nums_b[0] - 1;
			if (count($nums_b) > 1) $end_src = $nums_b[1] - 1;
			$end_src = $start_src + $end_src;
			
			unset($nums);
			unset($nums_b);
			//使用済み削除
			unset($arr_diff[$i]);
			
			//割り当て情報にないアイテム(行)を処理
			for($j = $prev_end_src; $j < $start_src; $j++) {
				$arr_src[$src] = $line_src.','.LSTAT_NOTMODIFIED.','.$arr_src_file[$line_src-1];
				$arr_dst[$dst] = $line_dst.','.LSTAT_NOTMODIFIED.','.$arr_src_file[$line_src-1];
				$src++;
				$line_src++;
				$dst++;
				$line_dst++;
			}
			
			$readed = $i + 1;
			$ended = count($arr_diff) + $i;
			
			while($readed < $ended) {
				$cur_lstat = LSTAT_NOTMODIFIED;
				
				if (!empty($arr_diff[$readed])) {
					//次の変更情報行があったらこのループを終了
					if (preg_match('{^@@}',$arr_diff[$readed]) > 0) {
						//アイテム位置をそろえる
						$line_max = max($src,$dst);
						$src = $line_max;
						$dst = $line_max;
						break;
					}
					
					//変更の種類を知る
					if (preg_match('{^\+}',$arr_diff[$readed]) > 0) {
						$cur_lstat = LSTAT_ADDITEM;
					}elseif (preg_match('{^-}',$arr_diff[$readed]) > 0) {
						$cur_lstat = LSTAT_DELITEM;
					}elseif (preg_match('{^ }',$arr_diff[$readed]) > 0) {
						$cur_lstat = LSTAT_NOTMODIFIED;
					}else{
						$readed++;
						continue;
					}
					//DIFF書式排除
					$line_content = ltrim($arr_diff[$readed],' +-');
					//使用済み削除
					unset($arr_diff[$readed]);
				}else{
					$readed++;
					continue;
				}
				
				if ($prev_lstat != $cur_lstat) {
					if ($prev_lstat != LSTAT_NOTMODIFIED) {
//					if ($prev_lstat != LSTAT_NOTMODIFIED &&
//							($prev_lstat != LSTAT_DELITEM && $cur_lstat != LSTAT_ADDITEM)) {
						//アイテム位置をそろえる
						$line_max = max($src,$dst);
						$src = $line_max;
						$dst = $line_max;
					}
					$prev_lstat = $cur_lstat;
				}
				//アイテム（行）配置
				switch($cur_lstat) {
					case LSTAT_ADDITEM:
						$arr_dst[$dst] = $line_dst.','.LSTAT_ADDITEM.','.$line_content;
						$dst++;
						$line_dst++;
						$cnt_add++;
						break;
					case LSTAT_DELITEM:
						$arr_src[$src] = $line_src.','.LSTAT_DELITEM.','.$line_content;
						$src++;
						$line_src++;
						$cnt_del++;
						break;
					case LSTAT_NOTMODIFIED:
						$arr_dst[$dst] = $line_dst.','.LSTAT_NOTMODIFIED.','.$line_content;
						$dst++;
						$line_dst++;
						$arr_src[$src] = $line_src.','.LSTAT_NOTMODIFIED.','.$line_content;
						$src++;
						$line_src++;
						break;
				}
				$i = $readed;
			}
			$prev_end_src = $end_src + 1;
		}
	}
	unset($arr_diff);
	
	$msg.= "<br />";
	$msg_del = "削除(<span style=\"color:".COL_DEL."\">gold</span>)：".$cnt_del."行";
	$msg_add = "追加(<span style=\"color:".COL_ADD."\">skyblue</span>)：".$cnt_add."行";
	if (($cnt_del === 0) && ($cnt_add === 0)) {
		$msg.= "ファイル内容が一致しました。<br />\n";
		$flg_zero = true;
	}elseif ($cnt_del > 0) {
		$msg.= $msg_del;
		if ($cnt_add > 0) {
			$msg.= "、".$msg_add;
		}
		$msg.= "ありました。<br />\n";
	}elseif ($cnt_add > 0) {
		$msg.= $msg_add."ありました。<br />\n";
	}
	
	if (!$flg_zero) {
		$msg.= "<table><tr><th>行</th><th>ファイル１</th><th>ファイル２</th><th>行</th></tr>\n";
		
		//カラーコード対応配列
		$arr_color = array(
				LSTAT_NOTMODIFIED=>COL_EQ,
				LSTAT_ADDITEM=>COL_ADD,
				LSTAT_DELITEM=>COL_DEL,
				-1=>COL_ALL
		);
		//最後の要素
		end($arr_src);
		end($arr_dst);
		$max = max(key($arr_src),key($arr_dst));
		
		for($k = 0; $k <= $max; $k++) {
			$msg.= '<tr>';
			
			$tmp = isset($arr_src[$k]) ? $arr_src[$k] : ' ,-1, ';
			$tmp = explode(',',$tmp,3);
			list($linenum,$modnum,$line) = $tmp;
			if (mb_detect_encoding($line, 'UTF-8, SJIS') == 'SJIS') {
				$line = mb_convert_encoding($line, 'UTF-8', 'SJIS');
			}
			$line = htmlspecialchars($line,ENT_QUOTES); // HTMLサニタイズ
			$msg.= '<td bgcolor="'.$arr_color[$modnum].'">'.$linenum.'</td><td bgcolor="'.$arr_color[$modnum].'">'.$line.'</td>';
			
			$tmp = isset($arr_dst[$k]) ? $arr_dst[$k] : ' ,-1, ';
			$tmp = explode(',',$tmp,3);
			list($linenum,$modnum,$line) = $tmp;
			if (mb_detect_encoding($line, 'UTF-8, SJIS') == 'SJIS') {
				$line = mb_convert_encoding($line, 'UTF-8', 'SJIS');
			}
			$line = htmlspecialchars($line,ENT_QUOTES); // HTMLサニタイズ
			$msg.= '<td bgcolor="'.$arr_color[$modnum].'">'.$line.'</td><td bgcolor="'.$arr_color[$modnum].'">'.$linenum.'</td>';
			
			$msg.= "</tr>\n";
		}
		
		$msg.= "</table>\n";
	}
	//var_dump($arr_src_file);
	//var_dump($arr_diff);
	//$msg = "";
	return $msg;
}
?>
