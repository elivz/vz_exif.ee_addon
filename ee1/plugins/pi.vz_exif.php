<?php

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
    function exif()
    {
        global $TMPL, $FNS;
        $tagdata = $TMPL->tagdata;
        
        $data = array(
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
        );
    
        // Run the conditional statements
        $tagdata = $FNS->prep_conditionals($tagdata, $data);
        
        // Replace the tags with their values
        foreach ($data as $tag => $value)
        {
            if (strstr($tagdata, LD.$tag.RD))
            {
                $tagdata = str_replace(LD.$tag.RD, $value, $tagdata);
            }
        }
        
        return $tagdata;
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
		global $TMPL, $FNS, $SESS, $LOC;
        	
        $image = $TMPL->fetch_param('image');
        $root = $TMPL->fetch_param('path');
        	
        // Initialize the cache
        $cache =& $SESS->cache['vz_exif'][$root.$image];
        	
        // Only get the Exif data once per page load
        if (!isset($cache))
        {
            $image = str_ireplace('http://'.$_SERVER['HTTP_HOST'], '', $image);
            $file = '';
            	
            if ($root)
            {
            	// Add the path to the image file to get the full path
            	$file = $FNS->remove_double_slashes($root.$image);
            }
            elseif (strncmp($image, '/', 1) == 0)
            {
            	// The image url is relative to the web root
            	$root_path = rtrim( (array_key_exists('DOCUMENT_ROOT', $_ENV)) ? $_ENV['DOCUMENT_ROOT'] : $_SERVER['DOCUMENT_ROOT'], '/\\');
            	$file = $root_path.$image;
            }
            
            // Get the data from the file
            if (@exif_imagetype($file) != IMAGETYPE_JPEG) return '<!-- The file "'.$image.'" could not be found or was not in jpeg format -->';
            $cache = exif_read_data($file);
    	}

		$exif = $cache;
		
		// Get the value we need from the array
		switch ($tag) {
			case 'FileSize':
				return isset($exif[$tag]) ? round($exif[$tag] / 1024) : '';
			case 'Height': case 'Width': case 'ApertureFNumber':
				return isset($exif['COMPUTED'][$tag]) ? $exif['COMPUTED'][$tag] : '';
			case 'Make': case 'Model':
				return isset($exif[$tag]) ? ucwords(strtolower($exif[$tag])) : '';
			case 'FocalLength':
				$length = '';
				if (isset($exif[$tag])) eval('$length = '.$exif[$tag].';');
				return $length;
			case 'ExposureTime':
				$val = '';
				if (isset($exif[$tag]))
				{
					if (strstr($exif[$tag], '/'))
					{
						// Reduce the fraction
						$val_parts = explode('/', $exif[$tag]);
						$val = '1/' . ($val_parts[1] / $val_parts[0]);
					}
					elseif ($exif[$tag] < 1)
					{
						// Turn the decimal into a fraction
						$val = '1/' . (1 / $exif[$tag]);
					}
					else
					{
						$val = $exif[$tag];
					}
				}
				return $val;
			case 'DateTime':
				$format = $TMPL->fetch_param('format');
				$date = strtotime(isset($exif['DateTimeOriginal']) ? $exif['DateTimeOriginal'] : $exif['DateTime']);
				return $format ? $LOC->decode_date($format, $date) : $date;
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
The VZ Exif Plugin extracts Exif data from an image and makes it available in your templates.

TAG PAIR:
=========

The following code will output <em>all</em> the available Exif data in a list. You don't need to include all the variables in your template. You can also use any of the variables in a conditional statement ({if aperture}Aperture: {aperture}{/if}).

{exp:vz_exif:exif image="{photo}"}
<ul>
	<li>File Size: {size}kb</li>
	<li>Dimensions: {width}x{height}</li>
	<li>Taken on: {date format="%F %d, %Y"}</li>
	<li>Camera brand: {make}</li>
	<li>Camera: {model}</li>
	<li>Focal length: {focal_length}mm{if focal_length_equiv} (<abbr title="35mm equivalent">{focal_length_equiv}mm-e</abbr>){/if}</li>
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