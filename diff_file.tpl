<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>ファイル比較</title>
<script language="Javascript">
function submit_check(){
  var form = document.f_sch;
  form.submit();
}
</script>
</head>
<body>
<form name="f_sch" method="post" action="diff_file.php" enctype="multipart/form-data">
<table>
  <tr>
    <td colspan="2">
      ファイル１に変更元、ファイル２に変更先をそれぞれ選択し、比較ボタンをクリックして下さい。<br />
    </td>
  </tr>
  <tr>
    <td>ファイル１：</td>
    <td>
      <input name="file_name1" id="file_name1" type="file" size="50" maxlength="100" style="cursor:pointer" />
    </td>
  </tr>
  <tr>
    <td>ファイル２：</td>
    <td>
      <input name="file_name2" id="file_name2" type="file" size="50" maxlength="100" style="cursor:pointer" />
    </td>
  </tr>
  <tr>
   <td width>&nbsp;</td>
   <td>
      <input type="button" name="btn_ok" value="　　比　較　　" onclick="submit_check()" style="cursor:pointer" />
      <input type="button" name="clear" value="クリア" onclick="location.href='diff_file.php'" style="cursor:pointer" />
    </td>
  </tr>
</table>
</form>
<?php echo $msg ?>
</body>
</html>
