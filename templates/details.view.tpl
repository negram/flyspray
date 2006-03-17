<div id="taskdetails" <?php if ($user->can_edit_task($task_details)): ?>ondblclick='openTask("{CreateURL('edittask', $task_details['task_id'])}")'<?php endif;?>>
<span id="navigation"> <?php if ($prev_id): ?>
  {!tpl_tasklink($prev_id, $language['previoustask'], false, array('id'=>'prev', 'accesskey' => 'p'))}
  <?php endif; ?>
  <?php if ($prev_id && $next_id): ?> | <?php endif; ?>
  <?php if ($next_id): ?>
  {!tpl_tasklink($next_id, $language['nexttask'], false, array('id'=>'next', 'accesskey' => 'n'))}
  <?php endif; ?>
</span>

  <h2 class="severity{$task_details['task_severity']}">
	 FS#{$task_details['task_id']} &mdash; {$task_details['item_summary']}
  </h2>

  <div id="fineprint">
	 {$language['attachedtoproject']} &mdash;
	 <a href="{$baseurl}?project={$task_details['attached_to_project']}">{$task_details['project_title']}</a>
	 <br />
	 {$language['openedby']} {!tpl_userlink($task_details['opened_by'])}
	 - {!formatDate($task_details['date_opened'], true)}
	 <?php if ($task_details['last_edited_by']): ?>
	 <br />
	 {$language['editedby']}  {!tpl_userlink($task_details['last_edited_by'])}
	 - {formatDate($task_details['last_edited_time'], true)}
	 <?php endif; ?>
  </div>

  <div id="taskfields1">
	 <table>
		<tr class="tasktype">
		  <th id="tasktype">{$language['tasktype']}</th>
		  <td headers="tasktype">{$task_details['tasktype_name']}</td>
		</tr>
		<tr class="category">
		  <th id="category">{$language['category']}</th>
		  <td headers="category">
			 <?php if ($task_details['parent_category_name']): ?>
			 {$task_details['parent_category_name']} &#8594;
			 <?php endif; ?>
			 {$task_details['category_name']}
		  </td>
		</tr>
		<tr class="status">
		  <th id="status">{$language['status']}</th>
		  <td headers="status">
			 <?php if ($task_details['is_closed']): ?>
			 {$language['closed']}
			 <?php else: ?>
			 {$task_details['status_name']}
			 <?php endif; ?>
		  </td>
		</tr>
		<tr class="assignedto">
		  <th id="assignedto">{$language['assignedto']}</th>
		  <td headers="assignedto">
			 <?php if (empty($assigned_users)): ?>
			 {$language['noone']}
			 <?php else:
			 foreach ($assigned_users as $userid):
			 ?>
			 {!tpl_userlink($userid)}<br />
			 <?php endforeach;
			 endif; ?>
		  </td>
		</tr>
		<tr class="os">
		  <th id="os">{$language['operatingsystem']}</th>
		  <td headers="os">{$task_details['os_name']}</td>
		</tr>
	 </table>
  </div>

  <div id="taskfields2">
	 <table>
		<tr class="severity">
		  <th id="severity">{$language['severity']}</th>
		  <td headers="severity">{$task_details['severity_name']}</td>
		</tr>
		<tr class="priority">
		  <th id="priority">{$language['priority']}</th>
		  <td headers="priority">{$task_details['priority_name']}</td>
		</tr>
		<tr class="reportedver">
		  <th id="reportedver">{$language['reportedversion']}</th>
		  <td headers="reportedver">{$task_details['reported_version_name']}</td>
		</tr>
		<tr class="dueversion">
		  <th id="dueversion">{$language['dueinversion']}</th>
		  <td headers="dueversion">
			 <?php if ($task_details['due_in_version_name']): ?>
			 {$task_details['due_in_version_name']}
			 <?php else: ?>
			 {$language['undecided']}
			 <?php endif; ?>
		  </td>
		</tr>
		<tr class="duedate">
		  <th id="duedate">{$language['duedate']}</th>
		  <td headers="duedate">
			 {formatDate($task_details['due_date'], false, $language['undecided'])}
		  </td>
		</tr>
		<tr class="percent">
		  <th id="percent">{$language['percentcomplete']}</th>
		  <td headers="percent">
			 <img src="{$baseurl}themes/{$proj->prefs['theme_style']}/percent-{$task_details['percent_complete']}.png"
				title="{$task_details['percent_complete']}% {$language['complete']}"
				alt="{$task_details['percent_complete']}%" />
		  </td>
		</tr>
	 </table>
  </div>

  <div id="taskdetailsfull">
	 <h3 class="taskdesc">{$language['details']}</h3>
	 {!tpl_formatText($task_details['detailed_desc'])}

	 <?php // XXX stolen from details.tab.comment.tpl keep in sync
	 if ($user->perms['view_attachments'] || $proj->prefs['others_view']):
	 foreach ($attachments = $proj->listTaskAttachments($task_details['task_id']) as $attachment):
	 ?>
	 <span class="attachments">
		<a href="{$baseurl}?getfile={$attachment['attachment_id']}" title="{$attachment['file_type']}">
		  <?php
		  // Strip the mimetype to get the icon image name
		  list($main) = explode('/', $attachment['file_type']);
		  $imgdir = BASEDIR . "/themes/{$proj->prefs['theme_style']}/mime/";
          $imgpath = "{$baseurl}themes/{$proj->prefs['theme_style']}/mime/";
		  if (file_exists($imgdir.$attachment['file_type'] . '.png')):
		  ?>
		  <img src="{$imgpath}{$attachment['file_type']}.png" alt="({$attachment['file_type']})" title="{$attachment['file_type']}" />
		  <?php else: ?>
		  <img src="{$imgpath}{$main}.png" alt="" title="{$attachment['file_type']}" />
		  <?php endif; ?>
		  &nbsp;&nbsp;{$attachment['orig_name']}</a>

		<?php if ($user->perms['delete_attachments']): ?>
		&mdash;
		<a href="{$baseurl}?do=modify&amp;action=deleteattachment&amp;id={$attachment['attachment_id']}"
		  onclick="return confirm('{$language['confirmdeleteattach']}');">
		  {$language['delete']}</a>
		<?php endif; ?>
	 </span>
	 <?php endforeach; ?>
	 <br />
	 <?php elseif (count($attachments)): ?>
	 <span class="attachments">{$language['attachnoperms']}</span>
	 <br />
	 <?php endif; ?>
  </div>

  <div id="taskinfo">
	 <div id="taskdeps">
		<b>{$language['taskdependson']}</b>
		<br />
		<?php foreach ($deps as $dependency): ?>
		<?php $link = tpl_tasklink($dependency, null, true);
				if(!$link) continue;
		?>
		{!$link}
		<?php if ($user->can_edit_task($task_details)): ?>
		<span class="DoNotPrint"> &mdash;
		  <a class="removedeplink"
			 href="{$baseurl}?do=modify&amp;action=removedep&amp;depend_id={$dependency['depend_id']}">
			 {$language['remove']}</a>
		</span>
		<?php endif; ?>
		<br />
		<?php endforeach; ?>

		<br class="DoNotPrint" />

		<?php if (count($deps) || count($blocks)): ?>
		<a class="DoNotPrint" href="{CreateURL('depends', Get::val('id'))}">{$language['depgraph']}</a>
		<br />
		<br />
		<?php endif; ?>

		<?php if ($user->can_edit_task($task_details)): ?>
		<form action="{$baseurl}" method="post">
		  <div>
			 <input type="hidden" name="do" value="modify" />
			 <input type="hidden" name="action" value="newdep" />
			 <input type="hidden" name="task_id" value="{Get::val('id')}" />
			 <input class="text" type="text" name="dep_task_id" size="5" maxlength="10" />
			 <button type="submit" name="submit">{$language['addnew']}</button>
		  </div>
		</form>
		<?php endif; ?>
	 </div>

	 <div id="taskblocks">
		<b>{$language['taskblocks']}</b>
		<br />
		<?php foreach ($blocks as $block): ?>
		<?php $link = tpl_tasklink($block, null, true);
				if(!$link) continue;
		?>
		{!$link}
		<br />
		<?php endforeach; ?>
	 </div>
  </div>

  <?php if ($task_details['is_closed']): ?>
  <div id="taskclosed">
      {$language['closedby']}&nbsp;&nbsp;{!tpl_userlink($task_details['closed_by'])}<br />
      {formatDate($task_details['date_closed'], true)}<br />
      <strong>{$language['reasonforclosing']}</strong> &nbsp;{$task_details['resolution_name']}<br />
      <?php if ($task_details['closure_comment']): ?>
      <strong>{$language['closurecomment']}</strong> &nbsp;{!tpl_FormatText($task_details['closure_comment'], true)}
      <?php endif; ?>
  </div>
  <?php endif; ?>

  <div id="actionbuttons">
	 <?php if ($task_details['is_closed']): ?>

	 <?php if ($user->can_close_task($task_details)): ?>
	 <a href="{$baseurl}?do=modify&amp;action=reopen&amp;task_id={Get::val('id')}">
		{$language['reopenthistask']}</a>
	 <?php elseif (!$fs->adminRequestCheck(2, $task_details['task_id']) && !$user->isAnon()): ?>
	 <a href="#close" id="reqclose" class="button" onclick="showhidestuff('closeform');">
		{$language['reopenrequest']}</a>
	 <div id="closeform">
		<form name="form3" action="{$baseurl}" method="post" id="formclosetask">
		  <div>
			 <input type="hidden" name="do" value="modify" />
			 <input type="hidden" name="action" value="requestreopen" />
			 <input type="hidden" name="task_id" value="{Get::val('id')}" />
			 <label for="reason">{$language['reasonforreq']}</label>
			 <textarea id="reason" name="reason_given"></textarea><br />
			 <button type="submit">{$language['submitreq']}</button>
		  </div>
		</form>
	 </div>
	 <?php endif; ?>
     <?php if (!$user->isAnon()): ?>
	 <?php if (!$watched): ?>
	 <a id="addnotif" class="button" accesskey="w"
		href="{$baseurl}?do=modify&amp;action=add_notification&amp;ids={Get::val('id')}&amp;user_id={$user->id}">
		{$language['watchtask']}</a>
	 <?php else: ?>
	 <a id="removenotif" class="button" accesskey="w"
		href="{$baseurl}?do=modify&amp;action=remove_notification&amp;ids={Get::val('id')}&amp;user_id={$user->id}">
		{$language['stopwatching']}</a>
	 <?php endif; ?>
	 <?php endif; ?>

	 <?php else: ?>

	 <?php if ($user->can_close_task($task_details) && !$d_open): ?>
	 <a href="#close" id="closetask" class="button" accesskey="y" onclick="showhidestuff('closeform');">
		{$language['closetask']}</a><div id="closeform">
		<form action="{$baseurl}" method="post" id="formclosetask">
		  <div>
			 <input type="hidden" name="do" value="modify" />
			 <input type="hidden" name="action" value="close" />
			 <input type="hidden" name="assigned_to" value="{implode(' ',$task_details['assigned_to'])}" />
			 <input type="hidden" name="task_id" value="{Get::val('id')}" />
			 <select class="adminlist" name="resolution_reason">
				<option value="0">{$language['selectareason']}</option>
				{!tpl_options($proj->listResolutions())}
			 </select>
			 <button type="submit">{$language['closetask']}</button>
			 <label class="default text" for="closure_comment">{$language['closurecomment']}</label>
			 <textarea class="text" id="closure_comment" name="closure_comment" rows="3" cols="30"></textarea>
			 <input type="checkbox" name="mark100" value="1" checked="checked" />&nbsp;&nbsp;{$language['mark100']}
		  </div>
		</form>
	 </div>
	 <?php elseif (!$d_open && $task_details['assigned_to'] == $user->id
	 && !$fs->AdminRequestCheck(1, $task_details['task_id'])): ?>
	 <a href="#close" id="reqclose" class="button" onclick="showhidestuff('closeform');">
		{$language['requestclose']}</a>
	 <div id="closeform">
		<form name="form3" action="{$baseurl}" method="post" id="formclosetask">
		  <div>
			 <input type="hidden" name="do" value="modify" />
			 <input type="hidden" name="action" value="requestclose" />
			 <input type="hidden" name="task_id" value="{Get::val('id')}" />
			 <label for="reason">{$language['reasonforreq']}</label>
			 <textarea id="reason" name="reason_given"></textarea><br />
			 <button type="submit">{$language['submitreq']}</button>
		  </div>
		</form>
	 </div>
	 <?php endif; ?>

	 <?php if ($user->can_take_ownership($task_details)): ?>
	 <a id="own" class="button"
		href="{$baseurl}?do=modify&amp;action=takeownership&amp;ids={Get::val('id')}">
		{$language['assigntome']}</a>
	 <?php endif; ?>

	 <?php if ($user->can_add_to_assignees($task_details)): ?>
	 <a id="own_add" class="button"
		href="{$baseurl}?do=modify&amp;action=addtoassignees&amp;ids={Get::val('id')}">
		{$language['addmetoassignees']}</a>
	 <?php endif; ?>

	 <?php if ($user->can_edit_task($task_details)): ?>
	 <a id="edittask" class="button" href="{CreateURL('edittask', Get::val('id'))}">
		{$language['edittask']}</a>
	 <?php endif; ?>

	 <?php if ($user->can_mark_public($task_details)): ?>
	 <a id="public" class="button"
		href="{$baseurl}?do=modify&amp;action=makepublic&amp;id={Get::val('id')}">
		{$language['makepublic']}</a>
	 <?php elseif ($user->can_mark_private($task_details)): ?>
	 <a id="private" class="button"
		href="{$baseurl}?do=modify&amp;action=makeprivate&amp;id={Get::val('id')}">
		{$language['makeprivate']}</a>
	 <?php endif; ?>

	 <?php if (!$user->isAnon()): ?>
	 <?php if (!$watched): ?>
	 <a id="addnotif" class="button" accesskey="w"
		href="{$baseurl}?do=modify&amp;action=add_notification&amp;ids={Get::val('id')}&amp;user_id={$user->id}">
		{$language['watchtask']}</a>
	 <?php else: ?>
	 <a id="removenotif" class="button" accesskey="w"
		href="{$baseurl}?do=modify&amp;action=remove_notification&amp;ids={Get::val('id')}&amp;user_id={$user->id}">
		{$language['stopwatching']}</a>
	 <?php endif; ?>
	 <?php endif; ?>
         
	 <?php if ($user->can_vote($task_details['task_id'])): ?>
            <a id="addvote" class="button"
                href="{$baseurl}?do=modify&amp;action=addvote&amp;id={Get::val('id')}">
                {$language['addvote']}</a>
         <?php endif; ?>

	 <?php endif; ?>
	 <?php if (count($penreqs)): ?>
     <span class="pendingreq">{$language['taskpendingreq']}</span>
     <?php endif; ?>
  </div>
</div>
