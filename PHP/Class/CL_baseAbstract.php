<?php

namespace PHPClass;
abstract class CL_baseAbstract {

    protected int $ID;

    public function getID(): int {
        return $this->ID;
    }

    public function setID($ID): void {
        $this->ID = $ID;
    }

}
