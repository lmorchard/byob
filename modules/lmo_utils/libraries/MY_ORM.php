<?php
/**
 * ORM class that consults config for which database to use.
 *
 * @package    LMO_Utils
 * @subpackage libraries
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class ORM extends ORM_Core {

    /**
     * Initialize the object with a configured database.
     */
	public function __initialize()
	{
		if (!is_object($this->db)) {
            $this->db = Database::instance(
                Kohana::config('model.database')
            );
        }
        parent::__initialize();
	}

    /**
     * Try reconnecting to the database.
     */
    public function reconnect()
    {
        $this->db = Database::reconnect(Kohana::config('model.database'));
    }

    /**
	 * Sets object values from an array.
	 *
	 * @chainable
	 * @return  ORM
     */
    public function set($arr=null)
    {
        if (empty($arr)) return;
        foreach ($arr as $name=>$value) {
            if (isset($this->table_columns[$name]))
                $this->{$name} = $value;
        }
        return $this;
    }

    /**
     * Before saving, update created/modified timestamps and generate a UUID if 
     * necessary.
     *
	 * @chainable
	 * @return  ORM
     */
    public function save()
    {
        if (isset($this->table_columns['created']) && empty($this->created)) {
            $this->created = gmdate('c');
        }
        if (isset($this->table_columns['modified'])) {
            $this->modified = gmdate('c');
        }
        if (isset($this->table_columns['uuid']) && empty($this->uuid)) {
            $this->uuid = uuid::uuid();
        }
        return parent::save();
    }

}
