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

    use \Support\Context as Context;
/**
 * Support /project/
 */
    class Project extends \Framework\Siteaction
    {
/**
 * Handle project operations
 *
 * @param Context   $context    The context object for the site
 *
 * @return string|array   A template name
 */
        public function handle(Context $context)
        {
            //check either post, get or not


            //if post, is it addproject or deleteproject
            if($context->web()->isPost()) {
                $this->addProject($context);
            }
            else {
                return '@content/createproject.twig';
            }
            
            //if get, single project or all projects?
        }

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