<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
  'pi_name' => 'VZ Exif',
  'pi_version' => '1.0',
  'pi_author' => 'Eli Van Zoeren',
  'pi_author_url' => 'http://elivz.com/',
  'pi_description' => 'Extract Exif information from an image',
  'pi_usage' => Vz_exif::usage()
  );

/**
 * Memberlist Class
 *
 * @package			ExpressionEngine
 * @category		Plugin
 * @author			Eli Van Zoeren
 * @copyright		Copyright (c) 2010, Eli Van Zoeren
 * @link				http://elivz.com/blog/single/vz_exif
 * @license			http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported/
 */

class Vz_exif {
	
	/*
	 * Tag pair: {exp:vz_exif}Template data{/exp:vz_exif}
	 */
  function Vz_exif()
  {
		$this->EE =& get_instance();
		$tagdata = $this->EE->TMPL->tagdata;
    $tag = $this->EE->TMPL->fetch_param('tag');
    
		return $this->get_exif($tag);
  }
  
  /*
   * Catch any other calls and use the method name called
   * as the EXIF value to retrieve
   */
  function size() { return $this->get_exif('FileSize'); }
  function height() { return $this->get_exif('Height'); }
  function width() { return $this->get_exif('Width'); }
  function aperture() { return $this->get_exif('ApertureFNumber'); }
  function model() { return $this->get_exif('Model'); }
  function make() { return $this->get_exif('Make'); }
  function focal_length() { return $this->get_exif('FocalLength'); }
  function focal_length_equiv() { return $this->get_exif('FocalLengthIn35mmFilm'); }
  function software() { return $this->get_exif('Software'); }
  function shutter() { return $this->get_exif('ExposureTime'); }
  function iso() { return $this->get_exif('ISOSpeedRatings'); }
  function date() { return $this->get_exif('DateTime'); }
  function flash() { return $this->get_exif('Flash'); }

	/*
	 * This is the heart of the plugin.
	 * Get the exif data from the image
	 * and return in in an array
	 */
	private function get_exif($tag)
	{
    $image = $this->EE->TMPL->fetch_param('image');
    $root = $this->EE->TMPL->fetch_param('path');
    
		$image = str_ireplace('http://'.$_SERVER['HTTP_HOST'], '', $image);
		$file = '';
			
		if ($root)
		{
			// Add the path to the image file to get the full path
			$file = $this->EE->functions->remove_double_slashes($root.$image);
		}
		elseif (substr($image, 0, 1) == '/')
		{
			// The image url is relative to the web root
			$site_relative_url = trim(str_ireplace('http://'.$_SERVER['HTTP_HOST'], '', $this->EE->functions->fetch_site_index(1)), '/');
			$root_path = str_ireplace($site_relative_url, '', FCPATH);
			$file = $this->EE->functions->remove_double_slashes($root_path.$image);
		}
		
		// Get the data from the file
		if (!is_readable($file)) return '<!-- Could not read the file '.$image.'. -->';
		$exif = exif_read_data($file);
		//print_r($exif); die();
		
		// Get the value we need from the array
		switch ($tag) {
			case 'FileSize':
				return isset($exif[$tag]) ? round($exif[$tag] / 1024) : '';
			case 'Height': case 'Width': case 'ApertureFNumber':
				return isset($exif['COMPUTED'][$tag]) ? $exif['COMPUTED'][$tag] : 'none';
			case 'Make': case 'Model':
				return isset($exif[$tag]) ? ucwords(strtolower($exif[$tag])) : '';
			case 'FocalLength':
				if (isset($exif[$tag])) eval('$length = '.$exif[$tag].';');
				return $length;
			case 'DateTime':
				$format = $this->EE->TMPL->fetch_param('format');
				$date = strtotime(isset($exif['DateTimeOriginal']) ? $exif['DateTimeOriginal'] : $exif['DateTime']);
				return $format ? $this->EE->localize->decode_date($format, $date) : $date;
			case 'Flash':
				return $exif['Flash'] ? 'Yes' : '';
			default:
				return isset($exif[$tag]) ? $exif[$tag] : '';
		}
	}

  function usage()
  {
	  ob_start(); 
	  ?>
The VZ EXIF Plugin extracts EXIF data from an image
and makes it available in your templates.

{exp:vz_exif}

This is an incredibly simple Plugin.

	  <?php
	  $buffer = ob_get_contents();
	  ob_end_clean(); 
	  return $buffer;
  }

}

/* End of file pi.vz_exif.php */ 
/* Location: ./system/expressionengine/third_party/vz_exif/pi.vz_exif.php */