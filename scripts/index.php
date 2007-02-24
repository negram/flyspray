<?php

/*
   This script sets up and shows the front page with
   the list of all available tasks that the user is
   allowed to view.
*/

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

if (!$user->can_view_project($proj->id)) {
    $proj = new Project(0);
}

$perpage = '20';
if (isset($user->infos['tasks_perpage'])) {
    $perpage = $user->infos['tasks_perpage'];
}

$pagenum = max(1, Get::num('pagenum', 1));

$offset = $perpage * ($pagenum - 1);

// Get the visibility state of all columns
$visible = explode(' ', trim($proj->id ? $proj->prefs['visible_columns'] : $fs->prefs['visible_columns']));
if (!is_array($visible) || !count($visible) || !$visible[0]) {
    $visible = array('id');
}

list($tasks, $id_list) = Backend::get_task_list($_GET, $visible, $offset, $perpage);

$page->uses('tasks', 'offset', 'perpage', 'pagenum', 'visible');

// List of task IDs for next/previous links
$_SESSION['tasklist'] = $id_list;
$page->assign('total', count($id_list));

// }}}
// tpl function that  draws a cell {{{

function tpl_draw_cell($task, $colname, $format = "<td class='%s'>%s</td>") {
    global $fs, $proj, $page;

    $indexes = array (
            'id'         => 'task_id',
            'project'    => 'project_title',
            'severity'   => '',
            'summary'    => 'item_summary',
            'dateopened' => 'date_opened',
            'openedby'   => 'opened_by_name',
            'assignedto' => 'assigned_to_name',
            'lastedit'   => 'event_date',
            'comments'   => 'num_comments',
            'votes'      => 'num_votes',
            'attachments'=> 'num_attachments',
            'dateclosed' => 'date_closed',
            'progress'   => '',
            'state'      => '',
        );

    //must be an array , must contain elements and be alphanumeric (permitted  "_")
    if(!is_array($task) || empty($task) || preg_match('![^A-Za-z0-9_]!', $colname)) {
        //run away..
        return '';
    }

    switch ($colname) {
        case 'id':
            $value = tpl_tasklink($task, $task['task_id']);
            break;
        case 'summary':
            $value = tpl_tasklink($task, utf8_substr($task['item_summary'], 0, 55), false, array(), array('state','age','percent_complete'));
            if (utf8_strlen($task['item_summary']) > 55) {
                $value .= '...';
            }
            break;

        case 'severity':
            $value = @$fs->severities[$task['task_severity']];
            break;

        case 'dateopened':
        case 'dateclosed':
        case 'lastedit':
            $value = formatDate($task[$indexes[$colname]]);
            break;

        case 'progress':
            $value = tpl_img($page->get_image('percent-' . $task['percent_complete'], false),
                    $task['percent_complete'] . '%');
            break;

        case 'assignedto':
            $value = htmlspecialchars($task[$indexes[$colname]], ENT_QUOTES, 'utf-8');
            if ($task['num_assigned'] > 1) {
                $value .= ', +' . ($task['num_assigned'] - 1);
            }
            break;

        case 'state':
            if ($task['is_closed']) {
                $value = L('closed');
            } elseif ($task['closed_by']) {
                $value = L('reopened');
            } else {
                $value = L('open');
            }
            break;

        default:
            if (isset($indexes[$colname])) {
                $value = $task[$indexes[$colname]];
            } elseif (isset($task[$colname . '_name'])) {
                $value = $task[$colname . '_name'];
            } else {
                $value = $task[$colname];
            }
            htmlspecialchars($value, ENT_QUOTES, 'utf-8');
            break;
    }

    return sprintf($format, 'task_'.$colname, $value);
}

// } }}

// Javascript replacement
if (Get::val('toggleadvanced')) {
    $advanced_search = intval(!Req::val('advancedsearch'));
    Flyspray::setCookie('advancedsearch', $advanced_search, time()+60*60*24*30);
    $_COOKIE['advancedsearch'] = $advanced_search;
}

// Update check {{{
if (Get::has('hideupdatemsg')) {
    unset($_SESSION['latest_version']);
} else if ($conf['general']['update_check'] && $user->perms('is_admin')
           && $fs->prefs['last_update_check'] < time()-60*60*24*3) {
    if (!isset($_SESSION['latest_version'])) {
        $latest = Flyspray::remote_request('http://flyspray.org/version.txt', GET_CONTENTS);
		//if for some silly reason we get and empty response, we use the actual version
 		$_SESSION['latest_version'] = empty($latest) ? $fs->version : $latest ;
        $db->Execute('UPDATE {prefs} SET pref_value = ? WHERE pref_id = 23', array(time()));
	}
}
if (isset($_SESSION['latest_version']) && version_compare($fs->version, $_SESSION['latest_version'] , '<') ) {
    $page->assign('updatemsg', true);
}
// }}}
$page->setTitle($fs->prefs['page_title'] . $proj->prefs['project_title'] . ': ' . L('tasklist'));
$page->pushTpl('index.tpl');

?>
