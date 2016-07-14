<?php

namespace light;

use PDO;
use medoo;

/**
 * @method select($columns, $where) Select data from database
 * @method insert($data = []) Insert new records in table
 * @method update($data = [], $where = []) Modify data in table
 * @method delete($where = []) Delete data from table
 * @method replace($column, $search, $replace, $where) Replace old data into new one
 *
 * @method get($columns = [], $where = []) Get only one record from table
 * @method has(array $where = []) Determine whether the target data existed
 * @method count($column, $where = []) Counts the number of rows
 * @method max($column, $where) Get the maximum value for the column
 * @method min($column, $where) Get the minimum value for the column
 * @method avg($column, $where) Get the average value for the column
 * @method sum($column, $where = []) Get the total value for the column
 *
 * @method $query($query) Insert new records in a table
 *
 * 模型基类 基于 Medoo 代理封装
 *
 * @package light
 */
abstract class Model
{
    /**
     * 数据连接实例
     *
     * @var array
     */
	protected $connect = [];

	/**
	 * 库名
	 *
	 * @var
	 */
    public $database;

	/**
	 * 表名
	 *
	 * @var
	 */
	public $table;

	/**
	 * 表主键
	 *
	 * @var
	 */
	public $primary = 'id';

	/**
	 * 是否是读操作
	 *
	 * @var bool
	 */
	public $read = false;

	/**
	 * 自动维护 created_at 和 updated_at
	 *
	 * @var array
	 */
	public $timestamps = false;

	/**
	 * 获取 sql 执行错误信息
	 *
	 * @return array
	 */
	public function error()
	{
		return $this->getConnectInstance()->error();
	}

	/**
	 * 获取数据库连接实例
	 *
	 * @return PDO
	 */
	public function pdo()
	{
		return $this->getConnectInstance()->pdo;
	}

	/**
	 * 根据主键查询一条数据
	 *
	 * @param $id
	 * @param string $columns
	 *
	 * @return bool|null
	 */
	public function find($id, $columns = '*')
	{
		if (!$id) return null;

		$this->read = true;
		return $this->getConnectInstance()->get($this->table, $columns, [$this->primary => $id]);
	}

	/**
	 * 根据主键删除一条数据
	 *
	 * @param $id
	 *
	 * @return bool|int
	 */
	public function destroy($id)
	{
		if (!$id) return false;

		return $this->getConnectInstance()->delete($this->table, [$this->primary => $id]);
	}

	/**
	 * 获取 sql 执行记录
	 *
	 * @return array
	 */
	public function log()
	{
		return $this->getConnectInstance()->log();
	}

	/**
	 * 获取最后一条 sql
	 *
	 * @return mixed
	 */
	public function last_query()
	{
		return $this->getConnectInstance()->last_query();
	}

	/**
	 * Medoo 调用代理
	 *
	 * @param $method
	 * @param $arguments
	 *
	 * @return mixed
	 */
	public function __call($method, $arguments)
	{
		$this->read = in_array($method, ['count', 'select', 'has', 'sum', 'max', 'min', 'avg', 'get']);

		$arguments = array_merge([$this->table], $arguments);

		// 自动维护数据库 插入更新时间
		// todo 可以指定其他字段
		if ($this->timestamps) {
			$timestamp = date('Y-m-d H:i:s');

			if ($method == 'insert' || $method == 'replace') {
				$arguments[1] =  array_merge($arguments[1], ['created_at' => $timestamp, 'updated_at' => $timestamp]);
			}

			if ($method == 'update') {
				$arguments[1] = array_merge($arguments[1], ['updated_at' => $timestamp]);
			}
		}

		return call_user_func_array([$this->getConnectInstance(), $method], $arguments);
	}

    /**
     * 获取 connect 实例
     *
     * @return \medoo
     */
    protected function getConnectInstance()
    {
        if (!array_key_exists($this->database, $this->connect)) {
            $database = app()->configGet('database');
            $driver = $database['driver'];
            $config = $database['config'];

            $master = $config[$this->database]['master'];
            $this->connect[$this->database]['master'] = self::connection($master, $driver);

            $slave = $config[$this->database]['slave'];
            $slave = $slave[array_rand($slave)];
            $this->connect[$this->database]['slave'] = self::connection($slave, $driver);
        }

        return $this->connect[$this->database][$this->read ? 'slave' : 'master'];
    }

    /**
     * 创建连接
     *
     * @param array $config
     * @param string $driver
     *
     * @return \medoo
     */
	static public function connection($config = [], $driver = 'mysql')
    {
        return new medoo([
            'database_type' => $driver,
            'database_name' => $config['database'],
            'server' => $config['server'],
            'username' => $config['username'],
            'password' => $config['password'],
            'charset' => $config['charset'],
            'port' => $config['port'],
            'option' => [PDO::ATTR_CASE => PDO::CASE_NATURAL]
        ]);
    }
}
