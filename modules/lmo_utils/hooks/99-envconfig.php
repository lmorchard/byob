<?php
/**
 * Hook to override application configs via a selectable environmental config 
 * override.
 *
 * Other hooks can respond to the EnvConfig.select_environment event to supply 
 * a method for selecting the environment.
 *
 * In order to rely on the selected configs, other hooks must register handlers 
 * with Event::add(LMO_Utils_EnvConfig::EVENT_READY, $callback)
 *
 * @TODO Load config overrides per hostname / environment
 *
 * @package    LMO_Utils
 * @subpackage hooks
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class LMO_Utils_EnvConfig {

    /** 
     * Event run to allow other modules to select config environment according 
     * to extended logic. 
     */
    const EVENT_SELECT_ENVIRONMENT = 
        'LMO_Utils_EnvConfig.select_environment';
    /** 
     * Event run when the selected configuration overrides have been applied.
     */
    const EVENT_READY = 
        'LMO_Utils_EnvConfig.ready';

    /**
     * Initialize to apply configs after system.ready
     */
    public static function init()
    {
        Event::add('system.ready', array(get_class(), '_handle_ready'));
    }

    /**
     * Apply config overrides after system.ready
     */
    public static function _handle_ready()
    {
        $env = 'local';
        Event::run(self::EVENT_SELECT_ENVIRONMENT, $env);
        if (!empty($env)) {
            self::apply($env);
        }
        Event::run(self::EVENT_READY, $env);
    }

    /**
     * Apply config overrides for given environment.
     */
    public static function apply($env='local')
    {
        $config = array();
        $fn = Kohana::find_file('config', 'config-' . $env);
        if (!empty($fn)) {
            foreach ($fn as $f) {
                include($f);
                if (isset($config) && !empty($config)) {
                    foreach ($config as $key => $value) {
                        Kohana::config_set($key, $value);
                    }
                }
            }
        }
    }

}
LMO_Utils_EnvConfig::init();
