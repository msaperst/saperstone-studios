@gallery
Feature: Gallery
  As a user
  I want to view gallery images
  So that I can browse photography samples

  Background:
    Given gallery 999 exists with 16 images

  Scenario: Initial gallery images are displayed
    When I am on the "portrait/galleries.php?w=999" page
    Then I see the "1st" gallery images load

  Scenario: More gallery images load while scrolling
    Given I am on the "portrait/galleries.php?w=999" page
    When I scroll to the bottom of the page
    Then I see the "3rd" gallery images load

  Scenario: Hovering over an image reveals the view control
    Given I am on the "portrait/galleries.php?w=999" page
    When I hover over gallery image 1
    Then I see the gallery view control

  Scenario: Selecting an image opens the gallery viewer
    Given I am on the "portrait/galleries.php?w=999" page
    When I view gallery image 1
    Then I see gallery image 1 in the gallery viewer

  Scenario: Selecting a later image opens it in the gallery viewer
    Given I am on the "portrait/galleries.php?w=999" page
    When I view gallery image 6
    Then I see gallery image 6 in the gallery viewer

  Scenario: Gallery viewer does not automatically advance
    Given I am on the "portrait/galleries.php?w=999" page
    When I view gallery image 1
    And I wait for 5 seconds
    Then I see gallery image 1 in the gallery viewer

  Scenario: Gallery viewer opened by URL does not automatically advance
    Given I am on the "portrait/galleries.php?w=999#0" page
    When I wait for 5 seconds
    Then I see gallery image 1 in the gallery viewer

  Scenario: User can advance to the next image
    Given I am on the "portrait/galleries.php?w=999#0" page
    When I advance to the next gallery image
    Then I see gallery image 2 in the gallery viewer

  Scenario: User can advance to the previous image
    Given I am on the "portrait/galleries.php?w=999#0" page
    When I advance to the previous gallery image
    Then I see gallery image 16 in the gallery viewer

  Scenario: Gallery indicator can select an image
    Given I am on the "portrait/galleries.php?w=999#0" page
    When I skip to gallery image 7
    Then I see gallery image 7 in the gallery viewer

  Scenario: Images with captions display captions
    Given gallery 999 image 2 has caption "sample caption"
    When I am on the "portrait/galleries.php?w=999#1" page
    Then I see the gallery caption "sample caption" displayed

  Scenario: Images without captions do not display captions
    When I am on the "portrait/galleries.php?w=999#2" page
    Then I do not see any gallery captions

  Scenario: Opening an image sets the hash to that image
    Given I am on the "portrait/galleries.php?w=999" page
    When I view gallery image 3
    Then I am taken to the "portrait/galleries.php?w=999#2" page

  Scenario: Gallery URL hash opens the selected image
    When I am on the "portrait/galleries.php?w=999#1" page
    Then I see gallery image 2 in the gallery viewer

  Scenario: Advancing updates the gallery URL hash
    Given I am on the "portrait/galleries.php?w=999#1" page
    When I advance to the next gallery image
    Then I am taken to the "portrait/galleries.php?w=999#2" page

  Scenario: Going back updates the gallery URL hash
    Given I am on the "portrait/galleries.php?w=999#1" page
    When I advance to the previous gallery image
    Then I am taken to the "portrait/galleries.php?w=999#0" page

  Scenario: Closing the gallery viewer hides it
    Given I am on the "portrait/galleries.php?w=999#1" page
    When I close the gallery view
    Then I don't see the gallery viewer

  Scenario: Closing the gallery viewer clears the URL hash
    Given I am on the "portrait/galleries.php?w=999#1" page
    When I close the gallery view
    Then I am taken to the "portrait/galleries.php?w=999#" page

  Scenario: Selecting an indicator updates the URL hash
    Given I am on the "portrait/galleries.php?w=999#0" page
    When I skip to gallery image 7
    Then I am taken to the "portrait/galleries.php?w=999#6" page
