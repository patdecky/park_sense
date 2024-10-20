<?php

use PHPClass\CL_pl_history;

require_once __DIR__ . '/../Class/CL_pl_history.php';
require_once __DIR__ . '/DBH_abstract.php';
require_once __DIR__ . '/DBH_connection.php';

class DBH_pl_history extends DBH_abstract
{
    const TABLE_NAME = 'pl_history';
    const MAX_PER_SELECT = 1024;
    const FULL_SELECT = '`id`, `parkinglot_id`, `vacancy`, `current_timestamp`';
    const FULL_INSERT = '(`parkinglot_id`, `vacancy`)';
    protected array $tablesToLock = [self::TABLE_NAME];

    protected function setLanguage(): void
    {
        $this->linkDB->query("SET NAMES 'utf8'") or $this->dbError();
    }

    /**
     * Insert a CL_pl_history object into the database
     *
     * @param CL_pl_history $ObjToBeInserted
     * @return int row ID
     */
    public function insert($ObjToBeInserted): int
    {
        if (!($ObjToBeInserted instanceof CL_pl_history)) {
            throw new InvalidArgumentException;
        }
        parent::insert($ObjToBeInserted);
        $sql = 'INSERT INTO `' . self::TABLE_NAME . '` ' . self::FULL_INSERT . ' VALUES '
            . '(' . $ObjToBeInserted->getParkinglotId() . ', '
            . $ObjToBeInserted->getVacancy() . ');';
        mysqli_query($this->linkDB, $sql) or $this->dbError();
        return mysqli_insert_id($this->linkDB);
    }

    /**
     * Insert multiple CL_pl_history objects into the database
     *
     * @param CL_pl_history[] $ObjsToBeInserted
     * @return int last row inserted |int Class error num
     * @throws Exception
     */
    public function inserts(array $ObjsToBeInserted): int
    {
        parent::inserts($ObjsToBeInserted);
        $sql = 'INSERT INTO `' . self::TABLE_NAME . '` ' . self::FULL_INSERT . chr(0xa);
        $i = false;
        foreach ($ObjsToBeInserted as $event) {
            if (!($event instanceof CL_pl_history)) {
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
     * Select a CL_pl_history object from the database by ID
     *
     * @param int $id
     * @return CL_pl_history|int Object on success, int with error on failure
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

    /**
     * @throws DateMalformedStringException
     */
    public function getHistoryInterval(int $parkinglot_id, \DateTime $start, \DateTime $end, int $limit = self::MAX_PER_SELECT): array
    {
        $limit = min($limit, self::MAX_PER_SELECT);
        $sql = 'SELECT ' . self::FULL_SELECT . ' FROM `' . self::TABLE_NAME . '` ' . chr(0xa) .
            'WHERE `parkinglot_id` = ' . $parkinglot_id . ' AND ' . chr(0xa) .
            '`current_timestamp` BETWEEN ' . $start->format('Y-m-d H:i:s') . ' AND ' . $end->format('Y-m-d H:i:s') . ' ' . chr(0xa) .
            'ORDER BY `current_timestamp` DESC ' . chr(0xa) .
            "LIMIT = $limit;";
        $result = mysqli_query($this->linkDB, $sql) or $this->dbError();

        $history = [];
        while ($row = mysqli_fetch_object($result)) {
            $history[] = $this->rowToObj($row);
        }
        return $history;
    }

    /**
     * Convert a database row to a CL_pl_history object
     *
     * @param \stdClass $row
     * @return CL_pl_history
     * @throws DateMalformedStringException
     */
    public function rowToObj(\stdClass $row): CL_pl_history
    {
        return new CL_pl_history(
            $row->parkinglot_id,
            $row->vacancy,
            new \DateTime($row->current_timestamp)
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

    private function insertSql(CL_pl_history $history): string
    {
        return "( {$history->getParkinglotId()} , {$history->getVacancy()} )";
    }
}