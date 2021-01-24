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

use Model\Project as mProject;
use Model\Note as mNote;
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

            $rest = $context->rest();
/** 
 *  /project operation
 */
            if($context->action() == 'project') {
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

                    
                    $context->local()->addval('project',$project);
                    $context->local()->addVal('notes',$this->getNotesInProject($rest[0]));
                    return "@content/notelist.twig";
    
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











        public function addProject(Context $context) : void
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
 *  //TODO: ensure user is logged in.
 */
    public function getAllProjects(Context $context) : void 
    {
        $projects = \R::find('project','active = TRUE AND author_id = ' . $context->user()->id);
        $context->local()->addval('projects',$projects);
    }



/**
 * Get information about a single project by id
 * 
 * Controller function checks for security before sending to the model.
 */
    public function getProjectById(int $projectId) :  \RedbeanPHP\OODBBean
    {
        //secure this with user.
        return mProject::findProjectById($projectId);
    }

    //TODO: secure this with user.
    public function getNotesInProject(int $project) : array {
        return mNote::getAllNotes($project);
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
    public function deleteProject(Context $context) : void {}

}




   


?>