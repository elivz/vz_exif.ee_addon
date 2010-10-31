<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
  'pi_name' => 'VZ Exif',
  'pi_version' => '1.0',
  'pi_author' => 'Eli Van Zoeren',
  'pi_author_url' => 'http://elivz.com/',
  'pi_description' => 'Extract the Exif information from an image',
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
  }

	function exif()
	{
		$data = array(array(
		  'size' => $this->get_exif('FileSize'),
		  'height' => $this->get_exif('Height'),
		  'width' => $this->get_exif('Width'),
		  'date' => $this->get_exif('DateTime'),
		  'make' => $this->get_exif('Make'),
		  'model' => $this->get_exif('Model'),
		  'focal_length' => $this->get_exif('FocalLength'),
		  'focal_length_equiv' => $this->get_exif('FocalLengthIn35mmFilm'),
		  'aperture' => $this->get_exif('ApertureFNumber'),
		  'shutter' => $this->get_exif('ExposureTime'),
		  'iso' => $this->get_exif('ISOSpeedRatings'),
		  'software' => $this->get_exif('Software'),
		  'flash' => $this->get_exif('Flash'),
    ));
    
		return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $data);
	}
  
  /*
   * Catch any other calls and use the method name called`
   * as the EXIF value to retrieve
   */
  function size() { return $this->get_exif('FileSize'); }
	function height() { return $this->get_exif('Height'); }
  function width() { return $this->get_exif('Width'); }
  function date() { return $this->get_exif('DateTime'); }
  function make() { return $this->get_exif('Make'); }
  function model() { return $this->get_exif('Model'); }
  function focal_length() { return $this->get_exif('FocalLength'); }
  function focal_length_equiv() { return $this->get_exif('FocalLengthIn35mmFilm'); }
  function aperture() { return $this->get_exif('ApertureFNumber'); }
  function shutter() { return $this->get_exif('ExposureTime'); }
  function iso() { return $this->get_exif('ISOSpeedRatings'); }
  function software() { return $this->get_exif('Software'); }
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
		
		// Initialize the cache
    $cache =& $this->EE->session->cache['vz_exif'][$root.$image];
		
    // Only get the Exif data once per page load
    if (!isset($cache))
    {
			$image = str_ireplace('http://'.$_SERVER['HTTP_HOST'], '', $image);
			$file = '';
				
			if ($root)
			{
				// Add the path to the image file to get the full path
				$file = $this->EE->functions->remove_double_slashes($root.$image);
			}
			elseif (strncmp($image, '/', 1) == 0)
			{
				// The image url is relative to the web root
				$site_relative_url = trim(str_ireplace('http://'.$_SERVER['HTTP_HOST'], '', $this->EE->functions->fetch_site_index(1)), '/');
				$root_path = str_ireplace($site_relative_url, '', FCPATH);
				$file = $this->EE->functions->remove_double_slashes($root_path.$image);
			}
			
			// Get the data from the file
			if (!is_readable($file)) return '<!-- Could not read the file '.$image.'. -->';
			$cache = exif_read_data($file);
		}

		$exif = $cache;
		
		// Get the value we need from the array
		switch ($tag) {
			case 'FileSize':
				return isset($exif[$tag]) ? round($exif[$tag] / 1024) : '';
			case 'Height': case 'Width': case 'ApertureFNumber':
				return isset($exif['COMPUTED'][$tag]) ? $exif['COMPUTED'][$tag] : 'none';
			case 'Make': case 'Model':
				return isset($exif[$tag]) ? ucwords(strtolower($exif[$tag])) : '';
			case 'FocalLength':
				if (isset($exif[$tag])) eval('$length = '.$exif[$tag].'; return $length;');
				return '';
			case 'DateTime':
				$format = $this->EE->TMPL->fetch_param('format');
				$date = strtotime(isset($exif['DateTimeOriginal']) ? $exif['DateTimeOriginal'] : $exif['DateTime']);
				return $format ? $this->EE->localize->decode_date($format, $date) : $date;
			case 'Flash':
				return !@empty($exif['Flash']) ? 'Yes' : '';
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

TAG PAIR:
=========

The following code will output <em>all</em> the available Exif data in a list. You don't need to include all the variables in your template.

{exp:vz_exif:exif image="{photo}"}
<ul>
	<li>File Size: {size}kb</li>
	<li>Dimensions: {width}x{height}</li>
	<li>Taken on: {date format="%F %d, %Y"}</li>
	<li>Camera brand: {make}</li>
	<li>Camera: {model}</li>
	<li>Focal length: {focal_length}mm (<abbr title="35mm equivalent">{focal_length_equiv}mm-e</abbr>)</li>
	<li>Aperture: {aperture}</li>
	<li>Shutter speed: {shutter} seconds</li>
	<li>ISO: {iso}</li>
	<li>Processed with: {software}</li>
	<li>Flash used: {flash}</li>
</ul>
{/exp:vz_exif:exif}

SINGLE TAGS:
============

You can also get each piece of data using a single tag in the form: {exp:vz_exif:[exif_tag_to_get]}. The Exif data is cached after the first time you get it, so the performance cost for this method should be insignificant.

<p>The following photo was taken with a {exp:vz_exif:model image="{photo}"} camera.</p>

PARAMETERS:
===========

image= (required)
This is the url of the image to get Exif data from. The image needs to be on your server and in JPEG format. Typically, this will be in the form of a custom field inside your {exp:channels} tag pair.

root= (optional)
VZ Exif will do its best to determine the server path to the image you give it, but in some cases you may want to specify the root path manually. The root url will simply be prepended to the image url (minus the domain name, if there is one).

	  <?php
	  $buffer = ob_get_contents();
	  ob_end_clean(); 
	  return $buffer;
  }

}

/* End of file pi.vz_exif.php */ 
/* Location: ./system/expressionengine/third_party/vz_exif/pi.vz_exif.php */