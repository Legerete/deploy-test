$(window).load(function () {
	$(function () {
		$('#loading').fadeOut(400, 'linear');
	});
});

var wow = new WOW({
	animateClass: 'animated',
	offset: 100
});
wow.init();