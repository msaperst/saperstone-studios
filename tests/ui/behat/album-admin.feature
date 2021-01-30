@album @admin
Feature: Admin Album
  As an admin
  I want to be able to view an album and manage it
  So that I can easily setup the album for others

  Background:
    Given an enabled admin user account exists
    And I am logged in with saved credentials
    And album 99999 exists with 16 images
    And I am on the "user/album.php?album=99999" page

  Scenario: Album images load
    Then I see the "1st" album images load

  Scenario: Album images keep loading
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
#    And the share favorites button is disabled
    And the submit favorites button is disabled

  Scenario: Able to download favorites
    Given album 99999 image 2 is a favorite
    When I view my favorites
    And I download my favorites
    Then I see the download terms of service

  Scenario: Download favorites
    Given album 99999 image 2 is a favorite
    And I have download rights for album 99999 image 2
    When I view my favorites
    And I download my favorites
    And I confirm my download
    Then I see an info message indicating download will start shortly
    And I see album 99999 download with my favorites
    And I see an email indicating images "2" from album 99999 downloaded

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
    And I see an email indicating images "2, 4, 7" from album 99999 downloaded

#  Scenario: Unable to share favorites
#    Given album 99999 image 2 is a favorite
#    When I view my favorites
#    And I share my favorites
#    Then I see that sharing isn't available

  Scenario: Able to submit favorites
    Given album 99999 image 2 is a favorite
    When I view my favorites
    And I submit my favorites
    Then I see the form to submit my favorites

  Scenario: Able to submit favorites as guest
    When I logout
    And album 99999 has code "album 99999"
    And I have searched for album "album 99999"
    And I am on the "user/album.php?album=99999" page
    And I view album image 2
    And I favorite the image
    And I close the album image modal
    And I view my favorites
    And I submit my favorites
    Then I see the empty form to submit my favorites

  Scenario: Submit favorites
    Given album 99999 image 2 is a favorite
    When I view my favorites
    And I submit my favorites
    And I confirm my submission
    Then the submit submission button is disabled
    And the confirm submission dialog is no longer present
    And an email is sent indicating album 99999 image 2 submitted
    And I receive an email indicating I have submitted my selects

#  Scenario: Unable to share all images
#    When I share all my images
#    Then I see that sharing isn't available

  Scenario: Able to download all images
    And I download all my images
    Then I see the download terms of service

  Scenario: Download all images
    And I have download rights for album 99999 image 2
    And I download all my images
    And I confirm my download
    Then I see an info message indicating download will start shortly
    And I see album 99999 download with images "1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16"
    And I see an email indicating images "1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16" from album 99999 downloaded

  Scenario: Adding image to cart increases cart count
    When I view album image 2
    And I add the image to my cart
    And I add 1 "Signature" "10x10" "Metal Prints"
    Then I see 1 "Signature" "10x10" "Metal Prints" price calculated
    And I see the cart count is "1"

  Scenario: Able to increase cart count input
    When I view album image 2
    And I add the image to my cart
    And I increase "Signature" "10x10" "Metal Prints" count
    Then I see 1 "Signature" "10x10" "Metal Prints" price calculated
    And I see the cart count is "1"

  Scenario: Cart images can be saved
    Given album 99999 image 2 has 1 "Signature" "10x10" "Metal Prints" in the cart
    And I reload the page
    When I view album image 2
    And I add the image to my cart
    Then I see 1 "Signature" "10x10" "Metal Prints" price calculated
    And I see the cart count is "1"

  Scenario: Removing image from cart decreases cart count
    Given album 99999 image 2 has 1 "Signature" "10x10" "Metal Prints" in the cart
    When I view album image 2
    And I add the image to my cart
    And I add 0 "Signature" "10x10" "Metal Prints"
    Then I see 0 "Signature" "10x10" "Metal Prints" price calculated
    And I see the cart count is ""

  Scenario: No cart images shows empty cart
    When I view my cart
    Then I see 0 cart items
    And I see the cart count is ""

  Scenario: Able to view cart images
    Given album 99999 image 2 has 1 "Signature" "10x10" "Metal Prints" in the cart
    And I reload the page
    When I view my cart
    Then I see 1 cart items
    And I see album 99999 image 2 has 1 "Signature" "10x10" "Metal Prints" listed
    And I see the cart count is "1"

  Scenario: Able to remove image from cart
    Given album 99999 image 2 has 1 "Signature" "10x10" "Metal Prints" in the cart
    When I view my cart
    And I remove album 99999 image 2 "Signature" "10x10" "Metal Prints" from the cart
    Then I see 0 cart items
    And I see the cart count is ""

  Scenario: Unable to submit cart when no cart images
    When I view my cart
    And I provide "Max" for the shipping "name"
    And I provide "msaperst+sstest@gmail.com" for the shipping "email"
    And I provide "5712453351" for the shipping "phone"
    And I provide "5012 Whisper Willow Dr" for the shipping "address"
    And I provide "Fairfax" for the shipping "city"
    And I provide "VA" for the shipping "state"
    And I provide "22030" for the shipping "zip"
    Then the place order button is disabled

  Scenario Outline: Unable to submit cart when cart info isn't filled out
    Given album 99999 image 2 has 1 "Signature" "10x10" "Metal Prints" in the cart
    When I view my cart
    And I provide "<value1>" for the shipping "<input1>"
    And I provide "<value2>" for the shipping "<input2>"
    And I provide "<value3>" for the shipping "<input3>"
    And I provide "<value4>" for the shipping "<input4>"
    And I provide "<value5>" for the shipping "<input5>"
    And I provide "<value6>" for the shipping "<input6>"
    And I provide "<value7>" for the shipping "<input7>"
    Then the place order button is disabled
    And cart input "<input>" shows as invalid
    Examples:
      | value1 | input1 | value2                    | input2 | value3     | input3 | value4                 | input4  | value5  | input5 | value6 | input6 | value7 | input7 | input   |
      |        | name   | msaperst+sstest@gmail.com | email  | 5712453351 | phone  | 5012 Whisper Willow Dr | address | Fairfax | city   | VA     | state  | 22030  | zip    | name    |
      | Max    | name   |                           | email  | 5712453351 | phone  | 5012 Whisper Willow Dr | address | Fairfax | city   | VA     | state  | 22030  | zip    | email   |
      | Max    | name   | msaperst+sstest@gmail.com | email  |            | phone  | 5012 Whisper Willow Dr | address | Fairfax | city   | VA     | state  | 22030  | zip    | phone   |
      | Max    | name   | msaperst+sstest@gmail.com | email  | 5712453351 | phone  |                        | address | Fairfax | city   | VA     | state  | 22030  | zip    | address |
      | Max    | name   | msaperst+sstest@gmail.com | email  | 5712453351 | phone  | 5012 Whisper Willow Dr | address |         | city   | VA     | state  | 22030  | zip    | city    |
      | Max    | name   | msaperst+sstest@gmail.com | email  | 5712453351 | phone  | 5012 Whisper Willow Dr | address | Fairfax | city   |        | state  | 22030  | zip    | state   |
      | Max    | name   | msaperst+sstest@gmail.com | email  | 5712453351 | phone  | 5012 Whisper Willow Dr | address | Fairfax | city   | VA     | state  |        | zip    | zip     |

  Scenario: Unable to submit cart when options aren't filled out
    Given album 99999 image 2 has 1 "Prints" "11x14" "Photo Prints" in the cart
    When I view my cart
    And I provide "Max" for the shipping "name"
    And I provide "msaperst+sstest@gmail.com" for the shipping "email"
    And I provide "5712453351" for the shipping "phone"
    And I provide "5012 Whisper Willow Dr" for the shipping "address"
    And I provide "Fairfax" for the shipping "city"
    And I provide "VA" for the shipping "state"
    And I provide "22030" for the shipping "zip"
    Then the place order button is disabled
    And I see option is invalid for "1st" album 99999 image 2 "Prints" "11x14" "Photo Prints"

  Scenario: Able to select cart options
    Given album 99999 image 2 has 1 "Prints" "11x14" "Photo Prints" in the cart
    When I view my cart
    And I select option "Glossy" in cart for album 99999 image 2 "Prints" "11x14" "Photo Prints"
    Then I see option is valid for "1st" album 99999 image 2 "Prints" "11x14" "Photo Prints"

  Scenario: Able to submit cart
    Given album 99999 image 2 has 1 "Prints" "11x14" "Photo Prints" in the cart
    Given album 99999 image 3 has 1 "Signature" "10x10" "Metal Prints" in the cart
    When I view my cart
    And I provide "Max" for the shipping "name"
    And I provide "msaperst+sstest@gmail.com" for the shipping "email"
    And I provide "5712453351" for the shipping "phone"
    And I provide "5012 Whisper Willow Dr" for the shipping "address"
    And I provide "Fairfax" for the shipping "city"
    And I provide "VA" for the shipping "state"
    And I provide "22030" for the shipping "zip"
    And I select option "Glossy" in cart for album 99999 image 2 "Prints" "11x14" "Photo Prints"
    And I submit my cart
    Then I see an info message indicating forwarding to paypal
    And the place order button is disabled
    And I am forwarded to the paypal page
    # TODO - put in check for email

  Scenario: Tax and Price are properly calculated
    Given album 99999 image 1 has 2 "Prints" "11x14" "Photo Prints" in the cart
    Given album 99999 image 3 has 1 "Signature" "10x10" "Metal Prints" in the cart
    Given album 99999 image 3 has 1 "Standard" "16x20" "Stand Out Frames" in the cart
    Given album 99999 image 4 has 1 "Standard" "16x20" "Stand Out Frames" in the cart
    When I view my cart
    Then I see the tax calculated as $72.00
    And I see the total calculated as $1272.00

  Scenario: Able to see images from other albums
    And album 99998 exists with 3 images
    Given album 99998 image 2 has 1 "Signature" "10x10" "Metal Prints" in the cart
    Given album 99999 image 2 has 1 "Signature" "10x10" "Metal Prints" in the cart
    And I reload the page
    When I view my cart
    Then I see 2 cart items
    And I see album 99998 image 2 has 1 "Signature" "10x10" "Metal Prints" listed
    And I see album 99999 image 2 has 1 "Signature" "10x10" "Metal Prints" listed
    And I see the cart count is "2"

  Scenario: Able to download single image
    Given I have download rights for album 99999 image 2
    When I view album image 2
    And I download the image
    Then I see the download terms of service

  Scenario: Download single image
    Given I have download rights for album 99999 image 2
    When I view album image 2
    And I download the image
    And I confirm my download
    Then I see an info message indicating download will start shortly
    And I see album 99999 download with images "2"
    And I see an email indicating images "2" from album 99999 downloaded

#  Scenario: Unable to share single image
#    Given I have share rights for album 99999 image 2
#    When I view album image 2
#    And I share the image
#    Then I see that sharing isn't available

  Scenario: Able to submit single image
    When I view album image 2
    And I submit the image
    Then I see the form to submit my favorites

  Scenario: Submit single image
    When I view album image 2
    And I submit the image
    And I confirm my submission
    Then the submit submission button is disabled
    And the confirm submission dialog is no longer present
    And an email is sent indicating album 99999 image 2 submitted
    And I receive an email indicating I have submitted my selects

  Scenario: No email updates
    Then I don't see any email notification messages

  Scenario: Email notifications exist
    Given album 99999 has notifications:
      | email                      | contacted |
      | msaperst+sstest@gmail.com  | 0         |
      | msaperst+sstest2@gmail.com | 1         |
    When I reload the page
    Then I see notification emails of:
      | email                     |
      | msaperst+sstest@gmail.com |

  Scenario: Correct logged in message
    Given album 99999 has notifications:
      | email                     | contacted |
      | msaperst+sstest@gmail.com | 0         |
    When I reload the page
    And I send the user notifications
    Then I see the email notification set to "Images have been posted to album Album 99999. You can access your images by logging in at https://saperstonestudios.com/ and then navigating to https://saperstonestudios.com/user/album.php?album=99999."

  Scenario: Correct code message
    Given album 99999 has code "code"
    Given album 99999 has notifications:
      | email                     | contacted |
      | msaperst+sstest@gmail.com | 0         |
    When I reload the page
    And I send the user notifications
    Then I see the email notification set to "Images have been posted to album Album 99999. You can access your images by navigating to https://saperstonestudios.com/#album and entering in album code `code`."

  Scenario: Can't send email notification with blank message
    Given album 99999 has notifications:
      | email                     | contacted |
      | msaperst+sstest@gmail.com | 0         |
    When I reload the page
    And I send the user notifications
    And I set the email notification message to ""
    And I confirm sending user notification
    Then I see an error message indicating all fields need to be filled in

  Scenario: Able to send out user list
    Given album 99999 has notifications:
      | email                     | contacted |
      | msaperst+sstest@gmail.com | 0         |
    When I reload the page
    And I send the user notifications
    And I confirm sending user notification
    Then I don't see any email notification messages
    And email notifications are marked as sent for album 99999
    And I see an album notification for album 99999 was emailed out

    #TODO - finish me