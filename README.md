# Laravel FileStorage package

[![Latest Stable Version](https://img.shields.io/packagist/v/imahmood/laravel-file-storage.svg?style=flat-square)](https://packagist.org/packages/imahmood/laravel-file-storage)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/imahmood/laravel-file-storage/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/imahmood/laravel-file-storage/actions?query=workflow%3Atests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/imahmood/laravel-file-storage.svg?style=flat-square)](https://packagist.org/packages/imahmood/laravel-file-storage)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

FileStorage is a Laravel package that simplifies the process of uploading and attaching files to models,
while also automating preview generation and image optimization.

## Features

- Easily upload and attach files to models.
- Specify the storage disk dynamically using the `onDisk` method.
- Automatically generate previews for images and PDFs and optimize images, improving user experience.

## Requirements

Before using this package, make sure your environment meets the following requirements:

- **PHP:** 8.1 or later.
- **Extensions:** `ext-ffi` extension is required.
- **System Packages:** `libvips42` library is necessary for image processing.


## Documentation

* [Installation](docs/installation.md)
* [Quick start](docs/quick-start.md)
* [Getting a file URL](docs/getting-url.md)
* [Methods](docs/methods.md)
* [Events](docs/events.md)

## License
The MIT License (MIT). Please see [License File](LICENSE) for more information.
