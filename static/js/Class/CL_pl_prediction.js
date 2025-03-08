'use strict';

class CL_pl_prediction {
    id;
    parkinglot_id;
    vacancy;
    day;
    day_timestamp;

    constructor(id, parkinglot_id, vacancy, day, day_timestamp) {
        this.id = id;
        this.parkinglot_id = parkinglot_id;
        this.vacancy = vacancy;
        this.day = day;
        this.day_timestamp = day_timestamp;
    }
}