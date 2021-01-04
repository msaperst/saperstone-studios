Feature: Contract
  As a user
  I want to be able to view and sign my contract
  So that I can employ saperstone studios to do my bidding

  Background:
    Given contract 99999 exists
    Given I am on the "contract.php?c=8e07fb32bf072e1825df8290a7bcdc57" page

  Scenario: Unable to sign contract without name
    Then the submit contract button is disabled

  Scenario: Unable to sign contract without address
    When I provide "Max" for the contract "name-signature"
    Then the submit contract button is disabled

  Scenario: Unable to sign contract without phone
    When I provide "Max" for the contract "name-signature"
    And I provide "123 Sesame Street" for the contract "address"
    Then the submit contract button is disabled

  Scenario: Unable to sign contract without email
    When I provide "Max" for the contract "name-signature"
    And I provide "123 Sesame Street" for the contract "address"
    And I provide "1234567890" for the contract "number"
    Then the submit contract button is disabled

  Scenario: Unable to sign without initials
    When I provide "Max" for the contract "name-signature"
    And I provide "123 Sesame Street" for the contract "address"
    And I provide "1234567890" for the contract "number"
    And I provide "msaperst+sstest@gmail.com" for the contract "email"
    Then the submit contract button is disabled

  Scenario: Unable to sign without signature
    When I provide "Max" for the contract "name-signature"
    And I provide "123 Sesame Street" for the contract "address"
    And I provide "1234567890" for the contract "number"
    And I provide "msaperst+sstest@gmail.com" for the contract "email"
    And I initial the contract
    Then the submit contract button is disabled

  Scenario: Successfully submit contract
    When I provide "Max" for the contract "name-signature"
    And I provide "123 Sesame Street" for the contract "address"
    And I provide "1234567890" for the contract "number"
    And I provide "msaperst+sstest@gmail.com" for the contract "email"
    And I initial the contract
    And I sign the contract
    And I submit the contract
    Then the submit contract button is disabled
    And the submit contract button is not present
    And I see a success message indicating my contract will be emailed to me
    And I see the signed contract displayed
    And I the signed contract exists for 99999
    And contract 99999 was emailed to me
    And a copy of contract 99999 was emailed to the admin

  Scenario: Unable to sign contract with bad email
    When I provide "Max" for the contract "name-signature"
    And I provide "123 Sesame Street" for the contract "address"
    And I provide "1234567890" for the contract "number"
    And I provide "email" for the contract "email"
    And I initial the contract
    And I sign the contract
    And I submit the contract
    Then I see an error message indicating an invalid email
