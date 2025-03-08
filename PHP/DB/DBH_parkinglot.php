<?php

use PHPClass\CL_parkinglot;

require_once __DIR__ . '/../Class/CL_parkinglot.php';
require_once __DIR__ . '/DBH_abstract.php';
require_once __DIR__ . '/DBH_connection.php';

class DBH_parkinglot extends DBH_abstract
{

    const TABLE_NAME = 'parkinglot';
    const MAX_PER_SELECT = 64;
    const FULL_SELECT = '`id`, `car_capacity`, ST_X(geopos) as longitude, ST_Y(geopos) as latitude, `name`, `description`';
    const FULL_INSERT = '( `car_capacity`, `geopos`, `name`, `description)';
    protected array $tablesToLock = [self::TABLE_NAME];

    protected function setLanguage(): void
    {
        $this->linkDB->query("SET NAMES 'utf8'") or $this->dbError();
    }

    /**
     *
     * @param CL_parkinglot $ObjToBeInserted
     * @return int row ID
     */
    public function insert($ObjToBeInserted): int
    {
        if (!($ObjToBeInserted instanceof CL_parkinglot)) {
            throw new InvalidArgumentException;
        }
        parent::insert($ObjToBeInserted);
        mysqli_query($this->linkDB, 'INSERT INTO `' . self::TABLE_NAME . '` ' . self::FULL_INSERT . chr(0xa)
            . 'VALUES ' . $this->insertSql($ObjToBeInserted) . ';')
        or $this->dbError();
        return mysqli_insert_id($this->linkDB);
    }

    /**
     *
     * @param CL_parkinglot[] $ObjsToBeInserted
     * @return int last row inserted |int Class error num
     * @throws Exception
     */
    public function inserts(array $ObjsToBeInserted): int
    {
        parent::inserts($ObjsToBeInserted);
        $sql = 'INSERT INTO `' . self::TABLE_NAME . '` ' . self::FULL_INSERT . chr(0xa);
        $i = false;
        foreach ($ObjsToBeInserted as $event) {
            if (!($event instanceof CL_parkinglot)) {
                throw new InvalidArgumentException;
            }
            if ($i) {
                $sql .= ',' . chr(0xa);
            } else {
                $sql .= 'VALUES ';
            }
            $sql .= $this->insertSql($event);
            $i = true;
        }
        $sql .= ';';
        mysqli_query($this->linkDB, $sql) or $this->dbError();

        $lastID = mysqli_insert_id($this->linkDB);
        if ($lastID < 1) {
            error_log('inserts last inserted ID error: ' . $lastID);
            error_log(mysqli_error($this->linkDB));
            return self::ERROR;
        }
        return $lastID;
    }

    /**
     * Searches DB for instance of CL_parkinglot
     * @param CL_parkinglot $log
     * @return CL_parkinglot|int Object on success, int with error on failure
     * @throws Exception
     */
    public function searchWithoutID($log)
    {
        throw new Exception('Not implemented');
    }

    /**
     * @param array $carPosition
     * @param int $distanceMeters
     * @param int $limit
     * @return array
     * @todo check if meters are really meters and not some random unit
     */
    public function searchNearestFreeParking(array $carPosition, int $distanceMeters = 500, int $limit = 5): array
    {
        $limit = min($limit, self::MAX_PER_SELECT);
        $sql = 'SELECT ' . self::FULL_SELECT . ', ST_Distance_Sphere(geopos, POINT(' . $carPosition[1] . ', ' . $carPosition[0] . ')) AS distancee ' . chr(0xa)
            . 'FROM `' . self::TABLE_NAME . '` ' . chr(0xa)
            . 'WHERE `car_capacity` > 0 ' . chr(0xa)
            . 'AND ST_Distance_Sphere(geopos, POINT(' . $carPosition[1] . ', ' . $carPosition[0] . ')) <= ' . $distanceMeters . ' ' . chr(0xa)
            . 'ORDER BY distancee ASC ' . chr(0xa)
            . "LIMIT $limit;";

            // var_dump($sql);
            // exit(0);
        $result = mysqli_query($this->linkDB, $sql);
        $parkingLots = [];
        while ($row = mysqli_fetch_object($result)) {
            $parkingLots[] = $this->rowToObj($row);
        }
        return $parkingLots;
    }

    /**
     *
     * shows collected data per day within the provided interval
     * @param int $from
     * @param int $to
     * @return array|int [unix_timestamp, count]
     * @throws InvalidArgumentException
     */
    public
    function selectTimeDependent(int $from, int $to): int|array
    {
        if ($from <= 0 || $to <= 0 || $to < $from) {
            throw new InvalidArgumentException;
        }
        try {
            $sql = 'SELECT ' . self::FULL_SELECT . ' FROM `' . self::TABLE_NAME . "` " . chr(0xa)
                . "WHERE current_time >= FROM_UNIXTIME($from) " . chr(0xa)
                . "AND current_time <= FROM_UNIXTIME($to) " . chr(0xa)
                . "ORDER BY `" . self::TABLE_NAME . "` . `current_time`  ASC" . chr(0xa);

            $result = mysqli_query($this->linkDB, $sql);
            if (mysqli_num_rows($result) > 0) {
                return $this->resultToArray($result);
            }
            return self::IS_EMPTY;
        } catch (Exception $ex) {
            error_log(mysqli_error($this->linkDB));
            error_log($ex);
            exit(__CLASS__ . ' - ' . __FUNCTION__ . ' error');
        }
    }

    /**
     *
     * @param string[] $where ["req"=>"ID = 1", "sep"=>0]   |   sep 1 = OR, sep 0 = AND
     * @param string[] $orderBy
     * @param int $lowerLimit
     * @param int $higherLimit
     * #@return CL_parkinglot[] |int Class error num
     * @throws Exception
     */
    private
    function select(array $where, array $orderBy, bool $obASC, int $lowerLimit, int $higherLimit)
    {
        # throw not implemented
        throw new Exception('Not implemented');
    }

    public
    function rowToObj(\stdClass $row): CL_parkinglot
    {
        return new CL_parkinglot(
            $row->id,
            $row->car_capacity,
            $row->longitude,
            $row->latitude,
            $row->name,
            $row->description
        );
    }

    private function insertSql(CL_parkinglot $geopos):string
    {
        return "( {$geopos->getCarCapacity()} , POINT({$this->escpNull($geopos->getGeoposX())}, {$this->escpNull($geopos->getGeoposY())}, {$this->mres($geopos->getName())}, {$this->mres($geopos->getDescription())}) )";
    }

}