*** Settings ***
Library  SeleniumLibrary
Library  FakerLibrary


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

	Create site  ${site1_name}  1  ${site1_host}  ${site1_path}
	Create site  ${site2_name}  2  ${site2_host}  ${site2_path}

the user visits site 1 via the 'Editor' button on the manage sites screen


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
    [Return]                            ${name}  ${host}  ${path}


Select from dropdown by number
	[Arguments]  ${dropdown}  ${dropdown_list}  ${number}
	Click Element                       ${dropdown}
    Wait Until Element Is Visible       ${dropdown_list}
    Click Element                       ${dropdown_list} ul.el-select-dropdown__list li:nth-of-type(${number})

