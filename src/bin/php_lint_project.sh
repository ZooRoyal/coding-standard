#!/bin/bash

# A POSIX variable
OPTIND=1         # Reset in case getopts has been used previously in the shell.

##### Variables #####

scriptDirectory=""
parallelLintCommand=""
targetBranch=""

##### Functions #####

function constructor()
{
    # readlink has no -f option with Darwin kernels used in Macs
    if [ "$(uname -s)" = 'Darwin' ]; then
        scriptDirectory=$(realpath "$(dirname "$(readlink "$(realpath $0)" || echo "$(echo "$(realpath $0)" | sed -e 's,\\,/,g')")")")
    else
        scriptDirectory=$(realpath "$(dirname "$(readlink -f "$(realpath $0)" || echo "$(echo "$(realpath $0)" | sed -e 's,\\,/,g')")")")
    fi

    findFilesCommand="bash ${scriptDirectory}/find_files_to_check.sh -s .dontLintPHP -f .php"
    findParentWithFile="bash ${scriptDirectory}/find_parent_with_file.sh"
    rootDirectory=$($findParentWithFile -d $scriptDirectory -f composer.lock)
    parallelLintCommand="php ${rootDirectory}/vendor/bin/parallel-lint -j 2"
}

function show_help()
{
    echo "This tool lints the PHP-Files of this Project. It ignores files which are in directories with a .dontLintPHP"
    echo "file. Subdirectories are ignored too."
    echo "usage: $(basename $0)[ -h][ -t <git tree-ish>]"
    echo "    -h                   Shows this help"
    echo "    -t <git tree-ish>    Lints PHP-Files which have changed since the current branch parted from the target Path only."
}

function lint_diff()
{
    local parameter=""
    local changedPhpFiles=()

    changedPhpFiles=$($findFilesCommand -t $targetBranch)

    if [ -z "$changedPhpFiles" ];
    then
        echo "No PHP-Files to check!"
        exit 0
    fi

    $parallelLintCommand $changedPhpFiles
    exit $?
}

function lint_all()
{
local parameter=""
    local excludedDirectories=()

    rawExcludedDirectories=$($findFilesCommand -e)
    read -a excludedDirectories <<<$rawExcludedDirectories

    if ! [ ${#excludedDirectories[@]} -eq 0 ]
    then
        excludedDirectoriesParameter=`printf -- "--exclude %s " "${excludedDirectories[@]}"`
    fi

    $parallelLintCommand $excludedDirectoriesParameter .
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
        targetBranch=$OPTARG
        ;;
    esac
done

if ! [ -z $targetBranch ]
then
    lint_diff
else
    lint_all
fi

