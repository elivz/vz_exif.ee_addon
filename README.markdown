VZ Exif
=======

The VZ Exif Plugin extracts Exif data from an image and makes it available in your templates.

Tag Pair
--------

The following code will output <em>all</em> the available Exif data in a list. You don't need to include all the variables in your template. You can also use any of the variables in a conditional statement ({if aperture}Aperture: {aperture}{/if}).

	{exp:vz_exif image="{photo}"}
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
	{/exp:vz_exif}

Single Tags
-----------

You can also get each piece of data using a single tag in the form: {exp:vz_exif:[exif_tag_to_get]}. The Exif data is cached after the first time you get it, so the performance cost for this method should be insignificant.

	<p>The following photo was taken with a {exp:vz_exif:model image="{photo}"} camera.</p>

Parameters
----------

image= (required)
This is the url of the image to get Exif data from. The image needs to be on your server and in JPEG format. Typically, this will be in the form of a custom field inside your {exp:channels} tag pair.

path= (optional)
VZ Exif will do its best to determine the server path to the image you give it, but in some cases you may want to specify the root path manually. The root url will simply be prepended to the image url (minus the domain name, if there is one).

Configuration Variables
-----------------------

$config['vz_exif_offset'] = 3600;
The number of seconds to add to the Exif timestamp. In some cases, the timestamps will be consistently off by a certain amount. This variable allows you to correct for that. Can be be a negative number.

$config['vz_exif_path'] = "/var/www/public/";
Sets a default server root. This is exactly the same as the tag path parameter, but it saves you from adding that parameter to every tag. The root parameter in your template will override this variable.

Acknowlegements
---------------

Thanks to @robinsowell for adding EE 5 compatibility.