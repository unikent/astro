*** Settings ***
Library  SeleniumLibrary

Resource  ../Resources/Credentials/Credentials.Robot

*** Keywords ***
begin web test
    Open Browser  ${START_URL}  ${BROWSER}
    Maximize Browser Window
    Credentials.Insert Credentials
    Click Button  css=#submitButton
    Wait Until Page Contains  Menu
    click element  ${AccountButton}
    page should contain  Signed in as ${username}


End web test
    Close Browser



Insert Testing Data
    log  I am setting up the test data



Cleanup Testing Data
    log  I am cleaning up the test data