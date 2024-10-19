'use strict';

class DateMK2 {
    time = null;

    /**
     *
     * @param {Date} t
     * @returns {DateMK2}
     * @throws {string} if t is not an instance of Date
     */
    constructor(t) {
        if (typeof t !== "object" || !(t instanceof Date))
            throw ("Date obj expected!");
        this.time = t;
        if (!DateMK2.isValidDate(this.time)) {
            throw ("Invalid date!");
        }
    }

    /**
     *
     * @param {Date}time
     * @returns {boolean}
     */
    static isValidDate(time) {
        return !isNaN(time.getTime());
    }

    /**
     *
     * @returns {DateMK2}
     */
    static fromNow() {
        return new DateMK2(new Date());
    }

    /**
     *
     * @param {number} seconds
     * @returns {DateMK2}
     */
    static fromNowMinusSeconds(seconds) {
        let d = DateMK2.fromNow();
        d.addSeconds(-seconds);
        return d;
    }

    /**
     *
     * @param {Number} t timestamp in seconds
     * @returns {DateMK2}
     */
    static fromPhpTimestamp(t) {
        //let tmz = (new Date()).getTimezoneOffset() * 60000;
        // return new DateMK2(new Date((new Date(t * 1000)).toUTCString()));
        return new DateMK2(new Date(t * 1000));
    }

    /**
     * subtract seconds from the date object
     * @param {number} seconds
     */
    addSeconds(seconds) {
        this.time.setTime(this.time.getTime() + seconds * 1000);
    }

    /**
     * subtract hours from the date object
     * @param {number} hours
     */
    minusHours(hours) {
        this.addSeconds(-hours * 60 * 60);
    }

    /**
     *
     * @returns {Number} Offset in seconds
     */
    static localTimezoneOffset() {
        return (new Date()).getTimezoneOffset() * 60;
    }

    /**
     * returns timezone offset in seconds
     * @param date
     * @returns {number} offset in seconds
     */
    static timezoneOffsetFromDate(date) {
        return date.getTimezoneOffset() * 60;
    }

    /**
     * Private method for creating Date objects and stripping them from timezones
     * @param {object} dEl input=date element
     * @returns {Date}
     */
    static _DateElHandle(dEl) {
        let d = new Date(dEl.value);
        return new Date(
            Math.round(
                d.getTime() / 1000 + this.timezoneOffsetFromDate(d)
            ) * 1000);
    }

    /**
     *
     * @param {object} dEl input=date element
     * @returns {DateMK2}
     */
    static fromDateInput(dEl) {
        return new DateMK2(
            this._DateElHandle(dEl)
        );
    }

    /**
     *
     * @param {object} dEl input=date element
     * @param {object} tEl input=time element
     * @returns {DateMK2}
     */
    static fromDateTimeInput(dEl, tEl) {
        let d = this._DateElHandle(dEl);
        let splt = tEl.value.split(':');
        let hours = parseInt(splt[0]);
        let minutes = parseInt(splt[1]);

        d.setMinutes(d.getMinutes() + minutes);
        d.setHours(d.getHours() + hours);

        return new DateMK2(d);
    }

    /**
     * extracts "from date" from the dataHolder object
     * @returns {DateMK2}
     */
    static fromDhFrom() {
        let dh = new dataHolder();
        return this.fromPhpTimestamp(dh.distrFrom);
    }

    /**
     * extracts "to date" from the dataHolder object
     * @returns {DateMK2}
     */
    static fromDhTo() {
        let dh = new dataHolder();
        return this.fromPhpTimestamp(dh.distrTo);
    }

    /**
     * returns date and time string - local
     * @returns {string}
     */
    getFullDateTimeString() {
        return (new Date(this.time.getTime() - (this.time.getTimezoneOffset() * 60000)).toISOString()).split('Z')[0];
    }

    /**
     * returns date ISO string - local
     * @returns {string}
     */
    getISO() {
        return this.getFullDateTimeString().split('T')[0];
    }

    /**
     * legacy
     * use getIso instead
     * @returns {string}
     */
    toInput() {
        return this.getISO();
    }

    /**
     * set desired time into the input=time element
     * @param {object} el input=time element
     * @returns {undefined}
     */
    setTimeInput(el) {
        el.value = ('0' + this.time.getHours()).slice(-2) + ':' + ('0' + this.time.getMinutes()).slice(-2);
    }

    /**
     * set desired date into the input=date element
     * @param {HTMLElement} el input=date element
     * @returns {undefined}
     */
    setDateInput(el) {
        // el.value = this.toInput();
        // el.value = this.time;
        // el.value = this.time.toISOString().split('T')[0];
        el.value = this.getISO();
    }

    /**
     * returns unix timestamp (in seconds)
     * @returns {Number}
     */
    getPhpTimestamp() {
        return Math.round(this.time.getTime() / 1000);
    }

    /**
     * splits date into array
     * @returns {{year: 0, month: 0, day: 0, hour: 0, minute: 0}}
     */
    getDateTimeArr() {
        let timeObj = {year: 0, month: 0, day: 0, hour: 0, minute: 0};
        timeObj.year = this.time.getFullYear();
        timeObj.month = ('0' + (this.time.getMonth() + 1)).slice(-2);
        timeObj.day = ('0' + this.time.getDate()).slice(-2);
        timeObj.hour = ('0' + this.time.getHours()).slice(-2);
        timeObj.minute = ('0' + this.time.getMinutes()).slice(-2);
        return timeObj;
    }

    /**
     *
     * @param {DateMK2} secondDate
     * @returns {Number}
     */
    getSecondsDiff(secondDate) {
        var dif = this.time.getTime() - secondDate.time.getTime();

        var Seconds_from_T1_to_T2 = dif / 1000;
        return Math.abs(Seconds_from_T1_to_T2);
    }

    /**
     * compares two dates
     * @param {DateMK2} secondDate
     * @returns {number} 1 if *this* is greater, -1 if *secondDate* is greater, 0 if equal
     * @throws {string} if secondDate is not an instance of DateMK2
     */
    compareDates(secondDate) {
        if (!(secondDate instanceof DateMK2)) {
            throw ('secondDate must be an instance of DateMK2');
        }
        const dif = this.time.getTime() - secondDate.time.getTime();
        const Seconds_from_T1_to_T2 = dif / 1000;

        if (Seconds_from_T1_to_T2 > 0) {
            return 1;
        } else if (Seconds_from_T1_to_T2 < 0) {
            return -1;
        }
        return 0;
    }

    clone() {
        return new DateMK2(new Date(this.time.getTime()));
    }
}