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

use Model\Project as modelProject;
use \Support\Context as Context;
/**
 * Support /project/
 */
    class Project extends \Framework\Siteaction
    {
/**
 * Handle project operations - /project /create
 *
 * @param Context   $context    The context object for the site
 *
 * @return string|array   A template name
 */
        public function handle(Context $context)
        {
            //check either post, get or not


/**
 *  /project operation
 */
            if($context->action() == 'project') {
                // 
                $this->getAllProjects($context);
                return '@content/project.twig';
            }

/**
 *  GET /createproject returns web page
 *  POST /createproject create new project given name.
 */
            else if($context->action() == 'createproject') {
                $rest = $context->rest();
                if($context->web()->isPost()) {
                    $this->addProject($context);
                    return $context->divert("/project");
                } else {
                    return '@content/createproject.twig';
                }

            }


            
            
            //if get, single project or all projects?
        }

        public function addProject(Context $context) : void
        {

            $now = $context->utcnow(); // make sure time is in UTC
            $fdt = $context->formdata('post');
            $projName = $fdt->mustFetch('name');
            
            //ensure project != null
            if($projName !== '') {

                //create project bean
                modelProject::createProject($projName, $context->user(), $now);

            }
        }
    

/**
 *  Get all projects associated with an email.
 * 
 */
    public function getAllProjects(Context $context) : void 
    {
        $projects = \R::find('project','author_id = ' . $context->user()->id);
        $context->local()->addval('projects',$projects);
    }



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