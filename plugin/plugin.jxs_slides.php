<?php if (!defined('SITE')) exit('No direct script access allowed');

class Jxs_slides
{
	public function Jxs_slides()
	{
		self::__construct();
	}
	
	public function output()
	{
		echo json_encode($this->output); 
		exit;
	}
	
	public function __construct()
	{
		$OBJ =& get_instance();
		global $default;
		
		// remove sleep if it drives you completely insane ;)
		//sleep(1);

		$rs = $OBJ->db->fetchRecord("SELECT * FROM ".PX."objects, ".PX."media 
			WHERE media_id = '$_POST[i]' 
			AND media_ref_id = id");
	
		$caption = ($rs['media_title'] == '') ? '' : "<div class='title'>" . $rs['media_title'] . "</div>";
		$caption .= ($rs['media_caption'] == '') ? '' : "<div class='caption'>" . $rs['media_caption'] . "</div>";
		
		// tmep
		$vids = array_merge($default['media'], $default['services']);
	
		$path = '/files/gimgs/';
	
		if (in_array($rs['media_mime'], $vids))
		{
			// if it's a movie else it's a service
			$file = (in_array($rs['media_mime'], $default['media'])) ? DIRNAME . $path . $rs['media_file'] : $rs['media_file'];
			$mime = $rs['media_mime'];
			
			$OBJ->vars->exhibit['id'] = $rs['id'];
			$OBJ->vars->exhibit['object'] = $rs['object'];
			$OBJ->abstracts->front_abstracts();
	
			// height and width of thumbnail
			$size[0] = $rs['media_x'];
			$size[1] = $rs['media_y'];
			
			// new dimensions 
			$new_height = $height;
			$new_width = round(($size[0] * $height) / $size[1]);
		
			$right_margin = (isset($OBJ->hook->options['slideshow_settings']['margin'])) ? 
				$OBJ->hook->options['slideshow_settings']['margin'] : 25;
			$bottom_margin = (isset($OBJ->hook->options['slideshow_settings']['bottom_margin'])) ? 
				$OBJ->hook->options['slideshow_settings']['bottom_margin'] : 25;

			$temp_x = $new_width + $right_margin;
			$temp_y = $new_height + $bottom_margin;
		
			// we need the base index.php file for this one
			require_once(DIRNAME . '/ndxzsite/plugin/index.php');
			
			$file = ($rs['media_dir'] != '') ? $rs['media_dir'].'/'.$rs['media_file'] : $rs['media_file'];

			$click_width = $size[0];
			
			$bottom_setting = ($size[1] - 90);
			
			$adjuster = ($size[0] - $click_width);
			
			// odd vimeo bug
			$mime_display = ($rs['media_mime'] == 'vimeo') ? '' : ' display: none;';
			
			// autoplay is true from this format
			$OBJ->vars->media['autoplay'] = true;
			
			$a = '<div id="slide' . $_POST['z'] . '" class="picture videoslide" style="z-index: ' . $_POST['z'] . '; position: absolute;' . $mime_display . '">';
			
			$a .= "<a href='#' onclick=\"next(); return false;\"><span class='nextlink'></span></a>";
			
			$a .= $mime($file, $new_width, $new_height, $rs['media_thumb']);

			if (($rs['media_title'] == '') && ($rs['media_caption'] == ''))
			{
				// do nothing
			}
			else
			{
				$a .= "<div class='captioning'>\n";

				$a .= ($rs['media_title'] !=  '') ? "<span class='media-title'>" . $rs['media_title']. "</span>" : '';
				$a .= (($rs['media_title'] !=  '') && ($rs['media_caption'] !=  '')) ? "<span class='media-title-separator'></span>" : '';
				$a .= ($rs['media_caption'] !=  '') ? "<span class='media-caption'>" . strip_tags($rs['media_caption'], "a,i,b,br,u") . "</span>" : '';

				$a .= "</div>\n";
			}

			$a .= "</div>\n\n";

			$this->output['height'] = $rs['media_y'];
			$this->output['output'] = $a;
			return;
		}
		else if (in_array($rs['media_mime'], $default['images'])) // it's an image
		{
			$file = DIRNAME . '/files/gimgs/' . $rs['id'] . '_' . $rs['media_file'];
	
			// height and width of thumbnail
			$size = getimagesize($file);
			
			$OBJ->vars->exhibit['id'] = $rs['id'];
			$OBJ->vars->exhibit['object'] = $rs['object'];
			$OBJ->abstracts->front_abstracts();
			
			$click_width = $size[0];
			$bottom_setting = ($size[1] - 90);
			
			$a = "<div id='slide" . $_POST['z'] . "' class='picture' style='z-index: " . $_POST['z'] . "; position: absolute; display: none;'>";
			$a .= "<a style='width: {$click_width}px; height: {$bottom_setting}px;' href='#' onclick=\"next(); return false;\">";
			
			$name = $rs['id'] . '_' . $rs['media_file'];
			
			$a .= '<img src="' . $OBJ->baseurl . '/files/gimgs/' . $name . '" width="' . $size[0] . '" height="' . $size[1] . '" />';
			
			$a .= "</a>";
			
			if (($rs['media_title'] == '') && ($rs['media_caption'] == ''))
			{
				// do nothing
			}
			else
			{
				$a .= "<div class='captioning'>\n";

				$a .= ($rs['media_title'] !=  '') ? "<span class='media-title'>" . $rs['media_title'] . "</span>" : '';
				$a .= (($rs['media_title'] !=  '') && ($rs['media_caption'] !=  '')) ? "<span class='media-title-separator'></span>" : '';
				$a .= ($rs['media_caption'] !=  '') ? "<span class='media-caption'>" . strip_tags($rs['media_caption'], "a,i,b,br,u") . "</span>" : '';

				$a .= "</div>\n";
			}		
		
			$a .= "</div>\n";
			
			$this->output['mime'] 	= $rs['media_mime'];
			$this->output['height'] = $size[1];
			$this->output['output'] = $a;

			return;
		}
		else // it's text only
		{
			$OBJ->vars->exhibit['id'] = $rs['id'];
			$OBJ->vars->exhibit['object'] = $rs['object'];
			$OBJ->abstracts->front_abstracts();
			
			// how do we get abstracts here?
			$height = (isset($OBJ->abstracts->abstract['height'])) ? $OBJ->abstracts->abstract['height'] : 575;

			// only if media_mime = txt
			$a .= '<div  id="slide' . $_POST['z'] . '" class="picture" style="z-index: ' . $_POST['z'] . '; position: absolute; height: ' . $height . 'px;">';
			
			// we need to get the text from the file
			$handle = fopen(DIRNAME . '/files/' . $rs['media_file'], 'r');
			$text = fread($handle, 1000000);
			fclose($handle);
			
			// new dimensions 
			$a .= "<div id='slideshow-text'>\n";
			$a .= $text;
			$a .= "</div>\n";
			
			$a .= "</div>\n";
			
			$this->output['mime'] 	= $rs['media_mime'];
			$this->output['height'] = $height;
			$this->output['output'] = $a;
			return;
		}

		$this->output = '';
	}
}