<?php

use PHPClass\CL_data_source;

require_once __DIR__ . '/../Class/CL_data_source.php';
require_once __DIR__ . '/DBH_abstract.php';
require_once __DIR__ . '/DBH_connection.php';

class DBH_data_source extends DBH_abstract
{
    const TABLE_NAME = 'data_source';
    const MAX_PER_SELECT = 64;
    const FULL_SELECT = '`id`, `parkinglot_id`, `type`, `source`';
    const FULL_INSERT = '(`parkinglot_id`, `type`, `source`)';
    protected array $tablesToLock = [self::TABLE_NAME];

    protected function setLanguage(): void
    {
        $this->linkDB->query("SET NAMES 'utf8'") or $this->dbError();
    }

    /**
     * Insert a CL_data_source object into the database
     *
     * @param CL_data_source $ObjToBeInserted
     * @return int row ID
     */
    public function insert($ObjToBeInserted): int
    {
        if (!($ObjToBeInserted instanceof CL_data_source)) {
            throw new InvalidArgumentException;
        }
        parent::insert($ObjToBeInserted);
        $sql = 'INSERT INTO `' . self::TABLE_NAME . '` ' . self::FULL_INSERT . ' VALUES '
            . '(' . $ObjToBeInserted->parkinglot_id . ', '
            . $ObjToBeInserted->type . ', '
            . '"' . $ObjToBeInserted->source . '");';
        mysqli_query($this->linkDB, $sql) or $this->dbError();
        return mysqli_insert_id($this->linkDB);
    }

    /**
     * Insert multiple CL_data_source objects into the database
     *
     * @param CL_data_source[] $ObjsToBeInserted
     * @return int last row inserted |int Class error num
     * @throws Exception
     */
    public function inserts(array $ObjsToBeInserted): int
    {
        parent::inserts($ObjsToBeInserted);
        $sql = 'INSERT INTO `' . self::TABLE_NAME . '` ' . self::FULL_INSERT . chr(0xa);
        $i = false;
        foreach ($ObjsToBeInserted as $event) {
            if (!($event instanceof CL_data_source)) {
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
     * Select a CL_data_source object from the database by ID
     *
     * @param int $id
     * @return CL_data_source|int Object on success, int with error on failure
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
     * Convert a database row to a CL_data_source object
     *
     * @param \stdClass $row
     * @return CL_data_source
     */
    public function rowToObj(\stdClass $row): CL_data_source
    {
        return new CL_data_source(
            $row->id,
            $row->parkinglot_id,
            $row->type,
            $row->source
        );
    }

    public function searchWithoutID($ObjToBeSearched)
    {
        if (!($ObjToBeSearched instanceof CL_data_source)) {
            throw new InvalidArgumentException;
        }

        $source = $this->mres($ObjToBeSearched->source);
        $sql = 'SELECT ' . self::FULL_SELECT . ' FROM `' . self::TABLE_NAME . '` WHERE `source` LIKE "%' . $source . '%" LIMIT ' . self::MAX_PER_SELECT . ';';
        $result = mysqli_query($this->linkDB, $sql) or $this->dbError();

        $dataSources = [];
        while ($row = mysqli_fetch_object($result)) {
            $dataSources[] = $this->rowToObj($row);
        }
        return $dataSources;
    }

    private function insertSql(CL_data_source $dataSource): string
    {
        return "( {$dataSource->parkinglot_id} , {$dataSource->type} , '{$this->mres($dataSource->source)}' )";
    }
}