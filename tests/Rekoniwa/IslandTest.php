<?php
declare(strict_types=1);

require_once __DIR__ . "/../../config.php";

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

$init = new \Hakoniwa\Init;

$test_data = [
    "name" => "isl_name",
    "owner_name" => "owner_name",
    "num_monster" => 0,
    "num_port" => 1,
    "ships" => [],
    "id" => Uuid::uuid4(),
    "start_turn" => 1,
    "is_battlefield" => false,
    "is_keep" => false,
    "prizes" => [],
    "absent" => 0,
    "comment" => "comment_note",
    "comment_date_turn" => 1,
    "point" => 100,
    "point_priv" => null,
    "satelites" => [],
    "zins" => [],
    "items" => [],
    "money" => 1000,
    "money_priv" => null,
    "num_lottery" => 0,
    "food" => 1000,
    "food_priv" => null,
    "population" => 1000,
    "population_priv" => null,
    "area" => 20,
    "population_of" => [
        "farmer" => 10,
        "engineer" => 10,
        "salesman" => 10,
        "miner" => 10,
        "power_plant" => 10,
    ],
    "num_defeat_monster" => 0,
    "millitary_force_lv" => 0,
    "num_launchable_missile" => 0,
    "weather" => 1,
    "soccer" => [
        "win" => 0,
        "lose" => 0,
        "draw" => 0,
        "atk" => 0,
        "def" => 0,
        "gotten_point" => 0,
        "losen_point" => 0
    ],
];

final class IslandTest extends TestCase
{
    /**
     * @dataProvider dpIncome
     */
    public function testIncome($modify, $expected): void
    {
        global $test_data;

        $island = new \Rekoniwa\Island(array_replace($test_data, $modify));
        // $this->assertSame(array_replace($test_data, $expected), $island->income());
        $this->assertSame(array_replace($test_data, $expected), $test_data);
    }

    public function dpIncome()
    {
        yield "test" => [[], ["money" => 100]];
    }
}
