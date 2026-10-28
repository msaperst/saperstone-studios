@collections @albums
Feature: Organizational parent and child albums
  As a client I can navigate collections without revealing unauthorized siblings
  As an uploader I can organize my unshared albums without granting client access
  As an admin I can manage shared collections without deleting child photos

  Scenario: Parent code opens the collection and its children
    Given an enabled admin user account exists
    And I am logged in with saved credentials
    And album 99999 exists with 16 images
    And a collection named "Mitzvah" exists with code "EVENT99999"
    And album 99999 belongs to that collection
    When I logout
    And I search for album "EVENT99999"
    Then I see the "Mitzvah" collection landing page
    And I see "sample-album" as a child gallery
    When I open the "sample-album" child gallery
    Then the "Mitzvah" breadcrumb links back to the collection
    When I follow the "Mitzvah" breadcrumb
    Then I see the "Mitzvah" collection landing page

  Scenario: Child code does not reveal inaccessible parent
    Given an enabled admin user account exists
    And I am logged in with saved credentials
    And album 99999 exists with 16 images
    And a collection named "Mitzvah" exists with code "EVENT99999"
    And album 99999 belongs to that collection
    And album 99999 has code "CHILD99999"
    When I logout
    And I search for album "CHILD99999"
    Then I can open album 99999
    And I do not see a parent collection breadcrumb

  Scenario: Administrator can reorder galleries using the existing management screen
    Given an enabled admin user account exists
    And I am logged in with saved credentials
    And a collection named "Mitzvah" exists
    And album 99999 exists with 16 images
    And album 99998 exists with 0 images
    And album 99999 belongs to that collection
    And album 99998 belongs to that collection
    When I am on the "user/" page
    And I expand the "Mitzvah" collection
    And I drag gallery 99998 above gallery 99999
    And I reload the page
    Then gallery 99998 is listed before gallery 99999

  Scenario: Parent deletion retains every child as an independent gallery
    Given an enabled admin user account exists
    And I am logged in with saved credentials
    And a collection named "Mitzvah" exists
    And album 99999 exists with 16 images
    And album 99999 belongs to that collection
    When I am on the "user/" page
    And I delete the "Mitzvah" collection
    Then the confirmation says child albums will be preserved
    When I confirm deleting the "Mitzvah" collection
    Then album 99999 is still listed as a standalone album
