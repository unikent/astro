# API Feature Tests Framework

## Introduction

These tests target the API and sit just below the HTTP level, using Laravel's test
API to simulate HTTP requests. They cover everything from routing through controllers
actions, through models, etc.

It is likely that a bug will call many of these tests to fail at once, without giving a
clear indication of where the bug lies; that is OK - the goal is to stop bugs from 
sneaking through to production.

"Helper" traits exist to:
 
 * Setup existing sites / pages in the database based on json or php arrays
 * Extract named properties (e.g. page title, definition, etc) from json data
 * Make assertions on the database contents (e.g. pageIsAChildOf)
 * Validate that json returned matches a schema.
 * Validate that error responses are correct.

## Test Configuration 

Each command exposed by the API should have one or more tests giving wide coverage.
Tests should also exist for conditions where the API is expected to return an error.

Most test classes will want to use one or more of the utility traits (especially the
CreatesFeatureFixtures which sets up the test database to a known state before each test).

### Test Definitions

The Tests\TestCase base class sets the definitions path to the ``tests/Support/Fixtures/definitions`` folder.

## Writing Tests

Tests should cover the following conditions:

- API calls should succeed or fail for each type of user account.
- Responses should have the expected status code and payload.
- Error responses (where expected) should be in the expected format with expected values for each error condition.
- The internal site / page structure should be in the expected state after each command.

### Setting up the environment

Tests should use the [CreatesFeatureFixtures trait](Traits/CreatesFeatureFixtures.php) which automatically
sets up the following environment before each test:

- Two sites based on a test site definition.
- An admin, viewer, owner, editor, contributor and randomer user account.
- Gives the admin and viewer accounts "admin" and "viewer" global roles.
- Adds the owner, editor and contributor to the first site and assigns them the roles they are named after.
- Does not assign the randomer account any role on any site.
- Runs the artisan command to setup permissions for roles.

It also provides two utility methods used when creating
[data providers](https://phpunit.de/manual/3.7/en/writing-tests-for-phpunit.html#writing-tests-for-phpunit.data-providers) 
to be consumed by tests, `packArrayForProvider()` and `combineForProvider()`. These are useful for creating
permutations of both user account and test data to run in each test whilst including keys which make
failed test error messages useful.

The utility method `packArrayForProvider()` takes an array of strings and returns
an array suitable to be returned by a data provider. PHPUnit expects the values 
for the array to also be arrays as data providers can provider
multiple arguments for each iteration of the test  (the values, which are passed as parameters to
the test method, must be arrays even if there is only one parameter), e.g.
 
    ['admin', 'owner'] -> 
    [
      'admin' => ['admin'], 
      'owner' => ['owner']
    ]

The "key" in the array returned by a data provider can be output by
phpunit to identify which data a test fails on. 

The `combineForProvider()` method takes multiple arrays or values as arguments and combines them
into permutations to be returned by a data provider. This is useful for example to test multiple
request payloads for multiple users, eg:

```php
<?php

$this->combineForProvider(
	['admin', 'owner'],
	['createSite_Valid_1' => $json1, 'createSite_valid_2' => $json2 ]
);

/* Result (note: the array key identifies the data set on test failure)
[
 'admin_createsite_valid_1' => [ 'admin', $json1 ],
 'admin_createsite_valid_2' => [ 'admin', $json2 ],
 'owner_createsite_valid_1' => [ 'owner', $json1 ],
 'owner_createsite_valid_2' => [ 'owner', $json2 ]
]
*/
```

### Testing for different user privileges

The simple way to do this is to use a data provider to provide the users accounts who should
or shouldn't be able to perform the operation under test.

The user accounts created by the [CreatesFeatureFixtures trait](Traits/CreatesFeatureFixtures.php) 
can be accessed directly as properties, eg. `$this->admin`, `$this->editor`, etc.

There are also two utility methods, `authorizedUsers()` and `unauthorizedUsers()` which can be used
to get an array of [username => username] for each user account which is either authorized or unauthorized
to perform the command being tested. By default, all users except for `admin` are considered unauthorized.
To alter this override the `authorizedUsers` property with an array of usernames that should be allowed to 
perform the command being tested, eg:

```php
<?php

class MyCommandTest extends TestCase
{
	use CreatesFeatureFixtures;
	
	/**
	 * admins, editors and owners will all be returned from $this->authorizedUsers() and not from $this->unauthorizedUsers()
 	 */
	public $authorizedUsers = ['admin', 'editor', 'owner'];
}
```

*NOTE - both the property AND the method are called `authorizedUsers` - the method ensures that
the array returned is keyed by its values which is useful in providing useful error messages about 
which user account was under test when a test fails.*

There are also two data provider methods `unauthorizedUserProvider` and `authorizedUserProvider`
which return the same data but in the correct format for use as a data provider (an array of arrays),
eg. [ username1 => [username1], username2 => [username2].

*The data provider cannot return the actual user objects, as
data providers are run [before the phpunit setup method](https://phpunit.de/manual/current/en/writing-tests-for-phpunit.html#writing-tests-for-phpunit.data-providers).
Instead your data providers should return the names of the object properties that hold the desired user accounts
and the test itself should access the user object using, eg:*

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
    return $this->authorizedUsers();
}
```

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

