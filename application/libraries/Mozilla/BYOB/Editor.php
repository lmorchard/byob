<?php
/**
 * BYOB editor module
 *
 * @package    Mozilla_BYOB_Editor
 * @subpackage Libraries
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Mozilla_BYOB_Editor {

    public $id        = 'change_me';
    public $title     = 'Change Me';
    public $view_name = null;
    public $review_view_name = null;

    private static $_instances = array();

    /**
     * If set, render this module's review view and shove the result into the 
     * appropriate slot.
     */
    public function renderReviewSection()
    {
        if (empty($this->review_view_name)) return;
        slot::append(
            'BYOB.repack.edit.review.sections',
            View::factory($this->review_view_name, Event::$data)
        );
    }

    /**
     * @param string $classname
     * @return Singleton
     */
    public static function getInstance($cls=null)
    {
        if (null===$cls && function_exists('get_called_class')) {
            $cls = get_called_class();
        }
        if (!isset(self::$_instances[$cls])) {
            self::$_instances[$cls] = new $cls();
        }
        return self::$_instances[$cls];
    }

    /**
     * Event handler to register this editor.
     */
    public static function register($cls=null)
    {
        if (null===$cls && function_exists('get_called_class')) {
            $cls = get_called_class();
        }
        $self = self::getInstance($cls);
        Mozilla_BYOB_EditorRegistry::register($self);

        if (!empty($self->review_view_name)) {
            // If the review view is supplied, register the handler to render 
            // it when it's time.
            Event::add('BYOB.repack.edit.review.renderSections',
                array($self, 'renderReviewSection'));
        }

        return $self;
    }

}
