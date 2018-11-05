*** Settings ***
Variables    ../config.py

*** Variables ***

${AccountButton} =  css=.user-account-button
${username} =  ${SITE_EDITOR_USER}

${AddSiteButton} =  id=add-site
${AddSiteDialogue} =  css=.el-dialog__wrapper
${AddSiteFormName} =  id=input-site-name
${AddSiteFormTemplate} =  id=input-site-template
${AddSiteFormTemplateList} =  css=.input-site-template
${AddSiteFormHost} =  id=input-site-host
${AddSiteFormPath} =  id=input-site-path
${AddSiteFormButton} =  id=input-add-site-button
${TopBarBackButton} =  css=.top-bar-backbutton
${SitePicker} =  css=.site-pick
${SitePickerList} =  css=.site-picker
${LoadingSpinner} =  css=.el-loading-spinner


# MENU

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