@gallery
Feature: Gallery
  As a user
  I want to view gallery images
  So that I see samples of photos

  Background:
    Given gallery 999 exists with 16 images

  Scenario: Gallery images load
    When I am on the "portrait/galleries.php?w=999" page
    Then I see the "1st" gallery images load

  Scenario: Gallery images keep loading
    Given I am on the "portrait/galleries.php?w=999" page
    When I scroll to the bottom of the page
    Then I see the "3rd" gallery images load

  Scenario: Hovering an image zooms in
    Given I am on the "portrait/galleries.php?w=999" page
    When I hover over gallery image 1
    Then I see the info icon on gallery image 1

  Scenario: Clicking an image brings up a preview modal
    Given I am on the "portrait/galleries.php?w=999" page
    When I view gallery image 1
    Then I see gallery image 1 in the preview modal

  Scenario: Clicking another image brings up a preview modal
    Given I am on the "portrait/galleries.php?w=999" page
    When I view gallery image 6
    Then I see gallery image 6 in the preview modal

  Scenario: Modal does not automatically scroll to the next image
    Given I am on the "portrait/galleries.php?w=999" page
    When I view gallery image 1
    And I wait for 5 seconds
    Then I see gallery image 1 in the preview modal

  Scenario: Modal does not automatically scroll to the next image on hash
    Given I am on the "portrait/galleries.php?w=999#0" page
    When I wait for 5 seconds
    Then I see gallery image 1 in the preview modal

  Scenario: Able to manually advance to next image
    Given I am on the "portrait/galleries.php?w=999#0" page
    When I advance to the next gallery image
    Then I see gallery image 2 in the preview modal

  Scenario: Able to manually advance to previous image
    Given I am on the "portrait/galleries.php?w=999#0" page
    When I advance to the previous gallery image
    Then I see gallery image 16 in the preview modal

  Scenario: Indicator allows skipping to image
    Given I am on the "portrait/galleries.php?w=999#0" page
    When I skip to gallery image 7
    Then I see gallery image 7 in the preview modal

  Scenario: Images with captions display captions
    Given gallery 999 image 2 has captain "sample caption"
    When I am on the "portrait/galleries.php?w=999#1" page
    Then I see the gallery caption "sample caption" displayed

  Scenario: Images without captions do not display captions
    When I am on the "portrait/galleries.php?w=999#2" page
    Then I do not see any gallery captions

  Scenario: Opening an image sets the hash to that image
    Given I am on the "portrait/galleries.php?w=999" page
    When I view gallery image 3
    Then I am taken to the "portrait/galleries.php?w=999#2" page

  Scenario: Opening gallery with hash displays that image
    When I am on the "portrait/galleries.php?w=999#1" page
    Then I see gallery image 2 in the preview modal

  Scenario: Going to next image increases hash
    Given I am on the "portrait/galleries.php?w=999#1" page
    When I advance to the next gallery image
    Then I am taken to the "portrait/galleries.php?w=999#2" page

  Scenario: Going to previous image decreases hash
    Given I am on the "portrait/galleries.php?w=999#1" page
    When I advance to the previous gallery image
    Then I am taken to the "portrait/galleries.php?w=999#0" page

  Scenario: Closing image closes modal
    Given I am on the "portrait/galleries.php?w=999#1" page
    When I close the gallery view
    Then I don't see the gallery preview modal

  Scenario: Closing image removes hash
    Given I am on the "portrait/galleries.php?w=999#1" page
    When I close the gallery view
    Then I am taken to the "portrait/galleries.php?w=999#" page

  Scenario: Clicking indicator changes the hash
    Given I am on the "portrait/galleries.php?w=999#0" page
    When I skip to gallery image 7
    Then I am taken to the "portrait/galleries.php?w=999#6" page
