<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Database customizations to enable shadow DB for read
 *
 * @package    LMO_Utils
 * @subpackage libraries
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Database extends Database_Core {

    /** Global flag whether or not to use shadow DB for reads */
    public static $enable_shadow = true;

    private $shadow_db = null;

    /**
     * Destroy an existing DB instance and rebuild it to reconnect.
     */
    public static function & reconnect($name='default', $config=NULL)
    {
        if (isset(Database::$instances[$name])) {
            unset(Database::$instances[$name]);
        }
        return Database::instance($name,$config);
    }

    /**
     * Globally disable use of the shadow database for reads
     */
    public static function disable_read_shadow()
    {
        self::$enable_shadow = false;
    }

    /**
     * Globally enable use of the shadow database for reads
     */
    public static function enable_read_shadow()
    {
        self::$enable_shadow = true;
    }

    /**
     * Determine whether read shadow DB is enabled and usable.
     *
     * @return boolean
     */
    public function is_read_shadow_enabled()
    {
        return self::$enable_shadow && isset($this->config['read_shadow']);
    }

    /**
     * Get a handle on the read shadow DB
     *
     * @return Database instance of Database
     */
    public function get_shadow_db()
    {
        if (!$this->is_read_shadow_enabled()) {
            return null;
        }
        if (empty($this->shadow_db)) {
            $this->shadow_db = 
                Database::instance($this->config['read_shadow']);
        }
        return $this->shadow_db;
    }

    /**
     * Get the field data for a database table, along with the field's attributes.
     *
     * @param   string  table name
     * @return  array
     */
    public function list_fields($table = '')
    {
        if ($this->is_read_shadow_enabled()) {
            return $this->get_shadow_db()->list_fields($table);
        } else {
            return parent::list_fields($table);
        }
    }

    /**
     * Lists all the tables in the current database.
     *
     * @return  array
     */
    public function list_tables()
    {
        if ($this->is_read_shadow_enabled()) {
            return $this->get_shadow_db()->list_tables();
        } else {
            return parent::list_tables();
        }
    }

    /**
     * Runs a query into the driver and returns the result.
     *
     * @param   string  SQL query to execute
     * @return  Database_Result
     */
    public function query($sql = '')
    {
        if ($sql == '') return FALSE;

        // If read shadow is enabled, and defined in config, and this 
        // particular SQL query is not a write, try using the shadow DB 
        // instance.
        if ($this->is_read_shadow_enabled() && 
                !preg_match('#\b(?:INSERT|UPDATE|REPLACE|SET|DELETE|TRUNCATE)\b#i', $sql)) {
            return $this->get_shadow_db()->query($sql);
        }

        // No link? Connect!
        $this->link or $this->connect();

        // Start the benchmark
        $start = microtime(TRUE);

        if (func_num_args() > 1) //if we have more than one argument ($sql)
        {
            $argv = func_get_args();
            $binds = (is_array(next($argv))) ? current($argv) : array_slice($argv, 1);
        }

        // Compile binds if needed
        if (isset($binds))
        {
            $sql = $this->compile_binds($sql, $binds);
        }

        // Fetch the result
        $result = $this->driver->query($this->last_query = $sql);

        // Stop the benchmark
        $stop = microtime(TRUE);

        if ($this->config['benchmark'] == TRUE)
        {
            // Benchmark the query
            Database::$benchmarks[] = array('query' => $sql, 'time' => $stop - $start, 'rows' => count($result));
        }

        return $result;
    }

}
