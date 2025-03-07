<?php

namespace PHPClass;

use PHPClass\CL_DBDataParser;

require_once __DIR__ . "/CL_DBDataParser.php";


class CL_data_source extends CL_DBDataParser
{
    public int $parkinglot_id;
    public int $type;
    public string $source;

    public function __construct(int $ID, int $parkinglot_id, int $type, string $source)
    {
        $this->ID = $ID;
        $this->parkinglot_id = $parkinglot_id;
        $this->type = $type;
        $this->source = $source;
    }

    public function count(): int
    {
        return count(get_object_vars($this));
    }
}