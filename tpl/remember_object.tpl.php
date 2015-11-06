<div class="RembemerMe">
	<table width="100%" class="border">
		<th>
			<td>Type</td>
			<td>Titre</td>
			<td>Message</td>
			<td>Nb jours après</td>
			[onshow;block=begin;when [view.status]=='DRAFT']
			<td>Actions</td>
			[onshow;block=end]
		</th>
		
		<tr>
			<td>Type</td>
			<td>Titre</td>
			<td>Message</td>
			<td>Nb jours après</td>
			[onshow;block=begin;when [view.status]=='DRAFT']
			<td>Actions</td>
			[onshow;block=end]
		</tr>
		
		
		
		<tr><td width="20%">Numéro</td><td>[assetOf.numero;strconv=no]</td></tr>
		<tr><td>Ordre</td><td>[assetOf.ordre;strconv=no;protect=no]</td></tr>
		[onshow;block=begin;when [assetOf.id]=0]
		<tr><td>Produit à produire</td><td>[assetOf.product_to_create;strconv=no;protect=no]</td></tr>
		<tr><td>Quantité à produire</td><td>[assetOf.quantity_to_create;strconv=no;protect=no]</td></tr>
		[onshow;block=end]
		<tr class="notinparentview"><td>OF Parent</td><td>[assetOf.link_assetOf_parent;strconv=no;protect=no;magnet=tr]</td></tr>
		<tr class="notinparentview"><td>Commande</td><td>[assetOf.fk_commande;strconv=no;magnet=tr]</td></tr>
		<tr class="notinparentview"><td>Commande Fournisseur</td><td>[assetOf.commande_fournisseur;strconv=no;magnet=tr]</td></tr>
		<tr><td>Client</td><td>[assetOf.fk_soc;strconv=no;protect=no;magnet=tr]</td></tr>
		<tr><td>Projet</td><td>[assetOf.fk_project;strconv=no;protect=no;magnet=tr]</td></tr>
		<tr><td>Date du besoin</td><td>[assetOf.date_besoin;strconv=no]</td></tr>
		<tr><td>Date de lancement</td><td>[assetOf.date_lancement;strconv=no]</td></tr>
		<tr><td>Temps estimé de fabrication</td><td>[assetOf.temps_estime_fabrication;strconv=no] heure(s)</td></tr>
		<tr><td>Temps réel de fabrication</td><td>[assetOf.temps_reel_fabrication;strconv=no] heure(s)</td></tr>
		<tr><td>Statut</td><td>[assetOf.status;strconv=no]<span style="display:none;">[assetOf.statustxt;strconv=no]</span>
			[onshow;block=begin;when [view.status]!='CLOSE';when [view.mode]=='view']
				<span class="viewmode notinparentview">
					
		
				[onshow;block=begin;when [view.status]=='DRAFT']
					, passer à l'état :<input type="button" onclick="if (confirm('Valider cet Ordre de Fabrication ?')) { submitForm([assetOf.id],'valider'); }" class="butAction" name="valider" value="Valider">
				[onshow;block=end]
				[onshow;block=begin;when [view.status]=='VALID']
					, passer à l'état :<input type="button" onclick="if (confirm('Lancer cet Ordre de Fabrication ?')) { submitForm([assetOf.id],'lancer'); }" class="butAction" name="lancer" value="Production en cours">
				[onshow;block=end]
				[onshow;block=begin;when [view.status]=='OPEN']
					, passer à l'état :<input type="button" onclick="if (confirm('Terminer cet Ordre de Fabrication ?')) { submitForm([assetOf.id],'terminer'); }" class="butAction" name="terminer" value="Terminer">
					<!-- <a href="[assetOf.url]?id=[assetOf.id]&action=terminer" onclick="return confirm('Terminer cet Ordre de Fabrication ?');" class="butAction">Terminer</a> -->
				[onshow;block=end]
			[onshow;block=end]
			</span>
		</td></tr>
		
		<tr><td>Actions</td><td>[assetOf.note;strconv=no]</td></tr>
		
	</table>
</div>