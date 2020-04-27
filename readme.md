# PagesModule

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

Create CMS pages module

## Installation

Via Composer

``` bash
$ composer require zdrojowa/pages-module
```

## NPM required:

``` bash
"@ckeditor/ckeditor5-vue": "1.0.1",
"@ckeditor/ckeditor5-build-classic": "18.0.0",
"axios": "^0.19",
"bootstrap-vue": "2.11.0"
"vue": "^2.6.10",
"vue-multiselect": "2.1.6,
"vuedraggable": "2.23.2",
```

## Usage
- Add in webpack.mix.js

``` bash
mix.module('PagesModule', 'vendor/zdrojowa/pages-module');
```

- Add module PagesModule in config/selene.php

``` bash
'modules' => [
    PagesModule::class,
],

'menu-order' => [
    'PagesModule',
],
```

- Add templates in resources/views/partials:

``` bash
main.blade.php
```

- run npm

``` bash
npm install
npm run prod
```

- Add lang in Settings

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

## Credits

- [author name][link-author]
- [All Contributors][link-contributors]

## License

license. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/zdrojowa/pages-module.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/zdrojowa/pages-module.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/zdrojowa/pages-module/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/zdrojowa/pages-module
[link-downloads]: https://packagist.org/packages/zdrojowa/pages-module
[link-travis]: https://travis-ci.org/zdrojowa/pages-module
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/zdrojowa
[link-contributors]: ../../contributors
