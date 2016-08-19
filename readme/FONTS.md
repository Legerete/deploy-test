# FONTS

## How to add fonts to the project?

There are two ways, how to add fonts.

1. **Bower dependency** - you can download fonts via Bower and with bower main files you can override, which files you want to pick from bower packages.
When you specify font files in bower package main files, these files will be copied into `www/assets/fonts` when you perform Gulp task `gulp fonts`.

2. **Add manually** - you can put font files into the `websrc/fonts` directory (you can use subdirectory for each font family too). All files from this
directory are copied into `www/assets/fonts` directory (when subdirectories are used, subdirectories will be preserved in assets directory too).

## How to specify font files in bower packages?

Every bower package has its own bower.json file, that specifies main files. These files are only, that is used in current project. If you want to modify,
which files you want to use, you can override it in your own bower.json file. There is snippet with how to do it:

```javascript
	{
 	overrides": {
 		"bootstrap-sass": {
 			"main": [
 				...
 				"./assets/fonts/bootstrap/glyphicons-halflings-regular.eot",
 				"./assets/fonts/bootstrap/glyphicons-halflings-regular.svg",
 				"./assets/fonts/bootstrap/glyphicons-halflings-regular.ttf",
 				"./assets/fonts/bootstrap/glyphicons-halflings-regular.woff",
 				"./assets/fonts/bootstrap/glyphicons-halflings-regular.woff2"
 			]
 		}
 	}

```
