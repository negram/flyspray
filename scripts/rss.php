<?php
// We can't include this script as part of index.php?do= etc,
// as that would introduce html code into it.  HTML != Valid XML
// So, include the headerfile to set up database access etc
require_once(dirname(dirname(__FILE__)).'/header.php');

$limit = intval(Req::val('num', 10));
$proj  = intval(Req::val('proj', $fs->prefs['default_project']));

switch (Req::val('type')) {
   case 'new': $orderby = 'date_opened';
               $title   = 'Recently opened tasks';
   break;
   case 'clo': $orderby = 'date_closed';
               $title   = 'Recently closed tasks';
   break;
   case 'sev': $orderby = 'task_severity';
               $title   = 'Most severe tasks';
   break;
   case 'pri': $orderby = 'task_priority';
               $title   = 'Priority tasks';
   break;
   default:    $orderby = 'date_opened';
               $title   = 'Recently opened tasks';
   break;
}

$task_details = $db->Query("SELECT  task_id, item_summary, detailed_desc
                              FROM  {tasks} t
                         LEFT JOIN  {projects} p ON t.attached_to_project = p.project_id
                             WHERE  t.is_closed <> '1' AND p.project_id = ?
                                    AND p.project_is_active = '1' AND t.mark_private <> '1'
                          ORDER BY  $orderby DESC", array($proj), $limit);

// Set up the basic XML head
header ('Content-type: text/xml');
echo '<?xml version="1.0"?>'."\n";
?>
<rss version="2.0">
  <channel>
    <title>Flyspray</title>
    <description>Flyspray:: <?php echo $proj->prefs['project_title'] . ': ' .  $title ?></description>
    <link>http://flyspray.rocks.cc/</link>
<?php
        while ($row = $db->FetchArray($task_details)):
            $item_summary  = htmlspecialchars($row['item_summary']);
            $detailed_desc = htmlspecialchars($row['detailed_desc']);
?>
    <item>
      <title><?php echo $item_summary ?></title>
      <description><?php echo tpl_FormatText($detailed_desc) ?></description>
      <link><?php echo $fs->CreateURL('details', $row['task_id']) ?></link>
    </item>
<?php   endwhile; ?>
  </channel>
</rss>
