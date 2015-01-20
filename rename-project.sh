#!/bin/bash

if [ -z "$1" ]; then
    echo -e "Usage:\n$0 \"Project Name\""
    exit 1
else
    echo "Renaming to '$1' ... "
    sed -i~ "s/Project Name/$1/g" module/Application/view/layout/layout.phtml
    sed -i~ "s/Project Name/$1/g" module/Application/view/layout/login.phtml
    sed -i~ "s/Project Name/$1/g" module/Application/view/layout/footer.phtml
    echo "done."
fi
