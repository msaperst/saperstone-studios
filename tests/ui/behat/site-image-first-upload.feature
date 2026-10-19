@site-image-admin
Feature: First Site Image Upload
  As an administrator
  I want to upload the first image for a site section
  So that a fresh installation can be populated through the browser

  Background:
    Given I am logged in with admin credentials

  Scenario: Admin can upload the first image for an empty nested site section
    Given the B'nai Mitzvah "Details" site image has not been uploaded yet
    And I am on the "b-nai-mitzvah/index.php" page
    When I upload "flower.jpeg" for the first "Details" site image
    Then I see the first "Details" site image ready to save
