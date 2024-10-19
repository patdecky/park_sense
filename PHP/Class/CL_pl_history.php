<?php

namespace PHPClass;

use PHPClass\CL_DBDataParser;
use ReturnTypeWillChange;

require_once __DIR__ . "/CL_DBDataParser.php";


class CL_pl_history extends CL_DBDataParser implements \JsonSerializable
{
    private int $parkinglot_id;
    /**
     * @var int
     * @required
     * @brief free parking spaces for car
     * @brief cannot be above Cl_parkinglot.car_capacity
     */
    private int $vacancy;

    private \DateTime $current_timestamp;

    /**
     * @param int $parkinglot_id
     * @param int $vacancy
     * @param \DateTime $current_timestamp
     */
    public function __construct(int $parkinglot_id, int $vacancy, \DateTime $current_timestamp)
    {
        $this->parkinglot_id = $parkinglot_id;
        $this->vacancy = $vacancy;
        $this->current_timestamp = $current_timestamp;
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

    public function getCurrentTimestamp(): \DateTime
    {
        return $this->current_timestamp;
    }

    public function setCurrentTimestamp(\DateTime $current_timestamp): void
    {
        $this->current_timestamp = $current_timestamp;
    }

    public function count(): int
    {
        return count(get_object_vars($this));
    }
}