#!/bin/bash

echo -e "\e[1;34mChecking syntax errors\e[00m"
EXIT=0
while IFS= read -r file; do
    if ! php --syntax-check "$file" > /dev/null 2>&1; then
        php --syntax-check "$file"
        EXIT=1
    fi
done < <(find . -path ./vendor -prune -o \( -name '*.php' -o -name '*.lib' \) -print)
if [[ $EXIT -ne 0 ]]; then
    echo -e '\e[00;31mPlease check files syntax\e[00m'
else
    echo -e "\e[1;32mSyntax is OK\e[00m"
fi
exit $EXIT
