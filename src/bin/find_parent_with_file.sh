# A POSIX variable
OPTIND=1         # Reset in case getopts has been used previously in the shell.

##### Global Variables #####

directory=$PWD
file=""

##### Functions #####

function show_help()
{
    echo "This tool finds the closest parent directory containing a file identified by name."
    echo "usage: find_parent_with_file[ -h][ -d <directory>] -f <file-name>"
    echo "    -h                Shows this help"
    echo "    -f <file-name>    The name of the file to find."
    echo "    -d <directory>    The directory to start the search. If not set it will use the PWD."
}

function find_parent_with_file()
{
    if [ -z $file  ]
    then
        echo "No valid file provided (-f). Use -h flag for help."
        exit 1
    fi

    while [[ $directory != "." && $directory != "" && $directory != "/" ]];
    do
        result=$(find "$directory" -maxdepth 1 -mindepth 1 -iname "$file" | egrep '.*')
        if [ "$?" -eq "0" ]
        then
            echo $directory
            break
        fi
        directory=$(dirname "$directory")
    done
}

###############

while getopts "hf:d:" opt; do
    case "$opt" in
    h)
        show_help
        exit 0
        ;;
    d)
        case $OPTARG in
            /*) directory=$OPTARG;;
            *) directory=$(realpath $PWD/$OPTARG);;
        esac
        ;;
    f)
        file=$OPTARG
        ;;
    \?)
        echo "Invalid option: -$OPTARG. Use -h flag for help."
        exit 1
        ;;
    esac
done

find_parent_with_file
