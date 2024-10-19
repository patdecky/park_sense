<?php

namespace PHPClass;

use PHPClass\CL_DBDataParser;

require_once __DIR__ . "/CL_DBDataParser.php";


class CL_parkinglot extends CL_DBDataParser
{
    private int $geopos_x;
    private int $geopos_y;
    private int $car_capacity;

    /**
     * @param int $geopos_x
     * @param int $geopos_y
     * @param int $car_capacity
     */
    public function __construct(int $id, int $car_capacity, int $geopos_x, int $geopos_y)
    {
        $this->ID = $id;
        $this->geopos_x = $geopos_x;
        $this->geopos_y = $geopos_y;
        $this->car_capacity = $car_capacity;
    }

    public static function pointToXY(int $id, int $car_capacity, array $point): CL_parkinglot
    {
        return new CL_parkinglot($id, $car_capacity, $point[0], $point[1]);
    }

    public function getGeoposX(): int
    {
        return $this->geopos_x;
    }

    public function setGeoposX(int $geopos_x): void
    {
        $this->geopos_x = $geopos_x;
    }

    public function getGeoposY(): int
    {
        return $this->geopos_y;
    }

    public function setGeoposY(int $geopos_y): void
    {
        $this->geopos_y = $geopos_y;
    }

    public function getCarCapacity(): int
    {
        return $this->car_capacity;
    }

    public function setCarCapacity(int $car_capacity): void
    {
        $this->car_capacity = $car_capacity;
    }


    public function count(): int
    {
        return count(get_object_vars($this));
    }
}