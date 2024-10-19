<?php

use PHPClass\CL_camera;

require_once __DIR__ . '/../Class/CL_camera.php';
require_once __DIR__ . '/DBH_abstract.php';
require_once __DIR__ . '/DBH_connection.php';

class DBH_camera extends DBH_abstract
{
    const TABLE_NAME = 'camera';
    const MAX_PER_SELECT = 64;
    const FULL_SELECT = '`id`, `parkinglot_id`, `address`';
    const FULL_INSERT = '(`parkinglot_id`, `address`)';
    protected array $tablesToLock = [self::TABLE_NAME];

    protected function setLanguage(): void
    {
        $this->linkDB->query("SET NAMES 'utf8'") or $this->dbError();
    }

    /**
     * Insert a CL_camera object into the database
     *
     * @param CL_camera $ObjToBeInserted
     * @return int row ID
     */
    public function insert($ObjToBeInserted): int
    {
        if (!($ObjToBeInserted instanceof CL_camera)) {
            throw new InvalidArgumentException;
        }
        parent::insert($ObjToBeInserted);
        $sql = 'INSERT INTO `' . self::TABLE_NAME . '` ' . self::FULL_INSERT . ' VALUES '
            . '(' . $ObjToBeInserted->getParkinglotId() . ', '
            . '"' . $ObjToBeInserted->getAddress() . '");';
        mysqli_query($this->linkDB, $sql) or $this->dbError();
        return mysqli_insert_id($this->linkDB);
    }

    /**
     * Insert multiple CL_camera objects into the database
     *
     * @param CL_camera[] $ObjsToBeInserted
     * @return int last row inserted |int Class error num
     * @throws Exception
     */
    public function inserts(array $ObjsToBeInserted): int
    {
        parent::inserts($ObjsToBeInserted);
        $sql = 'INSERT INTO `' . self::TABLE_NAME . '` ' . self::FULL_INSERT . chr(0xa);
        $i = false;
        foreach ($ObjsToBeInserted as $event) {
            if (!($event instanceof CL_camera)) {
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
     * Select a CL_camera object from the database by ID
     *
     * @param int $id
     * @return CL_camera|int Object on success, int with error on failure
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
     * Convert a database row to a CL_camera object
     *
     * @param \stdClass $row
     * @return CL_camera
     */
    public function rowToObj(\stdClass $row): CL_camera
    {
        return new CL_camera(
            $row->parkinglot_id,
            $row->address
        );
    }

    public function searchWithoutID($ObjToBeSearched)
    {
        if (!($ObjToBeSearched instanceof CL_camera)) {
            throw new InvalidArgumentException;
        }

        $address = $this->mres($ObjToBeSearched->getAddress());
        $sql = 'SELECT ' . self::FULL_SELECT . ' FROM `' . self::TABLE_NAME . '` WHERE `address` LIKE "%' . $address . '%" LIMIT ' . self::MAX_PER_SELECT . ';';
        $result = mysqli_query($this->linkDB, $sql) or $this->dbError();

        $cameras = [];
        while ($row = mysqli_fetch_object($result)) {
            $cameras[] = $this->rowToObj($row);
        }
        return $cameras;
    }


    private function insertSql(CL_camera $camera): string
    {
        return "( {$camera->getParkinglotId()} , '{$this->mres($camera->getAddress())}' )";
    }
}