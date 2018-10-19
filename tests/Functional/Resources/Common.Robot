*** Settings ***
Library  SeleniumLibrary

Resource  Variables.Robot

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

The user is in the site administration area
    Click Link  css=a[href$="/site/${DEFAULT_SITE_ID}"]
    Wait Until Page Contains  ${site.name}
    Wait Until Page Contains  Dashboard

End web test
    Capture Page Screenshot
    Close All Browsers

The user uses the 'Editor' button to edit site
    [Arguments]  ${site}
    Click Link  css=a[href*="/site/${site.id}/page"]
    Wait Until Page Contains Element  css=.page-list

get list of pages
    ${pages}=  Execute Javascript  var pages = []; document.querySelectorAll('.page-list__text').forEach((item) => { pages.push(item.innerText)}); return pages
    [Return]   ${pages}

Create site
    [Arguments]  ${name}  ${template}  ${host}  ${path}
    Click Element                       ${AddSiteButton}
    Wait Until Element is visible       ${AddSiteDialogue}
    Input Text                          ${AddSiteFormName}  ${name}
    Select from dropdown by number      ${AddSiteFormTemplate}  ${AddSiteFormTemplateList}  ${template}
    Input Text                          ${AddSiteFormHost}  ${host}
    Input Text                          ${AddSiteFormPath}  ${path}
    Click Element                       ${AddSiteFormButton}
    Wait Until Element Is Not Visible   ${AddSiteDialogue}
    Wait Until Page Contains            ${name}
    Wait Until Page Contains            ${host}
    Wait Until Page Contains            ${path}
    ${url}=  get element attribute  css=.el-table tbody tr:last-of-type a  href
    ${junk}  ${id} =  Split String From Right  ${url}  /  1
    &{site}=  Create Dictionary  name=${name}  host=${host}  path=${path}  url=${url}  id=${id}
    [Return]                            ${site}

The user clicks the 'Back' link in the top bar
    Click Element                       ${TopBarBackButton}

The user goes back to the manage sites screen via the 'Back to sites' link in the sites menu
    Click Element                       ${SitePicker}
    Wait Until Element Is Visible       ${SitePickerList}
    Click Link                          ${SitePickerList} li:last-of-type a
    Wait Until Element Is Visible       ${LoadingSpinner}
    Wait Until Element Is Not Visible   ${LoadingSpinner}