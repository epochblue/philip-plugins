<?php

use Philip\IRC\Response;

/**
 * Adds some canned responses to the Philip IRC bot.
 *
 * @author Sean Crystal <seancrystal@gmail.com>
 */
class CannedResponsePlugin
{
    /** @var \Philip\IRC\Philip */
    protected $bot;

    /** @var array */
    private $responses = array();

    /** @var array */
    private $attributes = array('an IRC bot');

    /**
     * Constructor
     *
     * @param \Philip\IRC\Philip $bot
     */
    public function __construct($bot)
    {
        $this->bot = $bot;
    }

    /**
     * Init the plugin and start listening to messages
     */
    public function init()
    {
        $this->addResponses();

        $config = $this->bot->getConfig();

        // detects someone speaking to the bot
        $responses = $this->responses;
        $address_re = "/(^{$config['nick']}(.+)|(.+){$config['nick']}[!.?]*)$/i";
        $this->bot->onChannel($address_re, function($event) use ($responses) {
            $matches = $event->getMatches();
            $message = $matches[1] ? $matches[1] : $matches[2];
            
            foreach ($responses as $regex => $function) {
                if (preg_match($regex, $message, $matches)) {
                    $event->addResponse(
                        Response::msg($event->getRequest()->getSource(), $function($matches))
                    );
                }
            }
        });
    }

    /**
     * Set up all the canned responses
     */
    private function addResponses()
    {
        // workaround for $this being unavailable in closures
        $plugin = $this;

        // basic response
        $this->addResponse('/i (love|<3) (you|u)/i', function($matches) { 
            return 'Shutup baby, I know it!'; 
        });

        // matches things like "bot: you're the greatest thing ever!" and saves the attribute for later
        $this->addResponse('/(you are|you\'re) (the |a |an )*([\w ]+)/i', function($matches) use ($plugin) {             
            $plugin->addAttribute(trim($matches[2] .' '. trim($matches[3])));
            return "No, *you're* {$matches[2]} ". trim($matches[3]) .'!';
        });

        // matches things like "bot is amazing!" and saves the attribute for later
        $this->addResponse('/is (the |a |an )*([\w ]+)/i', function($matches) use ($plugin) { 
            $plugin->addAttribute(trim($matches[1] .' '. trim($matches[2])));
            return "No, *you're* {$matches[1]} ". trim($matches[2]) .'!';
        });

        // responds to things like "who is bot?" with a remembered attribute
        $this->addResponse('/(what|who) (are you|is)/i', function($matches) use ($plugin) {
            return "I'm ". $plugin->getRandomAttribute();
        });
    }

    /**
     * Get a random attribute
     */
    public function getRandomAttribute()
    {
        $index = rand(0, count($this->attributes) - 1);
        return $this->attributes[$index];
    }

    /**
     * Add an attribute to the bot
     *
     * @param string $attribute
     */
    public function addAttribute($attribute)
    {
        $this->attributes[] = $attribute;
    }

    /**
     * Helper method to add responses to the plugin
     *
     * @param string $regex
     * @param \Closure $function
     */
    private function addResponse($regex, $function)
    {
        $this->responses[$regex] = $function;
    }
}
