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

  Scenario: Hovering an images zooms in
    When I hover over image 1
    Then I see the info icon on image 1

  Scenario: Clicking an image brings up a preview modal
    When I view image 1
    Then I see image 1 in the preview modal

  Scenario: Clicking another image brings up a preview modal
    When I view image 6
    Then I see image 6 in the preview modal

  Scenario: Modal automatically scrolls to the next image
    When I view image 1
    And I wait for 5 seconds
    Then I see image 2 in the preview modal

  Scenario: Able to manually advance to next image
    When I view image 1
    And I advance to the next image
    Then I see image 2 in the preview modal

  Scenario: Able to manually advance to previous image
    When I view image 1
    And I advance to the previous image
    Then I see image 16 in the preview modal

  Scenario: Indicator allows skipping to image
    When I view image 1
    And I skip to image 7
    Then I see image 7 in the preview modal

  Scenario: Images with captions display captions
  Scenario: Images without captions do not display captions