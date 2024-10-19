<?php

namespace PHPClass;

use PHPClass\CL_DBDataParser;
use ReturnTypeWillChange;

require_once __DIR__ . "/CL_DBDataParser.php";


class CL_statistics extends CL_DBDataParser
{

    protected int $day_w;
    protected int $hours;
    protected int $minutes;
    protected int $total_arrival_count;

    /**
     * @param int $day_w
     * @param int $hours
     * @param int $minutes
     * @param int $total_arrival_count
     */
    public function __construct(int $id, int $day_w, int $hours, int $minutes, int $total_arrival_count)
    {
        $this->ID = $id;
        $this->day_w = $day_w;
        $this->hours = $hours;
        $this->minutes = $minutes;
        $this->total_arrival_count = $total_arrival_count;
    }

    public function getDayW(): int
    {
        return $this->day_w;
    }

    public function setDayW(int $day_w): void
    {
        $this->day_w = $day_w;
    }

    public function getHours(): int
    {
        return $this->hours;
    }

    public function setHours(int $hours): void
    {
        $this->hours = $hours;
    }

    public function getMinutes(): int
    {
        return $this->minutes;
    }

    public function setMinutes(int $minutes): void
    {
        $this->minutes = $minutes;
    }

    public function getTotalArrivalCount(): int
    {
        return $this->total_arrival_count;
    }

    public function setTotalArrivalCount(int $total_arrival_count): void
    {
        $this->total_arrival_count = $total_arrival_count;
    }


    public function count(): int
    {
        return count(get_object_vars($this));
    }
}