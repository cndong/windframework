<?php
/**
 * @author Qian Su <aoxue.1988.su.qian@163.com> 2010-12-10
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2110 phpwind.com
 * @license 
 */
require_once('../../../../BaseTestCase.php');
L::import(WIND_PATH . '/component/exception/WindException.php');
L::import(WIND_PATH . '/component/db/drivers/mssql/WindMsSqlBuilder.php');
/**
 * the last known user to change this file in the repository  <$LastChangedBy$>
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @version $Id$ 
 * @package 
 */
class WindMsSqlBuilderTest extends BaseTestCase{

	private $config = '';
	private $WindMsSqlBuilder = null;
	
	
	public function init(){
		
		if($this->WindMsSqlBuilder == null){
			$this->config = C::getDataBaseConnection('user');
			$this->WindMsSqlBuilder = new WindMsSqlBuilder($this->config);
		}
	}
	
	public static function provider(){
		return array(
			array('pw_posts','','','',''),
			array('pw_posts','a.uid=pw_posts.authorid','','',''),
			array('pw_posts','a.uid=b.authorid','b','',''),
			array('pw_posts','a.uid=b.authorid','b','subject,pid',''),
			array('pw_posts','a.uid=b.authorid','b','b.subject,b.pid',''),
			array('pw_posts','a.uid=b.authorid','b',array('subject','pid'),'phpwind_8'),
		);
	}
	
	public static function providerWhere(){
		return array(
			 array('username = "1"','',''),
			 array(array('age <= ?','uid > ?'),array(3,4),''),
			 array(array('age = 3','uid >4'),'',''),
			 array('uid < ?',1,true),
			 array('username = ? AND uid > ? ',array('"suqian"',2),false),
		);
	}
	
	public static function providerOrder(){
		return array(
			array(array('dateline'=>'desc'),true),
			array('dateline',true),
			array('age',false),
			array(array('dateline'=>true,'age'=>false),true)
		);
	}
	
	public static function providerLimit(){
		return array(
			array(1,0),
			array(1,5)
		);
	}
	
	public static function providerData(){
		return array(
			array(array('a','b','c')),
			array(array(array('1a','1b','1c'),array('2a','2b','2c')))
		);
	}
	
	public static function providerSet(){
		return array(
			 array('username = "1"',''),
			 array(array('age <= ?','uid > ?'),array(3,4)),
			 array(array('age = 3','uid >4'),''),
			 array('uid < ?',1),
			 array('username = ? , uid > ? ',array('"suqian"',2)),
		);
	}
	
	public static function providerAffected(){
		return array(
			array(true),
			array(false),
		);
	}
	
	public function setUp() {
		$this->init();
	}
	
	public function tearDown(){
		$this->WindMsSqlBuilder->reset();
	}
	
	/**
     * @dataProvider provider
     */

	public function testFrom($table,$joinWhere,$table_alias,$fields,$schema){

		$builder = $this->WindMsSqlBuilder->from($table,$table_alias,$fields,$schema);
		$from = $this->WindMsSqlBuilder->getSql(WindSqlBuilder::FROM);
		$field = $fields ? $this->WindMsSqlBuilder->getSql(WindSqlBuilder::FIELD):true;
		$this->assertTrue($from && $field && ($builder instanceof WindSqlBuilder));
	}
	
	public function testDistinct(){
		$builder = $this->WindMsSqlBuilder->distinct(true);
		$distinct = $this->WindMsSqlBuilder->getSql(WindSqlBuilder::DISTINCT);
		$this->assertEquals(WindSqlBuilder::SQL_DISTINCT,$distinct);
		$this->assertTrue(($builder instanceof WindSqlBuilder));
	}
	
	public function testField(){
	
		$this->assertTrue($this->_field('username','uid'));
	}
	
	public function testFieldWithArray(){
		$this->assertTrue($this->_field(array('username','uid')));
	}
	
	public function testFieldWithParam(){
		$this->assertTrue($this->_field('username','uid','age'));
	}
	
	/**
     * @dataProvider provider
     */
	public function testJoin($table,$joinWhere,$table_alias,$fields,$schema){
		$this->assertTrue($this->_join(WindSqlBuilder::INNER,$table,$joinWhere,$table_alias,$fields,$schema));
	}
	
	
	/**
     * @dataProvider provider
     */
	public function testLeftJoin($table,$joinWhere,$table_alias,$fields,$schema){
		$this->assertTrue($this->_join(WindSqlBuilder::LEFT,$table,$joinWhere,$table_alias,$fields,$schema));
	}
	
	/**
     * @dataProvider provider
     */
	public function testRightJoin($table,$joinWhere,$table_alias,$fields,$schema){
		$this->assertTrue($this->_join(WindSqlBuilder::RIGHT,$table,$joinWhere,$table_alias,$fields,$schema));
	}
	
	/**
     * @dataProvider provider
     */
	public function testFullJoin($table,$joinWhere,$table_alias,$fields,$schema){
		$this->assertTrue($this->_join(WindSqlBuilder::FULL,$table,$joinWhere,$table_alias,$fields,$schema));
	}
	
	/**
     * @dataProvider provider
     */
	public function testInnerJoin($table,$joinWhere,$table_alias,$fields,$schema){
		$this->assertTrue($this->_join(WindSqlBuilder::INNER,$table,$joinWhere,$table_alias,$fields,$schema));
	}
	
	/**
     * @dataProvider provider
     */
	public function testCrossJoin($table,$joinWhere,$table_alias,$fields,$schema){
		$this->assertTrue($this->_join(WindSqlBuilder::CROSS,$table,$joinWhere,$table_alias,$fields,$schema));
	}
	
	/**
	 * @dataProvider providerWhere
	 */
	public function testWhere($where,$value,$group){
		$this->assertTrue($this->_where(WindSqlBuilder::WHERE,$where,$value,$group,true));
	}
		
	
	/**
	 * @dataProvider providerWhere
	 */
	public function testOrWhere($where,$value,$group){
		$this->assertTrue($this->_where(WindSqlBuilder::WHERE,$where,$value,$group,false));
	}
	
	public function testGroup(){
		$this->assertTrue($this->_field('dateline','uid'));
	}
	
	public function testGroupWithArray(){
		$this->assertTrue($this->_field(array('dateline','uid')));
	}
	
	public function testGroupWithParam(){
		$this->assertTrue($this->_field('username','uid'));
	}
	
	/**
	 * @dataProvider providerWhere
	 */
	public function testHaving(){
		$this->assertTrue($this->_where(WindSqlBuilder::HAVING,$where,$value,$group,true));
	}
	
	/**
	 * @dataProvider providerWhere
	 */
	public function testOrHaving(){
		$this->assertTrue($this->_where(WindSqlBuilder::HAVING,$where,$value,$group,false));
	}
	
	/**
	 * @dataProvider providerOrder
	 */
	public function testOrder($field,$type){
		$builder = $this->WindMsSqlBuilder->order($field,$type);
		$order = $this->WindMsSqlBuilder->getSql(WindSqlBuilder::ORDER);
		$this->assertTrue($order && ($builder instanceof WindSqlBuilder));
	}
	
	/**
	 *@dataProvider providerLimit
	 */
	public function testLimit($limit,$offset){
		$builder = $this->WindMsSqlBuilder->limit($limit,$offset);
		$page = $this->WindMsSqlBuilder->getSql(WindSqlBuilder::LIMIT);
		$this->assertTrue($page && ($builder instanceof WindSqlBuilder));
	}
	
	/**
	 *@dataProvider providerData
	 */
	public function testData($data){
		$builder = $this->WindMsSqlBuilder->data($data);
		$data = $this->WindMsSqlBuilder->getSql(WindSqlBuilder::DATA);
		$this->assertTrue($data && ($builder instanceof WindSqlBuilder));
	}
	
	/**
	 * @dataProvider providerSet
	 */
	public function testSet($field,$value) {
		$builder = $this->WindMsSqlBuilder->set($field,$value);
		$set = $this->WindMsSqlBuilder->getSql(WindSqlBuilder::SET);
		$this->assertTrue($set && ($builder instanceof WindSqlBuilder));
	}
	
	public function testGetSelectSql(){
		$sql = "SELECT    a.username,b.title FROM   pw_members AS a   LEFT JOIN  pw_posts AS b ON a.uid=b.authorid  WHERE a.uid !=  1  OR a.group > 0  GROUP BY a.age  HAVING a.age !=  4   ORDER BY dateline DESC  ";
		$assemblySql = $this->WindMsSqlBuilder->from('pw_members','a','username')
					 ->leftJoin('pw_posts','a.uid=b.authorid','b','title')
					 ->where('a.uid != ?',1)
					 ->orWhere('a.group > 0')
					 ->group('a.age')
					 ->having(array('a.age != ?'),array(4))
					 ->order('dateline',true)
					 ->getSelectSql();
		$this->assertEquals($sql,$assemblySql);
	}
	
	public function testGetInsertSql(){
		$sql = "INSERT   pw_members  ( name,age )VALUES  (  'a'  ,  'b'  )  (  'c'  ,  'd'  ) ";
		$insertSql = $this->WindMsSqlBuilder->from('pw_members')
					 ->field('name','age')
					 ->data(array(array('a','b'),array('c','d')))
					 ->getInsertSql();
		 $this->assertEquals($sql,$insertSql);
					 
	}
	
	public function testGetUpdateSql(){
		$sql = "UPDATE   pw_members  SET  username=  'suqian'  ,age= 3   WHERE uid = 11 ";
		$updateSql = $this->WindMsSqlBuilder->from('pw_members')
					 ->set('username=?,age=?',array('suqian',3))
					 ->where('uid = 11')
					 ->getUpdateSql();
	     $this->assertEquals($sql,$updateSql);
	}
	
	public function testGetReplaceSql(){
		$sql = "REPLACE   pw_members  ( name,age )SET  (  'a'  ,  'b'  )  (  'c'  ,  'd'  ) ";
		$replaceSql = $this->WindMsSqlBuilder->from('pw_members')
					 ->field('name','age')
					 ->data(array(array('a','b'),array('c','d')))
					 ->getReplaceSql();
		 $this->assertEquals($sql,$replaceSql);
	}
	
	public function testGetDeleteSql(){
		$sql = "DELETE  FROM   pw_members AS a   WHERE a.uid =  11  ";
		$deleteSql = $this->WindMsSqlBuilder->from('pw_members','a')
					 ->where('a.uid = ?',11)
					 ->getDeleteSql();
		$this->assertEquals($sql,$deleteSql);
	}
	
	
	public function testGetAffectedSql(){
		$sql = 'SELECT @@ROWCOUNT AS  affectedRows';
		$this->assertEquals($sql,$this->WindMsSqlBuilder->getAffectedSql(true));
	}
	
	public function testGetLastInsertIdSql(){
		$sql = 'SELECT @@IDENTITY AS  insertId';
		$this->assertEquals($sql,$this->WindMsSqlBuilder->getLastInsertIdSql());
	}
	
	public function testGetMetaTableSql(){
		$sql = "SELECT name,object_id FROM phpwind.sys.all_objects WHERE type = 'U'";
		$this->assertEquals($sql,$this->WindMsSqlBuilder->getMetaTableSql('phpwind'));
	}
	
	public function testGetMetaColumnSql(){
		$sql = "SELECT    b.name Field,b.max_length,b.precision,b.scale,b.is_nullable,b.is_identity FROM   sys.objects AS a   INNER JOIN  sys.all_columns AS b ON a.object_id = b.object_id INNER JOIN  sys.types AS c ON b.system_type_id = c.system_type_id  WHERE a.name =   'pw_members'      ";
		$this->assertEquals($sql,$this->WindMsSqlBuilder->getMetaColumnSql('pw_members'));
	}
	
	public function testSelect(){
		$result=$this->WindMsSqlBuilder->from('pw_members','a')
							 ->from('pw_posts','b')
							 ->where('a.uid=b.authorid')
							 ->where('b.tid >= ? and b.pid < ? and uid IN ?',array(2,1000,array(1,2,3,4,5,6,7,8)))
					 		 ->field('username','uid','b.subject')
					 		 ->select()
					 		 ->getAllRow(MYSQL_ASSOC);
		$this->assertTrue(is_array($result));
	}
	
	public function testUpdate(){
		$builder = $this->WindMsSqlBuilder->from('pw_posts')
					 ->set('subject=?,buy=?',array('suqian',3))
					 ->where('pid = 1')
					 ->update();
	    $this->assertTrue(($builder instanceof WindSqlBuilder));
	}
	
	public function testDelete(){
		$builder = $this->WindMsSqlBuilder->from('pw_posts')
					 ->where('pid = 2')
					 ->delete();
		$this->assertTrue(($builder instanceof WindSqlBuilder));
	}
	
	public function testInsert(){
		$builder = $this->WindMsSqlBuilder->from('pw_actions')
					 ->field('images','descrip','name')
					 ->data('a','b','c')
					 ->insert();
		$this->assertTrue(($builder instanceof WindSqlBuilder));
	}
	
	
	private function _where($type,$where,$value,$group,$logic){
		$method = $logic ? $type : 'or'.ucfirst($type);
		$builder = $this->WindMsSqlBuilder->$method($where,$value,$group);
		$_where = $this->WindMsSqlBuilder->getSql($type);
		return $_where && ($builder instanceof WindSqlBuilder);
	}
	private function _field($field){
		$params = func_num_args();
		$field = $params >1 ? func_get_args() : func_get_arg(0);
		$builder = $this->WindMsSqlBuilder->field($field);
		$field = $this->WindMsSqlBuilder->getSql(WindSqlBuilder::FIELD);
		return $field && ($builder instanceof WindSqlBuilder);
	}
	
	private function _join($joinType,$table,$joinWhere,$table_alias,$fields,$schema){
		$joinMethod = $joinType.'Join';
		$builder = $this->WindMsSqlBuilder->$joinMethod($table,$joinWhere,$table_alias,$fields,$schema);
		$join = $this->WindMsSqlBuilder->getSql(WindSqlBuilder::JOIN);
		$field = $fields ? $this->WindMsSqlBuilder->getSql(WindSqlBuilder::FIELD):true;
		return $join && $field && ($builder instanceof WindSqlBuilder);
	}
}