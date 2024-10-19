'use strict';

class Cl_parkinglot {
    id;
    geopos_x;
    geopos_y;
    car_capacity;
    name;

    constructor(id, geopos_x, geopos_y, car_capacity, name) {
        this.id = id;
        this.geopos_x = geopos_x;
        this.geopos_y = geopos_y;
        this.car_capacity = car_capacity;
        this.name = name;
    }
}
