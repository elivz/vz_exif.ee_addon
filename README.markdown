VZ EXIF
=======

The VZ Exif Plugin extracts Exif data from an image and makes it available in your templates.

TAG PAIR:
---------

The following code will output <em>all</em> the available Exif data in a list. You don't need to include all the variables in your template.

{exp:vz_exif:exif image="{photo}"}
&lt;ul&gt;
	&lt;li&gt;File Size: {size}kb&lt;/li&gt;
	&lt;li&gt;Dimensions: {width}x{height}&lt;/li&gt;
	&lt;li&gt;Taken on: {date format=&quot;%F %d, %Y&quot;}&lt;/li&gt;
	&lt;li&gt;Camera brand: {make}&lt;/li&gt;
	&lt;li&gt;Camera: {model}&lt;/li&gt;
	&lt;li&gt;Focal length: {focal_length}mm (&lt;abbr title=&quot;35mm equivalent&quot;&gt;{focal_length_equiv}mm-e&lt;/abbr&gt;)&lt;/li&gt;
	&lt;li&gt;Aperture: {aperture}&lt;/li&gt;
	&lt;li&gt;Shutter speed: {shutter} seconds&lt;/li&gt;
	&lt;li&gt;ISO: {iso}&lt;/li&gt;
	&lt;li&gt;Processed with: {software}&lt;/li&gt;
	&lt;li&gt;Flash used: {flash}&lt;/li&gt;
&lt;/ul&gt;
{/exp:vz_exif:exif}

SINGLE TAGS:
------------

You can also get each piece of data using a single tag in the form: {exp:vz_exif:[exif_tag_to_get]}. The Exif data is cached after the first time you get it, so the performance cost for this method should be insignificant.

&lt;p&gt;The following photo was taken with a {exp:vz_exif:model image=&quot;{photo}&quot;} camera.&lt;/p&gt;

PARAMETERS:
-----------

image= (required)
This is the url of the image to get Exif data from. The image needs to be on your server and in JPEG format. Typically, this will be in the form of a custom field inside your {exp:channels} tag pair.

root= (optional)
VZ Exif will do its best to determine the server path to the image you give it, but in some cases you may want to specify the root path manually. The root url will simply be prepended to the image url (minus the domain name, if there is one).