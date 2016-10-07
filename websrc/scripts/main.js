import './libs/nette.ajax.js';

import './libs/js-core/jquery-ui-core.js';
import './libs/js-core/jquery-ui-widget.js';
import './libs/js-core/jquery-ui-mouse.js';
import './libs/js-core/jquery-ui-position.js';
import './libs/js-core/transition.js';
import './libs/template/widgets/progressbar/progressbar.js';
import './libs/template/widgets/superclick/superclick.js';
import './libs/template/widgets/input-switch/inputswitch-alt.js';
import './libs/template/widgets/slimscroll/slimscroll.js';
import './libs/template/widgets/slidebars/slidebars.js';
import './libs/template/widgets/slidebars/slidebars-demo.js';
import './libs/template/widgets/charts/piegage/piegage.js';
import './libs/template/widgets/charts/piegage/piegage-demo.js';
import './libs/template/widgets/screenfull/screenfull.js';
import './libs/template/widgets/content-box/contentbox.js';
import './libs/template/widgets/material/material.js';
import './libs/template/widgets/material/ripples.js';
import './libs/template/widgets/overlay/overlay.js';
import './libs/template/widgets/tabs/tabs-responsive.js';

import './libs/template/themes/admin/layout.js';
import './widgets-init.js';

import './libs/nette.websocket';

$('form a[data-show]').on('click', function (e) {
	var $el = $(e.currentTarget);

	if ($el.data('show')) {
		e.preventDefault();

		if (history.pushState && typeof history.pushState === 'function') {
			history.pushState({}, $el.attr('title'), $el.attr('href'));
		}

		$('#' + $el.data('hide')).fadeOut(300, function () {
			$('#' + $el.data('show')).fadeIn();
		});
	}
});

$(function () {
	// var WebSocketConnection;
	// window.WebSocketConnection = WebSocketConnection = new WebSocket('ws://localhost:8006');
	// WebSocketConnection.onopen = function (e) {
	// 	console.log('Connection established!');
	// };
	//
	// WebSocketConnection.onmessage = function (e) {
	// 	console.log(e.data);
	// };

	// $.nette.ext('WebSocket', {
	// 	init: function () {
	// 		/* global LeWebSocket, window */
	// 		window.ws = new LeWebSocket('localhost', 8006);
	// 		console.log(window.ws.getPort());
	// 	}
	// });
	//
	// $.nette.init();

	/* global window, kendo */
	window.SPA = kendo.observable({

		/**
		 * SetUp app
		 * @returns {void}
		 */
		init: function () {
			/* global LeWebSocket */
			this.ws = new LeWebSocket('localhost', 8006);

			/**
			 * Bind internal app events routines
			 */
			this.bind('changedPanel', this.actualizeActivePanel);
		},

		clickOnTab: function (e) {
			var uid = $(e.currentTarget).data('uid');
			var panel = this.panelsList().dataSource.getByUid(uid);

			this.trigger('changedPanel', panel);
		},

		/**
		 * WebSocket Connection
		 * @type {WebSocket}
		 */
		ws: null,

		panelDefaultSettings: {
			uuid: null,
			data: {},
			type: '',
			name: '',
			multiInstances: false,
			active: true
		},

		/**
		 * Uniformed model for panels
		 */
		Panel: kendo.data.Model.define({
			uuid: null,
			data: {},
			type: '',
			name: '',
			multiInstances: false,
			active: true
		}),

		registerPanel: function () {

		},

		closePanel: function (e) {

		},

		/**
		 * Opened panels
		 * @returns {object}
		 */
		panelsList: function () {
			return $('#spa-panels-list').data('kendoListView');
		},

		refreshPanels: function () {
			this.panelsList().refresh();
		},

		actualizeActivePanel: function (panel) {
			this.openedPanels().forEach(function (item) {
				item.set('active', false);
			});
			panel.set('active', true);
		},

		panelsDataSource: new kendo.data.DataSource({}),

		/**
		 * Panels creators
		 * @type {Object}
		 */
		panelTypes: {
			'dashboard': {
				'settings': {
					multiInstances: true
				},
				'create': function (e, context) {
					context.addPanel({
						name: 'Dashboard',
						type: 'dashboard'
					});
				}
			}
		},

		/**
		 * Is setup in this.init()
		 * @type {array}
		 * @returns {array}
		 */
		openedPanels: function () {
			return this.panelsList().dataSource.data();
		},

		/**
		 * @param {object} settings {
		 *   name: {string} Foo
		 *   multiInstances: {bool} false
		 * }
		 * @returns {string} uid
		 */
		addPanel: function (settings) {
			var newPanel = new this.Panel(settings);
			var panels = this.openedPanels();

			panels.forEach(function (item) {
				item.set('active', false);
			});

			this.panelsList().dataSource.insert(0, newPanel);

			this.trigger('addedPanel');
			return newPanel.uid;
		},

		addPanelCreator: function (name, callback) {

		},

		/**
		 * Create panel from anchor data-attributes
		 * @param {Z.Event} e Kendo Event
		 * @returns {void}
		 */
		openPanel: function (e) {
			var target = e.currentTarget;
			var panelType = $(e.currentTarget).data('panel-type');

			if (typeof this.panelTypes[panelType].create === 'function') {
				if (this.canBePanelCreated(panelType)) {
					/**
					 * Call panel type with provided event
					 */
					this.panelTypes[panelType].create(target, this);
				}
			} else {
				console.info('Panel of type ' + panelType + ' is not registered.');
			}
		},

		canBePanelCreated: function (panelType) {
			var panelSettings = $.extend({}, this.panelDefaultSettings, this.panelTypes[panelType].settings);
			var panelCanBeCreated = true;
			if (!panelSettings.multiInstances) {
				this.openedPanels().forEach(function (panel) {
					if (panel.type === panelType) {
						panelCanBeCreated = false;
					}
				});
			}

			return panelCanBeCreated;
		},

		clickOnStatistics: function (e) {
			console.log('event triggered [statistics]');
			this.trigger('clickOnStatistics');
		},

		clickOnNotifications: function (e) {
			console.log('event triggered [notifications]');
			this.trigger('clickOnNotifications');
		},

		clickOnChatSidebar: function (e) {
			console.log('event triggered [sidebar]');
			this.trigger('clickOnChatSidebar');
		},

		clickOnSearch: function (e) {
			console.log('event triggered [search]');
			this.trigger('clickOnSearch');
		}
	});

	window.SPA.init();

	kendo.bind($('body#spa-application'), window.SPA);
});
