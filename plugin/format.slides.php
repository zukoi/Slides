<?php if (!defined('SITE')) exit('No direct script access allowed');

/*
Format Name: Slides
Format URI: http://www.indexhibit.org/format/slides/
Description: Slides format with ajax.
Version: 1.0
Author: Vaska
Author URI: http://vaska.com/
Params: format,images,custom('navigate')
Options Builder: default_settings
Source: exhibit
Objects: exhibits
*/

class Exhibit
{
	// PADDING AND TEXT WIDTH ADJUSTMENTS UP HERE!!!
	var $picture_block_padding_right = 0;
	var $text_width = 250;
	var $text_padding_right = 35;
	var $final_img_container = 0; // do not adjust this one
	var $imgs = array();
	var $br = 1;
	var $placement = false;
	var $titles = true;
	var $bottom_margin = 25;
	var $operand = 0;
	var $source;
	var $text_block_height;
	var $collapse = 1;
	var $center = false;
	var $settings = array();
	var $text_placement = 0;
	var $effect;
	var $navigate;
	var $nav_type;
	var $thumb_height;
	var $textwidth;
	
	///////////////
	var $x;

	public function __construct()
	{
		$OBJ =& get_instance();
			
		// default is on
		$this->navigate = (isset($OBJ->abstracts->abstract['navigate'])) ? 
			$OBJ->abstracts->abstract['navigate'] : 0;
			
		$this->effect = (isset($OBJ->hook->options['slides_settings']['effect'])) ? 
			$OBJ->hook->options['slides_settings']['effect'] : 1;
			
		$this->textwidth = (isset($OBJ->hook->options['slides_settings']['textwidth'])) ? 
			$OBJ->hook->options['slides_settings']['textwidth'] : 200;
	}
	
	public function Exhibit()
	{
		self::__construct();
	}
	
	
	public function createExhibit()
	{
		$OBJ =& get_instance();
		global $uploads, $default;
		
		// we need to customize the allowed formats here
		// adding txt for customization
		$OBJ->vars->media = array_merge($default['images'], $default['media'], $default['services'], array('txt'));
		
		// exhibit only source
		$this->source = $default['filesource'][0];
	
		// get images
		$OBJ->vars->images = $OBJ->page->get_imgs();

		// if no images return our text only
		if (!$OBJ->vars->images[0]) { $OBJ->page->exhibit['exhibit'] = $OBJ->vars->exhibit['content']; return; }
		
		$OBJ->page->exhibit['lib_css'][] = "slides.css";
	
		$s = ''; $a = ''; $i = 0;
		
		$total = count($OBJ->vars->images[0]);
		
		if ($total > 1)
		{
			// make the javascript array
			foreach ($OBJ->vars->images[0] as $img)
			{
				$arr[] = $img['media_id'];
				$i++;
			}
		
			$tmp = implode(', ', $arr);
			
			$OBJ->page->exhibit['dyn_js'][] = ($this->effect == 1) ? "var fade = true;" : "var fade = false;";
			$OBJ->page->exhibit['dyn_js'][] = "var baseurl = '" . $OBJ->baseurl . "';";
			$OBJ->page->exhibit['dyn_js'][] = "var count = 0;";
			$OBJ->page->exhibit['dyn_js'][] = "var total = $total;";
			$OBJ->page->exhibit['dyn_js'][] = "var img = new Array(" . $tmp . ");";
			$OBJ->page->exhibit['dyn_js'][] = ($this->text_placement == 0) ? "var placement = true;" : "var placement = false;";
			$OBJ->page->add_jquery('jquery.slides.js', 21);
		}
		
		// we need to see if there are any videos and set the js
		global $uploads; 
		$preload = array();
		
		foreach ($OBJ->vars->images as $tests)
		{
			foreach ($tests as $test)
			{
				// let's make an array of images for preloading
				if (in_array($test['media_mime'], array_merge($default['images'])))
				{
					$preload[] = $OBJ->vars->exhibit['id'] . '_' . $test['media_file'];
				}
			}
		}
		
		// first image array
		$image = $OBJ->vars->images[0][0];
		
		// if it's an image
		if (in_array($image['media_mime'], $default['images']))
		{
			$size = getimagesize(DIRNAME . '/files/gimgs/' . $OBJ->vars->exhibit['id'] . '_' . $image['media_file']);
		
			$a = "<div id='slideshow-wrapper'>\n";
			$a .= "<div id='slideshow' style='position: relative;'>\n";
			$a .= "<div id='slides-total' style='display: none;'><span class='current-slide'>1</span> / <span class='total-slide'>" . $total . "</span></div>\n";
			$a .= '<div id="slide1000" class="picture" style="z-index: 1000; position: absolute;">';
			$a .= '<a href="#" onclick="next(); return false;" alt="">';
			$a .= '<img src="' . BASEURL . '/files/gimgs/' . $OBJ->vars->exhibit['id'] . '_' . $image['media_file'] . '" width="' . $size[0] . '" height="' . $size[1]. '" />';		
			$a .= '</a>';
		
			if (($image['media_title'] == '') && ($image['media_caption'] == ''))
			{
				// do nothing
			}
			else
			{
				$a .= "<div class='captioning'>\n";

				$a .= ($image['media_title'] !=  '') ? "<span class='media-title'>" . $image['media_title']. "</span>" : '';
				$a .= (($image['media_title'] !=  '') && ($image['media_caption'] !=  '')) ? "<span class='media-title-separator'></span>" : '';
				$a .= ($image['media_caption'] !=  '') ? "<span class='media-caption'>" . strip_tags($image['media_caption'], "a,i,b,br,u") . "</span>" : '';

				$a .= "</div>\n";
			}
			
			$a .= '</div>';
			$a .= '</div>';
		
			$a .= "</div>\n";
		}
		else if (in_array($image['media_mime'], array_merge($default['media'], $default['services']))) // it's a video
		{
			$mime = $image['media_mime'];
			
			$a = "<div id='slideshow-wrapper'>\n";
			$a .= "<div id='slideshow' style='position: relative;'>\n";
			$a .= "<div id='slides-total' style='display: none;'><span class='current-slide'>1</span> / <span class='total-slide'>" . $total . "</span></div>\n";
			$a .= '<div  id="slide1000" class="picture videoslide" style="z-index: 1000; position: absolute;">';		
			$a .= "<a href='#' onclick=\"next(); return false;\"><span class='nextlink'></span></a>";
			$a .= $mime($image['media_file'], $image['media_x'], $image['media_y'], $image['media_thumb']);
			$a .= '</div>';
		
			if (($image['media_title'] != '') && ($image['media_caption'] != ''))
			{
				// do nothing
			}
			else
			{
				$a .= "<div class='captioning'>\n";

				$a .= ($image['media_title'] !=  '') ? "<span class='media-title'>" . $image['media_title']. "</span>" : '';
				$a .= (($image['media_title'] !=  '') && ($image['media_caption'] !=  '')) ? "<span class='media-title-separator'></span>" : '';
				$a .= ($image['media_caption'] !=  '') ? "<span class='media-caption'>" . strip_tags($image['media_caption'], "a,i,b,br,u") . "</span>" : '';

				$a .= "</div>\n";
			}
			
			$a .= '</div>';
			$a .= "</div>\n";
		}
		else // it's text only
		{
			// only if media_mime = txt
			$a = "<div id='slideshow-wrapper'>\n";
			$a .= "<div id='slideshow' style='position: relative;'>\n";
			$a .= "<div id='slides-total' style='display: none;'><span class='current-slide'>1</span> / <span class='total-slide'>" . $total . "</span></div>\n";
			$a .= '<div  id="slide1000" class="picture" style="z-index: 1000; position: absolute;">';
			
			// we need to get the text from the file
			$handle = fopen(DIRNAME . '/files/' . $image['media_file'], 'r');
			$text = fread($handle, 1000000);
			fclose($handle);
			
			// new dimensions 
			$a .= "<div id='slideshow-text'>\n";
			$a .= $text;
			$a .= "</div>\n";
			
			$a .= '</div>';
			$a .= "</div>\n";
		}
		
		// the nav
		// put this with textspace
		if ($total > 1)
		{
			if ($this->navigate == 1)
			{
				$nav = "\n\n<div id='slideshow-nav'>\n";
				$nav .= '<span id="previous"><a href="#" onclick="previous(); return false;" class="slide-previous"></a></span>';
				$nav .= '<span class="between-nav"></span>';
				$nav .= '<span id="next"><a href="#" onclick="next(); return false;" class="slide-next"></a></span>';
				$nav .= "</div>\n\n";
			} 
			else
			{
				$nav = '';
			}
		}
		else
		{
			$nav = '';
		}		
		
		// preload array
		if (count($preload) >= 1)
		{
			$OBJ->page->exhibit['dyn_js'][] = "$(function() { $(['" . implode("', '", $preload) . "']).preload(); });";
		}
		
		// composition space - text placement
		$content = "<div id='textspace' style='width: " . $this->textwidth . "px;'>\n<div>" . $OBJ->vars->exhibit['content'] . "</div>\n";
		$content .= $nav;
		$content .= "</div>\n";
			
		// placing our layout
		$layout = $content . $a;
		
		$s .= "<div id='img-container'>\n";
		$s .= $layout;
		$s .= "</div>\n";
		
		$OBJ->page->exhibit['exhibit'] = $s;
			
		$OBJ->page->exhibit['dyn_css'][] = $this->defaultCSS();
		
		return $OBJ->page->exhibit['exhibit'];
	}


	public function defaultCSS()
	{
		$OBJ =& get_instance();

		return "";
	}
	
	
	///////////////// SETTINGS
	public function default_settings()
	{
		$OBJ =& get_instance();

		$effect = (isset($this->settings['effect'])) ? $this->settings['effect'] : 1;
		$textwidth = (isset($this->settings['textwidth'])) ? $this->settings['textwidth'] : 200;
		
		$OBJ->template->add_css('themes/ui-lightness/jquery.ui.all.css');
		$OBJ->template->add_js('ui/jquery.ui.core.js');
		$OBJ->template->add_js('ui/jquery.ui.widget.js');
		$OBJ->template->add_js('ui/jquery.ui.mouse.js');
		$OBJ->template->add_js('ui/jquery.ui.slider.js');
	
		$html = "<label>transition effect</label>\n";
		$html .= "<p><select name='option[effect]'>\n";
		$html .= "<option value='1'" . $this->selected($effect, 1) . ">fade</option>\n";
		$html .= "<option value='0'" . $this->selected($effect, 0) . ">none</option>\n";
		$html .= "</select></p>\n";
		
		$html .= "<label id='textwidth_value'>text width <span>$textwidth</span></label>\n";
		$html .= "<input type='hidden' id='textwidth' name='option[textwidth]' value='$textwidth' />\n";
		$html .= "<div id='slider3' style='margin: 10px 0;'></div>\n\n";

		$OBJ->template->onready[] = "$('#slider3').slider({ value: $textwidth, max: 500, 
stop: function(event, ui) { $('#textwidth').val(ui.value); },
slide: function(event, ui) { $('label#textwidth_value span').html(ui.value) }
});";
	
		return $html;
	}

	public function selected($var='', $check='')
	{
		return ($var == $check) ? " selected='selected'" : '';
	}
	
	public function custom_option_navigate()
	{
		$OBJ =& get_instance();

		$navigate = (isset($OBJ->abstracts->abstract['navigate'])) ?
			$OBJ->abstracts->abstract['navigate'] : 0;

		// ++++++++++++
		$onoff = array('off', 'on');

		$li = '';
		$input = ($navigate == '') ? 0 : $navigate;
		
		$html = label($OBJ->lang->word('navigate')) . br();

		foreach ($onoff as $key => $val)
		{
			$active = ($input == $key) ? "class='active'" : '';
			$extra = ($key == 0) ? "id='after'" : '';
			$li .= li($OBJ->lang->word($val), "$active title='$key' $extra");
		}
		
		$html .= ul($li, "class='listed' id='navigate'");
		
		$OBJ->template->onready[] = "$('#navigate li').option_list_post('navigate', 'exhibits');";
		
		// output column
		$OBJ->options->custom_output[3][1] = $html;
		
		return;
	}
	
	// new text width
	public function custom_option_textwidth()
	{
		$OBJ =& get_instance();

		$textwidth = (isset($OBJ->abstracts->abstract['textwidth'])) ?
			(int)$OBJ->abstracts->abstract['textwidth'] : 200;
			
		$set = (isset($OBJ->abstracts->abstract['textwidth'])) ? 1 : 0;
		
		$OBJ->template->add_css('themes/ui-lightness/jquery.ui.all.css');
		$OBJ->template->add_js('ui/jquery.ui.core.js');
		$OBJ->template->add_js('ui/jquery.ui.widget.js');
		$OBJ->template->add_js('ui/jquery.ui.mouse.js');
		$OBJ->template->add_js('ui/jquery.ui.slider.js');
	
		$html = "<div style='padding-right: 15px;'><label id='width_value'>text width <span>$textwidth</span></label>\n";
		$html .= "<input type='hidden' id='width' name='option[width]' value='$textwidth' />\n";
		$html .= "<div id='slider' style='margin: 10px 0;'></div></div>\n\n";

		$OBJ->template->onready[] = "$('#slider').slider({ value: $textwidth, min: 150, max: 500, step: 1,  
	stop: function(event, ui) { $('#width').val(ui.value); update_abstract(ui.value, 'textwidth', $set); },
	slide: function(event, ui) { $('label#width_value span').html(ui.value); }
	});";
		
		// output column
		$OBJ->options->custom_output[2][1] = $html;
	
		return;
	}
}