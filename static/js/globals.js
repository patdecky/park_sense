const QE = {
    QE_NOT_AUTH: 0,
    QE_DB_ERROR: 1,
    QE_INPUT_INVALID: 2,
    QE_NO_KNOWN_REQ: 3,
    QE_NOTHING_TO_RET: 4,
    QE_INTERVAL_TOO_WIDE: 5,
    QE_FILTER_NOT_SUPPORTED: 6,
    QE_NOT_IMPLEMENTED: 7,
    QE_OK: 200
};

async function fetchTimeout(ms, promise) {
    return new Promise((resolve, reject) => {
        const timer = setTimeout(() => {
            reject(new Error('TIMEOUT'));
        }, ms);

        promise
                .then(value => {
                    clearTimeout(timer);
                    resolve(value);
                })
                .catch(reason => {
                    clearTimeout(timer);
                    reject(reason);
                });
    });
}
;

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}