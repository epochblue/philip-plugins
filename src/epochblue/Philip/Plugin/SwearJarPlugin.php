<?php

namespace Epochblue\Philip\Plugin;

use Epochblue\Philip\AbstractPlugin as BasePlugin;
use Epochblue\Philip\IRC\Response;

/**
 * Adds a "swear jar" to the Philip IRC bot.
 *
 * @author Bill Israel <bill.israel@gmail.com>
 */
class SwearJarPlugin extends BasePlugin
{
    /**
     * Listens to channel messages and lets everyone know who owes what to
     * the "swear jar".
     */
    public function init()
    {
        $swears = array('fu+ck', 'sh+i+t', 'cunt', '\bco+ck\b');
        $swear_jar = array();

        // Don't say bad words, kids.
        $re = '/' . implode('|', $swears) . '/i';
        $this->bot->onChannel($re, function($event) use (&$swear_jar) {
            $cost = 0.25;
            $who = $event->getRequest()->getSendingUser();
            if (!isset($swear_jar[$who])) {
                $swear_jar[$who] = 0;
            }

            $price = ($swear_jar[$who] += $cost);
            $event->addResponse(
                Response::msg(
                    $event->getRequest()->getSource(),
                    sprintf("Mind your tongue $who! Now you owe \$%.2f to the swear jar.", $price)
            ));
        });
    }
}

