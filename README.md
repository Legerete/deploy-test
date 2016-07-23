# How to run the project

1. Clone repository
2. Copy file `PROJECT_ROOT/app/config/config.local.neon.example` as `PROJECT_ROOT/app/config/config.local.neon`
3. Change properties in `PROJECT_ROOT/app/config/config.local.neon` to your local server
4. Go to root of the project and run `$ composer install`
5. Install Node.JS dependencies and modules by `$ npm install`
6. Install Gulp and Bower dependencies and modules by `$ npm run build`
7. To compile source files run Gulp `$ gulp --no-watch`
8. Copy file `PROJECT_ROOT/bs-config.json.example` as `PROJECT_ROOT/bs-config.json`
9. Change settings for BrowserSync to correspond your server
10. To automatic Gulp watch task run `$ gulp` only

## How to add custom packages to Bootstrap
1. Edit file `PROJECT_ROOT/bower.json` on root of the project
2. Find section `overrides` -> `bootstrap-sass` -> `main`
3. Add someone CSS or JavaScript of Bootstrap components into list

_Important note:_ Never edit `wiredep` in file `PROJECT_ROOT/bower.json`!

## How to use Doctrine
- CLI Doctrine tool use `PROJECT_ROOT/app/cli-config.php`
- ER diagrams and clean database structure as SQL queries are stored in `PROJECT_ROOT/documentations/database`

### New database table or changes in ERD
1. Modify ERD via Navicat
2. Save modified file to `PROJECT_ROOT/documentations/database` too
3. Run command line, change directory to `PROJECT_ROOT/app` and run command `$ ../vendor/bin/doctrine orm:convert:mapping annotation ./ --from-database`
   _Tip_: use parameter `--filter="entity_name"` and change destination path for better location of each entity
4. Fix namespace of entity, add getters and setters
5. For detailed help run `$ ../vendor/bin/doctrine orm:convert:mapping --help`