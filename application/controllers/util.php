<?php
/**
 * Index / home controller
 *
 * @package    BYOB
 * @subpackage controllers
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Util_Controller extends Local_Controller {

    protected $auto_render = FALSE;

    /**
     * Constructor
     */
    function __construct() 
    {
        parent::__construct();

        if ('cli' !== PHP_SAPI)
            die("For command-line use only.");

        restore_exception_handler();
        restore_error_handler();
        ob_end_clean();

        #set_error_handler(array($this, '_handleError'), E_ALL);

        $this->db = Database::instance(
            Kohana::config('model.database')
        );

        $args   = $_SERVER['argv'];
        $script = array_shift($args);
        $route  = array_shift($args);

        $this->args = $args;
    }

    /**
     * Util tool usage instructions
     */
    function index()
    {
        echo "TODO: Usage instructions\n";
    }

    /**
     * Create a user with name, email, and role.
     */
    function createlogin()
    {
        if (!isset($this->args) || 3 != count($this->args)) {
            echo count($this->args);
            echo "Usage: createlogin {screen name} {email} {role}\n";
            die;
        }

        list($login_name, $email, $role) = $this->args;

        Database::disable_read_shadow();

        $user = ORM::factory('login', $login_name);
        if ($user->loaded) {
            echo "Login '{$login_name}' already exists.\n";
            die;
        }

        $password = $this->_rand_string(7);

        if (!ORM::factory('profile')->register_with_login(array(
                'screen_name' => $login_name,
                'first_name' => $login_name,
                'last_name' => $login_name,
                'login_name' => $login_name,
                'email' => $email,
                'password' => $password
            ), true)) {  
            echo "Problem creating new profile!";
            die;
        };

        $new_profile = ORM::factory('profile', $login_name);
        $new_profile->save();
        $new_profile->add_role($role);

        echo "Profile ID {$new_profile->id} created for '{$login_name}' with role '{$role}'\n"; 
        echo "Password: {$password}\n";
    }

    /**
     * Generate a random string.
     * see: http://www.php.net/manual/en/function.mt-rand.php#76658
     */
    function _rand_string($len, $chars = 'abcdefghijklmnopqrstuvwxyz0123456789')
    {
        $string = '';
        for ($i = 0; $i < $len; $i++)
        {
            $pos = rand(0, strlen($chars)-1);
            $string .= $chars{$pos};
        }
        return $string;
    }

}
