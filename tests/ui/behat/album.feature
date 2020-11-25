@album
Feature: Album
  As a user
  I want to be able to view my album
  So that I can make selects, download images, and share them on social media

  Background:
    Given an enabled user account exists
    And I am logged in with saved credentials
    And album 99999 exists with 16 images
    And I have access to album 99999
    Given I am on the "user/album.php?album=99999" page

  Scenario: Gallery images load
    Then I see the "1st" album images load

  Scenario: Gallery images keep loading
    When I scroll to the bottom of the page
    Then I see the "3rd" album images load

  Scenario: Hovering an image zooms in
    When I hover over album image 1
    Then I see the info icon on album image 1

  Scenario: Clicking an image brings up a preview modal
    When I view album image 1
    Then I see album image 1 in the preview modal

  Scenario: Clicking another image brings up a preview modal
    When I view album image 6
    Then I see album image 6 in the preview modal

  Scenario: Modal does not automatically scroll to the next image
    When I view album image 1
    And I wait for 5 seconds
    Then I see album image 1 in the preview modal

  Scenario: Able to manually advance to next image
    When I view album image 1
    And I advance to the next album image
    Then I see album image 2 in the preview modal

  Scenario: Able to manually advance to previous image
    When I view album image 1
    And I advance to the previous album image
    Then I see album image 16 in the preview modal

  Scenario: Images with captions display captions
    Given album 99999 image 2 has captain "sample caption"
    When I reload the page
    And I view album image 2
    Then I see the album caption "sample caption" displayed

  Scenario: Images without captions do not display captions
    When I view album image 3
    Then I do not see any album captions

  Scenario: Favoriting image increases favorites count
    When I view album image 2
    And I favorite the image
    Then I see the image as a favorite
    And I see the favorite count is "1"

  Scenario: Defavoriting image decreases favorites count
    Given album 99999 image 2 is a favorite
    When I view album image 2
    And I defavorite the image
    Then I do not see the image as a favorite
    And I see the favorite count is ""

  Scenario: No favorites shows empty favorites
    When I view my favorites
    Then I see 0 favorites
    And I see the favorite count is ""

  Scenario: Able to view favorite images
    Given album 99999 image 2 is a favorite
    And I reload the page
    When I view my favorites
    Then I see 1 favorite
    And I see album image 2 as a favorite
    And I see the favorite count is "1"

  Scenario: Able to remove favorite from favorites
    Given album 99999 image 2 is a favorite
    When I view my favorites
    And I remove favorite image 2
    Then I see 0 favorites
    And I see the favorite count is ""

  Scenario: Unable to do any actions when no favorites
    When I view my favorites
    Then the download favorites button is disabled
    And the share favorites button is disabled
    And the submit favorites button is disabled

  Scenario: Able to download favorites
    Given album 99999 image 2 is a favorite
    When I view my favorites
    And I download my favorites
    Then I see the download terms of service

  Scenario: Unable to download favorites
    Given album 99999 image 2 is a favorite
    When I view my favorites
    And I download my favorites
    And I confirm my download
    Then I see an error message indicating no files are available to download

  Scenario: Download favorites
    Given album 99999 image 2 is a favorite
    And I have download rights for album 99999 image 2
    When I view my favorites
    And I download my favorites
    And I confirm my download
    Then I see an info message indicating download will start shortly
    And I see album 99999 download with my favorites

  Scenario: Download multiple favorites
    Given album 99999 image 2 is a favorite
    Given album 99999 image 4 is a favorite
    Given album 99999 image 7 is a favorite
    And I have download rights for album 99999 image 2
    And I have download rights for album 99999 image 7
    When I view my favorites
    And I download my favorites
    And I confirm my download
    Then I see an info message indicating download will start shortly
    And I see album 99999 download with my favorites

  Scenario: Unable to share favorites
  Scenario: Able to submit favorites

  Scenario: Adding image to cart increases cart count
  Scenario: Removing image from cart decreases cart count
  Scenario: No cart shows empty cart
  Scenario: Able to view cart content
  Scenario: Unable to share all images
  Scenario: Able to download all images
  Scenario: Purchase image adds single image to cart
  Scenario: Able to submit single image
