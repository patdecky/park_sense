'use strict'

class dataHolderStatistics extends dataHolderBase {
    statistics = [];

    constructor() {
        super();
        if (dataHolderStatistics._instance) {
            return dataHolderStatistics._instance;
        }
        dataHolderStatistics._instance = this;

        this.newListenerType('statisticsLoaded');
        return this._instance
    }

    _statisticsRequestTimer;

    newStatisticsLoadedAddObserver(callback) {
        this.addListener('statisticsLoaded', callback);
    }

    newStatisticsLoadedCallback() {
        this.notifyListeners('statisticsLoaded');
    }


    setStatisticsId(parkinglot_id) {
        clearTimeout(this._statisticsRequestTimer);
        this._statisticsRequestTimer = setTimeout(async () => {
            // await (new dataRequester()).loadStatistics();
            let ret = await (new dataRequester()).loadParkingLotsPredictedVacancyWholeDay(parkinglot_id);
            this.setStatistics(ret);
            // this.newStatisticsLoadedCallback();

        }, 1000);
    }

    setStatistics(statistics) {
        if (!statistics || statistics.length <= 0) {
            this.statistics = [];
            this.notifyListeners('statisticsLoaded');
            return;
        }
        this.statistics = statistics;
        this.notifyListeners('statisticsLoaded');
    }
}

window.addEventListener('load', () => {
    dataHolderStatistics._instance = new dataHolderStatistics();
});