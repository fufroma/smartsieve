#!/bin/bash

# Just here to be ready when I want to enable it :)
exit 0

if [ -e "php-cs-fixer.phar" ]
then
    PHPCSFIXER="php php-cs-fixer.phar"
elif hash php-cs-fixer
then
    PHPCSFIXER="php-cs-fixer"
else
    echo -e "\e[1;31mPlease install or download php-cs-fixer\e[00m";
    echo -e "\e[1;31mhttp://cs.sensiolabs.org/\e[00m";
    exit 1
fi

PHPCSFIXERARGS="fix -v --fixers="
# Mandatory fix
FIXERS1="indentation,linefeed,trailing_spaces,short_tag,braces,controls_spaces,eof_ending,visibility,align_equals,concat_with_spaces,elseif,line_after_namespace,lowercase_constants,lowercase_keywords"
# Optionnal fix & false positive
#FIXERS2="visibility"

EXIT=0

echo -e "\e[1;34mChecking mandatory formatting/coding standards\e[00m"
$PHPCSFIXER $PHPCSFIXERARGS$FIXERS1 --dry-run .
rc=$?
if [[ $rc == 0 ]]
then
    echo -e "\e[1;32mFormatting is OK\e[00m"
else
    echo -e "\e[1;31mPlease check code Formatting\e[00m"
    echo -e "\e[1;31m$PHPCSFIXER $PHPCSFIXERARGS$FIXERS1 .\e[00m"
    EXIT=1
fi

exit $EXIT
