Feature: System Administration
  As a user of the website
  I want to be able to manage my own user
  So that I can update my information as needed

  Scenario: Unable to 'Remember Me' on registration
    Given I have cookies disabled
    And I am on the registration page
    Then I see that there is no register option to remember me