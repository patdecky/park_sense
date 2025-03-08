<?php

namespace PHPClass;

use PHPClass\CL_DBDataParser;

require_once __DIR__ . "/CL_DBDataParser.php";


class CL_occupancy_community extends CL_DBDataParser
{
    public int $occupancy;
    public \DateTime $current_timestamp;
    public int $parkinglot_id;

    public function __construct(int $ID, int $parkinglot_id, int $occupancy, \DateTime $current_timestamp)
    {
        $this->ID = $ID;
        $this->occupancy = $occupancy;
        $this->current_timestamp = $current_timestamp;
        $this->parkinglot_id = $parkinglot_id;
    }

    public function count(): int
    {
        return count(get_object_vars($this));
    }
}