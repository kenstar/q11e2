## q11e2プラグイン -- ks

since 2006-03-11

### ニュース
* Version KCAPTCHA 2.30 リリース--[KCAPTCHA対応]
  最近SPAMが多いため、根本的な対応を考えておりました。KCAPTCHAを利用して、SPAMを受け付けないように修正しました。(元としているバージョンは、2.20になります。)q11e2をq11e2_kcaptchaに書き換えて使用してください。

### バリエーション
* Version 2.30 q11e2_kcaptcha.inc.php.230 [kcaptcha対応版] &new{2008-01-22 (火) 23:50:00};
* Version 2.20 q11e2.inc.php.220 [２重書込み防止機能付] &new{2007-04-14 (土) 01:00:00};

### 概要
* questionnaireのプラグインを進化させました。questionnaire2だと、前のプラグインと名前が衝突して問題が。。なので、名前を変更しました。ちなみに、uestionnairの文字数が11文字だったので、このようなネーミングです。

### 特徴・注意事項
* Version 2.30
 * SPAMが多いため、KCAPTCHAに対応しました。q11e2をq11e2_kcaptchaに書き換えて使用してください。pukiwikiの方にも設定が必要ですが、設定方法については[pcomment_kcaptcha.inc.php](http://pukiwiki.sourceforge.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3%2Fpcomment_kcaptcha.inc.php)と同様の設定方法ですので、そちらを参照してください。
* Version 2.20
 * 同じ回答者が連続して回答した場合、二回目以降の回答は無視されます。
 * COMMENT_NAME_FORMATの修正を行うと正常に動かない可能性があります。その場合は旧バージョン(Ver2.10)を使用してください。（ちなみに\[\[]]を取る程度では問題ありません。newを外したり、newの前に新しい変化する要素を追加すると予想外の動きをする可能性があります）
* Version 2.10
 * listbox2を使用して、ページの編集をしなくても状態を変えられるように変更。

### 変更履歴
* Version 2.30 &new{2008-01-22 (火) 23:50:00};
 * KCAPTCHAを使用して、SPAM防止。q11e2をq11e2_kcaptchaに書き換えて使用。
* Version 2.20 &new{2007-04-14 (土) 01:00:00};
 * 同一回答者名による連続回答を防止（2回目以降の回答を無視する）
 * JavaScriptによる、Submitボタンの二重押し防止機能追加
 * コメント、回答者名その他の回答内容から「|」を除去
* Version 2.10 &new{2006-04-03 23:33:43};
 * listbox対応 (Special Thanks to io)。使用する場合は[listbox2](http://pukiwiki.sourceforge.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3%2Flistbox.inc.php)のプラグインが必要。

### その他
* SiteDev+AT対応状況
 * [SiteDev+AT](http://www.a-kojima.net/set/)を使用されているユーザさんより連絡があり、[SiteDev+AT](http://www.a-kojima.net/set/)でも使用できるようです。

* pukiwiki-1.4.7対応状況
 * pukiwiki-1.4.7でも動作することを確認しました。おそらく、1.4.xで大きな仕様変更が入らないかぎり動作するものと思われます。ただし、1.4.6から同一プラグインの呼び出しが制限され、デフォルト設定のままでは768回に制限されます。よって、''アンケートの回答行が768行以上になると、閲覧が行えなくなる可能性があります。''アンケートページを分けるなどの対処をお願いします。(2007/1/30)

### 使用方法
* ラジオボタンも使える。「::RADIO::」を最初に記述。
* リストも扱える。「::LIST::」を最初に記述。
* プルダウンも扱える。「::PULLDOWN::」を最初に記述。
* チェックボックスを扱える。そのまま記述。
* フリーテキストを扱える。「::TEXT::」を記述。先頭である必要はないですが、チェックボックスとしか併用できません。
* 最初から選択させるためには、「CHECKED:」を要素の最初に記述。
* 書き込んだリストの中でチェックボックスを使えるようなカスタマイズ。[listbox2](http://pukiwiki.sourceforge.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3%2Flistbox.inc.php)のプラグインが必要になります。「::LISTOUT::」を要素の最初に記述。(special thanks to io)

### サンプル
```
 #q11e2(ラジオボタン[::RADIO::##1##2##CHECKED:3(選択されてる)##4],チェックボックス[i##ii##iii],リスト[::LIST::##a##CHECKED:b(選択されてる)##c##],プルダウン[::PULLDOWN::##A##B##C],テキスト[特になし##::TEXT::],状態[::LISTOUT::##状態A##状態B])
 #q11e2_kcaptcha(ラジオボタン[::RADIO::##1##2##CHECKED:3(選択されてる)##4],チェックボックス[i##ii##iii],リスト[::LIST::##a##CHECKED:b(選択されてる)##c##],プルダウン[::PULLDOWN::##A##B##C],テキスト[特になし##::TEXT::],状態[::LISTOUT::##状態A##状態B])
```
