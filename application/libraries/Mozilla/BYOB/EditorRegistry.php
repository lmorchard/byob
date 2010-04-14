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
    public static function getSections()
    {
        $sections = array();

        // TODO: Refactor away from this:
        foreach (Repack_Model::$edit_sections as $n=>$l) {
            if ('review' === $n) continue;
            $sections[$n] = $l;
        }

        foreach (self::$editors as $editor_id => $editor) {
            $sections[$editor->id] = $editor->title;
        }

        $sections['review'] = false;

        return $sections;
    }

}
