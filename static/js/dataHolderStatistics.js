'use strict'

class dataHolderStatistics extends dataHolderBase {
    day_w = -1;
    statistics = {};

    constructor() {
        super();
        if (dataHolderStatistics._instance) {
            return dataHolderStatistics._instance;
        }
        dataHolderStatistics._instance = this;

        this.newListenerType('statisticsLoaded');
        this.setStatisticsDay(this.getCurrentWeekday());
    }

    _statisticsRequestTimer;

    newStatisticsLoadedAddObserver(callback) {
        this.addListener('statisticsLoaded', callback);
    }

    newStatisticsLoadedCallback() {
        this.notifyListeners('statisticsLoaded');
    }


    getCurrentWeekday() {
        const date = new Date();
        const day = date.getDay();
        return day === 0 ? 7 : day; // Adjust Sunday (0) to be 7
    };


    setStatisticsDay(day_w) {
        this.day_w = day_w;
        clearTimeout(this._statisticsRequestTimer);
        this._statisticsRequestTimer = setTimeout(async () => {
            await (new dataRequester()).loadStatistics();
            this.newStatisticsLoadedCallback();
        }, 1000);
    }

    setStatistics(statistics) {
        this.statistics = statistics;
        this.notifyListeners('statisticsLoaded');
    }
}

window.addEventListener('load', () => {
    dataHolderStatistics._instance = new dataHolderStatistics();
});