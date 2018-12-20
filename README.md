Nette media
===========

Status BETA. Don't using!

Správa a poskytování souborů a obrázků v Nette.

Uploadujeme obrázky do nějakého úložiště. Poskytujeme konkrétní náhledy
obrázků. Umožňuje stahování souborů.



## Odkazování v šabloně

original image:

	<img n:src="users, 'david.jpg'">
	<a href={src users, 'david.jpg'}>

thumbnail with width:

	<img n:src="users, 'david.jpg', 150">

thumbnail with width and height:

	<img n:src="users, 'david.jpg', '150x150'">

thumbnail with different resize method (default is fit):

	<img n:src="users, 'david.jpg', 150, stretch">

absolute urls (works only at latte):

	<img n:src="users, '//david.jpg'">

You can even use names without files' extensions and images-manager will try to find it for you:

	<img n:src="users, david, 100">



## Konfigurace

extensions:
	media: Taco\NetteWebImages\Extension

media:
	cacheDir: %appDir%/../temp/assets
	providers:
		- Taco\NetteWebImages\DefaultImageProvider(%appDir%/../../var/uploads)
	routes:
		- 'assets/<id>.<ext>'
	rules:
		medium: [width: 300, height: 200, algorithm: fit, quality: 75]
		small:  [width: 100, height: 100, algorithm: fit, quality: 75]
		big:    [width: 800, height: 600, algorithm: fit, quality: 100]


## TODO
- Zkontrolovat routy, zda tam jde upravovat size. Možná přejmenovat na variants.
- Resize předělat na transformační keychain.
- Cache do samostatné třídy.
- Načítat statická data.


## Poznámky
Statická data, jako jsou javascripty, nebo css se nenačítají pomocí media on-demand. Zde spoléháme na aktualizaci pomocí externího nástroje.
