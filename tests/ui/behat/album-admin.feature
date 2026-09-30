@album @admin
Feature: Admin Album
  As an admin
  I want to be able to view an album and manage it
  So that I can easily setup the album for others

  Background:
    Given an enabled admin user account exists
    And I am logged in with saved credentials
    And album 99999 exists with 16 images

  Scenario: Album images load
    When I am on the "user/album.php?album=99999" page
    Then I see the "1st" album images load

  Scenario: Album images keep loading
    Given I am on the "user/album.php?album=99999" page
    When I scroll to the bottom of the page
    Then I see the "3rd" album images load

  Scenario: Hovering an image reveals image controls
    Given I am on the "user/album.php?album=99999" page
    When I hover over album image 1
    Then I see the image controls on album image 1

  Scenario: Opening an image displays it in the image viewer
    Given I am on the "user/album.php?album=99999" page
    When I view album image 1
    Then I see album image 1 in the image viewer

  Scenario: Opening another image displays it in the image viewer
    Given I am on the "user/album.php?album=99999" page
    When I view album image 6
    Then I see album image 6 in the image viewer

  Scenario: Image viewer does not automatically advance
    Given I am on the "user/album.php?album=99999" page
    When I view album image 1
    And I wait for 5 seconds
    Then I see album image 1 in the image viewer

  Scenario: Deep-linked image does not automatically advance
    Given I am on the "user/album.php?album=99999#0" page
    When I wait for 5 seconds
    Then I see album image 1 in the image viewer

  Scenario: Able to manually advance to next image
    Given I am on the "user/album.php?album=99999#0" page
    When I advance to the next album image
    Then I see album image 2 in the image viewer

  Scenario: Able to manually advance to previous image
    Given I am on the "user/album.php?album=99999#0" page
    When I advance to the previous album image
    Then I see album image 16 in the image viewer

  Scenario: Images with captions display captions
    Given album 99999 image 2 has caption "sample caption"
    Given I am on the "user/album.php?album=99999#1" page
    Then I see the album caption "sample caption" displayed

  Scenario: Images without captions do not display captions
    Given I am on the "user/album.php?album=99999#2" page
    Then I do not see any album captions

  Scenario: Favoriting image increases favorites count
    Given I am on the "user/album.php?album=99999#1" page
    When I favorite the image
    Then I see the image as a favorite
    And I see the favorite count is "1"

  Scenario: Defavoriting image decreases favorites count
    Given album 99999 image 2 is a favorite
    When I am on the "user/album.php?album=99999#1" page
    And I defavorite the image
    Then I do not see the image as a favorite
    And I see the favorite count is ""

  Scenario: No favorites shows empty favorites
    Given I am on the "user/album.php?album=99999" page
    When I view my favorites
    Then I see 0 favorites
    And I see the favorite count is ""

  Scenario: Able to view favorite images
    Given album 99999 image 2 is a favorite
    And I am on the "user/album.php?album=99999" page
    When I view my favorites
    Then I see 1 favorite
    And I see album image 2 as a favorite
    And I see the favorite count is ""

  Scenario: Able to remove favorite from favorites
    Given album 99999 image 2 is a favorite
    And I am on the "user/album.php?album=99999" page
    When I view my favorites
    And I remove favorite image 2
    Then I see 0 favorites
    And I see the favorite count is ""

  Scenario: Favorite actions are disabled when there are no favorites
    Given I am on the "user/album.php?album=99999" page
    When I view my favorites
    Then the download favorites button is disabled
#    And the share favorites button is disabled
    And the submit favorites button is disabled

  Scenario: Able to download favorites
    Given album 99999 image 2 is a favorite
    And I am on the "user/album.php?album=99999" page
    When I view my favorites
    And I download my favorites
    Then I see the download terms of service

  Scenario: Download favorites
    Given album 99999 image 2 is a favorite
    And I have download rights for album 99999 image 2
    And I am on the "user/album.php?album=99999" page
    When I view my favorites
    And I download my favorites
    And I confirm my download
    Then I see an info message indicating download will start shortly
    And I see album 99999 download with images "2"
    And I see an email indicating images "2" from album 99999 downloaded

  Scenario: Download multiple favorites
    Given album 99999 image 2 is a favorite
    Given album 99999 image 4 is a favorite
    Given album 99999 image 7 is a favorite
    And I have download rights for album 99999 image 2
    And I have download rights for album 99999 image 7
    When I am on the "user/album.php?album=99999" page
    And I view my favorites
    And I download my favorites
    And I confirm my download
    Then I see an info message indicating download will start shortly
    And I see album 99999 download with images "2, 4, 7"
    And I see an email indicating images "2, 4, 7" from album 99999 downloaded


  Scenario: Able to submit favorites
    Given album 99999 image 2 is a favorite
    And I am on the "user/album.php?album=99999" page
    When I view my favorites
    And I submit my favorites
    Then I see the form to submit my favorites

  Scenario: Guest can submit favorites
    Given album 99999 has code "album 99999"
    When I logout
    And I search for album "album 99999"
    And I am on the "user/album.php?album=99999" page
    And I view album image 2
    And I favorite the image
    And I close the image viewer
    And I view my favorites
    And I submit my favorites
    Then I see the empty form to submit my favorites

  Scenario: Submit favorites
    Given album 99999 image 2 is a favorite
    And I am on the "user/album.php?album=99999" page
    When I view my favorites
    And I submit my favorites
    And I confirm my submission
    Then the submit submission button is disabled
    And the confirm submission dialog is no longer present
    And an email is sent indicating album 99999 images "2" submitted
    And I receive an email indicating I have submitted my selects


  Scenario: Able to download all images
    Given I am on the "user/album.php?album=99999" page
    When I download all my images
    Then I see the download terms of service

  Scenario: Download all images
    Given I have download rights for album 99999 image 2
    And I am on the "user/album.php?album=99999" page
    When I download all my images
    And I confirm my download
    Then I see an info message indicating download will start shortly
    And I see album 99999 download with images "1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16"
    And I see an email indicating images "1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16" from album 99999 downloaded

  Scenario: Able to download single image
    Given I have download rights for album 99999 image 2
    And I am on the "user/album.php?album=99999#1" page
    When I download the image
    Then I see the download terms of service

  Scenario: Download single image
    Given I have download rights for album 99999 image 2
    And I am on the "user/album.php?album=99999#1" page
    When I download the image
    And I confirm my download
    Then I see an info message indicating download will start shortly
    And I see album 99999 download with images "2"
    And I see an email indicating images "2" from album 99999 downloaded


  Scenario: Able to submit single image
    Given I am on the "user/album.php?album=99999#1" page
    When I submit the image
    Then I see the form to submit my favorites

  Scenario: Submit single image
    Given I am on the "user/album.php?album=99999#1" page
    When I submit the image
    And I confirm my submission
    Then the submit submission button is disabled
    And the confirm submission dialog is no longer present
    And an email is sent indicating album 99999 images "2" submitted
    And I receive an email indicating I have submitted my selects

  Scenario: No email updates
    Given I am on the "user/album.php?album=99999" page
    Then I don't see any email notification messages

  Scenario: Email notifications exist
    Given album 99999 has notifications:
      | email                      | contacted |
      | msaperst+sstest@gmail.com  | 0         |
      | msaperst+sstest2@gmail.com | 1         |
    When I am on the "user/album.php?album=99999" page
    Then I see notification emails of:
      | email                     |
      | msaperst+sstest@gmail.com |

  Scenario: Correct logged in message
    Given album 99999 has notifications:
      | email                     | contacted |
      | msaperst+sstest@gmail.com | 0         |
    When I am on the "user/album.php?album=99999" page
    And I send the user notifications
    Then I see the email notification set to "Images have been posted to album Album 99999. You can access your images by logging in at https://saperstonestudios.com/ and then navigating to https://saperstonestudios.com/user/album.php?album=99999."

  Scenario: Correct code message
    Given album 99999 has code "code"
    And album 99999 has notifications:
      | email                     | contacted |
      | msaperst+sstest@gmail.com | 0         |
    When I am on the "user/album.php?album=99999" page
    And I send the user notifications
    Then I see the email notification set to "Images have been posted to album Album 99999. You can access your images by navigating to https://saperstonestudios.com/#album=code."

  Scenario: Can't send email notification with blank message
    Given album 99999 has notifications:
      | email                     | contacted |
      | msaperst+sstest@gmail.com | 0         |
    When I am on the "user/album.php?album=99999" page
    And I send the user notifications
    And I set the email notification message to ""
    And I confirm sending user notification
    Then I see an error message indicating all fields need to be filled in

  Scenario: Able to send out user list
    Given album 99999 has notifications:
      | email                     | contacted |
      | msaperst+sstest@gmail.com | 0         |
    When I am on the "user/album.php?album=99999" page
    And I send the user notifications
    And I confirm sending user notification
    Then I don't see any email notification messages
    And I see an album notification for album 99999 was emailed out

  Scenario: Opening an image sets the hash to that image
    Given I am on the "user/album.php?album=99999" page
    When I view album image 3
    Then I am taken to the "user/album.php?album=99999#2" page

  Scenario: Opening an album at an image hash displays that image
    When I am on the "user/album.php?album=99999#1" page
    Then I see album image 2 in the image viewer

  Scenario: Going to next image increases hash
    Given I am on the "user/album.php?album=99999#1" page
    When I advance to the next album image
    Then I am taken to the "user/album.php?album=99999#2" page

  Scenario: Going to previous image decreases hash
    Given I am on the "user/album.php?album=99999#1" page
    When I advance to the previous album image
    Then I am taken to the "user/album.php?album=99999#0" page

  Scenario: Closing the image viewer hides it
    Given I am on the "user/album.php?album=99999#1" page
    When I close the image viewer
    Then I don't see the image viewer

  Scenario: Closing the image viewer removes the image hash
    Given I am on the "user/album.php?album=99999#1" page
    When I close the image viewer
    Then I am taken to the "user/album.php?album=99999" page