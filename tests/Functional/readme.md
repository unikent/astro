Functional tests
================

The functioal tests are writted with [Robotframework](http://robotframework.org).

## Setup and running tests

The easiest way to get going is with docker. You can [download and install docker from here](https://www.docker.com/get-started). 

Otherwise [follow the instaructions here to install robot framewok on your machine](https://github.com/robotframework/robotframework/blob/master/INSTALL.rst). Once installed, run the tests with 

### Running tests

If you have docker installed run the tests simple with:

`docker-compose run robot`.

Otherwise, use: 

`robot --outputdir results/ ./Tests`. 

