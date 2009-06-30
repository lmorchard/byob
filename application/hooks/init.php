<?php
/**
 * Initialization hook for BYOB
 *
 * @package    Mozilla_BYOB
 * @subpackage hooks
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Mozilla_BYOB {

    /**
     * Initialize and wire up event responders.
     */
    public static function init()
    {
        DeferredEvent::add(
            'BYOB.process_repack',
            array('Repack_Model', 'handleProcessRepackEvent')
        );
        DeferredEvent::add(
            'BYOB.move_builds',
            array('Repack_Model', 'handleMoveBuildsEvent')
        );
        DeferredEvent::add(
            'BYOB.delete_builds',
            array('Repack_Model', 'handleDeleteBuildsEvent')
        );
    }

}
Event::add('system.ready', array('Mozilla_BYOB', 'init'));
