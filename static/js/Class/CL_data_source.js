'use strict';

class CL_data_source {
    id;
    parkinglot_id;
    type;
    source;

    constructor(id, parkinglot_id, type, source) {
        this.id = id;
        this.parkinglot_id = parkinglot_id;
        this.type = type;
        this.source = source;
    }
}