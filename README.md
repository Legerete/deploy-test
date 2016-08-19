# Prerequisities

1. Download Node.js
1. Download Gulp and Bower via npm globally so that they can be accessible in command line
1. Install EditorConfig plugin to your IDE

# How to run the project

1. Clone repository
1. Copy file `PROJECT_ROOT/app/config/config.local.neon.example` as `PROJECT_ROOT/app/config/config.local.neon`
1. Change properties in `PROJECT_ROOT/app/config/config.local.neon` to your local server
1. Go to root of the project and run `$ composer install`
1. Install Node.JS dependencies and modules by `$ npm install`
1. Install Gulp and Bower dependencies and modules by `$ npm run build`
1. To compile source files run Gulp `$ gulp --no-watch`
1. Copy file `PROJECT_ROOT/bs-config.json.example` as `PROJECT_ROOT/bs-config.json`
1. Change settings for BrowserSync to correspond your server
1. To automatic Gulp watch task run `$ gulp` only

# Preprocessing web sources

[Sprites, image minification](./readme/IMAGES)
[Stylesheets, SASS](./readme/STYLESHEETS)
[JavaScript](./readme/SCRIPTS)
[Versioning of web sources](./readme/VERSIONING)

# Code Standards

There are some coding standars that should be followed. There are described CS for each language and how it is tested:
[Coding standards, settings](./readme/CODE_STANDARDS)

## How to add custom packages to Bootstrap
1. Edit file `PROJECT_ROOT/bower.json` on root of the project
1. Find section `overrides` -> `bootstrap-sass` -> `main`
1. Add someone CSS or JavaScript of Bootstrap components into list

_Important note:_ Never edit `wiredep` in file `PROJECT_ROOT/bower.json`!

## How to use Doctrine
- CLI Doctrine tool use `PROJECT_ROOT/app/cli-config.php`
- ER diagrams and clean database structure as SQL queries are stored in `PROJECT_ROOT/documentations/database`

### New database table or changes in ERD
1. Modify ERD via Navicat
1. Save modified file to `PROJECT_ROOT/documentations/database` too
1. Run command line, change directory to `PROJECT_ROOT/app` and run command `$ ../vendor/bin/doctrine orm:convert:mapping annotation ./ --from-database`
   _Tip_: use parameter `--filter="entity_name"` and change destination path for better location of each entity
1. Fix namespace of entity, add getters and setters
1. For detailed help run `$ ../vendor/bin/doctrine orm:convert:mapping --help`
