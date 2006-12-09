<div id="notify" class="tab">
  <p><em>{L('theseusersnotify')}</em></p>
  <?php foreach ($notifications as $row): ?>
  <p>
    {!tpl_userlink($row['user_id'])} &mdash;
    <a href="{$_SERVER['SCRIPT_NAME']}?action=remove_notification&amp;ids={$task_details['task_id']}&amp;user_id={$row['user_id']}">{L('remove')}</a>
  </p>
  <?php endforeach; ?>

  <?php if ($user->perms('manage_project')): ?>
  <form action="{$_SERVER['SCRIPT_NAME']}index.php#notify" method="get">
    <p>
        <label class="default multisel" for="notif_user_id">{L('addusertolist')}</label>
        {!tpl_userselect('user_id', Req::val('user_id'), 'notif_user_id')}
    
      <button type="submit">{L('add')}</button>
      <input type="hidden" name="ids" value="{Req::num('ids', $task_details['task_id'])}" />
      <input type="hidden" name="action" value="details.add_notification" />
    </p>
  </form>
  <?php endif; ?>
</div>

