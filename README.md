
![Build Status](https://github.com/ZooRoyal/coding-standard/workflows/Continuous%20Integration/badge.svg?branch=master)
[![Packagist Release](https://img.shields.io/packagist/v/ZooRoyal/coding-standard.svg?longCache=true)](https://packagist.org/packages/zooroyal/coding-standard)
[![License](https://img.shields.io/packagist/l/ZooRoyal/coding-standard.svg?longCache=true)](/blob/master/LICENSE)


# ZooRoyal Coding Standard

This repository holds the necessary data to use the ZooRoyal coding standard. It incorporates
* [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer) and its configuration
* [PHP Mess Detector](https://github.com/phpmd/phpmd) and its configuration
* [PHP Copy Paste Detector](https://github.com/sebastianbergmann/phpcpd)
* [PHP-Parallel-Lint](https://github.com/JakubOnderka/PHP-Parallel-Lint)
* [PHPStan - PHP Static Analysis Tool](https://github.com/phpstan/phpstan)
* [ES-LINT](https://github.com/eslint/eslint) and its configuration
* [STYLE-LINT](https://github.com/stylelint/stylelint) and its configuration

Furthermore there is a php script in src/bin which is meant to be used in the .travis build file. It searches your source code for files to check by its static code analysis tools. Information about its usage can be found by calling it with -h option.

# Installation

To install this package just run

```bash
composer require --dev "zooroyal/coding-standard"
```

## Eslint and StyleLint

In case you want to use eslint and stylelint checks you have to install the packages from the package.json in the root folder of your project.

### Local Installation

You may install the packages locally in your project. For this to happen you need to follow the following  steps:

1. `composer require --dev zooroyal/coding-standard`
2. `npm install --save-dev vendor/zooroyal/coding-standard`

### Global Installation

You may have installed eslint and stylelint globally in your system. If you want coding-standard to try to use them
 just make sure coding-standard is installed properly.

```bash
composer require --dev zooroyal/coding-standard
```

# Update

To update this package just run

```bash
composer update "zooroyal/coding-standard"
```

# Usage coding-standard

**Please keep in mind, that coding-standard can only run from inside a git
repository.**

Run the command to get usage instructions.
```bash
php vendor/bin/coding-standard
```
```
Available commands:
  find-files                Finds files for code style checks.
  help                      Displays help for a command
  list                      Lists commands
 checks
  checks:forbidden-changes  Checks for unwanted code changes.
 sca
  sca:all                   Run all static code analysis tools.
  sca:copy-paste-detect     Run PHP-CPD on PHP files.
  sca:eslint                Run ESLint on JS files.
  sca:mess-detect           Run PHP-MD on PHP files.
  sca:parallel-lint         Run Parallel-Lint on PHP files.
  sca:sniff                 Run PHP-CS on PHP files.
  sca:stylelint             Run StyleLint on Less files.
  sca:stan                  Run PHPStan on PHP files.
```

## Example `sca:all`

```bash
vendor/bin/coding-standard sca:all -h
```
```
Usage:
  sca:all [options]

Options:
  -t, --target=TARGET      Finds Files which have changed since the current branch parted from the target
                           branch only. The Value has to be a commit-ish. [default: false]
  -a, --auto-target        Finds Files which have changed since the current branch parted from the parent
                           branch only. It tries to find the parent branch by automagic.
  -f, --fix                Runs tool to try to fix violations automagically.
  -p, --process-isolation  Runs all checks in separate processes. Slow but not as resource hungry.
  -h, --help               Display this help message
  -q, --quiet              Do not output any message
  -V, --version            Display this application version
      --ansi               Force ANSI output
      --no-ansi            Disable ANSI output
  -n, --no-interaction     Do not ask any interactive question
  -v|vv|vvv, --verbose     Increase the verbosity of messages: 1 for normal output, 2 for more verbose output
                           and 3 for debug

Help:
  This tool executes all static code analysis tools on files of this project. It ignores files which are in
  directories with a .dont<toolshortcut> file. Subdirectories are ignored too.
```

The all command forwards all applicable parameters to all implemented static code analysis tools.

```bash
vendor/bin/coding-standard sca:all -a -f
```

This command for example tries to find the parent branch by automagic (-a) and tells all static code analysis
tools to fix found violations if they are able to.

```bash
vendor/bin/coding-standard sca:all -t origin/master
```

This command computes the diff to the branch origin/master and searches for all violations in this files.

For examples just have a look an the .travis.yml

