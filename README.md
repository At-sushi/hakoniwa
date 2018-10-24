# Re:箱庭諸島

**[テストプレイできます](https://hakoniwa.cgi-game-preservations.org/)**

<strong style="color:wine;">本プログラムを利用してサーバーを立ち上げる際は下記[「利用条件」](#%E5%88%A9%E7%94%A8%E6%9D%A1%E4%BB%B6)をお読みの上、同意ください。</strong>

## 概要

「[箱庭諸島 S.E ver23_r09](http://hakoniwa.symphonic-net.com/)」、および「[Hakoniwa R.A.](http://www5b.biglobe.ne.jp/~k-e-i/)」、「箱庭諸島 海戦」のいいとこどりをしようという小学生じみた発想をそのままやってしまおうという魂胆のなんか面倒なやつ

## 目的

* モダン環境で遊びたい
	* PHPさんもバージョン7.xに入ってからめきめき良さが増えてるので、使ってあげないと損
	* そもそもPHPやめたらみたいな話もあるけど、とりあえずそれはそれ
* なんかめっちゃバグある
* スマートフォン対応
	* 箱庭諸島の最盛期にそんなものは普及してなかった
* クライアントサイドで出来る処理はクライアントサイドにお任せしたい
	* 基本的なバリデーションとか
	* とはいえ結局サーバ側でし直すんですけども
* 今流行りのMV\*とか勉強したい

### 方針

* 気になったものから適宜修正。ホットフィックスは最優先。
* ひとまずPHPをクリーンアップする作業を中心に行う。
	* ちまちまとSPAっぽく作り替えたい所存
	* いちいち全操作に対してページ遷移を発生させるのはしんどい

## テスト環境

* Nginx 1.9
* PHP 7.1
	* 7.3のstableが出たら更新する予定

## 利用条件

以下をすべて守ってください。

* ユーザーが簡単にアクセスできる位置（e.g. 各ページの最下部）に、このページ（ https://github.com/sotalbireo/hakoniwa ）へのリンクを明記すること。
* ライセンス「GNU Affero General Public License v3.0 (GNU AGPL v3, Affero GPL v3.0)」に準拠すること。（参考：[「たくさんあるオープンソースライセンスのそれぞれの特徴のまとめ | コリス」](https://coliss.com/articles/build-websites/operation/work/choose-a-license-by-github.html#h210)）
* ゲームのトップディレクトリに本リポジトリ付属の「LICENSE」ファイルを必ず置くこと

## はうつーぷれい

1. **前提環境**：
	* HTMLサーバが動いていること
	* PHPが動作すること（バージョン7以降が必須）
	* "Composer"がインストール済みであること（PHPのパッケージマネージャ）
	* "Nodejs"、"npm"がインストール済みであること（主に開発中タスクランナーとして利用しています）
1. 任意のディレクトリ（`/var/www/html`とか）にclone
1. コンソールから`npm install`
1. `/hako-init-default.php`を参考にして、`/hako-init.php`をお好みに設定
1. ブラウザでトップディレクトリを開く
1. 指示に従い管理パスワード、ゲームデータを設定
1. (ﾟдﾟ)ｳﾏｰ

## はうつーあっぷでーと

1. **前提条件**：
	*　「はうつーぷれい」の前提条件をすべて満たすこと
	* "git"がインストール済みであること（アップデート処理中に利用されます）
	* 現在利用中のバージョンと、アップデートしたいバージョンのメジャーバージョン（バージョン表記の最初の`.`までの数字）が等しいこと
	* 現在利用中のバージョンが、アップデートしたいバージョンより確実に古いこと

## 変更点

* **バグフィックス**
* **最適化**
	* そこまでガッツリとはやらない
* **レガシー対応の削除**
	* 各種モダンウェブ規格で廃止される要素の削除
	* IEやNetscapeなどレガシーブラウザ固有の処理・対応を削除
	* 文字コード変換モジュール（jcode.phps）の廃止
* **モジュール化**
	* ブロック遊びをするようにゲームシステムをアレンジできるように
