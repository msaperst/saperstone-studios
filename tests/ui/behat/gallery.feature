@gallery
Feature: Gallery
  As a user
  I want to view gallery images
  So that I see samples of photos

  Scenario: Gallery images load
    Given I am on the "portrait/galleries.php?w=999" page
    Then I see the "1st" gallery images load
    
  Scenario: Gallery images keep loading
    Given I am on the "portrait/galleries.php?w=999" page
    When I scroll to the bottom of the page
    Then I see the "3rd" gallery images load

  Scenario: Hovering an images zooms in
  Scenario: Clicking an image brings up a preview modal
  Scenario: Modal automatically scrolls to the next image
  Scenario: Able to manually advance to next image
  Scenario: Able to manually advance to previous image
  Scenario: Indicator allows skipping to image
  Scenario: Images with captions display captions