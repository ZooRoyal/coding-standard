#!/bin/sh

set -e;

if ! docker info > /dev/null 2>&1; then
    echo Docker not available. You need to install docker if you want to use \
        the Coding-Standard this way. Aborting!;
    exit 1;
fi

docker run --rm -it -v "$(realpath .)":/app -v "$(realpath .)/vendor/zooroyal/coding-standard/config":/coding-standard/config -v "$(realpath .)/vendor/zooroyal/coding-standard/config":/coding-standard/config ghcr.io/rdss-sknott/cs-src:___VERSION___ $@

