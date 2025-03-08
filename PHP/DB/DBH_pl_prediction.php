<?php

use PHPClass\CL_pl_prediction;

require_once __DIR__ . '/../Class/CL_pl_prediction.php';
require_once __DIR__ . '/DBH_abstract.php';
require_once __DIR__ . '/DBH_connection.php';

class DBH_pl_prediction extends DBH_abstract
{
    const TABLE_NAME = 'pl_prediction';
    const MAX_PER_SELECT = 1024;
    const FULL_SELECT = '`id`, `parkinglot_id`, `vacancy`, `day`, `day_timestamp`';
    const FULL_INSERT = '(`parkinglot_id`, `vacancy`, `day`, `day_timestamp`)';
    protected array $tablesToLock = [self::TABLE_NAME];

    protected function setLanguage(): void
    {
        $this->linkDB->query("SET NAMES 'utf8'") or $this->dbError();
    }

    /**
     * Insert a CL_pl_prediction object into the database
     *
     * @param CL_pl_prediction $ObjToBeInserted
     * @return int row ID
     */
    public function insert($ObjToBeInserted): int
    {
        if (!($ObjToBeInserted instanceof CL_pl_prediction)) {
            throw new InvalidArgumentException;
        }
        parent::insert($ObjToBeInserted);
        $sql = 'INSERT INTO `' . self::TABLE_NAME . '` ' . self::FULL_INSERT . ' VALUES '
            . '(' . $ObjToBeInserted->getParkinglotId() . ', '
            . $ObjToBeInserted->getVacancy() . ', '
            . $ObjToBeInserted->getDay() . ', '
            . $ObjToBeInserted->getDayTimestamp() . ');';
        mysqli_query($this->linkDB, $sql) or $this->dbError();
        return mysqli_insert_id($this->linkDB);
    }

    /**
     * Insert multiple CL_pl_prediction objects into the database
     *
     * @param CL_pl_prediction[] $ObjsToBeInserted
     * @return int last row inserted |int Class error num
     * @throws Exception
     */
    public function inserts(array $ObjsToBeInserted): int
    {
        parent::inserts($ObjsToBeInserted);
        $sql = 'INSERT INTO `' . self::TABLE_NAME . '` ' . self::FULL_INSERT . chr(0xa);
        $i = false;
        foreach ($ObjsToBeInserted as $event) {
            if (!($event instanceof CL_pl_prediction)) {
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
     * Select a CL_pl_prediction object from the database by ID
     *
     * @param int $id
     * @return CL_pl_prediction|int Object on success, int with error on failure
     * @throws DateMalformedStringException
     */
    public function selectById(int $id)
    {
        $sql = 'SELECT ' . self::FULL_SELECT . ' FROM `' . self::TABLE_NAME . '` WHERE `id` = ' . $id . ' LIMIT 1;';
        $result = mysqli_query($this->linkDB, $sql) or $this->dbError();

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_object($result);
            return $this->rowToObj($row);
        } else {
            return self::IS_EMPTY;
        }
    }

    public function selectByDayAndSecondsInterval(int $parkinglot_id, int $day, int $dayTimestamp){
        $sql = 'SELECT ' . self::FULL_SELECT . ', CAST(`day_timestamp` AS SIGNED) as signed_day_timestamp FROM `' . self::TABLE_NAME . '` WHERE `day` = ' . $day . ' AND `parkinglot_id` = ' . $parkinglot_id . '  ORDER BY ABS(signed_day_timestamp - ' . $dayTimestamp . ') LIMIT 1;';
        $result = mysqli_query($this->linkDB, $sql) or $this->dbError();

        $return = [];
        while ($row = mysqli_fetch_object($result)) {
            $return[] = $this->rowToObj($row);
        }
        return $return;
    }
    /**
     * Convert a database row to a CL_pl_prediction object
     *
     * @param \stdClass $row
     * @return CL_pl_prediction
     * @throws DateMalformedStringException
     */
    public function rowToObj(\stdClass $row): CL_pl_prediction
    {
        return new CL_pl_prediction(
            $row->parkinglot_id,
            $row->vacancy,
            $row->day,
            $row->day_timestamp
        );
    }

    /**
     * @throws Exception
     */
    function searchWithoutID($ObjToBeSearched)
    {
        // TODO: Implement searchWithoutID() method.
        throw new Exception('Not implemented');
    }

    private function insertSql(CL_pl_prediction $prediction): string
    {
        return "( {$prediction->getParkinglotId()} , {$prediction->getVacancy()} , {$prediction->getDay()} , {$prediction->getDayTimestamp()} )";
    }
}