<?php
/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 */

/**
 * A Page is basically data in a nice pretty format.
 *
 * Pages should not have any form actions / elements in them. If they
 * do, make sure you use CRM_Core_Form and the related structures. You can
 * embed simple forms in Page and do your own form handling.
 *
 */
class CRM_Core_Page {

  /**
   * The name of the page (auto generated from class name)
   *
   * @var string
   */
  protected $_name;

  /**
   * The title associated with this page.
   *
   * @var object
   */
  protected $_title;

  /**
   * A page can have multiple modes. (i.e. displays
   * a different set of data based on the input
   * @var int
   */
  protected $_mode;

  /**
   * Is this object being embedded in another object. If
   * so the display routine needs to not do any work. (The
   * parent object takes care of the display)
   *
   * @var bool
   */
  protected $_embedded = FALSE;

  /**
   * Are we in print mode? if so we need to modify the display
   * functionality to do a minimal display :)
   *
   * @var int|string
   *   Should match a CRM_Core_Smarty::PRINT_* constant,
   *   or equal 0 if not in print mode
   */
  protected $_print = FALSE;

  /**
   * Cache the smarty template for efficiency reasons
   *
   * @var CRM_Core_Smarty
   */
  static protected $_template;

  /**
   * Cache the session for efficiency reasons
   *
   * @var CRM_Core_Session
   */
  static protected $_session;

  /**
   * What to return to the client if in ajax mode (snippet=json)
   *
   * @var array
   */
  public $ajaxResponse = [];

  /**
   * Url path used to reach this page
   *
   * @var array
   */
  public $urlPath = [];

  /**
   * Should crm.livePage.js be added to the page?
   * @var bool
   */
  public $useLivePageJS;

  /**
   * Variables smarty expects to have set.
   *
   * We ensure these are assigned (value = NULL) when Smarty is instantiated in
   * order to avoid e-notices / having to use empty or isset in the template layer.
   *
   * @var string[]
   */
  public $expectedSmartyVariables = [
    'isForm',
    'hookContent',
    'hookContentPlacement',
    // required for footer.tpl
    'contactId',
    // required for info.tpl
    'infoMessage',
    'infoTitle',
    'infoType',
    'infoOptions',
    // required for Summary.tpl (contact summary) but seems
    // likely to be used more broadly to warrant inclusion here.
    'context',
    // for CMSPrint.tpl
    'urlIsPublic',
    'breadcrumb',
    'pageTitle',
    'isDeleted',
    // Required for footer.tpl,
    // See ExampleHookTest:testPageOutput.
    'footer_status_severity',
    // in 'body.tpl
    'suppressForm',
    'beginHookFormElements',
    // This is checked in validate.tpl
    'snippet_type',
  ];

  /**
   * The permission we have on this contact
   *
   * @var string
   */
  public $_permission;

  /**
   * @var int
   */
  protected $_action;

  /**
   * @var int
   */
  protected $_id;

  /**
   * Class constructor.
   *
   * @param string $title
   *   Title of the page.
   * @param int $mode
   *   Mode of the page.
   *
   * @return CRM_Core_Page
   */
  public function __construct($title = NULL, $mode = NULL) {
    $this->_name = CRM_Utils_System::getClassName($this);
    $this->_title = $title;
    $this->_mode = $mode;

    // let the constructor initialize this, should happen only once
    if (!isset(self::$_template)) {
      self::$_template = CRM_Core_Smarty::singleton();
      self::$_session = CRM_Core_Session::singleton();
    }
    // Smarty $_template is a static var which persists between tests, so
    // if something calls clearTemplateVars(), the static still exists but
    // our ensured variables get blown away, so we need to set them even if
    // it's already been initialized.
    self::$_template->ensureVariablesAreAssigned($this->expectedSmartyVariables);

    // FIXME - why are we messing with 'snippet'? Why not just pass it directly into $this->_print?
    if (!empty($_REQUEST['snippet'])) {
      if ($_REQUEST['snippet'] == CRM_Core_Smarty::PRINT_PDF) {
        $this->_print = CRM_Core_Smarty::PRINT_PDF;
      }
      // FIXME - why does this number not match the constant?
      elseif ($_REQUEST['snippet'] == 5) {
        $this->_print = CRM_Core_Smarty::PRINT_NOFORM;
      }
      // Support 'json' as well as legacy value '6'
      elseif (in_array($_REQUEST['snippet'], [CRM_Core_Smarty::PRINT_JSON, 6])) {
        $this->_print = CRM_Core_Smarty::PRINT_JSON;
      }
      else {
        $this->_print = CRM_Core_Smarty::PRINT_SNIPPET;
      }
    }

    // if the request has a reset value, initialize the controller session
    if (!empty($_REQUEST['reset'])) {
      $this->reset();
    }
  }

  /**
   * This function takes care of all the things common to all pages.
   *
   * This typically involves assigning the appropriate smarty variables :)
   */
  public function run() {
    if ($this->_embedded) {
      return NULL;
    }

    self::$_template->assign('mode', $this->_mode);

    $pageTemplateFile = $this->getHookedTemplateFileName();
    self::$_template->assign('tplFile', $pageTemplateFile);

    // invoke the pagRun hook, CRM-3906
    CRM_Utils_Hook::pageRun($this);

    if ($this->_print) {
      if (in_array($this->_print, [
        CRM_Core_Smarty::PRINT_SNIPPET,
        CRM_Core_Smarty::PRINT_PDF,
        CRM_Core_Smarty::PRINT_NOFORM,
        CRM_Core_Smarty::PRINT_JSON,
      ])) {
        $content = self::$_template->fetch('CRM/common/snippet.tpl');
      }
      else {
        $content = self::$_template->fetch('CRM/common/print.tpl');
      }

      CRM_Utils_System::appendTPLFile($pageTemplateFile,
        $content,
        $this->overrideExtraTemplateFileName()
      );

      //its time to call the hook.
      CRM_Utils_Hook::alterContent($content, 'page', $pageTemplateFile, $this);

      if ($this->_print === CRM_Core_Smarty::PRINT_PDF) {
        CRM_Utils_PDF_Utils::html2pdf($content, "{$this->_name}.pdf", FALSE);
      }
      elseif ($this->_print === CRM_Core_Smarty::PRINT_JSON) {
        $this->ajaxResponse['content'] = $content;
        CRM_Core_Page_AJAX::returnJsonResponse($this->ajaxResponse);
      }
      else {
        echo $content;
      }
      CRM_Utils_System::civiExit();
    }

    $config = CRM_Core_Config::singleton();

    // @fixme this is probably the wrong place for this.  It is required by jsortable.tpl which is inherited from many page templates.
    //   So we have to add it here to deprecate $config->defaultCurrencySymbol
    $this->assign('defaultCurrencySymbol', CRM_Core_BAO_Country::defaultCurrencySymbol());

    // Intermittent alert to admins
    CRM_Utils_Check::singleton()->showPeriodicAlerts();

    if ($this->useLivePageJS && Civi::settings()->get('ajaxPopupsEnabled')) {
      CRM_Core_Resources::singleton()->addScriptFile('civicrm', 'js/crm.livePage.js', 1, 'html-header');
    }

    $content = self::$_template->fetch(CRM_Utils_System::getContentTemplate());
    CRM_Utils_System::appendTPLFile($pageTemplateFile, $content);

    //its time to call the hook.
    CRM_Utils_Hook::alterContent($content, 'page', $pageTemplateFile, $this);

    echo CRM_Utils_System::theme($content, $this->_print);
  }

  /**
   * Store the variable with the value in the form scope.
   *
   * @param string|array $name name of the variable or an assoc array of name/value pairs
   * @param mixed $value
   *   Value of the variable if string.
   */
  public function set($name, $value = NULL) {
    self::$_session->set($name, $value, $this->_name);
  }

  /**
   * Get the variable from the form scope.
   *
   * @param string $name name of the variable
   *
   * @return mixed
   */
  public function get($name) {
    return self::$_session->get($name, $this->_name);
  }

  /**
   * Assign value to name in template.
   *
   * @param string $var
   * @param mixed $value
   *   Value of variable.
   */
  public function assign($var, $value = NULL) {
    self::$_template->assign($var, $value);
  }

  /**
   * Assign value to name in template by reference.
   *
   * @param string $var
   * @param mixed $value
   *   (reference) value of variable.
   *
   * @deprecated since 5.72 will be removed around 5.84
   */
  public function assign_by_ref($var, &$value) {
    CRM_Core_Error::deprecatedFunctionWarning('assign');
    self::$_template->assign($var, $value);
  }

  /**
   * Appends values to template variables.
   *
   * @param array|string $tpl_var the template variable name(s)
   * @param mixed $value
   *   The value to append.
   * @param bool $merge
   */
  public function append($tpl_var, $value = NULL, $merge = FALSE) {
    self::$_template->append($tpl_var, $value, $merge);
  }

  /**
   * Returns an array containing template variables.
   *
   * @deprecated since 5.69 will be removed around 5.93. use getTemplateVars.
   *
   * @param string $name
   *
   * @return array
   */
  public function get_template_vars($name = NULL) {
    return $this->getTemplateVars($name);
  }

  /**
   * Get the value/s assigned to the Template Engine (Smarty).
   *
   * @param string|null $name
   */
  public function getTemplateVars($name = NULL) {
    return self::$_template->getTemplateVars($name);
  }

  /**
   * Destroy all the session state of this page.
   */
  public function reset() {
    self::$_session->resetScope($this->_name);
  }

  /**
   * Use the form name to create the tpl file name.
   *
   * @return string
   */
  public function getTemplateFileName() {
    return strtr(
      CRM_Utils_System::getClassName($this),
      [
        '_' => DIRECTORY_SEPARATOR,
        '\\' => DIRECTORY_SEPARATOR,
      ]
    ) . '.tpl';
  }

  /**
   * A wrapper for getTemplateFileName that includes calling the hook to
   * prevent us from having to copy & paste the logic of calling the hook
   */
  public function getHookedTemplateFileName() {
    $pageTemplateFile = $this->getTemplateFileName();
    CRM_Utils_Hook::alterTemplateFile(get_class($this), $this, 'page', $pageTemplateFile);
    return $pageTemplateFile;
  }

  /**
   * Default extra tpl file basically just replaces .tpl with .extra.tpl
   * i.e. we dont override
   *
   * @return string
   */
  public function overrideExtraTemplateFileName() {
    return NULL;
  }

  /**
   * Setter for embedded.
   *
   * @param bool $embedded
   */
  public function setEmbedded($embedded) {
    $this->_embedded = $embedded;
  }

  /**
   * Getter for embedded.
   *
   * @return bool
   *   return the embedded value
   */
  public function getEmbedded() {
    return $this->_embedded;
  }

  /**
   * Setter for print.
   *
   * @param int|string $print
   *   Should match a CRM_Core_Smarty::PRINT_* constant,
   *   or equal 0 if not in print mode
   *
   * @return void
   */
  public function setPrint($print) {
    $this->_print = $print;
  }

  /**
   * Getter for print.
   *
   * @return int|string
   *   Value matching a CRM_Core_Smarty::PRINT_* constant,
   *   or 0 if not in print mode
   */
  public function getPrint() {
    return $this->_print;
  }

  /**
   * @return CRM_Core_Smarty
   */
  public static function &getTemplate() {
    return self::$_template;
  }

  /**
   * @param string $name
   *
   * @return null
   */
  public function getVar($name) {
    return $this->$name ?? NULL;
  }

  /**
   * @param string $name
   * @param $value
   */
  public function setVar($name, $value) {
    $this->$name = $value;
  }

  /**
   * Assign metadata about fields to the template.
   *
   * In order to allow the template to format fields we assign information about them to the template.
   *
   * At this stage only date field metadata is assigned as that is the only use-case in play and
   * we don't want to assign a lot of unneeded data.
   *
   * @param string $entity
   *   The entity being queried.
   *
   * @throws \CRM_Core_Exception
   */
  protected function assignFieldMetadataToTemplate($entity) {
    $fields = civicrm_api3($entity, 'getfields', ['action' => 'get']);
    $dateFields = [];
    foreach ($fields['values'] as $fieldName => $fieldMetaData) {
      if (isset($fieldMetaData['html']) && ($fieldMetaData['html']['type'] ?? NULL) == 'Select Date') {
        $dateFields[$fieldName] = CRM_Utils_Date::addDateMetadataToField($fieldMetaData, $fieldMetaData);
      }
    }
    $this->assign('fields', $dateFields);
  }

  /**
   * Handy helper to produce the standard markup for an icon with alternative
   * text for a title and screen readers.
   *
   * See also the smarty block function `icon`
   *
   * @param string $icon
   *   The class name of the icon to display.
   * @param string $text
   *   The translated text to display.
   * @param bool $condition
   *   Whether to display anything at all. This helps simplify code when a
   *   checkmark should appear if something is true.
   * @param array $attribs
   *   Attributes to set or override on the icon element.  Any standard
   *   attribute can be unset by setting the value to an empty string.
   *
   * @return string
   *   The whole bit to drop in.
   */
  public static function crmIcon($icon, $text = NULL, $condition = TRUE, $attribs = []) {
    if (!$condition) {
      return '';
    }

    // Add icon classes to any that might exist in $attribs
    $classes = array_key_exists('class', $attribs) ? explode(' ', $attribs['class']) : [];
    $classes[] = 'crm-i';
    $classes[] = $icon;
    $attribs['class'] = implode(' ', array_unique($classes));

    $standardAttribs = ['aria-hidden' => 'true'];
    if ($text === NULL || $text === '') {
      $sr = '';
    }
    else {
      $standardAttribs['title'] = $text;
      $sr = "<span class=\"sr-only\">$text</span>";
    }

    // Assemble attribs
    $attribString = '';
    // Strip out title if $attribs specifies a blank title
    $attribs = array_merge($standardAttribs, $attribs);
    foreach ($attribs as $attrib => $val) {
      if (strlen($val)) {
        $val = htmlspecialchars($val, ENT_COMPAT);
        $attribString .= " $attrib=\"$val\"";
      }
    }

    return "<i$attribString></i>$sr";
  }

  /**
   * Add an expected smarty variable to the array.
   *
   * @param string $elementName
   */
  public function addExpectedSmartyVariable(string $elementName): void {
    $this->expectedSmartyVariables[] = $elementName;
  }

  /**
   * Add an expected smarty variable to the array.
   *
   * @param array $elementNames
   */
  public function addExpectedSmartyVariables(array $elementNames): void {
    foreach ($elementNames as $elementName) {
      // Duplicates don't actually matter....
      $this->addExpectedSmartyVariable($elementName);
    }
  }

  public function invalidKey() {
    throw new CRM_Core_Exception(ts("Sorry, your session has expired. Please reload the page or go back and try again."), 419, [ts("Could not find a valid session key.")]);
  }

}
