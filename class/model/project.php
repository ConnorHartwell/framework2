<?php
/**
 * A model class for the RedBean object Project
 *
 * @author Connor Hartwell <c.hartwell@newcastle.ac.uk>
 * @copyright 2013-2020 Newcastle University
 * @package Framework
 * @subpackage SystemModel
 */
    namespace Model;

    use \Config\Framework as FW;
    use \Support\Context;


    class Project extends \RedBeanPHP\SimpleModel
    {

/**
 * Add a project.
 * 
 */

        public static function createProject(String $name, \RedbeanPHP\OODBBean $author, string $creationDate) {

            $p = \R::dispense('project');
            $p->name = strip_tags($name);
            $p->author = $author;
            $p->createdOn = strip_tags($creationDate);
            $p->active = TRUE;
            $p->sharedUser[] = $author;
            \R::store($p);
        }

        public static function findProjectById(int $id) : \RedbeanPHP\OODBBean {
            $p = \R::findOne('project','id = ' . $id);

            if($p !== null) 
            {
                return $p;
            }
            else 
            {
                throw new \Framework\Exception\MissingBean('Project not found');
            }
        }

/**
 * Check to ensure that currently logged in user in context has access to view a project
 * Access = 'on the project' i.e. in the members list
 *          OR user is server admin.
 * 
 * @param $project - project bean to check
 * @param $context - context
 * 
 * @return bool TRUE if user exists in project, or has admin access.
 */
        public static function hasAccess(\RedbeanPHP\OODBBean $project, Context $context) : bool
        {
            $result = \R::count('project_user','WHERE user_id = ' . $context->user()->id . " AND project_id = " . $project->id);
            return $result == 1 || $context->hasAdmin();
        }

/**
 * If no access to context, we can check if a user is part of a project.
 * 
 * @param project - project for access
 * @param user - user to check
 * 
 * @return bool TRUE if user exists in project.
 */
        public static function userAccess(\RedbeanPHP\OODBBean $project, \RedbeanPHP\OODBBean $user) : bool
        {
            $result = \R::count('project_user','WHERE user_id = ' . $user->getID() . " AND project_id = " . $project->id);
            return $result == 1;
        }


        /**
 * Static function should not be running on instance to be deleted
 * 
 * Checks for user = author.
 * 
 * @param $context - current context
 * @param $project - project to delete
 * 
 * @throws Forbidden - user does not have access to delete project.
 */
        public static function deleteProject(Context $context, \RedbeanPHP\OODBBean $project) {
            if($project->isOwner($context)) {
                $project->setActive(FALSE);
                \R::store($project);
            }
            else
            {
                $context->local()->message(\Framework\Local::ERROR, "You do not have access to delete this project.");
            }


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

/**
 * Load all users for $this project into context
 * 
 * 'users' - all users for project
 * 'admin' boolean if this user is an admin. Used to allow access for modification of users.
 */
        public function loadUsers(Context $context) : void
        {
            $context->local()->addval('users', $this->getUsers());
            $context->local()->addval('admin',$this->isOwner($context));
        }

        public function isOwner(Context $context) : bool
        {
            return $context->user()->getID() === $this->bean->author_id;
        }

/**
 *  Get all projects associated with an email.
 * 
 * @param $context - Context
 * 
 * @throws BadOperation - If user is not currently logged in, we cannot load projects.
 */
        public static function loadProjects(Context $context) : void 
        {
            if($context->hasUser())
            {
                //get all project that have current user in them.
                $userProjects = \R::getAll('SELECT p.* FROM project p
                                        INNER JOIN project_user pu ON p.id = project_id
                                        WHERE pu.user_id = :id AND p.active = TRUE', array(':id' => $context->user()->getID()));

                $context->local()->addval('projects',\R::convertToBeans('project',$userProjects));
            } 
            else
            {
                throw new \Framework\Exception\BadOperation('User must be logged in');
            }
        }

/**
 * Add a user to this project
 * 
 * //TODO: add context to check if user calling this is the project owner.
 */
        public function addUser(Context $context, \RedbeanPHP\OODBBean $user) 
        {
            //check user calling this is admin of project.
            //check user is not currently on 

            $project = $this->bean;
            if(Project::hasAccess($project, $context))
            {

                //add user to project
                $userList = $this->getUsers();

                array_push($userList, $user);
                $project->sharedUser = $userList;
                \R::store($project);
            }
            else
            {
                throw new \Framework\Exception\Forbidden('You do not have access to add users.');
            }
        }


/**
 * Remove a user from this project.
 * 
 * @param $context - delete context for security
 * @param $user - user to remove from this project.
 */
        public function removeUser(Context $context, \RedbeanPHP\OODBBean $user) {
            //check user calling this is admin of project
            //remove user from shared list
            $project_id = $this->bean->id;
            $user_id = $user->getID();
            if(Project::hasAccess($user, $context))
            {
                $bean = \R::findOne('project_user',"project_id = " . $project_id . " AND user_id = " . $user_id);
                \R::trash($bean);
            }
        }

    }
?>