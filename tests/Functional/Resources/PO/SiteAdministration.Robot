*** Settings ***
Library  SeleniumLibrary

*** Variables ***

*** Keywords ***

The user is in the site administration area
    Click Link  css=a[href$="/site/${DEFAULT_SITE_ID}"]
    Wait Until Page Contains  ${site.name}
    Wait Until Page Contains  Dashboard
