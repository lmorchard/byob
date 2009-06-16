<?php
/**
 * Model managing message queue
 *
 * @TODO un-reserve a message to release it
 * @TODO mark a message as a failure
 * @todo tie a profile to a message as owner
 * @todo methods to find statistics on messages, ie. avg time of execution, messages over time, etc.
 *
 * @TODO allow selective dequeue of messages based on subscription pattern
 * @TODO implement dependent batches that are processed in sequence
 * @TODO somehow exercise the lock on finding a new message?
 *
 * @package    MessageQueue
 * @subpackage models
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class MessageQueue_Model extends Model
{
    protected $_table_name = 'message_queue';

    protected $_subscriptions;
    protected $_objs;

    // Flag allowing global disabling of deferred messages.
    public $disable_deferred = false;

    const DUPLICATE_IGNORE  = 0;
    const DUPLICATE_REPLACE = 1;
    const DUPLICATE_DISCARD = 2;

    /**
     * Initialize the model.
     */
    function __construct() 
    {
        parent::__construct();
        $this->_batch_uuid = uuid::uuid();
        $this->_batch_seq = 0;
        $this->_subscriptions = array();
        $this->_objs = array();
    }

    /**
     * Subscribe to a message topic
     *
     * @param string message topic
     * @param string|object name of a class to instantiate, or an object instance
     * @param string method to invoke on the instance
     * @param mixed context data passed as second parameter to instance method
     * @return mixed Opaque subscription handle, for use with unsubscribe
     */
    public function subscribe($params)
    {
        // Accept named parameters with defaults.
        extract(array_merge(array(
            'topic'     => null,
            'object'    => null,
            'method'    => 'handleMessage',
            'context'   => null,
            'deferred'  => false,
            'priority'  => 0,
            'duplicate' => self::DUPLICATE_IGNORE
        ), $params));
        
        // Create an array for this topic, if none exists
        if (!isset($this->_subscriptions[$topic]))
            $this->_subscriptions[$topic] = array();

        // Punt on serializing object instances in deferred subscriptions that 
        // can be handled out of process.
        if ($deferred && !is_string($object)) {
            throw new Exception(
                'Object instances cannot be used in deferred subscriptions.'
            );
        }

        // Add a new subscription record.
        $this->_subscriptions[$topic][] = array(
            $object, $method, $context, $deferred, $priority, $duplicate
        );

        // Return a pointer to this subscription usable by unsubscribe.
        return array($topic, count($this->_subscriptions[$topic])-1);
    }

    /**
     * Cancel a subscription to a message topic
     *
     * @param mixed Opaque subscription handle returned by the subscribe message.
     */
    public function unsubscribe($details) 
    {
        list($topic, $idx) = $details;
        // HACK: Just set the subscription to null, rather than deal with 
        // resorting the array or whatnot.
        $this->_subscriptions[$topic][$idx] = null;
    }

    /**
     * Publish a message to a topic
     *
     * @todo Allow topic pattern matching
     *
     * @param string message topic
     * @param mixed message data
     */
    public function publish($topic, $data=null, $scheduled_for=null, $owner=null) {

        if (isset($this->_subscriptions[$topic])) {
            
            // Distribute the published message to subscriptions.
            foreach ($this->_subscriptions[$topic] as $subscription) {

                // Skip cancelled subscriptions
                if (null == $subscription) continue;

                // Unpack the subscription array.
                list($object, $method, $context, $deferred, $priority, $duplicate) = $subscription;

                // Check if deferred jobs have been disabled for this message queue instance.
                if ($this->disable_deferred)
                    $deferred = false;

                if (!$deferred) {
                    // Handle non-deferred messages immediately.
                    $this->handle($topic, $object, $method, $context, $data);
                } else {
                    // Queue deferred messages.
                    $this->queue($topic, $object, $method, $context, $data, $priority, $scheduled_for, $duplicate, $owner);
                }

            }
        }

    }

    /**
     * Handle a message by calling the appropriate method on the specified 
     * object, instantiating it first if need be.
     *
     * @param string topic
     * @param mixed class name or object instance
     * @param string method name
     * @param mixed context data from subscription
     * @param mixed message data
     */
    public function handle($topic, $object=null, $method=null, $context=null, $body=null)
    {
        // If the first param is an array, assume it's a message array
        if (is_array($topic)) extract($topic);
        
        // One way or another, get an object for this subscription.
        if (is_object($object)) {
            $obj = $object;
        } else {
            if (!isset($this->_objs[$object])) 
                $this->_objs[$object] = new $object();
            $obj = $this->_objs[$object];
        }

        // Make a static call to default method name, or call the specified 
        // name dynamically.
        if (NULL == $method || $method == 'handleMessage') {
            $obj->handleMessage($topic, $body, $context);
        } else {
            call_user_func(array($obj, $method), $topic, $body, $context);
        }

    }

    /**
     * Queue a message for deferred processing.
     *
     * @param string topic
     * @param mixed class name or object instance
     * @param string method name
     * @param mixed context data from subscription
     * @param mixed message data
     * @param integer message priority
     * @param string scheduled time for message
     * @param integer duplicate message handling behavior
     * @param string optional ownership info
     *
     * @return array queued message data
     */
    public function queue($topic, $object, $method, $context, $data, $priority, $scheduled_for, $duplicate=self::DUPLICATE_IGNORE, $owner=null)
    {
        if (!is_string($object)) {
            throw new Exception(
                'Object instances cannot be used in deferred subscriptions.'
            );
        }

        // Encode the context and body data as JSON.
        $context = json_encode($context);
        $body    = json_encode($data);

        // Build a signature hash for this message.
        $signature = md5(join(':::', array(
            $object, $method, $context, $body
        )));

        // Check to see if anything should be done with signature duplicates.
        if ($duplicate != self::DUPLICATE_IGNORE) {

            // Look for an unreserved message with the same signature as the 
            // one about to be queued.
            $this->lock();
            $row = $this->db->select()
                ->from($this->_table_name)
                ->where('reserved_at IS', NULL)
                ->where('signature', $signature)
                ->get()->current();

            if ($row) {
                if ($duplicate == self::DUPLICATE_REPLACE) {
                    // In replace case, delete the existing message.
                    $this->db->delete(
                        $this->_table_name,
                        array('uuid' => $row->uuid)
                    );
                    $this->unlock();
                } else if ($duplicate == self::DUPLICATE_DISCARD) {
                    // In discard case, fail silently.
                    $this->unlock();
                    return false;
                }
            }

        }

        // Finally insert a new message.
        $row = array(
            'owner'         => $owner,
            'created'       => gmdate('c'),
            'modified'      => gmdate('c'),
            'uuid'          => uuid::uuid(),
            'batch_uuid'    => $this->_batch_uuid,
            'batch_seq'     => ($this->_batch_seq++),
            'priority'      => $priority,
            'scheduled_for' => $scheduled_for,
            'topic'         => $topic,
            'object'        => $object,
            'method'        => $method,
            'context'       => $context,
            'body'          => $body,
            'signature'     => $signature
        );
        $this->db->insert($this->_table_name, $row);

        return $row;
    }

    /**
     * Reserve a message from the queue for handling.
     *
     * @return array Message data
     */
    public function reserve()
    {
        $this->lock();
        try {

            // Start building query to find an unreserved message.  Account for 
            // priority and FIFO.
            $now = gmdate('c');
            $msg = $this->db->query("
                SELECT * FROM {$this->_table_name}
                WHERE
                    ( scheduled_for IS NULL OR scheduled_for < '{$now}' ) AND
                    reserved_at IS NULL AND
                    finished_at IS NULL AND
                    batch_uuid NOT IN (
                        SELECT DISTINCT l1.batch_uuid
                        FROM message_queue AS l1
                        WHERE
                            l1.reserved_at IS NOT NULL AND
                            l1.finished_at IS NULL
                    )
                ORDER BY
                    priority ASC, created ASC, batch_seq ASC
                LIMIT 1
            ")->result(FALSE)->current();

            if (!$msg) {
                $msg = null;
            } else {

                // Decode the data blobs.
                $msg['context'] = json_decode($msg['context'], true);
                $msg['body']    = json_decode($msg['body'], true);

                // Update the timestamp to reserve the message.
                $msg['reserved_at'] = gmdate('c');
                $this->db->update(
                    $this->_table_name,
                    array(
                        'modified'    => gmdate('c'),
                        'reserved_at' => $msg['reserved_at']
                    ),
                    array('uuid' => $msg['uuid'])
                );

            }

            // Finally, unlock the table and return the message.
            $this->unlock();
            return $msg;

        } catch (Exception $e) {
            // If anything goes wrong, be sure to unlock the table.
            $this->unlock();
            throw $e;
        }

    }

    /**
     * Mark a message as finished.
     *
     * @param string Message UUID.
     */
    public function finish($msg)
    {
        $row = $this->db->select()
            ->from($this->_table_name)
            ->where('uuid', $msg['uuid'])
            ->get()->current();
        if (!$row) {
            throw new Exception("No such message $uuid found.");
        }
        $this->db->update(
            $this->_table_name,
            array(
                'modified'    => gmdate('c'),
                'finished_at' => gmdate('c')
            ),
            array('uuid' => $msg['uuid'])
        );
    }

    /**
     * Process messages continually.
     */
    public function run()
    {
        while (True) {
            $msg = $this->runOnce();
            if (!$msg) sleep(1);
        }
    }

    /**
     * Process messages until the queue comes up empty.
     */
    public function exhaust($max_runs=NULL)
    {
        $cnt = 0;
        while ($msg = $this->runOnce()) {
            if ($max_runs != NULL && ( ++$cnt > $max_runs ) )
                throw new Exception('Too many runs');
        }
    }

    /**
     * Attempt to reserve and handle one message.
     */
    public function runOnce()
    {
        $msg = $this->reserve();
        if ($msg) try {
            $this->handle($msg);
            $this->finish($msg);
            Kohana::log('debug',
                "processed {$msg['topic']} {$msg['uuid']} ".
                "{$msg['object']} {$msg['method']}"
            ); 
        } catch (Exception $e) {
            Kohana::log('error',
                "EXCEPTION! {$msg['topic']} {$msg['uuid']} ".
                "{$msg['object']} {$msg['method']} " . 
                $e->getMessage()
            );
        }
        return $msg;
    }

    /**
     * Lock the table for read/write.
     */
    public function lock()
    {
        //$adapter_name = strtolower(get_class($db));
        //if (strpos($adapter_name, 'mysql') !== false) {
            $this->db->query(
                "LOCK TABLES {$this->_table_name} WRITE, ".
                // HACK: Throw in a few aliased locks for subqueries.
                "{$this->_table_name} AS l1 WRITE, ".
                "{$this->_table_name} AS l2 WRITE, ".
                "{$this->_table_name} AS l3 WRITE"
            );
        //}
    }

    public function unlock()
    {
        //$db = $this->getAdapter();
        //$adapter_name = strtolower(get_class($db));
        //if (strpos($adapter_name, 'mysql') !== false) {
            $this->db->query('UNLOCK TABLES'); 
        //}
    }

    /**
     * Delete all.  Useful for tests, but dangerous otherwise.
     */
    public function deleteAll()
    {
        if (!Kohana::config('model.enable_delete_all'))
            throw new Exception('Mass deletion not enabled');
        $this->db->query('DELETE FROM ' . $this->_table_name);
    }

    /**
     * Find queued messages by owner.
     *
     * @param string Ownership key
     */
    public function findByOwner($owner)
    {
        $rows = $this->db->select()
            ->from($this->_table_name)
            ->where('owner', $owner)
            ->get();
        return $rows;
    }
}
