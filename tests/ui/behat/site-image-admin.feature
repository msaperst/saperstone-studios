@site-image-admin
Feature: Site Image Administration
  As an administrator
  I want to replace images used by public site content
  So that the website can be updated through the browser

  Background:
    Given I am logged in with admin credentials
    And I have backed up the Portraits site image
    And I am on the "index.php" page

  Scenario: Admin can access site image editing
    Then I see an edit control for the "Portraits" site image

  Scenario: Uploading a replacement image enters editing mode
    When I upload "flower.jpeg" for the "Portraits" site image
    Then I see the "Portraits" site image ready to save

  Scenario: Admin can save a replacement site image
    When I upload "flower.jpeg" for the "Portraits" site image
    And I save the "Portraits" site image
    Then I see the saved "Portraits" site image

  Scenario: Saved site image persists after reload
    When I upload "flower.jpeg" for the "Portraits" site image
    And I save the "Portraits" site image
    And I reload the page
    Then I see the saved "Portraits" site image after reload

  Scenario: Too-small replacement image shows a useful error
    When I try to upload "flower-small.jpeg" for the "Portraits" site image
    Then I see a site image upload error indicating the image is too small
    And I see the original "Portraits" site image unchanged
