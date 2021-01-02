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
    use \Support\Context;

    class Project extends \RedBeanPHP\SimpleModel
    {

        public static function createProject(String $name, \RedbeanPHP\OODBBean $author, string $creationDate) {
            $p = \R::dispense('project');
            $p->name = $name;
            $p->author = $author;
            $p->createdOn = $creationDate;
            $p->active = TRUE;

            \R::store($p);
        }


        public function isActive() : bool
        {
            return $this->bean->active;
        }

    }
?>