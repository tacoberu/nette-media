Nette media
===========

Manage and provide files and images in Nette. On-the-fly generated images thumbnails for your Nette app. Very inspired (and copy) from dotBlue (http://dotblue.net).

We upload images to some storage. We provide specific previews
pictures. Allows you to download files.



## Using in a template

Original image:

	<img n:media="users/david.jpg">
	<a href={media users/david.jpg}>


Image preview. We choose from predetermined variants (protection against DoS). The preview is generated automatically on request and saved for the next time.

	<a href={media users/david.jpg, small}>
	<img n:media="users/david.jpg, small">


Image forced to download:

	<a href={download users/david.jpg}>


And non-image file:

	<a href={media users/david.pdf}>
	<link rel="stylesheet" media="screen,projection,tv" href="{media screen.css}" />



## Configuration

	extensions:
		media: Taco\NetteWebImages\Extension

	media:
		# Where to save thumbnails. It can be lubricated at any time.
		cacheDir: %appDir%/../temp/assets
		# Where source images are taken.
		providers:
			- Taco\NetteWebImages\DefaultImageProvider(%appDir%/../../var/uploads)
		routes:
			- 'assets/<id>'
		# Transformations over images. Typically thumbnails.
		rules:
			medium: [width: 300, height: 200, algorithm: fit, quality: 75]
			small:  [width: 100, height: 100, algorithm: fit, quality: 75]
			big:    [width: 800, height: 600, algorithm: fit, quality: 100]
