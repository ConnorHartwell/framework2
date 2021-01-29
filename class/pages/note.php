<?php
/**
 * A class that contains code to handle any requests for  /notes/
 *
 * @author Connor Hartwell <c.hartwell@newcastle.ac.uk>
 * @copyright 2020 Connor Hartwell
 * @package Framework
 * @subpackage UserPages
 */
    namespace Pages;

    use \Support\Context as Context;
    use \RedbeanPHP as R;
    use \Model\Project as mProject;
    use \Model\Note as mNote;
    use ModelExtend\Upload;

/**
 * Support /notes/
 */
    class Note extends \Framework\Siteaction
    {
/**
 * Handle notes operations
 * Potential URLs 
 * /note/create/<projectid>
 * /note/<noteid>
 * 
 *                          action  rest[0]     rest[1]     ENDPOINT 
 * GET localhost/logger/    note/   create/     <integer>   create project web page.
 * POST localhost/logger/   note/   create/     <integer>   adding to the database
 * 
 * GET localhost/logger/    note/   <integer>               get note web page. should send 
 * 
 * @param Context   $context    The context object for the site
 *
 * @return string|array   A template name
 */
        public function handle(Context $context)
        {
            $rest = $context->rest();

            //if a note is specified.

            if($context->web()->isPost() == TRUE)
            {
                // /note/create/<projectid>
                if($rest[0] === "create")
                {
                    $projectid = $rest[1];
                
                    $this->addNote($context, $projectid);
                    return $context->divert("/project/" . $projectid,FALSE,'Note created.', FALSE, TRUE);
                }
                // /note/<id>/delete
                else if(is_numeric($rest[0]))
                {
                    if(count($rest) == 2 && $rest[1] === "delete")
                    {
                        //lazy writing but works.
                        $note = mNote::getNoteById($rest[0]);
                        
                        mNote::deleteNote($context,mNote::getNoteById($rest[0]));
                        return $context->divert("/project/" . $note->project_id);
                    }
                    

                }
            }
            else
            {
                if($rest[0] === "create")
                {
                                    //TODO: secure this - ensure the user is logged on.
                //should always have a rest[1].
                mProject::hasAccess(mProject::findProjectById($rest[1]),$context);
                return "@content/createnote.twig";
                }
                else if(is_numeric($rest[0])) 
                {
                    //$context->local()->addval('note',$this->getNote($context));
                    $this->getNote($context,$rest[0]);
                    return '@content/note.twig';
                }
            }
        }
        



        //TODO: implement start and end time
        private function addNote(Context $context, int $projectid) {
            if($context->web()->isPost()) {

                $noteid = mNote::createNote(  $context, $projectid );
            }
        }

        private function allNotes(Context $context) {
            $notes = \R::find('note','project = ' . $context->rest()[1]);
            $context->local()->addval('allnotes',$notes);
        }


/**
 * When a user clicks on a note, we need to retrieve any attachments linked to
 * related note.
 * 
 * @param $context - current page context
 * @param $noteid - id of note to view.
 */
        public function getNote(Context $context, int $noteid) : void {
            $note = mNote::getNoteById($noteid);
            $note->loadNote($context);
            Upload::getUploads($context,$note);
        }
    }
?>