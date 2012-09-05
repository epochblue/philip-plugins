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
     * Set up all the canned responses
     */
    private function addResponses()
    {
        // basic response
        $this->addResponse('/i (love|<3) (you|u)/i', function($matches) { 
            return 'Shutup baby, I know it!'; 
        });

        // matches things like "bot: you're the greatest thing ever!" and saves the attribute for later
        $this->addResponse('/(you are|you\'re) (the |a |an )*([\w ]+)/i', function($matches) {             
            $this->addAttribute(trim($matches[2] .' '. trim($matches[3])));
            return "No, *you're* {$matches[2]} ". trim($matches[3]) .'!';
        });

        // matches things like "bot is amazing!" and saves the attribute for later
        $this->addResponse('/is (the |a |an )*([\w ]+)/i', function($matches) { 
            $this->addAttribute(trim($matches[1] .' '. trim($matches[2])));
            return "No, *you're* {$matches[1]} ". trim($matches[2]) .'!';
        });

        // responds to things like "who is bot?" with a remembered attribute
        $this->addResponse('/(what|who) (are you|is)/i', function($matches) {
            $index = rand(0, count($this->attributes)- 1);
            return "I'm {$this->attributes[$index]}.";
        });
    }

    /**
     * Add an attribute to the bot
     *
     * @param string $attribute
     */
    private function addAttribute($attribute)
    {
        $this->attributes[] = $attribute;
    }

    /**
     * Init the plugin and start listening to messages
     */
    public function init()
    {
        $this->addResponses();

        $config = $this->bot->getConfig();

        // detects someone speaking to the bot
        $address_re = "/(^{$config['nick']}(.+)|(.+){$config['nick']}[!.?]*)$/i";
        $this->bot->onChannel($address_re, function($request, $matches) {
            $message = $matches[1] ? $matches[1] : $matches[2];
            
            foreach ($this->responses as $regex => $function) {
                if (preg_match($regex, $message, $matches)) {                
                    return Response::msg($request->getSource(), $function($matches));
                }
            }
        });
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

    /** @var array */
    private $responses = array();

    /** @var array */
    private $attributes = array('an IRC bot');
}
