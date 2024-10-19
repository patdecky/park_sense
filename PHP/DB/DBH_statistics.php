<?php

use PHPClass\CL_statistics;

require_once __DIR__ . '/../Class/CL_statistics.php';
require_once __DIR__ . '/DBH_abstract.php';
require_once __DIR__ . '/DBH_connection.php';

class DBH_statistics extends DBH_abstract
{
    const TABLE_NAME = 'statistics';
    const MAX_PER_SELECT = 4096;
    const FULL_SELECT = '`id`, `day_w`, `hours`, `minutes`, `total_arrival_count`';
    const FULL_INSERT = '(`day_w`, `hours`, `minutes`, `total_arrival_count`)';
    protected array $tablesToLock = [self::TABLE_NAME];

    protected function setLanguage(): void
    {
        $this->linkDB->query("SET NAMES 'utf8'") or $this->dbError();
    }

    /**
     * Insert a CL_statistics object into the database
     *
     * @param CL_statistics $ObjToBeInserted
     * @return int row ID
     */
    public function insert($ObjToBeInserted): int
    {
        if (!($ObjToBeInserted instanceof CL_statistics)) {
            throw new InvalidArgumentException;
        }
        parent::insert($ObjToBeInserted);
        $sql = 'INSERT INTO `' . self::TABLE_NAME . '` ' . self::FULL_INSERT . ' VALUES '
            . '(' . $ObjToBeInserted->getDayW() . ', '
            . $ObjToBeInserted->getHours() . ', '
            . $ObjToBeInserted->getMinutes() . ', '
            . $ObjToBeInserted->getTotalArrivalCount() . ');';
        mysqli_query($this->linkDB, $sql) or $this->dbError();
        return mysqli_insert_id($this->linkDB);
    }

    /**
     * Insert multiple CL_statistics objects into the database
     *
     * @param CL_statistics[] $ObjsToBeInserted
     * @return int last row inserted |int Class error num
     * @throws Exception
     */
    public function inserts(array $ObjsToBeInserted): int
    {
        parent::inserts($ObjsToBeInserted);
        $sql = 'INSERT INTO `' . self::TABLE_NAME . '` ' . self::FULL_INSERT . chr(0xa);
        $i = false;
        foreach ($ObjsToBeInserted as $event) {
            if (!($event instanceof CL_statistics)) {
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
     * Select a CL_statistics object from the database by ID
     *
     * @param int $id
     * @return CL_statistics|int Object on success, int with error on failure
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
     * Convert a database row to a CL_statistics object
     *
     * @param \stdClass $row
     * @return CL_statistics
     */
    public function rowToObj(\stdClass $row): CL_statistics
    {
        return new CL_statistics(
            $row->id,
            $row->day_w,
            $row->hours,
            $row->minutes,
            $row->total_arrival_count
        );
    }

    public function searchWithoutID($ObjToBeSearched){
        throw new Exception('Not implemented');
    }
    public function searchByDayW(int $day_w)
    {
        $sql = 'SELECT ' . self::FULL_SELECT . ' FROM `' . self::TABLE_NAME . '` WHERE `day_w` = ' . $day_w . ' LIMIT ' . self::MAX_PER_SELECT . ';';
        $result = mysqli_query($this->linkDB, $sql) or $this->dbError();

        $statistics = [];
        while ($row = mysqli_fetch_object($result)) {
            $statistics[] = $this->rowToObj($row);
        }
        return $statistics;
    }

    private function insertSql(CL_statistics $statistics): string
    {
        return "( {$statistics->getDayW()} , {$statistics->getHours()} , {$statistics->getMinutes()} , {$statistics->getTotalArrivalCount()} )";
    }
}