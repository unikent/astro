# API Feature Tests Framework

## Introduction

Each test class should cover a single API endpoint and run just below the HTTP level,
using Laravel's testing API to simulate HTTP requests. 

This tests routing, authentication, controllers, validation and the models themselves.

## Writing Tests

Tests should cover the following conditions:

- Unauthorized and unauthenticated API calls should fail.
- Authorized requests with invalid payloads should fail.
- Responses should have the expected status code and payload.
- Error responses (where expected) should be in the expected format.
  with expected payload for each error condition.
- Authorized, valid requests should update the database as expected.
- The internal site / page structure should be in the expected state after success or failure.

Tests should cover as many conditions as possible, both for success or failure. For
example, testing adding a page should check it works both for the default
(adding as last child of its parent) and also for adding a page before another child page.

### Utility Traits & Classes

Most tests will extend the base test class [APICommandTestBase](APICommandTestBase.php) 
which runs some basic tests and uses the [utility traits](Traits) to simplify:

 * Setting up test fixtures (sites, pages, users) based on json or php arrays
 * Loading test API payloads from the [fixture data support directory](../Support/Fixtures/featuredata)
 * Extracting named properties (e.g. page title, definition, etc) from json data
 * Making assertions on the database contents (e.g. pageIsAChildOf)
 * Validating that json returned matches a schema.
 * Validating that error responses are correct.

#### Extending APICommandTestBase

Extending this base class and implementing some simple abstract methods 
automatically gives you a large amount of test coverage of invalid,
unauthorized and unauthenticated requests to the API endpoint under test.

The basic example below is all that is needed to run the following tests for
each combination of authorized/unauthorized/unauthenticated user with 
valid / invalid request payload:

* request_withValidData_andInvalidJWT_failsWith401
* request_withInvalidData_andInvalidJWT_failsWith401
* request_withValidData_andNoBearerToken_failsWith401
* request_withInvalidData_andNoBearerToken_failsWith401
* request_withValidData_andUnauthorizedUsers_failsWith403
* request_withInvalidData_andUnauthorizedUsers_failsWith403
* request_withInvalidData_andAuthorizedUsers_failsWith422

Sample valid / invalid json payloads should be saved with appropriate
names in the  [Fixtures/featuredata](../Support/Fixtures/featuredata) directory.
 
See the [Request Payloads](#request-payloads) section below for more information.
 
##### Basic Example (the real AddPageTest has additional tests of its own)

```php
<?php

namespace Tests\Feature;

/**
 * Feature tests for the add page api endpoint.
 * @package Tests\Feature
 */
class AddPageTest extends APICommandTestBase
{
    // specify the users who are authorized to make this api request
	public $authorizedUsers = [
		'admin',
		'owner',
	];

    // implement the abstract methods from APICommandTestBase

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
        return '/api/v1/pages';
    }

    /**
     * This modifies the auto-loaded fixture data before it is used.
     * Here for example we set the correct parent_id based on our created site fixtures.
     * Use correct parent_ids in fixture data
     * @param array $input
     * @param string $test
     * @return array
     */
    protected function modifyFixtureData($input, $test)
    {
        if($input['parent_id']) {
            $input['parent_id'] = $this->site->draftHomepage->id;
        }
        return $input;
    }

    /**
     * Get the prefix used for filenames for fixture data for this command, e.g. "CreateSite",
     * @return string
     */
    public function fixtureDataPrefix()
    {
        return 'AddPage';
    }

    /**
     * @TODO - this isn't really used yet, just return true for now
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

    // ... additional custom tests go here
}
```

### Definitions & Fixtures

#### Definitions

The definitions path used by the tests is set to the
[tests/Support/Fixtures/definitions](../Support/Fixtures/definitions) folder by the
[Tests\TestCase](../TestCase.php) base class.

__These definitions may be used by other tests__ (such as unit) so if you need to change
one for a test consider creating a new test definition instead.

#### Fixtures

The following fixtures are created automatically before each test whose class uses
the [CreateFeatureFixtures](Traits/CreatesFeatureFixtures.php) trait.
It uses Laravel's DatabaseTransactions trait to run each test inside a transaction
which is then rolled back before the next test is run.

1. Three Sites
   1. A single page site based on the one-page-site-v1 definition.
   2. A multiple page site based on the multi-page-site-v1 definition.
   3. A single page site, which is valid by default,  based on the homepage-uses-layout-with-valid-region-with-valid-block-v1 definition.
2. User accounts, each named after the role they have on these two sites:
   1. owner
   2. editor
   3. contributor
3. An admin account (called 'admin')
4. An account with _no_ site membership, called 'randomer'
5. A 'viewer' account (can read everything, as used by astro-www)

All of these are available as [properties](Traits/CreatesFeatureFixtures.php) on the test class:

```php
   /**
    * @var Site|null - A single-page site, created as part of the fixtures
    */
    public $site = null;

   /**
    * @var Site|null - A multi-page site, created as part of the fixtures
    */
    public $multiPageSite = null;

   /**
    * @var User - user with admin privileges.
    */
    public $admin = null;

   /**
    * @var User - user with site owner privileges on the test site.
    */
    public $owner = null;

   /**
    * @var User - user with editor privileges on the test site.
    */
    public $editor = null;

   /**
    * @var User - user with contributor privileges on the test site.
    */
    public $contributor = null;

   /**
    * @var User - user with no privileges on the test site.
    */
    public $randomer = null;

   /**
    * @var User - user account with "viewer" level permissions (can read everything)
    */
    public $viewer = null;
```

#### Request Payloads

The [featuredata](../Support/Fixtures/featuredata) directory contains both valid and invalid
JSON format request payloads for each test. Defining these payloads here keeps the test classes simpler.

To allow the correct data for each test to be loaded by the `getValidFixtureData()` 
and `getInvalidFixtureData()` methods of the [LoadsFixtureData](Traits/LoadsFixtureData.php) 
trait, files should follow the following naming convention:

   `{test-name}_{valid|invalid}_{short-description}.json`

where
 
 * {test-name} is something like "createsite", "addpage", 
 * {short-description} describes this variation of the data (included in message when test fails).
 
The data is returned by those methods in a form usable by a phpUnit data provider.

This command will also (clumsily) strip out any comment sections contained within /* ... */
or lines beginning with zero or more white space characters followed by a #
(comments are not allowed in standard json but may be useful for documenting these fixtures).

### Testing for different user privileges

The simple way to do this is to use a data provider to provide the user accounts who should
or shouldn't be able to perform the operation under test.

The user accounts created by the [CreatesFeatureFixtures](Traits/CreatesFeatureFixtures.php)  
trait can be accessed directly as properties, eg. `$this->admin`, `$this->editor`, etc.

There are also two utility methods, `authorizedUsers()` and `unauthorizedUsers()` which 
can be used to get an array of `[username => username]` (suitable for a data provider)
for each user account which is either authorized or unauthorized

By default, all users except for `admin` are considered unauthorized.

Override the `authorizedUsers` property with an array of usernames that should be allowed to 
perform the command being tested to change this, eg:

```php
<?php

class MyCommandTest extends APICommandTestBase
{
    /**
     * admins, editors and owners will all be returned from $this->authorizedUsers() and not from $this->unauthorizedUsers()
      */
    public $authorizedUsers = ['admin', 'editor', 'owner'];
}
```

__N.B.__ - both the property AND the method are called `authorizedUsers`.
 
The method ensures that the array returned is keyed by its values which is useful in
providing useful error messages about which user account was under test when a test fails.

### User Data Providers

The data providers `unauthorizedUserProvider` and `authorizedUserProvider` return the same data as
`authorizedUsers` and `unauthorizedUsers` in the correct data provider format (an array of arrays),
eg.
 
    [ 
        username1 => [username1],
        username2 => [username2]
    ]

__N.B.__ - the data provider cannot return the actual user objects, as
data providers are run [before the phpunit setup method](https://phpunit.de/manual/current/en/writing-tests-for-phpunit.html#writing-tests-for-phpunit.data-providers).

Instead the name of the object properties that hold the desired user accounts are returned
and the test itself should access the user object using, eg:

```php
/**
 * @test
 * @dataProvider authorizedUserProvider
 */
public function testUsersCanDoSomething($user) {
    $user = $this->$user;
    // test something using this user...
}

```

### Data Provider Utility Methods

The [CreatesFeatureFixtures](Traits/CreatesFeatureFixtures.php) trait also provides 
two utility methods used when creating
[data providers](https://phpunit.de/manual/3.7/en/writing-tests-for-phpunit.html#writing-tests-for-phpunit.data-providers).

`packArrayForProvider()` and `combineForProvider()` are both useful for creating
permutations of both user account and test data to run in each test whilst retaining
information to indicate which data was in use when a test fails.

`packArrayForProvider()` takes an array of strings and returns
an array suitable to be returned by a data provider. PHPUnit expects the values 
for the array to also be arrays as data providers can provider
multiple arguments for each iteration of the test  (the values, which are passed as parameters to
the test method, must be arrays even if there is only one parameter), e.g.
 
    ['admin', 'owner'] 
    
becomes

    [
      'admin' => ['admin'], 
      'owner' => ['owner']
    ]

`combineForProvider()` takes multiple arrays or values as arguments and combines them
into permutations to be returned by a data provider. This is useful for example to test multiple
request payloads for multiple users, eg:

```php
<?php

$this->combineForProvider(
    ['admin', 'owner'],
    ['createSite_Valid_1' => $json1, 'createSite_valid_2' => $json2 ]
);
```

Result (note: the array key identifies the data set on test failure):

    [
        'admin_createsite_valid_1' => [ 'admin', $json1 ],
        'admin_createsite_valid_2' => [ 'admin', $json2 ],
        'owner_createsite_valid_1' => [ 'owner', $json1 ],
        'owner_createsite_valid_2' => [ 'owner', $json2 ]
    ]

### Testing API Responses

#### Successful Responses

Utility methods to extract certain properties from the JSON returned by the
API in order to test them exist in the 
[ExtractPageAttributesFromPageJson](Traits/ExtractsPageAttributesFromPageJson.php) trait.

__TODO__ - Are these being used and what else is needed?

#### Errors

The [MakesAssertionsAboutErrors](Traits/MakesAssertionsAboutErrors.php) trait
simplifies testing that error responses are in the correct JSON format
and have (where expected) the correct error message keys.

See the `assertValidErrorResponseBody()` for details of how to use it.

__TODO__ - move expected error details into each test fixture itself and
automate the running of this test.


### Validating the Database

As well as testing the response from the API, tests should where possible ensure that the database itself
has been updated as expected by each test.

To simplify testing should the database structure change in the future, utility methods to test this
have been abstracted into two traits, [MakesAssertionsAboutSites](Traits/MakesAssertionsAboutSites.php)
and [MakesAssertionsAboutPages](Traits/MakesAssertionsAboutPages.php).

__TODO__ - add functionality to simplify confirming that no unexpected changes have been made to the data.

### Validating JSON Response Schemas

__TODO__ use this functionality and document it better

The [ValidatesJsonSchema](Traits/ValidatesJsonSchema.php) trait has an `assertValidJsonSchema()` method
which takes a JSON response and the filename of a schema stored in the [Support/Schemas](../Support/Schemas) 
directory and returns true or false depending on whether or not the JSON validates against the schema.

