*** Settings ***
Library  SeleniumLibrary

Resource  ../Resources/Credentials/Credentials.Robot

*** Keywords ***
Begin web test
    Open Browser  ${START_URL}  ${BROWSER}
    Maximize Browser Window
    Insert Credentials
    Click Button  css=#submitButton
    Wait Until Page Contains  Menu
    click element  ${AccountButton}
    page should contain  Signed in as ${username}

End web test
    Close Browser

Insert testing data
    log  I am setting up the test data

Cleanup testing data
    log  I am cleaning up the test data