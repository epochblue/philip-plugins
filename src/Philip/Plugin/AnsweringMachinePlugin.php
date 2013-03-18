<?php

namespace Philip\Plugin;

use Philip\AbstractPlugin as BasePlugin;
use Philip\IRC\Response;
use Philip\IRC\Event;

/**
 * Adds an "answering machine" to the Philip IRC bot.
 *
 * @author Bill Israel <bill.israel@gmail.com>
 */
class AnsweringMachinePlugin extends BasePlugin
{
    /**
     * This plugin is made of two parts: the command to save messages, and the listener to deliver messages.
     *
     * !msg <recipient> <message> - saves a message for the recipient
     * Example Usage:
     *      !msg irc-buddy Call me whenever you get this
     *
     * NOTE: if the command is sent via private message, the message will be delivered via private message as well.
     */
    public function init()
    {
        $bot = $this->bot;

        /** The messages to deliver publicly to users */
        $public = array();

        /** The messages to deliver privately to users */
        $private = array();

        // Saves messages
        $this->bot->onMessages('/^!msg\s(\S+)\s(.*)/', function(Event $event) use ($bot, &$private, &$public) {
            $matches = $event->getMatches();
            $recipient = $matches[0];
            $sender = $event->getRequest()->getSendingUser();
            $msg = $matches[1];

            if ($event->getRequest()->isPrivateMessage()) {
                $queue =& $private;
            } else {
                $queue =& $public;
            }

            if (!isset($queue[$recipient])) {
                $queue[$recipient] = array();
            }

            $queue[$recipient][$msg] = $sender;

            $event->addResponse(Response::msg($event->getRequest()->getSource(), "Message for $recipient saved successfully."));
        });


        // Delivers messages
        $this->bot->onJoin(function(Event $event) use ($bot, &$public, &$private) {
            $joiner = $event->getRequest()->getSendingUser();
            $channel = $event->getRequest()->getSource();

            // Retrieve public messages
            if (in_array($joiner, array_keys($public))) {
                foreach($public[$joiner] as $message => $sender) {

                    if ($joiner == $sender) {
                        $msg = "$joiner: you left yourself a message - $message";
                    } else {
                        $msg = "$joiner: $sender left you a message - $message";
                    }

                    $event->addResponse(Response::msg($channel, $msg));
                    unset($public[$joiner][$message]);
                }
            }

            // Retrieve private messages
            if (in_array($joiner, array_keys($private))) {
                foreach($private[$joiner] as $message => $sender) {
                    if ($joiner == $sender) {
                        $msg = "You left yourself a message - $message";
                    } else {
                        $msg = "$sender left you a message - $message";
                    }

                    $event->addResponse(Response::msg($joiner, $msg));
                    unset($private[$joiner][$message]);
                }
            }
        });
    }

    /**
     * Help messages!
     */
    public function displayHelp(Event $event)
    {
        return "!msg <who> <msg> - Save a message for the specified user (send as a PM for the message to be delivered privately)";
    }

    /**
     * Return the name of the plugin.
     */
    public function getName()
    {
        return 'AnsweringMachinePlugin';
    }
}