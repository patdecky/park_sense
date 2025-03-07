<?php

namespace PHPClass;

use PHPClass\CL_DBDataParser;
use ReturnTypeWillChange;

require_once __DIR__ . "/CL_DBDataParser.php";


class CL_pl_prediction extends CL_DBDataParser implements \JsonSerializable
{
    protected int $parkinglot_id;
    /**
     * @var int
     * @required
     * @brief free parking spaces for car
     * @brief cannot be above Cl_parkinglot.car_capacity
     */
    protected int $vacancy;

    protected int $day;

    /**
     * seconds in the day
     * @var int $day_timestamp
     */
    protected int $day_timestamp;

    /**
     * @param int $parkinglot_id
     * @param int $vacancy
     */
    public function __construct(int $parkinglot_id, int $vacancy, int $day, int $day_timestamp)
    {
        $this->parkinglot_id = $parkinglot_id;
        $this->vacancy = $vacancy;
        $this->day = $day;
        $this->day_timestamp = $day_timestamp;
    }

    public function getDayTimestamp(): int
    {
        return $this->day_timestamp;
    }

    public function setDayTimestamp(int $day_timestamp): void
    {
        $this->day_timestamp = $day_timestamp;
    }

    public function getDay(): int
    {
        return $this->day;
    }

    public function setDay(int $day): void
    {
        $this->day = $day;
    }


    public function getParkinglotId(): int
    {
        return $this->parkinglot_id;
    }

    public function setParkinglotId(int $parkinglot_id): void
    {
        $this->parkinglot_id = $parkinglot_id;
    }

    public function getVacancy(): int
    {
        return $this->vacancy;
    }

    public function setVacancy(int $vacancy): void
    {
        $this->vacancy = $vacancy;
    }

    public function count(): int
    {
        return count(get_object_vars($this));
    }
}