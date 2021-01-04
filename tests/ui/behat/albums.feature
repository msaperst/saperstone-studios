@albums
Feature: Albums
  As a user
  I want to be able to view all my albums
  So that I can easily manage and view them

  Background:
    Given an enabled user account exists
    And I am logged in with saved credentials
    And album 99999 exists
    And I have access to album 99999
    And album 99998 exists with code "album99998"
    And I am on the "user/" page

  Scenario: Downloader able to see all their albums
    Then I see 1 album listed
    And I see album 99999 listed

  Scenario: Downloader add blank album
    When I add album "" to my albums
    Then I see an error message indicating album code required

  Scenario: Downloader add bad album
    When I add album "bad-album" to my albums
    Then I see an error message indicating no album exists

  Scenario: Downloader able to add an album
    When I add album "album99998" to my albums
    Then I see an info message indicating album successfully added
    And I see 2 albums listed
    And I see album 99998 listed

  Scenario: Downloader able to add an album by keyboard
    When I add album "album99998" to my albums with keyboard
    Then I see an info message indicating album successfully added
    And I see 2 albums listed
    And I see album 99998 listed

  Scenario Outline: Downloader able to see album information
    Then I see album 99999 album <attribute>
    Examples:
      | attribute   |
      | name        |
      | description |
      | date        |
      | images      |

  Scenario: Downloader unable to see album information
    Then I don't see ability to add an album
    And I don't see album 99999 edit icon
    And I don't see album 99999 log icon
    And I don't see album 99999 album last accessed
    And I don't see album 99999 album code
