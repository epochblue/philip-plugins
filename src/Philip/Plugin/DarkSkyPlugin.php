<?php

namespace Philip\Plugin;

use Philip\AbstractPlugin as BasePlugin;
use Philip\IRC\Response;

/**
 * Uses the DarkSky API to get up-to-the-minute forecasts.
 *
 * @author Bill Israel <bill.israel@gmail.com>
 */
class DarkSkyPlugin extends BasePlugin
{
    /**
     * Uses the DarkSky API to retrieve weather data
     */
    public function init()
    {
        $config = $this->bot->getConfig();

        if (!isset($config['darksky_api_key'])) {
            throw new \Exception('Unable to locate darksky_api_key in bot configuration.');
        }

        $plugin = $this;    // PHP5.3, you suck.
        $darkSky = new \DarkSky($config['darksky_api_key']);

        // Weather, bitches.
        $this->bot->onChannel('/^!ds (.*)/', function ($event) use ($plugin, $darkSky) {
            $matches = $event->getMatches();
            $where = $matches[0];

            if (!$found = $plugin->getLatLongForLocation($where)) {
                $event->addResponse(
                    Response::msg(
                        $event->getRequest()->getSource(),
                        "Oops, sorry. I'm unable to find a lat/long for that location."
                    )
                );

                return;
            }

            list($lat, $long) = $found;
            $forecast = $darkSky->getBriefForecast($lat, $long);

            $event->addResponse(
                Response::msg(
                    $event->getRequest()->getSource(),
                    sprintf(
                        '[Weather for %s] Currently: %s-degrees and %s. For the next hour: %s',
                        $where,
                        $forecast['currentTemp'],
                        $forecast['currentSummary'],
                        $forecast['hourSummary']
                    )
                )
            );
        });
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
}