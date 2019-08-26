<?php

declare(strict_types=1);

namespace Hakoniwa\Model;

require_once __DIR__."/../../config.php";
use \Hakoniwa\Helper\Util_alliance as Util;

/**
 * Alliances Model
 * @author @Sotalbireo
 */
class Alliance
{
    private const MAX_ALLIANCE_ID = 200;


    /**
     * 同盟登録確認
     * @param  [type] $game [description]
     * @param  [type] $data [description]
     * @param  bool   $dry_run  jsonを返さずboolだけ返す
     * @return [type]       [description]
     */
    public function confirm($game, $data, $dry_run = false)
    {
        global $init;

        $player_ID = (int)$data["Whoami"];
        $password = base64_decode($data["Password"] ?? "", true);
        $island = $game->islands[(int)$game->idToNumber[$player_ID]];
        $candidate = [
            "name"  => htmlspecialchars($data["AllianceName"]),
            "sign"  => $data["AllianceSign"],
            "color" => $data["AllianceColor"]
        ];

        $admin_mode = \Util::checkPassword("", $password);
        $checked = [
            "id" =>    ["status" => true, "message" => ""],
            "pass" =>  ["status" => true, "message" => ""],
            "name" =>  ["status" => true, "message" => ""],
            "sign" =>  ["status" => true, "message" => ""],
            "color" => ["status" => true, "message" => ""],
            "other" => ["status" => true, "messages" => []]
        ];
        $is_valid = true;

        // パスワードチェック
        if ($admin_mode) {/* thru */
        } elseif ($password === "") {
            $checked["pass"]["status"] = false;
            $checked["pass"]["message"] = "no_password";
            $is_valid = false;
        } elseif (!\Util::checkPassword($island["password"], $password)) {
            $checked["pass"]["status"] = false;
            $checked["pass"]["message"] = "wrong_password";
            $is_valid = false;
        }

        // 同盟名の重複はNG
        if ($this->is_duplicate_name($game, $candidate["name"])) {
            $checked["name"]["status"] = false;
            $checked["name"]["message"] = "duplicate_name";
            $is_valid = false;
        }

        // 同盟徽章の重複はNG
        if ($this->is_duplicate_sign($game, $candidate["sign"])) {
            $checked["sign"]["status"] = false;
            $checked["sign"]["message"] = "duplicate_sign";
            $is_valid = false;
        }

        function is_match_in_array($string, $array)
        {
            foreach ($array as $v) {
                if (mb_strpos($string, $v) !== false) {
                    return true;
                }
            }

            return false;
        }
        // 禁止語句利用のチェック
        if ($candidate["name"] === ""
            || preg_match($init->regex_denying_name_words, $candidate["name"])
            || is_match_in_array($candidate["name"], $init->denying_name_words)) {
            $checked["name"]["status"] = false;
            $checked["name"]["message"] = "illegal_name";
            $is_valid = false;
        }

        // 同盟色のバリデーション
        if (!preg_match("/^#[0-9a-fA-F]{6}$/", $candidate["color"])) {
            $checked["color"]["status"] = false;
            $checked["color"]["message"] = "illegal_color";
            $is_valid = false;
        }

        // 同盟主が他の同盟に参加していないかの確認
        // [NOTE] 同盟主は他の同盟に参加できない仕様のため、すでに別の同盟に参加している時点で偽
        if (count($island["allyId"]) > 0) {
            $checked["other"]["status"] = false;
            $checked["other"]["messages"][] = "master_can_not_join_other_alliances";
            $is_valid = false;
        }

        // 同盟結成資金の確認
        if ($admin_mode) {/* thru */
        } elseif ($island["money"] < $init->costMakeAlly) {
            $checked["other"]["status"] = false;
            $checked["other"]["messages"][] = "not_enough_money";
            $is_valid = false;
        }

        // [NOTE]
        // : 設定から同盟結成権が GM のみになっていた場合、ほかの警告出してもしょうがないので全部上書き
        if ($init->allyUse === 2 && !$admin_mode) {
            $checked["id"]["status"] = true;
            $checked["pass"]["status"] = true;
            $checked["name"]["status"] = true;
            $checked["sign"]["status"] = true;
            $checked["color"]["status"] = true;
            $checked["other"]["status"] = false;
            $checked["other"]["messages"] = ["admin_only"];
            $is_valid = false;
        }

        if (!$dry_run) {
            header("Content-Type:application/json;charset=utf-8");
            echo json_encode($checked);
        } else {
            return $is_valid;
        }
    }



    /**
     * 結成
     * @param  [type] $game ゲームデータ
     * @param  [type] $data プレイヤー入力
     * @return void
     */
    public function establish(&$game, $data): void
    {
        global $init;

        $player_ID = (int)$data['Whoami'];
        $password = base64_decode($data['Password'] ?? '', true);
        $island = $game->islands[(int)$game->idToNumber[$player_ID]];
        $new_alliance = [
            'name'      => htmlspecialchars($data['AllianceName']),
            'sign'      => (int)$data['AllianceSign'],
            'sign_str'  => $init->allyMark[(int)$data['AllianceSign']],
            'color'     => $data['AllianceColor']
        ];

        $alliance = [
            'id'         => -1,
            'oName'      => '',
            'password'   => '',
            'number'     => 1,
            'score'      => -1,
            'occupation' => 0,
            'memberId'   => [],
            'ext'        => [0],
            'name'       => '',
            'mark'       => '',
            'color'      => ''
        ];

        $admin_mode = \Util::checkPassword("", $password);

        $alliance_int = $game->allyNumber;

        $alliance['oName']       = $island['name'].$init->nameSuffix;
        $alliance['password']    = $island['password'];
        $alliance['memberId'][0] = $player_ID;
        $alliance['score']       = $island['pop'];
        $alliance['id']    = $player_ID;
        $alliance['name']  = $new_alliance['name'];
        $alliance['mark']  = $new_alliance['sign_str'];
        $alliance['color'] = $new_alliance['color'];

        $island['allyId']  = $alliance_int;
        $island['money']  -= !$admin_mode ? $init->costMakeAlly : 0;

        $game->idToAllyNumber[$alliance_int] = $alliance_int;
        $game->allyNumber++;


        // データ書き出し
        $game->ally[$alliance_int] = $alliance;
        $game->islands[$player_ID] = $island;
        $this->calculates_share($game);
        Util::allySort($game);
        $game->writeAllyFile();
    }

    /**
     * 変更
     * @param  [type] $hako [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function update($hako, $data)
    {
        global $init;

        $current_ID = $data['ISLANDID'];
        $allyID = $data['ALLYID'] ?? "";
        $current_alliance_number = $data['ALLYNUMBER'] ?? "";
        $allyName = htmlspecialchars($data['ALLYNAME']);
        $allyMark = $data['MARK'];
        $allyColor = $data['colorCode'];
        $admin_mode = 0;

        // パスワードチェック
        $data['OLDPASS'] = $data['OLDPASS'] ?? "";
        if (AllyUtil::checkPassword("", $data['OLDPASS'])) {
            $admin_mode = 1;
            if ($allyID > 200) {
                $max = $allyID;
                if ($hako->allyNumber) {
                    for ($i=0; $i < count($hako->ally); $i++) {
                        if ($max <= $hako->ally[$i]['id']) {
                            $max = $hako->ally[$i]['id'] + 1;
                        }
                    }
                }
                $current_ID = $max;
            } else {
                $current_ID = $hako->ally[$current_alliance_number]['id'];
            }
        }
        if (!$init->allyUse && !$admin_mode) {
            \HakoError::newAllyForbbiden();

            return;
        }
        // 同盟名があるかチェック
        if ($allyName == '') {
            \HakoError::newAllyNoName();

            return;
        }
        // 同盟名が正当かチェック
        if (preg_match("/[,\?\(\)\<\>\$]|^無人|^沈没$/", $allyName)) {
            \HakoError::newIslandBadName();

            return;
        }
        // 名前の重複チェック
        $current_number = $hako->idToNumber[$current_ID];
        if (!($admin_mode && ($allyID == '') && ($allyID < 200)) &&
            ((AllyUtil::nameToNumber($hako, $allyName) != -1) ||
            ((AllyUtil::aNameToId($hako, $allyName) != -1) && (AllyUtil::aNameToId($hako, $allyName) != $current_ID)))) {
            HakoError::newAllyAlready();

            return;
        }
        // マークの重複チェック
        if (!($admin_mode && ($allyID == '') && ($allyID < 200)) &&
            ((AllyUtil::aMarkToId($hako, $allyMark) != -1) && (AllyUtil::aMarkToId($hako, $allyMark) != $current_ID))) {
            HakoError::markAllyAlready();

            return;
        }
        // passwordの判定
        $island = $hako->islands[$current_number];
        if (!$admin_mode && !AllyUtil::checkPassword($island['password'], $data['PASSWORD'])) {
            HakoError::wrongPassword();

            return;
        }
        // 結成資金不足判定
        if (!$admin_mode && $island['money'] < $init->costMakeAlly) {
            HakoError::noMoney();

            return;
        }
        $n = $hako->idToAllyNumber[$current_ID] ?? '';
        if ($n !== '') {
            if ($admin_mode && ($allyID != '') && ($allyID < 200)) {
                $alliance_member = $hako->ally[$n]['memberId'];
                $aIsland = $hako->islands[$hako->idToNumber[$allyID]];
                $flag = 0;
                foreach ($alliance_member as $id) {
                    if ($id == $allyID) {
                        $flag = 1;

                        break;
                    }
                }
                if (!$flag) {
                    if ($aIsland['allyId'][0] == '') {
                        $flag = 2;
                    }
                }
                if (!$flag) {
                    echo "変更できません。\n";

                    return;
                }
                $hako->ally[$n]['id']    = $allyID;
                $hako->ally[$n]['oName'] = $aIsland['name'];
                if ($flag == 2) {
                    $hako->ally[$n]['password'] = $aIsland['password'];
                    $hako->ally[$n]['score']    = $aIsland['pop'];
                    $hako->ally[$n]['number'] ++;
                    array_push($hako->ally[$n]['memberId'], $aIsland['id']);
                    array_push($aIsland['allyId'], $aIsland['id']);
                }
            } else {
                // すでに結成ずみなら変更
            }
        } else {
            // 他の島の同盟に入っている場合は、結成できない
            $flag = 0;
            for ($i = 0; $i < $hako->allyNumber; $i++) {
                $alliance_member = $hako->ally[$i]['memberId'];
                foreach ($alliance_member as $id) {
                    if ($id == $current_ID) {
                        $flag = 1;

                        break;
                    }
                }
                if ($flag) {
                    break;
                }
            }
            if ($flag) {
                HakoError::otherAlready();

                return;
            }
            if (($init->allyUse == 2) && !$admin_mode && !AllyUtil::checkPassword("", $data['PASSWORD'])) {
                HakoError::newAllyForbbiden();

                return;
            }
            // 新規
            $n = $hako->allyNumber;
            $hako->ally[$n]['id'] = $current_ID;
            $memberId = [];
            if ($allyID < 200) {
                $hako->ally[$n]['oName']    = $island['name'].$init->nameSuffix;
                $hako->ally[$n]['password'] = $island['password'];
                $hako->ally[$n]['number']   = 1;
                $memberId[0]                = $current_ID;
                $hako->ally[$n]['score']    = $island['pop'];
            } else {
                $hako->ally[$n]['oName']    = '';
                $hako->ally[$n]['password'] = AllyUtil::encode($data['PASSWORD']);
                $hako->ally[$n]['number']   = 0;
                $hako->ally[$n]['score']    = 0;
            }
            $hako->ally[$n]['occupation']   = 0;
            $hako->ally[$n]['memberId']     = $memberId;
            $island['allyId']               = $memberId;
            $ext = [0];
            $hako->ally[$n]['ext']          = $ext;
            $hako->idToAllyNumber[$current_ID] = $n;
            $hako->allyNumber++;
        }

        // 同盟の各種の値を設定
        $hako->ally[$n]['name']  = $allyName;
        $hako->ally[$n]['mark']  = $allyMark;
        $hako->ally[$n]['color'] = $allyColor;

        // 費用をいただく
        $island['money'] -= !$admin_mode ? $init->costMakeAlly : 0;

        // データ格納先へ
        $hako->islands[$current_number] = $island;

        // データ書き出し
        AllyUtil::calculates_share($hako);
        AllyUtil::allySort($hako);
        $hako->writeAllyFile();

        // トップへ
        $html = new HtmlAlly();
        $html->allyTop($hako, $data);
    }

    /**
     * 参加する
     * @param         $game ゲーム総合データ
     * @param         $data プレイヤー入力
     * @return [type]       [description]
     */
    public function join(&$game, $data)
    {
        global $init;
        $player_ID = (int)$data["Whoami"] ?? -1;
        $player = $game->islands[(int)$game->idToNumber[$player_ID]];
        $password = base64_decode($data["Pwd"] ?? "", true);
        $join_to = (int)$data["JoinTo"] ?? -1;
        assert($join_to !== -1);
        $alliance = $game->ally[$game->idToAllyNumber[$join_to]];

        $status = [
            "status" => "true",
            "errors" => []
        ];

        // Administrator => thru;
        // Wrong player password => false;
        if (\Util::checkPassword("", $password)) {
        } elseif (!\Util::checkPassword($player["password"], $password)) {
            $status["status"] = "false";
            $status["errors"][] = "wrong_password";
        }

        // 複数加盟チェック
        if ($init->allyJoinOne && count($player['allyId']) > 0) {
            $status["status"] = "false";
            $status["errors"][] = "you_can_only_join_alliance_only_one";
        }

        // 同盟主は他の同盟に所属できない
        if (array_key_exists($player_ID, $game->idToAllyNumber)) {
            $status["status"] = "false";
            $status["errors"][] = "master_can_not_join_other_alliances";
        }

        // 予算不足
        assert($init->costJoinAlly >= 0);
        if ((int)$player["money"] < $init->costJoinAlly) {
            $status["status"] = "false";
            $status["errors"][] = "budjet_shortage";
        }

        // pre-check
        if ($data["mode"] === "prejoin") {
            dump_logging($status);

            return $status;
        } elseif ($status["status"] === "false") {
            return $status;
        }

        $player["allyId"][] = $alliance["id"];
        // $alliance["score"] += $player["pop"];
        $alliance["number"]++;
        $player["money"] -= $init->costJoinAlly;

        $alliance_member = $alliance['memberId'];
        $alliance_member[] = $player_ID;
        $alliance["memberId"] = $alliance_member;

        // データ更新
        $game->ally[$game->idToAllyNumber[$join_to]] = $alliance;
        $game->islands[(int)$game->idToNumber[$player_ID]] = $player;
        $this->calculates_share($game);
        Util::allySort($game);
        $game->writeAllyFile();

        return $status;
    }

    /**
     * 脱退
     * @param         $game ゲーム総合データ
     * @param         $data プレイヤー入力
     * @return [type]       [description]
     */
    public function withdrawal(&$game, $data)
    {
        /**
         * -[x] 入力バリデーション
         * -[] 脱退可能か検証
         *   +[] 同盟に所属しているか
         *   +[] 同盟主じゃないか
         * -[] 脱退処理
         * -[] DB書き戻し
         */
        function get_player_data(array $game, int $id)
        {
            if (!array_key_exists($id, $game->idToNumber)) {
                return false;
            }
            $num = (int)$game->idToNumber[$id];
            if (!array_key_exists($num, $game->islands)) {
                return false;
            }

            return $game->islands[$num];
        }

        function get_alliance_data(array $game, int $id)
        {
            if (!array_key_exists($id, $game->idToAllyNumber)) {
                return false;
            }
            $num = (int)$game->idToAllyNumber[$id];
            if (!array_key_exists($num, $game->ally)) {
                return false;
            }

            return $game->ally[$num];
        }

        global $init;
        $player_ID = (int)$data["Whoami"] ?? -1;
        $player = get_player_data($game, $player_ID);
        $password = base64_decode($data["Pwd"] ?? "", true);
        $withdrawal_from = (int)$data["Alliance"] ?? -1;
        $alliance = get_alliance_data($game, $withdrawal_from);

        if (!$player || !$alliance) {
            return false;
        }
    }

    /**
     * 同盟の解散（削除）
     * @param         $hako ゲーム総合データ
     * @param         $data プレイヤー入力
     * @return [type]       [description]
     */
    public function delete_alliance($hako, $data)
    {
        global $init;

        $current_id = $data['ISLANDID'];
        $current_alliance_number = $data['ALLYNUMBER'];
        $current_number = $hako->idToNumber[$current_id];
        $island = $hako->islands[$current_number];
        $n = $hako->idToAllyNumber[$current_id];
        $admin_mode = 0;

        // 認証
        if (Util::checkPassword("", ($data['OLDPASS'] ?? ''))) {
            // 管理者
            $n = $current_alliance_number;
            $current_id = $hako->ally[$n]['id'];
            $admin_mode = 1;
        } else {
            // 一般

            // 島パスワード
            if (!Util::checkPassword($island['password'], $data['PASSWORD'])) {
                HakoError::wrongPassword();

                return;
            }
            // 同盟パスワード
            if (!Util::checkPassword($hako->ally[$n]['password'], $data['PASSWORD'])) {
                HakoError::wrongPassword();

                return;
            }
            // 念のためIDもチェック
            if ($hako->ally[$n]['id'] != $current_id) {
                HakoError::wrongAlly();

                return;
            }
        }

        $alliance_member = $hako->ally[$n]['memberId'];

        // 管理者 かつ （同盟に在籍者がいる または 同盟がない）
        if ($admin_mode && (($alliance_member[0] != '') || ($n == ''))) {
            echo "削除できません。\n";

            return;
        }

        foreach ($alliance_member as $id) {
            $island = $hako->islands[$hako->idToNumber[$id]];
            $new_id = [];
            foreach ($island['allyId'] as $id) {
                if ($id != $current_id) {
                    array_push($new_id, $id);
                }
            }
            $island['allyId'] = $new_id;
        }
        $hako->ally[$n]['dead'] = 1;
        $hako->idToAllyNumber[$current_id] = '';
        $hako->allyNumber--;

        // データ格納先へ
        $hako->islands[$current_number] = $island;

        // データ書き出し
        Util::calculates_share($hako);
        Util::allySort($hako);
        $hako->writeAllyFile();

        // トップへ
        $html = new HtmlAlly();
        $html->allyTop($hako, $data);
    }

    /**
     * 同盟主コメント更新
     * @param       $hako ゲーム総合データ
     * @param       $data プレイヤー入力
     * @return bool       処理の成否
     */
    public function allyPactMain($hako, $data)
    {
        $ally = $hako->ally[$hako->idToAllyNumber[$data['ALLYID']]];

        if (!Util::checkPassword($ally['password'], $data['Allypact'])) {
            // Authentication failed
            // [TODO]: Viewに移す
            HakoError::wrongPassword();

            return false;
        }
        $ally['comment'] = Util::htmlEscape($data['ALLYCOMMENT']);
        $ally['title']   = Util::htmlEscape($data['ALLYTITLE']);
        $ally['message'] = Util::htmlEscape($data['ALLYMESSAGE'], true);

        // データ書き出し
        $hako->ally[$hako->idToAllyNumber[$data['ALLYID']]] = $ally;
        $hako->writeAllyFile();

        // 変更成功
        // [TODO]: Viewに移す
        Success::allyPactOK($ally['name']);

        return true;
    }

    /**
     * 同盟関連データの再計算
     * @param  [type] &$game ゲーム総合データ
     * @return bool          更新があった場合true
     */
    public function calculation(&$game)
    {
        $calc1 = $this->delete_alliance_nobody_managed($game);
        $calc2 = $this->update_members($game);
        $calc3 = $this->update_alliances_score($game);

        if ($calc1 || $calc2 || $calc3) {
            // データ書き出し
            try {
                $this->calculates_share($game);
                Util::allySort($game);
                $game->writeAllyFile();
            } catch (Exception $e) {
                dump($e);

                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * 同盟の名前と記章が既存のものと重複しないか
     * @param          $game
     * @param  string  $name 同盟の名前
     * @param  integer $sign 同盟の記章
     * @return bool          しなけりゃtrue
     */
    private function check_duplicate($game, $name, $sign)
    {
        $current_number = $game->idToNumber[$current_ID];
        if ((Util::nameToNumber($game, $name) != -1)
            ||
            (
                (Util::aNameToId($game, $name) != -1)
                && (Util::aNameToId($game, $name) != $current_ID)
            )
        ) {
            HakoError::newAllyAlready();

            return;
        }

        if (Util::aMarkToId($game, $sign) != -1
            && Util::aMarkToId($game, $sign) != $current_ID
        ) {
            HakoError::markAllyAlready();

            return;
        }
    }

    private function is_duplicate_sign($game, $sign)
    {
        return Util::aMarkToId($game, $sign) !== -1;
    }
    private function is_duplicate_name($game, $name)
    {
        return Util::aNameToId($game, $name) !== -1;
    }

    /**
     * 同盟主として登録のある島が存在しないとき、当該の同盟を削除する
     * @param       &$game ゲーム総合データ
     * @return bool        削除処理が走った場合true
     */
    private function delete_alliance_nobody_managed(&$game)
    {
        $count = 0;

        for ($i=0; $i<$game->allyNumber; $i++) {
            $owner_id = $game->ally[$i]['id'];

            if (($game->idToNumber[$owner_id] ?? -1) < 0) {
                unset($game->ally[$i], $game->idToAllyNumber[$owner_id]);

                $game->ally[$i]['dead'] = 1;
                $count++;
            }
        }

        if ($count) {
            $game->allyNumber -= $count;
            if ($game->allyNumber < 0) {
                $game->allyNumber = 0;
            }

            return true;
        }

        return false;
    }

    /**
     * 同盟在籍ユーザの更新
     * @param       &$game ゲーム総合データ
     * @return bool        更新があった場合true
     */
    private function update_members(&$game)
    {
        $flg = false;

        for ($i=0; $i<$game->allyNumber; $i++) {
            $count = 0;
            $members = $game->ally[$i]['memberId'];
            $new_members = [];

            foreach ($members as $id) {
                if ($game->idToNumber[$id] > -1) {
                    array_push($new_members, $id);
                    $count++;
                }
            }

            if ($count != $game->ally[$i]['number']) {
                $game->ally[$i]['memberId'] = $new_members;
                $game->ally[$i]['number'] = $count;
                $flg = true;
            }
        }

        return $flg;
    }

    /**
     * 同盟ごとの所属人口（スコア）更新
     * @param       &$game ゲーム総合データ
     * @return bool        更新があった場合true
     */
    private function update_alliances_score(&$game)
    {
        $flg = false;

        for ($i = 0; $i < $game->allyNumber; $i++) {
            $score = 0;
            $members = $game->ally[$i]['memberId'];

            foreach ($members as $id) {
                $member_island = $game->islands[$game->idToNumber[$id]];
                $score += $member_island['pop'];
            }

            if ($score != $game->ally[$i]['score']) {
                $game->ally[$i]['score'] = $score;
                $flg = true;
            }
        }

        return $flg;
    }

    /**
     * 同盟の占有率の計算
     * @param array &$game ゲームデータ
     */
    private function calculates_share(&$game): void
    {
        $total_score = 0;

        if ((int)$game->allyNumber === 1) {
            $game->ally[0]['occupation'] = 100;

            return;
        }

        for ($i = 0; $i < $game->allyNumber; $i++) {
            $total_score += (int)$game->ally[$i]['score'];
        }

        $total_score = $total_score !== 0 ?: 1;

        for ($i = 0; $i < $game->allyNumber; $i++) {
            $game->ally[$i]['occupation'] = intdiv((int)$game->ally[$i]['score'], $total_score * 100);
        }
    }
}
