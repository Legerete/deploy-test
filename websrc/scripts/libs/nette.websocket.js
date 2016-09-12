/**
 *
 * @param {string} host
 * @param {number} port
 * @param {object} selectors
 * @returns {LeWebSocket}
 * @constructor
 */
LeWebSocket = function (host, port, selectors) {

	/**
	 * Internal methods and properties
	 *
	 * @type {{reconectTimeout: number, host: null, setHost: inner.setHost, port: null, setPort: inner.setPort, checkRequirements: inner.checkRequirements, init: inner.init, conn: null, open: inner.open, handleWebSocket: inner.handleWebSocket, tryReconnect: inner.tryReconnect, parseMessage: inner.parseMessage, processMessage: inner.processMessage}}
	 */
	var inner = {

		/**
		 * Sleep time before trying reconnect to the server
		 * @type {number}
		 */
		reconnectTimeout: 500,

		/**
		 * WebSocket server host
		 * @type {string}
		 */
		host: null,

		/**
		 * WebSocket server port
		 * @type {number}
		 */
		port: null,

		/**
		 * WebSocket connection
		 * @type {WebSocket}
		 */
		conn: null,

		/**
		 * WebSocket host setter
		 * If port if not set, try search host settings in body[data-ws-host] attribute
		 *
		 * @internal
		 * @param {string} host
		 */
		setHost: function (host) {
			if (typeof host !== undefined && host !== null) {
				inner.host = host;
			} else if ($('body').attr('data-ws-host')) {
				inner.host = $('body').attr('data-ws-host');
			}
		},

		/**
		 * WebSocket server port setter
		 * If port if not set, try search port settings in body[data-ws-port] attribute
		 *
		 * @internal
		 * @param {number} port
		 */
		setPort: function (port) {
			if (typeof port !== undefined && host !== null) {
				inner.port = port;
			} else if ($('body').attr('data-ws-port')) {
				inner.port = $('body').attr('data-ws-port');
			}
		},

		/**
		 * Chceck all requirements for module e.g. host and port settings
		 */
		checkRequirements: function () {
			if (! inner.host || ! inner.port) {
				throw 'Host or port not set for WebSocket connection.';
			}
		},

		/**
		 * Initializing WebSocket module
		 */
		init: function () {
			inner.open();
			inner.handleWebSocket();
		},

		/**
		 * Create WebSocket connection
		 */
		open: function () {
			if (typeof WebSocket == 'function') {
				if (!inner.conn || inner.conn.readyState != inner.conn.OPEN) {
					inner.conn = new WebSocket('ws://'+inner.host+':'+inner.port);
					inner.conn.onopen = function () {
						console.info('Websocket connection established with ws://'+inner.host+':'+inner.port);
					}
				}
			} else {
				throw 'WebSocket not supported on this browser.';
			}
		},

		/**
		 * Set-up
		 */
		handleWebSocket: function () {
			inner.conn.onmessage = function (message) {
				inner.processMessage(inner.parseMessage(message));
			};
			inner.conn.onerror = function (message) {
				console.log(message);
				console.log('WebSocket connection was closed after error.');
				$.trigger('message', {'state': 'close'}); // @todo
				inner.tryReconnect();
			};
			inner.conn.onclose = function (message) {
				console.log(message);
				console.log('WebSocket connection was closed.');
				inner.tryReconnect();
			};
		},

		/**
		 * Setting up recconecting timer
		 */
		tryReconnect: function () {
			setTimeout(function () {
				console.info('Reconnecting WebSocket.');
				inner.init();
			}, inner.reconnectTimeout);
		},

		constructMessage: function (e) {
			// console.log(e.currentTarget.href);
			var request = {
				'request': e.currentTarget.href
			};

			return JSON.stringify(request);
			// return 'constructedMessage - wooohooo';
		},

		/**
		 * Messages parser
		 * @param {JSON|html} message
		 */
		parseMessage: function (message) {
			console.log(message);
			try {
				message = JSON.parse(message);
				message = {
					type: 'json',
					body: message.data
				}
			} catch (e) {
				// @todo detekce zda se jedna o HTML
				message = {
					type: 'html',
					body: message.data
				};
			}

			return message;
		},

		/**
		 * Process messages from server
		 * @param message
		 */
		processMessage: function (message) {
			if (message.type === 'json') {
				$.nette.ext('snippets').updateSnippets(message.body);
			}
			if (message.type === 'html') {
				var $messageBody = $(message.body).find('#app-content');
				console.log($messageBody);
				console.log($messageBody.html());
				$('#app-content').html($messageBody.html());
			}
		},

		/**
		 *
		 * @param {object} e jQuery event
		 */
		requestHandler: function (e) {
			e.preventDefault();

			inner.conn.send(inner.constructMessage(e));
			e.stopPropagation();
		},

		/**
		 * Handle clicks, forms, buttons
		 * @param {object} settings
		 */
		initHandlers: function (settings) {
			var s = this.prepareInitHandlersSettings(settings);

			$(s.linkSelector).off('click.ws').on('click.ws', this.requestHandler);
			$(s.formSelector).off('submit.ws', this.requestHandler).on('submit.ws', this.requestHandler)
				.off('click.ws', ':image', this.requestHandler).on('click.ws', ':image', this.requestHandler)
				.off('click.ws', ':submit', this.requestHandler).on('click.ws', ':submit', this.requestHandler);
			$(s.buttonSelector).closest('form')
				.off('click.ws', s.buttonSelector, this.requestHandler).on('click.ws', s.buttonSelector, this.requestHandler);
		},

		/**
		 * Default handler settings
		 * @todo upravit tak aby byly prepsatelne ze [settings]
		 * @param settings
		 * @returns {{linkSelector: string, formSelector: string, buttonSelector: string}}
		 */
		prepareInitHandlersSettings: function (settings) {
			return {
				'linkSelector' : 'a.web-socket',
				'formSelector' : 'form.web-socket',
				'buttonSelector' : 'input.web-socket[type="submit"], button.web-socket[type="submit"], input.web-socket[type="image"]'
			};
		}

	};

	/**
	 * Port getter
	 * @returns {number}
	 */
	this.getPort = function () {
		return inner.port;
	};

	/**
	 * Is active connection to the server?
	 * @returns {boolean}
	 */
	this.isConnected = function () {
		return inner.conn ? true : false;
	};

	/**
	 * Returns WebSocket connection
	 * @returns {WebSocket}
	 */
	this.getConnection = function () {
		return inner.conn;
	};

	/**
	 * Send message to the server
	 * @param {JSON} message
	 */
	this.sendMessage = function (message) {
		inner.conn.send(message);
	};

	/**
	 * WebSocket extension constructor
	 * @param host
	 * @param port
	 * @param settings
	 * @constructor
	 */
	function LeWebSocket(host, port, settings) {
		inner.setHost(host);
		inner.setPort(port);
		inner.initHandlers(settings);

		inner.checkRequirements();

		inner.init();
	}

	/**
	 * Auto construct
	 */
	if (this instanceof LeWebSocket) {
		this.LeWebSocket(host, port, selectors);
		return this;
	} else {
		new LeWebSocket(host, port, selectors);
		return this;
	}
};

