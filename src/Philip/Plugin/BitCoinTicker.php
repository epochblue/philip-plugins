<?php

namespace Philip\Plugin;

use Philip\AbstractPlugin as BasePlugin;
use Philip\IRC\Response;
use Philip\IRC\Event;

/**
 * Adds a bitcoin tracker powered by the mtgox api.
 *
 * @author Marcus Fulbright <fulbright.marcus@gmail.com>
 */
class BitCoinTicker extends BasePlugin
{
    public function init()
    {
        $bot = $this->bot;

        $bot->onChannel('/(^!btc)/', function (Event $event) {
            $price = json_decode(
                file_get_contents("http://data.mtgox.com/api/1/BTCUSD/ticker"),
            true
            );

            $event->addResponse(
                Response::msg(
                    $event->getRequest()->getSource(),
                    "MtGox Buy: {$price['return']['buy']['display_short']}; Sell: {$price['return']['sell']['display_short']}; High: {$price['return']['high']['display_short']}; Low: {$price['return']['low']['display_short']}"
                )
            );
        });
    }    
}

    /**
     * Return the name of the plugin.
     */
    public function getName()
    {
        return 'BitCoinTicker';
    }
}