'use strict';

class Cl_pl_history{
    id;
    parkinglot_id;
    vacancy;
    current_timestamp;

    constructor(id, parkinglot_id, vacancy, current_timestamp) {
        this.id = id;
        this.parkinglot_id = parkinglot_id;
        this.vacancy = vacancy;
        this.current_timestamp = new DateMK2(new Date(current_timestamp));
    }

}
