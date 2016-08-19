# Processing JavaScript

You can use plain javascript (CoffeeScript is not supported). You can add dependencies via Bower or when no Bower package is available, you can put some external
scripts into special directory `websrc/scripts/libs

# Bower dependencies

Standard way how to add dependency is through Bower. In `bower.json` you can specify dependencies, that will download their js, css, scss content, fonts etc.

## Structure



## Conditional adding of dependency (when you need to add js when IE9, when mobile device, etc.)

You can put your script out of all other scripts (no linting or minification is processed). These files are supposed to be small pieces of code, so they
will be only coppied from `websrc/scripts/standalone` to `www/js` directoy and you have to link them into layout manually.
