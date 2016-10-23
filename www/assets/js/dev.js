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

		test: function () {
			console.log(this);
		},

		/**
		 * SetUp app
		 * @returns {void}
		 */
		init: function () {
			var that = this;
			this.trigger('spa.beforeInit', this);

			/* global LeWebSocket */
			// this.ws = new LeWebSocket('localhost', 8006);

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

			var view = this.changeActiveView(panel);
			if (typeof panel.wakeUp === 'function')
			{
				panel.wakeUp(view);
			}
		},

		changeActiveView: function (panel) {
			this.trigger('spa.beforePanelViewChange');
			var that = this;
			var appContent = $('#app-content');

			appContent.fadeOut(150, function () {
				appContent.html('');
				var viewTemplate = $(panel.view);
				if (viewTemplate.length === 0) {
					throw 'View template ' + panel.view + ' not found.'
				}

				var newView = new kendo.View(viewTemplate.html(), {model: panel.viewModel, show: function () {
					appContent.fadeIn(100);
				}});
				var view = newView.render('#app-content');
				that.trigger('spa.afterPanelViewChange');
				return view[0];
			});

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
			var target;
			if (e.currentTarget !== undefined) {
				target = e.currentTarget;
			} else {
				target = $(e);
			}
			var panelType = $(target).data('panel-type');

			if (typeof this.panelTypes[panelType].create === 'function') {
				if (this.canBePanelCreated(panelType)) {
					/**
					 * Call panel create method with provided event
					 */
					var panelSettings = this.panelTypes[panelType].create(target, this);
					this.addPanel(panelSettings);
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

	$(SPA).on('spa.afterInit', function () {
		console.log('test trigger');
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
			var view = $(this.settings.view);
			var readUrl = view.data('source-read');
			var viewModel = kendo.observable({
				isVisible: true,
				editUser: function (e) {
					var uid = $(e.target).closest('tr').data('uid');
					var model = this.products.getByUid(uid);
					console.log(model);
				},
				users: new kendo.data.DataSource({
					schema: {
						model: {
							id: 'id',
							fields: {
								ProductName: { type: "string" },
								UnitPrice: { type: "number" }
							}
						}
					},
					batch: true,
					transport: {
						read: {
							url: readUrl,
							dataType: "json"
						}
					}
				})
			});
			this.settings.viewModel = viewModel;
			this.registerListeners(viewModel);
			return this.settings;
		},
		registerListeners: function (viewModel) {
			SPA.bind('user-edit.updated', function (user) {
				viewModel.users.read();
			});
			SPA.bind('user-edit.created', function (user) {
				viewModel.users.read();
			});
		}
	});

	/**
	 * Register users panel
	 */
	SPA.addPanelType('user-edit', {
		settings: {
			multiInstances: true,
			closeable: true,
			name: 'User',
			type: 'user-edit',
			view: '#spa-view-user'
		},
		user: {
			id: null,
			username: '',
			name: '',
			surname: '',
			phone: '',
			email: '@',
			degree: '',
			role: [],
			edited: false,
			avatarSmall: '',
			avatarBig: '',
			newAvatar: false,
			status: 'ok',
		},
		User: kendo.data.Model.define(this.user),
		create: function (event, context) {
			this.registerListeners();
			var view = $(this.settings.view);
			var createUrl = view.data('source-create');
			var readUrl = view.data('source-read');
			var updateUrl = view.data('source-update');
			var userId = $(event).data('user-id');
			var avatarBigNoImage = view.data('avatar-big-no-image');
			var avatarLoadImage = view.data('avatar-load-image');
			var that = this;

			this.settings.viewModel = kendo.observable({
				/**
				 * Prepare user data for storing
				 */
				saveUser: function () {
					var user = this.user.toJSON();
					user.id = this.user.id;

					// existing user
					if (user.id) {
						this.updateUser(user);
					}
					// new user
					else {
						this.createUser(user);
					}
				},
				avatarBig: function (e) {
					/**
					 * User have set avatar image
					 */
					if (this.get('user.avatarBig'))
					{
						return '/' + this.get('user.avatarBig');
					}
					/**
					 * User not loaded
					 */
					else if (this.get('user.uid') === undefined) {
						return '/' + avatarLoadImage;
					}
					/**
					 * User don't have avatar (new user)
					 */
					return '/' + avatarBigNoImage;
				},
				uploadAvatarStart: function () {
					this.set('user.avatarBig', avatarLoadImage);
				},
				uploadAvatarComplete: function (e) {
					this.set('user.avatarBig', e.response['big-image']);
					this.set('user.newAvatar', '/' + e.response['original']);
				},
				createUser: function (user) {
					var vm = this;
					$.post(createUrl, user, function (responseData) {
						vm.createUserModel(responseData);
						SPA.trigger('user-edit.created', responseData);
					});
				},
				updateUser: function (user) {
					var vm = this;
					$.post(updateUrl, user, function (responseData) {
						vm.createUserModel(responseData);
						SPA.trigger('user-edit.updated', responseData);
					});
				},
				isDirty: function () {
					return this.get('user.edited');
				},
				setUserModel: function (model) {
					this.set('user', model);
				},
				readUserModel: function () {
					var viewModel = this;
					return $.getJSON(readUrl+userId, {}, function (data) {
						var panel = SPA.panelsDataSource.getByUid(that.settings.panelUid);
						panel.set('name', that.settings.name + ' - ' + data.name + ' ' + data.surname);
						viewModel.createUserModel(data);
						SPA.refreshPanels();
					})
				},
				createNewUserModel: function () {
					this.createUserModel(new that.User(that.user));
				},
				createUserModel: function (data) {
					var user = new that.User(data);
					user.bind('change', function () {
						this.set('edited', true);
					});
					this.setUserModel(user);
					return user;
				}
			});

			if (userId) {
				this.settings.viewModel.readUserModel();
			} else {
				this.settings.viewModel.createNewUserModel();
			}
			return this.settings;
		},
		registerListeners: function () {
			var that = this;
			SPA.bind('spa.addedPanel', function (panel) {
				that.settings.panelUid = panel.uid;
			});

			// @todo dopsat listener pro update uzivatele v případě shodnosti s updatovaným v jiném tabu,
			// @todo NEBO vyresit kontrolu v tabech, zda uz tentyz jiny neexistuje
		}
	});

	SPA.addPanelType('scheduler', {
		settings: {
			multiInstances: false,
			closeable: true,
			name: 'Scheduler',
			type: 'scheduler',
			view: '#spa-view-scheduler',
			wakeUp: function(view) {
				if (this.viewModel.get('navigateDate')) {
					var scheduler = $(view).find('div[data-role="scheduler"]').data('kendoScheduler');
					scheduler.date(this.viewModel.get('navigateDate'));
				}
			}
		},
		create: function (event, context) {
			kendo.culture().calendar.firstDay = 1;
			var view = $(this.settings.view);
			var scheduler = $('#app-content').find('div[data-role="scheduler"]').data('kendoScheduler');
			var createUrl = view.data('source-create');
			var readUrl = view.data('source-read');
			var updateUrl = view.data('source-update');
			var destroyUrl = view.data('source-destroy');

			var viewModel = kendo.observable({
				isVisible: true,
				navigateDate: null,
				navigateView: null,
				navigateAction: null,
				navigate: function (e) {
					this.navigateView = e.view;
					this.navigateDate = e.date;
					this.navigateAction = e.action;
				},
				tasks: new kendo.data.SchedulerDataSource({
					batch: true,
					transport: {
						read: {
							url: function() {
								// @todo find better way to get scheduler
								var scheduler = $('#app-content').find('div[data-role="scheduler"]').data('kendoScheduler');
								var url = readUrl;
								if (viewModel.get('navigateDate')) {
									var date = new Date(viewModel.get('navigateDate'));
									url = url + '&date=' +date.toLocaleDateString();
								} else {
									url = url + '&date=' + new Date().toLocaleDateString();
								}
								if (viewModel.get('navigateView')) {
									url = url + '&view=' + viewModel.get('navigateView');
								} else {
									url = url + '&view=' + scheduler.view().title.toLowerCase();
								}
								if (viewModel.get('navigateAction')) {
									url = url + '&schedulerAction=' + viewModel.get('navigateAction');
								}
								return url;
							},
							dataType: "json"
						},
						update: {
							url: updateUrl,
							dataType: "json",
							type: 'POST'
						},
						create: {
							url: createUrl,
							dataType: "json",
							type: "POST"
						},
						destroy: {
							url: destroyUrl,
							dataType: "json",
							type: 'POST'
						}
					},
					serverFiltering: true,
					serverPaging: true,
					serverGrouping: true,
					serverSorting: true,
					serverAggregates: true,
					schema: {
						data: function(response) {
							response.forEach(function (item) {
								item.start = item.start.date;
								item.end = item.end.date;
								if (item.recurrence) {
									item.recurrenceId = item.recurrence.id;
								}
							});
							return response;
						},
						model: {
							id: "id",
							fields: {
								id: { type: "number", from: 'id' },
								title: { defaultValue: "No title", validation: { required: true } },
								start: { type: "date"},
								end: { type: "date"},
								startTimezone: {  },
								endTimezone: {  },
								description: {  },
								recurrenceId: {  },
								recurrenceRule: {  },
								recurrenceException: {  },
								isAllDay: { type: "boolean"  }
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
