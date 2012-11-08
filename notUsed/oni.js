//REQUEST!!!!!!!!

function ClientLib.Data.World.prototype.SetRange(active, includeDetails, sx, sy, ex, ey) {
    var $createHelper;
    this.m_Active = active;
    this.m_HighDetails = includeDetails;
    this.m_RangeStartX = sx;
    this.m_RangeStartY = sy;
    this.m_RangeEndX = ex;
    this.m_RangeEndY = ey;
}
function ClientLib.Data.World.prototype.GetData(requestId) {
    var $createHelper;
    var $out = {};
    var result = "";
    if (this.m_Active) {
        var sx = Math.max(0, this.m_RangeStartX - 1);
        var ex = Math.max(0, this.m_RangeEndX + 1);
        var sy = Math.max(0, this.m_RangeStartY - 1);
        var ey = Math.max(0, this.m_RangeEndY + 1);
        for (var y = sy; y <= ey; y++) {
            for (var x = sx; x <= ex; x++) {
                var sector;
                var data = y << 8 | x;
                var version = 0;
                if ($out.$r = this.m_Sectors.TryGetValue$0(y << 16 | x, $out), sector = $out.value, $out.$r) {
                    version = sector.get_Version$0();
                    data |= sector.GetUpdateFlags$0(this.m_HighDetails) << 16;
                }
                result = result + ClientLib.Base.Base91.Encode19Bit$0(data) + ClientLib.Base.Base91.EncodeFlexInt$0(version);
            }
        }
    }
    return result;
}
function ClientLib.Data.WorldSector.prototype.GetUpdateFlags(highDetails) {
    var $createHelper;
    var result = 0;
    if (highDetails && this.m_TerrainDetails == null) {
        result |= 1;
    }
    if (this.m_Terrain == null) {
        result |= 2;
    }
    return result;
}
//RESPONSE!!!!!!!!!
function ClientLib.Data.WorldSector.prototype.SetDetails(detail, pos) {
    var $createHelper;
    var headData = ClientLib.Base.Base91.Decode13Bits$0(detail, pos);
    var x = headData & 31;
    var y = headData >> 5 & 31;
    var type = headData >> 10;
    switch (type) {
        case ClientLib.Data.WorldSector.ObjectType.None:
            this.m_Objects.Remove$3(y << 16 | x);
            return this.m_World.SetBaseOwnerInfo$0(this.m_SX * 32 + x, this.m_SY * 32 + y, ClientLib.Data.EOwnerType.Player, 0, 0, 0, false);
        case ClientLib.Data.WorldSector.ObjectType.City:
            var cityObj = (new ClientLib.Data.WorldSector.WorldObjectCity).$ctor$87(type, this.m_World, detail, pos + 2);
            this.m_Objects.set_Item$3(y << 16 | x, cityObj);
            var player = this.m_Players.d[cityObj.PlayerId];
            if (player == null) {
                return false;
            }
            var alliance = player.Alliance != 0 ? this.m_Alliances.d[player.Alliance] : null;
            return this.m_World.SetBaseOwnerInfo$0(this.m_SX * 32 + x, this.m_SY * 32 + y, alliance != null ? ClientLib.Data.EOwnerType.Alliance : ClientLib.Data.EOwnerType.Player, alliance != null ? alliance.Id : player.Id, cityObj.Radius, cityObj.Level, true);
        case ClientLib.Data.WorldSector.ObjectType.NewPlayerSlot:
            this.m_Objects.set_Item$3(y << 16 | x, (new ClientLib.Data.WorldSector.WorldObjectNewPlayerSlot).$ctor$87(type, this.m_World, detail, pos + 2));
            return this.m_World.SetBaseOwnerInfo$0(this.m_SX * 32 + x, this.m_SY * 32 + y, ClientLib.Data.EOwnerType.StartSlot, 0, 2, 1, true);
        case ClientLib.Data.WorldSector.ObjectType.NPCBase:
            var target = (new ClientLib.Data.WorldSector.WorldObjectNPCBase).$ctor$87(type, this.m_World, detail, pos + 2);
            this.m_Objects.set_Item$3(y << 16 | x, target);
            return this.m_World.SetBaseOwnerInfo$0(this.m_SX * 32 + x, this.m_SY * 32 + y, ClientLib.Data.EOwnerType.NPC, 0, target.Radius, target.Level, true);
        case ClientLib.Data.WorldSector.ObjectType.NPCCamp:
            var target = (new ClientLib.Data.WorldSector.WorldObjectNPCCamp).$ctor$87(type, this.m_World, detail, pos + 2);
            this.m_Objects.set_Item$3(y << 16 | x, target);
            if (target.CampType == ClientLib.Data.WorldSector.WorldObjectNPCCamp.ECampType.Destroyed) {
                return this.m_World.SetBaseOwnerInfo$0(this.m_SX * 32 + x, this.m_SY * 32 + y, ClientLib.Data.EOwnerType.Player, 0, 0, 0, false);
            }
            return this.m_World.SetBaseOwnerInfo$0(this.m_SX * 32 + x, this.m_SY * 32 + y, ClientLib.Data.EOwnerType.Player, 0, 0, 0, true);
        case ClientLib.Data.WorldSector.ObjectType.PointOfInterest:
            var target = (new ClientLib.Data.WorldSector.WorldObjectPointOfInterest).$ctor$87(type, this.m_World, detail, pos + 2);
            this.m_Objects.set_Item$3(y << 16 | x, target);
            return this.m_World.SetBaseOwnerInfo$0(this.m_SX * 32 + x, this.m_SY * 32 + y, ClientLib.Data.EOwnerType.Player, 0, 0, 0, true);
        case ClientLib.Data.WorldSector.ObjectType.Ruin:
            var target = (new ClientLib.Data.WorldSector.WorldObjectRuin).$ctor$87(type, this.m_World, detail, pos + 2);
            this.m_Objects.set_Item$3(y << 16 | x, target);
            var player = this.m_Players.d[target.PlayerId];
            if (player == null) {
                return false;
            }
            var alliance = player.Alliance != 0 ? this.m_Alliances.d[player.Alliance] : null;
            return this.m_World.SetBaseOwnerInfo$0(this.m_SX * 32 + x, this.m_SY * 32 + y, alliance != null ? ClientLib.Data.EOwnerType.Alliance : ClientLib.Data.EOwnerType.Player, alliance != null ? alliance.Id : player.Id, target.Radius, target.BaseLevel, true);
        default:
            ;
    }
    this.m_Objects.set_Item$3(y << 16 | x, (new ClientLib.Data.WorldSector.WorldObject).$ctor$86(type, this.m_World));
    return false;
}
function ClientLib.Data.WorldSector.WorldObjectCity.prototype.$ctor$87(type, world, details, pos) {
    var $createHelper;
    var $out = {};
    ClientLib.Data.WorldSector.WorldObject.prototype.$ctor$86.call(this, type, world);
    var size;
    var cityData = ClientLib.Base.Base91.Decode32Bits$0(details, pos);
    this.isAttacked = ((cityData & 1) != 0);
    this.isLocked = (((cityData >> 1) & 1) != 0);
    this.isProtected = (((cityData >> 2) & 1) != 0);
    this.isAlerted = (((cityData >> 3) & 1) != 0);
    this.hasCooldown = (((cityData >> 4) & 1) != 0);
    var isDefenseDamaged = (((cityData >> 5) & 1) != 0);
    this.Level = ((cityData >> 6) & 0xff);
    this.Radius = ((cityData >> 14) & 15);
    this.PlayerId = ((cityData >> 0x16) & 0x3ff);
    pos += 5;
    if (this.isLocked) {
        this.LockdownEndStep = ($out.$r = ClientLib.Base.Base91.DecodeFlexInt$0(details, pos, $out), size = $out.size, $out.$r);
        pos += size;
    }
    if (this.isProtected) {
        this.ProtectionEndStep = ($out.$r = ClientLib.Base.Base91.DecodeFlexInt$0(details, pos, $out), size = $out.size, $out.$r);
        pos += size;
    }
    if (this.isAlerted) {
        this.SupportAlertStartStep = ($out.$r = ClientLib.Base.Base91.DecodeFlexInt$0(details, pos, $out), size = $out.size, $out.$r);
        pos += size;
        this.SupportAlertEndStep = ($out.$r = ClientLib.Base.Base91.DecodeFlexInt$0(details, pos, $out), size = $out.size, $out.$r);
        pos += size;
    }
    if (this.hasCooldown) {
        this.MoveCooldownEndStep = ($out.$r = ClientLib.Base.Base91.DecodeFlexInt$0(details, pos, $out), size = $out.size, $out.$r);
        pos += size;
        this.MoveLockdownEndStep = ($out.$r = ClientLib.Base.Base91.DecodeFlexInt$0(details, pos, $out), size = $out.size, $out.$r);
        pos += size;
    }
    this.ConditionBuildings = ($out.$r = ClientLib.Base.Base91.DecodeFlexInt$0(details, pos, $out), size = $out.size, $out.$r);
    pos += size;
    if (isDefenseDamaged) {
        this.ConditionDefense = ($out.$r = ClientLib.Base.Base91.DecodeFlexInt$0(details, pos, $out), size = $out.size, $out.$r);
        pos += size;
    }
    else {
        this.ConditionDefense = -1;
    }
    this.Id = ($out.$r = ClientLib.Base.Base91.DecodeFlexInt$0(details, pos, $out), size = $out.size, $out.$r);
    this.Name = details.substr((pos + size));
    return this;
}

function ClientLib.Data.WorldSector.WorldObjectCity.prototype.$ctor (a, b, c, d) {
    var $createHelper;
    var e = {};
    $I.FYQWYB.prototype.NQZQKI.call(this, a, b);
    var f;
    this.lockdownEndStep = -1;
    this.protectionEndStep = -1;
    this.suportStart = -1;
    this.supportEnd = -1;
    this.HMSIIJ = -1;
    this.RPVXRZ = -1;
    this.WHKUKB = -1;
    var g = $I.FKZMMP.NBIQUL(c, d);
    this.isAttacked = (g & 1) != 0;
    var isLocked = (g >> 1 & 1) != 0;
    var isProteced = (g >> 2 & 1) != 0;
    var isAltered = (g >> 3 & 1) != 0;
    var hasCooldown = (g >> 4 & 1) != 0;
    var hasRecovery = (g >> 5 & 1) != 0;
    var hz = (g >> 6 & 1) != 0;
    var isDefenseDamaged = (g >> 7 & 1) != 0;
    this.level = g >> 8 & 255;
    this.radius = g >> 16 & 15;
    this.playerId = g >> 22 & 1023;
    d += 5;
    if (isLocked) {
        this.lockdownEndStep = (e.$r = $I.FKZMMP.UXIOBH(c, d, e), f = e.c, e.$r);
        d += f;
    }
    if (isProteced) {
        this.protectionEndStep = (e.$r = $I.FKZMMP.UXIOBH(c, d, e), f = e.c, e.$r);
        d += f;
    }
    if (isAltered) {
        this.suportStart = (e.$r = $I.FKZMMP.UXIOBH(c, d, e), f = e.c, e.$r);
        d += f;
        this.supportEnd = (e.$r = $I.FKZMMP.UXIOBH(c, d, e), f = e.c, e.$r);
        d += f;
    }
    if (hasCooldown) {
        this.HMSIIJ = (e.$r = $I.FKZMMP.UXIOBH(c, d, e), f = e.c, e.$r);
        d += f;
    }
    if (hasRecovery) {
        this.RPVXRZ = (e.$r = $I.FKZMMP.UXIOBH(c, d, e), f = e.c, e.$r);
        d += f;
        this.IPIVWX = (e.$r = $I.FKZMMP.UXIOBH(c, d, e), f = e.c, e.$r);
        d += f;
    }
    if (hz) {
        this.WHKUKB = (e.$r = $I.FKZMMP.UXIOBH(c, d, e), f = e.c, e.$r);
        d += f;
    }
    this.conditionBuildings = (e.$r = $I.FKZMMP.UXIOBH(c, d, e), f = e.c, e.$r);
    d += f;
    if (isDefenseDamaged) {
        this.conditionDefense = (e.$r = $I.FKZMMP.UXIOBH(c, d, e), f = e.c, e.$r);
        d += f;
    } else {
        this.conditionDefense = -1;
    }
    this.defenseAutoRepairStep = (e.$r = $I.FKZMMP.UXIOBH(c, d, e), f = e.c, e.$r);
    d += f;
    this.id = (e.$r = $I.FKZMMP.UXIOBH(c, d, e), f = e.c, e.$r);
    this.name = c.substr(d + f);
    return this;
}
function ClientLib.Data.WorldSector.WorldObjectCity.prototype.$ctor (type, world, details, pos) {
    var $createHelper;
    var e = {};
    $I.FFKFQE.prototype.CPTHBH.call(this, type, world);
    var f;
    this.JYPDYU = -1;
    this.LKUPOD = -1;
    this.FKPXOU = -1;
    this.VTQBQV = -1;
    this.WYBSES = -1;
    this.BQSPZH = -1;
    this.AIVBAC = -1;
    var g = $I.JKQSQL.LAJTKU(details, pos);
    this.isAttacked = (g & 1) != 0;
    var isLocked = (g >> 1 & 1) != 0;
    var isProtected = (g >> 2 & 1) != 0;
    var isAltered = (g >> 3 & 1) != 0;
    var hasCool = (g >> 4 & 1) != 0;
    var hasRec = (g >> 5 & 1) != 0;
    var hsFlag = (g >> 6 & 1) != 0;
    var isDefenceDamaged = (g >> 7 & 1) != 0;
    this.level = g >> 8 & 255;
    this.radius = g >> 16 & 15;
    this.playerId = g >> 22 & 1023;
    pos += 5;
    if (isLocked) {
        this.JYPDYU = (e.$r = $I.JKQSQL.OKTXEA(details, pos, e), f = e.c, e.$r);
        pos += f;
    }
    if (isProtected) {
        this.LKUPOD = (e.$r = $I.JKQSQL.OKTXEA(details, pos, e), f = e.c, e.$r);
        pos += f;
    }
    if (isAltered) {
        this.FKPXOU = (e.$r = $I.JKQSQL.OKTXEA(details, pos, e), f = e.c, e.$r);
        pos += f;
        this.VTQBQV = (e.$r = $I.JKQSQL.OKTXEA(details, pos, e), f = e.c, e.$r);
        pos += f;
    }
    if (hasCool) {
        this.WYBSES = (e.$r = $I.JKQSQL.OKTXEA(details, pos, e), f = e.c, e.$r);
        pos += f;
    }
    if (hasRec) {
        this.BQSPZH = (e.$r = $I.JKQSQL.OKTXEA(details, pos, e), f = e.c, e.$r);
        pos += f;
        this.SYNMMZ = (e.$r = $I.JKQSQL.OKTXEA(details, pos, e), f = e.c, e.$r);
        pos += f;
    }
    if (hsFlag) {
        this.AIVBAC = (e.$r = $I.JKQSQL.OKTXEA(details, pos, e), f = e.c, e.$r);
        pos += f;
    }
    this.condBuild = (e.$r = $I.JKQSQL.OKTXEA(details, pos, e), f = e.c, e.$r);
    pos += f;
    if (isDefenceDamaged) {
        this.condDef = (e.$r = $I.JKQSQL.OKTXEA(details, pos, e), f = e.c, e.$r);
        pos += f;
    } else {
        this.condDef = -1;
    }
    this.DefenseAutoRepairStartStep = (e.$r = $I.JKQSQL.OKTXEA(details, pos, e), f = e.c, e.$r);
    pos += f;
    this.id = (e.$r = $I.JKQSQL.OKTXEA(details, pos, e), f = e.c, e.$r);
    this.name = details.substr(pos + f);
    return this;
}
function ClientLib.Data.WorldSector.WorldObjectNPCBase.prototype.$ctor$87(type, world, details, pos) {
    var $createHelper;
    var $out = {};
    ClientLib.Data.WorldSector.WorldObject.prototype.$ctor$86.call(this, type, world);
    var size;
    var npcData = ClientLib.Base.Base91.Decode26Bits$0(details, pos);
    this.isAttacked = (npcData & 1) != 0;
    this.isLocked = (npcData >> 1 & 1) != 0;
    this.isAlerted = (npcData >> 2 & 1) != 0;
    var isDefenseDamaged = (npcData >> 3 & 1) != 0;
    this.Level = npcData >> 4 & 255;
    this.Radius = npcData >> 12 & 15;
    pos += 4;
    if (this.isLocked) {
        this.LockdownEndStep = ($out.$r = ClientLib.Base.Base91.DecodeFlexInt$0(details, pos, $out), size = $out.size, $out.$r);
        pos += size;
    }
    if (this.isAlerted) {
        this.SupportAlertStartStep = ($out.$r = ClientLib.Base.Base91.DecodeFlexInt$0(details, pos, $out), size = $out.size, $out.$r);
        pos += size;
        this.SupportAlertEndStep = ($out.$r = ClientLib.Base.Base91.DecodeFlexInt$0(details, pos, $out), size = $out.size, $out.$r);
        pos += size;
    }
    this.ConditionBuildings = ($out.$r = ClientLib.Base.Base91.DecodeFlexInt$0(details, pos, $out), size = $out.size, $out.$r);
    pos += size;
    if (isDefenseDamaged) {
        this.ConditionDefense = ($out.$r = ClientLib.Base.Base91.DecodeFlexInt$0(details, pos, $out), size = $out.size, $out.$r);
        pos += size;
    } else {
        this.ConditionDefense = -1;
    }
    this.LastCombatStep = ($out.$r = ClientLib.Base.Base91.DecodeFlexInt$0(details, pos, $out), size = $out.size, $out.$r);
    pos += size;
    this.Id = ($out.$r = ClientLib.Base.Base91.DecodeFlexInt$0(details, pos, $out), size = $out.size, $out.$r);
    return this;
}
function ClientLib.Data.WorldSector.WorldObjectPointOfInterest.prototype.$ctor$89(type, world, details, pos) {
    var $createHelper;
    var $out = {};
    ClientLib.Data.WorldSector.WorldObject.prototype.$ctor$88.call(this, type, world);
    var size;
    var poiData = ClientLib.Base.Base91.Decode26Bits$0(details, pos);
    this.Level = poiData & 255;
    this.POIType = poiData >> 8 & ClientLib.Data.WorldSector.WorldObjectPointOfInterest.EPOIType.Defense;
    pos += 4;
    this.Id = ($out.$r = ClientLib.Base.Base91.DecodeFlexInt$0(details, pos, $out), size = $out.size, $out.$r);
    pos += size;
    this.OwnerAllianceId = ($out.$r = ClientLib.Base.Base91.DecodeFlexInt$0(details, pos, $out), size = $out.size, $out.$r);
    pos += size;
    if (this.OwnerAllianceId > 0) {
        this.OwnerAllianceName = details.substr(pos);
    } else {
        this.OwnerAllianceName = "";
    }
    return this;
}
function ClientLib.Data.WorldSector.WorldObjectRuin.prototype.$ctor$87(type, world, details, pos) {
    var $createHelper;
    var $out = {};
    ClientLib.Data.WorldSector.WorldObject.prototype.$ctor$86.call(this, type, world);
    var size;
    var data = ClientLib.Base.Base91.Decode26Bits$0(details, pos);
    pos += 4;
    var isCityRuin = data & 1;
    this.BaseLevel = data >> 1 & 255;
    this.Radius = data >> 9 & 15;
    this.PlayerId = data >> 13 & 1023;
    this.CreateStep = ($out.$r = ClientLib.Base.Base91.DecodeFlexInt$0(details, pos, $out), size = $out.size, $out.$r);
    pos += size;
    if (isCityRuin == 1) {
        this.OldBaseOwnerId = ($out.$r = ClientLib.Base.Base91.DecodeFlexInt$0(details, pos, $out), size = $out.size, $out.$r);
        pos += size;
        this.OldBaseOwnerAllianceId = ($out.$r = ClientLib.Base.Base91.DecodeFlexInt$0(details, pos, $out), size = $out.size, $out.$r);
        pos += size;
        this.OldBaseOwnerFaction = ($out.$r = ClientLib.Base.Base91.DecodeFlexInt$0(details, pos, $out), size = $out.size, $out.$r);
        pos += size;
        this.OldBaseOwnerName = ($out.$r = ClientLib.Base.Base91.DecodeFlexString$0(details, pos, $out), size = $out.size, $out.$r);
        pos += size;
        this.OldBaseOwnerAllianceName = ($out.$r = ClientLib.Base.Base91.DecodeFlexString$0(details, pos, $out), size = $out.size, $out.$r);
        pos += size;
        this.BaseName = details.substr(pos);
    } else {
        this.OldBaseOwnerId = -1;
        this.OldBaseOwnerName = "";
        this.OldBaseOwnerAllianceId = -1;
        this.OldBaseOwnerAllianceName = "";
        this.OldBaseOwnerFaction = 3;
        this.BaseName = "";
    }
    return this;
}

