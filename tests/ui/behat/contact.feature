@contact
Feature: Contact
  As a user
  I want to contact Saperstone Studios
  So that I can inquire about photography services

  Background:
    Given I am on the "contact.php" page

  Scenario: Name is required to submit the contact form
    When I submit the contact form
    Then I see an error indicating contact name is required

  Scenario: Phone number is required to submit the contact form
    When I provide "Max" for the contact "name"
    And I submit the contact form
    Then I see an error indicating contact number is required

  Scenario: Email is required to submit the contact form
    When I provide "Max" for the contact "name"
    And I provide "1234567890" for the contact "phone"
    And I submit the contact form
    Then I see an error indicating contact email is required

  Scenario: A valid email is required to submit the contact form
    When I provide "Max" for the contact "name"
    And I provide "1234567890" for the contact "phone"
    And I provide "m@m.m" for the contact "email"
    And I submit the contact form
    Then I see an error indicating contact email is invalid

  Scenario: Message is required to submit the contact form
    When I provide "Max" for the contact "name"
    And I provide "1234567890" for the contact "phone"
    And I provide "msaperst+sstest@gmail.com" for the contact "email"
    And I submit the contact form
    Then I see an error indicating contact message is required

  Scenario: Contact messages submitted too quickly are silently rejected
    When I provide "Max" for the contact "name"
    And I provide "1234567890" for the contact "phone"
    And I provide "msaperst+sstest@gmail.com" for the contact "email"
    And I provide "This is a test message, feel free to ignore this" for the contact "message"
    And I submit the contact form too quickly
    Then no contact emails are sent

  Scenario: Valid contact message is submitted
    When I provide "Max" for the contact "name"
    And I provide "1234567890" for the contact "phone"
    And I provide "msaperst+sstest@gmail.com" for the contact "email"
    And I provide "This is a test message, feel free to ignore this" for the contact "message"
    And I submit the contact form
    Then I see a success message indicating my message was sent
    And I see a contact email sent to the user
    And I see a contact email sent to the admin with:
    | name | phone | email | message |
    | Max  | 1234567890 | msaperst+sstest@gmail.com | This is a test message, feel free to ignore this |