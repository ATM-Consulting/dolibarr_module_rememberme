<?php

require_once DOL_DOCUMENT_ROOT .'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT .'/comm/propal/class/propal.class.php';

class TRememberMe extends TObjetStd {
	
    function __construct() { /* declaration */
        global $langs,$db;
        parent::set_table(MAIN_DB_PREFIX.'rememberme');
        parent::add_champs('fk_societe,fk_user',array('index'=>true, 'type'=>'int'));
        parent::add_champs('nb_day_after,fk_object,fk_parent',array('type'=>'int'));
        
        parent::add_champs('trigger_code,type,type_event,type_object', array('index'=>true, 'type'=>'string', 'length'=>50));
        
        parent::add_champs('titre,message,message_condition,message_code', array('type'=>'text'));
        
        parent::_init_vars('type_msg');
        parent::start();
		
		$this->titre = 'RememberMe - titre';
		$this->message = 'Bonjour [societe_nom]'."\n".'
Code client [societe_code_client]'."\n".'
Propale ref client [ref]
Propale date [date]';
        
        $this->type='MSG';
        $this->TType=array(
            'MSG'=>'Message écran'
            ,'EVENT'=>'Evènement agenda'
            ,'EMAIL'=>'Envoi email'
            ,'EVAL'=>'Evaluation du code php (attention !)'
        );
        
        $this->type_msg = 'mesgs';
        $this->TTypeMessage=array(
            'mesgs'=>'Information'
            ,'warnings'=>'Alerte'
            ,'errors'=>'Erreur'
        );
		$this->db = $db;
        
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
	
	static function getArrayModify(&$PDOdb, $Tab=array(), $object)
	{
		$TRes=array();
	    foreach($Tab as $row) {
	        $r=new TRememberMe;
			$r->rowid = $row->rowid;
	        $r->load($PDOdb, $row->rowid);
	        
			if($r->fk_parent == 0)
	        	$TRes[$r->getId()] = $r;
			else if($object->id == $r->fk_object)
	        	$TRes[$r->fk_parent] = $r;
	    }
		return $TRes;
	}
	
    /**
     *  Fetch all triggers for current object
     *
     *  @param	CommonObject	$object			Object used for search
     *  @param	TPDOdb			$PDOdb			Abricot db object
     *  @return int         					empty array if KO, array if OK
     */
    static function fetchAllForObject(&$PDOdb, $object) {
    	$TRes = array();
        if(isset($object))
		{
			$type_object=$object->element;
			
	        $sql = "SELECT * FROM ".MAIN_DB_PREFIX."rememberme";
	        $sql.= " WHERE trigger_code LIKE '%".$type_object."%'";
	        $sql.= " ORDER BY rowid ASC";
	        
	        $Tab = $PDOdb->ExecuteAsArray($sql);
	        $TRes = self::getArrayModify($PDOdb, $Tab, $object);
		}
		return $TRes;
    }
    
    static function message($action, &$object, $type='') {
        global $user, $db;
        
        $PDOdb = new TPDOdb;
        $sql = "SELECT * FROM ".MAIN_DB_PREFIX."rememberme 
                WHERE trigger_code='".$action."'";
        if(!empty($type)) $sql.=" AND  type = '".$type."' "; 
        
        $Tab = $PDOdb->ExecuteAsArray($sql);
		
		// Requete pour récuperer les actioncomm futurs
		$sql = "SELECT id, location FROM ".MAIN_DB_PREFIX."actioncomm
			    WHERE location LIKE '%rememberme%'
			    AND datep > NOW()";
        $TActioncomm = $PDOdb->ExecuteAsArray($sql);
		
        foreach($Tab as $row) {
        	// Switch pour gérer des spécificité en fonction des triggers
	        switch($action)
			{
				case preg_match('/VALIDATE/', strtoupper($action))?true:false:
					// On parcours les actioncomm futurs pour trouver celles qui correspondent au trigger
					// et si ça correspond on supprime l'évenement
					// Précision : les actioncomm qui contiennent rememberme en location avec id trigger, sont forcément des emails.
					foreach($TActioncomm as $OneActioncomm) {
						$location = $OneActioncomm->location;
						$Tlocation = explode('|',$location);
						if($Tlocation[1] == $row->rowid)
						{
							$actioncomm=new ActionComm($db);
							$actioncomm->fetch($OneActioncomm->id);
							$actioncomm->delete();
						}
					}
					break;
				default:
					break;
			}
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
				$actioncomm->label = 'RememberMe - '.$row->titre ;
				
				$actioncomm->elementtype=$object->element;
				$actioncomm->fk_element = $object->id;
				$actioncomm->fk_project = $object->fk_project;
				
				$actioncomm->progress = 0;
				
				$actioncomm->durationp = 0;
				// Utile pour le suivi de trigger
				$actioncomm->location = 'rememberme|'.$row->rowid;
				
				$actioncomm->socid = !empty($object->socid) ? $object->socid : $object->fk_soc;
				$actioncomm->note = $row->message;
				
				$actioncomm->add($user);
                
            }
            else if($row->type == 'EMAIL') {
				$actioncomm=new ActionComm($db);
				$actioncomm->socid = !empty($object->socid) ? $object->socid : $object->fk_soc;
				$actioncomm->datep = strtotime('+'.$row->nb_day_after.'day');
				 
				$actioncomm->userownerid = $user->id;
				$actioncomm->type_code='AC_RMB_EMAIL';
				
				$actioncomm->elementtype=$object->element;
				$actioncomm->fk_element = $object->id;
				
				$actioncomm->progress = 0;
				
				$actioncomm->durationp = 0;
				// Utile pour le suivi de trigger
				$actioncomm->location = 'rememberme|'.$row->rowid;
				
				$actioncomm->label = self::changeTags($object, $row->titre);
				$actioncomm->note = self::changeTags($object, $row->message);
				
				$actioncomm->add($user);

            }
            
            
            if(!empty($row->message_code)) {
                eval($row->message_code);
            }
            
            
        }        
        $PDOdb->close();        
      
    }

	static function changeTags($object, $val)
	{
		global $db;
		$societe = new Societe($db);
		$socid = !empty($object->socid) ? $object->socid : $object->fk_soc;
		$societe->fetch($socid);
		$date = date("Y-m-d", $object->date);
		$TNewval = array("{$societe->name}", "{$societe->code_client}", "{$object->newref}", "{$object->ref_client}", "{$date}");
		$TTags = array('[societe_nom]','[societe_code_client]','[ref]','[ref_client]','[date]');
		return str_replace($TTags, $TNewval, $val);
	}
    
}
