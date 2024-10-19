<?php

/**
 * These methods must be set up in DBH classes in order to work with the CSV scan
 */
interface DBH_interface {
    function insert($ObjToBeInserted): int;

    /**
     * Inserts() returns the row ID
     * It should be safe to assume that ids are mysql_insert_id + the next count(array) in succession,
     * as the statement is done in one transaction
     */
    function inserts(array $ObjsToBeInserted): int;

    function searchWithoutID($ObjToBeSearched);

    function rowToObj(\stdClass $row);

    function resultToArray($result): array|int;

    function lockingTransaction(): int;

    function commitTransaction(): int;

    function rollbackTransaction(): int;
}
