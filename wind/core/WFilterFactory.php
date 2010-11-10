<?php
/**
 * @author Qiong Wu <papa0924@gmail.com> 2010-11-9
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2110 phpwind.com
 * @license 
 */

class WFilterFactory extends WFactory {
	private $index = 0;
	private $filters = array();
	private $configs = array();
	private $state = false;
	
	private $callBack = null;
	private $args = array();
	
	/**
	 * 创建一个Filter
	 * @param WSystemConfig $config
	 * @return WFilter
	 */
	function create($config = null) {
		if ($config != null && empty($this->filters))
			$this->_initFilters($config);
		return $this->createFilter();
	}
	
	function createFilter() {
		if ((int) $this->index >= count($this->filters)) {
			$this->state = true;
			return null;
		}
		list($filterName, $path) = $this->filters[$this->index++];
		W::import($path);
		if ($filterName && class_exists($filterName) && in_array('WFilter', class_parents($filterName))) {
			$class = new ReflectionClass($filterName);
			$object = $class->newInstance();
			return $object;
		}
		$this->createFilter();
	}
	
	/**
	 * 执行完过滤器后执行该方法的回调
	 */
	public function execute() {
		if ($this->callBack === null)
			$this->callBack = array(
				'WFrontController', 
				'process'
			);
		if (is_array($this->callBack)) {
			list($className, $action) = $this->callBack;
			if (!class_exists($className, true))
				throw new WException($className . ' is not exists!');
			if (!in_array($action, get_class_methods($className)))
				throw new WException('method ' . $action . ' is not exists in ' . $className . '!');
		} elseif (is_string($this->callBack))
			if (!function_exists($this->callBack))
				throw new WException($this->callBack . ' is not exists!');
		
		call_user_func_array($this->callBack, (array) $this->args);
	}
	
	/**
	 * 设置回调方法，执行完毕所有过滤器后将回调该方法
	 * 
	 * @param array $callback
	 * @param array $args
	 */
	public function setExecute($callback) {
		$args = func_get_args();
		if (count($args) > 1) {
			unset($args[0]);
			$this->args = $args;
		}
		$this->callBack = $callback;
	}
	
	/**
	 * 在filter链中动态的删除一个filter
	 * @param string $filterName
	 */
	protected function deleteFilter($filterName) {
		if (!in_array($filterName, $this->filters))
			return false;
		$deleteIndex = 0;
		foreach ($this->filters as $key => $value) {
			if ($value[0] == $filterName) {
				$deleteIndex = $key;
				unset($this->filters[$key]);
			}
		}
		if ($deleteIndex == $this->index)
			$this->index++;
	}
	
	/**
	 * 在filter链中动态的添加一个filter，当befor为空时，添加到程序结尾处
	 * @param string $filterName
	 * @param string $path
	 * @param string $beforFilter
	 */
	protected function addFilter($filterName, $path, $beforFilter = '') {
		$addIndex = count($this->filters);
		if ($beforFilter) {
			$exchange = null;
			foreach ($this->filters as $key => $value) {
				if ($key > $addIndex) {
					$this->filters[$key] = $exchange;
					$exchange = $value;
				}
				if ($value[0] == $beforFilter) {
					$addIndex = $key + 1;
					$exchange = $this->filters[$key + 1];
				}
			}
			$exchange != null && $this->filters[$key + 1] = $exchange;
		}
		$this->filters[$addIndex] = array(
			$filterName, 
			$path
		);
	}
	
	/**
	 * 获得当前过滤器状态，是否已经被初始化了
	 * @return string
	 */
	public function getState() {
		return $this->state;
	}
	
	/**
	 * 初始化一个过滤器
	 * @param WSystemConfig $config
	 */
	private function _initFilters($configObj) {
		$this->index = 0;
		$this->filters = array();
		$config = $configObj->getFiltersConfig();
		foreach ((array) $config as $key => $value) {
			if (($pos = strrpos($value, '.')) === false)
				$filterName = $value;
			else
				$filterName = substr($value, $pos + 1);
			$this->filters[] = array(
				$filterName, 
				$value, 
				$key
			);
		}
		$this->configs = $config;
	}
	
	/**
	 * @return WFilterFactory
	 */
	static function getFactory() {
		if (self::$instance === null) {
			$class = new ReflectionClass(__CLASS__);
			$args = func_get_args();
			self::$instance = call_user_func_array(array(
				$class, 
				'newInstance'
			), (array) $args);
		}
		return self::$instance;
	}
}