@user-admin
Feature: User Administration
  As an administrator
  I want to manage site users
  So that user accounts and access can be maintained through the website

  Background:
    Given I am logged in with admin credentials
    And a user administration test user exists
    And a user administration test album exists
    And I am on the "user/users.php" page

  Scenario: Admin can update user details
    When I update the administered user to "Updated User" with email "updated-user@example.org", role "uploader", and inactive status
    Then I see the updated administered user in the users table

  Scenario: User detail updates persist after reload
    When I update the administered user to "Updated User" with email "updated-user@example.org", role "uploader", and inactive status
    And I reload the page
    Then I see the updated administered user in the users table

  Scenario: Admin can create a user
    When I create an active downloader user "behat-created-user" named "Created User" with email "behat-created-user@example.org"
    Then I see the created user's edit dialog
    And I see the created user in the users table

  Scenario: Created user persists after reload
    When I create an active downloader user "behat-created-user" named "Created User" with email "behat-created-user@example.org"
    And I reload the page
    Then I see the created user in the users table

  Scenario: Duplicate user creation shows a useful error
    When I try to create an active downloader user "behat-admin-user" named "Duplicate User" with email "duplicate-user@example.org"
    Then I see a user administration error indicating the username already exists

  Scenario: Admin can delete a user
    When I delete the administered user
    Then I do not see the administered user in the users table

  Scenario: Deleted user remains deleted after reload
    When I delete the administered user
    And I reload the page
    Then I do not see the administered user in the users table

  Scenario: Admin can add an album to a user
    When I open album access for the administered user
    And I add the user administration test album
    Then I see the test album selected for the administered user

  Scenario: Added user album access persists
    When I open album access for the administered user
    And I add the user administration test album
    And I save administered user album access
    And I reload the page
    And I open album access for the administered user
    Then I see the test album selected for the administered user

  Scenario: Admin can remove an album from a user
    Given the administered user has the user administration test album
    When I open album access for the administered user
    And I remove the user administration test album
    Then I do not see the test album selected for the administered user

  Scenario: Removed user album access persists
    Given the administered user has the user administration test album
    When I open album access for the administered user
    And I remove the user administration test album
    And I save administered user album access
    And I reload the page
    And I open album access for the administered user
    Then I do not see the test album selected for the administered user

  Scenario: Admin can change a user's password
    When I change the administered user's password to "new-password"
    And I close the administered user dialog
    And I log out of the admin session
    And I log in as the administered user with password "new-password"
    Then I see the administered user name displayed

  Scenario: Admin can view a user's activity
    Given the administered user has a test activity log
    When I view activity for the administered user
    Then I see the administered user's test activity

  Scenario: Admin can view the site as another user
    When I view the site as the administered user
    Then I am viewing the site as the administered user
