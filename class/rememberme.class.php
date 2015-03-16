<?php

class TRememberMe extends TObjetStd {
    function __construct() { /* declaration */
        global $langs,$db;
        parent::set_table(MAIN_DB_PREFIX.'rememberme');
        parent::add_champs('fk_societe,fk_user','type=entier;index;');
        parent::add_champs('trigger_code','type=chaine;index;');
        
        parent::add_champs('message','type=text;');
        
        parent::_init_vars();
        parent::start();
    }
    
    static function message($action, &$object) {
        global $user;
        
        $PDOdb = new TPDOdb;
        
        $Tab = $PDOdb->ExecuteAsArray("SELECT fk_societe,fk_user,message,type_msg FROM ".MAIN_DB_PREFIX."rememberme WHERE trigger_code='".$action."'");
        foreach($Tab as $row) {
            //var_dump($row);
            if($row->fk_societe>0 && ($object->fk_soc!=$row->fk_societe && $object->socid!=$row->fk_societe ) ) continue; // pas pour lui ce message
            if($row->fk_user>0 && $row->fk_user!=$user->id)continue; // non plus
            
            if(empty($row->type_msg))$row->type_msg='warnings';
            
            setEventMessage($row->message, $row->type_msg);
            
        }
        
        
        $PDOdb->close();        
      
    }
    
}
