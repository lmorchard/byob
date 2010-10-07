<?php
/**
 * A simple adaptor between core Kohana Events and gearman_events
 * deferred messages.  The calling styles differ, but this allows plain
 * Kohana Event::run() calls to be routed through the message queue.
 *
 * @package    gearman_events
 * @subpackage libraries
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class DeferredEvent 
{
    public static $owner = null;

    private static $instance = null;
    private $proxies = array();
    
    /**
     * Deferred version of Kohana's Event::add(), with an extra parameter for 
     * deferred events.
     * 
     * @param   string   event name
     * @param   array    http://php.net/callback
     * @param   array    optional parameters passed to event handler in job
     * @return  boolean
     */
    public static function add($name, $callback, $params=null)
    {
        // If deferred events disabled, fall back to plain vanilla events.
        if (!Kohana::config('gearman_events.deferred_events'))
            return Event::add($name, $callback);

        if (!is_array($callback) || !is_string($callback[0]))
            throw new Exception("Callback object must be name of static class");

        $instance = self::getInstance();

        if (!isset($instance->proxies[$name])) {
            // Since no proxies have yet been set up for this event,
            // set up the list and register the proxy event handler
            Event::add($name, array($instance, 'proxy_' . $name));
            $instance->proxies[$name] = array();
        }

        // Stash the details about the callback to be proxied to gearman.
        $instance->proxies[$name][] = array(
            'name' => $name,
            'callback' => $callback,
            'params' => $params,
        );

        return true;
    }

    /**
     * Proxy a Kohana Event::run() into a message published to the queue.
     */
    public function __call($name, $args)
    {
        // Enforce proxy_* namespace for Event::run() events.
        if (strpos($name, 'proxy_') !== 0)
            throw new Exception('Unknown method ' . $name);

        // Bail out if no proxies set up for this event, which really shouldn't 
        // happen now that I think about it.
        $name = substr($name, strlen('proxy_'));
        if (empty($this->proxies[$name])) return; 

        // Queue up a handleEvent background job for each proxied callback, to 
        // be executed by a gearman worker.
        foreach ($this->proxies[$name] as $proxied) {
            $msg = array_merge($proxied, array( 'data' => Event::$data ));
            $this->gm_client->doBackground("handleEvent", json_encode($msg)); 
        }
    }

    /**
     * Get a singleton instance of this class, not meant for public 
     * consumption.
     */
    public static function getInstance()
    {
        if (null === self::$instance) self::$instance = new self();
        return self::$instance;
    }

    /**
     * Construct an instance.
     */
    public function __construct()
    {
        $servers = Kohana::config('gearman_events.servers');
        $this->gm_client = new GearmanClient();
        $this->gm_client->addServers($servers);
    }

}
