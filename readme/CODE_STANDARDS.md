# Coding Standards

There are some code standards to follow. Basic standards are set in `.editorconfig` file.
It says basics for all source files, e.g. UTF-8 encoding without BOM, new line file endings, using tabs over spaces for indents.

There is also some code standard specifications for PHP, JavaScript and SASS that you should follow:

## PHP

PHP sources should follow Nette Conding Standard `https://nette.org/en/coding-standard` that is modified PSR-1 and PSR-2 standard.
There are only two modifications:
- tabs are used for indenting
- PHP constants TRUE, FALSE and NULL are in upper case

Actually there is not any CS checker available, it depends only on GIT server.

## JavaScript

Javascript code standard is based on "StandardJS" `http://standardjs.com/rules.html` with some modifications that are:
- Always use semicolon
- Single quotes over standard quotes
- Tabs over two spaces
- Forcing JSDoc for each function written

Standards are linted before JavaScript is processed with Gulp.

# SASS

@todo WRITE SASS STANDARDS

Standards are linted before SASS is compiled with Gulp.
