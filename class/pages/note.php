<?php
/**
 * A class that contains code to handle any requests for  /notes/
 *
 * @author Your Name <Your@email.org>
 * @copyright year You
 * @package Framework
 * @subpackage UserPages
 */
    namespace Pages;

    use \Support\Context as Context;
    use \RedbeanPHP as R;
    use \Model\Project as mProject;
    use \Model\Note as mNote;
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

            
            if($rest[0] === "create" && $context->web()->isPost() == FALSE) {
                //TODO: secure this - ensure the user is logged on.
                //should always have a rest[1].
                return "@content/createnote.twig";
            }
            else if($rest[0] === "create" && $context->web()->isPost() == TRUE) {
                
                //TODO: require rest[1]. throw an error otherwise. 
                $projectid = $rest[1];
                
                $this->addNote($context, $projectid);
                return $context->divert("/project/" . $projectid);
            }


            else if(is_numeric($rest[0])) 
            {
                $context->local()->addval('note',$this->getNote($rest[0]));
                return '@content/note.twig';
            }
        }
        



        //TODO: implement start and end time
        private function addNote(Context $context, int $projectid) {
            if($context->web()->isPost()) {
                $fdt = $context->formData('post');
                $now = $context->utcnow();
                mNote::createNote(  $fdt->mustFetch('title'),
                                    $fdt->mustFetch('text'),
                                    $context->user(),
                                    $projectid, 
                                    $fdt->fetch('startTime', $now),
                                    $fdt->fetch('endTime', $now),
                                    $now
                                );
            }
        }

        private function allNotes(Context $context) {
            $notes = \R::find('note','project = ' . $context->rest()[1]);
            $context->local()->addval('allnotes',$notes);
        }


/**
 * When a user clicks on a note, we need to retrieve any attachments linked to
 * related note.
 */
        public function getNote(int $noteid) : \RedbeanPHP\OODBBean {
            return mNote::getNoteById($noteid);
        }
    }
?>