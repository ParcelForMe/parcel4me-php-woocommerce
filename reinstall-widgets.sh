#!/bin/bash

cd parcel4me-woo

echo
echo "About to remove the current version of the P4M widgets ..."
echo "Press any key to proceed"
read -n1 -r

rm -rf ./lib

echo
echo "Installing the P4M widgets from https://github.com/ParcelForMe/p4m-widgets/ into ./lib/"
echo "Press any key to proceed"
read -n1 -r 

mkdir ./lib
cd lib
git clone https://github.com/ParcelForMe/p4m-widgets/
cd p4m-widgets

echo " Use api_refactor branch, until such a time as that is merged back into master !!!"
git checkout api_refactor

bower install 
mv bower_components/* ..
rmdir bower_components
rm -rf .git*

