<?php
/**
 * Registration form error messages
 *
 * @author  l.m.orchard@pobox.com
 */
$lang = array(
    'login_name' => array(
        'default'
            => _('Valid login name is required.'),
        'required'             
            => _('Login name is required.'),
        'length'               
            => _('Login name must be between 3 and 12 characters in length'),
        'alpha_dash'           
            => _('Login name must contain only alphanumeric characters'),
        'isLoginNameAvailable' 
            => _('Login name is not available.')
    ),
    'email' => array(
        'default'
            => _('Valid email is required.'),
        'required' 
            => _('Email is required.'),
        'email'
            => _('Valid email is required.'),
        'is_email_available' 
            => _('A login has already been registered using this email address'),
    ),
    'email_confirm' => array(
        'default'
            => _('Valid email confirmation is required.'),
        'required' 
            => _('Email confirmation is required.'),
        'email'
            => _('Valid email confirmation is required.'),
        'matches'
            => _('Email confirmation does not match email.')
    ),
    'password' => array(
        'default'
            => _('Password is invalid.'),
        'length'
            => _('Password must be at least 6 characters long.'),
        'required' 
            => _('Password is required.')
    ),
    'password_confirm' => array(
        'required' 
            => _('Password confirmation is required.'),
        'matches'  
            => _('Password and confirmation must match.')
    ),
    'screen_name' => array(
        'default'
            => _('Screen name is not available.'),
        'required'              
            => _('Screen name is required.'),
        'length'                
            => _('Screen name must be between 3 and 64 characters in length'),
        'alpha_dash'            
            => _('Screen name must contain only alphanumeric characters'),
        'isScreenNameAvailable' 
            => _('Screen name is not available.'),
    ),
    'first_name' => array(
        'required'      
            => _('First name is required'),
        'standard_text' 
            => _('First name must contain only alphanumeric characters')
    ),
    'last_name' => array(
        'required'      
            => _('Last name is required'),
        'standard_text' 
            => _('Last name must contain only alphanumeric characters')
    ),
    'org_name' => array(
        'required'      
            => _('Organization name is required'),
    ),
    'recaptcha' => array(
        'default' 
            => _('Valid captcha response is required.')
    ),
    'old_password' => array(
        'default'
            => _('Old password is invalid.'),
        'required'
            => _('Old password is required'),
    ),
    'new_password' => array(
        'required'
            => _('New passwords is required'),
    ),
    'new_password_confirm' => array(
        'required'
            => _('New password confirmation required'),
        'matches'  
            => _('Password and confirmation must match.')
    ),
    'new_email' => array(
        'default' 
            => _('A valid new email is required'),
        'is_email_available' 
            => _('That email address is used by another login.'),
    ),
    'new_email_confirm' => array(
        'default' 
            => _('A valid new email confirmation is required')
    ),

    'phone' => array( 
        'default' => _('Phone number is required.') 
    ),
    'address_1' => array( 
        'default' => _('Street address is required.') 
    ),
    'city' => array( 
        'default' => _('City is required.') 
    ),
    'state' => array( 
        'default' => _('State is required.') 
    ),
    'zip' => array( 
        'default' => _('Zip is required.') 
    ),
    'country' => array( 
        'default' => _('Country is required.') 
    ),
);
