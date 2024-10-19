<?php
require_once __DIR__ . '/DBH_interface.php';
require_once __DIR__ . '/DBH_connection.php';

abstract class DBH_abstract extends DBH_connection implements DBH_interface {

    /**
     * @param array $ObjsToBeInserted
     * @return int
     * @throws Exception
     */
    public function inserts(array $ObjsToBeInserted): int {
        if (!count($ObjsToBeInserted)) {
            throw new Exception('Array Empty!   ' . __METHOD__);
        }
        return $this->lockingTransaction();
    }

    public function insert($ObjToBeInserted): int {
        return $this->lockingTransaction();
    }

    public function resultToArray($result): array|int {
        if (mysqli_num_rows($result) > 0) {
            $logs = array();
            while ($tmp = mysqli_fetch_object($result)) {
                $logs[] = $this->rowToObj($tmp);
            }
            return $logs;
        } else {
            return self::IS_EMPTY;
        }
    }


}