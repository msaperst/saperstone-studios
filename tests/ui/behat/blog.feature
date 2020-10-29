Feature: Blog
  As a user
  I want to view blog posts and content
  So that I see latest pretty images

  Scenario: Full Blog Displayed
    Given I am on the blog page
    Then I see the "1st" blog post load

  Scenario: Blogs Keep Loading
    Given I am on the blog page
    When I scroll to the bottom of the page
    Then I see the "2nd" blog post load

  Scenario: Blog Previews Displayed
    Given I am on the blog posts page
    Then I see the "1st" blog previews load

  Scenario: Blog Previews Keep Loading
    Given I am on the blog posts page
    When I scroll to the bottom of the page
    Then I see the "3rd" blog previews load

  Scenario: All Blog Categories are displayed
    Given I am on the blog categories page
    Then I see all of the categories displayed

  Scenario: Full Category Blog Displayed
    Given I am on the blog category 29 page
    Then I see the "1st" blog post load

  Scenario: Category Blogs Keep Loading
    Given I am on the blog category 29 page
    When I scroll to the bottom of the page
    Then I see the "2nd" blog post load

  Scenario: Search Blog Previews Displayed
    Given I am on the "blog/search.php?s=sample" page
    Then I see the "1st" blog previews load

  Scenario: Search Blog Previews Keep Loading
    Given I am on the "blog/search.php?s=sample" page
    When I scroll to the bottom of the page
    Then I see the "2nd" blog previews load

  Scenario: Full Blog Post Displayed
    Given I am on the "blog/post.php?p=999" page
    Then I see the full blog post

  Scenario: Full Blog Post View Comments
    Given I am on the "blog/post.php?p=999" page
    Then I see the blog post's comments

  Scenario: Only message required to leave comment
    Given I am on the "blog/post.php?p=999" page
    When I try to leave the comment "This is a great post"
    Then the submit comment button is enabled

  Scenario Outline: Can't leave a comment with curseword
    Given I am on the "blog/post.php?p=999" page
    When I try to leave the comment "<comment>"
    And the submit comment button is disabled
    Examples:
      | comment                      |
      | fuck this page               |
      | your a motherfucker          |
      | shove your face in a cuntpie |
      | don't be a bitch             |
      | bitches need stiches         |

  Scenario: Anonymous user can leave comment
    Given I am on the "blog/post.php?p=999" page
    When I leave the comment "This is a great post"
    Then I see the blog post's comments

  Scenario: User can leave comment
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I am on the "blog/post.php?p=999" page
    When I leave the comment "This is a great post"
    Then I see the blog post's comments

  Scenario: Anonymous User Can't Delete Comment
    Given I am on the "blog/post.php?p=999" page
    Then I can not delete the "1st" comment
    And I can not delete the "2nd" comment

  Scenario: Anonymous User Can't Delete Own Comment
    Given I am on the "blog/post.php?p=999" page
    When I leave the comment "This is a great post"
    Then I can not delete the "1st" comment

  Scenario: User Can't Delete Comment
    Given I am on the "blog/post.php?p=999" page
    Then I can not delete the "1st" comment
    And I can not delete the "2nd" comment

  Scenario: User Can Delete Own Comment
    Given an enabled user account exists
    And I am logged in with saved credentials
    And I have left the comment "This is a great post" on blog 999
    And I am on the "blog/post.php?p=999" page
    When I delete the "1st" comment
    Then I see the blog post's comments

  Scenario: Admin Can Delete Any Comment
    Given I am logged in with admin credentials
    And I am on the "blog/post.php?p=999" page
    When I delete the "2nd" comment
    Then I see the blog post's comments

  Scenario: User Can Delete Own New Comment
    Given an enabled user account exists
    And I am logged in with saved credentials
    Given I am on the "blog/post.php?p=999" page
    When I leave the comment "This is a great post"
    And I delete the "1st" comment
    Then I see the blog post's comments
