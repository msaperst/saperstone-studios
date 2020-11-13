Feature: System Navigation
  As a user of the website
  I want to have a common navigation bar
  So that I can access data simply

  Scenario: Cookie and Privacy Policy displayed
    Given I haven't reviewed the cookie policy
    Then I am prompted to review the privacy policy

  Scenario: Accepted Cookie and Privacy Policy not displayed
    Given I have reviewed the cookie policy
    Then I am not prompted to review the privacy policy

  Scenario: Cookie and Privacy Policy not displayed on PP
    Given I haven't reviewed the cookie policy
    And I am on the "Privacy-Policy.php" page
    Then I am not prompted to review the privacy policy

  Scenario: Able to edit the Cookie and Privacy Policy settings
    Given I have reviewed the cookie policy
    And I am on the "Privacy-Policy.php" page
    When I edit the cookie options
    Then I am prompted to review the privacy policy

  Scenario: Resolution stats sent with cookies
    Given I have reviewed the cookie policy
    Then my resolution is logged

  Scenario: Resolution stats not sent without cookies
    Given I have cookies disabled
    Then my resolution is not logged

  Scenario: Able to search for blog with keyboard
    When I search for "test" blog posts by typing
    Then I see "test" blog posts

  Scenario: Able to search for blog with mouse
    When I search for "test" blog posts
    Then I see "test" blog posts

  Scenario: Able to login with keyboard
    Given an enabled user account exists
    When I log in to the site by typing
    Then I see my user name displayed

  Scenario: Able to login with mouse
    Given an enabled user account exists
    When I log in to the site
    Then I see my user name displayed

  Scenario: Album finder auto-shows
    When I append "#album" to my url
    Then I see the find album modal

  Scenario: Able to dismiss announcement banner
    Given there is an announcement
    When I dismiss the announcement
    Then I no longer see the announcement

  Scenario: Dismissed announcement banner stays dismissed
    Given there is an announcement
    When I dismiss the announcement
    And I reload the page
    Then I no longer see the announcement

  Scenario: Find album modal no save option
    When I try to search for an album
    Then I see the find album modal
    And I see that there is no option to save album

  Scenario: Find album modal save option
    Given an enabled user account exists
    And I am logged in with saved credentials
    And an album exists with code "good-code"
    When I search for and save album "good-code"
    Then I am taken to the "user/album.php?album=99999" page
    And I see a cookie with my album

  Scenario: Error message for bad album code
    When I search for album "bad-code"
    Then I see an error message indicating no album exists

  Scenario: Able to search for album
    Given an album exists with code "good-code"
    When I search for album "good-code"
    Then I am taken to the "user/album.php?album=99999" page

  Scenario: Able to search for album with keyboard
    Given an album exists with code "34567"
    When I search for album "34567" with keyboard
    Then I am taken to the "user/album.php?album=99999" page

  Scenario: Dynamic content starts collapsed
    Given I am on the "portrait/faq.php" page
    Then I see the "1st" content collapsed

  Scenario: Able to expand dynamic content
    Given I am on the "portrait/faq.php" page
    When I click the "1st" content header
    Then I see the "1st" content expanded

  Scenario: Able to collapse dynamic content
    Given I am on the "portrait/faq.php" page
    When I click the "1st" content header
    And I click the "1st" content header
    Then I see the "1st" content collapsed

  Scenario: Able to expand multiple dynamic content
    Given I am on the "portrait/faq.php" page
    When I click the "1st" content header
    And I click the "2nd" content header
    And I click the "3rd" content header
    And I click the "2nd" content header
    Then I see the "1st" content expanded
    And I see the "2nd" content collapsed
    And I see the "3rd" content expanded