@gallery
Feature: Gallery
  As a user
  I want to view gallery images
  So that I see samples of photos

  Background:
    Given I am on the "portrait/galleries.php?w=999" page

  Scenario: Gallery images load
    Then I see the "1st" gallery images load
    
  Scenario: Gallery images keep loading
    When I scroll to the bottom of the page
    Then I see the "3rd" gallery images load

  Scenario: Hovering an image zooms in
    When I hover over gallery image 1
    Then I see the info icon on gallery image 1

  Scenario: Clicking an image brings up a preview modal
    When I view gallery image 1
    Then I see gallery image 1 in the preview modal

  Scenario: Clicking another image brings up a preview modal
    When I view gallery image 6
    Then I see gallery image 6 in the preview modal

  Scenario: Modal does not automatically scroll to the next image
    When I view gallery image 1
    And I wait for 5 seconds
    Then I see gallery image 1 in the preview modal

  Scenario: Able to manually advance to next image
    When I view gallery image 1
    And I advance to the next gallery image
    Then I see gallery image 2 in the preview modal

  Scenario: Able to manually advance to previous image
    When I view gallery image 1
    And I advance to the previous gallery image
    Then I see gallery image 16 in the preview modal

  Scenario: Indicator allows skipping to image
    When I view gallery image 1
    And I skip to gallery image 7
    Then I see gallery image 7 in the preview modal

  Scenario: Images with captions display captions
    When I view gallery image 2
    Then I see the gallery caption "sample caption" displayed

  Scenario: Images without captions do not display captions
    When I view gallery image 3
    Then I do not see any gallery captions