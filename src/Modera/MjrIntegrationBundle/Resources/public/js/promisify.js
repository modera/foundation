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
                    return null === error ? resolve(result) : reject(error);
                });
                fn.apply(thisArg, args);
            });
        };
    }

    function promisifyAction(action) {
        return promisify(function() {
            var args = [].slice.call(arguments);

            var callback = args.pop();

            var responseHandler = function(result, event, success, options) {
                callback(null, Ext.apply(function() {
                    if (Ext.isObject(event.xhr)) {
                        if (event.xhr.aborted) {
                            throw new DOMException('', 'AbortError');
                        }
                        else if (event.xhr.timedout) {
                            throw new DOMException('', 'TimeoutError');
                        }
                    }

                    if ('exception' === event.type) {
                        var error = new Error(event['message'] || 'Undefined exception.');
                        error.type = 'EXCEPTION';
                        var keys = Object.getOwnPropertyNames(error);
                        Ext.Object.each(event, function(key, value) {
                            if (event.hasOwnProperty(key) && -1 === keys.indexOf(key)) {
                                error[key] = value;
                            }
                        });
                        throw error;
                    }

                    if (Ext.isObject(result) && false === result['success']) {
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
            };

            if (action.directCfg.method.ordered) {
                var len = action.directCfg.method.len;
                var scope = args[len + 1];
                var options = args[len + 2];

                args = args.slice(0, action.directCfg.method.len).concat([responseHandler, scope, options]);
            } else {
                var data = Ext.apply({}, args[0]);
                var scope = args[2];
                var options = args[3];

                args = [data, responseHandler, scope, options];
            }

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

                        var usePromisified = false;
                        if (action.directCfg.method.ordered) {
                            usePromisified = !Ext.isFunction(args[action.directCfg.method.len]);
                        } else {
                            usePromisified = !Ext.isFunction(args[1]);
                        }

                        if (0 === args.length || usePromisified) {
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
            var provider = addProvider.apply(this, args);

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
