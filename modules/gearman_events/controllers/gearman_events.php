<?php
/**
 * Actions to help set up a gearman worker and events
 *
 * @package    gearman_events
 * @subpackage controllers
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class Gearman_Events_Controller extends Controller
{
    protected $auto_render = FALSE;

    /**
     * Run the procesing loop at the command line for a few jobs and exit.
     */
    public function worker()
    {
        $servers  = Kohana::config('gearman_events.servers');
        $max_jobs = Kohana::config('gearman_events.max_jobs');

        $this->gm_worker = new GearmanWorker();
        $this->gm_worker->addServers($servers);

        $funcs = array( 'handleEvent', 'killRepackWorker', 'echojob' );
        foreach ($funcs as $func) {
            $this->gm_worker->addFunction($func, array($this, '_job_'.$func));
        }

        // Invent a random ID to flag log events.
        $this->worker_id = rand(10000,19999);
        $this->keep_running = TRUE;
        $this->log('debug', "Worker {$this->worker_id} started");
        for ($i=0; $i<$max_jobs && $this->keep_running; $i++) {
            $this->gm_worker->work(); 
        }
        $this->log('debug', "Worker {$this->worker_id} exiting");
    }

    /**
     * Issue kill and restart command to worker.
     */
    public function restart()
    {
        $this->_connectClient();
        $this->gm_client->doBackground("killRepackWorker", '');
    }

    /**
     * Issue kill and restart command to worker.
     */
    public function echojob()
    {
        $this->_connectClient();
        $this->gm_client->doBackground("echojob", json_encode($this->args));
    }

    /**
     * Generic event handler for DeferredEvent.
     */
    public function _job_handleEvent($job)
    {
        $params = json_decode($job->workload(), true);

        $this->log('info', "Worker {$this->worker_id} performing {$params['name']}: ".
            "{$params['callback'][0]}::{$params['callback'][1]}");

        Event::$data =& $params['data'];
        call_user_func($params['callback']);

        $this->log('info', "Worker {$this->worker_id} finished {$params['name']}: ".
            "{$params['callback'][0]}::{$params['callback'][1]}");
    }

    /**
     * Kill this worker, most useful if wrapped in a script that will restart. 
     */
    public function _job_killRepackWorker($job)
    {
        $this->log('debug', "Worker {$this->worker_id} killed");
        $this->keep_running = FALSE;
    }

    /**
     * Echo workload to the log.
     */
    public function _job_echojob($job)
    {
        $workload = $job->workload();
        $this->log('info', "Worker {$this->worker_id} echoes: {$workload}");
    }

    /**
     * Trap any errors from the run loop.
     */
    public function _handleError($errno, $errstr, $file, $line, $ctx)
    {
        $this->log('error', "Worker {$this->worker_id} ERROR $errno $errstr");
    }

    private function _connectClient()
    {
        $servers = Kohana::config('gearman_events.servers');
        $this->gm_client = new GearmanClient();
        $this->gm_client->addServers($servers);
    }

    /**
     * Wrapper to emit a log message and save the log.
     */
    private function log($level, $msg)
    {
        Kohana::log($level, $msg);
        Kohana::log_save();
    }

    /**
     * Utility function to prepare controller method for CLI use.
     */
    public function __construct()
    {
        parent::__construct();

        if ('cli' !== PHP_SAPI)
            die("For command-line use only.");

        restore_exception_handler();
        restore_error_handler();
        ob_end_clean();

        #set_error_handler(array($this, '_handleError'), E_ALL);

        $args   = $_SERVER['argv'];
        $script = array_shift($args);
        $route  = array_shift($args);

        $this->args = $args;
    }

}
