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


DarkSky
-------

_This plugin requires a DarkSky API key be added to your bot's configuration
Add it like this:

```php
    $config = array(
        // ...
        'DarkSkyPlugin' => array(
            'api_key' => '<your API key here>'
        )
```

Adds the ability to get up-to-the-minute weather information and forecasts from the
[DarkSky API](https://developer.darkskyapp.com). This plugin contains only a single
bot command, but has multiple subcommands:

1. `!ds [current|now] <location>`: Gets weather info for the given location.

        Example usage:
            !ds now 37205
            !ds now Nashville, TN
            !ds current 600 Charlotte Ave, Nashville, TN 37219

2. `!ds [forecast|at] <location> @ <time>`: Get the forecast for the location at a
specific time.

        Example usage:
            !ds forecast 37214 @ +10 minutes
            !ds at 37214 @ -5 minutes

`Note: <time> must be between -8 hours and +1 hour from the current moment.`


AnsweringMachine
----------------

Adds an "answering machine" to a bot, allowing users to leave messages for people who aren't currently in the room.
The messages will be delivered when the recipient re-joins the room.

1. `!msg <recipient> <message>`: Saves a message for the intended recipient.

        Example usage:
            !msg irc-buddy Call me when you get this.

By default, messages are relayed in public; however, messages delivered privately to the bot, will be relayed
privately to the recipient when the user re-joins.


### AUTHORS/CONTRIBUTORS

* Bill Israel - [http://github.com/epochblue](http://github.com/epochblue) - [@epochblue](http://twitter.com/Epochblue)
* Sean Crystal - [http://github.com/spiralout](http://github.com/spiralout)
* Micah Breedlove - [http://github.com/druid628](http://github.com/druid628) - [@druid628](http://twitter.com/druid628)
