<?php
/**
 * Storage of a customized browser repack.
 *
 * @package    BYOB
 * @subpackage Models
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Repack_Model extends ORM
{
    protected $sorting = array('modified'=>'desc','created'=>'desc');
    
    private $rp = null;

    /**
     * Validation callback that checks to see if the given short name is taken 
     * any repack other than the one being validated.
     */
    public static function short_name_available($valid, $field)
    {
        $taken = (bool) ORM::factory('repack')
            ->where(array(
                'short_name' => $valid[$field],
                'uuid !='    => $valid['uuid']
            ))
            ->count_all();

        if ($taken) {
            $valid->add_error($field, 'short_name_available');
        }
    }

    /**
     * Create a new repack instance, stashing a reference to it in the storage 
     * model.
     *
     * @return Mozilla_BYOB_Repack
     */
    public function create($data=null)
    {
        return $this->rp = new Mozilla_BYOB_Repack($data);
    }
    
    /**
     * Accept a repack instance, setting metadata columns and serializing to 
     * the JSON storage column.
     *
     * @param  Mozilla_BYOB_Repack
     * @return Repack_Model
     */
    public function set(&$rp)
    {
        // Stash a reference to this repack instance.
        $this->rp = $rp;

        // Assign all the metadata values to columns.
        $meta = $rp->getMetadata();
        foreach ($meta as $name=>$value) {
            $this->{$name} = $value;
        }

        // Update the repack instance's ID
        if (!empty($rp->id)) {
            $this->id = $rp->id;
        }

        // Assign serialization of the object to the data column.
        $this->json_data = $rp->asJSON();

        // Force the ORM to think it's been loaded if there's an ID.
        if ($this->id) $this->loaded = true;

        // Return for chaining.
        return $this;
    }

    /**
     * Get a repack instance based on the results of the last query to this 
     * model.
     *
     * @return Mozilla_BYOB_Repack
     */
    public function get()
    {
        // If nothing has been loaded return null.
        if (!$this->loaded) return null;

        if (!$this->rp) {
            // If there's not already a stashed repack instance, create one 
            // from fetched JSON data.
            $this->rp = Mozilla_BYOB_Repack::factoryJSON($this->json_data);
        } else {
            // If there is a stashed instance, update it from JSON data.
            $this->rp->fromJSON($this->json_data);
        }

        // Update the repack instance's ID.
        $this->rp->id = $this->id;

        // Return the repack instance.
        return $this->rp;
    }

    /**
     * Store the state of the current repack instance.
     *
     * @param Mozilla_BYOB_Repack Optional new repack instance to use.
     * @return Repack_Model
     */
    public function save(&$rp = null)
    {
        if (!empty($rp)) {
            // If a repack is passed, stash it and use it.
            $rp->modified = gmdate('c');
            $rp->version = gmdate('YmdHis');
            $this->set($rp);
        } else if (!empty($this->rp)) {
            // If no new repack passed, use the existing one.
            $this->rp->modified = gmdate('c');
            $this->rp->version = gmdate('YmdHis');
            $this->set($this->rp);
        }

        // Use the ORM superclass to write to storage.
        $rv = parent::save();
        
        if (!empty($this->rp)) {
            // Update the repack instance's ID
            $this->rp->id = $this->id;
        }

        // Return for chaining.
        return $rv;
    }

    /**
     * Clear out the results of the last query, including the stashed repack 
     * instance.
     * 
     * @return Repack_Model
     */
    public function clear()
    {
        $this->rp = null;
        return parent::clear();
    }

}
