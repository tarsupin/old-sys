<?php /*

----------------------------------------------
------ About the Image_Responsive Class ------
----------------------------------------------

This class allows you to display photos in a responsive manner to accommodate mobile devices.

The primary method of this class, ::place(), will allow you to provide a photo to be used for desktops, tablets, and mobile devices. You can set the breakpoints of the screen size where these responsive photos should change.

For example, if you wanted to have a desktop image and a mobile image that gets used once the screen width has changed to 450 pixels or less, you could use the following command:
	
	// Provide a mobile image for <= 450 pixels
	echo Image_Responsive::place("/images/desktop.jpg", "/images/mobile.jpg", 450);

Since these images are being generated through a class, you must assign the CSS class through the $class parameter. To set the image above with the "resp-article" class, the command would change to the following:
	
	// Provide a mobile image for <= 450 pixels; apply the "resp-article" class
	echo Image_Responsive::place("/images/desktop.jpg", "/images/mobile.jpg", 450, "", 0, "resp-article");

------------------------------------------
------ Examples of using this class ------
------------------------------------------

// Include Responsive Script (allows responsive images on the page)
Image_Responsive::prepare();

// Display the Image
echo '<a href="/image-link">' . Image_Responsive::place("http://cdn.unifaction.com.local/assets/image-standard.jpg", "http://cdn.unifaction.com.local/assets/image-mobile.jpg", 600, "http://cdn.unifaction.com.local/assets/image-tablet.jpg", 1200, "resp-article") . '</a>';


-------------------------------
------ Methods Available ------
-------------------------------

// Prepares the page for responsive images
Image_Responsive::prepare();

// Loads an image for the appropriate device
Image_Responsive::place($imageURL, [$mobileURL], [$mobileBreak], [$tabletURL], [$tabletBreak], [$class]);

*/

abstract class Image_Responsive {
	
	
/****** Prepare a page for responsive photos ******/
	public static function prepare (
	)					// RETURNS <void>
	
	// Image_Responsive::prepare();
	{
		Metadata::addHeader('
			<script>document.createElement("picture");</script>
			<script src="' . CDN . '/scripts/picturefill_v1.js" async></script>'
		);
	}
	
	
/****** Display a Responsive Photo ******/
	public static function place
	(
		$imageURL			// <str> The URL of the base image
	,	$mobileURL = ""		// <str> The URL of the mobile image, if applicable
	,	$mobileBreak = 450	// <int> The breakpoint to switch from mobile and the standard image.
	,	$tabletURL = ""		// <str> The URL of the tablet image, if applicable
	,	$tabletBreak = 900	// <int> The breakpoint to switch to a tablet image.
	,	$class = ""			// <str> The class to assign to the image.
	)						// RETURNS <str> HTML to place the photo.
	
	// echo Image_Responsive::place($imageURL, [$mobileURL], [$mobileBreak], [$tabletURL], [$tabletBreak], [$class]);
	{
		// If only the main image is available
		if($mobileURL == "" and $tabletURL == "")
		{
			return '<img' . ($class == "" ? "" : ' class="' . $class . '"') . ' src="' . $imageURL . '" />';
		}
		
		// Prepare Class
		$class = ($class == "" ? "" : ' data-class="' . $class . '"');
		
		// Prepare the responsive image
		return '
		<span class="responsive-photo" data-picture data-alt="">' .
			($mobileURL != "" ? '<span data-src="' . $mobileURL . '"' . $class . '></span>' : '') .
			($tabletURL != "" ? '<span data-src="' . $tabletURL . '"' . $class . ' data-media="(min-width: ' . $mobileBreak . 'px)"></span>' : '') . '
			<span data-src="' . $imageURL . '"' . $class . ' data-media="(min-width: ' . max($mobileBreak, $tabletBreak) . 'px)"></span>
			
			<!-- Fallback -->
			<noscript><img src="' . $imageURL . '" /></noscript>
		</span>';
	}
	
	
/****** Display a Photo with Responsive Options (version 2.0 using the picture element) ******/
# Note: since this causes JS disabled browsers not to work, this is considered deprecated for now.
	public static function responsive2_0
	(
		$imageURL			// <str> The directory to use for the photo.
	,	$mobileURL = ""		// <str> The URL of the mobile image, if applicable
	,	$mobileBreak = 500	// <int> The breakpoint to switch from mobile and the standard image.
	,	$tabletURL = ""		// <str> The URL of the tablet image, if applicable
	,	$tabletBreak = 900	// <int> The breakpoint to switch to a tablet image.
	,	$class = ""			// <str> The class to assign to the image.
	)						// RETURNS <str> HTML to place the photo.
	
	// echo Image_Responsive::place($imageURL, [$mobileURL], [$mobileBreak], [$tabletURL], [$tabletBreak], [$class]);
	{
		// Prepare Class
		$class = ($class == "" ? "" : ' class="' . $class . '"');
		
		// picturefill.js solution
		// <script src="' . CDN . '/scripts/picturefill_v2.min.js"></script>
		
		return '
		<picture>' .
			($mobileURL != "" ? '<source srcset="' . $mobileURL . '">' : '') .
			($tabletURL != "" ? '<source srcset="' . $tabletURL . '" media="(min-width: ' . $mobileBreak . 'px)>' : '') . '
			<source srcset="' . $imageURL . '" media="(min-width: ' . max($mobileBreak, $tabletBreak) . 'px)">
			<img' . $class . ' srcset="' . $imageURL . '">
		</picture>';
	}
		
}
