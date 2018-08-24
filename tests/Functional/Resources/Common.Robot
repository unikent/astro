*** Settings ***
Library  SeleniumLibrary

Resource  ../Resources/Credentials/Credentials.Robot

*** Keywords ***
Begin web test
    Set Selenium Timeout    30 seconds
    Open Browser  ${START_URL}  ${BROWSER}
    # Maximize Browser Window  # TODO - ???
    Log in
    click element  ${AccountButton}
    page should contain  Signed in as ${username}
    Set site variable

Set site variable
    &{site_var} =   Execute Javascript  var el = document.querySelector("a[href$='/site/${DEFAULT_SITE_ID}']"); return el ? {id:${DEFAULT_SITE_ID}, url: el.href, name: el.text} : false;
    Set Test Variable  &{site}  &{site_var}

Log in
    Insert Credentials
    Click Button  css=[type="submit"]
    Wait Until Page Contains  Menu

End web test
    Capture Page Screenshot
    Close Browser

Insert testing data
    log  I am setting up the test data

Cleanup testing data
    log  I am cleaning up the test data