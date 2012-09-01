<?php

use Philip\IRC\Response;

/**
 * Adds a "swear jar" to the Philip IRC bot.
 *
 * @author Bill Israel <bill.israel@gmail.com>
 */
class SwearJarPlugin
{
    /** @var The injected instance of the bot */ 
    private $bot;

    /**
     * Constructor
     *
     * @param Philip The IRC bot instance
     */
    public function __construct($bot)
    {
        $this->bot = $bot;
    }

    /**
     * Listens to channel messages and lets everyone know who owes what to
     * the "swear jar".
     */
    public function init()
    {
        $swears = array('fu+ck', 'sh+it', 'cunt', 'cock');
        $swear_jar = array();

        // Don't say bad words, kids.
        $re = '/' . implode('|', $swears) . '/';
        $this->bot->onChannel($re, function($request, $matches) use (&$swear_jar) {
            $cost = 0.25;
            $who = $request->getSendingUser();
            if (!isset($swear_jar[$who])) {
                $swear_jar[$who] = 0;
            }

            $price = ($swear_jar[$who] += $cost);
            return Response::msg(
                $request->getSource(),
                sprintf("Mind your tongue $who! Now you owe \$%.2f to the swear jar.", $price)
            );
        });
    }
}

