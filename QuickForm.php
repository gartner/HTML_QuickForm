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
// | Authors: Adam Daniel <adaniel1@eesus.jnj.com>                        |
// |          Bertrand Mansion <bmansion@mamasam.com>                     |
// +----------------------------------------------------------------------+
//
// $Id$

require_once('PEAR.php');
require_once('HTML/Common.php');

$GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'] = 
        array(
            'group'         =>array('HTML/QuickForm/group.php','HTML_QuickForm_group'),
            'hidden'        =>array('HTML/QuickForm/hidden.php','HTML_QuickForm_hidden'),
            'reset'         =>array('HTML/QuickForm/reset.php','HTML_QuickForm_reset'),
            'checkbox'      =>array('HTML/QuickForm/checkbox.php','HTML_QuickForm_checkbox'),
            'file'          =>array('HTML/QuickForm/file.php','HTML_QuickForm_file'),
            'image'         =>array('HTML/QuickForm/image.php','HTML_QuickForm_image'),
            'password'      =>array('HTML/QuickForm/password.php','HTML_QuickForm_password'),
            'radio'         =>array('HTML/QuickForm/radio.php','HTML_QuickForm_radio'),
            'button'        =>array('HTML/QuickForm/button.php','HTML_QuickForm_button'),
            'submit'        =>array('HTML/QuickForm/submit.php','HTML_QuickForm_submit'),
            'select'        =>array('HTML/QuickForm/select.php','HTML_QuickForm_select'),
            'hiddenselect'  =>array('HTML/QuickForm/hiddenselect.php','HTML_QuickForm_hiddenselect'),
            'text'          =>array('HTML/QuickForm/text.php','HTML_QuickForm_text'),
            'textarea'      =>array('HTML/QuickForm/textarea.php','HTML_QuickForm_textarea'),
            'link'          =>array('HTML/QuickForm/link.php','HTML_QuickForm_link'),
            'advcheckbox'   =>array('HTML/QuickForm/advcheckbox.php','HTML_QuickForm_advcheckbox'),
            'date'          =>array('HTML/QuickForm/date.php','HTML_QuickForm_date'),
            'static'        =>array('HTML/QuickForm/static.php','HTML_QuickForm_static'),
            'header'        =>array('HTML/QuickForm/header.php', 'HTML_QuickForm_header'),
            'html'          =>array('HTML/QuickForm/html.php', 'HTML_QuickForm_html'),
            'hierselect'    =>array('HTML/QuickForm/hierselect.php', 'HTML_QuickForm_hierselect'),
            'autocomplete'  =>array('HTML/QuickForm/autocomplete.php', 'HTML_QuickForm_autocomplete')
        );

$GLOBALS['_HTML_QuickForm_registered_rules'] = array(
    'required'      => array('html_quickform_rule_required', 'HTML/QuickForm/Rule/Required.php'),
    'maxlength'     => array('html_quickform_rule_range',    'HTML/QuickForm/Rule/Range.php'),
    'minlength'     => array('html_quickform_rule_range',    'HTML/QuickForm/Rule/Range.php'),
    'rangelength'   => array('html_quickform_rule_range',    'HTML/QuickForm/Rule/Range.php'),
    'email'         => array('html_quickform_rule_email',    'HTML/QuickForm/Rule/Email.php'),
    'regex'         => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
    'lettersonly'   => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
    'alphanumeric'  => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
    'numeric'       => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
    'nopunctuation' => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
    'nonzero'       => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
    'callback'      => array('html_quickform_rule_callback', 'HTML/QuickForm/Rule/Callback.php'),
    'compare'       => array('html_quickform_rule_compare',  'HTML/QuickForm/Rule/Compare.php')
);

// {{{ error codes

/*
 * Error codes for the QuickForm interface, which will be mapped to textual messages
 * in the QuickForm::errorMessage() function.  If you are to add a new error code, be
 * sure to add the textual messages to the QuickForm::errorMessage() function as well
 */

define('QUICKFORM_OK',                      1);
define('QUICKFORM_ERROR',                  -1);
define('QUICKFORM_INVALID_RULE',           -2);
define('QUICKFORM_NONEXIST_ELEMENT',       -3);
define('QUICKFORM_INVALID_FILTER',         -4);
define('QUICKFORM_UNREGISTERED_ELEMENT',   -5);
define('QUICKFORM_INVALID_ELEMENT_NAME',   -6);
define('QUICKFORM_INVALID_PROCESS',        -7);
define('QUICKFORM_DEPRECATED',             -8);

// }}}

/**
* Create, validate and process HTML forms
*
* @author      Adam Daniel <adaniel1@eesus.jnj.com>
* @author      Bertrand Mansion <bmansion@mamasam.com>
* @version     2.0
* @since       PHP 4.0.3pl1
*/
class HTML_QuickForm extends HTML_Common {
    // {{{ properties

    /**
     * Array containing the form fields
     * @since     1.0
     * @var  array
     * @access   private
     */
    var $_elements = array();

    /**
     * Array containing element name to index map
     * @since     1.1
     * @var  array
     * @access   private
     */
    var $_elementIndex = array();

    /**
     * Array containing indexes of duplicate elements
     * @since     2.10
     * @var  array
     * @access   private
     */
    var $_duplicateIndex = array();

    /**
     * Array containing required field IDs
     * @since     1.0
     * @var  array
     * @access   private
     */ 
    var $_required = array();

    /**
     * Prefix message in javascript alert if error
     * @since     1.0
     * @var  string
     * @access   public
     */ 
    var $_jsPrefix = 'Invalid information entered.';

    /**
     * Postfix message in javascript alert if error
     * @since     1.0
     * @var  string
     * @access   public
     */ 
    var $_jsPostfix = 'Please correct these fields.';

    /**
     * Array of default form values
     * @since     2.0
     * @var  array
     * @access   private
     */
    var $_defaultValues = array();

    /**
     * Array of constant form values
     * @since     2.0
     * @var  array
     * @access   private
     */
    var $_constantValues = array();

    /**
     * Array of submitted form values
     * @since     1.0
     * @var  array
     * @access   private
     */
    var $_submitValues = array();

    /**
     * Array of submitted form files
     * @since     1.0
     * @var  integer
     * @access   public
     */
    var $_submitFiles = array();

    /**
     * Value for maxfilesize hidden element if form contains file input
     * @since     1.0
     * @var  integer
     * @access   public
     */
    var $_maxFileSize = 1048576; // 1 Mb = 1048576

    /**
     * Flag to know if all fields are frozen
     * @since     1.0
     * @var  boolean
     * @access   private
     */
    var $_freezeAll = false;

    /**
     * Array containing the form rules
     * @since     1.0
     * @var  array
     * @access   private
     */
    var $_rules = array();

    /**
     * Form rules, global variety
     * @var     array
     * @access  private
     */
    var $_formRules = array();

    /**
     * Array containing the validation errors
     * @since     1.0
     * @var  array
     * @access   private
     */
    var $_errors = array();

    /**
     * Note for required fields in the form
     * @var       string
     * @since     1.0
     * @access    public
     */
    var $_requiredNote = '<span style="font-size:80%; color:#ff0000;">*</span><span style="font-size:80%;"> denotes required field</span>';

    // }}}
    // {{{ constructor

    /**
     * Class constructor
     * @param    string      $formName          Form's name.
     * @param    string      $method            (optional)Form's method defaults to 'POST'
     * @param    string      $action            (optional)Form's action
     * @param    string      $target            (optional)Form's target defaults to '_self'
     * @param    mixed       $attributes        (optional)Extra attributes for <form> tag
     * @param    bool        $trackSubmit       (optional)Whether to track if the form was submitted by adding a special hidden field
     * @access   public
     */
    function HTML_QuickForm($formName='', $method='post', $action='', $target='_self', $attributes=null, $trackSubmit = false)
    {
        HTML_Common::HTML_Common($attributes);
        $method = (strtoupper($method) == 'GET') ? 'get' : 'post';
        $action = ($action == '') ? $_SERVER['PHP_SELF'] : $action;
        $target = (empty($target) || $target == '_self') ? array() : array('target' => $target);
        $attributes = array('action'=>$action, 'method'=>$method, 'name'=>$formName, 'id'=>$formName) + $target;
        $this->updateAttributes($attributes);
        if (!$trackSubmit || isset($_REQUEST['_qf__' . $formName])) {
            if (1 == get_magic_quotes_gpc()) {
                $this->_submitValues = $this->_recursiveFilter('stripslashes', 'get' == $method? $_GET: $_POST);
            } else {
                $this->_submitValues = 'get' == $method? $_GET: $_POST;
            }
            $this->_submitFiles =& $_FILES;
        }
        if ($trackSubmit) {
            unset($this->_submitValues['_qf__' . $formName]);
            $this->addElement('hidden', '_qf__' . $formName, null);
        }
    } // end constructor

    // }}}
    // {{{ apiVersion()

    /**
     * Returns the current API version
     *
     * @since     1.0
     * @access    public
     * @return    float
     */
    function apiVersion()
    {
        return 3.0;
    } // end func apiVersion

    // }}}
    // {{{ registerElementType()

    /**
     * Registers a new element type
     *
     * @param     string    $typeName   Name of element type
     * @param     string    $include    Include path for element type
     * @param     string    $className  Element class name
     * @since     1.0
     * @access    public
     * @return    void
     */
    function registerElementType($typeName, $include, $className)
    {
        $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][strtolower($typeName)] = array($include, $className);
    } // end func registerElementType

    // }}}
    // {{{ registerRule()

    /**
     * Registers a new validation rule
     *
     * @param     string    $ruleName   Name of validation rule
     * @param     string    $type       Either: 'regex', 'function' or 'rule' for an HTML_QuickForm_Rule object
     * @param     string    $data1      Name of function, regular expression or HTML_QuickForm_Rule classname
     * @param     string    $data2      Object parent of above function or HTML_QuickForm_Rule file path
     * @since     1.0
     * @access    public
     * @return    void
     */
    function registerRule($ruleName, $type, $data1, $data2 = null)
    {
        include_once('HTML/QuickForm/RuleRegistry.php');
        $registry =& HTML_QuickForm_RuleRegistry::singleton();
        $registry->registerRule($ruleName, $type, $data1, $data2);
    } // end func registerRule

    // }}}
    // {{{ elementExists()

    /**
     * Returns true if element is in the form
     *
     * @param     string   $element         form name of element to check
     * @since     1.0
     * @access    public
     * @return    boolean
     */
    function elementExists($element=null)
    {
        return isset($this->_elementIndex[$element]);
    } // end func elementExists

    // }}}
    // {{{ setDefaults()

    /**
     * Initializes default form values
     *
     * @param     array    $defaultValues       values used to fill the form
     * @param     mixed    $filter              (optional) filter(s) to apply to all default values
     * @since     1.0
     * @access    public
     * @return    void
     */
    function setDefaults($defaultValues=null, $filter=null)
    {
        if (is_array($defaultValues)) {
            if (isset($filter)) {
                if (is_array($filter) && (2 != count($filter) || !is_callable($filter))) {
                    foreach ($filter as $val) {
                        if (!is_callable($val)) {
                            return PEAR::raiseError(null, QUICKFORM_INVALID_FILTER, null, E_USER_WARNING, "Callback function does not exist in QuickForm::setDefaults()", 'HTML_QuickForm_Error', true);
                        } else {
                            $defaultValues = $this->_recursiveFilter($val, $defaultValues);
                        }
                    }
                } elseif (!is_callable($filter)) {
                    return PEAR::raiseError(null, QUICKFORM_INVALID_FILTER, null, E_USER_WARNING, "Callback function does not exist in QuickForm::setDefaults()", 'HTML_QuickForm_Error', true);
                } else {
                    $defaultValues = $this->_recursiveFilter($filter, $defaultValues);
                }
            }
            $this->_defaultValues = array_merge($this->_defaultValues, $defaultValues);
            foreach (array_keys($this->_elements) as $key) {
                $this->_elements[$key]->onQuickFormEvent('updateValue', null, $this);
            }
        }
    } // end func setDefaults

    // }}}
    // {{{ setConstants()

    /**
     * Initializes constant form values.
     * These values won't get overridden by POST or GET vars
     *
     * @param     array   $constantValues        values used to fill the form    
     * @param     mixed    $filter              (optional) filter(s) to apply to all default values    
     *
     * @since     2.0
     * @access    public
     * @return    void
     */
    function setConstants($constantValues=null, $filter=null)
    {
        if (is_array($constantValues)) {
            if (isset($filter)) {
                if (is_array($filter) && (2 != count($filter) || !is_callable($filter))) {
                    foreach ($filter as $val) {
                        if (!is_callable($val)) {
                            return PEAR::raiseError(null, QUICKFORM_INVALID_FILTER, null, E_USER_WARNING, "Callback function does not exist in QuickForm::setConstants()", 'HTML_QuickForm_Error', true);
                        } else {
                            $constantValues = $this->_recursiveFilter($val, $constantValues);
                        }
                    }
                } elseif (!$this->is_callable($filter)) {
                    return PEAR::raiseError(null, QUICKFORM_INVALID_FILTER, null, E_USER_WARNING, "Callback function does not exist in QuickForm::setConstants()", 'HTML_QuickForm_Error', true);
                } else {
                    $constantValues = $this->_recursiveFilter($filter, $constantValues);
                }
            }
            $this->_constantValues = array_merge($this->_constantValues, $constantValues);
            foreach (array_keys($this->_elements) as $key) {
                $this->_elements[$key]->onQuickFormEvent('updateValue', null, $this);
            }
        }
    } // end func setConstants

    // }}}
    // {{{ moveUploadedFile()

    /**
     * Moves an uploaded file into the destination (DEPRECATED)
     * @param    string  $element       Element name
     * @param    string  $dest          Destination directory path
     * @param    string  $fileName      (optional) New file name
     * @since    1.0
     * @access   public
     * @deprecated  Use HTML_QuickForm_file::moveUploadedFile() method
     */
    function moveUploadedFile($element, $dest, $fileName='')
    {
        PEAR::raiseError(null, QUICKFORM_DEPRECATED, null, E_USER_WARNING, "Method HTML_QuickForm::moveUploadedFile() is now deprecated in file ".$_SERVER['PHP_SELF'], 'HTML_QuickForm_Error', true);

        $file =& $this->_submitFiles[$element];
        if ($dest != ''  && substr($dest, -1) != '/')
            $dest .= '/';
        $fileName = ($fileName != '') ? $fileName : $file['name'];
        if (move_uploaded_file($file['tmp_name'], $dest . $fileName)) {
            return true;
        } else {
            return false;
        }
    } // end func moveUploadedFile
    
    // }}}
    // {{{ setMaxFileSize()

    /**
     * Sets the value of MAX_FILE_SIZE hidden element
     *
     * @param     int    $bytes    Size in bytes
     * @since     3.0
     * @access    public
     * @return    void
     */
    function setMaxFileSize($bytes = 0)
    {
        if ($bytes > 0) {
            $this->_maxFileSize = $bytes;
        }
        if (!$this->elementExists('MAX_FILE_SIZE')) {
            $this->addElement('hidden', 'MAX_FILE_SIZE', $this->_maxFileSize);
        } else {
            $el =& $this->getElement('MAX_FILE_SIZE');
            $el->updateAttributes(array('value' => $this->_maxFileSize));
        }
    } // end func setMaxFileSize

    // }}}
    // {{{ getMaxFileSize()

    /**
     * Returns the value of MAX_FILE_SIZE hidden element
     *
     * @since     3.0
     * @access    public
     * @return    int   max file size in bytes
     */
    function getMaxFileSize()
    {
        return $this->_maxFileSize;
    } // end func getMaxFileSize

    // }}}
    // {{{ isUploadedFile()

    /**
     * Checks if the given element contains an uploaded file (DEPRECATED)
     *
     * @param     string    $element    Element name
     * @since     2.10
     * @access    public
     * @return    bool      true if file has been uploaded, false otherwise
     * @deprecated  Use HTML_QuickForm_file::isUploadedFile() method
     */
    function isUploadedFile($element)
    {
        PEAR::raiseError(null, QUICKFORM_DEPRECATED, null, E_USER_WARNING, "Method HTML_QuickForm::isUploadedFile() is now deprecated in file ".$_SERVER['PHP_SELF'], 'HTML_QuickForm_Error', true);

        if (!$this->elementExists($element) || 'file' != $this->getElementType($element)) {
            return false;
        } else {
            $elementObject =& $this->getElement($element);
            return $elementObject->isUploadedFile();
        }
    } // end func isUploadedFile

    // }}}
    // {{{ getUploadedFile()

    /**
     * Returns temporary filename of uploaded file (DEPRECATED)
     * @param    string  $element  
     * @since    2.10
     * @access   public
     * @deprecated  Use either of HTML_QuickForm_file::getValue(), HTML_QuickForm::getElementValue(), HTML_QuickForm::getSubmitValue() methods to access this information
     */
    function getUploadedFile($element)
    {
        PEAR::raiseError(null, QUICKFORM_DEPRECATED, null, E_USER_WARNING, "Method HTML_QuickForm::getUploadedFile() is now deprecated in file ".$_SERVER['PHP_SELF'], 'HTML_QuickForm_Error', true);

        return isset($this->_submitFiles[$element])? $this->_submitFiles[$element]['tmp_name']: null;
    } // end func getUploadedFile

    // }}}
    // {{{ &createElement()

    /**
     * Creates a new form element of the given type.
     * 
     * This method accepts variable number of parameters, their 
     * meaning and count depending on $elementType
     *
     * @param     string     $elementType    type of element to add (text, textarea, file...)
     * @since     1.0
     * @access    public
     * @return    object extended class of HTML_element
     * @throws    HTML_QuickForm_Error
     */
    function &createElement($elementType)
    {
        $args = func_get_args();
        return HTML_QuickForm::_loadElement('createElement', $elementType, array_slice($args, 1));
    } // end func createElement
    
    // }}}
    // {{{ _loadElement()

    /**
     * Returns a form element of the given type
     *
     * @param     string   $event   event to send to newly created element ('createElement' or 'addElement')
     * @param     string   $type    element type
     * @param     array    $args    arguments for event
     * @since     2.0
     * @access    private
     * @return    object    a new element
     * @throws    HTML_QuickForm_Error
     */
    function &_loadElement($event, $type, $args)
    {
        $type = strtolower($type);
        if (!HTML_QuickForm::isTypeRegistered($type)) {
            return PEAR::raiseError(null, QUICKFORM_UNREGISTERED_ELEMENT, null, E_USER_WARNING, "Element '$type' does not exist in HTML_QuickForm::_loadElement()", 'HTML_QuickForm_Error', true);
        }
        $className = $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][$type][1];
        $includeFile = $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][$type][0];
        include_once($includeFile);
        $elementObject =& new $className();
        for ($i = 0; $i < 5; $i++) {
            if (!isset($args[$i])) {
                $args[$i] = null;
            }
        }
        $err = $elementObject->onQuickFormEvent($event, $args, $this);
        if ($err !== true) {
            return $err;
        }
        return $elementObject;
    } // end func _loadElement

    // }}}
    // {{{ addElement()

    /**
     * Adds an element into the form
     * 
     * If $element is a string representing element type, then this 
     * method accepts variable number of parameters, their meaning 
     * and count depending on $element
     *
     * @param    mixed      $element        element object or type of element to add (text, textarea, file...)
     * @since    1.0
     * @return   object     reference to element
     * @access   public
     * @throws   HTML_QuickForm_Error
     */
    function &addElement($element)
    {
        if (is_object($element) && is_subclass_of($element, 'html_quickform_element')) {
           $elementObject = &$element;
           $elementObject->onQuickFormEvent('updateValue', null, $this);
        } else {
            $args = func_get_args();
            $elementObject =& $this->_loadElement('addElement', $element, array_slice($args, 1));
            if (PEAR::isError($elementObject)) {
                return $elementObject;
            }
        }
        $elementName = $elementObject->getName();

        // Add the element if it is not an incompatible duplicate
        if (!empty($elementName) && isset($this->_elementIndex[$elementName])) {
            if ($this->_elements[$this->_elementIndex[$elementName]]->getType() ==
                $elementObject->getType()) {
                $this->_elements[] =& $elementObject;
                $this->_duplicateIndex[$elementName][] = end(array_keys($this->_elements));
            } else {
                return PEAR::raiseError(null, QUICKFORM_INVALID_ELEMENT_NAME, null, E_USER_WARNING, "Element '$elementName' already exists in HTML_QuickForm::addElement()", 'HTML_QuickForm_Error', true);
            }
        } else {
            $this->_elements[] =& $elementObject;
            $this->_elementIndex[$elementName] = end(array_keys($this->_elements));
        }

        return $elementObject;
    } // end func addElement
    
    // }}}
    // {{{ addGroup()

    /**
     * Adds an element group
     * @param    array      $elements       array of elements composing the group
     * @param    string     $name           (optional)group name
     * @param    string     $groupLabel     (optional)group label
     * @param    string     $separator      (optional)string to separate elements
     * @param    string     $appendName     (optional)specify whether the group name should be
     *                                      used in the form element name ex: group[element]
     * @return   object     reference to added group of elements
     * @since    2.8
     * @access   public
     * @throws   PEAR_Error
     */
    function &addGroup($elements, $name=null, $groupLabel='', $separator=null, $appendName = true)
    {
        static $anonGroups = 1;

        if (empty($name)) {
            $name       = 'qf_group_' . $anonGroups++;
            $appendName = false;
        }
        return $this->addElement('group', $name, $groupLabel, $elements, $separator, $appendName);
    } // end func addGroup
    
    // }}}
    // {{{ addElementGroup()

    /**
     * Adds an element group (DEPRECATED, use addGroup instead)
     * @param    array      $elements       array of elements composing the group
     * @param    string     $groupLabel     (optional)group label
     * @param    string     $name           (optional)group name
     * @param    string     $separator      (optional)string to seperate elements
     * @return   object     reference to added group of elements
     * @deprecated deprecated since 2.10, use addGroup() instead
     * @since    1.0
     * @access   public
     * @throws   PEAR_Error
     */
    function &addElementGroup($elements, $groupLabel='', $name=null, $separator=null)
    {
        PEAR::raiseError(null, QUICKFORM_DEPRECATED, null, E_USER_WARNING, "Method HTML_QuickForm::addElementGroup() is now deprecated in file ".$_SERVER['PHP_SELF'], 'HTML_QuickForm_Error', true);

        return $this->addGroup($elements, $name, $groupLabel, $separator);
    } // end func addElementGroup
    
    // }}}
    // {{{ &getElement()

    /**
     * Returns a reference to the element
     *
     * @param     string     $element    Element name
     * @since     2.0
     * @access    public
     * @return    object     reference to element
     * @throws    HTML_QuickForm_Error
     */
    function &getElement($element)
    {
        if (isset($this->_elementIndex[$element])) {
            return $this->_elements[$this->_elementIndex[$element]];
        } else {
            return PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$element' does not exist in HTML_QuickForm::getElement()", 'HTML_QuickForm_Error', true);
        }
    } // end func getElement

    // }}}
    // {{{ &getElementValue()

    /**
     * Returns the element's raw value
     * 
     * This returns the value as submitted by the form (not filtered) 
     * or set via setDefaults() or setConstants()
     *
     * @param     string     $element    Element name
     * @since     2.0
     * @access    public
     * @return    mixed     element value
     * @throws    HTML_QuickForm_Error
     */
    function &getElementValue($element)
    {
        if (!isset($this->_elementIndex[$element])) {
            return PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$element' does not exist in HTML_QuickForm::getElementValue()", 'HTML_QuickForm_Error', true);
        }
        $value = $this->_elements[$this->_elementIndex[$element]]->getValue();
        if (isset($this->_duplicateIndex[$element])) {
            foreach ($this->_duplicateIndex[$element] as $index) {
                if (null !== ($v = $this->_elements[$index]->getValue())) {
                    if (is_array($value)) {
                        $value[] = $v;
                    } else {
                        $value = (null === $value)? $v: array($value, $v);
                    }
                }
            }
        }
        return $value;
    } // end func getElementValue

    // }}}
    // {{{ getSubmitValue()

    /**
     * Returns the elements value after submit and filter
     *
     * @param     string     Element name
     * @since     2.0
     * @access    public
     * @return    mixed     submitted element value or null if not set
     */    
    function getSubmitValue($elementName)
    {
        $value = null;
        if (isset($this->_submitValues[$elementName])) {
            $value = $this->_submitValues[$elementName];
            if (is_array($value) && isset($this->_submitFiles[$elementName])) {
                foreach ($this->_submitFiles[$elementName] as $k => $v) {
                    $value = @array_merge_recursive($value, $this->_reindexFiles($this->_submitFiles[$elementName][$k], $k));
                }
            }

        } elseif ('file' == $this->getElementType($elementName)) {
            return $this->getElementValue($elementName);

        } elseif ('group' == $this->getElementType($elementName)) {
            $group    =& $this->getElement($elementName);
            $elements =& $group->getElements();
            foreach (array_keys($elements) as $key) {
                $name = $group->getElementName($key);
                if ($name != $elementName) {
                    // filter out radios
                    $value[$name] = $this->getSubmitValue($name);
                }
            }

        } elseif (false !== ($pos = strpos($elementName, '['))) {
            $base = substr($elementName, 0, $pos);
            $idx  = "['" . str_replace(array(']', '['), array('', "']['"), substr($elementName, $pos + 1, -1)) . "']";
            if (isset($this->_submitValues[$base])) {
                $value = eval("return (isset(\$this->_submitValues['{$base}']{$idx})) ? \$this->_submitValues['{$base}']{$idx} : null;");
            }

            if (null === $value && isset($this->_submitFiles[$base])) {
                $props = array('name', 'type', 'size', 'tmp_name', 'error');
                $code  = "if (!isset(\$this->_submitFiles['{$base}']['name']{$idx})) {\n" .
                         "    return null;\n" .
                         "} else {\n" .
                         "    \$v = array();\n";
                foreach ($props as $prop) {
                    $code .= "    \$v['{$prop}'] = \$this->_submitFiles['{$base}']['{$prop}']{$idx};\n";
                }
                $value = eval($code . "    return \$v;\n}\n");
            }
        }
        return $value;
    } // end func getSubmitValue

    // }}}
    // {{{ _reindexFiles()

   /**
    * A helper function to change the indexes in $_FILES array
    *
    * @param  mixed   Some value from the $_FILES array
    * @param  string  The key from the $_FILES array that should be appended
    * @return array
    */
    function _reindexFiles($value, $key)
    {
        if (!is_array($value)) {
            return array($key => $value);
        } else {
            $ret = array();
            foreach ($value as $k => $v) {
                $ret[$k] = $this->_reindexFiles($v, $key);
            }
            return $ret;
        }
    }

    // }}}
    // {{{ getElementError()

    /**
     * Returns error corresponding to validated element
     *
     * @param     string    $element        Name of form element to check
     * @since     1.0
     * @access    public
     * @return    string    error message corresponding to checked element
     */
    function getElementError($element)
    {
        if (isset($this->_errors[$element])) {
            return $this->_errors[$element];
        }
    } // end func getElementError
    
    // }}}
    // {{{ setElementError()

    /**
     * Set error message for a form element
     *
     * @param     string    $element    Name of form element to set error for
     * @param     string    $message    Error message
     * @since     1.0       
     * @access    public
     * @return    void
     */
    function setElementError($element,$message)
    {
        $this->_errors[$element] = $message;
    } // end func setElementError
         
     // }}}
     // {{{ getElementType()

     /**
      * Returns the type of the given element
      *
      * @param      string    $element    Name of form element
      * @since      1.1
      * @access     public
      * @return     string    Type of the element, false if the element is not found
      */
     function getElementType($element)
     {
         if (isset($this->_elementIndex[$element])) {
             return $this->_elements[$this->_elementIndex[$element]]->getType();
         }
         return false;
     } // end func getElementType

     // }}}
     // {{{ updateElementAttr()

    /**
     * Updates Attributes for one or more elements
     *
     * @param      mixed    $elements   Array of element names/objects or string of elements to be updated
     * @param      mixed    $attrs      Array or sting of html attributes
     * @since      2.10
     * @access     public
     * @return     void
     */
    function updateElementAttr($elements, $attrs)
    {
        if (is_string($elements)) {
            $elements = split('[ ]?,[ ]?', $elements);
        }
        foreach ($elements as $element) {
            if (is_object($element) && is_subclass_of($element, 'HTML_QuickForm_element')) {
                $element->updateAttributes($attrs);
            } elseif (isset($this->_elementIndex[$element])) {
                $this->_elements[$this->_elementIndex[$element]]->updateAttributes($attrs);
            }
        }
    } // end func updateElementAttr

    // }}}
    // {{{ removeElement()

    /**
     * Removes an element
     *
     * @param string    $elementName The element name
     * @param boolean   $removeRules True if rules for this element are to be removed too                     
     *
     * @access public
     * @since 2.0
     * @return void
     * @throws HTML_QuickForm_Error
     */
   function removeElement($elementName, $removeRules = true)
    {
        if (isset($this->_elementIndex[$elementName])) {
            unset($this->_elements[$this->_elementIndex[$elementName]]);
            unset($this->_elementIndex[$elementName]);
            if ($removeRules) {
                unset($this->_rules[$elementName]);
            }
        } else {
            return PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$elementName' does not exist in HTML_QuickForm::removeElement()", 'HTML_QuickForm_Error', true);
        }
    } // end func removeElement

    // }}}
    // {{{ addHeader()

    /**
     * Adds a header element to the form (DEPRECATED)
     *
     * @param     string    $label      label of header
     * @since     1.0   
     * @access    public
     * @deprecated deprecated since 3.0, use addElement('header', ...) instead
     * @return    object A reference to a header element
     * @throws    PEAR_Error
     */
    function &addHeader($label)
    {
        PEAR::raiseError(null, QUICKFORM_DEPRECATED, null, E_USER_WARNING, "Method HTML_QuickForm::addHeader() is now deprecated in file ".$_SERVER['PHP_SELF'], 'HTML_QuickForm_Error', true);

        return $this->addElement('header', null, $label);
    } // end func addHeader

    // }}}
    // {{{ addRule()

    /**
     * Adds a validation rule for the given field
     *
     * If the element is in fact a group, it will be considered as a whole.
     * To validate grouped elements as separated entities, 
     * use addGroupRule instead of addRule.
     *
     * @param    string     $element       Form element name
     * @param    string     $message       Message to display for invalid data
     * @param    string     $type          Rule type, use getRegisteredRules() to get types
     * @param    string     $format        (optional)Required for extra rule data
     * @param    string     $validation    (optional)Where to perform validation: "server", "client"
     * @param    boolean    $reset         Client-side validation: reset the form element to its original value if there is an error?
     * @param    boolean    $force         Force the rule to be applied, even if the target form element does not exist
     * @since    1.0
     * @access   public
     * @throws   HTML_QuickForm_Error
     */
    function addRule($element, $message, $type, $format=null, $validation='server', $reset = false, $force = false)
    {
        if (!$force) {
            if (!is_array($element) && !$this->elementExists($element)) {
                return PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$element' does not exist in HTML_QuickForm::addRule()", 'HTML_QuickForm_Error', true);
            } elseif (is_array($element)) {
                foreach ($element as $el) {
                    if (!$this->elementExists($el)) {
                        return PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$el' does not exist in HTML_QuickForm::addRule()", 'HTML_QuickForm_Error', true);
                    }
                }
            }
        }
        if (false === ($newName = $this->isRuleRegistered($type, true))) {
            return PEAR::raiseError(null, QUICKFORM_INVALID_RULE, null, E_USER_WARNING, "Rule '$type' is not registered in HTML_QuickForm::addRule()", 'HTML_QuickForm_Error', true);
        } elseif (is_string($newName)) {
            $type = $newName;
        }
        if (is_array($element)) {
            $dependent = $element;
            $element   = array_shift($dependent);
        } else {
            $dependent = null;
        }
        if ($type == 'required' || $type == 'uploadedfile') {
            $this->_required[] = $element;
        }
        if (!isset($this->_rules[$element])) {
            $this->_rules[$element] = array();
        }
        if ($validation == 'client') {
            $this->updateAttributes(array('onsubmit'=>'return validate_'.$this->_attributes['name'] . '();'));
        }
        $this->_rules[$element][] = array(
            'type'        => $type,
            'format'      => $format,
            'message'     => $message,
            'validation'  => $validation,
            'reset'       => $reset,
            'dependent'   => $dependent
        );
    } // end func addRule

    // }}}
    // {{{ addGroupRule()

    /**
     * Adds a validation rule for the given group of elements
     *
     * Only groups with a name can be assigned a validation rule
     * Use addGroupRule when you need to validate elements inside the group.
     * Use addRule if you need to validate the group as a whole. In this case,
     * the same rule will be applied to all elements in the group.
     * Use addRule if you need to validate the group against a function.
     *
     * @param    string     $group         Form group name
     * @param    mixed      $arg1          Array for multiple elements or error message string for one element
     * @param    string     $type          (optional)Rule type use getRegisteredRules() to get types
     * @param    string     $format        (optional)Required for extra rule data
     * @param    int        $howmany       (optional)How many valid elements should be in the group
     * @param    string     $validation    (optional)Where to perform validation: "server", "client"
     * @since    2.5
     * @access   public
     * @throws   HTML_QuickForm_Error
     */
    function addGroupRule($group, $arg1, $type='', $format=null, $howmany=0, $validation = 'server')
    {
        if (!$this->elementExists($group)) {
            return PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Group '$group' does not exist in HTML_QuickForm::addGroupRule()", 'HTML_QuickForm_Error', true);
        }

        $groupObj =& $this->getElement($group);
        if (is_array($arg1)) {
            $required = 0;
            foreach ($arg1 as $elementIndex => $rules) {
                foreach ($rules as $rule) {
                    $format = (isset($rule[2])) ? $rule[2] : null;
                    $type = $rule[1];
                    if (false === ($newName = $this->isRuleRegistered($type, true))) {
                        return PEAR::raiseError(null, QUICKFORM_INVALID_RULE, null, E_USER_WARNING, "Rule '$type' is not registered in HTML_QuickForm::addGroupRule()", 'HTML_QuickForm_Error', true);
                    } elseif (is_string($newName)) {
                        $type = $newName;
                    }

                    $elementName = $groupObj->getElementName($elementIndex);
                    $this->_rules[$elementName][] = array(
                                                        'type'        => $type,
                                                        'format'      => $format, 
                                                        'message'     => $rule[0],
                                                        'validation'  => 'server',
                                                        'group'       => $group);

                    if ('required' == $type || 'uploadedfile' == $type) {
                        $groupObj->_required[] = $elementIndex;
                        $this->_required[] = $elementName;
                        $required++;
                    }

                }
            }
            if ($required > 0 && count($groupObj->getElements()) == $required) {
                $this->_required[] = $group;
            }
        } elseif (is_string($arg1)) {
            if (false === ($newName = $this->isRuleRegistered($type, true))) {
                return PEAR::raiseError(null, QUICKFORM_INVALID_RULE, null, E_USER_WARNING, "Rule '$type' is not registered in HTML_QuickForm::addGroupRule()", 'HTML_QuickForm_Error', true);
            } elseif (is_string($newName)) {
                $type = $newName;
            }

            // Radios need to be handled differently when required
            if ($type == 'required' && $groupObj->getGroupType() == 'radio') {
                $howmany = ($howmany == 0) ? 1 : $howmany;
            } else {
                $howmany = ($howmany == 0) ? count($groupObj->getElements()) : $howmany;
            }

            $this->_rules[$group][] = array('type'       => $type,
                                            'format'     => $format, 
                                            'message'    => $arg1,
                                            'validation' => $validation,
                                            'howmany'    => $howmany);
            if ($type == 'required') {
                $this->_required[] = $group;
            }
            if ($validation == 'client') {
                $this->updateAttributes(array('onsubmit'=>'return validate_'.$this->_attributes['name'] . '();'));
            }
        }
    } // end func addGroupRule

    // }}}
    // {{{ addFormRule()

   /**
    * Adds a global validation rule 
    * 
    * This should be used when for a rule involving several fields or if
    * you want to use some completely custom validation for your form.
    * The rule function/method should return true in case of successful 
    * validation and array('element name' => 'error') when there were errors.
    * 
    * @access   public
    * @param    mixed   Callback, either function name or array(&$object, 'method')
    * @throws   HTML_QuickForm_Error
    */
    function addFormRule($rule)
    {
        if (!is_callable($rule)) {
            return PEAR::raiseError(null, QUICKFORM_INVALID_RULE, null, E_USER_WARNING, 'Callback function does not exist in HTML_QuickForm::addFormRule()', 'HTML_QuickForm_Error', true);
        }
        $this->_formRules[] = $rule;
    }
    
    // }}}
    // {{{ addData()

    /**
     * Adds raw HTML (or text) data element to the form (DEPRECATED)
     *
     * @param string $data The data to add to the form object
     * @access public
     * @deprecated deprecated since 3.0, use addElement('html', ...) instead
     * @return object reference to a new element
     * @throws PEAR_Error
     */
    function &addData($data)
    {
        PEAR::raiseError(null, QUICKFORM_DEPRECATED, null, E_USER_WARNING, "Method HTML_QuickForm::addData() is now deprecated in file ".$_SERVER['PHP_SELF'], 'HTML_QuickForm_Error', true);

        return $this->addElement('html', $data);
    }

    // }}}
    // {{{ applyFilter()

    /**
     * Applies a data filter for the given field(s)
     *
     * @param    mixed     $element       Form element name or array of such names
     * @param    mixed     $filter        Callback, either function name or array(&$object, 'method')
     * @since    2.0
     * @access   public
     */
    function applyFilter($element, $filter)
    {
        if (!is_callable($filter)) {
            return PEAR::raiseError(null, QUICKFORM_INVALID_FILTER, null, E_USER_WARNING, "Callback function does not exist in QuickForm::applyFilter()", 'HTML_QuickForm_Error', true);
        }
        if ($element == '__ALL__') {
            $this->_submitValues = $this->_recursiveFilter($filter, $this->_submitValues);
        } else {
            if (!is_array($element)) {
                $element = array($element);
            }
            foreach ($element as $elName) {
                if ($this->elementExists($elName)) {
                    if (isset($this->_submitValues[$elName])) {
                        $this->_submitValues[$elName] = $this->_recursiveFilter($filter, $this->_submitValues[$elName]);
                    }
                } else {
                    return PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$elName' does not exist in HTML_QuickForm::applyFilter()", 'HTML_QuickForm_Error', true);
                }
            }
        }
    } // end func applyFilter

    // }}}
    // {{{ _recursiveFilter()

    /**
     * Recursively apply a filter function
     *
     * @param     string   $filter    filter to apply
     * @param     mixed    $value     submitted values
     * @since     2.0
     * @access    private
     * @return    cleaned values
     */
    function _recursiveFilter($filter, $value)
    {
        if (is_array($value)) {
            $cleanValues = array();
            foreach ($value as $k => $v) {
                $cleanValues[$k] = $this->_recursiveFilter($filter, $value[$k]);
            }
            return $cleanValues;
        } else {
            return call_user_func($filter, $value);
        }
    } // end func _recursiveFilter

    // }}}
    // {{{ isTypeRegistered()

    /**
     * Returns whether or not the form element type is supported
     *
     * @param     string   $type     Form element type
     * @since     1.0
     * @access    public
     * @return    boolean
     */
    function isTypeRegistered($type)
    {
        return isset($GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][$type]);
    } // end func isTypeRegistered

    // }}}
    // {{{ getRegisteredTypes()

    /**
     * Returns an array of registered element types
     *
     * @since     1.0
     * @access    public
     * @return    array
     */
    function getRegisteredTypes()
    {
        return array_keys($GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES']);
    } // end func getRegisteredTypes

    // }}}
    // {{{ isRuleRegistered()

    /**
     * Returns whether or not the given rule is supported
     *
     * @param     string   $name    Validation rule name
     * @param     bool     Whether to automatically register subclasses of HTML_QuickForm_Rule
     * @since     1.0
     * @access    public
     * @return    mixed    true if previously registered, false if not, new rule name if auto-registering worked
     */
    function isRuleRegistered($name, $autoRegister = false)
    {
        if (is_scalar($name) && isset($GLOBALS['_HTML_QuickForm_registered_rules'][$name])) {
            return true;
        } elseif (!$autoRegister) {
            return false;
        }
        // automatically register the rule if requested
        include_once 'HTML/QuickForm/RuleRegistry.php';
        $ruleName = false;
        if (is_object($name) && is_a($name, 'html_quickform_rule')) {
            $ruleName = !empty($name->name)? $name->name: get_class($name);
        } elseif (is_string($name) && class_exists($name)) {
            $parent = strtolower($name);
            do {
                if ('html_quickform_rule' == $parent) {
                    $ruleName = strtolower($name);
                    break;
                }
            } while ($parent = get_parent_class($parent));
        }
        if ($ruleName) {
            $registry =& HTML_QuickForm_RuleRegistry::singleton();
            $registry->registerRule($ruleName, null, $name);
        }
        return $ruleName;
    } // end func isRuleRegistered

    // }}}
    // {{{ getRegisteredRules()

    /**
     * Returns an array of registered validation rules
     *
     * @since     1.0
     * @access    public
     * @return    array
     */
    function getRegisteredRules()
    {
        return array_keys($GLOBALS['_HTML_QuickForm_registered_rules']);
    } // end func getRegisteredRules

    // }}}
    // {{{ isElementRequired()

    /**
     * Returns whether or not the form element is required
     *
     * @param     string   $element     Form element name
     * @since     1.0
     * @access    public
     * @return    boolean
     */
    function isElementRequired($element)
    {
        return in_array($element, $this->_required, true);
    } // end func isElementRequired

    // }}}
    // {{{ isElementFrozen()

    /**
     * Returns whether or not the form element is frozen
     *
     * @param     string   $element     Form element name
     * @since     1.0
     * @access    public
     * @return    boolean
     */
    function isElementFrozen($element)
    {
         if (isset($this->_elementIndex[$element])) {
             return $this->_elements[$this->_elementIndex[$element]]->isFrozen();
         }
         return false;
    } // end func isElementFrozen

    // }}}
    // {{{ setJsWarnings()

    /**
     * Sets JavaScript warning messages
     *
     * @param     string   $pref        Prefix warning
     * @param     string   $post        Postfix warning
     * @since     1.1
     * @access    public
     * @return    void
     */
    function setJsWarnings($pref, $post)
    {
        $this->_jsPrefix = $pref;
        $this->_jsPostfix = $post;
    } // end func setJsWarnings
    
    // }}}
    // {{{ setElementTemplate()

    /**
     * Sets element template 
     *
     * @param     string   $html        The HTML surrounding an element 
     * @param     string   $element     (optional) Name of the element to apply template for
     * @since     2.0
     * @deprecated deprecated since 3.0, use renderers for controlling the presentation
     * @access    public
     * @return    void
     */
    function setElementTemplate($html, $element = null)
    {
        PEAR::raiseError(null, QUICKFORM_DEPRECATED, null, E_USER_WARNING, "Method HTML_QuickForm::setElementTemplate() is now deprecated in file ".$_SERVER['PHP_SELF'], 'HTML_QuickForm_Error', true);

        $renderer =& $this->defaultRenderer();
        return $renderer->setElementTemplate($html, $element);
    } // end func setElementTemplate

    // }}}
    // {{{ setHeaderTemplate()

    /**
     * Sets header template 
     *
     * @param     string   $html    The HTML surrounding the header 
     * @since     2.0
     * @deprecated deprecated since 3.0, use renderers for controlling the presentation
     * @access    public
     * @return    void
     */
    function setHeaderTemplate($html)
    {
        PEAR::raiseError(null, QUICKFORM_DEPRECATED, null, E_USER_WARNING, "Method HTML_QuickForm::setHeaderTemplate() is now deprecated in file ".$_SERVER['PHP_SELF'], 'HTML_QuickForm_Error', true);

        $renderer =& $this->defaultRenderer();
        return $renderer->setHeaderTemplate($html);
    } // end func setHeaderTemplate

    // }}}
    // {{{ setFormTemplate()

    /**
     * Sets form template 
     *
     * @param     string   $html    The HTML surrounding the form tags 
     * @since     2.0
     * @deprecated deprecated since 3.0, use renderers for controlling the presentation
     * @access    public
     * @return    void
     */
    function setFormTemplate($html)
    {
        PEAR::raiseError(null, QUICKFORM_DEPRECATED, null, E_USER_WARNING, "Method HTML_QuickForm::setFormTemplate() is now deprecated in file ".$_SERVER['PHP_SELF'], 'HTML_QuickForm_Error', true);

        $renderer =& $this->defaultRenderer();
        return $renderer->setFormTemplate($html);
    } // end func setFormTemplate

    // }}}
    // {{{ setRequiredNoteTemplate()

    /**
     * Sets element template 
     *
     * @param     string   $html    The HTML surrounding the required note 
     * @since     2.0
     * @deprecated deprecated since 3.0, use renderers for controlling the presentation
     * @access    public
     * @return    void
     */
    function setRequiredNoteTemplate($html)
    {
        PEAR::raiseError(null, QUICKFORM_DEPRECATED, null, E_USER_WARNING, "Method HTML_QuickForm::setRequiredNoteTemplate() is now deprecated in file ".$_SERVER['PHP_SELF'], 'HTML_QuickForm_Error', true);

        $renderer =& $this->defaultRenderer();
        return $renderer->setRequiredNoteTemplate($html);
    } // end func setElementTemplate

    // }}}
    // {{{ clearAllTemplates()

    /**
     * Clears all the HTML out of the templates that surround notes, elements, etc.
     * Useful when you want to use addData() to create a completely custom form look
     *
     * @since   2.0
     * @deprecated deprecated since 3.0, use renderers for controlling the presentation
     * @access  public
     * @return void
     */
    function clearAllTemplates()
    {
        PEAR::raiseError(null, QUICKFORM_DEPRECATED, null, E_USER_WARNING, "Method HTML_QuickForm::clearAllTemplates() is now deprecated in file ".$_SERVER['PHP_SELF'], 'HTML_QuickForm_Error', true);

        $renderer =& $this->defaultRenderer();
        return $renderer->clearAllTemplates();
    }

    // }}}
    // {{{ setRequiredNote()

    /**
     * Sets required-note
     *
     * @param     string   $note        Message indicating some elements are required
     * @since     1.1
     * @access    public
     * @return    void
     */
    function setRequiredNote($note)
    {
        $this->_requiredNote = $note;
    } // end func setRequiredNote

    // }}}
    // {{{ getRequiredNote()

    /**
     * Returns the required note
     *
     * @since     2.0
     * @access    public
     * @return    string
     */
    function getRequiredNote()
    {
        return $this->_requiredNote;
    } // end func getRequiredNote

    // }}}
    // {{{ validate()

    /**
     * Performs the server side validation
     * @access    public
     * @since     1.0
     * @return    boolean   true if no error found
     */
    function validate()
    {
        if (count($this->_rules) == 0 && count($this->_formRules) == 0 && 
            (count($this->_submitValues) > 0 || count($this->_submitFiles) > 0)) {
            return true;
        } elseif (count($this->_submitValues) == 0 && count($this->_submitFiles) == 0) {
            return false;
        }

        include_once('HTML/QuickForm/RuleRegistry.php');
        $registry =& HTML_QuickForm_RuleRegistry::singleton();

        foreach ($this->_rules as $target => $rules) {
            $submitValue = $this->getSubmitValue($target);

            foreach ($rules as $elementName => $rule) {
                if ((isset($rule['group']) && isset($this->_errors[$rule['group']])) ||
                     isset($this->_errors[$target])) {
                    continue 2;
                }
                if ((!isset($submitValue) || $submitValue == '') && 
                     !$this->isElementRequired($target)) {
                    // Element is not required
                    continue 2;
                }
                if (isset($rule['dependent']) && is_array($rule['dependent'])) {
                    $values = array($submitValue);
                    foreach ($rule['dependent'] as $elName) {
                        $values[] = $this->getSubmitValue($elName);
                    }
                    $result = $registry->validate($rule['type'], $values, $rule['format'], true);
                } elseif (is_array($submitValue) && !isset($rule['howmany'])) {
                    $result = $registry->validate($rule['type'], $submitValue, $rule['format'], true);
                } else {
                    $result = $registry->validate($rule['type'], $submitValue, $rule['format'], false);
                }

                if ($result === false || (!empty($rule['howmany']) && $rule['howmany'] > (int)$result)) {
                    if (isset($rule['group'])) {
                        $this->_errors[$rule['group']] = $rule['message'];
                    } else {
                        $this->_errors[$target] = $rule['message'];
                    }
                }
            }
        }

        // process the global rules now
        foreach ($this->_formRules as $rule) {
            if (true !== ($res = call_user_func($rule, $this->_submitValues, $this->_submitFiles))) {
                $this->_errors += $res;
            }
        }

        return (0 == count($this->_errors));
    } // end func validate

    // }}}
    // {{{ freeze()

    /**
     * Displays elements without HTML input tags
     *
     * @param    mixed   $elementList       array or string of element(s) to be frozen
     * @since     1.0
     * @access   public
     * @throws   HTML_QuickForm_Error
     */
    function freeze($elementList=null)
    {
        $elementFlag = false;
        if (isset($elementList) && !is_array($elementList)) {
            $elementList = split('[ ]*,[ ]*', $elementList);
        } elseif (!isset($elementList)) {
            $this->_freezeAll = true;
        }

        foreach ($this->_elements as $key => $val) {
            // need to get the element by reference
            $element = &$this->_elements[$key];
            if (is_object($element)) {
                $name = $element->getName();
                if ($this->_freezeAll || in_array($name, $elementList)) {
                    $elementFlag = true;
                    $element->freeze();
                }
            }
        }

        if (!$elementFlag) {
            return PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$element' does not exist in HTML_QuickForm::freeze()", 'HTML_QuickForm_Error', true);
        }
        return true;
    } // end func freeze
        
    // }}}
    // {{{ isFrozen()

    /**
     * Returns whether or not the whole form is frozen
     *
     * @since     3.0
     * @access    public
     * @return    boolean
     */
    function isFrozen()
    {
         return $this->_freezeAll;
    } // end func isFrozen

    // }}}
    // {{{ process()

    /**
     * Performs the form data processing
     *
     * @param    mixed     $callback        Callback, either function name or array(&$object, 'method')
     * @param    bool      $mergeFiles      Whether uploaded files should be processed too
     * @since    1.0
     * @access   public
     * @throws   HTML_QuickForm_Error
     */
    function process($callback, $mergeFiles = true)
    {
        if (!is_callable($callback)) {
            return PEAR::raiseError(null, QUICKFORM_INVALID_PROCESS, null, E_USER_WARNING, "Callback function does not exist in QuickForm::process()", 'HTML_QuickForm_Error', true);
        }
        $values = ($mergeFiles === true) ? array_merge($this->_submitValues, $this->_submitFiles) : $this->_submitValues;
        return call_user_func($callback, $values);
    } // end func process

    // }}}
    // {{{ accept()

   /**
    * Accepts a renderer
    *
    * @param object     An HTML_QuickForm_Renderer object
    * @since 3.0
    * @access public
    * @return void
    */
    function accept(&$renderer)
    {
        $renderer->startForm($this);
        foreach (array_keys($this->_elements) as $key) {
            $element =& $this->_elements[$key];
            if ($this->_freezeAll) {
                $element->freeze();
            }
            $elementName = $element->getName();
            $required    = ($this->isElementRequired($elementName) && $this->_freezeAll == false);
            $error       = $this->getElementError($elementName);
            $element->accept($renderer, $required, $error);
        }
        $renderer->finishForm($this);
    } // end func accept

    // }}}
    // {{{ defaultRenderer()

   /**
    * Returns a reference to default renderer object
    *
    * @access public
    * @since 3.0
    * @return object a default renderer object
    */
    function &defaultRenderer()
    {
        if (!isset($GLOBALS['_HTML_QuickForm_default_renderer'])) {
            include_once('HTML/QuickForm/Renderer/Default.php');
            $GLOBALS['_HTML_QuickForm_default_renderer'] =& new HTML_QuickForm_Renderer_Default();
        }
        return $GLOBALS['_HTML_QuickForm_default_renderer'];
    } // end func defaultRenderer

    // }}}
    // {{{ toHtml ()

    /**
     * Returns an HTML version of the form
     *
     * @param string $in_data (optional) Any extra data to insert right
     *               before form is rendered.  Useful when using templates.
     *
     * @return   string     Html version of the form
     * @since     1.0
     * @access   public
     */
    function toHtml ($in_data = null)
    {
        if (!is_null($in_data)) {
            $this->addElement('html', $in_data);
        }
        $renderer =& $this->defaultRenderer();
        $this->accept($renderer);
        return $renderer->toHtml();
    } // end func toHtml

    // }}}
    // {{{ _getJsValue()

   /**
    * Returns JavaScript to get and to reset the element's value 
    * 
    * @access private
    * @param  object HTML_QuickForm_element     element being processed
    * @param  string    element's name
    * @param  bool      whether to generate JavaScript to reset the value
    * @param  integer   value's index in the array (only used for multielement rules)
    * @return array     first item is value javascript, second is reset
    */
    function _getJsValue(&$element, $elementName, $reset = false, $index = null)
    {
        $jsIndex = isset($index)? '[' . $index . ']': '';
        $tmp_reset = $reset? "    var field = frm.elements['$elementName'];\n": '';
        if ($element->getType() == 'group' && $element->getGroupType() != 'radio' ||
           ($element->getType() == 'select' && $element->getMultiple())) {
            $value =
                "  value{$jsIndex} = '';\n" .
                "  for (var i = 0; i < frm.elements.length; i++) {\n" .
                "    if (frm.elements[i].name.indexOf('$elementName') == 0) {\n" .
                "      value{$jsIndex} += frm.elements[i].value;\n" .
                "    }\n" .
                "  }";
            if ($reset) {
                $tmp_reset =
                    "    for (var i = 0; i < frm.elements.length; i++) {\n" .
                    "      if (frm.elements[i].name.indexOf('$elementName') == 0) {\n" .
                    "        frm.elements[i].value = frm.elements[i].defaultValue;\n" .
                    "      }\n" .
                    "    }\n";
            }
        } elseif ($element->getType() == 'checkbox') {
            $value = "  if (frm.elements['$elementName'].checked) {\n" .
                     "    value{$jsIndex} = '1';\n" .
                     "  } else {\n" .
                     "    value{$jsIndex} = '';\n" .
                     "  }";
            $tmp_reset .= ($reset) ? "    field.checked = field.defaultChecked;\n" : '';
        } elseif ($element->getType() == 'group' && $element->getGroupType() == 'radio') {
            $value = "  value{$jsIndex} = '';\n" .
                     "  for (var i = 0; i < frm.elements['$elementName'].length; i++) {\n" .
                     "    if (frm.elements['$elementName'][i].checked) {\n" .
                     "      value{$jsIndex} = frm.elements['$elementName'][i].value;\n" .
                     "    }\n" .
                     "  }";
            if ($reset) {
                $tmp_reset .= "    for (var i = 0; i < field.length; i++) {\n" .
                              "      field[i].checked = field[i].defaultChecked;\n" .
                              "    }";
            }
        } else {
            $value = "  value{$jsIndex} = frm.elements['$elementName'].value;";
            $tmp_reset .= ($reset) ? "    field.value = field.defaultValue;\n" : '';
        }
        return array($value, $tmp_reset);
    }

    // }}}
    // {{{ getValidationScript()

    /**
     * Returns the client side validation script
     *
     * @since     2.0
     * @access    public
     * @return    string    Javascript to perform validation, empty string if no 'client' rules were added
     */
    function getValidationScript()
    {
        if (empty($this->_rules) || $this->_freezeAll || empty($this->_attributes['onsubmit'])) {
            return '';
        }

        include_once('HTML/QuickForm/RuleRegistry.php');
        $registry =& HTML_QuickForm_RuleRegistry::singleton();
        $test = array();
        $js_escape = array(
            "\r"    => '\r',
            "\n"    => '\n',
            "\t"    => '\t',
            "'"     => "\\'",
            '"'     => '\"',
            '\\'    => '\\\\'
        );

        foreach ($this->_rules as $elementName => $rules) {
            foreach ($rules as $rule) {
                if ($rule['validation'] == 'client') {
                    $reset      = (isset($rule['reset'])) ? $rule['reset'] : false;
                    $dependent  = isset($rule['dependent']) && is_array($rule['dependent']);
                    $jsReset    = '';
                    $jsValue    = '';

                    if (isset($rule['group'])) {
                        $group =& $this->getElement($rule['group']);
                        $elements =& $group->getElements();
                        $element =& $elements[$group->getElementName($elementName)];
                    } else {
                        $element =& $this->getElement($elementName);
                    }
                    list($jsValue, $jsReset) = $this->_getJsValue($element, $elementName, $reset, $dependent? 0: null);
                    if ($dependent) {
                        foreach ($rule['dependent'] as $idx => $elName) {
                            list($tmp_value, $tmp_reset) = $this->_getJsValue($this->getElement($elName), $elName, $reset, $idx + 1);
                            $jsValue .= "\n" . $tmp_value;
                            $jsReset .= $tmp_reset;
                        }
                        $jsValue = "  value = new Array();\n" . $jsValue;
                    }

                    $test[] = $registry->getValidationScript($rule['type'],
                                                             $jsValue, 
                                                             $elementName,
                                                             strtr($rule['message'], $js_escape),
                                                             $jsReset,
                                                             $rule['format']);
                }
            }
        }
        if (is_array($test) && count($test) > 0) {
            return
                "\n<script type=\"text/javascript\">\n" .
                "<!-- \n" . 
                "function validate_" . $this->_attributes['name'] . "() {\n" .
                "  var value = '';\n" .
                "  var errFlag = new Array();\n" .
                "  _qfMsg = '';\n" .
                "  var frm = document.forms['" . $this->_attributes['name'] . "'];\n" .
                join("\n", $test) .
                "\n  if (_qfMsg != '') {\n" .
                "    _qfMsg = '" . strtr($this->_jsPrefix, $js_escape) . "' + _qfMsg;\n" .
                "    _qfMsg = _qfMsg + '\\n" . strtr($this->_jsPostfix, $js_escape) . "';\n" .
                "    alert(_qfMsg);\n" .
                "    return false;\n" .
                "  }\n" .
                "  return true;\n" .
                "}\n" .
                "//-->\n" .
                "</script>";
        }
        return '';
    } // end func getValidationScript

    // }}}
    // {{{ getAttributesString()

    /**
     * Returns the HTML attributes of the form (DEPRECATED)
     *
     * @since     2.0
     * @access    public
     * @return    string
     * @deprecated  Use HTML_Common::getAttributes(true)
     */
    function getAttributesString()
    {
        PEAR::raiseError(null, QUICKFORM_DEPRECATED, null, E_USER_WARNING, "Method HTML_QuickForm::getAttributesString() is now deprecated in file ".$_SERVER['PHP_SELF'], 'HTML_QuickForm_Error', true);
        
        return $this->getAttributes(true);
    } // end func getAttributesString

    // }}}
    // {{{ getSubmitValues()

    /**
     * Returns the values submitted by the form
     *
     * @since     2.0
     * @access    public
     * @param     bool      Whether uploaded files should be returned too
     * @return    array
     */
    function getSubmitValues($mergeFiles = false)
    {
        return $mergeFiles? array_merge($this->_submitValues, $this->_submitFiles): $this->_submitValues;
    } // end func getSubmitValues

    // }}}
    // {{{ toArray()

    /**
     * Returns the form's contents in an array.
     *
     * The description of the array structure is in HTML_QuickForm_Renderer_Array docs
     * 
     * @since     2.0
     * @access    public
     * @return    array of form contents
     */
    function toArray()
    {
        include_once 'HTML/QuickForm/Renderer/Array.php';
        $renderer =& new HTML_QuickForm_Renderer_Array();
        $this->accept($renderer);
        return $renderer->toArray();
     } // end func toArray

    // }}}
    // {{{ exportValue()

    /**
     * Returns a 'safe' element's value
     * 
     * This method first tries to find a cleaned-up submitted value,
     * it will return a value set by setValue()/setDefaults()/setConstants()
     * if submitted value does not exist for the given element.
     *
     * @param  string   Name of an element
     * @access public
     * @return mixed
     */
    function exportValue($element)
    {
        if (!isset($this->_elementIndex[$element])) {
            return PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$element' does not exist in HTML_QuickForm::getElementValue()", 'HTML_QuickForm_Error', true);
        }
        $value = $this->_elements[$this->_elementIndex[$element]]->exportValue($this->_submitValues, false);
        if (isset($this->_duplicateIndex[$element])) {
            foreach ($this->_duplicateIndex[$element] as $index) {
                if (null !== ($v = $this->_elements[$index]->exportValue($this->_submitValues, false))) {
                    if (is_array($value)) {
                        $value[] = $v;
                    } else {
                        $value = (null === $value)? $v: array($value, $v);
                    }
                }
            }
        }
        return $value;
    }

    // }}}
    // {{{ exportValues()

    /**
     * Returns 'safe' elements' values
     *
     * Unlike getSubmitValues(), this will return only the values 
     * corresponding to the elements present in the form.
     * 
     * @param   mixed   Array/string of element names, whose values we want. If not set then return all elements.
     * @access  public
     * @return  array   An assoc array of elements' values
     * @throws  HTML_QuickForm_Error
     */
    function exportValues($elementList = null)
    {
        $values = array();
        if (null === $elementList) {
            // iterate over all elements, calling their exportValue() methods
            foreach (array_keys($this->_elements) as $key) {
                $value = $this->_elements[$key]->exportValue($this->_submitValues, true);
                if (is_array($value)) {
                    // This shit throws a bogus warning in PHP 4.3.x
                    $values = @array_merge_recursive($values, $value);
                }
            }
        } else {
            if (!is_array($elementList)) {
                $elementList = array_map('trim', explode(',', $elementList));
            }
            foreach ($elementList as $elementName) {
                $value = $this->exportValue($elementName);
                if (PEAR::isError($value)) {
                    return $value;
                }
                $values[$elementName] = $value;
            }
        }
        return $values;
    }

    // }}}
    // {{{ isError()

    /**
     * Tell whether a result from a QuickForm method is an error (an instance of HTML_QuickForm_Error)
     *
     * @access public
     * @param mixed     result code
     * @return bool     whether $value is an error
     */
    function isError($value)
    {
        return (is_object($value) && (get_class($value) == 'html_quickform_error' || is_subclass_of($value, 'html_quickform_error')));
    } // end func isError

    // }}}
    // {{{ errorMessage()

    /**
     * Return a textual error message for an QuickForm error code
     *
     * @access  public
     * @param   int     error code
     * @return  string  error message
     */
    function errorMessage($value)
    {
        // make the variable static so that it only has to do the defining on the first call
        static $errorMessages;

        // define the varies error messages
        if (!isset($errorMessages)) {
            $errorMessages = array(
                QUICKFORM_OK                    => 'no error',
                QUICKFORM_ERROR                 => 'unknown error',
                QUICKFORM_INVALID_RULE          => 'the rule does not exist as a registered rule',
                QUICKFORM_NONEXIST_ELEMENT      => 'nonexistent html element',
                QUICKFORM_INVALID_FILTER        => 'invalid filter',
                QUICKFORM_UNREGISTERED_ELEMENT  => 'unregistered element',
                QUICKFORM_INVALID_ELEMENT_NAME  => 'element already exists',
                QUICKFORM_INVALID_PROCESS       => 'process callback does not exist',
                QUICKFORM_DEPRECATED            => 'method is deprecated'
            );
        }

        // If this is an error object, then grab the corresponding error code
        if (HTML_QuickForm::isError($value)) {
            $value = $value->getCode();
        }

        // return the textual error message corresponding to the code
        return isset($errorMessages[$value]) ? $errorMessages[$value] : $errorMessages[QUICKFORM_ERROR];
    } // end func errorMessage

    // }}}
} // end class HTML_QuickForm

class HTML_QuickForm_Error extends PEAR_Error {

    // {{{ properties

    /**
    * Prefix for all error messages
    * @var string
    */
    var $error_message_prefix = 'QuickForm Error: ';

    // }}}
    // {{{ constructor

    /**
    * Creates a quickform error object, extending the PEAR_Error class
    *
    * @param int   $code the error code
    * @param int   $mode the reaction to the error, either return, die or trigger/callback
    * @param int   $level intensity of the error (PHP error code)
    * @param mixed $debuginfo any information that can inform user as to nature of the error
    */
    function HTML_QuickForm_Error($code = QUICKFORM_ERROR, $mode = PEAR_ERROR_RETURN,
                         $level = E_USER_NOTICE, $debuginfo = null)
    {
        if (is_int($code)) {
            $this->PEAR_Error(HTML_QuickForm::errorMessage($code), $code, $mode, $level, $debuginfo);
        } else {
            $this->PEAR_Error("Invalid error code: $code", QUICKFORM_ERROR, $mode, $level, $debuginfo);
        }
    }

    // }}}
} // end class HTML_QuickForm_Error
?>