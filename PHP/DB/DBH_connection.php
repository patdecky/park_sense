<?php

use JetBrains\PhpStorm\NoReturn;
use JetBrains\PhpStorm\Deprecated;

require_once __DIR__ . '/../globals.php';

abstract class DBH_connection {

    const LOCKED_ALREADY = -3;
    const IS_EMPTY = -2;
    const ERROR = -1;
    const FOUND = 0;
    const QUERY_OK = 1;

    protected mysqli|null|false $linkDB = NULL;
    /**
     * @var bool[]
     */
    public static array $isLocked = [];
    public static bool $isTransactionOpen = false;
    protected array $tablesToLock = [];


    /**
     * call this after calling a procedure
     * important when the changes need to be committed
     * @return void
     */
    public function callProcedureCleanUp(): void {
        while (mysqli_more_results($this->linkDB) && mysqli_next_result($this->linkDB)) {
            $result = mysqli_use_result($this->linkDB);
            if ($result instanceof mysqli_result) {
                mysqli_free_result($result);
            }
        }
    }

    /**
     * LOCK TABLE commits the current transaction
     * https://mariadb.com/kb/en/sql-statements-that-cause-an-implicit-commit/
     * @return int
     */
    private function lockTables(): int {
        if (empty($this->tablesToLock)) {
            return DBH_connection::ERROR;
        }

        //prep lock tables $sql
        $sql = 'LOCK TABLES ';
        $moreTables = false;
        $merged = array_unique(array_merge($this::$isLocked, $this->tablesToLock));
        foreach ($merged as $tableName) {
            if ($moreTables) {
                $sql .= ', ' . chr(0xa);
            }
            $sql .= $tableName . ' WRITE CONCURRENT';
            $moreTables = true;
        }
        $sql .= ';';

        //execute unlock and lock tables
        $ret = mysqli_query($this->linkDB, 'UNLOCK TABLES;') && mysqli_query($this->linkDB, $sql);
        //update isLocked
        $this::$isLocked = $ret ? $merged : [];
        //return code
        return $ret ? DBH_connection::QUERY_OK : DBH_connection::ERROR;
    }

    public function lockingTransaction(): int {
        if (!self::$isTransactionOpen) {
            mysqli_begin_transaction($this->linkDB, MYSQLI_TRANS_START_READ_WRITE) === false and $this->dbError();
            mysqli_autocommit($this->linkDB, false) === false and $this->dbError();
            self::$isTransactionOpen = true;
        }
        // check if all tables "to be locked" are already locked
        if (count(array_diff($this->tablesToLock, $this::$isLocked)) === 0) {
            return self::LOCKED_ALREADY;
        }
        $this->lockTables() !== self::QUERY_OK and $this->dbError();
        return self::QUERY_OK;
    }

    public function unlockTables(): int {
        $ret = mysqli_query($this->linkDB, 'UNLOCK TABLES') !== false;
        $this::$isLocked = $ret ? [] : $this::$isLocked;
        return $ret ? self::QUERY_OK : self::ERROR;
    }

    public function endTransaction(): int {
        if (self::$isTransactionOpen) {
            mysqli_autocommit($this->linkDB, true) === false and $this->dbError();
            self::$isTransactionOpen = false;
        }
        return self::QUERY_OK;
    }

    public function commitTransaction(): int {
        return mysqli_commit($this->linkDB) ? DBH_connection::QUERY_OK : DBH_connection::ERROR;
    }

    public function rollbackTransaction(): int {
        return mysqli_rollback($this->linkDB) ? DBH_connection::QUERY_OK : DBH_connection::ERROR;
    }

    public function mres($s): ?string {
        //return mysqli_real_escape_string($this->linkDB, $s);
        //$esc = htmlentities($s, ENT_QUOTES);
        if (is_null($s)) {
            return null;
        }
        return mysqli_real_escape_string($this->linkDB, $s);
        //escape quotes
        #$esc = mysqli_real_escape_string($this->linkDB, $s);
        //convert to database string formatting
        #$result = mysqli_query($this->linkDB, "SELECT CONVERT( '$esc' , CHAR) as convr;") or $this->dbError();
        //get the new string and escape quotes once again
        #return mysqli_real_escape_string($this->linkDB,(mysqli_fetch_object($result))->convr);
    }

    #[Deprecated(
        reason: 'decoding not needed'
    )]
    public function mdec($s): string {
        return $s;
    }

    protected function escpNull(string|null $s): string {
        if (is_null($s)|| $s === '') {
            return 'NULL';
        }

        return $s;
    }

    /**
     * @param string|null $s
     * @return string
     */
    protected function escpQtNull(string|null $s): string {
        if (is_null($s) || $s === '') {
            return "NULL";
        }

        return "'$s'";
    }

    /**
     * either returns 'IS NULL' or "= '$s'"
     * @param string|null $s
     * @return string
     */
    protected function escpQtIsNull(string|null $s): string {
        if (is_null($s) || $s === '') {
            return 'IS NULL';
        }

        return '= ' . "'$s'";
    }

    /**
     * @param int|null $s
     * @return string
     */
    protected function escpIsNull(int|null $s): string {
        if (is_null($s)) {
            return 'IS NULL';
        }

        return '= ' . $s;
    }

    protected function boolToBit(bool|null $b): int|string {
        if (is_bool($b)) {
            return ((int)$b);
        }
        return 'NULL';
    }

    protected function escpIntNull(int|null $s): string {
        if (is_null($s)) {
            return 'NULL';
        }
        return $s;
    }

    protected function __construct(?mysqli $linkDB = NULL) {
        if (!is_null($linkDB)) {
            $this->linkDB = $linkDB;
            count($this::$isLocked) > 0 ? $this->lockingTransaction() : null;
            return;
        }

        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Set MySQLi to throw exceptions

            $link_conn = mysqli_connect(DB_IP . ':' . DB_PORT, DB_USER, DB_PASS);

            if ($link_conn === False) {
                die('DB error');
            }
            $this->linkDB = mysqli_connect(DB_IP . ':' . DB_PORT, DB_USER, DB_PASS, DB_DBNAME) or $this->dbError();

            $this->setLanguage();
            mb_internal_encoding('UTF-8');
        } catch (Exception $ex) {
            error_log(__CLASS__ . ' ' . __FUNCTION__ . ' ' . $ex->getMessage());
            die('DB error');
        }
        count($this::$isLocked) > 0 ? $this->lockingTransaction() : null;
    }

    protected function setLanguage(): void {
        $this->linkDB->set_charset('utf8mb4') or $this->dbError();
        // setting collation is optional and not needed 99% of time
        // only if you need a specific one, like in this case
        //$this->linkDB->query("SET collation_connection = utf8mb4_general_ci") or $this->dbError();
    }

    /**
     * getInstance that is ``extends`` compatible
     * returns instance of child class
     * if you want to share DB connection, you may call this class with
     * @staticvar array $instances
     * @param DBH_connection|null $otherInstance
     * @return static
     */
    final public static function getInstance(?DBH_connection $otherInstance = null): static {
        static $instances = array();

        $calledClass = get_called_class();

        if (!isset($instances[$calledClass])) {
            $instances[$calledClass] = new $calledClass($otherInstance->linkDB ?? null);
        }

        return $instances[$calledClass];
    }

    final protected function __clone() {

    }

    public function __destruct() {
        if (is_resource($this->linkDB) && get_resource_type($this->linkDB) === 'mysql link') {
            $this->rollbackTransaction();
            mysqli_close($this->linkDB);
        }
    }

    /**
     * converts table column to IP address
     * encapsulates the column in CASE statement
     * @param $columnName
     * @return string
     */
    protected function extractIpAddress($columnName): string {
        $columnName = urlencode($columnName);
        return "       
        CASE 
           WHEN `$columnName` IS NULL THEN NULL
           ELSE INET6_NTOA(`$columnName`)
        END AS $columnName
        ";
    }

    #[NoReturn]
    protected function dbError(): void {
        echo '<br><br>' . chr(0xa) . chr(0xa);
        if (!is_object($this->linkDB)) {
            die('DB error: NULL');
        }
        if (mysqli_errno($this->linkDB) == 1213) {
            header($GLOBALS['retHeader'][503]);
            die('DB says: please wait');
        }

        if (DB_DEBUG) {
            echo mysqli_errno($this->linkDB);
            echo '<br><br>' . chr(0xa) . chr(0xa);
            echo mysqli_error($this->linkDB);
            echo '<br><br>' . chr(0xa) . chr(0xa);
        }
        error_log(mysqli_error($this->linkDB));
        exit('Database Error');
    }

    public function getDbWarnings(): array|int {
        $warnings = [];
        $result = mysqli_query($this->linkDB, 'SHOW WARNINGS');
        if ($result === false) {
            return self::ERROR;
        }
        if (mysqli_num_rows($result) === 0) {
            return self::IS_EMPTY;
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $warnings[] = $row;
        }
        return $warnings;
    }

}

?>
