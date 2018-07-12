*** Settings ***
Library  SeleniumLibrary

*** Variables ***

${SchoolName} =  Harry's School Site
${SchoolNameHeader} =  css=body > div.editor > div.top-bar > div:nth-child(1) > div.site-pick

*** Keywords ***

the user is in the site administration area
    click link  ${SchoolName}
    element should contain  ${SchoolNameHeader}  ${SchoolName}
    wait until page contains  Dashboard