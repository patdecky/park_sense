/* global Chart, changeBackgroundPlugin */

'use strict';

class statsChart {
    // timeDiv = 1 * 60; // 5mins
    _dcChart = null;
    config = null;
    // perHour = true;
    container = null;

    colorChart = 'blue'
    colorBackground = 'white'
    colorText = 'black'

    constructor() {
        if (statsChart._instance) {
            return statsChart._instance;
        }
        statsChart._instance = this;
        //constructor here:
        //...

        this.container = document.getElementById('chartCanvas');

        // Add event listener to the container
        this.container.addEventListener('click', () => {
            // this.perHour = !this.perHour; // Toggle perHour
            this.chartMaker(); // Call chartMaker
        });
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

            // const hourMap = new Map();

            dhs.statistics.forEach((stati) => {
                // convert seconds to the HH:MM format
                let hour = Math.floor(stati.day_timestamp / 3600);
                let minutes = Math.floor((stati.day_timestamp % 3600) / 60);

                labels.push(`${hour}:${minutes}`);
                counted.push(stati.vacancy);
            });

            if (labels.length < 1) {
                title.push("Žádná data pro dané parkoviště");
                // hide chart if empty
                if (location.hash === '#in_time') {
                    location.hash = '';
                    document.getElementById('chartContainer').style.display='none';
                }
            }

        } else {
            title.push('Žádná data pro dané parkoviště');
            if (location.hash === '#in_time') {
                location.hash = '';
                document.getElementById('chartContainer').style.display='none';
            }
        }

        const data = {
            labels: labels,
            datasets: [{
                label: 'Predpovedeny pocet aut',
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
                this.container,
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

    if (location.hash === '#in_time') {
        location.hash = '';
    }
});