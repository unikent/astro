*** Settings ***
Library  SeleniumLibrary
Variables    ../../config.py

*** Variables ***

${username} =  ${SITE_EDITOR_USER}

*** Keywords ***

Insert Credentials
    Input Text  id=username  ${SITE_EDITOR_USER}
    Input Text  id=password  ${SITE_EDITOR_PASSWORD}