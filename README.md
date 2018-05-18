# ZooRoyal Coding Standard

This repository holds the necessary data to use the ZooRoyal coding standard. It incorporates
* [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer) and its configuration
* [PHP Mess Detector](https://github.com/phpmd/phpmd) and its configuration
* [PHP Copy Paste Detector](https://github.com/sebastianbergmann/phpcpd)
* [PHP-Parallel-Lint](https://github.com/JakubOnderka/PHP-Parallel-Lint)
* [ES-LINT](https://github.com/eslint/eslint) and its configuration
* [STYLE-LINT](https://github.com/stylelint/stylelint) and its configuration

Furthermore there are the bash scripts in /src/bin which are meant to be used in the .travis build file. They search your source code for files to check by their static code analysis tool. Information about their usage can be found by calling them with -h option.

# Installation

To install this package just run

```bash
composer require "zooroyal/coding-standard"
```

# Usage

Run the command to get usage instructions. 
```bash
php vendor/bin/coding-standard
```

For examples just have a look an the .travis.yml


