#!/bin/bash

# A POSIX variable
OPTIND=1         # Reset in case getopts has been used previously in the shell.

##### Configuration #####

blacklistedDirectories=(
".git"
".idea"
".vagrant"
"vendor"
)

##### Variables #####

scriptDirectory=""
findParentWithFile=""
rootDirectory=""
targetBranch=""
stopword=""
filter=""

##### Functions #####

function constructor()
{
    # readlink has no -f option with Darwin kernels used in Macs
    if [ "$(uname -s)" = 'Darwin' ]; then
        scriptDirectory=$(realpath "$(dirname "$(readlink "$(realpath $0)" || echo "$(echo "$(realpath $0)" | sed -e 's,\\,/,g')")")")
    else
        scriptDirectory=$(realpath "$(dirname "$(readlink -f "$(realpath $0)" || echo "$(echo "$(realpath $0)" | sed -e 's,\\,/,g')")")")
    fi

    findParentWithFile="bash ${scriptDirectory}/find_parent_with_file.sh"
    rootDirectory=$($findParentWithFile -d $scriptDirectory -f composer.lock)
    }

function show_help()
{
    echo "This tool finds files, which should be considered for code style checks."
    echo "usage: sniff_php[ -h][ -t <git tree-ish>][ -s <name-of-file>][ -f <filter>][ -e]"
    echo "    -h                   Shows this help"
    echo "    -t <git tree-ish>    Finds PHP-Files which have changed since the current branch parted"
    echo "                         from the target branch only."
    echo "    -s <name-of-file>    Name of the file which triggers the exclusion of the path"
    echo "    -f <filter>          Filters the Filename. For example .php for PHP-Files"
    echo "    -e                   Gathers list of directories which should be excluded"
}


function find_diff_only()
{
    local branchRef="$(git symbolic-ref HEAD 2>/dev/null)"
    local localBranch=${branchRef##refs/heads/}
    local refinedDiff=()

    if [ "$localBranch" = "$targetBranch" ];
    then
        rawDiff=$(git diff --name-only --diff-filter=d HEAD~1 | grep -i $filter$)
    else
        mergeBase=$(git merge-base HEAD origin/$targetBranch)
        rawDiff=$(git diff --name-only --diff-filter=d $mergeBase | grep -i $filter$)
    fi


    for line in $rawDiff
    do
        hit=false

        # Exclude global Blacklist
        for filter in "${blacklistedDirectories[@]}"; do
            if [[ $line = $filter* ]]
            then
                hit=true
                break
            fi
        done

        # Exclude by $stopword
        if [[ "$hit" == false && ! "$stopword" == "" ]]
        then
            path=$(dirname "./$line")

            if [ -n "$($findParentWithFile -d $path -f $stopword)" ]
            then
                hit=true
            fi
        fi

        # Exclude Submodules
        if [[ "$hit" == false ]]
        then
            path=$(dirname "./$line")

            if ! [ "$($findParentWithFile -d $path -f .git)" == "$rootDirectory" ]
            then
                hit=true
            fi
        fi

        if [[ "$hit" = false ]]
        then
            refinedDiff+=($line)
        fi
    done

    echo "${refinedDiff[@]}"

}

function find_all()
{
    local blackListParameter=""

    compute_blacklisted_directories

    if ! [ ${#blacklistedDirectories[@]} -eq 0 ]
    then
        blackListParameter=`printf -- "-not -path './%s*' -a " "${blacklistedDirectories[@]}"`
        blackListParameter=${blackListParameter%???}
    fi

    command="find . -type f -name '*$filter' $blackListParameter -print0 | xargs -0 echo"
    eval $command
}

function compute_blacklisted_directories()
{
    local rawExcludePathsByFileByStopword
    local rawExcludePathsByFileByGit
    local rawExcludePathsUntrimmed
    local rawExcludePaths

    # Gather paths excluded by file $stopword
    if ! [ "$stopword" == "" ]
    then
        rawExcludePathsByFileByStopword=$(find . -name $stopword)
    fi
    rawExcludePathsByFileByGit=$(find . -mindepth 2 -name .git)
    rawExcludePathsUntrimmed=$(printf "%s\n%s" "$rawExcludePathsByFileByStopword" "$rawExcludePathsByFileByGit")
    # Trim
    rawExcludePaths=${rawExcludePathsUntrimmed/#$'\n'/}
    rawExcludePaths=${rawExcludePaths%$'\n'}

    if ! [ "$rawExcludePaths" == "" ]
    then
        # Add them to black list
        while read -r line; do blacklistedDirectories+=("$(dirname "${line#./}")"); done <<<"$rawExcludePaths"
    fi
}

###############

constructor

while getopts "ht:s:f:e" opt; do
    case "$opt" in
    h)
        show_help
        exit 0
        ;;
    t)
        targetBranch=$OPTARG
        ;;
    s)
        stopword=$OPTARG
        ;;
    f)
        filter=$OPTARG
        ;;
    e)
        exclusionSwitch=true
        ;;
    \?)
        echo "Invalid option: -$OPTARG. Use -h flag for help."
        exit
        ;;
    esac
done


if [ "$exclusionSwitch" == "true" ]
then
    compute_blacklisted_directories
    echo ${blacklistedDirectories[*]}
elif [ -z $targetBranch ]
then
    find_all
else
    find_diff_only
fi

