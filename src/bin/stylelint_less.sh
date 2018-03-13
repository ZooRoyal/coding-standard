#!/bin/bash

# A POSIX variable
OPTIND=1         # Reset in case getopts has been used previously in the shell.

##### Variables #####

scriptDirectory=""
sniffCommand=""
stylelintFixCommand=""
fixMode=false
findViolationsResult=""
targetBranch=""
changedLessFiles=()

##### Functions #####

function constructor()
{
    local stylelintConfig
    local stylelintIgnore

    # readlink has no -f option with Darwin kernels used in Macs
    if [ "$(uname -s)" = 'Darwin' ]; then
        scriptDirectory=$(realpath "$(dirname "$(readlink "$(realpath $0)" || echo "$(echo "$(realpath $0)" | sed -e 's,\\,/,g')")")")
    else
        scriptDirectory=$(realpath "$(dirname "$(readlink -f "$(realpath $0)" || echo "$(echo "$(realpath $0)" | sed -e 's,\\,/,g')")")")
    fi

    stylelintConfig="${scriptDirectory}/../config/stylelint/bin/stylelint.js"
    stylelintIgnore="${scriptDirectory}/../config/stylelint/.stylelintrc"

    findFilesCommand="bash ${scriptDirectory}/find_files_to_check.sh -s .dontSniffLESS -f .less"
    findParentWithFile="bash ${scriptDirectory}/find_parent_with_file.sh"
    rootDirectory=$($findParentWithFile -d $scriptDirectory -f composer.lock)
    sniffCommand="${rootDirectory}/node_modules/stylelint/bin/stylelint.js --config=$stylelintConfig"
    stylelintFixCommand="${rootDirectory}/node_modules/stylelint/bin/stylelint.js --config=$stylelintConfig --fix --ignore-path=$stylelintIgnore"
}

function show_help()
{
    echo "This tool executes STYLELINT on a certain set of Less files of this Project."
    echo "usage: stylelint_less [--help] [-t <git tree-ish>]"
    echo "    -h                   Shows this help"
    echo "    -t <git tree-ish>    Lints Less-Files which have changed since the current branch parted from the target path only."
    echo "    -f                   Fix all changed Less Files"
}

function sniff()
{
    if [ ! -z "$targetBranch" ];
    then
        parameter=" -t $targetBranch"
    fi

    changedLessFiles=$($findFilesCommand$parameter)

    if [ -z "$changedLessFiles" ];
    then
        echo "No Less-Files to check!"
        exit 0
    fi

    echo "########### STYLELINT ###########"
    find_violations

    if [ "$findViolationsResult" -ne "0" ] && [ "$fixMode" == "true" ]
    then
        echo "########### STYLELINT FIX ###########"
        echo "Trying to fix violations"
        fix_violations

        echo "########### STYLELINT FIX ###########"
        echo "Revalidating code after attempt to fix it"
        find_violations

        if [ "$findViolationsResult" -ne "0" ]
        then
            printf "\n\e[31mCouldn't fix all violations :( \e[0m\n"
        else
            printf "\n\e[32mFixed all Violations :D \e[0m\n"
        fi
    fi

    exit $findViolationsResult
}

function find_violations()
{
    findViolationsResult=""
    local result=0

    for directory in $changedLessFiles; do
        echo "StyleLinting $directory"
        $sniffCommand $directory
        sniffResult=$?
        if ! [ "$sniffResult" -eq "0" ]
        then
            result=$sniffResult
        fi
    done

    findViolationsResult="$result"
}

function fix_violations()
{
    local stylelintCommand

    if [ fixMode == 'true' ]
    then
        stylelintCommand=$stylelintFixCommand
    fi

    for directory in $changedLessFiles; do
        echo "Fixing $directory"
        $stylelintCommand $directory
        sniffResult=$?
    done
}

###############

constructor

while getopts "hft:" opt; do
    case "$opt" in
    h)
        show_help
        exit 0
        ;;
    f)
        echo "Will run in fix mode"
        fixMode=true
        ;;
    t)
        echo "Sniffing in diff to $OPTARG."
        targetBranch=$OPTARG
        ;;
    \?)
        echo "Invalid option: -$OPTARG. Use -h flag for help."
        exit 1
        ;;
    esac
done

sniff
