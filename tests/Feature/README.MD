# API Feature Tests Framework

## Introduction

These tests target the API and sit just below the HTTP level, using Laravel's test
API to simulate HTTP requests. They cover everything from routing through controllers
actions, through models, etc.

It is likely that a bug will call many of these tests to fail at once, without giving a
clear indication of where the bug lies; that is OK - the goal is to stop bugs from 
sneaking through to production.

"Helper" traits exist to:
 
 * Setup existing sites / pages in the database based of json or php arrays
 * Extract named properties (e.g. page title, definition, etc) from json data
 * Make assertions data objects (e.g. pageIsAChildOf)
 * Validate that json returned matches a schema.


## Test Configuration 

Each command exposed by the API should have one or more tests giving wide coverage.
Tests should also exist for conditions where the API is expected to return an error.

Most test classes will want to use one or more of the utility traits (especially the
CreatesFeatureFixtures which sets up the test database to a known state before each test).

### Test Definitions

The Tests\TestCase base class sets the definitions path to the ``tests/Support/Fixtures/definitions`` folder.

## Writing Tests

The API should be tested against requests from each of these users, ensuring that
they succeed or fail as expected given the privileges for each account.

The simplest way to do this is to use phpunit data providers to run each test
with each valid (or invalid) user.

Due to the fact that the data provider cannot return the actual user object, as
data providers run BEFORE the phpunit setup method 
(see note on https://phpunit.de/manual/current/en/writing-tests-for-phpunit.html#writing-tests-for-phpunit.data-providers);
instead they should return the property names of the desired user accounts
and the test itself should access the user object using, eg:

```php
/**
 * @test
 * @dataProvider usersWhoCanDoSomethingProvider
 */
public function testUsersCanDoSomething($user) {
    $user = $this->$user;
    // test something using this user...
}

/**
 * provide an array (of arrays) of property names for the objects that can do this something
 * @return array
 */
public function usersWhoCanDoSomethingProvider()
{
    return [
        'admin' => ['admin'],
        'owner' => ['owner']
    ];
}
```

Note that the "key" in the array returned by the data provider can be output by
phpunit to identify which data a test fails on. PHPUnit expects the values 
for the array to also be arrays as data providers can potentially return 
multiple arguments for each iteration of the test.	 
	 
The utility method ``packArrayForProvider`` takes an array of strings and returns
an array suitable for a dataprovider to return, e.g. 
 
    ['admin', 'owner'] -> 
    [
      'admin' => ['admin'], 
      'owner' => ['owner']
    ]


### Test Request Payloads

The directory ``tests/Support/Fixtures/featuredata`` contains json files containing payloads
to use for testing. Defining these payloads here keeps the test classes simpler.

Files should follow the following naming convention:

   "\<test-name>\_<valid|invalid>\_<short-description>.json"

where
 
 * \<test-name> is something like "createsite", "addpage", 
 * \<short-description> is just something that describes this variation of the data.
 
 
The trait ``LoadsFixtureData`` has two methods ``getValidFixtureData`` and ``getInvalidFixtureData``
which take a parameter matching ``\<test-name>`` and returns the json contained
in any fixture files matching that name with ``invalid`` or ``valid`` respectively.

The data is returned in a form usable by a data provider.

This command will also (clumsily) strip out any commentsections contained within /* ... */
or lines beginning with zero or more white space characters followed by a #
(comments are not allowed in json).

## Helper Traits

### CreatesFeatureFixtures

This trait sets up the test environment before each test. 

It uses Laravel's DatabaseTransactions trait to run each test inside a transaction
which is then rolled back.

It creates:

* A test site
* Five users:
  * An admin account
  * Standard users with owner, editor and contributor permissions on the test site
  * A standard user NOT connected to the test site.
  * The user objects are available as attributes of the test class, named:
    $this->admin, $this->editor, $this->owner, $this->contributor, $this->randomer
    

### LoadsFixtureData

