*** Settings ***
Library  SeleniumLibrary
Variables    ../../config.py

*** Variables ***

${username} =  ${SITE_EDITOR_USER}

*** Keywords ***

Insert credentials
    Input Text  name=username  ${SITE_EDITOR_USER}
    Input Text  name=password  ${SITE_EDITOR_PASSWORD}