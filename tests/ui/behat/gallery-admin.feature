@gallery-admin
Feature: Gallery Administration
  As an administrator
  I want to manage public galleries
  So that gallery content can be maintained through the website

  Background:
    Given I am logged in with admin credentials
    And gallery 999 exists with 4 images
    And I am on the "portrait/galleries.php?w=999" page

  Scenario: Admin can update gallery details
    When I rename the gallery to "Updated Gallery"
    And I reload the page
    Then I see the gallery heading "Updated Gallery Gallery"

  Scenario: Admin can update gallery image metadata
    When I view gallery image 1
    And I update the current gallery image title to "Updated Image" and caption to "Updated caption"
    And I reload the page
    And I view gallery image 1
    Then I see the current gallery image title "Updated Image" and caption "Updated caption"

  Scenario: Admin can delete a gallery image
    When I view gallery image 1
    And I delete the current gallery image
    And I reload the page
    Then I see 3 gallery images

  Scenario: Admin can reorder gallery images
    When I begin rearranging gallery images
    And I move gallery image 1 after gallery image 4
    And I save the gallery image order
    And I reload the page
    Then I see the reordered gallery image order

  Scenario: Admin can upload an image to a gallery
    When I open the gallery for editing
    And I upload the gallery test image
    Then I see 5 gallery images
    And I see the uploaded gallery image
    When I reload the page
    Then I see 5 gallery images
    And I see the uploaded gallery image
