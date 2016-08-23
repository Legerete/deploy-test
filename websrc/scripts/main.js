
// import './libs/nette.ajax.js';
// import './main2.js';
/**
 * Created by Nick Nemame on 17.07.2016.
 */

var xs = 1;

if (xs === 1) {
	alert('ALERT Z main.js');
}

$('h2').after('<p>Skeleton\'s first paragraph. Feel free to change this message!</p>');

var x = (_color) => {
	let color = _color;
	color = color.trim();
	$('body').css('background', color);
};

x('red ');
