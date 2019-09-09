#!/bin/bash

# Add a new remote "upstream"
# Only for contributors
# Update your forks with current main repository commits

git remote add upstream https://github.com/jonatanflores/database.git

# Get all the branches of this new remote,
# like upstream/master for example:

git fetch upstream

# Make sure you are in the master branch:

git checkout master

# Rewrite your master branch so that your commits
# that are not in the original project appear, and that your
# commits top the list:

git rebase upstream/master

# If you don't want to rewrite your master branch history
# (maybe because someone has already cloned it) so you should
# replace the last command with `a`

git merge upstream/master

# However, to make future pull requests look as good as possible
# Clean as possible, it is a good idea to do the rebase.

# If you rebased your branch from upstream/master, maybe
# you need to push your own Github repository.
# You can do this with:

git push -f origin master

# You'll only need to do this with -f the first time you
# make a rebase.