@login
Feature: System Authentication
  As a user of the website
  I want to authenticate securely
  So that I can access my account and saved content

  Scenario: Enabled user can log in
    Given an enabled user account exists
    When I log in to the site
    Then I see my user name displayed

  Scenario: Disabled user cannot log in
    Given a disabled user account exists
    When I log in to the site
    Then I see an error message indicating my account has been disabled

  Scenario: Invalid credentials are rejected
    When I log in to the site
    Then I see an error message indicating my credentials aren't valid

  Scenario: Retrying login replaces the previous error
    When I log in to the site
    Then I see an error message indicating my credentials aren't valid
    When I resubmit invalid login credentials
    Then I see one login error message

  Scenario Outline: Login with incomplete credentials
    When I log in to the site using credentials "<username>" "<password>"
    Then I see an error message indicating all fields need to be filled in
    Examples:
      | username | password |
      |          |          |
      |          | password |
      | username |          |

  Scenario: 'Remember Me' is available before cookie preferences are selected
    Given I haven't reviewed the cookie policy
    When I try to login to the site
    Then I see the logon option to remember me

  Scenario: 'Remember Me' is unavailable when preference cookies are rejected
    Given I have rejected preference cookies
    When I try to login to the site
    Then I don't see the logon option to remember me

  Scenario: 'Remember Me' is hidden immediately when preference cookies are rejected
    Given I have accepted preference cookies
    When I reject preference cookies without reloading
    And I try to login to the site
    Then I don't see the logon option to remember me

  Scenario: Remember Me creates persistent login credentials
    Given an enabled user account exists
    When I stay logged in to the site
    Then I see my user name displayed
    And I see a cookie with my credentials

  Scenario: Remember Me restores the login session
    Given an enabled user account exists
    And I am logged in with saved credentials
    Then I see my user name displayed

  Scenario: User can log out
    Given an enabled user account exists
    And I am logged in with saved credentials
    When I logout
    Then I don't see my user name displayed
    And I don't see a cookie with my credentials

  Scenario: Logout keeps the user on public pages
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the "portrait/" page
    When I logout
    Then I am taken to the "portrait/" page

  Scenario: Logout returns the user home from protected pages
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the "user" page
    When I logout
    Then I am taken to the "" page

  Scenario: Password reset request provides reset credentials
    Given an enabled user account exists
    When I request a reset key
    Then I can enter in new credentials
    And I receive an email with my reset key

  Scenario: Existing reset key opens the password reset form
    Given an enabled user account exists
    When I have a reset key
    Then I can enter in new credentials

  Scenario: Remember Me is unavailable during reset when preference cookies are rejected
    Given I have rejected preference cookies
    And an enabled user account exists
    When I have a reset key
    Then I see that there is no reset option to remember me

  Scenario: Password reset requires an email address
    Given an enabled user account exists
    When I submit email "" for reset
    Then I see an error message indicating all fields need to be filled in

  Scenario: Password reset rejects an invalid email address
    Given an enabled user account exists
    When I submit email "nonaddress" for reset
    Then I see an error message indicating invalid field values

  Scenario: User can reset credentials with a valid reset key
    Given an enabled user account exists
    When I request a reset key
    And I submit reset credentials
    Then I see my user name displayed

  Scenario: Reset password persists for future logins
    Given an enabled user account exists
    When I request a reset key
    And I reset my password to "password1" using the emailed key
    And I logout
    And I log in to the site using credentials "testUser" "password1"
    Then I see my user name displayed

  Scenario Outline: Incomplete reset credentials are rejected
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

  Scenario: Password reset rejects mismatched passwords
    Given an enabled user account exists
    When I have a reset key
    And I submit "e@12.co" "123456" "password" "password1" reset credentials
    Then I see an error message indicating passwords do not match

  Scenario Outline: Invalid reset key credentials are rejected
    Given an enabled user account exists
    When I have a reset key
    And I submit "<email>" "<code>" "<password>" "<confirm>" reset credentials
    Then I see an error message indicating my credentials aren't valid
    Examples:
      | email              | code   | password | confirm  |
      | e@12.co            | 123456 | password | password |
      | msaperst@gmail.com | 123456 | password | password |
