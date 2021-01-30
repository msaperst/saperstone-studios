@contact
Feature: Contact
  As a user
  I want to be able to easily contact SS
  So that I can hire her

  Background:
    Given I am on the "contact.php" page

  Scenario: Unable to submit message without name
    When I submit the contact form
    Then I see an error indicating contact name is required

  Scenario: Unable to submit message without number
    When I provide "Max" for the contact "name"
    And I submit the contact form
    Then I see an error indicating contact number is required

  Scenario: Unable to submit message without email
    When I provide "Max" for the contact "name"
    And I provide "1234567890" for the contact "phone"
    And I submit the contact form
    Then I see an error indicating contact email is required

  Scenario: Unable to submit message with invalid email
    When I provide "Max" for the contact "name"
    And I provide "1234567890" for the contact "phone"
    And I provide "m@m.m" for the contact "email"
    And I submit the contact form
    Then I see an error indicating contact email is invalid

  Scenario: Unable to submit message without message
    When I provide "Max" for the contact "name"
    And I provide "1234567890" for the contact "phone"
    And I provide "msaperst+sstest@gmail.com" for the contact "email"
    And I submit the contact form
    Then I see an error indicating contact message is required

  Scenario: Successfully submit message
    When I provide "Max" for the contact "name"
    And I provide "1234567890" for the contact "phone"
    And I provide "msaperst+sstest@gmail.com" for the contact "email"
    And I provide "This is a test message, feel free to ignore this" for the contact "message"
    And I submit the contact form
    Then I see a warning message indicating my message is being sent
    And the submit contact button is disabled
    And I see a success message indicating my message was sent
    And I see a contact email sent to the user
    And I see a contact email send to the admin with:
    | name | phone | email | message |
    | Max  | 1234567890 | msaperst+sstest@gmail.com | This is a test message, feel free to ignore this |