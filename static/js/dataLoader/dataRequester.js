/* global DateMK2, QE */

'use strict';

/**
 * Requests data with fetchAPI and stores them into dataHolder
 */
class dataRequester extends dataHolderBase {
    constructor() {
        super();
        if (dataRequester._instance) {
            return dataRequester._instance;
        }
        dataRequester._instance = this;
        //constructor here:
        //...
    }

    //maximum time interval for data requests
    maxSeconds = 86400 * 3;

    hijackClientsOverride = false;

    _distroLoading = [];
    _filteredDataLoading = [];
    _filteredBusDataLoading = [];
    _loadOdisScraperLoading = [];
    _loadDataFilterFuncs = [];
    _loadBusDataFilterFuncs = [];

    /**
     * Add new callback for when distro (*.log.csv) is being loaded
     * @param {Function} callFunction
     * @returns {undefined}
     */
    newDistroLoadingListener(callFunction) {
        this._distroLoading.push(callFunction);
    }

    distroLoading() {
        this._distroLoading.forEach(el => {
            el.call();
        });
    }

    /**
     * Add new callback for when client and station data is being loaded
     * @param {Function} callFunction
     * @returns {undefined}
     */
    newfdLoadingListener(callFunction) {
        this._filteredDataLoading.push(callFunction);
    }

    fdLoading() {
        this._filteredDataLoading.forEach(el => {
            el.call();
        });
    }

    /**
     * Add new callback for when bus data is being loaded
     * @param {Function} callFunction
     * @returns {undefined}
     */
    newBdLoadingListener(callFunction) {
        this._filteredBusDataLoading.push(callFunction);
    }

    bdLoading() {
        this._filteredBusDataLoading.forEach(el => {
            el.call();
        });
    }

    /**
     * Add new callback for when bus data is being loaded
     * @param {Function} callFunction
     * @returns {undefined}
     */
    newOdisLoadingListener(callFunction) {
        this._loadOdisScraperLoading.push(callFunction);
    }

    odisLoading() {
        this._loadOdisScraperLoading.forEach(el => {
            el.call();
        });
    }

    //TODO add rest of the listeners
    _listeners = {
        extLogsLoading: [],
        clientsFromExtendedLogsLoading: [],
    };

    _fromToCheck(from, to) {
        return !(from > to || to <= 0 || from <= 0);
    }

    _fromToCheckDh() {
        return this._fromToCheck(DateMK2.fromDhFrom().getPhpTimestamp(), DateMK2.fromDhTo().getPhpTimestamp());
    }

    /**
     *
     * @param {Number}from
     * @param {Number}to
     * @param {Number}limit seconds
     * @returns {boolean}
     * @private
     */
    _fromToCheckWithLimit(from, to, limit) {
        return !(from > to || to <= 0 || from <= 0 || to - from > limit);
    }


    /**
     *
     * @param limit seconds
     * @returns {boolean}
     * @private
     */
    _fromToCheckDhWithLimit(limit) {
        return this._fromToCheckWithLimit(DateMK2.fromDhFrom().getPhpTimestamp(), DateMK2.fromDhTo().getPhpTimestamp(), limit);
    }

    async loadDistribution() {
        const dh = new dataHolder();
        this.distroLoading();

        if (!this._fromToCheckDh()) {
            return;
        }

        let params = this.getFromToDictionary({
            "distrMode": dh.distrMode,
            "location": dh.distrLocation
        });

        let ret = await fetchAPI.AllInOneReq("adDistribution.php", "distribution", params, 0);

        dh.distrData = [];
        let wdcs = {};
        const arrayFromPayload = Object.keys(ret).map(key => ({
            [key]: ret[key]
        }));
        arrayFromPayload.forEach(el => {
            const keys = Object.keys(el);
            const unixts = keys[0];
            Object.keys(el[unixts]).forEach(logType => {
                const counted = el[unixts][logType];

                if (wdcs[unixts] === undefined) {
                    wdcs[unixts] = [];
                }
                wdcs[unixts].push(new wifiDistributionCl(new Date(parseInt(unixts) * 1000), counted, logType));
            })
        });
        dh.distrData = wdcs;
        dh.distrChanged();
    }

    async loadLocations() {
        let dh = new dataHolder();
        let ret = await fetchAPI.AllInOneReq("adLocation.php", "sumLocation", {});

        dh.locations = [];
        ret.forEach(el => {
            dh.locations.push(new locationSum(el.position, el.clientPackets, el.stationBeacons));
        });
        dh.locationsChanged();
    }

    async loadLastGps() {
        let parObj = {
            req: "getLastDay",
            par: {}
        };
        await this._gpsReq(parObj);
    }

    async loadIntervalGps() {
        if (!this._fromToCheckDhWithLimit(this.maxSeconds)) {
            return;
        }
        let parObj = {
            req: "getInterval",
            par: this.getFromToDictionary()
        };
        this._gpsReq(parObj).then();
    }

    async _gpsReq(parObj) {
        let dh = new dataHolder();
        let ret = await fetchAPI.AllInOneReq("gpxLogger.php", parObj.req, parObj.par);

        dh.gpxData = [];
        ret.forEach(el => {
            dh.gpxData.push(new gpxLogger(el.ID, DateMK2.fromPhpTimestamp(parseInt(el.timestmp)), el.lat, el.lon, el.ele, el.fix, el.hdop, el.vdop, el.pdop));
        });
        dh.gpxChanged();
    }

    /**
     * Register a function that returns a dictionary of filters
     * @param func
     */
    registerDataFilter(func) {
        this._loadDataFilterFuncs.push(func);
    }

    getRegisteredFilters(dict = {}) {
        this._loadDataFilterFuncs.forEach(el => {

            let funcDict = el.call();
            if (funcDict === undefined) {
                throw "getRegisteredFilters - callback did not return anything!";
            }
            dict = Object.assign({}, dict, funcDict);
        });
        return dict;
    }

    getFromToDictionary(dict = {}) {
        //create dictionary with "from" and "to" keys
        let fromToDict = {"from": DateMK2.fromDhFrom().getPhpTimestamp(), "to": DateMK2.fromDhTo().getPhpTimestamp()};
        //merge with existing dictionary
        dict = Object.assign({}, dict, fromToDict);
        return dict;
    }

    async loadStatistics(macArr) {
        let dhs = new dataHolderStatistics();
        let ret = await fetchAPI.AllInOneReq("statistics.php", "getStatsForDay", {'day_w': dhs.day_w}, 0);
        if (ret.length <= 0) {
            //observer triggered in vendorList setter
            dhs.statistics = [];
            return
        }

        let mvs = [];
        ret.forEach(el => {
            mvs.push(new Cl_statistics(el.day_w, el.hours, el.minutes, el.total_arrival_count));
        });
        //observer triggered in vendorList setter
        dhs.setStatistics(mvs);
    }

    async loadParkingLots(lat, long, radius, limit) {
        let ret = await fetchAPI.AllInOneReq("parkingSpaces.php", "getNearestParkingLots", {'lat': lat, "long": long, "radius":radius, "limit":limit}, 0);
        if (ret.length <= 0) {
            //observer triggered in vendorList setter
            return
        }

        let mvs = [];
        ret.forEach(el => {
            mvs.push(new Cl_parkinglot(el.id, el.geopos_x, el.geopos_y, el.car_capacity, el.name));
        });
        //observer triggered in vendorList setter
        return mvs
    }

}