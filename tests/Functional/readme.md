Functional tests
================

The functioal tests are writted with [Robotframework](http://robotframework.org).

## Setup and running tests

The easiest way to get going is with docker. You can [download and install docker from here](https://www.docker.com/get-started). 

Otherwise [follow the instaructions here to install robot framewok on your machine](https://github.com/robotframework/robotframework/blob/master/INSTALL.rst). 

### Config

 * Copy the `config.sample.py` file and save it as `config.py`, then...
   * Set the `START_URL` the the URL of the instance of the site editor to be tested.
   * Set the `SITE_EDITOR_USER` and `SITE_EDITOR_PASSWORD` to the username and password of the primary user with which the site will be tested.
 * Copy the `docker.env.sample` file and save it as `docker.env`.

### Running tests

If you have docker installed run the tests simple with:

`docker-compose run robot`.

Otherwise, use: 

`robot --outputdir reports/ ./Tests/`. 

