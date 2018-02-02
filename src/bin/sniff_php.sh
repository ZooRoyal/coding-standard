#!/bin/bash

# A POSIX variable
OPTIND=1         # Reset in case getopts has been used previously in the shell.

##### Variables #####

scriptDirectory=""
sniffCommand=""

##### Functions #####

function constructor()
{
    if [ "$(uname -s)" = 'Darwin' ]; then
        scriptDirectory=$(realpath "$(dirname "$(readlink "$(realpath $0)" || echo "$(echo "$(realpath $0)" | sed -e 's,\\,/,g')")")")
    else
        scriptDirectory=$(realpath "$(dirname "$(readlink -f "$(realpath $0)" || echo "$(echo "$(realpath $0)" | sed -e 's,\\,/,g')")")")
    fi

    findFilesCommand="bash ${scriptDirectory}/find_files_to_check.sh -s .dontSniffPHP -f .php"
    findParentWithFile="bash ${scriptDirectory}/find_parent_with_file.sh"
    rootDirectory=$($findParentWithFile -d $scriptDirectory -f composer.lock)
    sniffCommand="php ${rootDirectory}/vendor/bin/phpcs -p --extensions=php --standard=${scriptDirectory}/../config/phpcs/ZooroyalDefault/ruleset.xml"
}

function show_help()
{
    echo "This tool executes PHP-CS on a certain set of PHP files of this Project. It ignores files which are in "
    echo "directories with a .dontSniffPHP file. Subdirectories are ignored too."
    echo "usage: sniff_php [--help] [-t <git tree-ish>]"
    echo "    -h                   Shows this help"
    echo "    -t <git tree-ish>    Sniffs PHP-Files which have changed since the current branch parted from the target path only."
}

function sniff()
{
    local parameter=""
    local branchname=$1
    local changedPhpFiles=()

    if [ ! -z "$branchname" ];
    then
        parameter=" -t $branchname"
    fi

    changedPhpFiles=$($findFilesCommand$parameter)

    if [ -z "$changedPhpFiles" ];
    then
        echo "No PHP-Files to check!"
        exit 0
    fi

    for directory in $changedPhpFiles; do
        echo "Sniffing $directory"
        $sniffCommand $directory
        sniffResult=$?
        if ! [ "$sniffResult" -eq "0" ]
        then
            result=$sniffResult
        fi
    done

    exit $result
}

###############

constructor

while getopts "ht:" opt; do
    case "$opt" in
    h)
        show_help
        exit 0
        ;;
    t)
        echo "Sniffing in diff to $OPTARG."
        sniff $OPTARG
        ;;
    esac
done

sniff
