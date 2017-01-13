$(function () {

	kendo.data.binders.oneWayHtml = kendo.data.Binder.extend({
		refresh: function () {
			// do nothing
		},
		init: function(element, bindings, options) {
			//call the base constructor
			kendo.data.Binder.fn.init.call(this, element, bindings, options);

			var that = this;
			var value = that.bindings["oneWayHtml"].get(); //get the value from the View-Model

			$(that.element).html(value);

			// listen for the change event of the element
			$(that.element).on("change keyup click dragend", function() {
				that.change(); //call the change function
			});
		},
		change: function() {
			var value = $(this.element).html();
			this.bindings["oneWayHtml"].set(value); //update the View-Model
		}
	});

	kendo.data.binders.imgFromHtml = kendo.data.Binder.extend({
		refresh: function () {
			// do nothing
		},
		init: function(element, bindings, options) {
			//call the base constructor
			kendo.data.Binder.fn.init.call(this, element, bindings, options);
			var that = this;

			let uid = $(that.element).parent().data('uid');
			let originalElement = $('#pdf-export-wrapper').find('[data-uid="' + uid + '"]');

			kendo.drawing.drawDOM(originalElement, {})
				.then(function(group) {
					return kendo.drawing.exportImage(group);
				})
				.done(function(data) {
					$(that.element).attr('src', data);
				});

			// listen for the change event of the element
			$(that.element).on("change keyup click dragend", function() {
				that.change(); //call the change function
			});
		},
		change: function() {
			var that = this;

			let uid = $(that.element).parent().data('uid');
			let originalElement = $('#pdf-export-wrapper').find('[data-uid="' + uid + '"]');

			kendo.drawing.drawDOM(originalElement, {})
				.then(function(group) {
					return kendo.drawing.exportImage(group);
				})
				.done(function(data) {
					$(that.element).attr('src', data);
				});
		}
	});

	// $('#page-content').resize(function () {
	// 	resizeAppContent();
	// 	console.log('resize');
	// });

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

			var view = this.changeActiveView(panel, panel.wakeUp);
		},

		changeActiveView: function (panel, callback) {
			this.trigger('spa.beforePanelViewChange');
			var that = this;
			var appContent = $('#app-content');

			appContent.fadeOut(75, function () {
				appContent.html('');
				var viewTemplate = $(panel.view);
				if (viewTemplate.length === 0) {
					throw 'View template ' + panel.view + ' not found.'
				}

				var newView = new kendo.View(viewTemplate.html(), {model: panel.viewModel, show: function () {
					appContent.fadeIn(125, function () {
						that.trigger('spa.afterPanelViewChange');
						if (typeof callback === 'function')
						{
							callback = callback.bind(panel);
							callback(view.get(0));
						}
					});
				}});
				var view = newView.render('#app-content');
				appContent.find('>div').addClass('content-box').css('min-height', appContent.height());
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

		toggleFullScreen: function (e) {
			let element = document.getElementById('spa-application');
			let $element = $(element);
			let $sender = $(e.target);
			$('.glyph-icon', this).toggleClass('icon-outdent').toggleClass('icon-indent');

			if (! this.isInFullScreen()) {
				$element.addClass('closed-sidebar');
				$sender.addClass('text-success');
				$sender.removeClass('icon-iconic-resize-full').addClass('icon-iconic-resize-small');
				this.requestFullScreen(element);
			} else {
				$element.removeClass('closed-sidebar');
				$sender.removeClass('text-success');
				$sender.removeClass('icon-iconic-resize-small').addClass('icon-iconic-resize-full');
				this.exitFullScreen();
			}
		},

		requestFullScreen: function (element) {
			if(element.requestFullscreen) {
				element.requestFullscreen();
			} else if(element.mozRequestFullScreen) {
				element.mozRequestFullScreen();
			} else if(element.webkitRequestFullscreen) {
				element.webkitRequestFullscreen();
			} else if(element.msRequestFullscreen) {
				element.msRequestFullscreen();
			}
		},

		exitFullScreen: function () {
			if(document.exitFullscreen) {
				document.exitFullscreen();
			} else if(document.mozCancelFullScreen) {
				document.mozCancelFullScreen();
			} else if(document.webkitExitFullscreen) {
				document.webkitExitFullscreen();
			}
		},

		isInFullScreen: function () {
			return document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement;
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
		},

		data: {
			avatar: null,
		}
	});

	kendo.bind($('body#spa-application'), window.SPA);

	window.SPA.bind('spa.afterInit', function (context) {
		$(context.panelsList().element).kendoSortable({
			axis: 'x',
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

	window.SPA.bind('spa.afterInit', function (context) {
		var dataElement = $('body#spa-application');
		context.set('data.id', dataElement.data('user-id'));
		context.set('data.name', dataElement.data('user-name'));
		context.set('data.surname', dataElement.data('user-surname'));
		context.set('data.email', dataElement.data('user-email'));
		context.set('data.avatar', dataElement.data('user-avatar'));
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
			var blockUserUrl = view.data('source-block-user');
			var unblockUserUrl = view.data('source-unblock-user');
			var viewModel = kendo.observable({
				isVisible: true,
				editUser: function (e) {
					var uid = $(e.target).closest('tr').data('uid');
					var model = this.products.getByUid(uid);
				},

				blockUser: function (e) {
					var model = e.data;
					var modal = $('#spa-view-user-confirm-block').modal('show');
					kendo.bind(modal, model);
					kendo.bind(modal.find('.modal-footer'), this);
				},

				confirmBlockUser: function (e) {
					var uid = $(e.target).closest('.modal-content').data('uid');
					var model = this.users.getByUid(uid);

					$.post(blockUserUrl, { id: model.get('id')}).success(function () {
						model.set('status', 'blocked');
						$('#spa-view-user-confirm-block').modal('hide');
						noty({text: 'User was blocked.',type: 'success', timeout:2500});
					});
				},

				unblockUser: function (e) {
					var uid = e.data.uid;
					var model = this.users.getByUid(uid);

					$.post(unblockUserUrl, { id: model.get('id')}).success(function () {
						model.set('status', 'ok');
						noty({text: 'User was unblocked.',type: 'success', timeout:2500});
					});
				},

				users: new kendo.data.DataSource({
					schema: {
						model: {
							id: 'id'
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
				// viewModel.users.read();
			});
			SPA.bind('user-edit.created', function (user) {
				// viewModel.users.read();
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
			isAdmin: false,
			isNew: true,
			otp: null,
			username: '',
			name: '',
			surname: '',
			phone: '',
			email: '@',
			degree: '',
			roles: [],
			edited: false,
			avatarSmall: '',
			avatarBig: '',
			newAvatar: false,
			color: null,
			status: 'ok',
		},

		User: kendo.data.Model.define(this.user),
		create: function (event, context) {
			this.registerListeners();
			var view = $(this.settings.view);
			var createUrl = view.data('source-create');
			var readUrl = view.data('source-read');
			var updateUrl = view.data('source-update');
			var readAvailableRolesUrl = view.data('source-read-available-roles');
			var userId = $(event).data('user-id');
			var currentUserId = view.data('user-id');
			var avatarBigNoImage = view.data('avatar-big-no-image');
			var avatarLoadImage = view.data('avatar-load-image');
			var dataSourceCheckUsernameUrl = view.data('source-check-username');
			var dataSourceCheckEmailUrl = view.data('source-check-email');
			var userAllowedCollors = view.data('user-allowed-collors');
			var that = this;

			this.settings.viewModel = kendo.observable({
				rolesDataSource: new kendo.data.DataSource({
					transport: {
						read: {
							type: 'json',
							url: readAvailableRolesUrl
						}
					}
				}),

				isCurrentUser: function () {
					return userId === currentUserId;
				},

				userAllowedCollors: userAllowedCollors,

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
					this.set('user.avatar', '/' + e.response['original']);
				},
				createUser: function (user) {
					var vm = this;
					$.post(createUrl, user, function (responseData) {
						vm.createUserModel(responseData);
						SPA.trigger('user-edit.created', responseData);
					}).success(function () {
						noty({text: 'New user was saved.',type: 'success', timeout:2500});
					});
				},
				updateUser: function (user) {
					var vm = this;
					$.post(updateUrl, user, function (responseData) {
						vm.createUserModel(responseData);
						SPA.trigger('user-edit.updated', responseData);
					}).success(function () {
						noty({text: 'User was updated.', type: 'success', timeout:2500});
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
					let user = that.user;
					user.color = '#' + userAllowedCollors[Math.floor(Math.random()*userAllowedCollors.length)];
					this.createUserModel(new that.User(user));
				},
				createUserModel: function (data) {
					var user = new that.User(data);
					user.bind('change', function () {
						this.set('edited', true);
					});
					this.setUserModel(user);
					return user;
				},
				isUsernameAvailable: function (e) {
					var target = $(e.target);
					var errorMessage = 'Username ' + e.target.value + ' is not available.';
					this.validateInput(target, e.target.value, dataSourceCheckUsernameUrl, errorMessage);
				},
				isEmailAvailable: function (e) {
					var target = $(e.target);
					var errorMessage = 'Email ' + e.target.value + ' is not available.';
					this.validateInput(target, e.target.value, dataSourceCheckEmailUrl, errorMessage);
				},
				validatePassword: function (e) {
					let value = e.target.value;
					let valid = true;

					if (value.length < 8) {
						valid = false;
						noty({text: 'Password must have min. 8 chars.', type: 'warning', timeout:2500});
					}
					this.setInputValidateStatus($(e.target), valid);
				},
				validatePasswordRe: function (e) {
					let value = e.target.value;
					let password = $(e.target).closest('form').find('input[name="password"]').val();
					let valid = true;
					if (value !== password) {
						valid = false;
						noty({text: 'PasswordRe must be equal to password.', type: 'warning', timeout:2500});
					}
					this.setInputValidateStatus($(e.target), valid);

					if (valid) {
						this.set('user.password', value);
					}
				},
				validateInput: function (target, value, validatingUrl, errorMessage) {
					var viewModel = this;
					target.addClass('validating');

					$.get(validatingUrl + value, function (response) {
						if (! response.available) {
							noty({text: errorMessage, type: 'warning', timeout:2500});
						}

						viewModel.setInputValidateStatus(target, response.available);
						target.removeClass('validating');
					});
				},
				setInputValidateStatus($element, valid) {
					if (valid) {
						$element.removeClass('validate-error');
					} else {
						$element.addClass('validate-error');
					}
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

	/**
	 * Register information memorandum panel
	 */
	SPA.addPanelType('information-memorandum-edit', {
		settings: {
			multiInstances: true,
			closeable: true,
			name: 'IM',
			type: 'information-memorandum-edit',
			view: '#spa-view-information-memorandum-edit',
			scrollerScale: .2,
		},

		im: {
			id: null,
			edited: false,
			status: 'ok',
		},
		Im: kendo.data.Model.define(this.im),

		insertLayout: function (e) {
			console.log('top', e, this);
		},
		create: function (event, context) {
			var view = $(this.settings.view);
			var createUrl = view.data('source-create');
			var readUrl = view.data('source-read');
			var updateUrl = view.data('source-update');
			var addPageDialog = $('#im-add-page-modal');
			var availablePagesReadUrl = view.data('available-pages-source-read');
			var readPageLayoutUrl = view.data('read-page-layout-url');
			var that = this;

			this.settings.viewModel = kendo.observable({
				isDirty: function () {
					return this.get('im.edited');
				},
				setUserModel: function (model) {
					this.set('user', model);
				},
				validateInput: function (target, value, validatingUrl, errorMessage) {
					var viewModel = this;
					target.addClass('validating');

					$.get(validatingUrl + value, function (response) {
						if (! response.available) {
							noty({text: errorMessage, type: 'warning', timeout:2500});
						}

						viewModel.setInputValidateStatus(target, response.available);
						target.removeClass('validating');
					});
				},
				setInputValidateStatus($element, valid) {
					if (valid) {
						$element.removeClass('validate-error');
					} else {
						$element.addClass('validate-error');
					}
				},
				setImModel: function (model) {
					this.set('im', model);
				},

				readImModel: function () {
					var viewModel = this;
					return $.getJSON(readUrl+ImId, {}, function (data) {
						// var panel = SPA.panelsDataSource.getByUid(that.settings.panelUid);
						// panel.set('name', that.settings.name + ' - ' + data.name + ' ' + data.surname);
						viewModel.createImModel(data);
						// SPA.refreshPanels();
					})
				},
				createNewImModel: function () {
					this.createImModel(new that.Im(that.im));
				},
				createImModel: function (data) {
					var im = new that.Im(data);
					im.bind('change', function () {
						this.set('edited', true);
					});
					this.setImModel(im);
					return im;
				},
				changeScrollerScale: function (scale) {
					// if (!scale) {
					// 	let scale = this.computeScrollerScale();
					// }
					// $('#pdf-scroller').css('transform', 'scaleY(.2) scaleX(' + scale + ')');
				},
				computeScrollerScale: function () {
					// let originWidth = $('#pdf-export').width();
					let originWidth = this.get('$pdfExport').width();
					// let originWrapperWidth = $('#pdf-export-wrapper').innerWidth();
					let originWrapperWidth = this.get('$pdfExportWrapper').innerWidth();
					let scale = originWrapperWidth / originWidth;

					if (scale > 0.3859416) {
						scale = 0.3859416;
					}
					this.set('scrollerScale', scale);
					return 0.2;
				},
				changeScrollerWrapperWidth: function (width) {
					// this.get('$pdfScroller').width(width);
					// $('#pdf-scroller').width(width);
				},
				changeScrollerWidth: function (scale) {
					// if (!scale) {
					// 	let scale = this.computeScrollerScale();
					// }
					// let width = $('#pdf-export-wrapper').innerWidth();
					// let scroller = $('#scroller-scrollbar').width(width * scale);
				},
				scrollerOffset: 0,
				scrollerScale: 0,
				pdfExportTranslateX: 0,
				pdfExport: $('#pdf-export'),
				changeExportWrapperWidth: function () {
					let width = this.computeExportWrapperWidth();
					this.get('$pdfExport').width(width);
					// $('#pdf-export').width(width);
					this.trigger('exportWrapperWidthChanged', width);
				},
				computeExportWrapperWidth: function () {
					let items = this.pagesDataSource.data();

					// sirka zahardcodovana kvuli performance, zjistovani sirky kazdeho elementu je hrozne narocne
					let width = items.length * 605;

					return width - 9;
				},
				scrollerDataBound: function(e) {

				},
				pageModel: kendo.data.Model.define({
					id: 0,
					content: '',
					isFirst: function (e) {
						return this.uid === this.parent()[0].uid;
					},
					isNotFirst: function (e) {
						return this.uid !== this.parent()[0].uid;
					}
				}),
				page: kendo.data.Model.define(this.pageModel),
				insertLayout: function (e) {
					console.log('vm', e, this);
				},
				pagesDataSource: new kendo.data.DataSource({
					batch: true
				}),

				openAddPageDialog: function (e) {
					addPageDialog.modal('show');
				},

				addSelectedPage: function () {
					let availablePagesListView = addPageDialog.find('.k-listview').data('kendoListView');
					let selectedItem = availablePagesListView.select();
					let pageLayout = availablePagesListView.dataSource.getByUid(selectedItem.data('uid'));
					var that = this;

					console.log(typeof pageLayout);
					if (typeof pageLayout !== 'undefined') {
							$.get(readPageLayoutUrl, {
									layout: pageLayout.file
								},
								function (response) {
									let model = new that.pageModel({
										content: response.layout
									});

									that.pagesDataSource.add(model);
									// that.changeExportWrapperWidth();

									addPageDialog.modal('hide');
								}
							);
					} else {
						noty({text: 'Select any template.', timeout:2500});
					}
				},

				lastSelectedPage: null,

				boundPages: function (e) {
					e.preventDefault();
					// console.log('bound pages', e);
				},

				bindPages: function (e) {
					// if (e.index == 5) {
					// 	e.preventDefault();
					// }
					// $(e.sender.wrapper).data('kendoListView').refresh();
					// console.log('bind pages', e, this);
				},

				changedPage: function (e) {
					var that = this;
					// console.log('changedPage', e);
					let listView = $(e.sender.wrapper).data('kendoListView');
					let item = $(listView.select());
					let uid = item.data('uid');
					let itemModel = this.pagesDataSource.getByUid(uid);
					let pages = $(e.sender.wrapper).find('.page');

					// pages.each( function(index, page) {
						// console.log($(page).data('uid'), uid)
						// if ($(page).data('uid') !== uid) {
						// 	console.log(page);
						// 	kendo.destroy(page);
						// 	kendo.unbind(page);
						// } else if (that.get('lastSelectedPage') !== uid) {
						// 	kendo.bind(item, this.pagesDataSource);
						// }
					// });

					// if (this.get('lastSelectedPage') !== uid) {
					// 	// kendo.destroy(item);
					// 	// kendo.unbind(item);
					// 	console.log('bind');
					//
					// 	let editors = item.find('[data-editor]');
					// 	editors.each( function(index, editableItem) {
					// 		let $editableItem = $(editableItem);
					// 		// kendo.unbind($editableItem);
					// 		// kendo.destroy($editableItem);
					// 		$editableItem.attr('data-role', $editableItem.attr('data-editor'));
					// 	});
					// 	kendo.bind(item, this.pagesDataSource);
					// 	this.set('lastSelectedPage', uid)
					// }
				},

				addPage: function (e) {
					let layout = $(e.target.getAttribute('data-layout'));
					let model = new this.pageModel({
						content: layout.html()
					});

					this.pagesDataSource.add(model);
					this.changeExportWrapperWidth();
				},

				// availablePagesDataSource: [],

				availablePagesDataSource: new kendo.data.DataSource({
					transport: {
						read: {
							type: 'json',
							url: availablePagesReadUrl
						}
					}
				}),

				downloadPdf: function () {
					if (! ((this.pagesDataSource.data().length % 4) === 0)) {
						noty({text: 'Number of pages must be divisible by four.', timeout:2500});
						return;
					}



					$('#pdf-scroller').css({ transform: 'none'});
					// Convert the DOM element to a drawing using kendo.drawing.drawDOM
					kendo.drawing.drawDOM($("#pdf-scroller"), {
							forcePageBreak: ".page:not(:first-child)",
						})
						.then(function(group) {
							console.log('scale up');
							// Render the result as a PDF file
							return kendo.drawing.exportPDF(group, {
								paperSize: "A4",
								margin: { left: "0cm", top: "0cm", right: "0cm", bottom: "0cm" }
							});
						})
						.done(function(data) {
							// Save the PDF file
							kendo.saveAs({
								dataURI: data,
								fileName: "IM.pdf"
								// proxyURL: "//demos.telerik.com/kendo-ui/service/export"
							});
							$('#pdf-scroller').css({ transform: 'scaleY(0.2) scaleX(0.2)'});
						});

				}
			});
			this.registerListeners();
			kendo.bind(addPageDialog, this.settings.viewModel);

			// if (ImId) {
			// 	this.settings.viewModel.readImModel();
			// } else {
				this.settings.viewModel.createNewImModel();
			// }
			return this.settings;
		},
		registerListeners: function () {
			var that = this;

			SPA.bind('spa.addedPanel', function (panel) {
				if (panel.type === 'information-memorandum-edit') {
					// panel.viewModel.set('$pdfExport', $('#pdf-export'));
					panel.viewModel.set('$pdfScroller', $('#pdf-scroller'));
					// panel.viewModel.set('$pdfExportWrapper', $('#pdf-export-wrapper'));
					panel.viewModel.set('$pdfScrollerWrapper', $('#pdf-scroller-wrapper'));
					// panel.viewModel.set('$scrollerScrollBar', $('#scroller-scrollbar'));
				}

				that.settings.panelUid = panel.uid;
			});

			SPA.bind('spa.afterPanelViewChange', function (panel) {
				var height = $('#im-edit').height() - 55;

				// $('#pdf-scroller-wrapper').animate({ 'height': height});
				$('#pdf-scroller-wrapper').height(height);
			// 	that.settings.viewModel.set('$pdfExport', $('#pdf-export'));
			// 	that.settings.viewModel.set('$pdfScroller', $('#pdf-scroller'));
			// 	that.settings.viewModel.set('$pdfExportWrapper', $('#pdf-export-wrapper'));
			// 	that.settings.viewModel.set('$pdfScrollerWrapper', $('#pdf-scroller-wrapper'));
			// 	that.settings.viewModel.set('$scrollerScrollBar', $('#scroller-scrollbar'));
			//
			//
			// 	that.settings.viewModel.changeExportWrapperWidth();
			// 	let pdfExportWrapper = document.getElementById('pdf-export-wrapper');
			// 	let $pdfExportWrapper = that.settings.viewModel.get('$pdfExportWrapper');
			// 	// const pdfExport = $('#pdf-export');
			// 	const $pdfExport = that.settings.viewModel.get('$pdfExport');

				// if (pdfExportWrapper) {
				// 	pdfExportWrapper.addEventListener("wheel", function (e) {
				// 		let scale = that.settings.viewModel.get('scrollerScale');
				// 		let currentTranslateX = that.settings.viewModel.get('pdfExportTranslateX');
				// 		let maxTranslateX = 0 - ($pdfExport.outerWidth() - $pdfExportWrapper.outerWidth());
				//
				// 		if (e.deltaX) {
				// 			e.preventDefault();
				// 			currentTranslateX += e.wheelDeltaX;
				//
				// 			if (currentTranslateX > 0) {
				// 				currentTranslateX = 0;
				// 			} else if (currentTranslateX < maxTranslateX) {
				// 				currentTranslateX = maxTranslateX;
				// 			}
				//
				// 			that.settings.viewModel.set('pdfExportTranslateX', currentTranslateX);
				// 			that.settings.viewModel.set('scrollerOffset', 0 - (currentTranslateX * scale));
				// 		}
				// 	});
				// }
			// });

			// that.settings.viewModel.bind('exportWrapperWidthChanged', function(width) {
				// let scale = that.settings.viewModel.computeScrollerScale();
				// let scrollerScrollBar = that.settings.viewModel.get('$scrollerScrollBar');
				// let pdfScrollerWrapper = that.settings.viewModel.get('$pdfScrollerWrapper');
				// let pdfExport = that.settings.viewModel.get('$pdfExport');

				// that.settings.viewModel.changeScrollerWrapperWidth(width);
				// that.settings.viewModel.changeScrollerScale(scale);

				// $("#scroller-scrollbar.draggable").kendoDraggable({
				// 	hint: function(element) {
				// 		return element.clone();
				// 	},
				// 	axis: 'x',
				// 	cursor: 'move',
				// 	// autoScroll: true,
				// 	container: pdfScrollerWrapper,
				// 	dragstart: function (e) {
				// 		$(e.initialTarget).addClass("hidden");
				// 	},
				// 	dragend: function (e) {
				// 		let clone = $(e.sender.hint);
				// 		let containerOffset = pdfScrollerWrapper.offset().left;
				// 		let offsetScroller = clone.offset().left - containerOffset;
				// 		clone.hide();
				// 		if (offsetScroller < 0) {
				// 			offsetScroller = 0;
				// 		}
				// 		that.settings.viewModel.set('scrollerOffset', offsetScroller);
				//
				// 		$(e.initialTarget).removeClass("hidden");
				// 	},
				// 	drag: function (e) {
				// 		let scale = that.settings.viewModel.get('scrollerScale');
				// 		let offset = $(e.sender.hint).offset().left - that.settings.viewModel.get('$pdfScrollerWrapper').offset().left;
				// 		let position = 0 - (offset / scale);
				// 		that.settings.viewModel.set('pdfExportTranslateX', position);
				// 	}
				// });

				// that.settings.viewModel.bind('change',  function (e) {
				// 	if (e.field === 'pdfExportTranslateX') {
				// 		pdfExport.css({transform: 'translateX(' + that.settings.viewModel.get('pdfExportTranslateX') + 'px)'});
				// 	} else if (e.field === 'scrollerOffset') {
				// 		// @todo nejaky bug, chtelo by prozkoumat kvuli preneseni zateze na GPU
				// 		// scrollerScrollBar.css({transform: 'translateX(' + that.settings.viewModel.get('scrollerOffset') + 'px)'})
				// 		that.settings.viewModel.get('$scrollerScrollBar').css({left: that.settings.viewModel.get('scrollerOffset')})
				// 	}
				// });

				// that.settings.viewModel.changeScrollerWidth(scale);
			});

			// @todo dopsat listener pro update uzivatele v případě shodnosti s updatovaným v jiném tabu,
			// @todo NEBO vyresit kontrolu v tabech, zda uz tentyz jiny neexistuje
		}
	});

	/**
	 * Register acl panel
	 */
	SPA.addPanelType('acl', {
		settings: {
			multiInstances: false,
			closeable: true,
			name: 'Acl',
			type: 'acl',
			view: '#spa-view-acl'
		},
		user: {
			id: null,
			status: 'ok',
		},

		/**
		 * @param {jQuery.object} container
		 * @param {object} options
		 */
		parentRolesEditor: function (container, options) {
			let grid = container.closest('div[data-role="grid"]').data('kendoGrid');
			let currentModelUid = container.closest('tr').data('uid');
			let multiSelectDataSource = [];

			/**
			 * Filtering current line from available parents, cannot filter on grid.dataSource,
			 * because filter method reduce data in original dataSource
			 */
			grid.dataSource.data().forEach(function (item) {
				if (item.uid !== currentModelUid) {
					multiSelectDataSource.push({
						id: item.id,
						uid: item.uid,
						title: item.title
					});
				}
			});

			/**
			 * Add the editor to the cell
			 */
			$('<input name="' + options.field + '"/>')
				.appendTo(container)
				.kendoMultiSelect({
					clearButton: false,
					placeholder: "-- add translate roles --",
					dataTextField: "title",
					dataValueField: "id",
					dataSource: {
						data: multiSelectDataSource
					},
					change: function (e) {
						let uid = e.sender.wrapper.closest('tr').data('uid');
						grid.dataSource.getByUid(uid).set('edited', true);
					}
				}).data('kendoMultiSelect').open();
		},

		/**
		 * @param {array} roles
		 * @returns {string}
		 */
		generateRolesTemplate: function (roles) {
			var result = '';

			if (typeof roles !== 'undefined') {
				for (var i = 0; i < roles.length; i++) {
					result += '<span class="bs-label label-primary">' + roles[i].title + '</span> ';
				}
			}

			return result;
		},

		/**
		 * Tab creator interface, used in SPA::openPanel()
		 * @param {jQuery.event} event Event then trigger open panel
		 * @param context
		 * @returns {settings|{multiInstances, closeable, name, type, view}}
		 */
		create: function (event, context) {
			this.registerListeners();
			var view = $(this.settings.view);
			var createUrl = view.data('source-create');
			var readUrl = view.data('source-read');
			var updateUrl = view.data('source-update');
			var aclResources = view.data('resources');
			var that = this;

			this.settings.viewModel = kendo.observable({
				isDirty: function () {
					return this.get('user.edited');
				},
				gridDataBound: function (e) {
					var rows = e.sender.tbody.find('tr.k-master-row');
					rows.each(function (index, row) {
						var uid = row.getAttribute('data-uid');
						var button = $(row).find('div[data-role="update"]');
						kendo.bind(button, e.sender.dataSource.getByUid(uid));
						button.on('click', function () {
							e.sender.wrapper.data('kendoGrid').saveChanges();
						});
					});
				},

				/**
				 * Actual roles, dataSource for grid with roles
				 */
				roles: new kendo.data.DataSource({
					schema: {
						model: {
							id: 'id',
							fields: {
								id: { type: 'number'},
								edited: { defaultValue: false},
								update: { editable: false, defaultValue: null},
								name: { type: 'string', defaultValue: ''},
								title: { type: 'string', defaultValue: ''},
								parents: { type: 'array', defaultValue: [] },
								resources: { type: 'array', defaultValue: aclResources}
							}
						},
						parse: function(data) {

							/**
							 * Extend received resources with default data
							 */
							for (i = 0; i < data.length; i++) {
								let newResourcesMap = {};
								newResourcesMap = $.extend(true, {}, aclResources, data[i].resources);
								data[i].resources = newResourcesMap;
							}

							return data;
						}
					},
					batch: true,
					transport: {
						create: {
							async: false,
							url: createUrl,
							dataType: "json",
							method: 'POST'
						},
						read: {
							url: readUrl,
							dataType: "json"
						},
						update: {
							async: false,
							url: updateUrl,
							dataType: "json",
							method: 'POST'
						}
					},
					change: function (e) {
						if (e.action !== undefined && e.action !== 'sync') {
							$(e.items).each(function (index, item) {
								item.set('edited', true);
							});
						}
					}
				}),

				saveGridChanges: function (e) {
					e.sender.table.find('tr .table-cell.title').removeClass('bg-danger');
					if (typeof e.values.edited === 'undefined' || e.model.isNew())
					{
						if (typeof e.values.title !== 'undefined') {
							e.model.set('title', e.values.title);
						}

						this.validateAll(e.sender.dataSource.data());
					}
				},

				saveChanges: function (e) {
					if (! this.validateAll(e.sender.dataSource.data())) {
						e.preventDefault();
					}
				},

				validateAll: function (data) {
					let that = this;
					var titleValidation = 0;

					data.forEach(function (item) {
						if (! that.validateTitleUnique(item)) {
							titleValidation++;
						}
					});

					if (titleValidation > 0) {
						noty({text: 'You have ' + titleValidation + ' duplicates in column title. Column title must be unique.', timeout:2500});
					}

					return titleValidation === 0;
				},
				
				validateTitleUnique: function (validatedModel) {
					let that = this;
					var success = true;

					this.roles.data().forEach(function (item) {
						if (item.title === validatedModel.title && item.uid !== validatedModel.uid) {
							that.markWrongCell(that.findCellByUidAndClass(item.uid, 'title'));
							that.markWrongCell(that.findCellByUidAndClass(validatedModel.uid, 'title'));
							success = false;
						}
					});

					return success;
				},

				findCellByUidAndClass: function (uid, cellClass) {
					return $('#acl-roles-grid').find('tr[data-uid="' + uid + '"]').find('.table-cell.' + cellClass);
				},

				markWrongCell: function (cell) {
					cell.addClass('bg-danger');
				},

				detailExpand: function (e) {
					var grid = $(e.sender.element).data('kendoGrid');
					grid.collapseRow(':not([data-uid="' + e.masterRow.data('uid') + '"])');
				},

				detailInit: function (e) {
					var gridElement = e.sender.element;
					var grid = $(gridElement).data('kendoGrid');
					let roleUid = e.masterRow.data('uid');
					let roleModel = this.roles.getByUid(roleUid);

					roleModel.onChangeResource = function (e) {
						grid.expandRow($(gridElement).find('tr[data-uid="' + roleUid + '"]'));
					};

					kendo.bind(e.detailRow, roleModel);
				}
			});

			return this.settings;
		},
		registerListeners: function () {
			var that = this;
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
				view = $(view).closest('#app-content');
				var viewHeight = view.height();
				view.find('div[data-role="scheduler"]').css('height', (viewHeight - 31));

				if (this.viewModel.get('navigateDate') && view) {
					var scheduler = view.find('div[data-role="scheduler"]').data('kendoScheduler');
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
