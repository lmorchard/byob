<?php
/**
 * Actions to help process and maintain the message queue
 *
 * @todo admin methods to view queue statistics, health, etc
 * @todo personal methods to see reports on messages per profile
 *
 * @package    MessageQueue
 * @subpackage controllers
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class Messagequeue_Controller extends Controller
{ 
    protected $auto_render = TRUE;

    private function _prepareForCli()
    {
        if ('cli' !== PHP_SAPI)
            die("For command-line use only.");

        restore_exception_handler();
        restore_error_handler();
        ob_end_clean();

        $args   = $_SERVER['argv'];
        $script = array_shift($args);
        $route  = array_shift($args);
        return $args;
    }

    /**
     * Run the procesing loop at the command line.
     */
    public function run()
    {
        $args = $this->_prepareForCli();
        $mq = new MessageQueue_Model();
        $mq->run();
    }
    
    /**
     * Run the procesing loop at the command line.
     */
    public function runonce()
    {
        $args = $this->_prepareForCli();
        $mq = new MessageQueue_Model();
        $msg = $mq->runOnce();
    }
    
    /**
     * Run one processing loop on the queue and output status in JSON.
     */
    public function runonce_json()
    {
        $mq = new MessageQueue_Model();
        $msg = $mq->runOnce();

        if ($msg) {
            $out = json_encode(array(
                'empty' => false,
                'uuid'  => $msg['uuid'] 
            ));
        } else {
            header('HTTP/1.1 304 Not Modified');
            $out = json_encode(array(
                'empty' => true
            ));
        }

        if (!isset($_GET['callback'])) {
            $callback = FALSE;
        } else {
            $callback = preg_replace(
                '/[^0-9a-zA-Z\(\)\[\]\,\.\_\-\+\=\/\|\\\~\?\!\#\$\^\*\: \'\"]/', '', 
                $_GET['callback']
            );
        }

        $this->auto_render = FALSE;
        $this->view = null;
        if ($callback) {
            header('Content-Type: text/javascript');
            echo "$callback($out)";
        } else {
            header('Content-Type: application/json');
            echo $out;
        }

    }

}
