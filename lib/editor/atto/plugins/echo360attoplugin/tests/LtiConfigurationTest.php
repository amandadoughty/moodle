<?php
/**
 * Atto text editor integration
 *
 * @package    atto_echo360attoplugin
 * @copyright  COPYRIGHTINFO
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Echo360;

use \Exception as Exception;
use \TypeError as TypeError;

class MockObject {
  public $key;
  function __construct() {
    $this->key = 'value';
  }
}

function get_config($plugin_name, $config_name) {
  return LtiConfigurationTest::$mock_config_val ?: \get_config($plugin_name, $config_name);
}

function get_user_roles($context, $id) {
  return LtiConfigurationTest::$mock_user_roles ?: \get_user_roles($context, $id);
}

function role_get_name($role, $context) {
  return LtiConfigurationTest::$mock_user_role_name ?: \role_get_name($role, $context);
}

function get_admins() {
  return LtiConfigurationTest::$mock_admin_list ?: \get_admins();
}

class LtiConfigurationTest extends \PHPUnit\Framework\TestCase {

  /**
   * @var string $mock_config_val Configuration value that will be returned by get_config()
   */
  public static $mock_config_val;

  /**
   * @var array $mock_user_roles User roles array that will be returned by get_user_roles()
   */
  public static $mock_user_roles;

  /**
   * @var string $mock_user_role_name User role names array that will be returned by role_get_name()
   */
  public static $mock_user_role_name;

  /**
   * @var string $mock_admin_list List of admin users that will be returned by get_admins()
   */
  public static $mock_admin_list;

  /**
   * @var LtiConfiguration $ltiConfiguration Test subject
   */
  private $ltiConfiguration;

  /**
   * Create test subject before test
   */
  protected function setUp()
  {
    parent::setUp();
    self::$mock_config_val = 'mock_config_val';
    self::$mock_user_roles = array('1');
    self::$mock_user_role_name = 'Teacher';
    self::$mock_admin_list = array(array('id'=>'1', 'name'=>'Admin'));
    $this->ltiConfiguration = new LtiConfiguration(self::$mock_context, PLUGIN_NAME);
  }
  /**
   * Reset custom time after test
   */
  protected function tearDown()
  {
    self::$mock_config_val = null;
    self::$mock_user_roles = null;
    self::$mock_user_role_name = null;
    self::$mock_admin_list = null;
  }

  public function testCanBeCreatedFromValidContext() {
    $this->assertInstanceOf(LtiConfiguration::class, $this->ltiConfiguration);
  }

  public function testCannotBeCreatedFromNullContext() {
    $this->expectException(Exception::class);
    new LtiConfiguration(null, 'plugin');
  }

  public function testCannotBeCreatedFromEmptyContext() {
    $this->expectException(Exception::class);
    new LtiConfiguration(array(), 'plugin');
  }

  public function testGetPluginConfig() {
    $plugin_config = LtiConfiguration::get_plugin_config('hosturl', PLUGIN_NAME);
    $this->assertEquals(LtiConfigurationTest::$mock_config_val, $plugin_config);
  }

  public function testGetRoleNames(){
    $role_names = LtiConfiguration::get_role_names(self::$mock_context, self::$mock_user_roles, PLUGIN_NAME);
    $this->assertEquals(array(self::$mock_user_role_name, self::$mock_admin_list[0]['name']), $role_names);
  }

  public function testSortArrayAlphabetically() {
    $arr = array("beta" => "beta val", "charlie" => "charlie val", "alpha" => "alpha val");
    $sorted = LtiConfiguration::sort_array_alphabetically($arr);
    $this->assertEquals(array('alpha=alpha%20val', 'beta=beta%20val', 'charlie=charlie%20val'), $sorted);
  }

  public function testCannotSortArrayAlphabeticallyFromNull() {
    $this->expectException(TypeError::class);
    LtiConfiguration::sort_array_alphabetically(null);
  }

  public function testCannotSortArrayAlphabeticallyFromString() {
    $this->expectException(TypeError::class);
    LtiConfiguration::sort_array_alphabetically('String');
  }

  public function testObjectToJson() {
    $obj = new MockObject();
    $toJson = LtiConfiguration::object_to_json($obj);
    $this->assertEquals('{"key":"value"}', $toJson);
  }

  public function testCanConvertObjectToJsonFromString() {
    $toJson = LtiConfiguration::object_to_json('String');
    $this->assertEquals('["String"]', $toJson);
  }

  public function testCanConvertObjectToJsonFromNull() {
    $toJson = LtiConfiguration::object_to_json(null);
    $this->assertEquals('[]', $toJson);
  }

  public static $mock_context = array(
    "cacherev" => "9999999999",
    "calendartype" => "",
    "category" => "2",
    "completionnotify" => "0",
    "defaultgroupingid" => "0",
    "enablecompletion" => "0",
    "enddate" => "1522288800",
    "format" => "weeks",
    "fullname" => "Intro to LTI",
    "groupmode" => "0",
    "groupmodeforce" => "0",
    "id" => "4",
    "idnumber" => "9999",
    "lang" => "",
    "legacyfiles" => "0",
    "marker" => "0",
    "maxbytes" => "20971520",
    "newsitems" => "0",
    "requested" => "0",
    "shortname" => "ILTI",
    "showgrades" => "0",
    "showreports" => "0",
    "sortorder" => "20001",
    "startdate" => "1516233600",
    "summary" => "<p>Introductory course to Learning tools interoperability&nbsp;</p>",
    "summaryformat" => "1",
    "theme" => "",
    "timecreated" => "1516218899",
    "timemodified" => "1516218899",
    "visible" => "1",
    "visibleold" => "1"
  );
}
