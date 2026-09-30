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
    Then I see the edit album details modal for the new album

  Scenario: Add new album full
    When I add a new album
    And I provide "My New Album" for the album "name"
    And I provide "Some sample test album" for the album "description"
    And I provide "01/01/2030" for the album "date"
    And I create my album
    Then I see the edit album details modal for the new album

  Scenario: Edit new album
    When I edit album 99999
    Then I see the edit album details modal for album 99999

  Scenario: Album name cannot be removed
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

  Scenario: Upload image to album
    When I edit album 99999
    And I upload test image "flower.jpeg"
    And I close the album details modal
    Then I see album 99999 has 17 images

  Scenario: Admin can manage album access
    When I edit album 99999
    And I set access to my album
    Then I see the ability to set access

  Scenario: Album access list shows authorized users
    Given user uploader has access to album 99999
    When I edit album 99999
    And I set access to my album
    Then I see users "uploader" with album access
    And I see users "" with download access
    And I see users "" with share access

  Scenario: Download access requires album access
    Given user uploader has download access to album 99999
    When I edit album 99999
    And I set access to my album
    Then I see users "" with album access
    And I see users "" with download access
    And I see users "" with share access

  Scenario: Download access list shows authorized users
    Given user uploader has access to album 99999
    Given user uploader has download access to album 99999
    When I edit album 99999
    And I set access to my album
    Then I see users "uploader" with album access
    And I see users "uploader" with download access
    And I see users "" with share access

  Scenario: Share access requires album access
    Given user uploader has share access to album 99999
    When I edit album 99999
    And I set access to my album
    Then I see users "" with album access
    And I see users "" with download access
    And I see users "" with share access

  Scenario: Share access list shows authorized users
    Given user uploader has access to album 99999
    Given user uploader has share access to album 99999
    When I edit album 99999
    And I set access to my album
    Then I see users "uploader" with album access
    And I see users "" with download access
    And I see users "uploader" with share access

  Scenario: Admin can grant album access
    When I edit album 99999
    And I set access to my album
    And I add user uploader for album access
    Then I see users "uploader" with album access

  Scenario: Admin can revoke album access
    Given user uploader has access to album 99999
    When I edit album 99999
    And I set access to my album
    And I remove user uploader for album access
    Then I see users "" with album access

  Scenario: Admin can grant download access to all users
    When I edit album 99999
    And I set access to my album
    And I add user 0 for download access
    Then I see users "0" with download access

  Scenario: Admin cannot grant download access without album access
    When I edit album 99999
    And I set access to my album
    And I try to add user uploader for download access
    Then I see users "" with download access

  Scenario: Admin can grant download access to an authorized user
    Given user uploader has access to album 99999
    When I edit album 99999
    And I set access to my album
    And I add user uploader for download access
    Then I see users "uploader" with download access

  Scenario: Admin can revoke download access
    Given user uploader has access to album 99999
    Given user uploader has download access to album 99999
    When I edit album 99999
    And I set access to my album
    And I remove user uploader for download access
    Then I see users "uploader" with album access
    Then I see users "" with download access

  Scenario: Admin can grant share access to all users
    When I edit album 99999
    And I set access to my album
    And I add user 0 for share access
    Then I see users "0" with share access

  Scenario: Admin cannot grant share access without album access
    When I edit album 99999
    And I set access to my album
    And I add user uploader for share access
    Then I see users "" with share access

  Scenario: Admin can grant share access to an authorized user
    Given user uploader has access to album 99999
    When I edit album 99999
    And I set access to my album
    And I add user uploader for share access
    Then I see users "uploader" with share access

  Scenario: Admin can revoke share access
    Given user uploader has access to album 99999
    Given user uploader has share access to album 99999
    When I edit album 99999
    And I set access to my album
    And I remove user uploader for share access
    Then I see users "uploader" with album access
    Then I see users "" with share access

  Scenario: Granted album access persists after reload
    When I edit album 99999
    And I set access to my album
    And I add user uploader for album access
    And I reload the page
    And I edit album 99999
    And I set access to my album
    Then I see users "uploader" with album access

  Scenario: Revoked album access persists after reload
    Given user uploader has access to album 99999
    When I edit album 99999
    And I set access to my album
    And I remove user uploader for album access
    And I reload the page
    And I edit album 99999
    And I set access to my album
    Then I see users "" with album access

  Scenario: Granted download access persists after reload
    Given user uploader has access to album 99999
    When I edit album 99999
    And I set access to my album
    And I add user uploader for download access
    And I reload the page
    And I edit album 99999
    And I set access to my album
    Then I see users "uploader" with download access

  Scenario: Revoked download access persists after reload
    Given user uploader has access to album 99999
    Given user uploader has download access to album 99999
    When I edit album 99999
    And I set access to my album
    And I remove user uploader for download access
    And I reload the page
    And I edit album 99999
    And I set access to my album
    Then I see users "" with download access

  Scenario: Granted share access persists after reload
    Given user uploader has access to album 99999
    When I edit album 99999
    And I set access to my album
    And I add user uploader for share access
    And I reload the page
    And I edit album 99999
    And I set access to my album
    Then I see users "uploader" with share access

  Scenario: Revoked share access persists after reload
    Given user uploader has access to album 99999
    Given user uploader has share access to album 99999
    When I edit album 99999
    And I set access to my album
    And I remove user uploader for share access
    And I reload the page
    And I edit album 99999
    And I set access to my album
    Then I see users "" with share access

  Scenario: Delete album
    When I edit album 99999
    And I delete my album
    And I confirm my deletion of my album
    Then I don't see album 99999 listed

  Scenario Outline: Admin can create thumbnails
    Given album 99999 images are generic
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