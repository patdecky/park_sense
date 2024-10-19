'use strict';
const fetchApiDefTimeout = 20000;

class fetchAPI {

    static defaultTimeout = fetchApiDefTimeout;
    constructor() {
        if (fetchAPI._instance) {
            return fetchAPI._instance;
        }
        fetchAPI._instance = this;
        //constructor here:
        //...
    }

    /**
     *
     * @param {string}file
     * @param {string}req
     * @param {{}}params
     * @param {Number}type 1 for GET, 0 for POST
     * @param {number}timeout
     * @param {number}maxRetries
     * @returns {Promise<*|*[]>}
     * @constructor
     */
    static async AllInOneReq(file, req, params = {}, type = 1, timeout = fetchApiDefTimeout, maxRetries = 3) {
        let fa = new this();
        let ret = {};
        ret.payload = undefined;

        if (maxRetries < 1) {
            console.error("API request to " + file + " failed after several retries.\nRequest: " + req + "\nParams: " + JSON.stringify(params));
            return [];
        }

        let reqTypes = {
            0: fa.POSTreq,
            1: fa.GETreq
        }

        try {
            //has to be called with .call to pass the correct 'this' context
            ret = await reqTypes[type].call(fa, file, req, params, timeout);
        } catch (e) {
            console.error(e);
            ret = false;
        }
        //general failure e.g. no internet
        if (ret === undefined || ret === false) {
            setTimeout(() => {
                this.AllInOneReq(file, req, params, type, timeout, maxRetries - 1)
            }, 1250);
            return [];
        }

        //no records for the year
        if (ret.code === QE.QE_NOTHING_TO_RET) {
            console.warn("No data from " + file + ".\nRequest: " + req + "\nParams: " + JSON.stringify(params));
            return [];
        }

        if (ret.code !== QE.QE_OK) {
            console.warn("Non-200 response from " + file + ".\nQuery error code: " + ret.code + "\nRequest: " + req + "\nParams: " + JSON.stringify(params));
            return [];
        }

        return ret.payload;
    }

    async GETreq(file, req, params = {}, timeout = fetchApiDefTimeout) {
        let finUrl = `PHP/API/${file}?req=${req}`;
        for (const [key, value] of Object.entries(params)) {
            finUrl += `&${key}=${value}`;
        }


        let FT = await fetchTimeout(timeout, fetch(finUrl, {keepalive: true}));
        if (FT === undefined) {
            console.error("API fetch - FT is undefined\n" + finUrl);
            return false;
        }

        return await FT.text().then(async (data, obj = this) => {
            return await obj.returnHandle(data);
        });
    }

    async POSTreq(file, req, params = {}, timeout = fetchApiDefTimeout) {
        let finUrl = `PHP/API/${file}?req=${req}`;

        const formData = new FormData();
        for (const [key, value] of Object.entries(params)) {
            formData.append(key, value);
        }

        let FT = await fetchTimeout(timeout, fetch(finUrl, {method: 'POST', keepalive: false, body: formData}));
        if (FT === undefined) {
            console.error("API fetch - FT is undefined\n" + finUrl);
            return false;
        }

        return await FT.text().then(async (data, obj = this) => {
            return await obj.returnHandle(data);
        });
    }

    async returnHandle(data) {
        try {
            //Server Response = sr
            const sr = JSON.parse(data);
            if (sr.length < 1) {
                console.error('API fetch - too small\n' + data);
                return false;
            }
            if (sr.code !== 200) {
                console.warn('API fetch warning \nResponse Code:    ' + sr.code + '\nResponse message: ' + sr.msg);
            }
            //return {code:sr.code,msg:sr.msg,payload:sr.payload};
            return sr;
        } catch (e) {
            console.error('API fetch -- json parse error');
            return false;
        }
    }
}