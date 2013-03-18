<?php

namespace Philip\Plugin;

use Philip\AbstractPlugin as BasePlugin;
use Philip\IRC\Response;
use Philip\IRC\Event;

/**
 * Uses the DarkSky API to get up-to-the-minute forecasts.
 *
 * @author Bill Israel <bill.israel@gmail.com>
 */
class DarkSkyPlugin extends BasePlugin
{

    /** @var \DarkSky $darksky An instance of the DarkSky API wrapper */
    private $darksky;


    /**
     * Uses the DarkSky API to retrieve weather data
     */
    public function init()
    {
        $config = $this->getConfig();

        if (!isset($config['api_key'])) {
            throw new \Exception('Unable to locate darksky_api_key in bot configuration.');
        }

        $this->darksky = new \DarkSky($config['api_key']);
        $plugin = $this;    // PHP 5.3, you suck.

        // Look at all this weather!
        $this->bot->onChannel('/^!ds (\w+)(.*)/', function(Event $event) use ($plugin) {
            $matches = $event->getMatches();
            switch($cmd = $matches[0]) {
                case 'now':
                case 'current':
                    $plugin->getCurrentWeather(trim($matches[1]), $event);
                    break;

                case 'forecast':
                case 'at':
                    $plugin->getPrecipitation(trim($matches[1]), $event);
                    break;

                default:
                    $plugin->addErrorMsg($event, "Unknown command: $cmd");
                    break;
            }
        });
    }

    /**
     * Gets the current weather and a simple prediction for the next hour.
     *
     * @param string $where The location to lookup
     * @param \Philip\IRC\Event $event The event to handle
     *
     * @return void
     */
    public function getCurrentWeather($where, $event)
    {
        if (!$found = $this->getLatLongForLocation($where)) {
            $this->addLatLongError($event);
            return;
        }

        try {
            list($lat, $long) = $found;
            $forecast = $this->darksky->getBriefForecast($lat, $long);

            $event->addResponse(
                Response::msg(
                    $event->getRequest()->getSource(),
                    sprintf('[Weather for %s] Currently: %s, %s degrees. For the next hour: %s',
                        $where,
                        $forecast['currentSummary'],
                        $forecast['currentTemp'],
                        $forecast['hourSummary']
                    )
                )
            );
        } catch (\Exception $ex) {
            $this->addErrorMsg($event, "Oops, I'm unable to get the weather for that location...");
            return;
        }
    }

    /**
     * Gets the precipitation at a specific place and time.
     *
     * @param string $match The remainder of the match (holds location info)
     * @param \Philip\IRC\Event $event The event to handle.
     *
     * @return void
     */
    public function getPrecipitation($match, $event)
    {
        $location = explode('@', $match);
        $where = trim($location[0]);
        $when  = trim($location[1]);

        if (!$found = $this->getLatLongForLocation($where)) {
            $this->addLatLongError($event);
            return;
        }

        try {
            $time = new \DateTime($when);
            list($lat, $long) = $found;
            $precipitation = $this->darksky->getPrecipitation(array(
                'lat'  => $lat,
                'long' => $long,
                'time' => $time->getTimestamp()
            ));

            $chanceOfRain = $precipitation['precipitation'][0]['probability'] * 100;
            $intensity = $precipitation['precipitation'][0]['intensity'];

            $event->addResponse(
                Response::msg(
                    $event->getRequest()->getSource(),
                    sprintf('[Weather for %s @ %s] %s (%d%% chance of rain)',
                        $where,
                        $time->format('g:ia'),
                        $this->getEnglishIntensity($intensity),
                        $chanceOfRain
                    )
                )
            );
        } catch (\Exception $ex) {
            $this->addErrorMsg($event, "Oops. I'm unable to get the precipitation for that location at that time...");
            return;
        }
    }

    /**
     * This method uses the Google Maps Geocoding API to retrieve the
     * lat and long for the given location.
     *
     *      More info here: https://developers.google.com/maps/documentation/geocoding/
     *
     * @param string $location The location to look up the lat/long for
     *
     * @return array An array of 2 elements: the lat and the long
     */
    public function getLatLongForLocation($location)
    {
        $encoded = urlencode($location);
        $response = json_decode(
            file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=$encoded"),
            true
        );

        if ($response['status'] === 'OK') {
            $lat = $response['results'][0]['geometry']['location']['lat'];
            $long = $response['results'][0]['geometry']['location']['lng'];

            return array($lat, $long);
        }

        return false;
    }

    /**
     * Add a lat/long error response to the event
     *
     * @param \Philip\IRC\Event $event The event to add a response to
     *
     * @return void
     */
    public function addLatLongError($event)
    {
        $this->addErrorMsg($event, "Oops. I'm unable to find a lat/long for that location...");
    }

    /**
     * Add an error message to the response.
     *
     * @param \Philip\IRC\Event $event The event to add a response to
     * @param string $msg The error message to send
     *
     * @return void
     */
    public function addErrorMsg($event, $msg)
    {
        $event->addResponse(Response::msg($event->getRequest()->getSource(), $msg));
    }

    /**
     * Converts an intensity value to a string.
     *
     * @param float $intensity The intensity value to convert to a string
     *
     * @return string
     */
    private function getEnglishIntensity($intensity)
    {
        $desc = 'no';

        if ($intensity >= 2 && $intensity < 15) {
            $desc = 'sporadic';
        } else if ($intensity >= 15 && $intensity < 30) {
            $desc = 'light';
        } else if ($intensity >= 30 && $intensity < 45) {
            $desc = 'moderate';
        } else if ($intensity > 45) {
            $desc = 'heavy';
        }

        return $desc . ' rain';
    }

    /**
     * Help messages!
     */
    public function displayHelp(Event $event)
    {
        return array(
            "!ds now|current <location> - retrieves the current conditions for the given location",
            "!ds forecast|at <location> @ <time> - retrieves the forecast for the location at the given time"
        );
    }

    /**
     * @see \Philip\AbstractPlugin#getName()
     */
    public function getName()
    {
        return 'DarkSkyPlugin';
    }
}