'use strict';

(function () {

    if (!window.promisify) {
        window.promisify = promisify;
    }

    function promisify(fn, context) {
        if (typeof fn !== 'function') {
            throw new TypeError('promisify must receive a function');
        }

        return function() {
            var thisArg = context || this;
            var args = [].slice.call(arguments);

            return new Promise(function(resolve, reject) {
                args.push(function(error, result) {
                    return error == null ? resolve(result) : reject(error);
                });
                fn.apply(thisArg, args);
            });
        };
    }

    function promisifyAction(action) {
        return promisify(function() {
            var args = [].slice.call(arguments);
            var callback = args.pop();
            args.push(function(result, event, success, options) {
                callback(null, Ext.apply(function() {
                    if ('exception' === event.type) {
                        var error = new Error(event['message'] || 'Undefined exception.');
                        var keys = Object.getOwnPropertyNames(error);
                        Ext.Object.each(event, function(key, value) {
                            if (event.hasOwnProperty(key) && -1 === keys.indexOf(key)) {
                                error[key] = value;
                            }
                        });
                        throw error;
                    }

                    if (!result['success']) {
                        var error = new Error(result['message'] || 'Undefined error.');
                        var keys = Object.getOwnPropertyNames(error);
                        Ext.Object.each(result, function(key, value) {
                            if ('success' !== key && result.hasOwnProperty(key) && -1 === keys.indexOf(key)) {
                                error[key] = value;
                            }
                        });
                        throw error;
                    }

                    return result;
                }, {
                    result: result,
                    event: event,
                    success: success,
                    options: options
                }));
            });
            action.apply(this, args);
        });
    }

    function promisifyActions(actions) {
        Ext.Object.each(actions, function(name, action) {
            if (actions.hasOwnProperty(name)) {
                if (Ext.isFunction(action)) {
                    var promisified = promisifyAction(action);
                    actions[name] = Ext.apply(function() {
                        var args = [].slice.call(arguments);
                        if (0 === args.length || !Ext.isFunction(args[args.length - 1])) {
                            return promisified.apply(this, args);
                        }
                        action.apply(this, args);
                    }, {
                        promisified: true,
                        directCfg: action.directCfg
                    });
                } else {
                    promisifyActions(action);
                }
            }
        });
    }

    Ext.onReady(function() {
        var addProvider = Ext.Direct.addProvider;
        Ext.Direct.addProvider = function() {
            var args = [].slice.call(arguments);
            var provider = addProvider.apply(Ext.Direct, args);

            if ('API' === provider.id && !provider.promisified) {
                provider.promisified = true;
                Ext.Object.each(provider.namespace, function(cls, actions) {
                    if (provider.namespace.hasOwnProperty(cls)) {
                        promisifyActions(actions);
                    }
                });
            }

            return provider;
        };
    });

}());
