<?php
namespace Pages;

/**
* A model class for the RedBean object 'Project'
*
 * @author Connor Hartwell c.hartwell@newcastle.ac.uk
 * @copyright 2020 Connor Hartwell
 * @package Framework
 * @subpackage SystemModel
*
**/

    use \Support\Context as Context;


    class createProject extends \Framework\SiteAction {



        public function handle(Context $context)
        {
            $action = $context->action(); // the validity of the action value has been checked before we get here
            //assert(method_exists($this, $action));
            if($context->web()->isPost()) {
                return $this->$action($context);
            }
            else {
                return '@content/createProject.twig';
            }

        }

/**
 *  Add a project using formdata. 
 * 
 */
    public function addProject(Context $context) : string
    {
        $now = $context->utcnow(); // make sure time is in UTC
        $fdt = $context->formdata('post');
        $projName = $fdt->mustFetch('name');
        
        //ensure project != null
        if($projName !== '') {

            //create project bean
            $p = \R::dispense('Project');

            $p->name = $fdt->mustFetch('name');
            $p->author = $context->user;
            $p->createdOn = $now;
            $p->active = TRUE;

            \R::store($p);

            return '@content/contact.twig';
        }
    }

/**
 *  Get all projects associated with an email.
 * 
 */
    public function getAllProjects(int $userId) : void {}



/**
 * Get information about a single project by id
 * 
 */
    public function getProjectById(int $projectId) : void {}


/**
 * Delete project, handling post form.
 * ---------------------
 * ADMIN ONLY
 * 
 * @param Context
 * 
 * @return bool - TRUE on success.
 */
    public function deleteProject(Context $context) : void {}

}
?>