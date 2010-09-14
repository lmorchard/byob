<?php
/**
 * Model class that consults config for which database to use.
 *
 * @package    LMO_Utils
 * @subpackage libraries
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class Model extends Model_Core {

    /**
     * If needed, load the database instance, using a group configured as 
     * model.database.
     */
	public function __construct()
	{
		if (!is_object($this->db)) {
            $this->db = Database::instance(
                Kohana::config('model.database')
            );
		}
	}

	/**
	 * Creates and returns a new model.
	 *
	 * @chainable
	 * @param   string  model name
	 * @param   mixed   parameter for find()
	 * @return  Model
	 */
	public static function factory($model)
	{
		// Set class name
		$model = ucfirst($model).'_Model';

		return new $model();
	}

    /**
     * Try reconnecting to the database.
     */
    public function reconnect()
    {
        $this->db = Database::reconnect(Kohana::config('model.database'));
    }

}
