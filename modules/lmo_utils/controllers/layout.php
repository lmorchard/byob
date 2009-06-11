<?php
/**
 * Layout controller
 *
 * @package    LMO_Utils
 * @subpackage controllers
 * @author     l.m.orchard@pobox.com
 */
class Layout_Controller extends Controller {

    // Wrapper layout for current view
    public $layout = NULL;

    // Wrapped view for current method
    public $view = NULL;

    // Automatically render the layout?
    protected $auto_render = FALSE;

    /**
     * Constructor, sets up the layout and core views, as well as registering 
     * the display handler
     */
    public function __construct()
    {
        parent::__construct();

        $this->layout = View::factory();
        $this->view   = View::factory();

        // Register the final display handler.
        Event::add('system.post_controller', array($this, '_display'));
    }

    /**
     * Render a template wrapped in the global layout.
     */
    public function _display()
    {
        if (TRUE === $this->auto_render) {

            Event::run('LMO_Utils.layout.before_auto_render', $this);

            if ($this->layout && !$this->layout->get_filename()) {
                // If no filename set for layout, use "layout"
                $this->layout->set_filename('layout');
            }

            if ($this->view && !$this->view->get_filename()) {
                // If no view filename set, use controller/method by default.
                $this->view->set_filename(
                    Router::$controller . '/' . Router::$method
                );
            }

            if (!empty($this->view) && !empty($this->layout)) {
                // Render the core view as a var inside layout, then render layout.
                $this->layout
                    ->set('content', $this->view->render())
                    ->render(true);
            } else if (!empty($this->layout)) {
                // Only render the layout, since core view emptied.
                $this->layout->render(true);
            } else if (!empty($this->view)) {
                // Only render the core view, since the layout emptied.
                $this->view->render(true);
            }

            Event::run('LMO_Utils.layout.auto_rendered', $this);

        }
    }

}
