#!/bin/bash

# A POSIX variable
OPTIND=1         # Reset in case getopts has been used previously in the shell.

##### Variables #####
scriptDirectory=$(dirname "$0")
findFilesCommand="bash ${scriptDirectory}/find_stylesheets_to_check.sh"
stylelintCommand="${scriptDirectory}/../../node_modules/stylelint/bin/stylelint.js --config ./.stylelintrc"
stylelintFixMode=false

##### Functions #####

function show_help()
{
    echo "This tool executes STYLELINT on a certain set of Less files of this Project."
    echo "usage: stylelint_less [--help] [-t <git tree-ish>]"
    echo "    -h                   Shows this help"
    echo "    -t <git tree-ish>    Lints Less-Files which have changed since the current branch parted from the target path only."
    echo "    -f                   Fix all changed Less Files"
}

function stylelint()
{
    local parameter=""
    local branchname=$1
    local changedStyleFiles=()
    local fixMode=""

    if [ ! -z "$branchname" ];
    then
        parameter=" -t $branchname"
    fi

    changedStyleFiles=$($findFilesCommand$parameter)

    if [ -z "$changedStyleFiles" ];
    then
        echo "No Less-Files to check!"
        exit 0
    fi

    for directory in $changedStyleFiles; do
        echo "stylelinting $directory"
        if [[ $stylelintFixMode == 'true' ]]
        then
           fixMode=" --fix"
        fi
        $stylelintCommand$fixMode $directory
        stylelintResult=$?

        if ! [ "$stylelintResult" -eq "0" ]
        then
            result=$stylelintResult
        fi
    done

    exit $result
}

###############

while getopts "ht:f" opt; do
    case "$opt" in
    h)
        show_help
        exit 0
        ;;
    t)
        echo "stylelinting in diff to $OPTARG."
        stylelint $OPTARG
        ;;
    f)
        echo "stylelint fixes your files"
        stylelintFixMode=true
        ;;
    esac
done

stylelint
