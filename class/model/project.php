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
            $p->sharedUser[] = $author;
            $p->sharedAdminUser[] = $author;
            \R::store($p);
        }

        public static function findProjectById(int $id) : \RedbeanPHP\OODBBean {
            return \R::findOne('project','id = ' . $id);
        }


        public function isActive() : bool
        {
            return $this->bean->active;
        }

        public function setActive(bool $status) {
            $this->bean->active = $status;
        }

        private function getUsers() : array {
            return $this->bean->sharedUser;

        }

        public function loadUsers(Context $context)
        {
            return $context->local()->addval('users', $this->getUsers());
        }

        public function isAdmin(Context $context) : bool
        {

            return in_array($context->user()->getID(), $this->bean->sharedAdminUser);
        }

        public function setAdmin(Context $context, \RedbeanPHP\OODBBean $user) {
            //check $user is in project
            //if user calling this is admin of project, set this user to admin.
            $allUsers = $this->getUsers();
            $adminUsers = $this->bean->sharedAdminUser;
            
            if(in_array($context->user()->getID(),$adminUsers)) 
            {
                if(in_array($user->getID(),$allUsers)) {
                    array_push($adminUsers, $user);
                    $this->bean->sharedAdminUser = $adminUsers;
                    \R::store($this->bean);
                } 
                else 
                {
                    throw new \Framework\Exception\BadValue('This user is not part of this project.\n Please add the user to the project first.');
                }
            }
            else 
            {
                throw new \Framework\Exception\Forbidden('You are not an admin.');
            }
        }


        public function addUser(\RedbeanPHP\OODBBean $user) 
        {

            
            //check user calling this is admin of project.
            //check user is not currently on 


            //add user to project
            $userList = $this->bean->userList;
            array_push($userList,$user);
            \R::store($this->bean);
        }

        public function removeUser(\RedbeanPHP\OODBBean $user) {
            //check user calling this is admin of project
            //remove user from shared list
        }

    }
?>