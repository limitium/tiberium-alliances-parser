<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Base91.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "CnCApi.php";

class Square
{
    public $_id;
    public $sx;
    public $sy;
    public $marks = array();
    public $alliances = array();
    public $players = array();

    /**
     * @static
     * @param $data
     * @return Square
     */
    public static function decode($data)
    {
        //        "a" - contains all alliances in the sector
        //        "d" - contains all Objects
        //        "i" - is used as the id of the sector
        //        "p" - are the players in this sector
        //        "t" - is the terrain
        //        "u" - are terrain details
        //        "v" - version of the world sector

        $sqare = new self();

        $id = (($data->i & 0xff) | (($data->i >> 8) << 0x10));
        $sqare->_id = $id;
        $sqare->sx = ($id & 0xffff);
        $sqare->sy = ($id >> 0x10);

        $sqare->parseAlliances($data->a);
        $sqare->parsePlayers($data->p);
        $sqare->parseObjects($data->d);

        return $sqare;
    }

    private function parseAlliances($data)
    {
        foreach ($data as $allianceData) {
            $id = Base91::Decode13Bits($allianceData, 0);
            $out = new stdClass();
            $pos = 2;
            $aId = Base91::DecodeFlexInt($allianceData, $pos, $out);
            $pos += $out->size;
            $points = Base91::DecodeFlexInt($allianceData, $pos, $out);
            $pos += $out->size;
            $name = substr($allianceData, $pos);

            $this->alliances[$id] = array(
                '_id' => $aId,
                'name' => $name,
                'points' => $points,
            );
        }
    }

    private function parsePlayers($data)
    {
        foreach ($data as $playerData) {
            $id = Base91::Decode13Bits($playerData, 0);
            $out = new stdClass();
            $pos = 2;
            $pid = Base91::DecodeFlexInt($playerData, $pos, $out);
            $pos += $out->size;
            $points = Base91::DecodeFlexInt($playerData, $pos, $out);
            $pos += $out->size;
            $state = Base91::Decode13Bits($playerData, $pos);
            $faction = (($state >> 1) & 3);
            $alliance = ($state >> 3);

            $pos += 2;
            if (($state & 1) != 0) {
                $peaceStart = Base91::DecodeFlexInt($playerData, $pos, $out);
                $pos += $out->size;
                $peaceDuration = Base91::DecodeFlexInt($playerData, $pos, $out);
                $pos += $out->size;
            }
            else {
                $peaceStart = 0;
                $peaceDuration = 0;
            }
            $name = substr($playerData, $pos);

            $this->players[$id] = array(
                "_id" => $pid,
                "peaceStart" => $peaceStart,
                "peaceDuration" => $peaceDuration,
                "name" => $name,
                "faction" => $faction,
                "alliance" => $alliance,
                "points" => $points,
            );

        }
    }

    private function parseObjects($data)
    {
        foreach ($data as $chunk) {
            $pos = 0;
            $headData = Base91::Decode13Bits($chunk, $pos);
            $x = ($headData & 0x1f);
            $y = (($headData >> 5) & 0x1f);
            $type = ($headData >> 10);

            $pos += 2;
            switch ($type) {
                case 0:
                    //None
                    //Remove objects $this->sx * 32 + x, $this->sy * 32 + y
                    break;
                case 1:
                    //City
                    // +2 because of the earlier decoding of the 13 bits of the Base91
                    $this->marks[] = City::decode($chunk, $pos)->setXY($this->sx * 32 + $x, $this->sy * 32 + $y);
                    break;
                case 2:
                    //NPCBase
//                    $this->marks[] = NPCBase::decode($chunk, $pos)->setXY($this->sx * 32 + $x, $this->sy * 32 + $y);
                    break;
                case 3:
                    //NPCCamp
                    break;
                case 4:
                    //PointOfInterst
                    $this->marks[] = POI::decode($chunk, $pos)->setXY($this->sx * 32 + $x, $this->sy * 32 + $y);
                    break;
                case 5:
                    //NewPlayerSLot
                    break;
                case 7:
                    //Ruin
//                    $this->marks[] = Ruin::decode($chunk, $pos)->setXY($this->sx * 32 + $x, $this->sy * 32 + $y);
                    break;
                default:
                    throw new Exception('Undefined type ' . $type);
            }
        }
    }

}

class Marker
{
    public $x;
    public $y;

    public function setXY($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
        return $this;
    }

    public function __toString()
    {
        print_r($this);
        print_r("\r\n");
    }
}

class Base extends Marker
{
    public $level;
    public $radius;
}

class AliveBase extends Base
{
    public $_id;
    public $isAttacked;
    public $isLocked;
    public $isAlerted;
    public $isDefenseDamaged;
    public $supportAlertStartStep;
    public $supportAlertEndStep;
    public $conditionBuildings;
    public $conditionDefense;
    public $lockdownEndStep;
}

class City extends AliveBase
{
    public $isProtected;
    public $hasCooldown;
    public $hasRecovery;
    public $playerId;
    public $protectionEndStep;
    public $cooldownEndStep;
    public $moveLockdownEndStep;
    public $name;
    public $hasMoveRecovery;

    /**
     * @static
     * @param $details
     * @param $pos
     * @return City
     */
    public static function decode($details, $pos)
    {
        $base = new self();
        $cityData = Base91::Decode32Bits($details, $pos);
        $base->isAttacked = (($cityData & 1) != 0);
        $base->isLocked = ((($cityData >> 1) & 1) != 0);
        $base->isProtected = ((($cityData >> 2) & 1) != 0);
        $base->isAlerted = ((($cityData >> 3) & 1) != 0);
        $base->hasCooldown = ((($cityData >> 4) & 1) != 0);
        $base->hasRecovery = ((($cityData >> 5) & 1) != 0);
        $base->hasMoveRecovery = ((($cityData >> 6) & 1) != 0);
        $base->isDefenseDamaged = ((($cityData >> 7) & 1) != 0);
        $base->level = (($cityData >> 8) & 255);
        $base->radius = (($cityData >> 16) & 15);
        $base->playerId = (($cityData >> 22) & 1023);
        $pos += 5;


        //???
        $out = new stdClass();
        $out->size = 0;
        if ($base->isLocked) {
            $base->lockdownEndStep = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
        }
        if ($base->isProtected) {
            $base->protectionEndStep = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
        }
        if ($base->isAlerted) {
            $base->supportAlertStartStep = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
            $base->supportAlertEndStep = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
        }
        if ($base->hasCooldown) {
            $base->cooldownEndStep = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
        }
        if ($base->hasRecovery) {
            $base->recoveryEndStep = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
            Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
        }
        if ($base->hasMoveRecovery) {
            Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
        }
        $base->conditionBuildings = Base91::DecodeFlexInt($details, $pos, $out);
        $pos += $out->size;
        if ($base->isDefenseDamaged) {
            $base->conditionDefense = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
        }
        else {
            $base->conditionDefense = -1;
        }
        $base->defenceAutoRepairStartStep = Base91::DecodeFlexInt($details, $pos, $out);
        $pos += $out->size;
        $base->_id = Base91::DecodeFlexInt($details, $pos, $out);
        $base->name = substr($details, ($pos + $out->size));
        return $base;
    }


}

class POI extends Marker
{
    public $_id;
    public $level;
    public $type;
    public $OwnerAllianceId;
    public $OwnerAllianceName;

    /**
     * @static
     * @param $details
     * @param $pos
     * @return POI
     */
    public static function decode($details, $pos)
    {
        $poi = new self();
        $poiData = Base91::Decode26Bits($details, $pos);
        $poi->level = $poiData & 255;
        $poi->type = $poiData >> 8 & 7; //ClientLib.Data.WorldSector.WorldObjectPointOfInterest.EPOIType.Defense;
        $pos += 4;
        $out = new stdClass();
        $poi->_id = Base91::DecodeFlexInt($details, $pos, $out);
        $pos += $out->size;
        $poi->OwnerAllianceId = Base91::DecodeFlexInt($details, $pos, $out);
        $pos += $out->size;
        if ($poi->OwnerAllianceId > 0) {
            $poi->OwnerAllianceName = substr($details, $pos);
        } else {
            $poi->OwnerAllianceName = "";
        }
        return $poi;
    }
}
class World
{
    public $players = array();
    public $alliances = array(0 => array(
        'a' => 0,
        'an' => "No Alliance",
        'p' => 0,
        'c' => 0,
    ));
    public $bases = array();
    public $pois = array();
    private $server;

    public function __construct($server)
    {
        $this->server = $server;
    }

    public function request($sx, $sy, $ex, $ey)
    {
        $result = "";
        for ($y = $sy; $y <= $ey; $y++) {
            for ($x = $sx; $x <= $ex; $x++) {
                $data = $y << 8 | $x;
                $version = 0;
                $data |= 0 << 16;
                $result = $result . Base91::Encode19Bit($data) . Base91::EncodeFlexInt($version);
            }
        }
        return $result;
    }

    public static function sort($a, $b)
    {
        return (strtolower($a['an']) > strtolower($b['an'])) ? 1 : -1;
    }

    public function addSquare(Square $square)
    {
        foreach ($square->alliances as $alliance) {
            if (!isset($this->alliances[$alliance['_id']])) {
                $this->alliances[$alliance['_id']] = array(
                    'a' => $alliance['_id'],
                    'an' => $alliance['name'],
                    'p' => $alliance['points'],
                    'c' => 0,
                );

            }
        }

        foreach ($square->players as $player) {
            if (!isset($this->players[$player['_id']])) {
                //absolute alliance ID
                $a = $square->alliances[$player['alliance']]['_id'];
                $player['alliance'] = !$a ? 0 : $a;
                $this->alliances[$player['alliance']]['c']++;

                $this->players[$player['_id']] = array(
                    'i' => $player['_id'],
                    'p' => $player['points'],
                    'a' => $player['alliance'],
                    'n' => $player['name'],
                    'f' => $player['faction'],
                    'ps' => $player['peaceStart'],
                    'pd' => $player['peaceDuration'],
                    'bc' => 0
                );
            }
        }

        foreach ($square->marks as $mark) {
            switch (get_class($mark)) {
                case "City":
                    //absolute player ID
                    $mark->playerId = $square->players[$mark->playerId]['_id'];

                    $this->players[$mark->playerId]['bc']++;

                    $this->bases[] = array(
                        'pi' => $mark->playerId,
                        'y' => $mark->y,
                        'x' => $mark->x,
                        'n' => $mark->name,
                        'i' => $mark->_id,
                        'l' => $mark->level,
//                        'at' => $mark->isAttacked,
                        'al' => $mark->isAlerted,
//                        'dd' => $mark->isDefenseDamaged,
//                        'lo' => $mark->isLocked,
                        'pr' => $mark->isProtected,
                        'cb' => $mark->conditionBuildings,
                        'cd' => $mark->conditionDefense,
//                        'mc' => $mark->moveCooldownEndStep,
//                        'ml' => $mark->moveLockdownEndStep,
                        'ps' => $mark->protectionEndStep,
//                        'ct' => $mark->codeTime
                    );
                    break;
                case "POI":
                    $this->pois[] = array(
                        'x' => $mark->x,
                        'y' => $mark->y,
                        't' => $mark->type,
                        'l' => $mark->level,
                        'a' => $mark->OwnerAllianceId,
                    );
                    break;
            }
        }
        //        foreach ($this->players as $p => $player) {
        //            if ($player['bc'] == 0) {
        //                unset($this->players[$p]);
        //            }
        //        }
        //        foreach ($this->alliances as $a => $alliance) {
        //            if ($alliance['c'] == 0) {
        //                unset($this->alliances[$a]);
        //            }
        //        }

    }

    public function toFile()
    {
        $data = $this->prepareData();


        $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR;
        $dataFile = $path . $this->server . ".js";
        //        @rename($dataFile, $path . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "data_" . $this->api->getServer() . "_" . date("Y-m-d_H-i-s") . ".json");
        file_put_contents($dataFile, "ccmapData = " . json_encode($data) . ";");
    }

    private function prepareData()
    {
        uasort($this->alliances, "World::sort");

        $data = array('bases' => $this->bases,
            'players' => array_values($this->players),
            'alliances' => array_values($this->alliances),
            'pois' => array_values($this->pois),
            'updated' => date("d/m/Y H:i:s"),
            'timestamp' => time(),
        );
        return $data;
    }

    public function toServer()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_URL, "http://data.tiberium-alliances.com/savedata");
        curl_setopt($ch, CURLOPT_TIMEOUT, 200);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, CnCApi::makePostData(array('key' => "wohdfo97wg4iurvfdc t7yaigvrufbs", 'world' => $this->server, 'data' => gzcompress("ccmapData = " . json_encode($this->prepareData()) . ";"))));

        return curl_exec($ch);
    }
}
