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

import './libs/template/themes/admin/layout.js';
import './widgets-init.js';

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
	window.wsConnection = wsConnection = new WebSocket('ws://localhost:8006');
	wsConnection.onopen = function (e) {
		console.log('Connection established!');
	};

	wsConnection.onmessage = function (e) {
		console.log(e.data);
	};
});
