@blog-admin
Feature: Blog Administration
  As an administrator
  I want to create and manage blog posts
  So that blog content can be prepared and maintained through the website

  Background:
    Given I am logged in with admin credentials

  Scenario: Admin can preview a new post and return to editing
    Given I am on the "blog/new.php" page
    When I enter "Behat Blog Admin Preview" as the blog administration title
    And I add blog administration text "Preview body content"
    And I preview the blog administration post
    Then I see the blog administration preview title "Behat Blog Admin Preview"
    And I see blog administration preview text "Preview body content"
    When I return to blog administration editing
    Then I see the blog administration editing controls

  Scenario: New posts require a title
    Given I am on the "blog/new.php" page
    When I try to save the blog administration draft
    Then I see the blog administration dialog message "Please enter a title for your post"

  Scenario: New posts require a preview image
    Given I am on the "blog/new.php" page
    When I enter "Behat Blog Admin Missing Preview" as the blog administration title
    And I try to save the blog administration draft
    Then I see the blog administration dialog message "Please select a preview image for your post"

  Scenario: Admin can create a draft with an uploaded preview, category, and text
    Given I am on the "blog/new.php" page
    When I enter "Behat Blog Admin Created Draft" as the blog administration title
    And I set the blog administration date to "2099-12-30"
    And I upload the blog administration test image
    And I select the uploaded image as the blog administration preview
    And I add the "Tea Ceremony" blog administration category
    And I add blog administration text "Created draft body content"
    And I save the blog administration draft
    Then the created blog administration post is stored as a draft
    And I see the created blog administration post

  Scenario: Admin can create a new blog category
    Given I am on the "blog/new.php" page
    When I create the blog administration category "Behat Blog Admin Category"
    Then I see the "Behat Blog Admin Category" blog administration category selected
    And the "Behat Blog Admin Category" blog category exists

  Scenario: Admin can update a draft in the full editor
    Given a blog administration draft exists
    And I am on the "blog/manage.php" page
    When I open the full editor for the blog administration draft
    And I change the blog administration draft title to "Behat Blog Admin Full Edit"
    And I change the blog administration draft text to "Updated full editor body"
    And I update the blog administration post
    Then the blog administration draft contains the full editor changes
    And I see the blog administration draft post

  Scenario: Admin can quick edit draft metadata
    Given a blog administration draft exists
    And I am on the "blog/manage.php" page
    When I quick edit the blog administration draft
    And I change the quick edit title to "Behat Blog Admin Quick Edit"
    And I update the quick edited blog administration post
    Then I see the quick edited blog administration post in the manage table
    And the quick edited blog administration post is persisted

  Scenario: Admin can delete a draft from the manage page
    Given a blog administration draft exists
    And I am on the "blog/manage.php" page
    When I quick edit the blog administration draft
    And I delete the blog administration draft
    And I confirm deletion of the blog administration draft
    Then I no longer see the blog administration draft in the manage table
    And the blog administration draft no longer exists
