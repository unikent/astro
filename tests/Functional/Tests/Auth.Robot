*** Settings ***
Documentation   This suite of tests checks that authentication and authorisation works as expected

Resource  ../Resources/Common.Robot
Resource  ../Resources/Auth.Robot

Test Setup  Begin Web Test
Test Teardown  End Web test


*** Test Cases ***
Site pages are loaded correctly across different sites
    [Tags]  wip
    Given the user creates two different sites
    When the user visits site one via the 'Editor' button on the manage sites screen
    And the user takes note of the list of pages in site one
    And the user clicks the 'Back' link
    And the user goes back to the manage sites screen via the 'Back to sites' link in the sites menu
    And the user visits site two via the 'Editor' button on the manage sites screen
    Then the list of pages in site two should be different from the list of pages in site one

