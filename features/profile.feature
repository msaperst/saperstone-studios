Feature: System Administration
  As a user of the website
  I want to be able to manage my own user
  So that I can update my information as needed

  Scenario: Unable to 'Remember Me' on registration
    Given I have cookies disabled
    And I am on the registration page
    Then I see that there is no register option to remember me

  Scenario Outline: Invalid username input
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

  Scenario: Valid username input
    Given I am on the registration page
    When I try to register a username of "msaperst"
    Then I see a success icon indicating a good username

  Scenario: Invalid username
    Given I am on the registration page
    When I register a user with "msaperst", "p", "p", "Max", "Saperstone", "msaperst+sstest@gmail.com"
    Then I see an error message indicating username already exists

  Scenario: Invalid password input
    Given I am on the registration page
    When I try to register a password of ""
    Then I see an error indicating a bad password
    And the register button is disabled

  Scenario: Invalid password confirm input
    Given I am on the registration page
    When I try to register a password confirm of "1"
    Then I see an error indicating a bad confirm password
    And the register button is disabled

  Scenario: Valid password input
    Given I am on the registration page
    When I try to register a password of "123"
    When I try to register a password confirm of "123"
    Then I see a success icon indicating a good password
    Then I see a success icon indicating a good confirm password

  Scenario: Invalid first name input
    Given I am on the registration page
    When I try to register a first name of ""
    Then I see an error indicating a bad first name
    And the register button is disabled

  Scenario: Valid first name input
    Given I am on the registration page
    When I try to register a first name of "Max"
    Then I see a success icon indicating a good first name

  Scenario: Invalid last name input
    Given I am on the registration page
    When I try to register a last name of ""
    Then I see an error indicating a bad last name
    And the register button is disabled

  Scenario: Valid last name input
    Given I am on the registration page
    When I try to register a last name of "Saperstone"
    Then I see a success icon indicating a good last name

  Scenario Outline: Invalid email input
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

  Scenario: Valid email input
    Given I am on the registration page
    When I try to register an email of "msaperst+sstest@gmail.com"
    Then I see a success icon indicating a good email

  Scenario: Invalid email
    Given I am on the registration page
    When I register a user with "msap_rst", "p", "p", "Max", "Saperstone", "msaperst@gmail.com"
    Then I see an error message indicating email already exists

  Scenario: Able to register user
    Given I am on the registration page
    When I register my user
    Then I see my user name displayed
    Then I am taken to the "user/profile.php" page
