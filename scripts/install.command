#!/bin/bash

# Change for main directory
cd $(dirname $0)/..
clear

ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"
brew install node
npm install
npm install -g gulp-cli