<?php
/**
 * BYOB editor module registry
 *
 * @package    BYOB
 * @subpackage Libraries
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Mozilla_BYOB_EditorRegistry {

    public static $editors = array();

    /**
     * Register an editor instance.
     */
    public static function register($editor)
    {
        self::$editors[$editor->id] = $editor;
    }

    /**
     * Inform all editors that locale is ready
     */
    public static function l10n_ready()
    {
        foreach (self::$editors as $editor) {
            $editor->l10n_ready();
        }
    }

    /**
     * Return an editor by ID.
     *
     * @param   string $id editor ID
     * @returns Mozilla_BYOB_Editor
     */
    public static function findById($id)
    {
        if (empty(self::$editors[$id])) return null;
        return self::$editors[$id];
    }

    /**
     * Get a list of all sections
     */
    public static function getSections($repack)
    {
        $sections = array(
            'general' => _('General')
        );

        foreach (self::$editors as $editor_id => $editor) {
            if (!$editor->isAllowed($repack)) continue;
            $sections[$editor->id] = $editor->title;
        }
        
        // TODO: Refactor away from this:
        foreach (Repack_Model::$edit_sections as $n=>$l) {
            if ('review' === $n) continue;
            if ('general' === $n) continue;
            $sections[$n] = $l;
        }

        $sections['review'] = false;

        return $sections;
    }

}
