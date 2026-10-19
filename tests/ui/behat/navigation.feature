@navigation
Feature: System Navigation
  As a user of the website
  I want consistent site navigation
  So that I can reach site features easily

  Scenario: Cookie preferences are requested before consent
    Given I haven't reviewed the cookie policy
    Then I am prompted to review the privacy policy

  Scenario: Cookie preferences are not requested after consent
    Given I have reviewed the cookie policy
    Then I am not prompted to review the privacy policy

  Scenario: Cookie preferences are not prompted on the privacy policy page
    Given I haven't reviewed the cookie policy
    And I am on the "Privacy-Policy.php" page
    Then I am not prompted to review the privacy policy

  Scenario: Cookie preferences can be reopened from the privacy policy page
    Given I have reviewed the cookie policy
    And I am on the "Privacy-Policy.php" page
    When I edit the cookie options
    Then I am prompted to review the privacy policy

  Scenario: Blog search works with the keyboard
    When I search for "test" blog posts by typing
    Then I see "test" blog posts

  Scenario: Blog search works with the mouse
    When I search for "test" blog posts
    Then I see "test" blog posts

  Scenario: Login works with the keyboard
    Given an enabled user account exists
    When I log in to the site by typing
    Then I see my user name displayed

  Scenario: Login works with the mouse
    Given an enabled user account exists
    When I log in to the site
    Then I see my user name displayed

  Scenario: Album finder opens from the URL hash
    When I append "#album" to my url
    Then I see the find album modal

  Scenario: Announcement can be dismissed
    Given there is an announcement
    When I dismiss the announcement
    Then I no longer see the announcement

  Scenario: Dismissed announcement stays dismissed after reload
    Given there is an announcement
    When I dismiss the announcement
    And I reload the page
    Then I no longer see the announcement

  Scenario: Guest album finder does not offer to save an album
    When I try to search for an album
    Then I see the find album modal
    And I see that there is no option to save album

  Scenario: Logged-in user can find and save an album
    Given an enabled user account exists
    And I am logged in with saved credentials
    And album 99999 exists with code "good-code"
    When I search for and save album "good-code"
    Then I am taken to the "user/album.php?album=99999" page
    And I see a cookie with album 99999

  Scenario: Album finder requires a code
    When I search for album ""
    Then I see an error message indicating album code required

  Scenario: Album finder rejects an unknown code
    When I search for album "bad-code"
    Then I see an error message indicating no album exists

  Scenario: Guest can find an album by code
    Given album 99999 exists with code "good-code"
    When I search for album "good-code"
    Then I am taken to the "user/album.php?album=99999" page

  Scenario: Album finder works with the keyboard
    Given album 99999 exists with code "34567"
    When I search for album "34567" with keyboard
    Then I am taken to the "user/album.php?album=99999" page

  Scenario: FAQ content starts collapsed
    Given I am on the "portrait/faq.php" page
    Then I see the "1st" content collapsed

  Scenario: FAQ content can be expanded
    Given I am on the "portrait/faq.php" page
    When I click the "1st" content header
    Then I see the "1st" content expanded

  Scenario: FAQ content can be collapsed
    Given I am on the "portrait/faq.php" page
    When I click the "1st" content header
    And I click the "1st" content header
    Then I see the "1st" content collapsed

  Scenario: FAQ sections expand independently
    Given I am on the "portrait/faq.php" page
    When I click the "1st" content header
    And I click the "2nd" content header
    And I click the "3rd" content header
    And I click the "2nd" content header
    Then I see the "1st" content expanded
    And I see the "2nd" content collapsed
    And I see the "3rd" content expanded
