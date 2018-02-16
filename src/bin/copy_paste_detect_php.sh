#!/bin/bash

# A POSIX variable
OPTIND=1         # Reset in case getopts has been used previously in the shell.

##### Variables #####

scriptDirectory=""
findFilesCommand=""
cpdCommand=""

##### Functions #####

function constructor()
{
    # readlink has no -f option with Darwin kernels used in Macs
    if [ "$(uname -s)" = 'Darwin' ]; then
        scriptDirectory=$(realpath "$(dirname "$(readlink "$(realpath $0)" || echo "$(echo "$(realpath $0)" | sed -e 's,\\,/,g')")")")
    else
        scriptDirectory=$(realpath "$(dirname "$(readlink -f "$(realpath $0)" || echo "$(echo "$(realpath $0)" | sed -e 's,\\,/,g')")")")
    fi

    findFilesCommand="bash ${scriptDirectory}/find_files_to_check.sh -s .dontCopyPasteDetectPHP -e"
    findParentWithFile="bash ${scriptDirectory}/find_parent_with_file.sh"
    rootDirectory=$($findParentWithFile -d $scriptDirectory -f composer.lock)
    cpdCommand="php ${rootDirectory}/vendor/bin/phpcpd -vvv --progress --fuzzy -n --names-exclude=ZRBannerSlider.php,Installer.php,ZRPreventShipping.php"
}

function show_help()
{
    echo "This tool executes PHP-CPD on a certain set of PHP files of this Project. It ignores files which are in "
    echo "directories with a .dontCopyPasteDetectPHP file. Subdirectories are ignored too."
    echo "usage: $(basename $0)[ -h]"
    echo "    -h                   Shows this help"
}

function copyPasteDetect()
{
    local excludedDirectories=()
    local rawExcludedDirectories

    rawExcludedDirectories=$($findFilesCommand)
    read -a excludedDirectories <<<$rawExcludedDirectories

    if ! [ ${#excludedDirectories[@]} -eq 0 ]
    then
        excludedDirectoriesParameter=`printf -- "--exclude=%s " "${excludedDirectories[@]}"`
    fi

    $cpdCommand $excludedDirectoriesParameter .
}

###############

constructor

while getopts "h" opt; do
    case "$opt" in
    h)
        show_help
        exit 0
        ;;
    esac
done

copyPasteDetect
