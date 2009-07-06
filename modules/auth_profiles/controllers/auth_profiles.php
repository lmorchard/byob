<?php 
/**
 * Controller handling all auth activities, including registration and 
 * login / logout
 *
 * @package    auth_profiles
 * @subpackage controllers
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class Auth_Profiles_Controller extends Local_Controller
{ 
    protected $auto_render = TRUE;

    /** 
     * Basic overall controller preamble
     */
    public function __construct()
    {
        parent::__construct();

        $protected_methods = array(
            'home', 'changeemail', 'logout'
        );
        if (!authprofiles::is_logged_in()) {
            if (in_array(Router::$method, $protected_methods)) {
                return authprofiles::redirect_login();
            }
        }

        $this->login_model = new Login_Model();
    }

    /**
     * Convenience action to redirect to logged in user's default profile.
     */
    public function home()
    {
        $this->auto_render = false;
        if (!authprofiles::is_logged_in()) {
            return url::redirect('login');
        } else {
            $auth_data = authprofiles::get_user_data();
            return url::redirect(sprintf(
                Kohana::config('auth_profiles.home_url'),
                authprofiles::get_profile('screen_name')
            ));
        }
    }

    /**
     * Combination login / registration action.
     */
    public function index()
    {
        return url::redirect('home');
    }


    /**
     * New user registration.
     */
    public function register()
    {
        $form_data = form::validate(
            $this, $this->login_model, 
            'validate_registration', 'form_errors_auth'
        );
        if (null===$form_data) return;

        $new_login = $this->login_model
            ->register_with_profile($form_data);

        if (!empty($new_login->email_verification_token)) {
            email::send_view(
                $new_login->new_email,
                'auth_profiles/register_email',
                array(
                    'email_verification_token' => 
                        $new_login->email_verification_token,
                    'login_name' => 
                        $new_login->login_name
                )
            );
        }
        Session::instance()->set_flash(
            'message', 
            'Check your email to verify your address before login.'
        );

        return url::redirect('login');
    }

    /**
     * User login action.
     */
    public function login()
    {
        $form_data = form::validate(
            $this, $this->login_model, 
            'validate_login', 'form_errors_auth'
        );
        if (null===$form_data) return;

        $login = ORM::factory('login', $form_data['login_name']);
        if (!$login->active) {
            $this->view->login_inactive = TRUE;
            return;
        } elseif (empty($login->email)) {
            $this->view->no_verified_email = TRUE;
            return;
        }

        // TODO: Allow profile selection here if multiple.
        $profile = $login->find_default_profile_for_login();

        $login->login($form_data);
        authprofiles::login($login->login_name, $login, $profile);

        if (isset($form_data['jump']) && substr($form_data['jump'], 0, 1) == '/') {
            // Allow post-login redirect only if the param starts with '/', 
            // interpreted as relatve to root of site.
            return url::redirect($form_data['jump']);
        } else {
            return url::redirect('/home');
        }
    }

    /**
     * User logout action.
     */
    public function logout()
    {
        authprofiles::logout();
    }


    /**
     * Manage profile details.
     */
    public function editprofile()
    {
        $params = Router::get_params(array(
            'screen_name' => null,
        ));

        $profile = ORM::factory('profile')->find($params['screen_name']);
        if (!$profile->checkPrivilege('edit')) 
            return Event::run('system.403');

        $this->view->profile = $profile;

        if ($profile->checkPrivilege('edit_roles')) {
            $this->view->set(array(
                'roles' => $profile->roles,
                'role_choices' => Kohana::config('auth_profiles.roles')
            ));
        }

        $form_data = form::validate(
            $this, $profile, 
            'validate_update', 'form_errors_auth'
        );
        if (null===$form_data) return;

        $profile->set($form_data)->save();

        if ($profile->checkPrivilege('edit_roles')) {
            if (empty($form_data['roles'])) {
                $profile->clear_roles();
            } else {
                $profile->add_roles($form_data['roles']); 
            }
            $profile->save();
        }

        Session::instance()->set_flash(
            'message', 'Profile updated'
        );
        return url::redirect(url::current());
    }

    /**
     * Start email address change process.
     */
    public function changeemail()
    {
        $form_data = form::validate(
            $this, $this->login_model, 
            'validate_change_email', 'form_errors_auth'
        );
        if (null===$form_data) return;

        $token = ORM::factory('login', authprofiles::get_login('id'))
            ->set_email_verification_token($form_data['new_email']);

        $this->view->email_verification_token_set = true;

        email::send_view(
            $form_data['new_email'],
            'auth_profiles/changeemail_email',
            array(
                'email_verification_token' => $token,
                'login_name' => authprofiles::get_login('login_name')
            )
        );
    }

    /**
     * Complete verification of a new email address.
     */
    public function verifyemail()
    {
        $token = ('post' == request::method()) ?
            $this->input->post('email_verification_token') :
            $this->input->get('email_verification_token');

        list($login, $new_email) = ORM::factory('login')
            ->find_by_email_verification_token($token);
        if (!$login) {
            $this->view->invalid_token = true;
            return;
        }
        $login->change_email($new_email);

        // TODO: Make auto-login on email verification configurable?
        if (!authprofiles::is_logged_in()) {

            $profile = $login->find_default_profile_for_login();
            $login->login();
            authprofiles::login($login->login_name, $login, $profile);
            Session::instance()->set_flash(
                'message', 
                'Email address verified. Welcome!'
            );

            return url::redirect('/home');
        }
    }


    /**
     * Change password for a login
     */
    public function changepassword()
    {
        // Try accepting a reset token from either GET or POST.
        $reset_token = ('post' == request::method()) ?
            $this->input->post('password_reset_token') :
            $this->input->get('password_reset_token');

        if (empty($reset_token) && !authprofiles::is_logged_in()) {
        
            // If no token and not logged in, jump to login.
            return authprofiles::redirect_login();
        
        } elseif (empty($reset_token) && authprofiles::is_logged_in()) {
        
            // Logged in and no token, so use auth login details.
            $login = ORM::factory('login', authprofiles::get_login('id')); 
        
        } else {
            
            // Look up the login by token, and abort if not found.
            $login = ORM::factory('login')
                ->find_by_password_reset_token($reset_token);
            if (!$login->loaded) {
                $this->view->invalid_reset_token = true;
                return;
            }

            // Use the found login ID and toss name into view.
            $this->view->forgot_password_login_name = $login->login_name;
            
            // Pre-emptively force logout in case current login and login 
            // associated with token differ.
            authprofiles::logout();

        }

        // Now that we know who's trying to change a password, validate the 
        // form appropriately
        $form_data = form::validate($this,
            $this->login_model, 
            empty($reset_token) ? 
                'validate_change_password' : 
                'validate_change_password_with_token', 
            'form_errors_auth'
        );
        if (null===$form_data) return;
        
        // Finally, perform the password change.
        $changed = $login->change_password($form_data['new_password']);
        if (!$changed) {
            // Something unexpected happened.
            $this->view->password_change_failed = true;
        } else {
            $this->view->password_changed = true;
        }
    }

    /**
     * Handle request to recover from a forgotten password.
     */
    public function forgotpassword() 
    {
        $form_data = form::validate($this,
            $this->login_model, 
            'validate_forgot_password', 'form_errors_auth'
        );
        if (null===$form_data) return;

        if (!empty($form_data['login_name'])) {
            $login = ORM::factory('login', $form_data['login_name']);
        } elseif (!empty($form_data['email'])) {
            $login = ORM::factory('login', array(
                'email' => $form_data['email']
            ));
        }

        $reset_token = $login->set_password_reset_token();
        $this->view->password_reset_token_set = true;

        email::send_view(
            $login->email,
            'auth_profiles/forgotpassword_email',
            array(
                'password_reset_token' => $reset_token,
                'login_name' => $login->login_name
            )
        );
    }


    /**
     * Profiles settings.
     */
    public function settings()
    {
        $params = Router::get_params(array(
            'screen_name' => null,
        ));

        $profile = ORM::factory('profile')->find($params['screen_name']);
        if (!$profile->checkPrivilege('edit')) 
            return Event::run('system.403');

        $u_name = rawurlencode($profile->screen_name);

        // Set up initial whiteboard, fire off event to gather content from 
        // interested listeners.
        $data = array(
            'controller' => $this, 
            'sections'   => array(
                array(
                    'title' => 'Basics',
                    'priority' => 999,
                    'items' => array(
                        array(
                            'url' => "profiles/{$u_name}/settings/basics/changepassword",
                            'title' => 'Change login password',
                            'description' => 'change current login password'
                        ),
                        array(
                            'url' => "profiles/{$u_name}/settings/basics/changeemail",
                            'title' => 'Change login email',
                            'description' => 'change current login email'
                        ),
                        array(
                            'url' => "profiles/{$u_name}/settings/basics/details",
                            'title' => 'Edit profile details',
                            'description' => 'change screen name, bio, etc.'
                        ),
                        /*
                        array(
                            'url' => "profiles/{$u_name}/settings/basics/logins",
                            'title' => 'Manage profile logins',
                            'description' => 'create and remove logins for this profile'
                        ),
                         */
                        /*
                        array(
                            'url' => "profiles/{$u_name}/settings/basics/delete",
                            'title' => 'Delete profile',
                            'description' => 'delete this profile altogether'
                        ),
                         */
                    )
                )
            )
        );
        
        Event::run('auth_profiles.before_settings_menu', $data);

        $this->view->set(array(
            'sections' => $data['sections'],
            'profile'  => $profile
        ));
    }
} 
