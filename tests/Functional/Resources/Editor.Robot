*** Settings ***
Library  SeleniumLibrary
Library  FakerLibrary
Library  String
Library  Collections

Resource  Variables.Robot

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

The user visits site one via the 'Editor' button on the manage sites screen
	the user uses the 'Editor' button to edit site  ${site1}

the user takes note of the list of pages in site one
	${pages}=  get list of pages
	Set To Dictionary  ${site1}  pages= ${pages}


Select from dropdown by number
	[Arguments]  ${dropdown}  ${dropdown_list}  ${number}
	Click Element                       ${dropdown}
    Wait Until Element Is Visible       ${dropdown_list}
    Click Element                       ${dropdown_list} ul.el-select-dropdown__list li:nth-of-type(${number})

The user visits site two via the 'Editor' button on the manage sites screen
	the user uses the 'Editor' button to edit site  ${site2}

The list of pages in site two should be different from the list of pages in site one
	${pages}=  get list of pages
	Set To Dictionary                   ${site2}  pages= ${pages}
	Should Not Be Equal                 ${site2.pages}  ${site1.pages}

The user goes directly to the manage sites screen
	Go To                               ${START_URL}
	Wait Until Element Is Visible       ${LoadingSpinner}
	Wait Until Element Is Not Visible   ${LoadingSpinner}



