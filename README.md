Philip Plugins
==============

A few simple plugins for [the Philip IRC bot](http://github.com/epochblue/philip).


Admin
-----

Adds basic administrative functionality to the bot in the form of `!quit`, `!join`, and `!leave` commands.

1. `!quit <quit message>`: Tells the bot to quit the IRC server.

        Example usage:
            !quit ...aaand boom goes the dynamite.
 
2. `!join <channels>`: Tells the bot to join the given channel(s).

        Example usage:
            !join #example-room
            !join #example-room1 #example-room2
 
3. `!leave <channels>`: Tells the bot to leave the given channel(s).

        Example usage:
            !leave #example-room
            !leave #example-room1 #example-room2

4. `!say <channel> <msg>`: Tells the bot to send a message to the given channel.

        Example usage:
            !say #example-room Look I can talk.

These commands only work via private message and only if the issuer
is in the ops array in the bot's configuration.

_The `!say` command was contributed by [Micah Breedlove](http://github.com/druid628)_


SwearJar
--------

Adds a "swear jar" that listens to the conversation and keeps track of how many times
someone has used a "bad word" and how much money they owe as a result.


ImageMe
-------

Adds the ability to request a random image from Google Images based on a keyword.
There's also a version of the command specifically for GIFs.

1. `!img <keyword>`: Gets a random image that matches the keyword.

        Example usage:
            !img wizard

2. `!image <keyword>`: Same as above

        Example usage:
            !image wizard

3. `!gif <keyword>`: Looks specifically for a GIF.

        Example usage:
            !gif wizard

These use the Google Images API and have SafeSearch turned on by default, so it should return
only SFW images, but your mileage may vary.


CannedResponse
--------------

The bot will sit in a channel and send back canned responses when spoken to.

_This plugin was contributed by [Sean Crystal](http://github.com/spiralout)_


### AUTHORS/CONTRIBUTORS

* Bill Israel - [http://github.com/epochblue](http://github.com/epochblue) - [@epochblue](http://twitter.com/epochblue)
* Sean Crystal - [http://github.com/spiralout](http://github.com/spiralout)
* Micah Breedlove - [http://github.com/druid628](http://github.com/druid628) - [@druid628](http://twitter.com/druid628)
