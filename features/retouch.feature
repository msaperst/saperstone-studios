Feature: System Administration
  As a user of the website
  I want to see image transformations
  So that I can be wowed by Leigh Ann's skill

  Scenario: Instructions initially displayed
    Given I am on the wedding retouch page
    Then I see initial retouch instructions

  Scenario: All retouch images displayed
    Given I am on the wedding retouch page
    Then I see thumbnails of each retouched image

  Scenario: Selecting a thumbnail displays the original image
    Given I am on the wedding retouch page
    When I select the "1st" retouched thumbnail
    Then I see the "1st" original image
    Then I see 0% of the "1st" retouched image

  Scenario: Selecting a thumbnail displays the comment
    Given I am on the wedding retouch page
    When I select the "1st" retouched thumbnail
    Then I see the "1st" image comment

  Scenario: Dragging the slider halfway displays half original, half retouched
    Given I am on the wedding retouch page
    And I select the "1st" retouched thumbnail
    When I move the slider to 50%
    Then I see 50% of the "1st" retouched image

  Scenario: Dragging the slider fully displays the retouched image
    Given I am on the wedding retouch page
    And I select the "1st" retouched thumbnail
    When I move the slider to 100%
    Then I see 100% of the "1st" retouched image

  Scenario: Able to replace displayed image
    Given I am on the wedding retouch page
    And I select the "1st" retouched thumbnail
    When I select the "2nd" retouched thumbnail
    Then I see the "2nd" original image
