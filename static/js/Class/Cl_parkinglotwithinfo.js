'use strict';

class Cl_parkinglotwithinfo {
    id;
    geopos_x;
    geopos_y;
    car_capacity;
    name;
    description;
    vacancy;
    predicted_vacancy;
    community_vacancy;

    constructor(id, geopos_x, geopos_y, car_capacity, name, description, vacancy, predicted_vacancy, community_vacancy) {
        this.id = id;
        this.geopos_x = geopos_x;
        this.geopos_y = geopos_y;
        this.car_capacity = car_capacity;
        this.name = name;
        this.description = description;
        this.vacancy = vacancy;
        this.predicted_vacancy = predicted_vacancy;
        this.community_vacancy = community_vacancy;
    }
}
