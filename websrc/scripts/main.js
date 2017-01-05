import './libs/nette.ajax.js';
import './libs/netteForms.js';

// import './libs/js-core/jquery-ui-core.js';
// import './libs/js-core/jquery-ui-widget.js';
// import './libs/js-core/jquery-ui-mouse.js';
// import './libs/js-core/jquery-ui-position.js';
// import './libs/js-core/transition.js';

import './libs/template/widgets/progressbar/progressbar.js';
import './libs/template/widgets/superclick/superclick.js';

// @todo delete
import './libs/template/widgets/input-switch/inputswitch.js';
import './libs/template/widgets/input-switch/inputswitch-alt.js';

import './libs/template/widgets/slimscroll/slimscroll.js';
import './libs/template/widgets/slidebars/slidebars.js';
import './libs/template/widgets/slidebars/slidebars-demo.js';
import './libs/template/widgets/noty-notifications/noty.js';
import './libs/template/widgets/charts/piegage/piegage.js';
import './libs/template/widgets/charts/piegage/piegage-demo.js';
import './libs/template/widgets/screenfull/screenfull.js';

// @todo ?
import './libs/template/widgets/content-box/contentbox.js';

import './libs/template/widgets/material/material.js';
import './libs/template/widgets/material/ripples.js';
import './libs/template/widgets/overlay/overlay.js';
import './libs/template/widgets/tabs/tabs-responsive.js';

import './widgets-init.js';
import './libs/template/themes/admin/layout.js';

import './libs/nette.websocket';

$('form a[data-show]').on('click', function (e) {
	let $el = $(e.currentTarget);

	if ($el.data('show')) {
		e.preventDefault();

		if (history.pushState && typeof history.pushState === 'function') {
			history.pushState({}, $el.attr('title'), $el.attr('href'));
		}

		$('#' + $el.data('hide')).fadeOut(100, function () {
			$('#' + $el.data('show')).fadeIn();
		});
	}
});

$(function () {
	// $.nette.init();
});
