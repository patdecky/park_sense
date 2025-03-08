<?php

namespace DB;

use DBH_abstract;
use PHPClass\CL_occupancy_community;

require_once __DIR__ . '/../Class/CL_occupancy_community.php';
require_once __DIR__ . '/DBH_abstract.php';
require_once __DIR__ . '/DBH_connection.php';

class DBH_occupancy_community extends DBH_abstract
{
    const TABLE_NAME = 'occupancy_community';
    const MAX_PER_SELECT = 1024;
    const FULL_SELECT = '`id`, `occupancy`, `current_timestamp`, `parkinglot_id`';
    const FULL_INSERT = '(`occupancy`, `current_timestamp`, `parkinglot_id`)';
    protected array $tablesToLock = [self::TABLE_NAME];

    protected function setLanguage(): void
    {
        $this->linkDB->query("SET NAMES 'utf8'") or $this->dbError();
    }

    /**
     * Insert a CL_occupancy_community object into the database
     *
     * @param CL_occupancy_community $ObjToBeInserted
     * @return int row ID
     */
    public function insert($ObjToBeInserted): int
    {
        if (!($ObjToBeInserted instanceof CL_occupancy_community)) {
            throw new InvalidArgumentException;
        }
        parent::insert($ObjToBeInserted);
        $sql = 'INSERT INTO `' . self::TABLE_NAME . '` ' . self::FULL_INSERT . ' VALUES '
            . '(' . $ObjToBeInserted->occupancy . ', '
            . '"' . $ObjToBeInserted->current_timestamp->format('Y-m-d H:i:s') . '", '
            . $ObjToBeInserted->parkinglot_id . ');';
        mysqli_query($this->linkDB, $sql) or $this->dbError();
        return mysqli_insert_id($this->linkDB);
    }

    /**
     * Insert multiple CL_occupancy_community objects into the database
     *
     * @param CL_occupancy_community[] $ObjsToBeInserted
     * @return int last row inserted |int Class error num
     * @throws Exception
     */
    public function inserts(array $ObjsToBeInserted): int
    {
        parent::inserts($ObjsToBeInserted);
        $sql = 'INSERT INTO `' . self::TABLE_NAME . '` ' . self::FULL_INSERT . chr(0xa);
        $i = false;
        foreach ($ObjsToBeInserted as $event) {
            if (!($event instanceof CL_occupancy_community)) {
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
     * Select a CL_occupancy_community object from the database by ID
     *
     * @param int $id
     * @return CL_occupancy_community|int Object on success, int with error on failure
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

    /**
     * @throws DateMalformedStringException
     */
    public function getRecentHistory($parkinglot_id, $hoursBack = 12, $limit = self::MAX_PER_SELECT): array
    {
        $limit = min($limit, self::MAX_PER_SELECT);
        $sql = 'SELECT ' . self::FULL_SELECT . ' FROM `' . self::TABLE_NAME . '` ' . chr(0xa) .
            'WHERE `parkinglot_id` = ' . $parkinglot_id . ' AND ' . chr(0xa) .
            '`current_timestamp` > DATE_SUB(NOW(), INTERVAL ' . $hoursBack . ' HOUR) ' . chr(0xa) .
            'ORDER BY `current_timestamp` DESC ' . chr(0xa) .
            "LIMIT $limit;";
        $result = mysqli_query($this->linkDB, $sql) or $this->dbError();

        $history = [];
        while ($row = mysqli_fetch_object($result)) {
            $history[] = $this->rowToObj($row);
        }
        return $history;
    }

    public function getLastByParkingLotID(int $parkinglot_id): CL_occupancy_community|int
    {
        $sql = 'SELECT ' . self::FULL_SELECT . ' FROM `' . self::TABLE_NAME . '` WHERE `parkinglot_id` = ' . $parkinglot_id . ' ORDER BY `current_timestamp` DESC LIMIT 1;';
        $result = mysqli_query($this->linkDB, $sql) or $this->dbError();
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_object($result);
            return $this->rowToObj($row);
        }
        return self::IS_EMPTY;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function getHistoryInterval(int $parkinglot_id, \DateTime $start, \DateTime $end, int $limit = self::MAX_PER_SELECT): array
    {
        $limit = min($limit, self::MAX_PER_SELECT);
        $sql = 'SELECT ' . self::FULL_SELECT . ' FROM `' . self::TABLE_NAME . '` ' . chr(0xa) .
            'WHERE `parkinglot_id` = ' . $parkinglot_id . ' AND ' . chr(0xa) .
            '`current_timestamp` BETWEEN "' . $start->format('Y-m-d H:i:s') . '" AND "' . $end->format('Y-m-d H:i:s') . '" ' . chr(0xa) .
            'ORDER BY `current_timestamp` DESC ' . chr(0xa) .
            "LIMIT $limit;";
        $result = mysqli_query($this->linkDB, $sql) or $this->dbError();

        $history = [];
        while ($row = mysqli_fetch_object($result)) {
            $history[] = $this->rowToObj($row);
        }
        return $history;
    }

    /**
     * Convert a database row to a CL_occupancy_community object
     *
     * @param \stdClass $row
     * @return CL_occupancy_community
     * @throws DateMalformedStringException
     */
    public function rowToObj(\stdClass $row): CL_occupancy_community
    {
        return new CL_occupancy_community(
            $row->id,
            $row->occupancy,
            new \DateTime($row->current_timestamp),
            $row->parkinglot_id
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

    private function insertSql(CL_occupancy_community $occupancy): string
    {
        return "( {$occupancy->occupancy} , \"{$occupancy->current_timestamp->format('Y-m-d H:i:s')}\" , {$occupancy->parkinglot_id} )";
    }
}