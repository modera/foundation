'use strict';

(function () {

    var DIRECT_API_NAMESPACE = 'Actions';

    if (!window.RPC) {
        window.RPC = RPC;
    }

    function RPC(options) {
        Ext.applyIf(this, options || {});
    }

    RPC.request = function rpc(resource) {
        var fn = Ext.direct.Manager.parseMethod(
            Ext.String.format('{0}.{1}.{2}', DIRECT_API_NAMESPACE, resource.action, resource.method)
        );

        if (!Ext.isFunction(fn)) {
            throw new Error('Undefined action');
        }

        if (!fn.promisified) {
            throw new Error('Un-promisified action');
        }

        var rpc = new RPC({
            abort: null,
            request: null,
        });

        return {
            abort: function(reason) {
                rpc.abort && rpc.abort(reason);
                rpc.abort = null;
            },
            send: function() {
                var args = [].slice.call(arguments);

                if (this.autoAbort) {
                    this.abort();
                }

                return new Promise(function(resolve, reject) {
                    rpc.abort = function(reason) {
                        if (rpc.request) {
                            try {
                                Ext.Ajax.abort(rpc.request);
                            } catch(err) {
                                console.error(err);
                            }
                        }
                        reject(new DOMException(reason || '', 'AbortError'));
                    };

                    var thisArg = resource.action
                        .split('.')
                        .reduce(function(obj, key) {
                            if (Ext.isObject(obj) && Ext.isObject(obj[key])) {
                                return obj[key];
                            }
                            return null;
                        }, window[DIRECT_API_NAMESPACE])
                    ;

                    var argsArray = args.concat([undefined, undefined, {
                        rpc: rpc
                    }]);

                    fn.apply(thisArg, argsArray).then(resolve);
                });
            }
        };
    }

    Ext.onReady(function() {
        var requestFn = Ext.Ajax.request;
        Ext.Ajax.request = function(options) {
            var args = [].slice.call(arguments);
            var requestHandler = function(request) {};

            if (options && options.transaction && options.transaction.callbackOptions) {
                var rpc = options.transaction.callbackOptions.rpc;
                if (rpc instanceof RPC) {
                    requestHandler = function(request) {
                        rpc.request = request;
                    };
                }
            }

            var request = requestFn.apply(this, args);
            requestHandler(request);

            return request;
        };
    });

}());
