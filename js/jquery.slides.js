var active = 0;
var zindex = 999;
var disable_click = false;

var indexhibit_slides = (function()
{
	var public = {

        displayer : function ()  
        {
            (state == 0) ? private.opener() : private.closer();
        }
    };

	var private = {

        opener : function ()  
        {
            nav_toggle.className = ' active';
            index.style.left = '0';
            private.fadeIn(close_layer);
            state = 1;
        },

		opener2 : function ()  
        {
            nav_toggle.className = ' active';
            index.style.left = '0';
            private.fadeIn(close_layer);
            state = 1;
        }
	};
	
	return public;
	
})();

// public
function next()
{
	if (disable_click == true) return false;
	disable_click = true;
	
	var tmp = img.length;
	active = active + 1;
	if ((active + 1) > tmp) active = 0;
	getNode(img[active]);
}

// public
function previous()
{
	if (disable_click == true) return false;
	disable_click = true;

	var tmp = img.length;
	active = active - 1;
	if ((active + 1) == 0) active = (tmp - 1);
	getNode(img[active]);
}

// private
function show(id, order)
{
	// we need to set the active position differently
	active = order;
	getNode(id);
}

// private
function getNode(id) 
{
	// display loading
	loading();
	
	// show active
	$('span.current-slide').html(active + 1);
	$('#slides-total').css('display','block');

	// get the grow content via ajax
	$.post(baseurl + '/ndxzsite/plugin/ajax.php', { jxs : 'slides', i : id, z : zindex }, 
		function(html) 
		{
			fillShow(html.output, html.height, html.mime);
			disable_click = false;
			$('#slides-total').css('display','none');
	}, 'json');
	
	return false;
}

// private
function loading()
{
	// remove previous and next slides
	$('a#slide-previous').remove();
	return;
}

// private
function adjust_height(next)
{
	var adjust = 0;
	var current_height = $('#slideshow div#slide' + zindex).height();
	
	$('#slideshow').height(next);

	return;
}

// private
function fillShow(content, next_height, mime)
{	
	// animate
	if ((fade == true))
	{
		$('#slideshow').append(content);
		
		var adj_height = $('#slideshow div#slide' + zindex).height();
		
		$('#slideshow div#slide' + (zindex + 1)).fadeOut('1000').queue(function(next){$(this).remove();});
		$('#slideshow div#slide' + zindex).fadeIn('1000');
		
		var tmp = $('#slideshow div#slide' + zindex + ' .captioning').height();
		tmp = (tmp == null) ? 0 : tmp;
	}
	else
	{
		$('#slideshow').append(content);
		
		var adj_height = $('#slideshow div#slide' + zindex).height();

		$('#slideshow div#slide' + (zindex + 1)).remove();
		$('#slideshow div#slide' + zindex).show();
		
		var tmp = $('#slideshow div#slide' + zindex + ' .captioning').height();
		tmp = (tmp == null) ? 0 : tmp;
	}
	
	// count down
	zindex--;
}

// public
$.fn.preload = function() 
{
    this.each(function()
	{
        $('<img/>')[0].src = baseurl + '/files/gimgs/' + this;
    });
}

// public
$(document).keydown(function(e)
{
	if (e.keyCode == 37) { previous(); }
	if (e.keyCode == 39) { next(); }
});