<?php

class TRememberMe extends TObjetStd {
    function __construct() { /* declaration */
        global $langs,$db;
        parent::set_table(MAIN_DB_PREFIX.'rememberme');
        parent::add_champs('fk_societe,fk_user',array('index'=>true, 'type'=>'int'));
        parent::add_champs('nb_day_after',array('type'=>'int'));
        
        parent::add_champs('trigger_code,type,type_event', array('index'=>true, 'type'=>'string', 'length'=>50));
        
        parent::add_champs('message,message_condition,message_code', array('type'=>'text'));
        
        parent::_init_vars('type_msg');
        parent::start();
        
        $this->type='MSG';
        $this->TType=array(
            'MSG'=>'Message écran'
            ,'EVENT'=>'Evènement agenda'
            ,'EVAL'=>'Evaluation du code php (attention !)'
        );
        
        
        $this->type_msg = 'mesgs';
        $this->TTypeMessage=array(
            'mesgs'=>'Information'
            ,'warnings'=>'Alerte'
            ,'errors'=>'Erreur'
        );
        
    }
    
    static function getAll(&$PDOdb, $type='') {
        
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."rememberme WHERE 1 ";
        
        if(!empty($type)) $sql.=" AND  type = '".$type."' "; 
        
        $sql.=" ORDER BY date_cre ";
        
        $Tab = $PDOdb->ExecuteAsArray($sql);
        
        $TRes = array();
        foreach($Tab as $row) {
            
            $r=new TRememberMe;
            $r->load($PDOdb, $row->rowid);
            
            $TRes[] = $r;
        }
        
        return $TRes ;
    }
    
    static function message($action, &$object, $type='') {
        global $user, $db;
        
        $PDOdb = new TPDOdb;
        
        $sql = "SELECT fk_societe,fk_user,message,type_msg,type,message_condition,message_code,nb_day_after
                FROM ".MAIN_DB_PREFIX."rememberme 
                WHERE trigger_code='".$action."'";
        if(!empty($type)) $sql.=" AND  type = '".$type."' "; 
        
        $Tab = $PDOdb->ExecuteAsArray($sql);
                
        foreach($Tab as $row) {
            //var_dump($row);
            if($row->fk_societe>0 && ($object->fk_soc!=$row->fk_societe && $object->socid!=$row->fk_societe ) ) continue; // pas pour lui ce message
            if($row->fk_user>0 && $row->fk_user!=$user->id)continue; // non plus
            
            if(empty($row->type_msg))$row->type_msg='warnings';
            
            if(!empty($row->message_condition)) {
                if(!eval('return ('.$row->message_condition.')')) continue; //ne répond pas au test 
            }
            
            if($row->type == 'MSG') setEventMessage($row->message, $row->type_msg);
            else if($row->type == 'EVENT') {
                    
                 $actioncomm=new ActionComm($db);    
                 $actioncomm->datep = strtotime('+'.$row->nb_day_after.'day');
                 //$a->datef = $t_end;
                 
                 $actioncomm->userownerid = $user->id;
                 $actioncomm->type_code='AC_OTH';
                 $actioncomm->label = 'RememberMe' ;
                 
                 $actioncomm->elementtype=$object->element;
                 $actioncomm->fk_element = $object->id;
                 $actioncomm->fk_project = $object->fk_project;
                
                 $actioncomm->progress = 0;
                 
                 $actioncomm->durationp = 0;
                 
                 $actioncomm->socid = !empty($object->socid) ? $object->socid : $object->fk_soc;
                 
                 $actioncomm->add($user);
                
            }
            
            
            if(!empty($row->message_code)) {
                eval($row->message_code);
            }
            
            
        }
        
        
        $PDOdb->close();        
      
    }
    
}
