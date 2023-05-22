# ZooRoyal Coding-Standard
![Release](https://img.shields.io/github/v/release/zooroyal/coding-standard?label=Latest%20Release)
[![Packagist Release](https://img.shields.io/packagist/v/ZooRoyal/coding-standard.svg?longCache=true&label=Available%20on%20Packagist)](https://packagist.org/packages/zooroyal/coding-standard)
[![License](https://img.shields.io/packagist/l/ZooRoyal/coding-standard.svg?longCache=true)](/blob/main/LICENSE)

## Description

This is a [Shim](https://en.wikipedia.org/wiki/Shim_(computing)) for the
[zooroyal/coding-standard-source](https://github.com/ZooRoyal/coding-standard-source)
package. For details on how to use the ZooRoyal Coding-Standard, please refer
to the README of the [source package](https://github.com/ZooRoyal/coding-standard-source#readme).

## Why?

In an afford to make the ZooRoyal Coding-Standard more accessible, we
decided to wrap the source package in a docker image. This way, there is
only one dependency to install, which is docker. The composer image has no
dependency on PHP or any other composer packages.

## Requirements

This packages **requires docker to be installed and accessible** on your system.

## Usage

The composer package exposes a bash script under
`vendor/bin/coding-standard`. This script is a wrapper for the docker
application. It should behave exactly like the source package, but it is
creating a docker container in the background and executes the source.

To learn more about the usage of ZooRoyal Coding-Standard, please refer to the
`--help` output of the script or the README of the
[source package](https://github.com/ZooRoyal/coding-standard-source#readme).
