<?php

require 'config.php';
require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/propal.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
dol_include_once('/rememberme/lib/rememberme.lib.php');

$langs->load("orders");
$langs->load("sendings");
$langs->load("companies");
$langs->load("propal");
$langs->load("rememberme@rememberme");

$id = __get('id', 0);
$ref= __get('ref', '');
$action=__get('action','view');
/*************************
 * 
 *  START check propal
 * 
 * ***********************/
// Security check
if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'propal', $id);

$object = new Propal($db);

// Load object
if ($id > 0 || ! empty($ref))
{
	$ret=$object->fetch($id, $ref);
	if ($ret == 0)
	{
		$langs->load("errors");
		setEventMessage($langs->trans('ErrorRecordNotFound'), 'errors');
		$error++;
	}
	else if ($ret < 0)
	{
		setEventMessage($object->error, 'errors');
		$error++;
	}
}
if ($error)
{
	header('Location: '.DOL_URL_ROOT.'/comm/propal.php?id='.$id);
	exit;
}
/*************************
 * 
 *    END check propal
 * 
 * ***********************/


$PDOdb=new TPDOdb;

switch($action) {	
	case 'new':
		$remember=new TRememberMe;
		
		_fiche($PDOdb, $remember, 'edit');
		
		break;

	case 'edit':
		$remember=new TRememberMe;
		$remember->load($PDOdb, $id);
		
		_fiche($PDOdb, $remember, 'edit');
		
		break;
		
	case 'save':
		$remember=new TRememberMe;
		$remember->load($PDOdb, $id);
		$remember->set_values($_REQUEST);
		$remember->save($PDOdb);
	
		setEventMessage($langs->trans('AssetSaveControlEvent'));
	
		_fiche($PDOdb, $remember, 'view');
		
		break;
	
	case 'delete':				
		$remember=new TRememberMe;
		$remember->load($PDOdb, $id);
		$remember->delete($PDOdb);
		
		$_SESSION['AssetMsg'] = 'AssetDeleteControlEvent';
		header('Location: '.DOL_MAIN_URL_ROOT.'/custom/asset/list_control.php');
		
		break;
	case 'view':
	default:
		$TRemember = TRememberMe::fetchAllForObject($PDOdb, $object);
		
		_fiche($PDOdb, $object, $TRemember, 'view');
		
		break;
}


function _fiche(&$PDOdb, $object, $TRemember = array(), $mode='edit') {
	global $langs,$db,$conf,$user,$hookmanager;
	/***************************************************
	* PAGE
	*
	* Put here all code to build page
	****************************************************/
	$parameters = array('id'=>$object->id);
	$reshook = $hookmanager->executeHooks('doActions',$parameters,$object,$mode);    // Note that $action and $object may have been modified by hook
	
	llxHeader('',$langs->trans('Propal'),'','');
	$head = propal_prepare_head($object);
	dol_fiche_head($head, 'remembermepropal', $langs->trans('Proposal'), 0, 'propal');
	
	$TBS=new TTemplateTBS();
	$TRemember = _fiche_ligne_rememberme_item($object, $TRemember);
	print $TBS->render('tpl/remember_object.tpl.php'
		,array(
			'TRemember'=> $TRemember
			)
		,array(
			'view'=>array(
				'mode'=>$mode
				,'statut'=>$object->statut
				,'user_id'=>$user->id
			)
		)
	);
	
	llxFooter('$Date: 2011/07/31 22:21:57 $ - $Revision: 1.19 $');
}

function _fiche_ligne_rememberme_item($object, &$TRemember)
{
    $res = array();
	
    foreach($TRemember as $item)
    {
        $res[] = array(
            'rowid' => $item->rowid
            ,'titre' => TRememberMe::changeTags($object, $item->titre)
            ,'description' => nl2br(TRememberMe::changeTags($object, $item->message))
            ,'type' => $item->type
            ,'nb_day_after' => $item->nb_day_after
            ,'link' => dol_buildpath('rememberme/admin/rememberme_setup.php',2)           
        );
    }
    return $res;
}
