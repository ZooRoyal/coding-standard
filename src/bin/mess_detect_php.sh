#!/bin/bash

# A POSIX variable
OPTIND=1         # Reset in case getopts has been used previously in the shell.

##### Variables #####

scriptDirectory=""
messDetectorCommandPrefix=""
messDetectorCommandSuffix=""

##### Functions #####

function constructor()
{
    if [ "$(uname -s)" = 'Darwin' ]; then
        scriptDirectory=$(realpath "$(dirname "$(readlink "$(realpath $0)" || echo "$(echo "$(realpath $0)" | sed -e 's,\\,/,g')")")")
    else
        scriptDirectory=$(realpath "$(dirname "$(readlink -f "$(realpath $0)" || echo "$(echo "$(realpath $0)" | sed -e 's,\\,/,g')")")")
    fi

    findFilesCommand="bash ${scriptDirectory}/find_files_to_check.sh -s .dontMessDetectPHP -f .php"
    findParentWithFile="bash ${scriptDirectory}/find_parent_with_file.sh"
    rootDirectory=$($findParentWithFile -d $scriptDirectory -f composer.lock)
    messDetectorCommandPrefix="php ${rootDirectory}/vendor/bin/phpmd "
    messDetectorCommandSuffix=" text ${scriptDirectory}/../config/phpmd/ZooRoyalDefault/phpmd.xml --suffixes php"
}

function show_help()
{
    echo "This tool executes PHP-MD on a certain set of PHP files of this Project. It ignores files which are in "
    echo "directories with a .dontMessDetectPHP file. Subdirectories are ignored too."
    echo "usage: mess_detect_php [--help] [-t <git tree-ish>]"
    echo "    -h                   Shows this help"
    echo "    -t <git tree-ish>    Detects mess in PHP-Files which have changed since the current branch parted "
    echo "                         from the target branch only."
}

function detect_mess()
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
        echo "Detecting Mess in $directory"
        $messDetectorCommandPrefix $directory $messDetectorCommandSuffix
        messdetectionResult=$?
        if ! [ "$messdetectionResult" -eq "0" ]
        then
            result=$messdetectionResult
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
        echo "Detecting mess in diff to $OPTARG."
        detect_mess $OPTARG
        ;;
    esac
done

echo "Detecting mess in whole project."

detect_mess

