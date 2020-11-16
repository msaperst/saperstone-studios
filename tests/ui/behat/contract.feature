Feature: Contract
  As a user
  I want to be able to view and sign my contract
  So that I can employ saperstone studios to do my bidding

  Background:
    Given I am on the "contract.php?c=8e07fb32bf072e1825df8290a7bcdc57" page

  Scenario: Unable to sign contract without name
    Then the submit contact button is disabled

  Scenario: Unable to sign contract without address
  Scenario: Unable to sign contract without phone
  Scenario: Unable to sign contract without email
  Scenario: Unable to sign contract with bad email
  Scenario: Unable to sign without initials
  Scenario: Unable to sign without signature
  Scenario: Able to sign contract