<?php
/**
 * A simple adaptor between core Kohana Events and MessageQueue_Model 
 * deferred messages.  The calling styles differ, but this allows plain
 * Kohana Event::run() calls to be routed through the message queue.
 *
 * It can also be switched to immediate events via messagequeue.deferred_events
 *
 * @package    MessageQueue
 * @subpackage libraries
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class DeferredEvent 
{
    public static $owner = null;

    private static $instance = null;
    private $proxies = array();
    private $mq = null;
    
    /**
     * Deferred version of Kohana's Event::add(), with an extra parameter for 
     * deferred messages.
     * 
     * @param   string   event name
     * @param   array    http://php.net/callback
     * @param   array    optional parameters passed to MessageQueue_Model->subscribe
     * @return  boolean
     */
    public static function add($name, $callback, $params=null)
    {
        // If deferred events disabled, fall back to plain vanilla events.
        if (!Kohana::config('messagequeue.deferred_events'))
            return Event::add($name, $callback);

        if (!is_array($callback) || !is_string($callback[0]))
            throw new Exception("Callback object must be name of static class");

        $instance = self::getInstance();

        $existing = Event::get($name);
        if (empty($existing)) {
            // Register a proxy to listen for the Kohana Event::run(), in case 
            // none has yet been registered.
            Event::add($name, array($instance, 'proxy_' . $name));
        }

        // Subscribe to messages proxied from Event::run() to the deferred 
        // queue, with the original callback identified in the context data.
        if (null == $params) $params = array();
        $instance->mq->subscribe(array_merge($params, array(
            'deferred' => true,
            'topic'    => $name,
            'object'   => 'DeferredEvent',
            'context'  => array(
                'object' => $callback[0],
                'method' => $callback[1]
            )
        )));

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
        $name = substr($name, strlen('proxy_'));

        $this->mq->disable_deferred = 
            !Kohana::config('messagequeue.deferred_events');

        // HACK: Accept a scheduled_for parameter in Event::run() data.
        $scheduled_for = !empty(Event::$data->scheduled_for) ?
            Event::$data->scheduled_for : null;

        // Finally, publish the event as a message.
        $this->mq->publish($name, Event::$data, $scheduled_for, self::$owner);
    }

    /**
     * Proxy messages received from the queue back into Event::run() style 
     * dynamic method calls.
     */
    public static function handleMessage($topic, $data, $context)
    {
        Event::$data =& $data;
        call_user_func(array($context['object'], $context['method']));
    }

    /**
     * Get a singleton instance of this class, not meant for public 
     * consumption.
     */
    public static function getInstance()
    {
        if (null === self::$instance)
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * Construct an instance.
     */
    public function __construct()
    {
        $this->mq = new MessageQueue_Model();
    }

}
