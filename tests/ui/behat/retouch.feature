@retouch
Feature: Retouch
  As a user of the website
  I want to compare original and retouched images
  So that I can see the effect of Saperstone Studios retouching

  Background:
    Given I am on the wedding retouch page

  Scenario: Retouch instructions are displayed initially
    Then I see initial retouch instructions

  Scenario: All retouch examples are available
    Then I see thumbnails of each retouched image

  Scenario: Selecting an example displays its original image
    When I select the "1st" retouched thumbnail
    Then I see the "1st" original image
    And I see 0% of the "1st" retouched image

  Scenario: Selecting an example displays its description
    When I select the "1st" retouched thumbnail
    Then I see the "1st" image comment

  Scenario: Comparison slider can show equal original and retouched portions
    When I select the "1st" retouched thumbnail
    And I move the slider to 50%
    Then I see 50% of the "1st" retouched image

  Scenario: Comparison slider can show the full retouched image
    When I select the "1st" retouched thumbnail
    And I move the slider to 100%
    Then I see 100% of the "1st" retouched image

  Scenario: Selecting another example replaces the displayed image
    When I select the "1st" retouched thumbnail
    And I select the "2nd" retouched thumbnail
    Then I see the "2nd" original image
