<?php
/**
 * A model class for the RedBean object User
 *
 * This is a Framework system class - do not edit!
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
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
/*
    Create note from context

   //TODO: check for user in project.
   //update start and end times.
*/
        public static function createNote(Context $context, int $projectid) : int {
            $fdt = $context->formData('post');
            $now = $context->utcnow();

            $n = \R::dispense('note');
            $n->title = $fdt->mustFetch('title');
            $n->text = $fdt->mustFetch('text');
            $n->author = $context->user;
            $n->project = mProject::findProjectById($projectid);
            $n->startTime = $fdt->fetch('startTime', $now);
            $n->endTime = $fdt->fetch('endTime', $now);
            $n->dateCreated = $now;
            $n->active = TRUE;

            $id = \R::store($n);



            return $id;
        }

        public static function getNoteById(int $id) : \RedBeanPHP\OODBBean {
            return \R::findOne('note','id = ' . $id);
        }
        

        public static function getAllNotes(Context $context, int $projectid) : void {
            $notes= \R::find('note','project_id = ' . $projectid);
            $context->local()->addval('notes', $notes);
        }

        public function loadNote(Context $context) {
            $context->local()->addval('note',$this->bean);
        }




        public function isActive() : bool
        {
            return $this->bean->active;
        }
    }
?>