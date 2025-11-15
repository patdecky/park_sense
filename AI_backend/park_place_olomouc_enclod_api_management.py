


def process_api_parking_lot(fraction_zone_vacant, parkinglot_id:int, parkinglot_capacity:int):

    
    if fraction_zone_vacant is None:
        return None
    estimated_vacancy = int(round(fraction_zone_vacant * parkinglot_capacity))
    return estimated_vacancy





