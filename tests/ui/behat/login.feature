Feature: System Authentication
  As a user of the website
  I want to have a login
  So that I can access my stored data

  Scenario: Login as a good user
    Given an enabled user account exists
    When I log in to the site
    And I see my user name displayed

  Scenario: Login as a disabled user
    Given a disabled user account exists
    When I log in to the site
    Then I see an error message indicating my account has been disabled

  Scenario: Login as a bad user
    When I log in to the site
    Then I see an error message indicating my credentials aren't valid

  Scenario Outline: Login with incomplete credentials
    When I log in to the site using credentials "<username>" "<password>"
    Then I see an error message indicating all fields need to be filled in
    Examples:
      | username | password |
      |          |          |
      |          | password |
      | username |          |

  Scenario: Unable to 'Remember Me'
    Given I have cookies disabled
    When I try to login to the site
    Then I see that there is no logon option to remember me

  Scenario: Able to 'Remember Me'
    Given an enabled user account exists
    When I stay logged in to the site
    Then I see my user name displayed
    And I see a cookie with my credentials

  Scenario: Able to stay logged in
    Given an enabled user account exists
    And I am logged in with saved credentials
    Then I see my user name displayed

  Scenario: Able to logout
    Given an enabled user account exists
    And I am logged in with saved credentials
    When I logout
    Then I don't see my user name displayed
    And I don't see a cookie with my credentials

  Scenario: Logout keeps you on unauth pages
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the "portrait/" page
    When I logout
    Then I am taken to the "portrait/" page

  Scenario: Logout returns to homepage on auth pages
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the "user" page
    When I logout
    Then I am taken to the "" page

  Scenario: Able to enter reset credentials
    Given an enabled user account exists
    When I request a reset key
    Then I can enter in new credentials
    And I receive an email with my reset key

  Scenario: Able to enter old reset credentials
    Given an enabled user account exists
    When I have a reset key
    Then I can enter in new credentials

  Scenario: Unable to 'Remember Me' on reset
    Given I have cookies disabled
    And an enabled user account exists
    When I have a reset key
    Then I see that there is no reset option to remember me

  Scenario: Blank email for password reset
    Given an enabled user account exists
    When I submit email "" for reset
    Then I see an error message indicating all fields need to be filled in

  Scenario: Bad email for password reset
    Given an enabled user account exists
    When I submit email "nonaddress" for reset
    Then I see an error message indicating invalid field values

  Scenario: Able to reset credentials
    Given an enabled user account exists
    When I request a reset key
    And I submit reset credentials
    Then I see my user name displayed

  Scenario Outline: Bad reset credentials
    Given an enabled user account exists
    When I have a reset key
    And I submit "<email>" "<code>" "<password>" "<confirm>" reset credentials
    Then I see an error message indicating all fields need to be filled in
    Examples:
      | email              | code   | password | confirm |
      |                    |        |          |         |
      | msaperst@gmail.com |        |          |         |
      | msaperst@gmail.com | 123456 |          |         |
      | msaperst@gmail.com | 123456 | password |         |

  Scenario: Bad reset credentials password mismatch
    Given an enabled user account exists
    When I have a reset key
    And I submit "e@12.co" "123456" "password" "password1" reset credentials
    Then I see an error message indicating passwords do not match

  Scenario Outline: Bad reset credentials no resetKey
    Given an enabled user account exists
    When I have a reset key
    And I submit "<email>" "<code>" "<password>" "<confirm>" reset credentials
    Then I see an error message indicating my credentials aren't valid
    Examples:
      | email              | code   | password | confirm  |
      | e@12.co            | 123456 | password | password |
      | msaperst@gmail.com | 123456 | password | password |
