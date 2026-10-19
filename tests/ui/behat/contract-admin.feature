@contract-admin
Feature: Contract Administration
  As an administrator
  I want to create and manage contracts
  So that client contracts can be prepared through the website

  Background:
    Given I am logged in with admin credentials
    And a contract administration unsigned contract exists
    And a contract administration signed contract exists
    And I am on the "user/contracts.php" page

  Scenario: Admin can update an unsigned contract
    When I update the administered contract name to "Updated Contract Client", session to "Updated Session", and date to "2030-01-02"
    Then I see the updated administered contract in the contracts table

  Scenario: Contract updates persist after reload
    When I update the administered contract name to "Updated Contract Client", session to "Updated Session", and date to "2030-01-02"
    And I reload the page
    Then I see the updated administered contract in the contracts table

  Scenario: Admin can create a commercial contract
    When I create a commercial contract for "Created Contract Client" with session "Created Session" and date "2031-02-03"
    Then I see the created contract in the contracts table

  Scenario: Created contract persists after reload
    When I create a commercial contract for "Created Contract Client" with session "Created Session" and date "2031-02-03"
    And I reload the page
    Then I see the created contract in the contracts table

  Scenario: Contract creation requires a client name
    When I start creating a commercial contract
    And I provide "Missing Name Session" for the administered contract session
    And I try to save the administered contract
    Then I see a contract administration error indicating a client name is required

  Scenario: Admin can view an unsigned contract
    When I view the administered unsigned contract
    Then I see the administered contract on the public contract page

  Scenario: Signed contracts expose signed-only actions
    Then I see signed-only actions for the administered signed contract
