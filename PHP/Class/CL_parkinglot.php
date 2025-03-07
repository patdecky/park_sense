<?php

namespace PHPClass;

use PHPClass\CL_DBDataParser;

require_once __DIR__ . "/CL_DBDataParser.php";


class CL_parkinglot extends CL_DBDataParser
{
    protected float $geopos_x;
    protected float $geopos_y;
    protected int $car_capacity;
    protected string $name;
    protected string|null $description;

    /**
     * @param float $geopos_x
     * @param float $geopos_y
     * @param int $car_capacity
     * @param string $name
     * @param string|null $description
     */
    public function __construct(int $id, int $car_capacity, float $geopos_x, float $geopos_y, string $name, string|null $description)
    {
        $this->ID = $id;
        $this->geopos_x = $geopos_x;
        $this->geopos_y = $geopos_y;
        $this->car_capacity = $car_capacity;
        $this->name = $name;
        $this->description = $description;
    }

    public static function pointToXY(int $id, int $car_capacity, array $point): CL_parkinglot
    {
        return new CL_parkinglot($id, $car_capacity, $point[0], $point[1]);
    }

    public function getGeoposX(): float
    {
        return $this->geopos_x;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setGeoposX(float $geopos_x): void
    {
        $this->geopos_x = $geopos_x;
    }

    public function getGeoposY(): float
    {
        return $this->geopos_y;
    }

    public function setGeoposY(float $geopos_y): void
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }




    public function count(): int
    {
        return count(get_object_vars($this));
    }
}