
Feature: Test di prova

    Scenario: Prova del test
        Given I am on "/"
        When print last response
        And I fill in "test" with "test"
        Then print current URL
        And show last response
        And the "test" field should contain "test"
