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

	var panelNum = 1;

	/* global window, kendo */
	window.SPA = kendo.observable({

		/**
		 * SetUp app
		 * @returns {void}
		 */
		init: function () {
			var that = this;
			this.trigger('spa.beforeInit', this);

			/* global LeWebSocket */
			this.ws = new LeWebSocket('localhost', 8006);

			/**
			 * Bind internal app events routines
			 */
			this.bind('spa.changedPanel', this.changeActivePanel);
			this.bind('spa.addedPanel', this.changeActivePanel);

			this.trigger('spa.afterInit', this);
		},

		clickOnPanel: function (e) {
			var uid = $(e.currentTarget).data('uid');
			var panel = this.panelsList().dataSource.getByUid(uid);

			this.trigger('spa.changedPanel', panel);
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
			closeable: true,
			active: true,
			view: null
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
			closeable: true,
			active: true,
			view: null
		}),

		/**
		 * Close panel activate another one
		 * @param {Z.Event} event Kendo event
		 */
		closePanel: function (event) {
			var uid = $(event.currentTarget).closest('li').data('uid');
			var panel = this.panelsDataSource.getByUid(uid);

			if (panel.active && this.panelsDataSource.data().length > 0) {
				this.changeActivePanel(this.panelsDataSource.at(0));
			}

			if (panel.uid === this.panelsDataSource.at(0).uid) {
				this.changeActivePanel(this.panelsDataSource.at(1));
			}

			this.panelsDataSource.remove(panel);
		},

		/**
		 * Opened panels
		 * @returns {object} kendoListView
		 */
		panelsList: function () {
			return $('#spa-panels-list').data('kendoListView');
		},

		refreshPanels: function () {
			this.panelsList().refresh();
		},

		changeActivePanel: function (panel) {
			this.openedPanels().forEach(function (item) {
				item.set('active', false);
			});
			panel.set('active', true);

			// @todo bug workaround, pri prepnuti existujiciho tabu se nereflektuje stav v zalozkach
			this.panelsList().refresh();

			this.changeActiveView(panel);
		},

		changeActiveView: function (panel) {
			this.trigger('spa.beforePanelViewChange');
			$('#app-content').html('');

			var viewTemplate = $(panel.view);
			if (viewTemplate.length === 0) {
				throw 'View template ' + panel.view + ' not found.'
			}

			var newView = new kendo.View(viewTemplate.html(), {model: panel.viewModel});
			newView.render('#app-content');

			this.trigger('spa.afterPanelViewChange');
		},

		panelsDataSource: new kendo.data.DataSource({}),

		/**
		 * Definition of panelType interface
		 * Settings property is optional, but returned object of create() method must have this properties.
		 */
		PanelType: kendo.data.Model.define({
			settings: {
				multiInstances: false,
				closeable: false,
				name: new Date(),
				type: new Date(),
				view: '',
				viewModel: new kendo.observable({})
			},
			create: function (event, context) {}
		}),

		/**
		 * Panels definitions
		 * @type {Object}
		 */
		panelTypes: {
		},

		/**
		 * Is setup in this.init()
		 * @type {array}
		 * @returns {array} List of opened panels
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

			this.panelsList().dataSource.add(newPanel);

			this.trigger('spa.addedPanel', newPanel);
			return newPanel.uid;
		},

		/**
		 * Add panel type with name, this name is same as data-panel-type attribute on the cta
		 * @see PanelType PanelType for info about panelSettings interface structure
		 * @param name
		 * @param panelSettings
		 */
		addPanelType: function (name, panelSettings) {
			if (this.panelTypes[name] !== undefined) {
				throw 'Panel type with name ' + name + ' is already registered';
			} else {
				this.panelTypes[name] = new this.PanelType(panelSettings);
			}
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
					 * Call panel create method with provided event
					 */
					var pannelSettings = this.panelTypes[panelType].create(target, this);
					this.addPanel(pannelSettings);
				} else {
					var that = this;
					this.openedPanels().forEach(function (panel) {
						if (panel.type === panelType) {
							that.changeActivePanel(panel);
						}
					});
				}
			} else {
				console.info('Panel of type ' + panelType + ' is not registered.');
			}
		},

		/**
		 * Check if can be panel create.
		 * @param {string} panelType
		 * @returns {boolean}
		 */
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

	kendo.bind($('body#spa-application'), window.SPA);

	window.SPA.bind('spa.afterInit', function (context) {
		$(context.panelsList().element).kendoSortable({
			filter: '>li',
			cursor: 'move',
			placeholder: function (element) {
				return element.clone().css('opacity', 0.1);
			},
			ignore: 'i.button-close',
			hint: function (element) {
				return element.clone().addClass('fake-responsive-tab');
			},
			change: function (e) {
				var dataSource = $(this.element).data('kendoListView').dataSource;
				var newIndex = e.newIndex;
				var dataItem = dataSource.getByUid(e.item.data("uid"));
				dataSource.remove(dataItem);
				dataSource.insert(newIndex, dataItem);
			}
		});
	});

	/**
	 * Register and basic dashboard panel
	 */
	window.SPA.bind('spa.beforeInit', function (context) {
		context.addPanelType('dashboard', {
				settings: {
					multiInstances: false,
					closeable: false,
					name: 'Dashboard',
					type: 'dashboard',
					view: '#spa-view-dashboard'
				},
				create: function (event, context) {
					return this.settings;
				}
			}
		);
	});

	/**
	 * Create dashboard panel if panel list is empty
	 */
	window.SPA.bind('spa.afterInit', function (context) {
		if (context.panelsList().dataSource.data().length === 0) {
			context.addPanel(this.panelTypes['dashboard'].settings);
		}
	});

	window.SPA.init();


	/**
	 * Register users panel
	 */
	SPA.addPanelType('users', {
		settings: {
			multiInstances: false,
			closeable: true,
			name: 'Users',
			type: 'users',
			view: '#spa-view-users'
		},
		create: function (event, context) {
			var viewModel = kendo.observable({
				isVisible: true,
				onSave: function(e) {
				},
				foo: $('#spa-view-dashboard').html(),
				products: new kendo.data.DataSource({
					schema: {
						model: {
							id: "ProductID",
							fields: {
								ProductName: { type: "string" },
								UnitPrice: { type: "number" }
							}
						}
					},
					batch: true,
					transport: {
						read: {
							url: "//demos.telerik.com/kendo-ui/service/products",
							dataType: "jsonp"
						},
						update: {
							url: "//demos.telerik.com/kendo-ui/service/products/update",
							dataType: "jsonp"
						},
						create: {
							url: "//demos.telerik.com/kendo-ui/service/products/create",
							dataType: "jsonp"
						},
						parameterMap: function(options, operation) {
							if (operation !== "read" && options.models) {
								return {models: kendo.stringify(options.models)};
							}
						}
					}
				})
			});
			this.settings.viewModel = viewModel;
			return this.settings;
		}
	});

	SPA.addPanelType('scheduler', {
		settings: {
			multiInstances: false,
			closeable: true,
			name: 'Scheduler',
			type: 'scheduler',
			view: '#spa-view-scheduler'
		},
		create: function (event, context) {
			var view = $(this.settings.view);
			var readUrl = view.data('source-read');
			var createUrl = view.data('source-create');

			var viewModel = kendo.observable({
				isVisible: true,
				onSave: function(e) {
				},
				tasks: new kendo.data.SchedulerDataSource({
					batch: true,
					transport: {
						read: {
							url: readUrl,
							dataType: "jsonp"
						},
						update: {
							url: "//demos.telerik.com/kendo-ui/service/tasks/update",
							dataType: "json",
							type: 'POST'
						},
						create: {
							url: createUrl,
							dataType: "json",
							type: "POST"
						},
						destroy: {
							url: "//demos.telerik.com/kendo-ui/service/tasks/destroy",
							dataType: "json",
							type: 'POST'
						},
						// parameterMap: function(options, operation) {
						// 	if (operation !== "read" && options.models) {
						// 		return {models: kendo.stringify(options.models)};
						// 	}
						// }
					},
					schema: {
						model: {
							id: "taskId",
							fields: {
								taskId: { type: "number" },
								title: { defaultValue: "No title", validation: { required: true } },
								start: { type: "date" },
								end: { type: "date" },
								startTimezone: {  },
								endTimezone: {  },
								description: {  },
								recurrenceId: {  },
								recurrenceRule: {  },
								recurrenceException: {  },
								isAllDay: { type: "boolean",  }
							}
						}
					}
				})
			});
			this.settings.viewModel = viewModel;

			return this.settings;
		}
	});
});