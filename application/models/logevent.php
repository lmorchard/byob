<?php
/**
 *
 */
class Logevent_Model extends ManagedORM
{

    // {{{ Model properties

    public $model_title = 'Log Event';

    public $table_column_titles = array(
        'id'         => 'ID',
        'uuid'       => 'Object UUID',
        'profile_id' => 'Profile',
        'action'     => 'Action',
        'details'    => 'Action details',
        'created'    => 'Created',
    );

    public $list_column_names = array(
        'id', 'profile_id', 'action', 'created',
    );

    protected $belongs_to = array('profile');

    protected $sorting = array(
        'created' => 'desc',
        'id'      => 'desc'
    );

    public static $current_profile_id = null;

    // }}}

    /**
     * Find all log events for a given UUID
     *
     * @param string UUID
     * @return ORM_Iterator
     */
    public function findByUUID($uuid)
    {
        return $this->where(array('uuid'=>$uuid))->find_all();
    }

    /**
     * Set the current profile ID for static log calls
     *
     * @param string Profile ID
     */
    public static function setCurrentProfileID($id)
    {
        self::$current_profile_id = $id;
    }

    /**
     * Static utility function for generating log events.
     *
     * @param   string       UUID of object acted upon
     * @param   string       Action performed on object
     * @param   string|array Detailed data on action performed
     * @returns Logevent_Model
     */
    public static function log($uuid, $action, $details=null, $data=null)
    {
        $event = new self();
        $event->set(array(
            'uuid'       => $uuid,
            'profile_id' => self::$current_profile_id,
            'action'     => $action,
        ));
        if (!empty($details)) {
            $event->details = $details;
        }
        if (!empty($data)) {
            $event->data = json_encode($data);
        }
        $event->save();
        return $event;
    }

}
