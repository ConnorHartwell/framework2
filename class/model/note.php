<?php
/**
 * A model class for the RedBean object Note
 *
 * @author Connor Hartwell <c.hartwell@newcastle.ac.uk>
 * @copyright 2013-2020 Newcastle University
 * @package Framework
 * @subpackage SystemModel
 */
    namespace Model;

    use \Config\Framework as FW;
    use DateTimeImmutable;
    use \Support\Context;
    use \Model\Project as mProject;

    class Note extends \RedBeanPHP\SimpleModel
    {
/**
 * Create note for project projectId.
 * 
 * @param context - current context including post data
 * @param projectid - project to contain note.
 * 
 */
        public static function createNote(Context $context, int $projectid) : void {
            if(mProject::hasAccess(mProject::findProjectById($projectid),$context))
            {
                $fdt = $context->formData('post');
                $now = $context->utcnow();

                $n = \R::dispense('note');
                $n->title = strip_tags($fdt->mustFetch('title'));
                $n->text = strip_tags($fdt->mustFetch('text'));
                $n->author = $context->user();
                $n->project = mProject::findProjectById($projectid);
                $n->startTime = strip_tags($fdt->fetch('startTime', $now));
                $n->endTime = strip_tags($fdt->fetch('endTime', $now));
                $n->dateCreated = $now;
                $n->active = TRUE;

                $id = \R::store($n);

                $formd = $context->formData('file');
                $file = $formd->fileData('addfile');

                if($file !== null)
                {
                    Upload::uploadFile($context, $file, $n);
                }
            }

        }

/**
 * Retrieve note bean from it's id.
 * 
 * @param int id - note id
 * 
 * @return \RedbeanPHP\OODBBean - note
 * 
 */
        public static function getNoteById(int $id) : \RedBeanPHP\OODBBean 
        {
            return \R::findOne('note','id = ' . $id);
        }
        
/**
 * Delete a note
 * 
 * Requires note ownership.
 * 
 * @param Context - context
 * @param $note - note to delete.
 */
        public static function deleteNote(Context $context, \RedBeanPHP\OODBBean $note) 
        {
            if($note->isOwner($context))
            {
                $note->setActive(FALSE);
                \R::store($note);
            }
            else
            {
                $context->local()->message(\Framework\Local::ERROR, "You do not have access to delete this note.");
            }
        }

/**
 * Add all notes in this project to the current context.
 * Checks that user has access to project first.
 * 
 */
        public static function getAllNotes(Context $context, int $projectid) : void {
            if(mProject::hasAccess(mProject::findProjectById($projectid),$context))
            {
                $notes= \R::find('note','project_id = ' . $projectid . ' AND active = TRUE');
                $context->local()->addval('notes', $notes);
            }
            else
            {
                $context->local()->message(\Framework\Local::ERROR, "You do not have access to this project.");
            }
        }

        public function loadNote(Context $context) {
            $context->local()->addval('note',$this->bean);
            $context->local()->addval('owner',$this->isOwner($context));
        }



        public function isOwner(Context $context) :bool
        {
            return $this->bean->author_id === $context->user()->getID();
        }
        public function isActive() : bool
        {
            return $this->bean->active;
        }

        public function setActive(bool $status) : void
        {
            $this->bean->active = $status;
        }
    }
?>