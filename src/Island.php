<?php

declare(strict_types=1);

namespace Rekoniwa;

require_once __DIR__."/../config.php";

/**
 * Island data container
 * @copyright 2018 sotalbireo, CGI Game Preservations.
 */
final class Island
{
    private const RATE_FOOD_PER_FARMER = 10;
    private const RATE_MONEY_PER_INDUSTRY = 20;
    private const RATE_MONEY_PER_COMMERCE = 20;


    /**
     * 島名
     * @var string
     */
    private $name;
    private $owner_name;
    private $num_monster;
    private $num_port;
    private $ships;
    private $id;
    private $start_turn;
    private $is_battlefield;
    private $is_keep;
    private $prizes;
    private $absent;
    private $comment;
    private $comment_date_turn;
    private $password;
    private $point;
    private $point_priv;
    private $satelites;
    private $zins;
    private $items;
    private $money;
    private $money_priv;
    private $num_lottery;
    private $food;
    private $food_priv;
    /** @var integer 人口 */
    private $population;
    private $population_priv;
    private $area;
    private $population_of;
    private $num_defeat_monster;
    private $millitary_force_lv;
    private $num_launchable_missile;
    private $weather;
    private $soccer;



    public function __construct($raw)
    {
        $prop_list = array_keys(get_class_vars(__CLASS__));
        foreach ($raw as $k => $v) {
            if (!in_array($k, $prop_list)) {
                continue;
            }

        }
    }

    public function getIslandDataFromLegacyDB(int $ordinal)
    {
        $init = new \Hakoniwa\Init;
        $hakojimadat_offset = 4;
        $hakojimadat_eachsize = 18;
        $_base = $hakojimadat_offset + ($hakojimadat_eachsize * $ordinal);

        function csv2mixedArr(string $csv, $pad = ""): array
        {
            return array_pad(
                explode(",", $csv),
                mb_substr_count($csv, ",") + 1,
                $pad
            );
        }
        function csv2intArr(string $csv, $pad = 0): array
        {
            return array_map(
                "intval",
                array_pad(
                    explode(",", $csv),
                    mb_substr_count($csv, ",") + 1,
                    $pad
                )
            );
        }

        $hakojimadat = file(ROOT.DS.$init->dirName.DS."hakojima.dat", FILE_IGNORE_NEW_LINES);

        [
            $this->name, $this->owner_name,
            $this->num_monster, $this->num_port,
            $this->ships[0], $this->ships[1],
            $this->ships[2], $this->ships[3],
            $this->ships[4], $this->ships[5],
            $this->ships[6], $this->ships[7],
            $this->ships[8], $this->ships[9],
            $this->ships[10], $this->ships[11],
            $this->ships[12], $this->ships[13],
            $this->ships[14]
        ] = csv2mixedArr($hakojimadat[$_base]);
        [
            $this->id, $this->start_turn,
            $this->is_battlefield, $this->is_keep
        ] = csv2intArr($hakojimadat[$_base + 1]);
        $this->prizes = trim($hakojimadat[$_base + 2]);
        $this->absent = intval(trim($hakojimadat[$_base + 3]), 10);
        [
            $this->comment, $this->comment_date_turn
        ] = csv2mixedArr($hakojimadat[$_base + 4]);
        $this->password = trim($hakojimadat[$_base + 5]);
        [
            $this->point,
            $this->point_priv
        ] = csv2intArr($hakojimadat[$_base + 6]);
        $this->satelites = csv2intArr($hakojimadat[$_base + 7]);
        $this->zins = csv2intArr($hakojimadat[$_base + 8]);
        $this->items = csv2mixedArr($hakojimadat[$_base + 9], null);
        [
            $this->money,
            $this->num_lottery,
            $this->money_priv
        ] = csv2intArr($hakojimadat[$_base + 10]);
        [
            $this->food,
            $this->food_priv
        ] = csv2intArr($hakojimadat[$_base + 11]);
        [
            $this->population,
            $this->population_priv
        ] = csv2intArr($hakojimadat[$_base + 12]);
        $this->area = trim($hakojimadat[$_base + 13]);
        [
            $this->population_of["agric"],
            $this->population_of["industry"],
            $this->population_of["commerce"],
            $this->population_of["mining"],
            $this->population_of["elec"]
        ] = csv2intArr($hakojimadat[$_base + 14]);
        [
            $this->num_defeat_monster,
            $this->millitary_force_lv,
            $this->num_launchable_missile
        ] = csv2intArr($hakojimadat[$_base + 15]);
        $this->weather = intval(trim($hakojimadat[$_base + 16]), 10);
        [
            $this->soccer["point"],
            $this->soccer["match"],
            $this->soccer["won"],
            $this->soccer["lose"],
            $this->soccer["draw"],
            $this->soccer["atk"],
            $this->soccer["def"],
            $this->soccer["score"],
            $this->soccer["got"],
            $this->soccer["stole"]
        ] = csv2intArr($hakojimadat[$_base + 17]);
    }



    public function getPublicDataJson(): string
    {
        $list = [
            "name",
            "owner_name",
            "num_monster",
            "num_port",
            "ships",
            "id",
            "start_turn",
            "is_battlefield",
            "is_keep",
            "prizes",
            "absent",
            "comment",
            "comment_date_turn",
            "point",
            "point_priv",
            "satelites",
            "zins",
            "items",
            "money",
            "money_priv",
            "num_lottery",
            "food",
            "food_priv",
            "population",
            "population_priv",
            "area",
            "population_of",
            "num_defeat_monster",
            "millitary_force_lv",
            "num_launchable_missile",
            "weather",
            "soccer",
        ];

        $preJsonArray = [];

        foreach ($list as $v) {
            $preJsonArray[$v] = $this->$v;
        }

        $json = json_encode($preJsonArray, JSON_UNESCAPED_UNICODE);

        return $json !== false ? $json : "{}";
    }


    /**
     * 収入
     * @return void
     */
    public function income(): void
    {
        if ($this->is_battlefield) {
            $this->money = \Rekoniwa::MAX_MONEY;
            $this->food  = \Rekoniwa::MAX_FOOD;

            return;
        }

        /** @var integer 農業専従者（最優先雇用） */
        $farmer = min($this->population, $this->population_of['farmer']);
        /** @var integer 商工業従事者 */
        $worker = $this->population - $farmer;
    }

    public function expense(): void
    {
    }
}
