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

        public static function createNote(string $title, String $text, \RedbeanPHP\OODBBean $author, int $projectid, string $startTime, string $endTime, string $creationDate) {
            $n = \R::dispense('note');
            $n->title = $title;
            $n->text = $text;
            $n->author = $author;
            $n->project = mProject::findProjectById($projectid);
            $n->startTime = $startTime;
            $n->endTime = $endTime;
            $n->dateCreated = $creationDate;
            $n->active = TRUE;

            \R::store($n);
        }


        public static function getAllNotes(int $projectid) : array {
            return \R::find('note','project_id = ' . $projectid);
        }

        public static function getNoteById($id) : \RedbeanPHP\OODBBean{
            return \R::findOne('note','id = ' . $id);
        }
        

        public function isActive() : bool
        {
            return $this->bean->active;
        }

    }
?>