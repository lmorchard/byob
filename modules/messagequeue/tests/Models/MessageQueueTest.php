<?php
/**
 * Test class for MessageQueue
 *
 * @TODO ensure combination of batch, sequence, priority
 *
 * @package    MessageQueue
 * @subpackage tests
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 * @group      models
 * @group      models.messagequeue
 */
class MessageQueueTest extends PHPUnit_Framework_TestCase 
{
    /**
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        DecafbadUtils_EnvConfig::apply('testing');

        $log = MessageQueueTest_LogCollector::getInstance();
        $log->reset();

        $mq = new MessageQueue_Model();
        $mq->deleteAll();
    }

    /**
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
    }

    /**
     * Exercise subscribe, unsubscribe, and publish for in-process pub/sub 
     */
    public function testInProcessSubscribeUnsubscribePublish()
    {
        $mq = new MessageQueue_Model();

        $log = MessageQueueTest_LogCollector::getInstance();

        $cn = 'MessageQueueTest_Listener';
        $l1 = new MessageQueueTest_Listener('l1');
        $l2 = new MessageQueueTest_Listener('l2');
        $l3 = new MessageQueueTest_Listener('l3');
        $l4 = new MessageQueueTest_Listener('l4');

        $s1 = $mq->subscribe(array('topic' => 't1', 'object' => $l1));
        $s2 = $mq->subscribe(array('topic' => 't2', 'object' => $l2, 'method' => 'h1'));
        $s3 = $mq->subscribe(array('topic' => 't2', 'object' => $cn, 'method' => 'h3', 'context' => 'c3'));
        $s4 = $mq->subscribe(array('topic' => 't3', 'object' => $l3, 'method' => 'h2', 'context' => 'c1'));
        $s5 = $mq->subscribe(array('topic' => 't3', 'object' => $cn, 'method' => 'h2', 'context' => 'c1'));
        $s6 = $mq->subscribe(array('topic' => 't4', 'object' => $l4, 'method' => 'h3', 'context' => 'c2'));
        $s7 = $mq->subscribe(array('topic' => 't5', 'object' => $cn));

        $mq->publish('t1'); 
        $mq->publish('t1', 'd1'); 
        $mq->publish('t2', 'd2'); 
        $mq->publish('t3', 'd3'); 
        $mq->publish('t4', 'd4'); 
        $mq->publish('t5', 'd5'); 

        $this->assertEquals(
            array(
                'l1 h0 t1 NU NU',
                'l1 h0 t1 d1 NU',
                'l2 h1 t2 d2 NU',
                'l0 h3 t2 d2 c3',
                'l3 h2 t3 d3 c1',
                'l0 h2 t3 d3 c1',
                'l4 h3 t4 d4 c2',
                'l0 h0 t5 d5 NU'
            ),
            $log->log
        );

        $log->reset();

        $mq->unsubscribe($s1);
        $mq->unsubscribe($s3);
        $mq->unsubscribe($s4);
        $mq->unsubscribe($s6);

        $mq->publish('t1'); 
        $mq->publish('t1', 'd1'); 
        $mq->publish('t2', 'd2'); 
        $mq->publish('t3', 'd3'); 
        $mq->publish('t4', 'd4'); 
        $mq->publish('t5', 'd5'); 

        $this->assertEquals(
            array(
                'l2 h1 t2 d2 NU',
                'l0 h2 t3 d3 c1',
                'l0 h0 t5 d5 NU'
            ),
            $log->log
        );

        $log->reset();

        $mq->unsubscribe($s2);
        $mq->unsubscribe($s5);
        $mq->unsubscribe($s7);

        $mq->publish('t1'); 
        $mq->publish('t1', 'd1'); 
        $mq->publish('t2', 'd2'); 
        $mq->publish('t3', 'd3'); 
        $mq->publish('t4', 'd4'); 
        $mq->publish('t5', 'd5'); 

        $this->assertEquals(array(), $log->log);
    }

    public function testDeferredSelectiveReservation()
    {
    }

    /**
     * Exercise basic deferred subscription functionality.
     */
    public function testDeferredPublishSubscribe()
    {
        $log = MessageQueueTest_LogCollector::getInstance();
        $mq = new MessageQueue_Model();

        // Ensure that deferred subscriptions refuse to accept an object instance.
        $l1 = new MessageQueueTest_Listener('l1');
        try {
            $s1 = $mq->subscribe(array('deferred' => true, 'topic' => 't1', 'object' => $l1));
            $this->fail('Object instances should be disallowed in deferred subscriptions');
        } catch (Exception $e) {
            // no-op
        }

        $cn = 'MessageQueueTest_Listener';
        $s1 = $mq->subscribe(array('deferred' => true, 'topic' => 't1', 'object' => $cn));
        $s2 = $mq->subscribe(array('deferred' => true, 'topic' => 't2', 'object' => $cn, 'method' => 'h1'));
        $s3 = $mq->subscribe(array('deferred' => true, 'topic' => 't2', 'object' => $cn, 'method' => 'h3', 'context' => 'c3'));
        $s4 = $mq->subscribe(array('deferred' => true, 'topic' => 't3', 'object' => $cn, 'method' => 'h2', 'context' => 'c1'));
        $s5 = $mq->subscribe(array('deferred' => true, 'topic' => 't3', 'object' => $cn, 'method' => 'h2', 'context' => 'c1'));
        $s6 = $mq->subscribe(array('deferred' => true, 'topic' => 't4', 'object' => $cn, 'method' => 'h3', 'context' => 'c2'));
        $s7 = $mq->subscribe(array('deferred' => true, 'topic' => 't5', 'object' => $cn));

        $mq->publish('t1'); 
        $mq->publish('t1', 'd1'); 
        $mq->publish('t2', 'd2'); 
        $mq->publish('t3', 'd3'); 
        $mq->publish('t4', 'd4'); 
        $mq->publish('t5', 'd5'); 

        // Ensure that deferred subscriptions are not immediately handled.
        $this->assertEquals( array(), $log->log );

        // Iterate through the queued messages, handle and finish each one.
        $cnt = 8;
        while ($msg = $mq->reserve()) {

            // Since only one batch is used here so far, ensure that serial 
            // message reservation is enforced.
            $msg2 = $mq->reserve();
            $this->assertTrue(
                !$msg2, 
                'No further message should be reserved until the '.
                'current one is finished.'
            );

            // Handle and finish the message.
            $mq->handle($msg);
            $mq->finish($msg);

            $this->assertGreaterThanOrEqual(
                0, --$cnt, 
                "Should run out of messages before counter zero"
            );

        }
        $this->assertEquals(0, $cnt, "Not enough messages dequeued");

        $this->assertEquals(
            array(
                'l0 h0 t1 NU NU',
                'l0 h0 t1 d1 NU',
                'l0 h1 t2 d2 NU',
                'l0 h3 t2 d2 c3',
                'l0 h2 t3 d3 c1',
                'l0 h2 t3 d3 c1',
                'l0 h3 t4 d4 c2',
                'l0 h0 t5 d5 NU'
            ),
            $log->log
        );

    }

    /**
     * Publish some deferred message scheduled over a small stretch of time.  
     * Ensure processing, order, and timing.
     */
    public function testDeferredScheduledMessage()
    {
        $log = MessageQueueTest_LogCollector::getInstance();

        $base_msg = array(
            'deferred' => true, 
            'object'   => 'MessageQueueTest_Listener'
        );

        $mq = new MessageQueue_Model();
        $mq->subscribe(array_merge($base_msg, array('topic'=>'t1')));

        // Publish some messages scheduled in reverse order, each 2 
        // seconds apart.
        $start = time();
        $mq->publish('t1', 'd1', gmdate('c', $start + 10)); 
        $mq->publish('t1', 'd2', gmdate('c', $start + 8)); 
        $mq->publish('t1', 'd3', gmdate('c', $start + 6)); 
        $mq->publish('t1', 'd4', gmdate('c', $start + 4)); 
        $mq->publish('t1', 'd5', gmdate('c', $start + 2));
        
        // Loop for 12 seconds, repeatedly attempting to reserve message every 
        // 1/2 second.  Ensure the proper number of messages are processed.
        $cnt = 5;
        $reserve_times = array();
        $last = $start;
        while ( ($now=time()) - $start < 12) {

            $msg = $mq->reserve();
            if ($msg) {
                
                // Check time elapsed since last message.
                $this->assertGreaterThanOrEqual(
                    2, $now - $last,
                    "Should have taken at least 2 seconds since last message."
                );
                $last = $now;

                // Process the message.  This assumes the time to process is 
                // sub-second with respect to the rest of the test.
                $mq->handle($msg);
                $mq->finish($msg);

                // Ensure the correct number of messages are processed.
                $this->assertGreaterThanOrEqual(
                    0, --$cnt, 
                    "Should run out of messages before counter zero"
                );

            }

            // Sleep for a 1/2 second before trying again for a message.
            usleep(500000);
        }
        $this->assertEquals(
            0, $cnt, 
            "Not enough messages dequeued in time"
        );

        // Finally, verify the results of handling the messages.
        $this->assertEquals(
            array(
                'l0 h0 t1 d5 NU',
                'l0 h0 t1 d4 NU',
                'l0 h0 t1 d3 NU',
                'l0 h0 t1 d2 NU',
                'l0 h0 t1 d1 NU'
            ),
            $log->log
        );

    }

    /**
     * Exercise replace/discard behavior on duplicates by varying scheduled 
     * time and watching which message (first or last) is processed.
     */
    public function testDeferredDuplicateReplaceOrUnique()
    {
        $log = MessageQueueTest_LogCollector::getInstance();

        $base_msg = array(
            'deferred' => true, 
            'object'   => 'MessageQueueTest_Listener'
        );

        $mq = new MessageQueue_Model();
        $mq->subscribe(array_merge($base_msg, array(
            'topic'     => 't0'
            //'duplicate' => MessageQueue::DUPLICATE_IGNORE
        )));
        $mq->subscribe(array_merge($base_msg, array(
            'topic'     => 't1', 
            'duplicate' => MessageQueue_Model::DUPLICATE_REPLACE
        )));
        $mq->subscribe(array_merge($base_msg, array(
            'topic'     => 't2', 
            'duplicate' => MessageQueue_Model::DUPLICATE_DISCARD
        )));

        // Under DUPLICATE_REPLACE, each additional message to the same 
        // object/method with identical context/data will push the scheduled 
        // time forward.
        $start = time();
        $mq->publish('t0', 'd1', gmdate('c', $start + 2));
        $mq->publish('t1', 'd1', gmdate('c', $start + 4));
        $mq->publish('t1', 'd1', gmdate('c', $start + 6)); 
        $mq->publish('t1', 'd1', gmdate('c', $start + 8)); 

        $last = $start;
        while ( ($now=time()) - $start < 10) {
            $msg = $mq->reserve();
            if ($msg) {
                $this->assertGreaterThanOrEqual(8, $now - $last, 
                    "Should have taken at least 6 seconds until message.");
                $last = $now;
                $mq->handle($msg);
                $mq->finish($msg);

                // Ensure the replacement topic got a message through.
                $this->assertEquals('t1', $msg['topic']);
            }
            usleep(500000);
        }

        // Under DUPLICATE_DISCARD, each additional message to the same 
        // object/method with identical context/data will be discarded
        // so that the original scheduled time stands.
        $start = time();
        $mq->publish('t0', 'd1', gmdate('c', $start + 2));
        $mq->publish('t2', 'd1', gmdate('c', $start + 4));
        $mq->publish('t2', 'd1', gmdate('c', $start + 6)); 
        $mq->publish('t2', 'd1', gmdate('c', $start + 8)); 

        $last = $start;
        while ( ($now=time()) - $start < 10) {
            $msg = $mq->reserve();
            if ($msg) {
                $this->assertLessThanOrEqual(3, $now - $last, 
                    "Should have taken no more than 3 seconds until message.");
                $last = $now;
                $mq->handle($msg);
                $mq->finish($msg);

                // Ensure the original topic got a message through.
                $this->assertEquals('t0', $msg['topic']);
            }
            usleep(500000);
        }

    }

    /**
     * Publish deferred messages paired with prioritized subscriptions and 
     * ensure that they're processed in priority order.
     */
    public function testDeferredMessagePriority()
    {
        $log = MessageQueueTest_LogCollector::getInstance();

        $base_msg = array(
            'deferred' => true, 
            'object'   => 'MessageQueueTest_Listener'
        );

        // Create some subscriptions with priorities.  The subscription 
        // creation is jumbled up a bit to get a mix of priority and natural
        // subscription order as influences on message processing order.
        $mq = new MessageQueue_Model();
        $mq->subscribe(array_merge($base_msg, array('topic'=>'t3', 'context'=>'c4', 'priority'=>0)));
        $mq->subscribe(array_merge($base_msg, array('topic'=>'t3', 'context'=>'c5', 'priority'=>0)));
        $mq->subscribe(array_merge($base_msg, array('topic'=>'t3', 'context'=>'c6', 'priority'=>0)));
        $mq->subscribe(array_merge($base_msg, array('topic'=>'t1', 'context'=>'c2', 'priority'=>-15)));
        $mq->subscribe(array_merge($base_msg, array('topic'=>'t1', 'context'=>'c1', 'priority'=>-20)));
        $mq->subscribe(array_merge($base_msg, array('topic'=>'t2', 'context'=>'c3', 'priority'=>-10)));
        $mq->subscribe(array_merge($base_msg, array('topic'=>'t1', 'context'=>'c7', 'priority'=>10)));
        $mq->subscribe(array_merge($base_msg, array('topic'=>'t2', 'context'=>'c9', 'priority'=>20)));
        $mq->subscribe(array_merge($base_msg, array('topic'=>'t2', 'context'=>'c8', 'priority'=>15)));

        // Publish some messages that hit each subscribed topic.
        $mq->publish('t3', 'd3'); 
        $mq->publish('t2', 'd2'); 
        $mq->publish('t1', 'd1'); 
        
        // Process all available messages.
        $mq->exhaust(10);

        // The order of messages resulting from subscription priorities should 
        // match the order of numbers in the context data.  This sums up the 
        // interaction between priority and natural subscription order.
        $this->assertEquals(
            array(
                'l0 h0 t1 d1 c1',
                'l0 h0 t1 d1 c2',
                'l0 h0 t2 d2 c3',
                'l0 h0 t3 d3 c4',
                'l0 h0 t3 d3 c5',
                'l0 h0 t3 d3 c6',
                'l0 h0 t1 d1 c7',
                'l0 h0 t2 d2 c8',
                'l0 h0 t2 d2 c9'
            ),
            $log->log
        );

    }

    /**
     * Try out parallel handling of several serial batches by leaving some 
     * batches with hanging reservations as the queue is exhausted.
     */
    public function testParallelDeferredBatches()
    {
        $log = MessageQueueTest_LogCollector::getInstance();

        $base_msg = array(
            'deferred' => true, 
            'object'   => 'MessageQueueTest_Listener'
        );

        // Set up a few queues and respective subscriptions.
        $mq1 = new MessageQueue_Model();
        $mq1->subscribe(array_merge($base_msg, array('topic'=>'t1_1')));
        $mq1->subscribe(array_merge($base_msg, array('topic'=>'t1_2')));
        $mq1->subscribe(array_merge($base_msg, array('topic'=>'t1_3')));
        $mq2 = new MessageQueue_Model();
        $mq2->subscribe(array_merge($base_msg, array('topic'=>'t2_1')));
        $mq2->subscribe(array_merge($base_msg, array('topic'=>'t2_2')));
        $mq2->subscribe(array_merge($base_msg, array('topic'=>'t2_3')));
        $mq3 = new MessageQueue_Model();
        $mq3->subscribe(array_merge($base_msg, array('topic'=>'t3_1')));
        $mq3->subscribe(array_merge($base_msg, array('topic'=>'t3_2')));
        $mq3->subscribe(array_merge($base_msg, array('topic'=>'t3_3')));

        // Interleave messages published to the queues in order to simulate 
        // parallel activity.
        $mq1->publish('t1_1'); 
        $mq2->publish('t2_1'); 
        $mq3->publish('t3_1'); 
        $mq1->publish('t1_2'); 
        $mq2->publish('t2_2'); 
        $mq3->publish('t3_2'); 
        $mq1->publish('t1_3'); 
        $mq2->publish('t2_3'); 
        $mq3->publish('t3_3'); 

        // Use yet another queue instance to simulate an external worker.
        $mq = new MessageQueue_Model();

        // Reserve the first message from first queue, but leave it hanging.
        $m1 = $mq->reserve();
        $mq->handle($m1);

        // Reserve the second message, finish it.
        $mq->runOnce();

        // Reserve the third message, leave it hanging.
        $m3 = $mq->reserve();
        $mq->handle($m3);

        // This should exhaust messages from the second queue.
        $mq->exhaust(10);

        // This should exhaust messages from the third queue.
        $mq->finish($m3);
        $mq->exhaust(10);

        // This should exhaust messages from the first queue.
        $mq->finish($m1);
        $mq->exhaust(10);

        // The log results should reflect the parallel batches / serial 
        // handling logic.
        $this->assertEquals(
            array(
                'l0 h0 t1_1 NU NU',
                'l0 h0 t2_1 NU NU',
                'l0 h0 t3_1 NU NU',
                'l0 h0 t2_2 NU NU',
                'l0 h0 t2_3 NU NU',
                'l0 h0 t3_2 NU NU',
                'l0 h0 t3_3 NU NU',
                'l0 h0 t1_2 NU NU',
                'l0 h0 t1_3 NU NU',
            ),
            $log->log
        );

    }
    
}

class MessageQueueTest_LogCollector
{
    public $log;

    protected static $_instance = null;
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $this->reset();
    }

    public function log($msg) {
        $this->log[] = $msg;
    }

    public function reset() {
        $this->log = array();
    }
}

class MessageQueueTest_Listener
{
    public $log;
    public $id;

    public function __construct($id='l0') {
        $this->id = $id;
        $this->log = array();
    }

    private function _log($method, $topic, $data, $context) {
        if ($data == null) $data = 'NU';
        if ($context == null) $context = 'NU';
        MessageQueueTest_LogCollector::getInstance()->log(
            $this->id . " $method $topic $data $context"
        );
    }
    
    public function handleMessage($topic, $data, $context) {
        $this->_log('h0', $topic, $data, $context);
    }

    public function h1($topic, $data, $context) {
        $this->_log('h1', $topic, $data, $context);
    }

    public function h2($topic, $data, $context) {
        $this->_log('h2', $topic, $data, $context);
    }

    public function h3($topic, $data, $context) {
        $this->_log('h3', $topic, $data, $context);
    }

}
