<?php

use Philip\IRC\Response;

/**
 * Looks up a random image based on a keyword.
 *
 * @author Bill Israel <bill.israel@gmail.com>
 */
class ImageMePlugin
{
    /** @var \Philip\Philip An instance of the IRC bot */
    protected $bot;

    /**
     * Constructor.
     *
     * @param \Philip\Philip The injected instance of the IRC bot
     */
    public function __construct($bot)
    {
        $this->bot = $bot;
    }

    /**
     * Initialize the plugin.
     */
    public function init()
    {
        // Ugh...PHP 5.3, you're killin' me.
        $that = $this;

        // Get me an image!
        $this->bot->onChannel('/^!(?:img|image) (.+)$/i', function($event) use ($that) {
            $matches = $event->getMatches();
            if ($img = $that->getImage(trim($matches[0]), false)) {
                $event->addResponse(Response::msg($event->getRequest()->getSource(), $img));
            }
        });


        // Get me a gif!
        $this->bot->onChannel('/^!gif (.+)$/i', function($event) use ($that) {
            $matches = $event->getMatches();
            if ($img = $that->getImage(trim($matches[0]), true)) {
                $event->addResponse(Response::msg($event->getRequest()->getSource(), $img));
            }
        });
    }


    /**
     * Uses the (deprecated) Google Images API to locate a random image
     *
     * @param string  $term The term to search for
     * @param boolean $gif  True if you only want to search for gifs
     *
     * @return mixed The URL of a found image, null otherwise
     */
    public function getImage($term, $gif = false) {
        $term = urlencode($term);
        $url = "http://ajax.googleapis.com/ajax/services/search/images?q={$term}&v=1.0&rsz=8&safe=active";

        if ($gif) {
            $url .= "&as_filetype=gif";
        }

        $json = trim(file_get_contents($url));
        $decoded = json_decode($json, true);
        $results = isset($decoded['responseData']['results']) ? $decoded['responseData']['results'] : array();

        if (!empty($results)) {
            $result = $results[array_rand($results)];
            return $result['unescapedUrl'];
        }
    }
}
