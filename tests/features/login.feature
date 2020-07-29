Feature: System Authentication
  As a user of the website
  I want to have a login
  So that I can access my stored data

  Scenario: Login as a good user
    Given an enabled user account exists
    When I log in to the site using credentials
    Then I see my user name displayed

  Scenario: Login as a disabled user
    Given a disabled user account exists
    When I log in to the site using credentials
    Then I see an error message indicating my account has been disabled

  Scenario: Login as a bad user
    When I log in to the site using credentials
    Then I see an error message indicating my credentials aren't valid

  Scenario Outline: Login with incomplete credentials
    When I log in to the site using credentials "<username>" "<password>"
    Then I see an error message indicating all fields need to be filled in
    Examples:
      | username | password |
      |          |          |
      |          | password |
      | username |          |

  Scenario: Able to 'Remember Me'
    Given an enabled user account exists
    When I remember my credentials
    And I log in to the site using credentials
    Then I see my user name displayed
    And I see a cookie with my credentials

  Scenario: Able to 'Remember Me'
    Given an enabled user account exists
    And I am logged in with saved credentials
    Then I see my user name displayed

  Scenario: Logout
    Given an enabled user account exists
    And I am logged in with saved credentials
    When I logout
    Then I don't my user name displayed
    And I don't see a cookie with my credentials