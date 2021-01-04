@albums @admin
Feature: Admin Albums
  As an admin
  I want to be able to view all albums and manage them
  So that I can easily setup albums for others

  Background:
    Given an enabled admin user account exists
    And I am logged in with saved credentials
    And album 99999 exists with 16 images
    And album 99998 exists with code "album99998"
    And I am on the "user/" page

  Scenario: Admin able to see all albums
    Then I see album 99998 listed
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
    And I see album 99999 edit icon
    And I see album 99999 log icon

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
    #TODO - gotta figure this one out...

  Scenario: Able to set access
    When I edit album 99999
    And I set access to my album
    Then I see the ability to set access

  Scenario: Able to view users with album access
    Given user 4 has access to album 99999
    When I edit album 99999
    And I set access to my album
    Then I see users "4" with album access
    And I see users "" with download access
    And I see users "" with share access

  Scenario: Unable to download images without album access
    Given user 4 has download access to album 99999
    When I edit album 99999
    And I set access to my album
    Then I see users "" with album access
    And I see users "" with download access
    And I see users "" with share access

  Scenario: Able to view users with download access
    Given user 4 has access to album 99999
    Given user 4 has download access to album 99999
    When I edit album 99999
    And I set access to my album
    Then I see users "4" with album access
    And I see users "4" with download access
    And I see users "" with share access

  Scenario: Unable to share images without album access
    Given user 4 has share access to album 99999
    When I edit album 99999
    And I set access to my album
    Then I see users "" with album access
    And I see users "" with download access
    And I see users "" with share access

  Scenario: Able to view users with share access
    Given user 4 has access to album 99999
    Given user 4 has share access to album 99999
    When I edit album 99999
    And I set access to my album
    Then I see users "4" with album access
    And I see users "" with download access
    And I see users "4" with share access

  Scenario: Able to add user for album access
    When I edit album 99999
    And I set access to my album
    And I add user 4 for album access
    Then I see users "4" with album access
    And users "4" have access to album 99999

  Scenario: Able to remove user for album access
    Given user 4 has access to album 99999
    When I edit album 99999
    And I set access to my album
    And I remove user 4 for album access
    Then I see users "" with album access
    And users "" have access to album 99999

  Scenario: Able to add all users for download access
    When I edit album 99999
    And I set access to my album
    And I add user 0 for download access
    Then I see users "0" with download access
    And users "0" can download album 99999

  Scenario: Unable to add user without album access to download access
    When I edit album 99999
    And I set access to my album
    And I add user 4 for download access
    Then I see users "" with download access
    And users "" can download album 99999

  Scenario: Able to add user with album access to download access
    Given user 4 has access to album 99999
    When I edit album 99999
    And I set access to my album
    And I add user 4 for download access
    Then I see users "4" with download access
    And users "4" can download album 99999

  Scenario: Able to remove user for download access
    Given user 4 has access to album 99999
    Given user 4 has download access to album 99999
    When I edit album 99999
    And I set access to my album
    And I remove user 4 for download access
    Then I see users "4" with album access
    Then I see users "" with download access
    And users "4" have access to album 99999
    And users "" can download album 99999

  Scenario: Able to add all users for share access
    When I edit album 99999
    And I set access to my album
    And I add user 0 for share access
    Then I see users "0" with share access
    And users "0" can share album 99999

  Scenario: Unable to add user without album access to share access
    When I edit album 99999
    And I set access to my album
    And I add user 4 for share access
    Then I see users "" with share access
    And users "" can share album 99999

  Scenario: Able to add user with album access to share access
    Given user 4 has access to album 99999
    When I edit album 99999
    And I set access to my album
    And I add user 4 for share access
    Then I see users "4" with share access
    And users "4" can share album 99999

  Scenario: Able to remove user for share access
    Given user 4 has access to album 99999
    Given user 4 has share access to album 99999
    When I edit album 99999
    And I set access to my album
    And I remove user 4 for share access
    Then I see users "4" with album access
    Then I see users "" with share access
    And users "4" have access to album 99999
    And users "" can share album 99999

  Scenario: Delete album
    When I edit album 99999
    And I delete my album
    And I confirm my deletion of my album
    Then I don't see album 99999 listed

  Scenario Outline: Able to make thumbnails
    When I edit album 99999
    And I make thumbnails for my album
    And I create "<thumbType>" thumbnails
    Then I see thumbnails being created
    Then I have created "<thumbType>" thumbnail images for album 99999
    Examples:
      | thumbType |
      | proof     |
      | watermark |
      | nothing   |

  Scenario: View album logs
    Given logs exist:
      | user | time                | action         | what                     | album |
      | 1    | 2020-10-14 13:02:18 | Visited Album  | NULL                     | 99999 |
      | 4    | 2020-10-14 13:02:20 | Visited Album  | NULL                     | 99999 |
      | 5    | 2020-12-07 08:41:10 | Unset Favorite | 32                       | 99999 |
      | 5    | 2020-12-07 08:41:41 | Downloaded     | sample1.jpg\nsample6.jpg | 99999 |
      | 4    | 2020-10-14 13:02:20 | Visited Album  | NULL                     | 99998 |
    When I view album 99999 logs
    Then I see album logs:
      | time                | action                                           |
      | 2020-10-14 13:02:18 | User msaperst Visited Album                      |
      | 2020-10-14 13:02:20 | User uploader Visited Album                      |
      | 2020-12-07 08:41:10 | User testUser Unset Favorite 32                  |
      | 2020-12-07 08:41:41 | User testUser Downloaded sample1.jpg sample6.jpg |