Nette media
===========

Správa a poskytování souborů a obrázků v Nette. On-the-fly generování náhledů obrázků pro vaši Nette aplikaci. Velmi inspirováno (a kopírováno) z dotBlue (http://dotblue.net).

Uploadujeme obrázky do nějakého úložiště. Poskytujeme konkrétní náhledy
obrázků. Umožňuje stahování souborů.


## Instalace

Doporučený způsob pomocí Composer:

	composer require tacoberu/nette-media



## Odkazování v šabloně

Původní obrázek:

	<img n:media="users/david.jpg">
	<a href={media users/david.jpg}>


Náhled obrázku. Volíme z předem určených variant (ochrana před DoS). Náhled se nám vygeneruje automaticky na požádání a uloží pro příště.

	<a href={media users/david.jpg, small}>
	<img n:media="users/david.jpg, small">


Obrázek vynucený ke stažení:

	<a href={download users/david.jpg}>


A neobrázkový soubor:

	<a href={media users/david.pdf}>
	<link rel="stylesheet" media="screen,projection,tv" href="{media screen.css}" />



## Konfigurace

	extensions:
		media: Taco\NetteWebImages\Extension(%tempDir%)

	media:
		# Kde se berou zdrojové obrázky.
		providers:
			- Taco\NetteWebImages\DefaultImageProvider(%appDir%/../../var/uploads)
		routes:
			- 'assets/<id>'
		# Transformace nad obrázky. Typicky náhledy.
		rules:
			medium: [width: 300, height: 200, algorithm: fit, quality: 75]
			small:  [width: 100, height: 100, algorithm: fit, quality: 75]
			big:    [width: 800, height: 600, algorithm: fit, quality: 100]
