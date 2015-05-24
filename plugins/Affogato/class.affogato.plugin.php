<?php if (!defined('APPLICATION')) exit();

// Define the plugin:
$PluginInfo['Affogato'] = array(
   'Name' => 'Affogato',
   'Description' => 'Allows Processing.js sketches to be posted and run',
   'Version' => '0.1',
   'RequiredApplications' => array('Vanilla' => '2.0.18'),
   'MobileFriendly' => TRUE,
   'Author' => 'Antony Bartlett',
   'AuthorEmail' => 'akb@akb.me.uk',
   'AuthorUrl' => 'https://github.com/Antony74/affogato',
   'SettingsPermission' => 'Garden.Settings.Manage',
);

class AffogatoPlugin extends Gdn_Plugin
{
    public function PrependSomething($body)
    {
        return 'Annoying message prepended during pre-processing<BR>' . $body;
    }

    public function AppendSomething($body)
    {
        return $body . '<BR>Annoying message appended during post-processing';
    }
   
    public function LogSender($sender)
    {
        file_put_contents('c:/temp/dump.txt', print_r($sender, true));
    }

    public function DiscussionController_BeforeCommentBody_Handler($sender)
    {
//        $sender->InformMessage("DiscussionController_BeforeCommentBody");
        $sender->EventArguments['Object']->Body = $this->PrependSomething($sender->EventArguments['Object']->Body);
    }

    public function DiscussionController_AfterCommentFormat_handler($sender)
    {
//        $sender->InformMessage("DiscussionController_afterCommentFormat_handler");
        $sender->EventArguments['Object']->FormatBody = $this->AppendSomething($sender->EventArguments['Object']->FormatBody);
    }

    public function PostController_BeforeCommentPreviewFormat_handler($sender)
    {
//        $sender->InformMessage("PostController_BeforeCommentRender_handler");
        $sender->Comment->Body = $this->PrependSomething($sender->Comment->Body);
    }

    public function PostController_AfterCommentPreviewFormat_handler($sender)
    {
//        $sender->InformMessage("PostController_AfterCommentPreviewFormat");
        $sender->Comment->Body = $this->AppendSomething($sender->Comment->Body);
    }
}

?>

