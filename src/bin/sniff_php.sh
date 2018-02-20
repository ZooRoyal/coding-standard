#!/bin/bash

# A POSIX variable
OPTIND=1         # Reset in case getopts has been used previously in the shell.

##### Variables #####

scriptDirectory=""
sniffCommand=""
cbfCommand=""
fixMode=false
findViolationsResult=""
targetBranch=""
changedPhpFiles=()

##### Functions #####

function constructor()
{
    local phpcsConfig

    # readlink has no -f option with Darwin kernels used in Macs
    if [ "$(uname -s)" = 'Darwin' ]; then
        scriptDirectory=$(realpath "$(dirname "$(readlink "$(realpath $0)" || echo "$(echo "$(realpath $0)" | sed -e 's,\\,/,g')")")")
    else
        scriptDirectory=$(realpath "$(dirname "$(readlink -f "$(realpath $0)" || echo "$(echo "$(realpath $0)" | sed -e 's,\\,/,g')")")")
    fi

    phpcsConfig="${scriptDirectory}/../config/phpcs/ZooroyalDefault/ruleset.xml"

    findFilesCommand="bash ${scriptDirectory}/find_files_to_check.sh -s .dontSniffPHP -f .php"
    findParentWithFile="bash ${scriptDirectory}/find_parent_with_file.sh"
    rootDirectory=$($findParentWithFile -d $scriptDirectory -f composer.lock)
    sniffCommand="php ${rootDirectory}/vendor/bin/phpcs -s --extensions=php --standard=$phpcsConfig"
    cbfCommand="php ${rootDirectory}/vendor/bin/phpcbf --extensions=php --standard=$phpcsConfig"
}

function show_help()
{
    echo "This tool executes PHP-CS on a certain set of PHP files of this Project. It ignores files which are in "
    echo "directories with a .dontSniffPHP file. Subdirectories are ignored too."
    echo "usage: $(basename $0)[ -h][ -t <git tree-ish>]"
    echo "    -h                   Shows this help"
    echo "    -t <git tree-ish>    Sniffs PHP-Files which have changed since the current branch parted from the target path only."
    echo "    -f                   Fix mode. Will Call phpcbf to autofix some violations."
}

function sniff()
{
    if [ ! -z "$targetBranch" ];
    then
        parameter=" -t $targetBranch"
    fi

    changedPhpFiles=$($findFilesCommand$parameter)

    if [ -z "$changedPhpFiles" ];
    then
        echo "No PHP-Files to check!"
        exit 0
    fi

    echo "########### PHPCS ###########"
    find_violations

    if [ "$findViolationsResult" -ne "0" ] && [ "$fixMode" == "true" ]
    then
        echo "########### PHPCBF ###########"
        echo "Trying to fix violations"
        fix_violations

        echo "########### PHPCS ###########"
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

    for directory in $changedPhpFiles; do
        echo "Sniffing $directory"
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
    for directory in $changedPhpFiles; do
        echo "Fixing $directory"
        $cbfCommand $directory
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
