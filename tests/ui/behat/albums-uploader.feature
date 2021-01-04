@albums @uploader
Feature: Uploader Albums
  As an uploader
  I want to be able to view all albums and manage them
  So that I can easily setup albums for myself

  Background:
    Given an enabled uploader user account exists
    And I am logged in with saved credentials
    And album 99999 exists with 16 images
    And I have access to album 99999
    And album 99998 exists with code "album99998"
    And I have created album 99997 with 16 images
    And I am on the "user/" page

  Scenario: Uploader only able to see their albums
    Then I see 2 albums listed
    And I see album 99997 listed
    And I see album 99999 listed

  Scenario: Uploader add blank album
    When I add album "" to my albums
    Then I see an error message indicating album code required

  Scenario: Uploader add bad album
    When I add album "bad-album" to my albums
    Then I see an error message indicating no album exists

  Scenario: Uploader able to add an album
    When I add album "album99998" to my albums
    Then I see an info message indicating album successfully added
    And I see 3 albums listed
    And I see album 99998 listed

  Scenario: Uploader able to add an album by keyboard
    When I add album "album99998" to my albums with keyboard
    Then I see an info message indicating album successfully added
    And I see 3 albums listed
    And I see album 99998 listed

  Scenario Outline: Uploader able to see album information
    Then I see album 99999 album <attribute>
    Examples:
      | attribute     |
      | name          |
      | description   |
      | date          |
      | images        |

  Scenario: Uploader able to see album icons
    Then I see ability to add an album
    And I see album 99997 edit icon
    And I don't see album 99999 log icon
    And I don't see album 99999 edit icon
    And I don't see album 99999 log icon
    And I don't see album 99999 album last accessed
    And I don't see album 99999 album code

  Scenario: Album name required for album
    When I add a new album
    And I create my album
    Then I see an error message indicating album name is required

  Scenario: Add new album
    When I add a new album
    And I provide "My New Album" for the album "name"
    And I create my album
    Then I see the album details modal for album 100000

  Scenario: Add new album full
    When I add a new album
    And I provide "My New Album" for the album "name"
    And I provide "Some sample test album" for the album "description"
    And I provide "01/01/2030" for the album "date"
    And I create my album
    Then I see the album details modal for album 100000

  Scenario: Cant remove album name
    When I edit album 99997
    And I provide "" for the album "name"
    And I update my album
    Then I see an error message indicating album name is required

  Scenario Outline: Update album information
    When I edit album 99997
    And I provide "My New Album" for the album "name"
    And I provide "Some sample test album" for the album "description"
    And I provide "01/01/2030" for the album "date"
    And I update my album
    Then I see album 99999 album <attribute>
    Examples:
      | attribute     |
      | name          |
      | description   |
      | date          |
      | images        |

  Scenario: Upload images to album
    #TODO - gotta figure this one out...

  Scenario: Unable to set access
    When I edit album 99997
    Then I don't see the ability to set access

  Scenario: Delete album
    When I edit album 99997
    And I delete my album
    And I confirm my deletion of my album
    Then I don't see album 99997 listed

  Scenario: Able to make thumbnails
    When I edit album 99997
    And I make thumbnails for my album
    Then I see thumbnails being created
    Then I have created "watermark" thumbnail images for album 99997