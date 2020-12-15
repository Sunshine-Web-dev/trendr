# TRS Mega Populate

This plugin creates huge amounts of TRS data, for use with testing scaling issues. Note that the data it produces is *dummy* data - while the plugin attempts to reference actual objects (users, groups, etc) when creating content, it does not attempt to keep the data logical. So you might end up with a bunch of friendship_created activity items for people who are not and never have been friends, for example.

This plugin currently supports:

- Members
- Activity

## Usage

- Activate the plugin
- Dashboard > Tools > TRS Mega Populate

## Warnings

This will create huge amounts of data. Only use on development environments!!!

Note:
To populate activities, this plugin requires that Buddypress's Discussion Forums is enabled and setup. Otherwise you may get a BB_Query not found error.