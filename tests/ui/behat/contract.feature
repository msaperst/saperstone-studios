@contract
Feature: Contract
  As a user
  I want to be able to view and sign my contract
  So that I can authorize Saperstone Studios to provide photography services

  Background:
    Given contract 99999 exists
    And I am on the "contract.php?c=8e07fb32bf072e1825df8290a7bcdc57" page

  Scenario: Name is required to sign the contract
    Then the submit contract button is disabled

  Scenario: Address is required to sign the contract
    When I provide "Max" for the contract "name-signature"
    Then the submit contract button is disabled

  Scenario: Phone number is required to sign the contract
    When I provide "Max" for the contract "name-signature"
    And I provide "123 Sesame Street" for the contract "address"
    Then the submit contract button is disabled

  Scenario: Email is required to sign the contract
    When I provide "Max" for the contract "name-signature"
    And I provide "123 Sesame Street" for the contract "address"
    And I provide "1234567890" for the contract "number"
    Then the submit contract button is disabled

  Scenario: Initials are required to sign the contract
    When I provide "Max" for the contract "name-signature"
    And I provide "123 Sesame Street" for the contract "address"
    And I provide "1234567890" for the contract "number"
    And I provide "msaperst+sstest@gmail.com" for the contract "email"
    Then the submit contract button is disabled

  Scenario: Signature is required to sign the contract
    When I provide "Max" for the contract "name-signature"
    And I provide "123 Sesame Street" for the contract "address"
    And I provide "1234567890" for the contract "number"
    And I provide "msaperst+sstest@gmail.com" for the contract "email"
    And I add my initials to the contract
    Then the submit contract button is disabled

  Scenario: Completed contract can be signed and submitted
    When I provide "Max" for the contract "name-signature"
    And I provide "123 Sesame Street" for the contract "address"
    And I provide "1234567890" for the contract "number"
    And I provide "msaperst+sstest@gmail.com" for the contract "email"
    And I add my initials to the contract
    And I sign the contract
    And I submit the contract
    Then the submit contract button is disabled
    And the submit contract button is not present
    And I see a success message indicating my contract will be emailed to me
    And I see the signed contract displayed
    And contract 99999 was emailed to me
    And a copy of contract 99999 was emailed to the admin

  Scenario: Invalid email is rejected when signing the contract
    When I provide "Max" for the contract "name-signature"
    And I provide "123 Sesame Street" for the contract "address"
    And I provide "1234567890" for the contract "number"
    And I provide "email" for the contract "email"
    And I add my initials to the contract
    And I sign the contract
    And I submit the contract
    Then I see an error message indicating an invalid email
