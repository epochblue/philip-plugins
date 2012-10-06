<?php

use Philip\IRC\Response;

/**
 * Adds basic administrative functionality to the Philip IRC bot.
 *
 * @author Bill Israel <bill.israel@gmail.com>
 */
class AdminPlugin
{
    /** @var Philip $bot The bot to add functionality to */
    protected $bot;

    /**
     * Constructur inject the bot.
     *
     * @param Philip $bot The Philip IRC bot instance
     */
    public function __construct($bot)
    {
        $this->bot = $bot;
    }

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
        $this->bot->onPrivateMessage("/^!join(.*)/", function($request, $matches) use ($config, $bot) {
            $user = $request->getSendingUser();
            $rooms = explode(' ', $matches[0]);

            if ($bot->isAdmin($user)) {
                return Response::join(implode(',', $rooms));
            } else {
                return Response::msg($user, "You're not the boss of me.");
            }
        });
        
        // Allow the bot to leave rooms
        $this->bot->onPrivateMessage("/^!leave(.*)/", function($request, $matches) use ($config, $bot) {
            $user = $request->getSendingUser();
            $rooms = explode(' ', $matches[0]);

            if ($bot->isAdmin($user)) {
                return Response::leave(implode(',', $rooms));
            } else {
                return Response::msg($user, "You're not the boss of me.");
            }
        });
 
        // Echo things into channels
        $this->bot->onPrivateMessage("/^!say ([#&][^\x07\x2C\s]{0,200}) (.+)/", function($request, $matches) use ($config, $bot) {
            $user = $request->getSendingUser();

            if ($bot->isAdmin($user)) {
                return Response::msg($matches[0], $matches[1]);
            } else {
                return Response::msg($user, "You're not the boss of me.");
            }
        });



        // Quit gracefully
        $this->bot->onPrivateMessage("/^!quit(.*)/", function($request, $matches) use ($config, $bot) {
            $user = $request->getSendingUser();
            $msg = $matches[0] ? trim($matches[0]) : 'Later, kids.';

            if ($bot->isAdmin($user)) {
                return Response::quit($msg);
            } else {
                return Response::msg($user, "You're not the boss of me.");
            }
        });
    }
}
