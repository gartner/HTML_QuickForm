<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Bertrand Mansion <bmansion@mamasam.com>                     |
// +----------------------------------------------------------------------+
//
// $Id$

require_once('HTML/QuickForm/Rule.php');

/**
* Validates values using callback functions or methods
* @version     1.0
*/
class HTML_QuickForm_Rule_Callback extends HTML_QuickForm_Rule
{
    /**
     * Array of callbacks
     *
     * Array is in the format:
     * $_data['rulename'] = array('functionname', 'classname');
     * If the callback is not a method, then the class name is not set.
     *
     * @var     array
     * @access  private
     */
    var $_data = array();

   /**
    * Whether to use BC mode for specific rules
    * 
    * Previous versions of QF passed element's name as a first parameter
    * to validation functions, but not to validation methods. This behaviour
    * is emulated if you are using 'function' as rule type when registering.
    * 
    * @var array
    * @access private
    */
    var $_BCMode = array();

    /**
     * Validates a value using a callback
     *
     * @param     string    $value      Value to be checked
     * @param     mixed     $options    Options for callback
     * @access    public
     * @return    boolean   true if value is valid
     */
    function validate($value, $options = null)
    {
        if (isset($this->_data[$this->name])) {
            $callback = $this->_data[$this->name];
            if (isset($callback[1])) {
                return call_user_func(array($callback[1], $callback[0]), $value, $options);
            } elseif ($this->_BCMode[$this->name]) {
                return $callback[0]('', $value, $options);
            } else {
                return $callback[0]($value, $options);
            }
        } elseif (is_callable($options)) {
            return call_user_func($options, $value);
        } else {
            return true;
        }
    } // end func validate

    /**
     * Adds new callbacks to the callbacks list
     *
     * @param     string    $name       Name of rule
     * @param     string    $callback   Name of function or method
     * @param     string    $class      Name of class containing the method
     * @param     bool      $BCMode     Backwards compatibility mode 
     * @access    public
     */
    function addData($name, $callback, $class = null, $BCMode = false)
    {
        if (!empty($class)) {
            $this->_data[$name] = array($callback, $class);
        } else {
            $this->_data[$name] = array($callback);
        }
        $this->_BCMode[$name] = $BCMode;
    } // end func addData

    /**
     * Returns the javascript test
     *
     * @param     string    $jsValue    JS code to find the element value
     * @param     string    $jsField    Element name in the form
     * @param     string    $jsMessage  Error message encoded for javascript
     * @param     string    $jsReset    JS code to revert the value back to default if error
     * @param     mixed     $options    Options for this rule, not used yet
     * @access    public
     * @return    string    javascript code
     */
    function getValidationScript($jsValue, $jsField, $jsMessage, $jsReset, $options = null)
    {
        if (isset($this->_data[$this->name])) {
            $callback = $this->_data[$this->name][0];
            $params   = $this->_BCMode[$this->name]? "'{$jsField}', value": 'value';
        } else {
            $callback = is_array($options)? $options[1]: $options;
            $params   = 'value';
        }
        $js = "$jsValue\n" .
              "  if (value != '' && !{$callback}({$params}) && !errFlag['$jsField']) {\n" .
              "    errFlag['$jsField'] = true;\n" .
              "    _qfMsg = _qfMsg + '\\n - $jsMessage';\n" .
              $jsReset .
              "  }\n";
        return $js;
    } // end func getValidationScript

} // end class HTML_QuickForm_Rule_Callback
?>