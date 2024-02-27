Nette media
===========

Manage and provide files and images in Nette. On-the-fly generated images thumbnails. Very inspired (and copy) from dotBlue (http://dotblue.net).

We usually want to upload some files or images that we store in one or more repositories (this is not part of this project).
We usually want to access files in a nice, configurable way.
We want to create some operations, previews, watermarks on images.
We usually want to generate previews only when needed and if possible only once.
We want to access the resulting previews directly, using mod_rewrite (or a similar technique) to make it as fast as possible.
It also comes in handy, allowing you to force an image download.



## Installation

The recommended way to install is via Composer:

	composer require tacoberu/nette-media



## Using in a template

Original image:

	<img n:media="users/david.jpg">
	<a href={media users/david.jpg}>

It will generate:

	/media/users/david.jpg


Image preview. We choose from predetermined variants (protection against DoS). The preview is generated automatically on request and saved for the next time.

	<a href={media users/david.jpg, small}>
	<img n:media="users/david.jpg, small">

It will generate:

	/media/users/david.jpg?small


Image forced to download:

	<a href={download users/david.jpg}>

It will generate:

	/media/users/david.jpg?download


And non-image file:

	<a href={media users/david.pdf}>
	<link rel="stylesheet" media="screen,projection,tv" href="{media screen.css}" />



## Configuration

	extensions:
		media: Taco\NetteMedia\Extension

	media:
		# Where source images are taken.
		providers:
			- Taco\NetteMedia\FileBasedProvider(%appDir%/../../var/uploads)

		# We need to deduce from the url that it is an image, and in which variant we get it
		route: Taco\NetteMedia\Router('media')

		# Transform over images. Typically previews.
		transformations:
			preview:
				- Taco\NetteMedia\ResizeTransformation(75, 250, 250, 'fit')
			medium:
				- Taco\NetteMedia\ResizeTransformation(75, 264, 264, 'fit')
			small:
				- Taco\NetteMedia\ResizeTransformation(75, 100, 100, 'fit')
			big:
				- Taco\NetteMedia\ResizeTransformation(100, 800, 600, 'fit')
				# - YourApp\NetteMedia\WatterMark

		# Where the previews are stored. It is possible to set it to public and configure .htaccess
		cache: Taco\NetteMedia\FileBasedThumbnailCache(%wwwDir%/cache)


## Sample .htaccess

It's nice that the system generates a preview for us on-the-fly. But even those previews have to be accessed via Nette, which would be better.
Let's add (manually) such .htaccess rules.

	# cached image previews
	RewriteCond %{REQUEST_URI} ^/media/(.+)$
	RewriteCond %{QUERY_STRING} ^(preview|medium|small|big)$
	RewriteRule ^media/(.*)$ /cache/%{QUERY_STRING}/$1 [L]

	# cached image original
	RewriteCond %{REQUEST_URI} ^/media/(.+)$
	RewriteCond %{QUERY_STRING} ^$
	RewriteRule ^media/(.*)$ /cache/__orig__/$1 [L]

	# front controller
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule ^ index.php [L]

Now it should work like this:

- We will display the url /media/users/david.jpg?preview
- We find that the preview of the image does not exist, so the original image is extracted and the preview transformation is performed on it.
- The result is saved in %wwwDir%/cache/preview
- The next time you access it, the image is already found in /cache/preview/users/david.jpg and displayed right away before php is used.
