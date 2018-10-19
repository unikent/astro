*** Settings ***
Library  SeleniumLibrary
Variables    ../config.py

*** Variables ***

${AccountButton} =  css=.user-account-button
${username} =  ${SITE_EDITOR_USER}

*** Keywords ***
Begin web test
    Set Selenium Timeout    30 seconds
    Open Browser  ${START_URL}  ${BROWSER}
    # Maximize Browser Window  # TODO - ???
    Log in
    Click Element  ${AccountButton}
    page should contain  Signed in as ${username}
	Click Element  ${AccountButton}
    Set site variable

Set site variable
    &{site_var} =   Execute Javascript  var el = document.querySelector("a[href$='/site/${DEFAULT_SITE_ID}']"); return el ? {id:1, home: el.href, name: el.text, url: el.parentNode.parentNode.parentNode.querySelector("td:nth-child(2) span.el-tag").textContent} : false;
    Set Test Variable  &{site}  &{site_var}

Log in
    Select Frame  css=#authiframe iframe
    Wait Until Page Contains Element  css=[name="username"]
    Input Text  name=username  ${SITE_EDITOR_USER}
    Input Text  name=password  ${SITE_EDITOR_PASSWORD}
    Click Button  css=[type="submit"]
    Unselect Frame
    Wait Until Page Contains  Menu

End web test
    Capture Page Screenshot
    Close All Browsers

Insert testing data
    log  I am setting up the test data

Cleanup testing data
    log  I am cleaning up the test data