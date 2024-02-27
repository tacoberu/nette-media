Nette media
===========

Poskytování souborů a obrázků v Nette. On-the-fly generování náhledů obrázků. Velmi inspirováno (a kopírováno) z dotBlue (http://dotblue.net).

Obvykle chceme nahrát nějaké soubory či obrázky, které si ukládáme do nějakého jednoho nebo více úložišt (to není součástí tohoto projektu).
K souborům obvykle chceme přistupovat nějak pěkně, konfigurovatelně.
Na obrázky chceme vytvářet nějaké operace, náhledy, vodoznaky.
Náhledy obvykle chceme generovat až když jsou potřeba a pokud možno jen jednou.
K výsledným náhledům chceme přistupovat přímo, pomocí mod_rewrite (či podobné techniky) aby to bylo co nejrychlejší.
Také se hodí, umožnit vynutit stáhnutí obrázku.



## Instalace

Doporučený způsob pomocí Composer:

	composer require tacoberu/nette-media



## Odkazování v šabloně

Původní obrázek:

	<img n:media="media/users/david.jpg">
	<a href={media media/users/david.jpg}>

Vygeneruje:

	/media/users/david.jpg


Náhled obrázku volíme z předem určených variant (ochrana před DoS). Náhled se nám vygeneruje automaticky na požádání a uloží do cache pro příště.

	<a href={media media/users/david.jpg, small}>
	<img n:media="media/users/david.jpg, small">

Vygeneruje:

	/media/users/david.jpg?small


Obrázek vynucený ke stažení:

	<a href={download users/david.jpg}>

Vygeneruje:

	/media/users/david.jpg?download


A neobrázkový soubor:

	<a href={media media/users/david.pdf}>
	<link rel="stylesheet" media="screen,projection,tv" href="{media media/screen.css}" />



## Konfigurace

	extensions:
		media: Taco\NetteMedia\Extension

	media:
		# Kde se berou zdrojové obrázky.
		providers:
			- Taco\NetteMedia\FileBasedProvider(%appDir%/../../var/uploads)

		# Z url potřebujeme odvodit že se jedná obrázek, a v jaké variantě jej získáváme
		route: Taco\NetteMedia\Router('media')

		# Transformace nad obrázky. Typicky náhledy.
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

		# Kam se ukládají náhledy. Možno dát rovnou public, a nakonfigurovat .htaccess
		cache: Taco\NetteMedia\FileBasedThumbnailCache(%wwwDir%/cache)


## Ukázka .htaccess

Je hezké, že nám systém vygeneruje náhled on-the-fly. Ale i na ty náhledy musíme přistupovat přes Nette, což by šle lépe.
Přidáme si (ručně) takovéto .htaccess pravidla.

	# nakešované náhledy obrázků
	RewriteCond %{REQUEST_URI} ^/media/(.+)$
	RewriteCond %{QUERY_STRING} ^(preview|medium|small|big)$
	RewriteRule ^media/(.*)$ /cache/%{QUERY_STRING}/$1 [L]

	# nakešované original obrázků
	RewriteCond %{REQUEST_URI} ^/media/(.+)$
	RewriteCond %{QUERY_STRING} ^$
	RewriteRule ^media/(.*)$ /cache/__orig__/$1 [L]

	# front controller
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule ^ index.php [L]

Nyní by to mělo fungovat takto:

- Zobrazíme si url /media/users/david.jpg?preview
- Zjistíme, že náhled obrázku neexistuje, vytáhne se tedy originál obrázku, a provede se na něj transformace preview.
- Výsledek se uloží %wwwDir%/cache/preview
- Při dalším přístupu se již najde obrázek v /cache/preview/users/david.jpg a zobrazí se rovnou ještě před tím, než se použije php.
