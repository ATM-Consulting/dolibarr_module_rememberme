<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\file		admin/rememberme.php
 * 	\ingroup	rememberme
 * 	\brief		This file is an example module setup page
 * 				Put some comments here
 */
// Dolibarr environment
require '../config.php';
// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/rememberme.lib.php';

// Translations
$langs->load("rememberme@rememberme");

// Access control
if (! $user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');
$PDOdb=new TPDOdb;
/*
 * Actions
 */

 if($action == 'save') {
     
     if(!empty($_REQUEST['TRemember'])) {
         
         foreach($_REQUEST['TRemember'] as $id_rem => &$rem) {
            
             $r=new TRememberMe;
             $r->load($PDOdb, $id_rem);
             $r->set_values($rem);
             
             $r->fk_societe = GETPOST('TRemember_'.$r->getId().'_fk_soc');
             $r->fk_user = GETPOST('TRemember_'.$r->getId().'_fk_user');
             
             $r->save($PDOdb);
         }
         
         
         setEventMessage('Saved');
     }
     
 } 
 else if($action == 'delete'){
     $r=new TRememberMe;
     $r->load($PDOdb, GETPOST('id'));
     $r->delete($PDOdb);
 }
 else if($action == 'add'){
     $r=new TRememberMe;
     $r->save($PDOdb);
     
 }
 
 
/*
 * View
 */
$page_name = "RememberMeSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
    . $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = remembermeAdminPrepareHead();
dol_fiche_head(
    $head,
    'settings',
    $langs->trans("Module104330Name"),
    0,
    "rememberme@rememberme"
);

// Setup page goes here
$form=new Form($db);
$formCore = new TFormCore('auto','formSave', 'post');
echo $formCore->hidden('action', 'save');

$var=false;

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("TypeTrigger").'</td>'."\n";
print '<td>'.$langs->trans("CompanyOrUser").'</td>'."\n";

print '<td>'.$langs->trans("Message").'</td>'."\n";

print '<td>'.$langs->trans("Condition").'</td>'."\n";
print '<td>'.$langs->trans("Action").'</td>'."\n";
print '</tr>';


    $TRemember = TRememberMe::getAll($PDOdb);

    foreach($TRemember as &$r) {
        
        $class = ($class == 'impair') ? 'pair' : 'impair';
        
        ?>
        <tr class="<?php echo $class  ?>">
            <td valign="top"><?php echo $formCore->combo('', 'TRemember['.$r->getId().'][type]', $r->TType, $r->type); ?><br />
            <?php echo $formCore->texte('','TRemember['.$r->getId().'][trigger_code]' , $r->trigger_code, 25,50, '', 'trigger_code'); 
            
            if($r->type == 'EVENT') {
                
                echo '<br />'.$formCore->texte($langs->trans('NbDayAfter'),'TRemember['.$r->getId().'][nb_day_after]' , $r->nb_day_after, 3,5); 
                
            }
            
            ?></td>
            <td valign="top"><?php echo $form->select_thirdparty_list($r->fk_societe,'TRemember_'.$r->getId().'_fk_soc', '', 1); ?>
                <br />
                <?php echo $form->select_dolusers( (empty($r->fk_user) ? -1 : $r->fk_user)  ,'TRemember_'.$r->getId().'_fk_user' ,1); ?><script type="text/javascript">
                 
                </script></td>
            <td valign="top"><?php 
                if($r->type != 'EVAL') {
                    echo $formCore->combo('', 'TRemember['.$r->getId().'][type_msg]', $r->TTypeMessage, $r->type_msg).'<br />';
                    echo $formCore->zonetexte('','TRemember['.$r->getId().'][message]' , $r->message, 50,5);
                    
                } 
            ?></td>
            
            <td valign="bottom"><?php 
                    echo $formCore->zonetexte($langs->trans('CodeToEvalBefore').'<br />','TRemember['.$r->getId().'][condition]' , $r->message_condition, 50,2); 
                    //if($r->type == 'EVAL') {
                        echo '<br />'.$formCore->zonetexte($langs->trans('CodeToEvalAfter').'<br />','TRemember['.$r->getId().'][message_code]' , $r->message_code, 50,2);
                    //}       
             ?></td>
            
            <td valign="bottom"><?php echo '<a href="?action=delete&id='.$r->getId().'">'.img_delete().'</a>';  ?></td>
        </tr>
        
        <?php
        
        
    }
    

print '</table>';


echo '<div class="tabsAction">
 <a href="?action=add" class="butAction">'.$langs->trans('Add').'</a>
 <input type="submit" class="butAction" value="'.$langs->trans('Save').'" name="bt_save" />
</div>
';

$formCore->end();
?>  
<script type="text/javascript">
$(document).ready(function() {
    var TTrigger = [
    "USER_CREATE","USER_CREATE_FROM_CONTACT","USER_MODIFY","USER_DELETE","USER_LOGIN","USER_LOGIN_FAILED","USER_LOGOUT","USER_ENABLEDISABLE"
    ,"USER_NEW_PASSWORD","USER_SETINGROUP","USER_REMOVEFROMGROUP","GROUP_CREATE","GROUP_MODIFY","GROUP_DELETE","COMPANY_CREATE","COMPANY_MODIFY"
    ,"COMPANY_DELETE","COMPANY_SENTBYMAIL","CONTACT_CREATE","CONTACT_MODIFY","CONTACT_DELETE","CONTACT_ENABLEDISABLE","PRODUCT_CREATE"
    ,"PRODUCT_MODIFY","PRODUCT_PRICE_MODIFY","PRODUCT_DELETE","SUPPLIER_PRODUCT_BUYPRICE_UPDATE","SUPPLIER_PRODUCT_BUYPRICE_CREATE"
    ,"ORDER_CREATE","ORDER_VALIDATE","ORDER_SENTBYMAIL","ORDER_DELETE","ORDER_CLASSIFY_BILLED","ORDER_CLONE","ORDER_CLOSE","ORDER_CANCEL"
    ,"ORDER_REOPEN","COMMANDE_ADD_CONTACT","COMMANDE_DELETE_CONTACT","COMMANDE_DELETE_RESOURCE","LINEORDER_INSERT","LINEORDER_UPDATE"
    ,"LINEORDER_DELETE","LINEORDER_DISPATCH","ORDER_SUPPLIER_CREATE","ORDER_SUPPLIER_CLONE","ORDER_SUPPLIER_VALIDATE","ORDER_SUPPLIER_SENTBYMAIL"
    ,"ORDER_SUPPLIER_APPROVE","ORDER_SUPPLIER_REFUSE","ORDER_SUPPLIER_CANCEL","ORDER_SUPPLIER_DELETE","ORDER_SUPPLIER_DISPATCH"
    ,"ORDER_SUPPLIER_ADD_CONTACT","ORDER_SUPPLIER_DELETE_CONTACT","ORDER_SUPPLIER_DELETE_RESOURCE","LINEORDER_SUPPLIER_CREATE"
    ,"LINEORDER_SUPPLIER_UPDATE","LINEORDER_SUPPLIER_DELETE","PROPAL_CREATE","PROPAL_DELETE","PROPAL_CLONE","PROPAL_REOPEN"
    ,"PROPAL_VALIDATE","PROPAL_CLOSE_SIGNED","PROPAL_CLOSE_REFUSED","PROPAL_SENTBYMAIL","PROPAL_ADD_CONTACT","PROPAL_DELETE_CONTACT"
    ,"PROPAL_DELETE_RESOURCE","LINEPROPAL_INSERT","LINEPROPAL_UPDATE","LINEPROPAL_DELETE","CONTRACT_CREATE","CONTRACT_VALIDATE"
    ,"CONTRACT_SERVICE_ACTIVATE","CONTRACT_SERVICE_CLOSE","CONTRACT_DELETE","CONTRAT_ADD_CONTACT","CONTRAT_DELETE_CONTACT"
    ,"CONTRAT_DELETE_RESOURCE","LINECONTRACT_INSERT","LINECONTRACT_UPDATE","LINECONTRACT_DELETE","BILL_CREATE","BILL_MODIFY"
    ,"BILL_CLONE","BILL_VALIDATE","BILL_UNVALIDATE","BILL_PAYED","BILL_UNPAYED","BILL_CANCEL","BILL_DELETE","BILL_SENTBYMAIL"
    ,"FACTURE_ADD_CONTACT","FACTURE_DELETE_CONTACT","FACTURE_DELETE_RESOURCE","LINEBILL_INSERT","LINEBILL_UPDATE","LINEBILL_DELETE"
    ,"BILL_SUPPLIER_CREATE","BILL_SUPPLIER_MODIFY","BILL_SUPPLIER_DELETE","BILL_SUPPLIER_VALIDATE","BILL_SUPPLIER_PAYED"
    ,"BILL_SUPPLIER_UNPAYED","BILL_SUPPLIER_SENTBYMAIL","INVOICE_SUPPLIER_ADD_CONTACT","INVOICE_SUPPLIER_DELETE_CONTACT"
    ,"INVOICE_SUPPLIER_DELETE_RESOURCE","LINEBILL_SUPPLIER_CREATE","LINEBILL_SUPPLIER_UPDATE","LINEBILL_SUPPLIER_DELETE"
    ,"PAYMENT_CUSTOMER_CREATE","PAYMENT_DELETE","PAYMENT_ADD_TO_BANK","PAYMENT_SUPPLIER_CREATE","PAYMENT_SALARY_CREATE"
    ,"PAYMENT_SALARY_MODIFY","PAYMENT_SALARY_DELETE","FICHINTER_CREATE","FICHINTER_MODIFY","FICHINTER_DELETE","FICHINTER_VALIDATE"
    ,"FICHINTER_SENTBYMAIL","FICHINTER_ADD_CONTACT","FICHINTER_DELETE_CONTACT","FICHINTER_DELETE_RESOURCE","LINEFICHINTER_CREATE"
    ,"LINEFICHINTER_UPDATE","LINEFICHINTER_DELETE","MEMBER_CREATE","MEMBER_VALIDATE","MEMBER_SUBSCRIPTION","MEMBER_MODIFY"
    ,"MEMBER_RESILIATE","MEMBER_NEW_PASSWORD","MEMBER_DELETE","CATEGORY_CREATE","CATEGORY_MODIFY","CATEGORY_DELETE","CATEGORY_LINK"
    ,"CATEGORY_UNLINK","SHIPPING_CREATE","SHIPPING_VALIDATE","SHIPPING_MODIFY","SHIPPING_DELETE","SHIPPING_SENTBYMAIL","DELIVERY_VALIDATE"
    ,"DELIVERY_DELETE","ACTION_CREATE","ACTION_MODIFY","ACTION_DELETE","ACTION_ADD_CONTACT","ACTION_DELETE_CONTACT","ACTION_DELETE_RESOURCE"
    ,"DEPLACEMENT_CREATE","DON_CREATE","DON_MODIFY","DON_DELETE","LOCALTAX_CREATE","LOCALTAX_MODIFY","LOCALTAX_DELETE","TVA_CREATE"
    ,"TVA_MODIFY","TVA_DELETE","TVA_ADDPAYMENT","PROJECT_CREATE","PROJECT_MODIFY","PROJECT_DELETE","PROJECT_VALIDATE","PROJECT_CLOSE"
    ,"PROJECT_ADD_CONTACT","PROJECT_DELETE_CONTACT","PROJECT_DELETE_RESOURCE","TASK_CREATE","TASK_MODIFY","TASK_DELETE","TASK_TIMESPENT_CREATE"
    ,"TASK_TIMESPENT_MODIFY","TASK_TIMESPENT_DELETE","PROJECT_TASK_ADD_CONTACT","PROJECT_TASK_DELETE_CONTACT","PROJECT_TASK_DELETE_RESOURCE"
    ,"MYECMDIR_CREATE","MYECMDIR_MODIFY","MYECMDIR_DELETE","IMPORT_DELETE","STOCK_MOVEMENT","PAYBOX_PAYMENT_OK","PAYPAL_PAYMENT_OK"
    ,"LINK_CREATE","LINK_MODIFY","LINK_DELETE","OPENSURVEY_CREATE","OPENSURVEY_DELETE"
    ];
    $( ".trigger_code" ).autocomplete({
      source: TTrigger
    });
    
   
  });
</script>
<?php

llxFooter();

$db->close();