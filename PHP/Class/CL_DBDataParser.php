<?php

namespace PHPClass;

require_once __DIR__ . '/CL_baseAbstract.php';
abstract class CL_DBDataParser extends CL_baseAbstract implements \Countable, \JsonSerializable {

    public function macEncodeForDb(string $mac): string {
        if (strlen($mac) == 12) {
            return $mac;
        }
        if (substr_count($mac, ':') != 5) {
            throw new TypeError('Invalid MAC address!');
        }
        return str_replace(':', '', $mac);
    }

    public function macDecodeFromDb(string $mac): string {
        if (strlen($mac) != 12) {
            throw new TypeError('Invalid MAC address! Expected 12 chars, '. strlen($mac) .' given.');
        }
        if (substr_count($mac, ':') != 5) {
            return $mac;
        }
        //Split string into an array.  Each element is 2 chars
        //Convert array to string.  Each element separated by the given separator.
        return implode(':', str_split($mac, 2));
    }

    public function checkIP(string $ip): string {
        $ip = trim($ip);
        $ipv4n6pattern = '/^(?>(?>([a-f0-9]{1,4})(?>:(?1)){7}|(?!(?:.*[a-f0-9](?>:|$))'
                . '{8,})((?1)(?>:(?1)){0,6})?::(?2)?)|(?>(?>(?1)(?>:(?1)){5}:|(?!(?:.'
                . '*[a-f0-9]:){6,})(?3)?::(?>((?1)(?>:(?1)){0,4}):)?)?(25[0-5]|2[0-4][0-9]|'
                . '1[0-9]{2}|[1-9]?[0-9])(?>\.(?4)){3}))$/iD';
        
        if (!preg_match($ipv4n6pattern, $ip)) {
            throw new TypeError("Invalid IP address!");
        }
        return $ip;
    }

    public function jsonSerialize(): mixed {
        return get_object_vars($this);
    }


    /**
     * Checks timestamps and throws an exception where dates are invalid
     * @throws TypeError
     */
    public static function checkTimes(int $fts, int $lts): void {
        if ($fts > $lts) {
            throw new TypeError('First time seen is greater than last time seen');
        }
        //it is not expected to capture for more than 1 day
        //check for two day straight capture and throw on higher than that
        $secondsInTwoDays = 2 * 24 * 60 * 60;
        if ($lts - $fts > $secondsInTwoDays) {
            throw new TypeError('Capture time is greater than 2 days');
        }
    }
}
