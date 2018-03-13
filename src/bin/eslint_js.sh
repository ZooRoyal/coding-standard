#!/bin/bash

# A POSIX variable
OPTIND=1         # Reset in case getopts has been used previously in the shell.

##### Variables #####

scriptDirectory=""
sniffCommand=""
eslintFixCommand=""
fixMode=false
fixDryMode=false
findViolationsResult=""
targetBranch=""
changedJsFiles=()

##### Functions #####

function constructor()
{
    local eslintConfig
    local eslintIgnore

    # readlink has no -f option with Darwin kernels used in Macs
    if [ "$(uname -s)" = 'Darwin' ]; then
        scriptDirectory=$(realpath "$(dirname "$(readlink "$(realpath $0)" || echo "$(echo "$(realpath $0)" | sed -e 's,\\,/,g')")")")
    else
        scriptDirectory=$(realpath "$(dirname "$(readlink -f "$(realpath $0)" || echo "$(echo "$(realpath $0)" | sed -e 's,\\,/,g')")")")
    fi

    eslintConfig="${scriptDirectory}/../config/eslint/.eslintrc.js"
    eslintIgnore="${scriptDirectory}/../config/eslint/.eslintignore"

    findFilesCommand="bash ${scriptDirectory}/find_files_to_check.sh -s .dontSniffJS -f .js"
    findParentWithFile="bash ${scriptDirectory}/find_parent_with_file.sh"
    rootDirectory=$($findParentWithFile -d $scriptDirectory -f composer.lock)
    sniffCommand="${scriptDirectory}/../../node_modules/eslint/bin/eslint.js --config=$eslintConfig"
    eslintFixCommand="${scriptDirectory}/../../node_modules/eslint/bin/eslint.js --config=$eslintConfig --fix --ignore-path=$eslintIgnore"
    eslintFixDryCommand="${scriptDirectory}/../../node_modules/eslint/bin/eslint.js --config=$eslintConfig --fix-dry-run --ignore-path=$eslintIgnore"
}

function show_help()
{
    echo "This tool executes ESLINT on a certain set of JS files of this Project."
    echo "usage: eslint_js [--help] [-t <git tree-ish>] [--fix <files>]"
    echo "    -h                   Shows this help"
    echo "    -t <git tree-ish>    Lints ESLINT-Files which have changed since the current branch parted from the target path only."
    echo "    -f                   Fix all changed JS Files"
    echo "    -d                   Fix all changed JS Files (Dry Run: just show violations)"
}

function sniff()
{
    if [ ! -z "$targetBranch" ];
    then
        parameter=" -t $targetBranch"
    fi

    changedJsFiles=$($findFilesCommand$parameter)

    if [ -z "$changedJsFiles" ];
    then
        echo "No JS-Files to check!"
        exit 0
    fi

    echo "########### ESLINT ###########"
    find_violations

    if [ "$findViolationsResult" -ne "0" ] && [ "$fixMode" == "true" ]
    then
        echo "########### ESLINT FIX ###########"
        echo "Trying to fix violations"
        fix_violations

        echo "########### EXLINT FIX ###########"
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

    for directory in $changedJsFiles; do
        echo "ESLinting $directory"
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
    local eslintCommand

    if [ "$fixDryMode" == "true" ]
    then
        eslintCommand=$eslintFixDryCommand
    else
        eslintCommand=$eslintFixCommand
    fi

    for directory in $changedJsFiles; do
        echo "Fixing $directory"
        $eslintCommand $directory
        sniffResult=$?
    done
}

###############

constructor

while getopts "hfdt:" opt; do
    case "$opt" in
    h)
        show_help
        exit 0
        ;;
    f)
        echo "Will run in fix mode"
        fixMode=true
        ;;
    d)
        echo "Will run in fix dry mode"
        fixMode=true
        fixDryMode=true
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
