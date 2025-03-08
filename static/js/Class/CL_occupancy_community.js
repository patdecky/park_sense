'use strict';

class CL_occupancy_community{
    id;
    parkinglot_id;
    occupancy;
    current_timestamp;

    constructor(id, parkinglot_id, occupancy, current_timestamp){
        this.id = id;
        this.parkinglot_id = parkinglot_id;
        this.occupancy = occupancy;
        this.current_timestamp = current_timestamp;
    }
}