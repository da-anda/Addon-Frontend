// Initial of dropdown menu

// tabs
		$(document).ready(function() {
		  $("#tabs").tabs({ fx: { height: 'toggle', opacity: 'toggle' } });
		});
		
// Tiny carousel
		$(document).ready(function(){
			$('.carousel_container').tinycarousel({
				start: 1, // where should the carousel start?
				display: 1, // how many blocks do you want to move at a time?
				axis: 'x', // vertical or horizontal scroller? 'x' or 'y' .
				controls: true, // show left and right navigation buttons?
				pager: false, // is there a page number navigation present?
				interval: 10000, // move to the next block on interval.
				intervaltime: 30000, // interval time in milliseconds.
				rewind: true, // If interval is true and rewind is true it will play in reverse if the last slide is reached
				animation: true, // false is instant, true is animate.
				duration: 1200, // how fast must the animation move in milliseconds?
				callback: null // function that executes after every move
			});	
		});
		
// tipsy
		$(function() {
			$('.social a').tipsy(
			{
				gravity: 's', // nw | n | ne | w | e | sw | s | se
				fade: true
			}); 
		});