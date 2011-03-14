<?php
/**
 * @author xiaoxia xu <xiaoxa.xuxx@aliyun-inc.com> 2011-3-9
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2110 phpwind.com
 * @license 
 */
L::import('WIND:core.dao.IWindDbTemplate');
/**
 * 
 * the last known user to change this file in the repository  <$LastChangedBy$>
 * @author xiaoxia xu <xiaoxia.xuxx@aliyun-inc.com>
 * @version $Id$ 2011-3-9
 * @package
 */
class WindSimpleDbTemplate implements IWindDbTemplate {

	/**
	 * 链接句柄
	 * 
	 * @var WindDbAdapter
	 */
	private $connection = null;

	/**
	 * (non-PHPdoc)
	 * @see IWindDbTemplate::setConnection()
	 */
	public function setConnection($connection) {
		$this->connection = $connection;
	}

	/**
	 * (non-PHPdoc)
	 * @see IWindDbTemplate::getConnection()
	 */
	public function getConnection() {
		return $this->connection;
	}

	/**
	 * 获得数据库链接操作句柄
	 * 
	 * @return WindDbAdapter $connection
	 */
	protected function getDbHandler() {
		return $this->getConnection();
	}

	/**
	 * (non-PHPdoc)
	 * @see IWindDbTemplate::query()
	 */
	public function query($sql) {
		return $this->getDbHandler()->query($sql);
	}

	/**
	 * (non-PHPdoc)
	 * @see IWindDbTemplate::findAllBySql()
	 */
	public function findAllBySql($sql, $resultIndexKey = '') {
		$db = $this->getDbHandler();
		$db->query($sql);
		return $db->getAllRow($resultIndexKey);
	}

	/**
	 * (non-PHPdoc)
	 * @see IWindDbTemplate::findBySql()
	 */
	public function findBySql($sql) {
		$db = $this->getDbHandler();
		$db->query($sql);
		return $db->getRow();
	}

	/**
	 * (non-PHPdoc)
	 * @see IWindDbTemplate::insert()
	 */
	public function insert($tableName, $data) {
		$db = $this->getDbHandler();
		$db->getSqlBuilder()->from($tableName)->data($data)->insert();
		return $db->getLastInsertId();
	}

	/**
	 * (non-PHPdoc)
	 * @see IWindDbTemplate::replace()
	 */
	public function replace($tableName, $data, $isGetAffectedRows = false) {
		$db = $this->getDbHandler();
		$result = $db->getSqlBuilder()->from($tableName)->data($data)->replace();
		return $isGetAffectedRows ? $db->getAffectedRows() : $result;
	}

	/**
	 * (non-PHPdoc)
	 * @see IWindDbTemplate::update()
	 */
	public function update($tableName, $data, $condition = array(), $isGetAffectedRows = false) {
		$condition = $this->cookCondition($condition);
		$db = $this->getDbHandler();
		$result = $db->getSqlBuilder()->from($tableName)->set($data)->where($condition['where'], $condition['whereValue'])->order($condition['order'])->limit($condition['limit'])->update();
		return $isGetAffectedRows ? $db->getAffectedRows() : $result;
	}

	/**
	 * (non-PHPdoc)
	 * @see IWindDbTemplate::updateByField()
	 */
	public function updateByField($tableName, $data, $filed, $value, $isGetAffectedRows = false) {
		if (!$this->checkFiled($filed)) return false;
		return $this->update($tableName, $data, array('where' => "$filed = ?", 'whereValue' => $value), $isGetAffectedRows);
	}

	/**
	 * (non-PHPdoc)
	 * @see IWindDbTemplate::delete()
	 */
	public function delete($tableName, $condition, $isGetAffectedRows = false) {
		$condition = $this->cookCondition($condition);
		$db = $this->getDbHandler();
		$result = $db->getSqlBuilder()->from($tableName)->where($condition['where'], $condition['whereValue'])->order($condition['order'])->limit($condition['limit'])->delete();
		return $isGetAffectedRows ? $db->getAffectedRows() : $result;
	}

	
    /**
     * (non-PHPdoc)
     * @see IWindDbTemplate::deleteByField()
     */
	public function deleteByField($tableName, $filed, $value, $isGetAffectedRows = false) {
		if (!$this->checkFiled($filed)) return array();
		return $this->delete($tableName, array('where' => "$filed = ?", 'whereValue' => $value), $isGetAffectedRows);
	}

	/**
	 * (non-PHPdoc)
	 * @see IWindDbTemplate::find()
	 */
	public function find($tableName, $condition = array()) {
		$condition = $this->cookCondition($condition);
		$db = $this->getDbHandler();
		$db->getSqlBuilder()->from($tableName)->field($condition['field'])->where($condition['where'], $condition['whereValue'])->group($condition['group'])->having($condition['having'], $condition['havingValue'])->order($condition['order'])->limit(1)->select();
		return $db->getRow();
	}

	/**
	 * (non-PHPdoc)
	 * @see IWindDbTemplate::findByField()
	 */
	public function findByField($tableName, $filed, $value) {
		if (!$this->checkFiled($filed)) return array();
		return $this->find($tableName, array('where' => "$filed = ?", 'whereValue' => $value));
	}

	/**
	 * (non-PHPdoc)
	 * @see IWindDbTemplate::findAll()
	 */
	public function findAll($tableName, $condition = array(), $ifCount = false) {
		$condition = $this->cookCondition($condition);
		$db = $this->getDbHandler();
		$query = $db->getSqlBuilder()->from($tableName)->field($condition['field'])->where($condition['where'], $condition['whereValue'])->group($condition['group'])->having($condition['having'], $condition['havingValue'])->order($condition['order'])->limit($condition['limit'], $condition['offset'])->select();
		$result = $db->getAllRow($condition['resultIndexKey']);
		if (!$ifCount) return $result;
		$count = $this->count($tableName, $condition);
		return array($result, $count);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see IWindDbTemplate::findAllByField()
	 */
	public function findAllByField($tableName, $field, $value, $ifCount = false) {
	    if (!$this->checkFiled($filed)) return array();
		return $this->findAll($tableName, array('where' => "$filed = ?", 'whereValue' => $value));
	}

	/**
	 * (non-PHPdoc)
	 * @see IWindDbTemplate::count()
	 */
	public function count($tableName, $condition) {
		$condition = $this->cookCondition($condition);
		$condition['field'] = ' COUNT(*) as total';
		$result = $this->find($tableName, $condition);
		return (int) $result['total'];
	}

	/**
	 * 初始化条件数据
	 * 
	 * @param array $condition	条件
	 * @return array
	 */
	private function cookCondition($condition) {
		$defaultValue = array('field' => '*', 'where' => '', 'whereValue' => array(), 'group' => array(), 'order' => array(), 
			'limit' => null, 'offset' => null, 'having' => '', 'havingValue' => array(), 'resultIndexKey' => '');
		return array_merge($defaultValue, (array) $condition);
	}

	/**
	 * 验证字段的合法性
	 * 
	 * @param string $filed
	 * @return bool
	 */
	public function checkFiled($filed) {
		return preg_match('/^[A-Za-z]{1}[A-Za-z0-9_]+$/i', $filed);
	}

	/**
	 * (non-PHPdoc)
	 * @see IWindDbTemplate::isExistField()
	 */
	public function isExistField($tableName, $field) {
		if ($field == '') return false;
		$fields = $this->getDbHandler()->getMetaColumns($tableName);
		foreach ($fields as $val) {
			if ($val['Field'] == $field) return true;
		}
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see IWindDbTemplate::getTableFields()
	 */
	public function getTableFields($tableName) {
		$fields = $this->getDbHandler()->getMetaColumns($tableName);
		$temp = array();
		foreach ($fields as $val) {
			$temp[] = $val['Field'];
		}
		return $temp;
	}

	/**
	 * (non-PHPdoc)
	 * @see IWindDbTemplate::createTable()
	 */
	public function createTable($tableName, $statement, $engine = 'MyISAM', $charset = 'GBK', $auto_increment = '') {
		if ($this->getDbHandler()->getVersion() > '4.1') {
			$engine = "ENGINE=$engine" . ($charset ? " DEFAULT CHARSET=$charset" : '');
		} else {
			$engine = "TYPE=$engine";
		}
		!empty($auto_increment) && $engine .= "  AUTO_INCREMENT=$auto_increment";
		$sql = 'CREATE TABLE ' . $tableName . '(' . $statement . ')' . $engine;
		return $this->query($sql);
	}
}