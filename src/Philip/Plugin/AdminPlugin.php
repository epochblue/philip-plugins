<?php

namespace Philip\Plugin;

use Philip\AbstractPlugin as BasePlugin;
use Philip\IRC\Response;
use Philip\IRC\Event;

/**
 * Adds basic administrative functionality to the Philip IRC bot.
 *
 * @author Bill Israel <bill.israel@gmail.com>
 */
class AdminPlugin extends BasePlugin
{
    /**
     * Adds !quit, !join, and !leave commands to the bot:
     *
     * !quit <quit message> -- Tells the bot to quit the IRC server
     * Example usage:
     *      !quit ...aaand boom goes the dynamite.
     *
     * !join <channels>
     * Example usage:
     *      !join #example-room
     *      !join #example-room1 #example-room2
     *
     * !leave <channels> -- Tells the bot to leave channels
     * Example usage:
     *      !leave #example-room
     *      !leave #example-room1 #example-room2
     *
     * !say <channel> <msg> -- Tells the bot to send a message to the given channel
     * Example usage:
     *      !say #example-room Look I can talk.
     *  
     * These commands only work via private message and only if the issuer
     * is in the ops array in the bot's configuration.
     */
    public function init()
    {
        $bot = $this->bot;
        $config = $bot->getConfig();

        // Allow the bot to join rooms
        $this->bot->onPrivateMessage("/^!join(.*)/", function(Event $event) use ($config, $bot) {
            $matches = $event->getMatches();
            $user = $event->getRequest()->getSendingUser();
            $rooms = explode(' ', $matches[0]);

            if ($bot->isAdmin($user)) {
                $event->addResponse(Response::join(implode(',', $rooms)));
            } else {
                $event->addResponse(Response::msg($user, "You're not the boss of me."));
            }
        });
        
        // Allow the bot to leave rooms
        $this->bot->onPrivateMessage("/^!leave(.*)/", function(Event $event) use ($config, $bot) {
            $matches = $event->getMatches();
            $user = $event->getRequest()->getSendingUser();
            $rooms = explode(' ', $matches[0]);

            if ($bot->isAdmin($user)) {
                $event->addResponse(Response::leave(implode(',', $rooms)));
            } else {
                $event->addResponse(Response::msg($user, "You're not the boss of me."));
            }
        });
 
        // Echo things into channels
        $this->bot->onPrivateMessage("/^!say ([#&][^\x07\x2C\s]{0,200}) (.+)/", function(Event $event) use ($config, $bot) {
            $matches = $event->getMatches();
            $user = $event->getRequest()->getSendingUser();

            if ($bot->isAdmin($user)) {
                $event->addResponse(Response::msg($matches[0], $matches[1]));
            } else {
                $event->addResponse(Response::msg($user, "You're not the boss of me."));
            }
        });



        // Quit gracefully
        $this->bot->onPrivateMessage("/^!quit(.*)/", function(Event $event) use ($config, $bot) {
            $matches = $event->getMatches();
            $user = $event->getRequest()->getSendingUser();
            $msg = $matches[0] ? trim($matches[0]) : 'Later, kids.';

            if ($bot->isAdmin($user)) {
                $event->addResponse(Response::quit($msg));
            } else {
                $event->addResponse(Response::msg($user, "You're not the boss of me."));
            }
        });
    }

    /**
     * Help messages!
     */
    public function displayHelp(Event $event)
    {
        return array(
            "!join <rooms> - join the specified rooms (admin req'd)",
            "!leave <rooms> - leave the specified rooms (admin req'd)",
            "!say <room> <msg> - say the message to the specified room (admin req'd)",
            "!quit <room> - quit the specified room (admin req'd)"
        );
    }

    /**
     * @see \Philip\AbstractPlugin#getName()
     */
    public function getName()
    {
        return 'AdminPlugin';
    }
}
