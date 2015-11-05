<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\GeocodeFarmProvider;

class GeocodeFarmProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new GeocodeFarmProvider($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('geocode_farm', $provider->getName());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetGeocodedDataWithNullApiKey()
    {
        $provider = new GeocodeFarmProvider($this->getMock('Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $provider->getGeocodedData('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage Could not execute empty address.
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new GeocodeFarmProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage Could not execute empty address.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new GeocodeFarmProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeocodeFarmProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithIP()
    {
        $provider = new GeocodeFarmProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('127.0.0.1');
    }



    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeocodeFarmProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new GeocodeFarmProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('::1');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        $json = '{
    "geocoding_results": {
        "LEGAL_COPYRIGHT": {
            "copyright_notice": "Copyright (c) 2014 Geocode.Farm - All Rights Reserved.",
            "copyright_logo": "https:\/\/www.geocode.farm\/assets\/img\/logo.png",
            "terms_of_service": "https:\/\/www.geocode.farm\/policies\/terms-of-service\/",
            "privacy_policy": "https:\/\/www.geocode.farm\/policies\/privacy-policy\/"
        },
        "STATUS": {
            "access": "FREE_USER, ACCESS_GRANTED",
            "status": "SUCCESS",
            "latitude_provided": "45.2040305",
            "longitude_provided": "-93.3995728",
            "result_count": 1
        },
        "ACCOUNT": {
            "ip_address": "127.0.0.1",
            "distribution_license": "NONE, UNLICENSED",
            "usage_limit": "250",
            "used_today": "101",
            "used_total": "251",
            "first_used": "2014-02-23 02:22:32"
        },
        "RESULTS": [
            {
                "result_number": 1,
                "formatted_address": "522-534 West Main Street, Anoka, MN 55303, USA",
                "accuracy": "HIGH_ACCURACY",
                "ADDRESS": {
                    "street_number": "522-534",
                    "street_name": "West Main Street",
                    "locality": "Anoka",
                    "admin_2": "Anoka County",
                    "admin_1": "Minnesota",
                    "postal_code": "55303",
                    "country": "United States"
                },
                "LOCATION_DETAILS": {
                    "elevation": 868.67,
                    "timezone_long": "Central Standard Time",
                    "timezone_short": "America\/Chicago"
                },
                "COORDINATES": {
                    "latitude": "45.2040307639451",
                    "longitude": "-93.3995726274743"
                },
                "BOUNDARIES": {
                    "northeast_latitude": "45.2040307758898",
                    "northeast_longitude": "-93.3995726521629",
                    "southwest_latitude": "45.2026515197081",
                    "southwest_longitude": "-93.4008553302914"
                }
            }
        ],
        "STATISTICS": {
            "https_ssl": "ENABLED, SECURE"
        }
    }
}';

        $provider = new GeocodeFarmProvider($this->getMockAdapterReturns($json), 'api_key');
        $result = $provider->getGeocodedData('522-534 West Main Street, Anoka, MN 55303, USA');

        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('Anoka', $result['city']);
        $this->assertEquals('Minnesota', $result['regionCode']);
        $this->assertEquals('West Main Street', $result['streetName']);
        $this->assertEquals('522-534', $result['streetNumber']);
        $this->assertEquals('45.2040307639451', $result['latitude']);
        $this->assertEquals('-93.3995726274743', $result['longitude']);
        $this->assertEquals('55303', $result['zipcode']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeocodeFarmProvider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        //$provider = new GeocodeFarmProvider($this->getMockAdapter($this->never()), 'api_key');
        //$provider->getReversedData(array(1, 2));
    }
}
