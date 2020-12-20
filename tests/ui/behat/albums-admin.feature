Feature: Admin Albums
  As an admin
  I want to be able to view all albums and manage them
  So that I can easily setup albums for others

  Background:
    Given an enabled admin user account exists
    And I am logged in with saved credentials
    And album 99999 exists
    And I have access to album 99999
    And album 99998 exists with code "album99998"
    And I am on the "user/" page

  Scenario: Admin able to see all albums
    And I see album 99998 listed
    And I see album 99999 listed

  Scenario Outline: Admin able to see album information
    Then I see album 99999 album <attribute>
    Examples:
      | attribute     |
      | name          |
      | description   |
      | date          |
      | images        |
      | last accessed |
      | code          |

  Scenario: Admin able to see album icons
    Then I see ability to add an album
    And I see album 99999 edit icons

  Scenario: Album name required for album
    When I add a new album
    And I create my album
    Then I see an error message indicating album name is required

  Scenario: Add new album
    When I add a new album
    And I provide "My New Album" for the album "name"
    And I create my album
    Then I see the edit album details modal for album 100000

  Scenario: Add new album full
    When I add a new album
    And I provide "My New Album" for the album "name"
    And I provide "Some sample test album" for the album "description"
    And I provide "01/01/2030" for the album "date"
    And I create my album
    Then I see the edit album details modal for album 100000

  Scenario: Edit new album
    When I edit album 99999
    Then I see the edit album details modal for album 99999

  Scenario: Cant remove album name
    When I edit album 99999
    And I provide "" for the album "name"
    And I update my album
    Then I see an error message indicating album name is required

  Scenario: Update album information
    When I edit album 99999
    And I provide "My New Album" for the album "name"
    And I provide "Some sample test album" for the album "description"
    And I provide "01/01/2030" for the album "date"
    And I provide "sample code" for the album "code"
    And I update my album
    And I edit album 99999
    Then I see the edit album details modal for album 99999

  Scenario: Upload images to album

  Scenario: Set access

  Scenario: Delete album
    When I edit album 99999
    And I delete my album
    And I confirm my deletion of my album
    Then I don't see album 99999 listed

  Scenario: Make thumbnails

  Scenario: View album logs