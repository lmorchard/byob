<?php
/**
 * Index & miscellaneous controller
 *
 * @package    BYOB
 * @subpackage Controllers
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Index_Controller extends Local_Controller
{
    protected $auto_render = TRUE;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Site front page action.
     */
    public function index()
    {
        if (!authprofiles::is_logged_in()) {
            $this->view->repacks = ORM::factory('repack')
                ->where('profile_id', authprofiles::get_profile('id'))
                ->find_all();
        }

        $this->view->latest_repacks = ORM::factory('repack')
            ->whereReleased()
            ->where('is_public', '1')
            ->orderby('modified', 'desc')
            ->find_all(8)
            ;
    }

    /**
     * Contact form handler
     */
    public function contact()
    {
        $data = $this->input->post();

        if (authprofiles::is_logged_in()) {
            // Use profile & login details for contact info if logged in.
            $data['name'] = 
                authprofiles::get_profile('first_name') . ' ' .
                authprofiles::get_profile('last_name');
            $data['email'] =
                authprofiles::get_login('email');
        }

        if ('post' == request::method()) {

            // Set up validation for the form...
            $data = Validation::factory($data)
                ->pre_filter('trim')
                ->add_rules('referer', 'required')
                ->add_rules('name', 'required')
                ->add_rules('email', 'required','valid::email')
                ->add_rules('category', 'required')
                ->add_rules('comments', 'required')
                ;
            if ('post' == request::method() && !recaptcha::check()) {
                $data->add_error('recaptcha', recaptcha::error());
            }
            
            if (!$data->validate()) {
                // Raise errors if there are problems.
                form::$errors = $data->errors('form_errors_contact');
            } else {
                // Assemble admin & editor addresses and send off the email.
                $watcher_emails = array();
                $watchers = ORM::factory('profile')
                    ->find_all_by_role(array('admin', 'editor'));
                foreach ($watchers as $p)
                    $watcher_emails[] = $p->find_default_login_for_profile()->email;
                $recipients = array(
                    'to'  => $data['email'],
                    'bcc' => $watcher_emails
                );
                email::send_view(
                    $recipients, 'index/contact_email', $data->as_array()
                );
                $this->view->email_sent = true;
            }

        }

        form::$data = $this->view->data = $data;
    }

}
