*** Settings ***
Library  SeleniumLibrary
Library  FakerLibrary
Library  String


*** Variables ***

${AddSiteButton} =  id=add-site
${AddSiteDialogue} =  css=.el-dialog__wrapper
${AddSiteFormName} =  id=input-site-name
${AddSiteFormTemplate} =  id=input-site-template
${AddSiteFormTemplateList} =  css=.input-site-template
${AddSiteFormHost} =  id=input-site-host
${AddSiteFormPath} =  id=input-site-path
${AddSiteFormButton}=  id=input-add-site-button


*** Keywords ***

the user creates two different sites
	${site1_name} =  FakerLibrary.Company
    ${site1_host} =  FakerLibrary.Domain Name
    ${site1_path} =  FakerLibrary.Uri Path

	${site2_name} =  FakerLibrary.Company
    ${site2_host} =  FakerLibrary.Domain Name
    ${site2_path} =  FakerLibrary.Uri Path

	
	&{site1_details}=  Create site  ${site1_name}  1  ${site1_host}  ${site1_path}
	&{site2_details}=  Create site  ${site2_name}  2  ${site2_host}  ${site2_path}
	
	Set Test Variable  &{site1}  &{site1_details}
	Set Test Variable  &{site2}  &{site2_details}

When the user visits site 1 via the 'Editor' button on the manage sites screen
	the user uses the 'Editor' button to edit site  ${site1}	

the user uses the 'Editor' button to edit site
	[Arguments]  ${site}
	Click Link  css=a[href*="/site/${site.id}/page"]

the user takes note of the list of pages in site 1
	${pages}=  get list of pages
	${site1.pages}=  ${pages}

get list of pages
	Wait Until Page Contains Element  css=.page-list
	${pages}=  Execute Javascript  var pages = []; document.querySelectorAll('.page-list__text').forEach((item) => { pages.push(item.innerText)}); return pages
	[Return]   ${pages}
	

Create site
	[Arguments]  ${name}  ${template}  ${host}  ${path}
	Click Element 						${AddSiteButton}
	Wait Until Element is visible 		${AddSiteDialogue}
	Input Text  						${AddSiteFormName}  ${name}
	Select from dropdown by number		${AddSiteFormTemplate}  ${AddSiteFormTemplateList}  ${template}
	Input Text  						${AddSiteFormHost}  ${host}
	Input Text  						${AddSiteFormPath}  ${path}
	Click Element 						${AddSiteFormButton}
	Wait Until Element Is Not Visible   ${AddSiteDialogue}
    Wait Until Page Contains            ${name}
    Wait Until Page Contains            ${host}
    Wait Until Page Contains            ${path}
	${url}=  get element attribute  css=.el-table tbody tr:last-of-type a  href
	${junk}  ${id} =  Split String From Right  ${url}  /  1
	&{site}=  Create Dictionary  name=${name}  host=${host}  path=${path}  url=${url}  id=${id}
    [Return]                            ${site}


Select from dropdown by number
	[Arguments]  ${dropdown}  ${dropdown_list}  ${number}
	Click Element                       ${dropdown}
    Wait Until Element Is Visible       ${dropdown_list}
    Click Element                       ${dropdown_list} ul.el-select-dropdown__list li:nth-of-type(${number})

