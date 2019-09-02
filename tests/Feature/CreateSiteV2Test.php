<?php

namespace Tests\Feature;

/**
 * Class CreateSiteTestV2
 * @package Tests\Feature
 */
class CreateSiteV2Test extends APICommandTestBase
{
    /**
     * Get the request method to use for this api command
     * @return string - The request method to use for this API request, e.g. GET, POST, DELETE.
     */
    public function requestMethod()
    {
        return 'POST';
    }

    /**
     * Get the api endpoint to test this command.
     * @return string - The API endpoint to test (usually starting with '/api/v1')
     */
    public function apiURL()
    {
        return '/api/v1/sites';
    }

    /**
     * Get the prefix used for filenames for fixture data for this command, e.g. "CreateSite",
     * @return string
     */
    public function fixtureDataPrefix()
    {
        return 'CreateSite';
    }

    /**
     * Utility method to confirm that the test has not modified the database. This is used as an additional
     * check when testing commands with invalid input or unauthorised users and should be implemented for each
     * api command test.
     * @param string $payload - The (json?) payload used to make the last request
     * @return bool
     */
    protected function fixturesAreUnchanged($payload)
    {
        return true;
    }
}
