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
    And I see "Album 99999" as a child gallery
    When I open the "Album 99999" child gallery
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

  Scenario: Expanded child rows retain the album thumbnail generation status
    Given an enabled admin user account exists
    And I am logged in with saved credentials
    And a collection named "Mitzvah" exists
    And album 99999 exists with 16 images
    And album 99998 exists with 0 images
    And album 99999 belongs to that collection
    And album 99998 belongs to that collection
    When I am on the "user/" page
    And I expand the "Mitzvah" collection
    Then child album 99999 shows "Missing" thumbnail status
    And child album 99998 shows "N/A" thumbnail status

  Scenario: Album editing actions stay in one row with the new cover picker
    Given an enabled admin user account exists
    And I am logged in with saved credentials
    And album 99999 exists with 16 images
    And I am on the "user/" page
    When I edit album 99999
    Then the album editor action buttons fit on one row

  Scenario: Album cover picker renders protected photo previews and persists selection
    Given an enabled admin user account exists
    And I am logged in with saved credentials
    And album 99999 exists with 16 images
    And album 99999 has a protected thumbnail for image 1
    And I am on the "user/" page
    When I edit album 99999
    And I open the album cover picker
    Then I can see a loaded protected cover preview
    When I select the first cover preview
    Then album 99999 uses the selected cover image

  Scenario: Admin can edit collection details directly from the collection page
    Given an enabled admin user account exists
    And I am logged in with saved credentials
    And a collection named "Mitzvah" exists
    When I am on the "user/collection.php?collection=98875" page
    And I open the collection editor from its page
    Then I see the existing "Edit Collection" management dialog
    When I set the collection description to "Updated from collection page"
    And I save the collection details from its page
    Then the collection page shows "Updated from collection page"
    And the collection database description is "Updated from collection page"

  Scenario: A visitor using a collection code cannot see the edit control
    Given an enabled admin user account exists
    And I am logged in with saved credentials
    And a collection named "Mitzvah" exists with code "EVENT99999"
    When I logout
    And I search for album "EVENT99999"
    Then I see the "Mitzvah" collection landing page
    And the collection edit control is not shown
