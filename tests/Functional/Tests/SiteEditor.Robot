*** Settings ***
Documentation   This suite of tests checks the front office page, the team link and copy on the team section

Resource  ../Resources/Common.Robot
Resource  ../Resources/PO/SiteListings.Robot
Resource  ../Resources/PO/Menu.Robot
Resource  ../Resources/PO/SiteAdministration.Robot

Test Setup  Begin Web Test
Test Teardown  End Web test


*** Test Cases ***
User can access a site’s menu manager from the ‘Manage Sites’ listing
    Given the user accesses the menu section of the site
    Then the user should arrive on the menu manager for the site


User can access a site’s menu manager from the site’s administration area
    Given the user is in the site administration area
    When the user clicks on the menu button on the left hand navigation
    Then the user should arrive on the menu manager for the site

Menu items can be added via the Site Pages ‘Add to menu’ buttons
    [Tags]  wip
    Given the user accesses the menu section of the site
    And there are no menu items
    When the user clicks on the ‘Add to menu’ button for the home page on the Site pages listing
    And the user saves the menu
    Then the new 'Home' menu item should appear in the menu items list
    And the home menu item should appear in the preview of the page


User can add a manual menu item to the menu on a site’s menu manager
    Given the user accesses the menu section of the site
    And there are no menu items
    When the user clicks on the ‘add link’ button
    And fills in the link text and url fields with valid data
    And the user saves the menu
    Then the new manual menu item should appear in the preview of the page

User receives validation error when saving a menu with a invalid menu item
    Given the user accesses the menu section of the site
    And there are no menu items
    When the user clicks on the ‘add link’ button
    And the user saves the menu despite validation errors
    Then there should be a validation error when saving the menu

User cannot publish when the menu has validation errors
    Given the user accesses the menu section of the site
    And there are no menu items
    When the user clicks on the ‘add link’ button
    And the user publishes the menu despite validation errors
    Then there should be a validation error when publishing the menu



#Edit Menu
    #[Documentation]   Open Site and Edit Menu
    #[Tags]  Test 1
    #SiteListings.Access Menu
    #Menu.Add side link 1

    #Menu.Delete link

    #Menu.Add Manual link

    #Menu.Save Menu

    #Menu.Publish Menu

    #Menu.Preview Menu

    #Menu.Menu Teardown

    #Menu.Total Menu Test



    #sleep  2s

#Total Menu Test
    #[Documentation]  Setup Add various side links, save, publish, teardown
    #Menu.Menu Setup
    #Menu.Add 5 side links
    #Menu.Save Menu
    #Menu.Publish Menu
    #Menu.Preview Menu
    #Menu.Back to Menu Window
    #Menu.Menu Teardown

