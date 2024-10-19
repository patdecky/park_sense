/* global Chart, changeBackgroundPlugin */

'use strict';

class statsChart {
    timeDiv = 1 * 60; // 5mins
    _dcChart = null;
    config = null;
    /* @var loader loadingScreen*/

    colorChart = '#7E60BF'
    colorBackground = '#D3EE98'
    colorText = 'black'

    constructor() {
        if (statsChart._instance) {
            return statsChart._instance;
        }
        statsChart._instance = this;
        //constructor here:
        //...

        let container = document.getElementById('chartCanvas');
    }

    /**
     * Creates and updates the distribution chart
     * @returns {undefined}
     */
    chartMaker() {
        Chart.defaults.color = this.colorText;
        let dhs = new dataHolderStatistics();
        let labels = [];
        let counted = [];
        let title = ["Statistiky"];

        if (dhs.statistics.length > 1) {
            dhs.statistics.forEach((stati) => {
                /** @var stati {Cl_statistics} */
                labels.push(stati.hours.toString().padStart(2, '0') + ':' + stati.minutes.toString().padStart(2, '0'));
                counted.push(stati.total_arrival_count);
            });
            if (labels.length < 1) {
                title.push("No data found for the time period");
            }
        } else {
            title.push('Time selection range too wide!');
        }

        const data = {
            labels: labels,
            datasets: [{
                label: 'Detected client devices',
                backgroundColor: this.colorChart,
                borderColor: this.colorChart,
                data: counted
            }]
        };

        this.config = {
            type: 'bar',
            data: data,
            options: {
                plugins: {
                    legend: {
                        /* reverse labels*/
                        reverse: true
                    },
                    title: {
                        display: true,
                        text: title
                    },
                    custom_canvas_background_color: {
                        color: this.colorBackground
                    }
                }
            },
            plugins: [changeBackgroundPlugin]
        };

        if (this._dcChart === null) {
            this._dcChart = new Chart(
                document.getElementById('chartCanvas'),
                this.config
            );
        } else {
            this._dcChart.data = this.config.data;
            this._dcChart.options = this.config.options;
            this._dcChart.update();
        }
    }
}

window.addEventListener('load', () => {
    let ddc = (new statsChart());
    let dhs = new dataHolderStatistics();

    dhs.newStatisticsLoadedAddObserver(() => {
        ddc.chartMaker();
    });

    ddc.chartMaker();
});