<?php
use Behat\Behat\Context\BehatContext,
    Behat\Behat\Event\SuiteEvent;
 
class FeatureContext extends Behat\MinkExtension\Context\MinkContext
{
    /**
     * Verifica il valore in un particolare input
     * Example: When I follow "Log In"
     * Example: And I follow "Log In"
     *
     * @Then /^field "(?P<field>(?:[^"]|\\")*)" has value "(?P<value>(?:[^"]|\\")*)"$/
     */
    public function fieldHasValue($field, $value)
    {
        $value_form = $this->getSession()->evaluateScript("return form['{$field}'].value");
        if ($value_form != $value) {
            throw new Exception("Valori diversi : [[$value_form}] [{$value}]");
        }
    }
}