<?php
/**
 * Permission model
 *
 * @package    auth_profiles
 * @subpackage models
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class Permission_Model extends ORM
{
    protected $has_and_belongs_to_many = array('roles');

    protected $known_permissions = null;

    /**
     * Gather known permissions from the entire application by running the 
     * event "auth_profiles.collect_permissions"*
     */
    public function find_known_permissions($force_fresh=false)
    {
        if (null===$this->known_permissions || $force_fresh) {
            $known_permissions = array();
            Event::run('auth_profiles.collect_permissions', $known_permissions);
            $this->known_permissions = $known_permissions;
        }
        return $this->known_permissions;
    }

    /**
	 * Returns the unique key for a specific value. This method is expected
	 * to be overloaded in models if the model has other unique columns.
	 *
     * If the key used in a find is a non-numeric string, search 'name' column.
     *
	 * @param   mixed   unique value
	 * @return  string
     */
    public function unique_key($id)
    {
        if (!empty($id) && is_string($id) && !ctype_digit($id)) {
            return 'name';
        }
        return parent::unique_key($id);
    }

    /**
	 * Finds and loads a single database row into the object.
     *
     * If a non-numeric string is passed, defer to find_by_name logic.
	 *
	 * @chainable
	 * @param   mixed  primary key or an array of clauses
	 * @return  ORM
     */
    public function find($id=null) {
        if (!empty($id) && is_string($id) && !ctype_digit($id)) {
            return $this->find_by_name($id);
        }
        return parent::find($id);
    }

    /**
	 * Finds and loads a single permission into the object.
     *
     * If a named permission is known, yet not found in database, an attempt 
     * to insert it will first be made.
     *
	 * @chainable
	 * @param   mixed  primary key or an array of clauses
	 * @return  ORM
     */
    public function find_by_name($name=null)
    {
        $known_permissions = $this->find_known_permissions();
        if (!isset($known_permissions[$name])) return null;

        $perm = parent::find(array('name'=>$name));
        if ($perm->loaded) return $perm;

        return $this->clear()->set(array(
            'name'        => $name,
            'description' => $known_permissions[$name]
        ))->save();
    }

    /**
	 * Finds multiple database rows and returns an iterator of the rows found.
     *
     * Attempts to ensure table is up to date with known permissions 
	 *
	 * @chainable
	 * @param   integer  SQL limit
	 * @param   integer  SQL offset
	 * @return  ORM_Iterator
     */
	public function find_all($limit = NULL, $offset = NULL)
    {
        $known_permissions = $this->find_known_permissions();
        $known_names = array_keys($known_permissions);

        // Find which of the known permissions are stored.
        $stored_permissions = array();
        if (!empty($known_names)) {
            $rows = $this->db->select('name')
                ->in('name', $known_names)
                ->get($this->table_name);
            foreach ($rows as $row) {
                $stored_permissions[] = $row->name;
            }
        }

        // Determine which permissions have not yet been stored.
        $permissions_to_store = array_diff(
            $known_names, $stored_permissions
        );

        // Insert rows for each new permission.
        foreach ($permissions_to_store as $name) {
            $this->db->insert($this->table_name, array(
                'name'        => $name, 
                'description' => $known_permissions[$name]
            ));
        }

        // Finally, continue on to normal find_all()
        return parent::find_all($limit, $offset);
    }
    
}
