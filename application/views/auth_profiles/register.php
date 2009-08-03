<?php slot::set('head_title', 'register'); ?>
<?php slot::set('crumbs', 'register a new account'); ?>
<?php
$state_list = array(
    'AL'=>"Alabama",  'AK'=>"Alaska",  'AZ'=>"Arizona",  'AR'=>"Arkansas",
    'CA'=>"California",  'CO'=>"Colorado",  'CT'=>"Connecticut",  
    'DE'=>"Delaware",  'DC'=>"District Of Columbia",  'FL'=>"Florida",  
    'GA'=>"Georgia",  'HI'=>"Hawaii",  'ID'=>"Idaho",  'IL'=>"Illinois",  
    'IN'=>"Indiana",  'IA'=>"Iowa",  'KS'=>"Kansas",  'KY'=>"Kentucky",  
    'LA'=>"Louisiana",  'ME'=>"Maine",  'MD'=>"Maryland",  
    'MA'=>"Massachusetts",  'MI'=>"Michigan",  'MN'=>"Minnesota",  
    'MS'=>"Mississippi",  'MO'=>"Missouri",  'MT'=>"Montana", 
    'NE'=>"Nebraska", 'NV'=>"Nevada", 'NH'=>"New Hampshire", 
    'NJ'=>"New Jersey", 'NM'=>"New Mexico", 'NY'=>"New York", 
    'NC'=>"North Carolina", 'ND'=>"North Dakota", 'OH'=>"Ohio",  
    'OK'=>"Oklahoma",  'OR'=>"Oregon",  'PA'=>"Pennsylvania",  
    'RI'=>"Rhode Island",  'SC'=>"South Carolina",  'SD'=>"South Dakota", 
    'TN'=>"Tennessee",  'TX'=>"Texas",  'UT'=>"Utah",  'VT'=>"Vermont",  
    'VA'=>"Virginia",  'WA'=>"Washington",  'WV'=>"West Virginia",  
    'WI'=>"Wisconsin",  'WY'=>"Wyoming"
);

$countries = array(
    "United States", "Canada", 
    "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and 
    Barbuda", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", 
    "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", 
    "Belize", "Benin", "Bhutan", "Bolivia", "Bosnia and Herzegovina", 
    "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", 
    "Cambodia", "Cameroon", "Cape Verde", "Central African Republic", 
    "Chad", "Chile", "China", "Colombi", "Comoros", "Congo (Brazzaville)", 
    "Congo", "Costa Rica", "Cote d'Ivoire", "Croatia", "Cuba", "Cyprus", "Czech 
    Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East 
    Timor (Timor Timur)", "Ecuador", "Egypt", "El Salvador", "Equatorial 
    Guinea", "Eritrea", "Estonia", "Ethiopia", "Fiji", "Finland", "France", 
    "Gabon", "Gambia, The", "Georgia", "Germany", "Ghana", "Greece", "Grenada", 
    "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Honduras", 
    "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", 
    "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", 
    "Kiribati", "Korea, North", "Korea, South", "Kuwait", "Kyrgyzstan", "Laos", 
    "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", 
    "Lithuania", "Luxembourg", "Macedonia", "Madagascar", "Malawi", "Malaysia", 
    "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", 
    "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia", "Morocco", 
    "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepa", "Netherlands", "New 
    Zealand", "Nicaragua", "Niger", "Nigeria", "Norway", "Oman", "Pakistan", 
    "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", 
    "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", "Saint Kitts 
    and Nevis", "Saint Lucia", "Saint Vincent", "Samoa", "San Marino", "Sao 
    Tome and Principe", "Saudi Arabia", "Senegal", "Serbia and Montenegro", 
    "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon 
    Islands", "Somalia", "South Africa", "Spain", "Sri Lanka", "Sudan", 
    "Suriname", "Swaziland", "Sweden", "Switzerland", "Syria", "Taiwan", 
    "Tajikistan", "Tanzania", "Thailand", "Togo", "Tonga", "Trinidad and 
    Tobago", "Tunisia", "Turkey", "Turkmenistan", "Tuvalu", "Uganda", 
    "Ukraine", "United Arab Emirates", "United Kingdom",  
    "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City", "Venezuela", "Vietnam", 
    "Yemen", "Zambia", "Zimbabwe"
);
$country_list = array();
foreach ($countries as $country) {
    $country_list[$country] = $country;
}

slot::start('login_details_intro');
?>
<div class="intro">
    <p>
    This is your basic account information, and will be used to login to the Build 
    Your Own Browser application. Your e-mail address will be used for account 
    verification, status notifications, and password resets, and must be a valid 
    address. If you are creating a customized version of Firefox for distribution 
    by an organization, you must use an email account using that organization's 
    domain name to ensure your submissions are approved.
    </p>
    <p class="required_note"><span>*</span> = Required field</p>
</div>
<?php
slot::end();

slot::start('account_details_intro');
?>
<div class="intro">
    <p>
    We require information about you and, if applicable, the organization you 
    represent. It helps us understand who is using BYOB, and to give us additional 
    ways to contact you should the need arise. This information is used solely by 
    Mozilla, and will never be shared with or sold to anyone else.
    </p>
    <p class="required_note"><span>*</span> = Required field</p>
</div>
<?php
slot::end();

echo form::build('register', array('class'=>'register'), array(
    form::fieldset('Login Details', array('class'=>'login'), array(
        '<li>' . slot::get('login_details_intro') . '</li>',
        form::field('input',    'login_name',       'Login name', array('class'=>'required'), array(
            "Enter the account name you will use to login to BYOB (4-12 ",
            "characters in length; alphanumeric, underscore, and hyphens only)"
        )),
        form::field('input',    'email',            'Email', array('class'=>'required'), array(
        )),
        form::field('input',    'email_confirm',    'Email (confirm)', array('class'=>'required'), array(
            "Enter a vaild email address. Verification will be ",
            "required before your account is activated. If you are ",
            "representing an organization, you must use an account with ",
            "that organization's domain name or your submissions may be ",
            "rejected."
        )),
        form::field('password', 'password',         'Password', array('class'=>'required'), array(
        )),
        form::field('password', 'password_confirm', 'Password (confirm)', array('class'=>'required'), array(
            "Enter your password here. Passwords must be a minimum ", 
            "of six characters in length. If you forget your password, reset ",
            "information will be sent to the email address above."
        )),
    )),
    form::fieldset('Account Details', array('class'=>'account'), array(
        '<li>' . slot::get('account_details_intro') . '</li>',
        form::field('input',    'first_name',  'First Name', array('class'=>'required'), array(
            'Your given name.'
        )),
        form::field('input',    'last_name',   'Last Name', array('class'=>'required'), array(
            'Your surname.'
        )),

        form::field('checkbox', 'is_personal', 'Personal account?', array('value'=>'1'), array(
            "Please check this box if you are using the versions of ",
            "Firefox you create for personal use (i.e. sharing with ",
            "friends and family, etc.)"
        )),

        form::field('dropdown', 'org_type',    'Organization Type', array(
            'options' => array(
                'corp'      => 'Corporation', 
                'nonprofit' => 'Non-Profit', 
                'other'     => 'Other',
            ),
            'class'=>'required'
        )),
        form::field('input',    'org_type_other', '(other)'),
        form::field('input',    'org_name',    'Organization Name', array('class'=>'required'), array(
            "Please enter the full, legal name of the organization you represent here."
        )),

        form::field('input',    'phone',       'Phone', array('class'=>'required'), array(
            'Your daytime contact number, with country code (US/Canada is "1").'
        )),
        form::field('input',    'fax',         'Fax', array(), array(
            'Your fax number, with country code (US/Canada is "1")'
        )),
        form::field('input',    'website',     'Website', array(), array(
            'Please provide the URL for your organizational or personal website.'
        )),

        form::field('input',    'address_1', 'Street Address 1', array('class'=>'required')),
        form::field('input',    'address_2', 'Street Address 2'),
        form::field('input',    'city',      'City', array('class'=>'required')),
        form::field('dropdown', 'state',     'State', array('class'=>'required', 'options'=>$state_list)),
        form::field('input',    'zip',       'Zip / Postal Code', array('class'=>'required', 'class'=>'required'), array(
        )),
        form::field('dropdown', 'country',   'Country', array('class'=>'required', 'options'=>$country_list), array(
            "Please provide your current mailing address or, if you are ",
            "representing an organization, your organization's mailing address ",
            "here."
        )),
    )),
    form::fieldset('finish', array(), array(
        '<li class="required"><label for="recaptcha">Captcha</label><span>' . recaptcha::html() . '</span></li>',
        form::field('submit', 'register', null, array('value'=>'Register')),
    ))
));
?>
