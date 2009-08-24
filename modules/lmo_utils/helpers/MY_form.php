<?php
/**
 * Custom form helpers
 *
 * @package    LMO_Utils
 * @subpackage helpers
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class form extends form_Core
{
    public static $errors = array();
    public static $data = array();

    /**
     * Perform model-based form POST data validation.
     *
     * @param  string / Model   name of model or model instance
     * @param  string / array   name of model validation method, or callback array
     * @param  string           name of the error messages file
     * @return Validation       validation object with data, or null on validation failure
     */
    public static function validate($ctrl, $model, $callback, $errors, $require_post=true) {
        self::$data = $ctrl->view->form_data = array_merge(
            $model->loaded ?
                $model->as_array() : array(),
            ('post' == request::method()) ? 
                $ctrl->input->post() : $ctrl->input->get()
        );
        
        if ($require_post && 'post' != request::method()) {
            return;
        }
        
        if (!is_object($model)) {
            $cls = ucfirst($model).'_Model';
            $model = new $cls();
        }
        
        if (is_string($callback)) {
            $callback = array($model, $callback);
        }

        $is_valid = call_user_func_array($callback, array(&self::$data));

        if (!$is_valid) {
            self::$errors = $ctrl->view->form_errors = 
                self::$data->errors($errors);
            return null;
        }

        return self::$data;
    }

    /**
     * Build a form from an array of lines.
     *
     * @param string Form URL
     * @param array Form element attributes
     * @param array List of form elements
     */
    public static function build($url, $attrs, $arr)
    {
        $out = array();

        $out = array_merge($out, array(
            form::open($url, $attrs),
            form::errors(),
            (!empty($arr)) ? join("\n", $arr) : '',
            form::close()
        ));

        return join("\n", $out);
    }

    /**
     *
     */
    public static function errors()
    {
        $out = array();
        if (!empty(self::$errors)) {
            $out[] = '<ul class="errors">';
            foreach (self::$errors as $field=>$error) {
                $out[] = '<li class="'.html::specialchars($field, false).'">'.
                    html::specialchars($error, false).'</li>';
            }
            $out[] = '</ul>';
        }
        return join("\n", $out);
    }

    /**
     * Build a fieldset from an array of lines
     *
     * @param string Fieldset legend
     * @param array  Fieldset element attributes
     * @param array  List of form elements
     */
    public static function fieldset($legend, $attrs, $arr)
    {
        return join("\n", array(
            form::open_fieldset($attrs),
            form::legend($legend),
            html::ul($arr, array('class'=>'fields')),
            form::close_fieldset()
        ));
    }

    /**
     * Wrap a form field element in a list item with an associated label 
     * element.  The field's value is derived using form::value().  An 'error' 
     * class is also applied to the list element if the field's name appears in 
     * form::$errors
     *
     * @param string Field type, corresponding form:: helper
     * @param string Field name
     * @param string Field label text
     * @param array  Field attributes
     */
    public static function field($type, $name, $label=null, $params=null, $arr=null)
    {
        if (null == $params) $params = array();

        $params_class = (isset($params['class'])) ?  $params['class'] : '';
        unset($params['class']);

        if ('checkbox' == $type) {
            // For checkboxes, the checked attribute is the significant thing.
            $value = form::value($name, @$params['checked']);
        } else {
            // All other fields care about the value attrib.
            $value = form::value($name, @$params['value']);
        }

        if ('hidden' == $type) {

            // Hidden form fields go unadorned.
            return form::hidden(array($name => $value));
        
        } else {

            $classes = array($type);
            if (!empty($params_class)) {
                $classes[] = $params_class;
            }

            if ('checkbox' == $type) {

                $field = form::checkbox(
                    array_merge(array(
                        'name' => $name, 
                        'class' => join(' ', $classes)
                    ), $params),
                    @$params['value'], $value, false
                );

            } else if ('dropdown' == $type || 'select' == $type) {

                // HACK: Keeps 'options' out of HTML attributes
                $options = $params['options'];
                unset($params['options']);

                $field = form::dropdown(
                    array_merge(array(
                        'name' => $name, 
                        'class' => join(' ', $classes)
                    ), $params),
                    $options, $value, ''
                );

            } else if ('radio' == $type) {

                // HACK: Keeps 'options' out of HTML attributes
                $options = $params['options'];
                unset($params['options']);

                $out = array();
                foreach ($options as $opt_value => $opt_label) {
                    $out[] = form::radio($name, $opt_value, ($opt_value == $value));
                    $out[] = '<span>' . html::specialchars($opt_label) . '</span>';
                }

                $field = '<span class="choices">'.join(' ', $out).'</span>';

            } else {

                // Dynamic dispatch to other form::* static helpers.
                $field = call_user_func(
                    array('form', $type), 
                    array('name' => $name, 'class' => join(' ', $classes)),
                    $value, '', false
                );

            }

            // List item classes consist of the name of the field type, along 
            // with 'error' if field present in $form::errors
            $classes = array($type);
            if (!empty($params_class))
                $classes[] = $params_class;
            if ('input' == $type)
                $classes[] = isset($params['type']) ? $params['type'] : 'text';
            if (!empty(self::$errors) && array_key_exists($name, self::$errors))
                $classes[] = 'error';
            $li_attrs = html::attributes(array(
                'class'=>join(' ', $classes)
            ));

            // Finally, assemble the whole mess and return it.
            return join("\n", array(
                '<li ' . $li_attrs .'>',
                ($label != null) ?
                    form::label($name, $label) : 
                    form::label(array('for'=>$name, 'class'=>'hidden'), ''),
                $field,
                (!empty($arr)) ? '<p class="notes">'.join("\n", $arr).'</p>' : '',
                '</li>'
            ));

        }
    }

    /**
     * Build a captcha field
     *
     * @param string Field name
     * @param string Field label text
     * @param array  Field attributes
     */
    public static function captcha($name, $label, $params=null)
    {
        if (null == $params) $params = array();

        $value = form::value($name, $params);

        return join("\n", array(
            '<li class="captcha">',
            form::label($name, $label),
            form::input('captcha', $value),
            Captcha::factory()->render(),
            '</li>'
        ));
    }

    /**
     * Return a value for a field, falling back through $form::data, $_POST, $_GET, and 
     * the supplied default.
     *
     * @param  string Field name
     * @param  string Field value
     * @return string
     */
    public static function value($name, $default=null)
    {
        if (!empty(self::$data[$name]))
            $value = self::$data[$name];
        else if (!empty($_POST[$name]))
            $value = $_POST[$name];
        else if (!empty($_GET[$name]))
            $value = $_GET[$name];
        else if (!empty($default))
            $value = $default;
        else
            $value = '';
        return $value;
    }

	/**
	 * Creates an HTML form input tag. Defaults to a text type.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   string        a string to be attached to the end of the attributes
	 * @param   boolean       encode existing entities
	 * @return  string
	 */
	public static function input($data, $value = '', $extra = '', $double_encode = TRUE )
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		// Type and value are required attributes
		$data += array
		(
			'type'  => 'text',
			'value' => $value
		);

		return '<input'.form::attributes($data).' '.$extra.' />';
	}

}
