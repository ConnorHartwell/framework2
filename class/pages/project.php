<?php
/**
 * A class that contains code to handle any requests for  /project/
 *
 * @author Connor Hartwell c.hartwell@newcastle.ac.uk
 * @copyright 2020 Connor Hartwell
 * @package Framework
 * @subpackage UserPages
 */
    namespace Pages;

use Framework\Local;
use Model\Project as mProject;
use Model\Note as mNote;
use ModelExtend\User;
use \Support\Context as Context;
/**
 * Support /project/
 */
    class Project extends \Framework\Siteaction
    {
/**
 * Handle project operations - 
 * 
 * /project/create
 * /project/all
 * /project/<int> - get information about a project - return template
 * /project/<int>/settings - open settings page for a project
 * /project/<int>/delete - delete project <int>
 * /project/<int>/user/<int>/add - add user to project
 * /project/<int>/user/<int>/remove - remove user from project
 * 
 * @param Context   $context    The context object for the site
 *
 * @return string|array   A template name
 */
        public function handle(Context $context)
        {
            //check either post, get or not

            $rest = $context->rest();
/** 
 *  /project operation
 */
            if($context->action() == 'project') 
            {
                // check it is /all or /value

                //   /project/all
                if($rest[0] === "all") 
                {
                    $this->getAllProjects($context);
                    return '@content/project.twig';
                } 


                //   /project/1/
                else if(is_numeric($rest[0])) 
                {
                    $project = $this->getProjectById($rest[0]);
                    if($project !== null)
                    {
                        if(mProject::hasAccess($project,$context))
                        {
           
                            $context->local()->addval('project',$project);

                            if(count($rest) >= 2) 
                            {
                                switch($rest[1]) 
                                {
                                    case "settings":
                                        $this->loadUsers($context,$project);
                                        return "@content/settings.twig";
                                        break;
                                    case "user":
                                        // /project/1/user/add
                                        if($context->web()->isPost())
                                        {
                                            if($rest[2] === "add")
                                            {
                                                
                                                //get post.
                                                //add user to project
                                                $this->addUserToProject($context, $project);
                                                //redirect to settings page again.
                                                $context->divert("/project/".$rest[0]."/settings", FALSE, 'User Added', FALSE, TRUE);
                                            }
                                            else if($rest[2] === "remove")
                                            {
                                                $this->removeUserFromProject($context, $project);
                                                //redirect to settings page again.
                                                $context->divert("/project/".$rest[0]."/settings", FALSE, 'User Deleted', FALSE, TRUE);
                                                
                                            }
                                        }
                                        break;
                                    case "delete":
                                        
                                        
                                        if($context->web()->isPost()) {
                                            $this->deleteProject($context,$project);
                                            $context->divert("/project/all", FALSE,'Project deleted.', FALSE, TRUE);
                                        }
                                        break;
                                    
                                }
                            } 
                            else 
                            {
                                
                                $this->getNotesInProject($context,$project);
                                return "@content/notelist.twig";
                            }
                        }
                        else
                        {
                            throw new \Framework\Exception\Forbidden('You do not have access to this project.');
                            $context->divert("/project/all",FALSE, 'User is forbidden',FALSE);
                        }
                    }
                    else
                    {
                        $context->divert("/project/all");
                    }


                
    
                }

                //GET /project/create - open page to create new project
                //POST /project/create - create new project in database.
                else if($rest[0] === "create") {

                    if($context->web()->isPost()) {
                        $this->addProject($context);
                        return $context->divert("/project/all");
                    } else {
                        return '@content/createproject.twig';
                    }
                }

                return '@content\contact.php';
            }
        }





/**
 * Adds user to project
 * Requires formData ('email') - STRING email as user
 * 
 * @param $context - context
 * @param $project - project to add user in form to.
 * 
 */
        private function addUserToProject(Context $context, \RedbeanPHP\OODBBean $project)
        {

            $email = strip_tags($context->formData('post')->mustFetch('email'));
            if(filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                $user = User::findFromEmail($email);
                if($user !== NULL) {
                    $project->addUser($context, $user);
                }
                else {
                    throw new \Framework\Exception\BadValue('Invalid Email.');
                }
            }
            else
            {
                $context->local()->message(Local::ERROR,'Invalid Email.');
            }

            
        }

/**
 * Retrieve array of users to remove from project (checkboxes on frontend)
 * 
 * 
*/
        private function removeUserFromProject(Context $context, \RedbeanPHP\OODBBean $project) 
        {
            $users = $context->formData('post')->fetchArray('users'); 
            foreach($users as $id) 
            {
                $user = User::findFromId($id); 
                $project->removeUser($user); 
            }
        }

        private function addProject(Context $context) : void
        {

            $now = $context->utcnow(); // make sure time is in UTC
            $fdt = $context->formdata('post');
            $projName = $fdt->mustFetch('name');
            
            //ensure project != null
            if($projName !== '') {
                //create project bean
                mProject::createProject($projName, $context->user(), $now);
            }
        }
    

/**
 *  Get all projects associated with an email.
 * 
 *  @param Context - context
 */
        public function getAllProjects(Context $context) : void 
        {
            if($context->hasUser())
            {
                mProject::loadProjects($context);
            }
            
        }



/**
 * Get information about a single project by id
 * 
 * Controller function checks for security before sending to the model.
 * 
 * @param int projectId - id
 * 
 * @return \RedbeanPHP\OODBBean - project with id= $projectId
 */
    public function getProjectById(int $projectId) :  \RedbeanPHP\OODBBean
    {
        //secure this with user.
        return mProject::findProjectById($projectId);
    }


/**
 * 
 */
    public function getNotesInProject(Context $context, \RedbeanPHP\OODBBean $project) : void 
    {
        if(mProject::hasAccess($project,$context))
        {
            mNote::getAllNotes($context, $project->id);
        }
        else
        {
            throw new \Framework\Exception\Forbidden('You do not have access to this project.');
        }
    }

/**
 * Wrapper function to load all users in a project to context.
 * Includes error check before sending to model
 * 
 * @param Context - current context
 * @param project - project to find users of.
 */
    private function loadUsers(Context $context, \RedBeanPHP\OODBBean $project) 
    {
        try
        {
            $project->loadUsers($context);
        }
        catch (\Framework\Exception\Forbidden $e)
        {
            $context->local()->message(\Framework\Local::ERROR, $e->getMessage());
        }

    }
/**
 * Delete project, handling post form.
 * ---------------------
 * ADMIN ONLY
 * 
 * @param Context
 * 
 * @return bool - TRUE on success.
 */
    private function deleteProject(Context $context, \RedBeanPHP\OODBBean $project) : void 
    {
        mProject::deleteProject($context,$project);
    }

}


?>