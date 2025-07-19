@plugin @local_test_plugin
Feature: Test Plugin functionality
  In order to test the CI/CD pipeline
  As a user
  I need to be able to access and use the test plugin

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | admin    | Admin     | User     | admin@example.com   |
      | teacher  | Teacher   | User     | teacher@example.com |
      | student  | Student   | User     | student@example.com |

  @javascript
  Scenario: Admin can access test plugin page
    Given I log in as "admin"
    And I am on "Site administration"
    When I navigate to "Plugins" > "Local plugins" > "Test Plugin" in site administration
    Then I should see "Test Plugin"
    And I should see "Welcome to the Test Plugin!"

  @javascript
  Scenario: Teacher cannot access test plugin without capability
    Given I log in as "teacher"
    And I am on "Site administration"
    When I navigate to "Plugins" > "Local plugins" > "Test Plugin" in site administration
    Then I should see "Access denied"

  @javascript
  Scenario: Student cannot access test plugin
    Given I log in as "student"
    And I am on "Site administration"
    When I navigate to "Plugins" > "Local plugins" > "Test Plugin" in site administration
    Then I should see "Access denied"

  @javascript
  Scenario: Plugin page displays correctly
    Given I log in as "admin"
    And I am on "/local/test_plugin/index.php"
    Then I should see "Test Plugin"
    And I should see "Welcome to the Test Plugin!"
    And the page should contain the css ".local-test-plugin-page" 