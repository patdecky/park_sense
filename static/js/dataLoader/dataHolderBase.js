'use strict';

class dataHolderBase {

    _listeners = {};

    checkType(type) {
        if (!this._listeners[type]) {
            throw new Error(`Unknown listener type: ${type}`);
        }
    }

    newListenerType(type) {
        this._listeners[type] = [];
    }

    /**
     * Alias for addListener
     * @alias addListener
     * @param type
     * @param callback
     */
    newListener(type, callback) {
        this.addListener(type, callback);
    }

    /**
     * Add listener to the listeners array.
     * Be sure to specify correct type of the child class.
     * Check _listeners object for available types.
     * @param type
     * @param callback
     */
    addListener(type, callback) {
        this.checkType(type);
        this._listeners[type].push(callback);
    }

    notifyListeners(type) {
        this.checkType(type);
        this._listeners[type].forEach(callback => callback());
    }

    nameOf(nameObject) {
        for (let varName in nameObject) {
            if (nameObject != null)
                return varName;
        }
    }
}