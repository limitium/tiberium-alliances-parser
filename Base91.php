<?php class Base91
{

    private static $dectab = array(91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 73, 62, 91, 63, 64, 65, 66, 90, 67, 68, 69, 70, 71, 91, 72, 91, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 74, 75, 76, 77, 78, 79, 80, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 81, 91, 82, 83, 84, 85, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 86, 87, 88, 89, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91, 91);
    private static $enctab = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "!", "#", "$", "%", "&", "(", ")", "*", "+", ",", ".", " ", ":", ";", "<", "=", ">", "?", "@", "[", "]", "^", "_", "`", "{", "|", "}", "~", "'");
    private static $SeperatorCode = 45;
    private static $Seperator = "-";

    public static function Decode13Bits($value, $offset)
    {
        return self::$dectab[ord($value[$offset])] + 91 * self::$dectab[ord($value[$offset + 1])];
    }

    public static function Decode32Bits($value, $offset)
    {
        return self::DecodeInt($value, $offset, 5);
    }

    public static function DecodeInt($value, $offset, $size)
    {
        $result = 0;
        $factor = 1;
        for ($i = 0; $i < $size; $i++) {
            $result += $factor * self::$dectab[ord($value[$offset + $i])];
            $factor *= 91;
        }
        return $result;
    }

    public static function DecodeFlexInt($value, $offset, $pout)
    {
        $result = 0;
        $factor = 1;
        $pout->size = 0;
        while ($pout->size < 5) {
            $next = ord($value[($offset + $pout->size)]);
            if ($next == self::$SeperatorCode) {
                $pout->size++;
                return $result;
            }
            $result += $factor * self::$dectab[$next];
            $factor *= 91;
            $pout->size++;
        }
        return $result;
    }

    public static function Decode26Bits($value, $offset)
    {
        return self::DecodeInt($value, $offset, 4);
    }

    public static function Encode19Bit($value)
    {
        return self::EncodeInt($value, 3);
    }

    public static function EncodeInt($value, $size)
    {
        $result = "";
        for ($i = 0; $i < $size; $i++) {
            $result = $result . self::$enctab[$value % 91];
            $value /= 91;
            $value = floor(floor($value));
        }
        return $result;
    }

    public static function EncodeFlexInt($value)
    {
        $result = "";
        $i = 0;
        while ($i < 5) {
            if ($value == 0) {
                break;
            }
            $result = $result . self::$enctab[$value % 91];

            $value /= 91;
            $value = floor(floor($value));
            $i++;
        }
        if ($i < 5) {
            $result = $result . self::$Seperator;
        }
        $i++;
        return $result;
    }
}