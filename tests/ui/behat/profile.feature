@profile
Feature: Profile Administration
  As a user of the website
  I want to manage my account
  So that I can update my information as needed

  Scenario: Remember Me is unavailable during registration when preference cookies are rejected
    Given I have rejected preference cookies
    And I am on the registration page
    Then I see that there is no register option to remember me

  Scenario Outline: Invalid username registration input
    Given I am on the registration page
    When I try to register a username of "<username>"
    Then I see an error indicating a bad username
    And the register button is disabled
    Examples:
      | username |
      |          |
      | 1234     |
      | msap     |
      | hgnk*&   |

  Scenario: Valid username registration input
    Given I am on the registration page
    When I try to register a username of "msaperst"
    Then I see a success icon indicating a good username

  Scenario: Invalid username registration
    Given I am on the registration page
    When I register a user with "msaperst", "p", "p", "Max", "Saperstone", "msaperst+sstest@gmail.com"
    Then I see an error message indicating username already exists

  Scenario: Invalid password registration input
    Given I am on the registration page
    When I try to register a password of ""
    Then I see an error indicating a bad password
    And the register button is disabled

  Scenario: Invalid password confirm registration input
    Given I am on the registration page
    When I try to register a password confirm of "1"
    Then I see an error indicating a bad confirm password
    And the register button is disabled

  Scenario: Valid password registration input
    Given I am on the registration page
    When I try to register a password of "123"
    And I try to register a password confirm of "123"
    Then I see a success icon indicating a good password
    And I see a success icon indicating a good confirm password

  Scenario: Invalid first name registration input
    Given I am on the registration page
    When I try to register a first name of ""
    Then I see an error indicating a bad first name
    And the register button is disabled

  Scenario: Valid first name registration input
    Given I am on the registration page
    When I try to register a first name of "Max"
    Then I see a success icon indicating a good first name

  Scenario: Invalid last name registration input
    Given I am on the registration page
    When I try to register a last name of ""
    Then I see an error indicating a bad last name
    And the register button is disabled

  Scenario: Valid last name registration input
    Given I am on the registration page
    When I try to register a last name of "Saperstone"
    Then I see a success icon indicating a good last name

  Scenario Outline: Invalid email registration input
    Given I am on the registration page
    When I try to register an email of "<email>"
    Then I see an error indicating a bad email
    And the register button is disabled
    Examples:
      | email |
      |       |
      | m     |
      | m@m   |
      | m@m.m |

  Scenario: Valid email registration input
    Given I am on the registration page
    When I try to register an email of "msaperst+sstest@gmail.com"
    Then I see a success icon indicating a good email

  Scenario: Invalid email registration
    Given I am on the registration page
    When I register a user with "msap_rst", "p", "p", "Max", "Saperstone", "msaperst@gmail.com"
    Then I see an error message indicating email already exists

  Scenario: User can register an account
    Given I am on the registration page
    When I register my user
    Then I see my user name displayed
    And I am taken to the "user/profile.php" page
    And I receive a welcome email

  Scenario: Registered account can log in again
    Given I am on the registration page
    When I register my user
    And I logout
    And I log in to the site using credentials "testUser" "12345"
    Then I see my user name displayed

  Scenario: Username cannot be changed from the profile
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the profile page
    Then the username field is disabled

  Scenario: Updating credentials doesn't require password
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the profile page
    Then I see a no icon for current password
    And I see a no icon for password
    And I see a no icon for confirm password

  Scenario: Invalid password update input
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the profile page
    When I try to update to a password of "1"
    Then I see an error indicating current password is required
    And the update button is disabled

  Scenario: Invalid password confirm update input
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the profile page
    When I try to update to a password confirm of "1"
    Then I see an error indicating a bad confirm password
    And the update button is disabled

  Scenario: Valid password update input
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the profile page
    When I try to set my password of "123"
    And I try to update to a password of "123"
    And I try to update to a password confirm of "123"
    Then I see a success icon indicating a good current password
    And I see a success icon indicating a good password
    And I see a success icon indicating a good confirm password

  Scenario: Invalid first name update input
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the profile page
    When I try to update to a first name of ""
    Then I see an error indicating a bad first name
    And the update button is disabled

  Scenario: Valid first name update input
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the profile page
    When I try to update to a first name of "Max"
    Then I see a success icon indicating a good first name

  Scenario: Invalid last name update input
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the profile page
    When I try to update to a last name of ""
    Then I see an error indicating a bad last name
    And the update button is disabled

  Scenario: Valid last name update input
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the profile page
    When I try to update to a last name of "Saperstone"
    Then I see a success icon indicating a good last name

  Scenario Outline: Invalid email update input
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the profile page
    When I try to update to an email of "<email>"
    Then I see an error indicating a bad email
    And the update button is disabled
    Examples:
      | email |
      |       |
      | m     |
      | m@m   |
      | m@m.m |

  Scenario: Valid email update input
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the profile page
    When I try to update to an email of "msaperst+sstest@gmail.com"
    Then I see a success icon indicating a good email

  Scenario: Invalid email update
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the profile page
    When I try to update to an email of "msaperst@gmail.com"
    And I update my user
    Then I see an error message indicating email already exists

  Scenario: Invalid password update
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the profile page
    When I try to set my password of "1234"
    And I try to update to a password of "password1"
    And I try to update to a password confirm of "password1"
    And I update my user
    Then I see an error message indicating wrong password provided

  Scenario: User can save an unchanged profile
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the profile page
    When I update my user
    Then I see a success message indicating my user was updated

  Scenario: User can update profile information
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the profile page
    When I try to update to a first name of "Max"
    And I try to update to a last name of "Saperstone"
    And I try to update to an email of "msaperst+sstest2@gmail.com"
    And I update my user
    Then I see a success message indicating my user was updated

  Scenario: Profile information updates persist after reload
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the profile page
    When I try to update to a first name of "Max"
    And I try to update to a last name of "Saperstone"
    And I try to update to an email of "msaperst+sstest2@gmail.com"
    And I update my user
    And I reload the page
    Then I see my updated profile information

  Scenario: User can update profile information and password
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the profile page
    When I try to set my password of "12345"
    And I try to update to a password of "password1"
    And I try to update to a password confirm of "password1"
    And I try to update to a first name of "Max"
    And I try to update to a last name of "Saperstone"
    And I try to update to an email of "msaperst+sstest2@gmail.com"
    And I update my user
    Then I see a success message indicating my user was updated

  Scenario: Updated password persists for future logins
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the profile page
    When I try to set my password of "12345"
    And I try to update to a password of "password1"
    And I try to update to a password confirm of "password1"
    And I update my user
    And I logout
    And I log in to the site using credentials "testUser" "password1"
    Then I see my user name displayed
