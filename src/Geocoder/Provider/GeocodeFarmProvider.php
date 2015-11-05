<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\InvalidCredentialsException;
use Geocoder\Exception\QuotaExceededException;
use Geocoder\HttpAdapter\HttpAdapterInterface;
use Geocoder\Exception\NoResultException;
use Geocoder\Exception\UnsupportedException;

/**
 * @author Max V. Kovrigovich <mvk@tut.by>
 */
class GeocodeFarmProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    //const GEOCODE_ENDPOINT_URL = 'http://www.geocodefarm.com/api/forward/json/%s/%s/';
    const GEOCODE_ENDPOINT_URL = 'https://www.geocode.farm/v3/json/forward/?addr=%s&country=%s&lang=%s&count=1&key=%s';

    /**
     * @var string
     */
    //const REVERSE_ENDPOINT_URL = 'http://www.geocodefarm.com/api/reverse/json/%s/%F/%F/';
    const REVERSE_ENDPOINT_URL = 'https://www.geocode.farm/v3/json/reverse/?lat=%F&lon=%F&country=%s&lang=%s&count=1&key=%s';

    /**
     * @var string
     */
    private $apiKey = null;

    public function __construct(HttpAdapterInterface $adapter, $apiKey = null, $locale = null)
    {
        parent::__construct($adapter, $locale);

        $this->apiKey = $apiKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address, $country = 'us', $lang = 'en')
    {
        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The GeocodeFarmProvider does not support IP addresses.');
        }
/*
        if (null === $this->apiKey) {
            throw new InvalidCredentialsException('No API Key provided.');
        }*/

        if (null === $address || '' === $address) {
            throw new UnsupportedException('Could not execute empty address.');
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, $address, $country, $lang, $this->apiKey);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        /*if (null === $this->apiKey) {
            throw new InvalidCredentialsException('No API Key provided.');
        }*/

        $query = sprintf(self::REVERSE_ENDPOINT_URL, $this->apiKey, $coordinates[0], $coordinates[1]);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'geocode_farm';
    }

    /**
     * @param string $query
     *
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = $this->getAdapter()->getContent($query);

        if (null === $content) {
            throw new UnsupportedException(sprintf('Could not execute query %s', $query));
        }

        $json = json_decode($content, true);

        if ($json['geocoding_results']['STATUS']['status'] == 'FAILED, ACCESS_DENIED') {
            throw new QuotaExceededException(sprintf('There are account issues and query was not successfully executed: %s', $json['geocoding_results']['STATUS']['access']));
        }

        if ($json['geocoding_results']['STATUS']['status'] == 'FAILED, NO_RESULTS') {
            throw new NoResultException(sprintf('Could not find results for given query: %s', $query));
        }

        $geocodeFarmResult = $json['geocoding_results']['RESULTS'][0];

        $city = $geocodeFarmResult['ADDRESS']['locality'];
        $streetName = $geocodeFarmResult['ADDRESS']['street_name'];
        $streetNumber = $geocodeFarmResult['ADDRESS']['street_number'];
        $state = $geocodeFarmResult['ADDRESS']['admin_1'];
        $zip = $geocodeFarmResult['ADDRESS']['postal_code'];
        $country = $geocodeFarmResult['ADDRESS']['country'];

        $latitude = $geocodeFarmResult['COORDINATES']['latitude'];
        $longitude = $geocodeFarmResult['COORDINATES']['longitude'];

        $result = array_merge($this->getDefaults(), array(
            'latitude'      => $latitude,
            'longitude'     => $longitude,
            'city'          => $city,
            'regionCode'    => $state,
            'zipcode'       => $zip,
            'country'       => $country,
            'streetName'    => $streetName,
            'streetNumber'  => $streetNumber,
        ));

        return $result;
    }
}
