*** Settings ***
Library  SeleniumLibrary


*** Variables ***

${SideMenu_1} =  css=#site-page-link-0 .add-to-menu-button
${SideMenu_2} =  css=#site-page-link-1 .add-to-menu-button
${SideMenu_3} =  css=#site-page-link-2 .add-to-menu-button
${SideMenu_4} =  css=#site-page-link-3 .add-to-menu-button
${SideMenu_5} =  css=#site-page-link-4 .add-to-menu-button
${SideMenu_6} =  css=#site-page-link-5 .add-to-menu-button
${SideMenu_7} =  css=#site-page-link-6 .add-to-menu-button

${DeleteMenu_1} =  css=#menu-editor-menu-item-0 .delete-menu-item-button
${Manual_AddLink} =  id=add-menu-item

${SaveMenu} =  id=save-menu
${SaveMenuSuccess} =  Successfully saved menu.
${SaveMenuValidationError} =  The menu saved, but there are some validation errors.

${PublishMenu} =  id=publish-menu
${PublishMenuSuccess} =  Successfully published menu.
${PublishMenuFailure} =  Menu not published

${PreviewMenu} =  id=preview-menu
${CheckPreviewWindow} =  This is a preview version of the site and is for internal use only. This page can be shared with your colleagues.
${SelectMenuWindow} =  url=${START_URL}/site/${DEFAULT_SITE_ID}/menu
${NoMenuItems} =  No menu items

${AccountButton} =  css=.user-account-button
${MenuButtonLeftHandNav} =  css=.admin-sidebar a[href$="/site/${DEFAULT_SITE_ID}/menu"]

${MenuItemLinkText_1} =  css=#menu-editor-menu-item-0 .link-text input
${MenuItemLinkURL_1} =  css=#menu-editor-menu-item-0 .link-url input
${MenuItem_1} =  id=menu-editor-menu-item-0

${PreviewFirstMenuItem} =  css=#navbar-menu > li > a
${NotificationClose} =  css=body > div.el-notification.right > div > div.el-notification__closeBtn.el-icon-close

*** Keywords ***

The user clicks on the menu button on the left hand navigation
    Click Element  ${MenuButtonLeftHandNav}

The user accesses the menu section of the site
    [Documentation]   Open Site and Edit Menu
    [Tags]  Test 1
    Click Link  css=a[href$="/site/${DEFAULT_SITE_ID}/menu"]
    Wait Until Page Contains Element  ${MenuButtonLeftHandNav}

The user should arrive on the menu manager for the site
    #URL should change to /site/site_id/menu
    Location Should Be  ${START_URL}/site/${DEFAULT_SITE_ID}/menu
    #There should be a sites pages section on the page
    Wait until Page contains  Site pages

There are no menu items
    Menu Teardown

Delete link
    [Documentation]  Delete menu link
    Click Element  ${DeleteMenu_1}
    # need to figure out how to check that item from top of list is gone
    Sleep  2s


Add side link
    [Documentation]   Add side link
    [Arguments]  ${SideMenuItemButton}
    Click Element  ${SideMenuItemButton}
    Capture Page Screenshot
    # need to figure out how to check for new item


Add 5 side links
    [Documentation]   Add 5 side links

    Click Element  ${SideMenu_1}
    Click Element  ${SideMenu_2}
    Click Element  ${SideMenu_3}
    Click Element  ${SideMenu_4}
    Click Element  ${SideMenu_5}

    # need to figure out how to check for new item

Add manual link
    [Documentation]  Add Manual Link
    Click Element   ${Manual_AddLink}
    # need to figure out how to check for new item
    sleep  1s

Save menu
    [Documentation]  Save Menu
    Click Element  ${SaveMenu}
    Wait Until Page Contains  ${SaveMenuSuccess}
    Wait Until Element is visible  ${NotificationClose}
    Click Element  ${NotificationClose}

The user saves the menu
    Save Menu

The user saves the menu despite validation errors
    Click Element  ${SaveMenu}

There should be a validation error when saving the menu
    Wait Until Page Contains  ${SaveMenuValidationError}
    Wait Until Element is visible  ${NotificationClose}
    Click Element  ${NotificationClose}


Preview menu
    [Documentation]  Preview Menu and then focus back on the menu editor
    Click Element  ${PreviewMenu}
    Wait Until Keyword Succeeds  30s  2s  Select Window  NEW 
    Wait Until Page Contains  ${CheckPreviewWindow}



Back to menu window
    Select Window  ${SelectMenuWindow}

Publish menu
    [Documentation]  Publish Menu
    Click Element  ${PublishMenu}
    Wait Until Page Contains  ${PublishMenuSuccess}

The user publishes the menu despite validation errors
    Click Element  ${PublishMenu}

There should be a validation error when publishing the menu
    Wait Until Page Contains  ${PublishMenuFailure}

No menu items
    [Documentation]  No Menu Items
    Page Should Contain  ${NoMenuItems}

Delete all menu items
    [Documentation]  Delete All Menu Items
    Run Keyword And Ignore Error  Click Element  ${DeleteMenu_1}
    Page Should Contain  ${NoMenuItems}

Menu teardown
    [Documentation]  If the top delete button exists click it until there are no items
    Wait Until Keyword Succeeds  10 sec  10 ms   Delete All Menu Items
    Save Menu

Menu setup
    [Documentation]  Clears menu of any existing items
    Wait Until Keyword Succeeds  10 sec  10 ms   Delete All Menu Items
    Save Menu

The user clicks on the ‘Add to menu’ button for the home page on the Site pages listing
    Add side link  ${SideMenu_1}
    ${site.home_page_name} =  Execute Javascript  return document.querySelector('.site-page-links__site-details span').textContent

The new 'Home' menu item should appear in the menu items list
    # Home in text field for link text
    Textfield Value Should Be  ${MenuItemLinkText_1}  ${site.home_page_name}
    #Item appears on the menu listing
    page should contain element  ${MenuItem_1}
    #The URL should be the site’s host and path
    #The URL is static and needs to be made dynamic
    Textfield Value Should Be  ${MenuItemLinkURL_1}  https://${site.url}/

The home menu item should appear in the preview of the page
    Preview Menu
    Page should contain link  ${site.home_page_name}
    Page should contain element  ${PreviewFirstMenuItem}


The user clicks on the ‘add link’ button
    Click Element   ${Manual_AddLink}

Fills in the link text and url fields with valid data
    Set test variable  ${LinkName}  Test
    Set test variable  ${LinkURL}  https://www.google.co.uk
    Input Text  ${MenuItemLinkText_1}  ${LinkName}
    Input Text  ${MenuItemLinkURL_1}  ${LinkURL}

The new manual menu item should appear in the preview of the page
    Preview Menu
    Page should contain link  ${LinkName}
    Page should contain link  ${LinkURL}
    Page should contain element  ${PreviewFirstMenuItem}
