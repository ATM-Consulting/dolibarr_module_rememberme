<div class="RembemerMe">
	<style>
		table.remember td, table.remember th {
			padding: 2px 5px;
		}
	</style>
	<table width="100%" class="border remember">
		<tr>
			<th class="left">Type</th>
			<th class="left">Titre</th>
			<th class="left">Message</th>
			<th class="right">Nb jours après</th>
			[onshow;block=begin;when [view.statut]==0]
			<th>Actions</th>
			[onshow;block=end]
		</tr>
		<tr>
			<td>[TRemember.type;strconv=no;block=tr]</td>
			<td>[TRemember.titre;strconv=no;block=tr]</td>
			<td>[TRemember.description;strconv=no;block=tr]</td>
			<td class="right">[TRemember.nb_day_after;strconv=no;block=tr]</td>
			[onshow;block=begin;when [view.statut]==0]
			<td class="center"><a href="[TRemember.link;strconv=no;block=tr]">Modifier</a></td>
			[onshow;block=end]
		</tr>
	</table>
</div>