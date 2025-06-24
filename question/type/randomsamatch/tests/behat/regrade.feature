@qtype @qtype_randomsamatch @javascript
Feature: Regrading a quiz with a random short-answer matching question

  Scenario: Regrading a random short-answer matching question should not result in an error
    Given the following "users" exist:
      | username |
      | teacher  |
      | student  |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | Course 1  | 0        |
    And the following "course enrolments" exist:
      | user    | course   | role           |
      | teacher | Course 1 | editingteacher |
      | student | Course 1 | student        |
    And the following "activities" exist:
      | activity | name   | course   | idnumber |
      | quiz     | Quiz 1 | Course 1 | quiz1    |
    And the following "question categories" exist:
      | contextlevel    | reference | name       |
      | Activity module | quiz1     | Category 1 |
    And the following "questions" exist:
      | questioncategory | qtype       | name                    | template |
      | Category 1       | shortanswer | Short answer question A | frogtoad |
      | Category 1       | shortanswer | Short answer question B | frogtoad |
    And I log in as "teacher"
    And I add a "Random short-answer matching" question to the "Quiz 1" quiz with:
      | Question name | RASM question |
      | Question text | Test          |
      | Default mark  | 1.0           |
    And user "student" has started an attempt at quiz "Quiz 1" randomised as follows:
      | slot | actualquestion          |
      | 1    | Short answer question A |
    And I am on the "Quiz 1" "quiz activity" page logged in as teacher
    And I click on "Attempts: 1" "link"
    And I click on "Regrade attempts..." "button"
    And I click on "Dry run" "button"
    Then I should see "Regrade completed"
    But I should not see "The number of sub-questions has changed"
