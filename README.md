# How to run the project

1. Clone repository
2. Copy file `PROJECT_ROOT/app/config/config.local.neon.example` as `PROJECT_ROOT/app/config/config.local.neon`
3. Change properties in `PROJECT_ROOT/app/config/config.local.neon` to your local server
4. Go to root of the project and run `# composer install`
5. Install Node.JS dependencies and modules by `# npm install`
6. Install Gulp and Bower dependencies and modules by `# npm run build`
7. Run Grunt to compile Less files `# gulp`

## How to add custom packages to Bootstrap
1. Edit file `bower.json` on root of the project
2. Find section `overrides` -> `bootstrap-sass` -> `main`
3. Add someone CSS or JavaScript of Bootstrap components into list

_Note:_ Never edit `wiredep` in file `bower.json`!
