<?php

class NPCBase extends AliveBase
{
    public $lastCombatStep;

    /**
     * @static
     * @param $details
     * @param $pos
     * @return NPCBase
     */
    public static function decode($details, $pos)
    {
        $npcBase = new self();
        $npcData = Base91::Decode26Bits($details, $pos);
        $npcBase->isAttacked = ($npcData & 1) != 0;
        $npcBase->isLocked = ($npcData >> 1 & 1) != 0;
        $npcBase->isAlerted = ($npcData >> 2 & 1) != 0;
        $npcBase->isDefenseDamaged = ($npcData >> 3 & 1) != 0;
        $npcBase->level = $npcData >> 4 & 255;
        $npcBase->radius = $npcData >> 12 & 15;
        $pos += 4;


        //???
        $out = new stdClass();
        $out->size = 0;
        if ($npcBase->isLocked) {
            $npcBase->lockdownEndStep = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
        }
        if ($npcBase->isAlerted) {
            $npcBase->supportAlertStartStep = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
            $npcBase->supportAlertEndStep = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
        }
        $npcBase->conditionBuildings = Base91::DecodeFlexInt($details, $pos, $out);
        $pos += $out->size;
        if ($npcBase->isDefenseDamaged) {
            $npcBase->conditionDefense = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
        } else {
            $npcBase->conditionDefense = -1;
        }
        $npcBase->lastCombatStep = Base91::DecodeFlexInt($details, $pos, $out);
        $pos += $out->size;
        $npcBase->_id = Base91::DecodeFlexInt($details, $pos, $out);
        return $npcBase;
    }

}

class Ruin extends Base
{

    public $playerId;

    public $isCityRuin;
    public $createStep;

    public $oldBaseOwnerId;
    public $oldBaseOwnerName;
    public $oldBaseOwnerAllianceId;
    public $oldBaseOwnerAllianceName;
    public $oldBaseOwnerFaction;
    public $baseName;

    /**
     * @static
     * @param $details
     * @param $pos
     * @return Ruin
     */
    public static function decode($details, $pos)
    {
        $ruin = new self();
        $data = Base91::Decode26Bits($details, $pos);
        $pos += 4;
        $ruin->isCityRuin = $data & 1;
        $ruin->level = $data >> 1 & 255;
        $ruin->radius = $data >> 9 & 15;
        $ruin->playerId = $data >> 13 & 1023;
        $out = new stdClass();
        $ruin->createStep = Base91::DecodeFlexInt($details, $pos, $out);
        $pos += $out->size;
        if ($ruin->isCityRuin == 1) {
            $ruin->oldBaseOwnerId = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
            $ruin->oldBaseOwnerAllianceId = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
            $ruin->oldBaseOwnerFaction = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
            $ruin->oldBaseOwnerName = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
            $ruin->oldBaseOwnerAllianceName = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
            $ruin->baseName = substr($details, $pos);
        } else {
            $ruin->oldBaseOwnerId = -1;
            $ruin->oldBaseOwnerName = "";
            $ruin->oldBaseOwnerAllianceId = -1;
            $ruin->oldBaseOwnerAllianceName = "";
            $ruin->oldBaseOwnerFaction = 3;
            $ruin->baseName = "";
        }
        return $ruin;
    }

}

