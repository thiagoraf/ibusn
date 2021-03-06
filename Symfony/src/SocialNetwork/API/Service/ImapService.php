<?php

namespace SocialNetwork\API\Service;

class ImapService
{
    private $config = array();
    private $mbox;
    private $mboxFolder;
    private $mboxAdmin;
    private $mboxFolderAdmin;


    public function __construct(array $config , $container )
    {
        $this->config = $config;
        $this->config['options'] = ($config['TLSEncryption']) ? '/tls/novalidate-cert' : '/notls/novalidate-cert' ;

        if( $token = $container->get('security.context')->getToken() )
        {
            $passwd = $container->get('request')->getSession()->get('plainPassword');
            $this->config['username'] = $token->getUserName();
            $this->config['userpass'] = $passwd;
        }

        $this->config['adminName'] = $container->getParameter('admin-uid');
        $this->config['adminPass'] = $container->getParameter('admin-plainPassword');
    }

    public function openMailbox( $folder = 'INBOX'  )
    {
        $newFolder = mb_convert_encoding( str_replace( '.' ,  $this->config['delimiter'] , $folder ) , 'UTF7-IMAP' , 'UTF-8');

        if($newFolder ===  $this->mboxFolder AND is_resource( $this->mbox ))
            return $this->mbox;

        $this->mboxFolder =  $newFolder;
        $url = '{'.$this->config['host'].":".$this->config['port'].$this->config['options'].'}'.$this->mboxFolder;

        if (is_resource($this->mbox))
            imap_reopen($this->mbox, $url );
        else
            $this->mbox = imap_open( $url , $this->config['username'] , $this->config['userpass'] );

        return $this->mbox;
    }
    public function openAdminMailbox( $folder = 'INBOX'  )
    {
        $newFolder = mb_convert_encoding( str_replace( '.' ,  $this->config['delimiter'] , $folder ) , 'UTF7-IMAP' , 'UTF-8');

        if($newFolder ===  $this->mboxFolderAdmin AND is_resource( $this->mboxAdmin ))
            return $this->mboxAdmin;

        $this->mboxFolderAdmin =  $newFolder;
        $url = '{'.$this->config['host'].":".$this->config['port'].$this->config['options'].'}'.$this->mboxFolderAdmin;

        if (is_resource($this->mboxAdmin))
            imap_reopen($this->mboxAdmin, $url );
        else
            $this->mboxAdmin = imap_open( $url , $this->config['adminName'] , $this->config['adminPass'] );

        return $this->mboxAdmin;
    }

    public function getDefaultFolders()
    {
        return $this->config['folders'];
    }


    public function getMailboxAcl($mailbox)
    {
        $this->openAdminMailbox();

        return (@imap_getacl ($this->mboxAdmin, 'user'. $this->config['delimiter'] . $mailbox));
    }

    public function shareMailbox($mailbox , $uid, $acl = '')
    {
        $this->openAdminMailbox();

        $folder = $mailbox;

        $serverString = '{'.$this->config['host'].":".$this->config['port'].$this->config['options'].'}';
        $mailboxes_list = $this->getMailBoxes('user'. $this->config['delimiter']. $folder . $this->config['delimiter'].'*');

        if (is_array($mailboxes_list))
        {
            if ( imap_setacl ($this->mboxAdmin, 'user'. $this->config['delimiter'] . $folder , $uid , $acl ) )
            {
                foreach ($mailboxes_list as $val)
                {
                    $folder = str_replace($serverString, "", imap_utf7_encode($val->name));
                    $folder = str_replace("&-", "&", $folder);

                    if (!imap_setacl ($this->mboxAdmin, $folder, $uid, $acl)){
                        throw new \Exception(imap_last_error());
                        return false;
                    }
                }
            } else {

                throw new \Exception(imap_last_error());
                return false;
            }
        }
    }

    /**
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e SocialNetwork Software Livre (www.SocialNetwork.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     Cristiano Corrêa Schmidt
     * @param      string $string string no formato mime RFC2047
     * @return     string
     * @access     public
     */
    public function decodeMimeString( $string )
    {
        $string =  preg_replace('/\?\=(\s)*\=\?/', '?==?', $string);
        return mb_convert_encoding(preg_replace_callback( '/\=\?([^\?]*)\?([qb])\?([^\?]*)\?=/i' ,array( 'self' , 'decodeMimeStringCallback'), $string) , 'UTF-8' , 'UTF-8,ISO-8859-1');
    }

    /**
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e SocialNetwork Software Livre (www.SocialNetwork.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     Cristiano Corrêa Schmidt
     * @param $matches
     * @return string
     * @access     public
     */
    private function decodeMimeStringCallback( $matches )
    {
        $str = (strtolower($matches[2]) == 'q') ?  quoted_printable_decode(str_replace('_','=20',$matches[3])) : base64_decode( $matches[3]) ;
        return ( strtoupper($matches[1]) == 'ISO-8859-1' ) ? mb_convert_encoding(  $str , 'UTF-8' , 'ISO-8859-1') : $str;
    }

    public function getMailBoxes ( $pattern  = '*')
    {
        $this->openAdminMailbox();
        return imap_getmailboxes( $this->mboxAdmin , '{'.$this->config['host'].":".$this->config['port'].$this->config['options'].'}' , $pattern );
    }

    public function check( $uid = false )
    {

        if($uid AND ( $uid != $this->mboxFolder ))
            $this->openMailbox( 'user'. $this->config['delimiter']. $uid);

        if(!is_resource($this->mbox))
            $this->openMailbox();

        return imap_check( $this->mbox );
    }

    public function status ( $uid, $options = SA_ALL)
    {
        $this->openAdminMailbox( 'user'. $this->config['delimiter']. $uid );
        return imap_status( $this->mboxAdmin , '{'.$this->config['host'].":".$this->config['port'].$this->config['options'].'}user'. $this->config['delimiter']. $uid, $options );
    }

    public function sort ( $criteria = SORTDATE , $reverse = 0 , $options = SE_UID , $searchCriteria = NULL , $charset = 'UTF-8')
    {
        return imap_sort( $this->mbox , $criteria , $reverse , $options , $searchCriteria , $charset);
    }


    public function headerInfo ( $message , $SE_UID = true  )
    {
        return imap_headerinfo( $this->mbox , $SE_UID ? imap_msgno( $this->mbox , $message ) : $message );
    }

    function formatMailObject( $obj )
    {
        $return = array();
        $return['mail'] = isset($obj->mailbox) ?  $this->decodeMimeString( $obj->mailbox  ) : '' ;
        $return['mail'] .= (isset( $obj->host) && $obj->host != 'unspecified-domain' && $obj->host != '.SYNTAX-ERROR.'  ) ? '@'. $obj->host : '';
        $return['name'] = ( isset( $obj->personal ) && trim($obj->personal) !== '' ) ? $this->decodeMimeString($obj->personal) : '' ;
        return $return;
    }

    function formatMailObjects( Array $objs)
    {
        $return = array();

        foreach($objs as $obj)
            $return[] = $this->formatMailObject($obj);

        return $return;
    }

    function body( $message , $options = false )
    {
        $options = ($options == false)  ? FT_UID|FT_PEEK : $options;
        return imap_body( $this->mbox, $message  , $options );
    }

    function header( $message , $options = FT_UID )
    {
        return imap_fetchheader( $this->mbox, $message  , $options  );
    }

    public function createFolder($folder){
        $folder = mb_convert_encoding( str_replace( '.' ,  $this->config['delimiter'] , $folder ) , 'UTF7-IMAP' , 'UTF-8');
        return imap_createmailbox($this->mbox, '{'.$this->config['host'].":".$this->config['port'].$this->config['options'].'}'.$folder);
    }

    public function editFolder($folder, $newFolder){
        $folder = mb_convert_encoding( str_replace( '.' ,  $this->config['delimiter'] , $folder ) , 'UTF7-IMAP' , 'UTF-8');
        $newFolder = mb_convert_encoding( str_replace( '.' ,  $this->config['delimiter'] , $newFolder ) , 'UTF7-IMAP' , 'UTF-8');
        $mailboxPath =  '{'.$this->config['host'].":".$this->config['port'].$this->config['options'].'}';
        return imap_renamemailbox($this->mbox, $mailboxPath.$folder, $mailboxPath.$newFolder);
    }

    public function deleteFolder($folder){
        $folder = mb_convert_encoding( str_replace( '.' ,  $this->config['delimiter'] , $folder ) , 'UTF7-IMAP' , 'UTF-8');
        return imap_deletemailbox($this->mbox, '{'.$this->config['host'].":".$this->config['port'].$this->config['options'].'}'.$folder);
    }

    public function moveMessage($UID, $folder){
        $folder = mb_convert_encoding( str_replace( '.' ,  $this->config['delimiter'] , $folder ) , 'UTF7-IMAP' , 'UTF-8');
        return imap_mail_move($this->mbox, $UID, $folder, CP_UID) ? imap_expunge($this->mbox) : false;
    }

    public function deleteMessage($UID){
        return imap_delete($this->mbox, $UID, FT_UID) ? imap_expunge($this->mbox) : false;
    }

    public function getQuotaRoot( $folder = 'INBOX' )
    {
        if(!is_resource($this->mboxAdmin))
            $this->openAdminMailbox();

       return @imap_get_quotaroot( $this->mboxAdmin , str_replace( '.' ,  $this->config['delimiter'] , $folder ));
    }

    public function renameMailBox($oldUid , $newUid)
    {
        $this->openAdminMailbox();
        $mailboxPath =  '{'.$this->config['host'].":".$this->config['port'].$this->config['options'].'}';
        return imap_renamemailbox($this->mboxAdmin, $mailboxPath. "user" . $this->config['delimiter'] . $oldUid , $mailboxPath. "user" . $this->config['delimiter'] . $newUid);
    }



    public function setQuota($folder = 'INBOX', $quota = false )
    {
        $this->openAdminMailbox();



        return imap_set_quota( $this->mboxAdmin , str_replace( '.' ,  $this->config['delimiter'] , $folder ), ($quota > 0 ? $quota*1024 : $quota));
    }

    public function createAccount($uid , $quota = false )
    {
        $this->openAdminMailbox();

        if (!imap_createmailbox($this->mboxAdmin, '{'.$this->config['host'].":".$this->config['port'].$this->config['options'].'}' . "user" . $this->config['delimiter'] . $uid))
        {
            $error = imap_last_error();
            if (!$error == 'Mailbox already exists')
            {
                throw new \Exception($error);
                return false;
            }
        }

        foreach($this->config['folders'] as $folder)
            imap_createmailbox($this->mboxAdmin, '{'.$this->config['host'].":".$this->config['port'].$this->config['options'].'}' . "user" . $this->config['delimiter'] . $uid . $this->config['delimiter'] . $folder);

        if($quota)
            imap_set_quota($this->mboxAdmin,"user" . $this->config['delimiter'] . $uid, ($quota > 0 ? $quota*1024 : $quota));

        return true;
    }
}