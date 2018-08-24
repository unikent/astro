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
 * Copy the `.env.sample` file and save it as `.env`, then...
   * If running the tests against a local env... Set the `IP_ADDRESS` to the IP address of the 

### Running tests

If you have docker installed run the tests simple with:

`docker-compose run robot`.

To pass specific robot specific option, do something like `docker-compose run -e ROBOT_OPTIONS="-T -i wip" robot`

For a wider screenwith, add `-e SCREEN_WIDTH=3000`

See [https://github.com/ppodgorsek/docker-robot-framework](https://github.com/ppodgorsek/docker-robot-framework) for more details of running the tests with docker.

Otherwise (in the absense of docker), use: 

`robot --outputdir reports/ ./Tests/`. 

