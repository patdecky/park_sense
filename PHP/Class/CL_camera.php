<?php

namespace PHPClass;

use PHPClass\CL_DBDataParser;

require_once __DIR__ . "/CL_DBDataParser.php";


class CL_camera extends CL_DBDataParser
{
    private int $parkinglot_id;
    private string $address;

    /**
     * @param int $parkinglot_id
     * @param string $address
     */
    public function __construct(int $parkinglot_id, string $address)
    {
        $this->parkinglot_id = $parkinglot_id;
        $this->address = $address;
    }

    public function getParkinglotId(): int
    {
        return $this->parkinglot_id;
    }

    public function setParkinglotId(int $parkinglot_id): void
    {
        $this->parkinglot_id = $parkinglot_id;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }



    public function count(): int
    {
        return count(get_object_vars($this));
    }
}