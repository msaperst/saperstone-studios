@blog
Feature: Blog
  As a user
  I want to view blog posts and content
  So that I can see the latest photography and updates

  Background:
    Given blog 2031 exists
    And blog 2032 exists
    And blog 2033 exists
    And blog 2034 exists
    And blog 2035 exists
    And blog 2036 exists
    And blog 2037 exists
    And blog 2038 exists
    And blog 2039 exists

  Scenario: Latest blog post is displayed
    Given I am on the blog page
    Then I see the "1st" blog post load

  Scenario: More blog posts load while scrolling
    Given I am on the blog page
    When I scroll to the bottom of the page
    Then I see the "2nd" blog post load

  Scenario: Blog previews are displayed
    Given I am on the blog posts page
    Then I see the "1st" blog previews load

  Scenario: More blog previews load while scrolling
    Given I am on the blog posts page
    When I scroll to the bottom of the page
    Then I see the "3rd" blog previews load

  Scenario: All blog categories are displayed
    Given I am on the blog categories page
    Then I see all of the categories displayed

  Scenario: Latest post in a category is displayed
    Given I am on the blog category 29 page
    Then I see the "1st" blog post load

  Scenario: More category posts load while scrolling
    Given I am on the blog category 29 page
    When I scroll to the bottom of the page
    Then I see the "2nd" blog post load

  Scenario: Blog search results are displayed
    Given I am on the blog search page for "sample"
    Then I see the "1st" blog previews load

  Scenario: More blog search results load while scrolling
    Given I am on the blog search page for "sample"
    When I scroll to the bottom of the page
    Then I see the "2nd" blog previews load

  Scenario: Full blog post is displayed
    Given I am on the "blog/post.php?p=2039" page
    Then I see the full blog post

  Scenario: Blog post comments are displayed
    Given I am on the "blog/post.php?p=2039" page
    Then I see the blog post's comments

  Scenario: Only a message is required to leave a comment
    Given I am on the "blog/post.php?p=2039" page
    When I try to leave the comment "This is a great post"
    Then the submit comment button is enabled

  Scenario Outline: Comments containing blocked words cannot be submitted
    Given I am on the "blog/post.php?p=2039" page
    When I try to leave the comment "<comment>"
    Then the submit comment button is disabled
    Examples:
      | comment                      |
      | fuck this page               |
      | your a motherfucker          |
      | shove your face in a cuntpie |
      | don't be a bitch             |
      | bitches need stitches        |

  Scenario: Anonymous user can leave comment
    Given I am on the "blog/post.php?p=2039" page
    When I leave the comment "This is a great post"
    Then I see the comment "This is a great post"

  Scenario: New blog comment persists after reload
    Given I am on the "blog/post.php?p=2039" page
    When I leave the comment "This is a great post"
    And I reload the page
    Then I see the comment "This is a great post"

  Scenario: User can leave comment
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the "blog/post.php?p=2039" page
    When I leave the comment "This is a great post"
    Then I see the comment "This is a great post"

  Scenario: Anonymous user cannot delete existing comments
    Given I am on the "blog/post.php?p=2039" page
    Then I can not delete the "1st" comment
    And I can not delete the "2nd" comment

  Scenario: Anonymous user cannot delete their own comment
    Given I am on the "blog/post.php?p=2039" page
    When I leave the comment "This is a great post"
    Then I can not delete the "1st" comment

  Scenario: User cannot delete another user's comment
    Given I am on the "blog/post.php?p=2039" page
    Then I can not delete the "1st" comment
    And I can not delete the "2nd" comment

  Scenario: User can delete their own comment
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I have left the comment "This is a great post" on blog 2039
    And I am on the "blog/post.php?p=2039" page
    When I delete the "1st" comment
    Then I do not see the comment "This is a great post"

  Scenario: Deleted blog comment remains deleted after reload
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I have left the comment "This is a great post" on blog 2039
    And I am on the "blog/post.php?p=2039" page
    When I delete the "1st" comment
    And I reload the page
    Then I do not see the comment "This is a great post"

  Scenario: Admin can delete any comment
    Given I am logged in with admin credentials
    And I am on the "blog/post.php?p=2039" page
    When I delete the "2nd" comment
    Then I do not see the comment "hehehehehe this rules!"

  Scenario: User can delete their own new comment
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the "blog/post.php?p=2039" page
    When I leave the comment "This is a great post"
    And I delete the "1st" comment
    Then I do not see the comment "This is a great post"
