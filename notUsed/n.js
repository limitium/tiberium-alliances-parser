function qwe(a, b, c, d) {
    var $createHelper;
    var e = {};
    $I.ANXQBZ.prototype.ERBNAL.call(this, a, b);
    var f;
    this.isLocked = -1;
    this.isProtected = -1;
    this.supportlaterdstart = -1;
    this.supportalertend = -1;
    this.move1 = -1;
    this.move2 = -1;
    this.end = -1;

    var g = $I.TWVUOT.GKMZAL(c, d);
    this.isAtackted = (g & 1) != 0;
    var islocked = (g >> 1 & 1) != 0;
    var isprotected = (g >> 2 & 1) != 0;
    var islatered = (g >> 3 & 1) != 0;
    var hascooldown = (g >> 4 & 1) != 0;
    var hasrecovery = (g >> 5 & 1) != 0;
    var h = (g >> 6 & 1) != 0;
    this.level = g >> 7 & 255;
    this.radius = g >> 15 & 15;
    this.playerid = g >> 22 & 1023;
    d += 5;
    if (islocked) {
        this.isLocked = (e.$r = $I.TWVUOT.LBGCQB(c, d, e), f = e.c, e.$r);
        d += f;
    }
    if (isprotected) {
        this.isProtected = (e.$r = $I.TWVUOT.LBGCQB(c, d, e), f = e.c, e.$r);
        d += f;
    }
    if (islatered) {
        this.supportlaterdstart = (e.$r = $I.TWVUOT.LBGCQB(c, d, e), f = e.c, e.$r);
        d += f;
        this.supportalertend = (e.$r = $I.TWVUOT.LBGCQB(c, d, e), f = e.c, e.$r);
        d += f;
    }
    if (hascooldown) {
        this.move1 = (e.$r = $I.TWVUOT.LBGCQB(c, d, e), f = e.c, e.$r);
        d += f;
        this.move2 = (e.$r = $I.TWVUOT.LBGCQB(c, d, e), f = e.c, e.$r);
        d += f;
        this.move3 = (e.$r = $I.TWVUOT.LBGCQB(c, d, e), f = e.c, e.$r);
        d += f;
    }
    if (hasrecovery) {
        this.end = (e.$r = $I.TWVUOT.LBGCQB(c, d, e), f = e.c, e.$r);
        d += f;
    }
    this.condotoionbuildings = (e.$r = $I.TWVUOT.LBGCQB(c, d, e), f = e.c, e.$r);
    d += f;
    if (h) {
        this.conditiondefence = (e.$r = $I.TWVUOT.LBGCQB(c, d, e), f = e.c, e.$r);
        d += f;
    } else {
        this.conditiondefence = -1;
    }
    this.defautoreps = (e.$r = $I.TWVUOT.LBGCQB(c, d, e), f = e.c, e.$r);
    d += f;
    this.id = (e.$r = $I.TWVUOT.LBGCQB(c, d, e), f = e.c, e.$r);
    this.name = c.substr(d + f);
    return this;
}


function npcbase (a, b, c, d) {
    var $createHelper;
    var e = {};
    $I.ANXQBZ.prototype.ERBNAL.call(this, a, b);
    var size;
    this.altered = -1;
    this.alterS = -1;
    this.alterSp = -1;
    var g = $I.TWVUOT.SHIVFD(c, d);
    this.isAttaacked = (g & 1) != 0;
    this.islocked = (g >> 1 & 1) != 0;
    var islocked = (g >> 2 & 1) != 0;
    var isAltered = (g >> 3 & 1) != 0;
    var ifDefdamaged = (g >> 4 & 1) != 0;
    this.HUFTET = (g >> 5 & 16383) / 100;
    this.OJOECD = Math.floor(Math.floor(this.HUFTET + 0.5));
    this.VCREGA = g >> 19 & 15;
    d += 4;
    if (islocked) {
        this.altered = (e.$r = $I.TWVUOT.LBGCQB(c, d, e), size = e.c, e.$r);
        d += size;
    }
    if (isAltered) {
        this.alterS = (e.$r = $I.TWVUOT.LBGCQB(c, d, e), size = e.c, e.$r);
        d += size;
        this.alterSp = (e.$r = $I.TWVUOT.LBGCQB(c, d, e), size = e.c, e.$r);
        d += size;
    }
    this.JSCUBC = (e.$r = $I.TWVUOT.LBGCQB(c, d, e), size = e.c, e.$r);
    d += size;
    if (ifDefdamaged) {
        this.KXUOOZ = (e.$r = $I.TWVUOT.LBGCQB(c, d, e), size = e.c, e.$r);
        d += size;
    } else {
        this.KXUOOZ = -1;
    }
    this.lastcs = (e.$r = $I.TWVUOT.LBGCQB(c, d, e), size = e.c, e.$r);
    d += size;
    this.id = (e.$r = $I.TWVUOT.LBGCQB(c, d, e), size = e.c, e.$r);
    return this;
}

function poi (a, b, c, d) {
    var $createHelper;
    var e = {};
    $I.ANXQBZ.prototype.ERBNAL.call(this, a, b);
    var f;
    var g = $I.TWVUOT.SHIVFD(c, d);
    this.LCFYKV = g & 255;
    this.TVPMIH = g >> 8 & $I.BAKTGJ.Defense;
    d += 4;
    this.PDSPWC = (e.$r = $I.TWVUOT.LBGCQB(c, d, e), f = e.c, e.$r);
    d += f;
    this.ZJXTYU = (e.$r = $I.TWVUOT.LBGCQB(c, d, e), f = e.c, e.$r);
    d += f;
    if (this.ZJXTYU > 0) {
        this.SJTCCP = c.substr(d);
    } else {
        this.SJTCCP = "";
    }
    return this;
}