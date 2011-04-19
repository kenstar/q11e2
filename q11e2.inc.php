<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: q11e2.inc.php,v 2.20 2006/04/01 10:40:00 ks Exp $
//

/////////////////////////////////////////////////
// コメントの名前テキストエリアのカラム数
define('COMMENT_NAME_COLS',15);
/////////////////////////////////////////////////
// コメントのテキストエリアのカラム数
define('COMMENT_COLS',70);
/////////////////////////////////////////////////
// フリーフォーマット記述のカラム数
define('FREEFORMAT_COLS',70);
/////////////////////////////////////////////////
// コメントの挿入フォーマット
define('COMMENT_NAME_FORMAT','[[$name]]');
define('COMMENT_MSG_FORMAT','$msg');
define('COMMENT_NOW_FORMAT','&new{$now};');
/////////////////////////////////////////////////
// コメントの挿入フォーマット(コメント内容)
//define('COMMENT_FORMAT',"\x08MSG\x08 -- \x08NAME\x08 \x08NOW\x08");
define('COMMENT_FORMAT',"\x08MSG\x08 | \x08NAME\x08 \x08NOW\x08");
/////////////////////////////////////////////////
// コメントが投稿された場合、内容をメールで送る先
define('COMMENT_MAIL',FALSE);
/////////////////////////////////////////////////
// listbox対応 by io
// ::LISTOUT::の代わりに表示されるメッセージ
//define('LISTBOX_MESSAGE',"プルダウンメニューにする項目を選んで下さい。");
define('LISTBOX_MESSAGE',"プルダウンメニューとして、下記の項目が表示されます");
/////////////////////////////////////////////////
// 出力欄のセパレーター by io
//define('OUTPUT_SEPARATOR',"&br;");
define('OUTPUT_SEPARATOR',", ");


function plugin_q11e2_action()
{
  global $script,$vars,$post,$now;
  global $_title_updated,$_no_name;
  global $_msg_comment_collided,$_title_comment_collided;
//  global $notify;

  $post['msg'] = preg_replace("/\n/",'',$post['msg']);

  if ($post['name'] == '' )
    {
      return array('msg'=>'','body'=>'');
    }

  $head = '';
  if (preg_match('/^(-{1,2})(.*)/',$post['msg'],$match))
    {
      $head = $match[1];
      $post['msg'] = $match[2];
    }

  $_msg  = str_replace('$msg',$post['msg'],COMMENT_MSG_FORMAT);
  $_escape_name = preg_replace("/\|/", "", $post['name']);
  $_name = $_escape_name == '' ? $_no_name : $_escape_name;
  $_name = ($_name == '') ? '' : str_replace('$name',$_name,COMMENT_NAME_FORMAT);
  $_now  = ($post['nodate'] == '1') ? '' : str_replace('$now',$now,COMMENT_NOW_FORMAT);

  $comment = str_replace("\x08MSG\x08", $_msg, COMMENT_FORMAT);
  $comment = str_replace("\x08NAME\x08",$_name,$comment);
  $comment = str_replace("\x08NOW\x08", $_now, $comment);
  $comment = $head.$comment;

  $postdata = '';
  $postdata_old  = get_source($post['refer']);
  $comment_no = 0;

// get checked options
  $a_genre = $post['genre'];

// 出力欄編集
  foreach ( $a_genre as $key_g => $value_g ){
    $a_values = $post[$value_g];

    $header .= "|CENTER:$value_g";
    $chkoption .= "|";
    $exist_check =	0;
// -----------この辺からlistbox2対応 by io
    $IsListbox = 0;	//Listboxフラグ初期化
    foreach ( $a_values as $key_v => $value_v ){
      //			 Special Thanks to "io"
      $value_v = preg_replace("/[\|]/",'',$value_v );
      $value_v = htmlspecialchars($value_v);
      if($exist_check == 1) {
	if($value_v != '') {
	  if($IsListbox == 1){
	    $chkoption .= ", ";
	  } else {

	    $chkoption .= OUTPUT_SEPARATOR;
	  }
	  $chkoption .= "$value_v";
	}
      }else {
	if ($value_v == "::LISTOUT::"){
	  $IsListbox = 1;
	  $chkoption .= "#listbox2( , ";
	}else{
	  $chkoption .= "$value_v";
	}
	$exist_check = 1;
      }
    }
    //リストボックスの場合括弧を閉じる{
    if($IsListbox == 1){
      $chkoption .= ")";
    }
  }
//header化 by io
  $header .= "|CENTER:comment|CENTER:回答者・時刻|h";
  $comment = "$chkoption| $comment|";

  if ($exist_check != 1 )
    {
      return array('msg'=>'','body'=>'');
    }

  $switch = 0;
  foreach ($postdata_old as $line)
  {
    if ($switch == 1 ){
      // 投稿メッセージを記述するべき表のヘッダがあるはず
      if (preg_match('/^\|/',$line)){
	// headerがあった
	// 次の行をみて、回答者が同じかどうかを判定する必要がある。
	$switch = 2;
      }else{
        // headerがない。初めての投稿。
	$postdata .= "$header\n$comment\n$line\n";
        $switch = 0;
      }
    }else if (preg_match('/^#q11e2\(/',$line) and $comment_no++ == $post['comment_no']){
      // #q11e2記述の次の行に、投稿されたメッセージを記述する。そのための$switch=1
      $switch = 1;
      $postdata .= $line;
    }else if ( $switch == 2 ){
	// header直後の回答
	//下記の6行で、最新の回答者名と今回の回答者名を取得する
	$pattern = "/(\|[^\|]*)*(\|[^\|]*\|)/";
	preg_match($pattern, $line, $linematches);
	preg_match($pattern, $comment, $commentmatches);
	$pattern = "/(.*) &new/";    // new以降は異なる可能性があり、比較対象外
	preg_match($pattern, $linematches[2], $linewriter);
	preg_match($pattern, $commentmatches[2], $commentwriter);

	// 最新の回答者と今回の回答者が同じ場合は、無視する
	if ($linewriter[1] == $commentwriter[1] ){
	   // 回答者が同じなので、今回の投稿は反映しない
	   $postdata .= "$header\n$line";
           return array('msg'=>'','body'=>'');
	}else{
	   // 回答者が異なるので、投稿を反映する
	   $postdata .= "$header\n$comment\n$line";
	}
	$switch =0;
    }else{
      $postdata .= $line;
    }
  }
  if ($switch == 1){
    // #q11e2が最後の行に記述されている場合は、上のループから抜けてしまう。
    // そのための対応
    $postdata .= "$header\n$comment\n";
  }	   	    
  $switch = 0;

  $title = $_title_updated;
  $body = '';
  if (md5(@join('',get_source($post['refer']))) != $post['digest'])
    {
      $title = $_title_comment_collided;
      $body = $_msg_comment_collided . make_pagelink($post['refer']);
    }
  //うちの環境だとメール送る設定だとうまく動かなかったので、改造 by io
  if(COMMENT_MAIL != FALSE) {
    $notify = 1;
  }
  page_write($post['refer'],$postdata);

  $retvars['msg'] = $title;
  $retvars['body'] = $body;

  $post['page'] = $vars['page'] = $post['refer'];

  return $retvars;
}
function plugin_q11e2_convert()
{
  global $script,$vars,$digest;
  global $_btn_comment,$_btn_name,$_msg_comment;
  static $numbers = array();

  if (!array_key_exists($vars['page'],$numbers))
    {
      $numbers[$vars['page']] = 0;
    }
  $comment_no = $numbers[$vars['page']]++;

// get options
  $options = func_num_args() ? func_get_args() : array();

  foreach ( $options as $arg){

    if (preg_match('/^([^[]+)\[([^]]+)\]$/',$arg,$match,$ch)){
      $s_genre = $match[1];
      $s_values = $match[2];
      if ( preg_match('/\#\#/',$s_values )){
	foreach (preg_split ("/\#\#/",$s_values ) as $value){
	  $a_genre[${s_genre}] .= "$value,";
	}
      }else{
	$a_genre[${s_genre}] .= "$s_values,";
      }
    }
  }

  foreach( $a_genre as $key => $values ){
    $key = htmlspecialchars($key);
    $values = htmlspecialchars($values);

    $avalues = preg_split ("/,/",$values );
    $nvalues = count($avalues);

    $counts=0;

    foreach ($avalues as $value){
      if ($value != '' ){

// 入力タイプの判定
	if ( $counts == 0 ){
	  $items .= "<tr><td class=\"style_td\">$key</td>";
	  $items .= "<td class=\"style_td\">";
	  $items .= "  <input type=\"hidden\" name=\"genre[]\" value=\"$key\">\n";

	  $counts++;
	  if ( $value == '::RADIO::' ){
	    $tagname = "input";
	    $type = "radio";
	    $nvalues--;
	    continue;
	  }else if( $value == '::PULLDOWN::' ){
	    $tagname = "option";
	    $type = "";
	    $nvalues--;
	    $items .= "<select name=\"${key}[]\">";
	    continue;
	  }else if( $value == '::LIST::' ){
	    $tagname = "option";
	    $type = "";
	    $nvalues--;
	    $items .= "<select name=\"${key}[]\" size=\"${nvalues}\">";
	    continue;
	  }else if( $value == '::LISTOUT::' ){
	    $type = "hidden";
	    $tagname = "hidden";
	    
	  }else{
	    $tagname = "input";
	    $type = "checkbox";
	  }
	}
	if( $tagname == "hidden" ){
	  if( $value == '::LISTOUT::' ){
	    $items .= " <input type=\"hidden\" name=\"${key}[]\" value=\"${value}\">";
	    $items .= LISTBOX_MESSAGE;
	    $items .= "<br/>";
	  }else{
	    $items .= " <input type=\"hidden\" name=\"${key}[]\" value=\"${value}\">";
	    $items .= "$value,";
	  }
	}
	if ($tagname == 'input' ){
	  if( $value == '::TEXT::' ){
	    $items .= "<input type=\"text\" name=\"${key}[]\" size=\"".FREEFORMAT_COLS."\" /><br/>\n";
	    continue;
	  }
	  if ( preg_match("/^CHECKED:/",$value) ){
	    $ch = " CHECKED";
	    $value = preg_replace("/^CHECKED:/",'',$value );
	  }else{
	    $ch = "";
	  }
	  $items .= " <input type=\"${type}\" name=\"${key}[]\" value=\"${value}\" ${ch}>${value}<br />\n";
	  $counts++;
	}else if ( $tagname == 'option' ){
	  if ( preg_match("/^CHECKED:/",$value) ){
	    $ch = " SELECTED";
	    $value = preg_replace("/^CHECKED:/",'',$value );
	  }else{
	    $ch = "";
	  }
	  $items .= "<option value=\"${value}\"${ch}>${value}</option>";
	  $counts++;
	}
      }
    }
    if( $tagname == 'option'){
      $items .= "</select>\n";
    }
    $items .= "</td></tr>\n";
  }

  $nametags = $_btn_name.'<input type="text" name="name" size="'.COMMENT_NAME_COLS."\" />\n";

  $s_page = htmlspecialchars($vars['page']);
  $comment_cols = COMMENT_COLS;
  $string = <<<EOD
<br />
<script>
var DisableSubmit = {
   init: function() {
      this.addEvent(window, 'load', this.set());
   },

   set: function() {
      var self = this;
      return function() {
         for (var i = 0; i < document.forms.length; ++i) {
            if(document.forms[i].onsubmit) continue;
            document.forms[i].onsubmit = function() {
               self.setDisable(this.getElementsByTagName('input'));
            };
         }
      }
   },

   setDisable: function(elms) {
      for (var i = 0, elm; elm = elms[i]; i++) {
         if ((elm.type == 'submit' || elm.type == 'image') && !elm.disabled) {
            Set(elm);
            unSet(elm);
         }
      }

      function Set(button) {
         window.setTimeout(function() { button.disabled = true; }, 1);
      }
      function unSet(button) {
         window.setTimeout(function() { button.disabled = false; }, 1000);
      }
   },

   addEvent: function(elm, type, event) {
      if(elm.addEventListener) {
         elm.addEventListener(type, event, false);
      } else if(elm.attachEvent) {
         elm.attachEvent('on'+type, event);
      } else {
         elm['on'+type] = event;
      }
   }
}

DisableSubmit.init();
</script>
<div class="ie5"><table class="style_table" cellspacing="1" border="0"><tbody>
<!-- header化 by io <tr><td class="style_td">項目</td><td class="style_td">選択肢 -->
 <form action="$script" method="post" onsubmit="DisableSubmit(this)">
 <thead><td class="style_td">項目</td><td class="style_td">選択肢
  <input type="hidden" name="comment_no" value="$comment_no" />
  <input type="hidden" name="refer" value="$s_page" />
  <input type="hidden" name="plugin" value="q11e2" />
  <input type="hidden" name="digest" value="$digest" />
<!-- header化 by io </td></tr> -->
</td></thead>
  $items
<tr><td class="style_td"> </td><td class="style_td">
  $nametags
  <input type="text" name="msg" size="$comment_cols" />
  <input type="submit" name="q11e2" value="回答" />
</td></tr>
 </form>
</tbody></table></div><br />
EOD;

  return $string;
}
?>
