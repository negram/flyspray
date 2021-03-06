<?php

  /********************************************************\
  | Task Dependancy Graph                                  |
  | ~~~~~~~~~~~~~~~~~~~~~                                  |
  \********************************************************/

/**
 * XXX: This stuff looks incredible ugly, rewrite me for 1.0
 */

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

class FlysprayDoDepends extends FlysprayDo
{
    var $task = array();

    /**
     * _onsubmit 
     * 
     * @access protected
     * @return array
     */
    function _onsubmit()
    {
        global $conf;

        if (Flyspray::function_disabled('shell_exec') && !array_get($conf['general'], 'dot_public')) {
            return array(ERROR_INPUT, L('error24'), CreateUrl(array('details', 'task' . $this->task['task_id'])));
        }
        return array(NO_SUBMIT);
    }

    /**
     * is_accessible 
     * 
     * @access public
     * @return bool
     */
    function is_accessible()
    {
        global $user;

        return ($this->task = Flyspray::GetTaskDetails(Get::num('task_id')))
                && $user->can_view_task($this->task);
    }

    /**
     * is_projectlevel 
     * 
     * @access public
     * @return void
     */
    function is_projectlevel() {
        return true;
    }

    /**
     * show 
     * 
     * @access public
     * @return void
     */
    function show()
    {
        global $user, $page, $fs, $conf, $db, $proj, $baseurl;

        $path_to_dot = array_get($conf['general'], 'dot_path', '');
        //php 4 on windows does not have is_executable..
        $func = function_exists('is_executable') ? 'is_executable' : 'is_file';
        $path_to_dot = $func($path_to_dot) ? $path_to_dot : '';
        $useLocal    = !Flyspray::function_disabled('shell_exec') && $path_to_dot;
        $fmt         = Filters::enum(array_get($conf['general'], 'dot_format', 'png'), array('png','svg'));


        $id = $this->task['task_id'];
        $page->assign('task_id', $id);

        $prunemode = Get::num('prune', 0);
        $selfurl   = CreateURL(array('depends', 'task' . $id));
        $pmodes    = array(L('none'), L('pruneclosedlinks'), L('pruneclosedtasks'));

        foreach ($pmodes as $mode => $desc) {
            if ($mode == $prunemode) {
                $strlist[] = $desc;
            } else {
                $strlist[] = "<a href='". Filters::noXSS($selfurl) .
                              ($mode !=0 ? "&amp;prune=$mode" : "") . "'>$desc</a>\n";
            }
        }

        $page->assign('strlist', $strlist);

        $starttime = microtime();

        $sql= 'SELECT t1.task_id AS id1, t1.prefix_id AS pxid1, p1.project_prefix AS ppx1, t1.item_summary AS sum1,
                     t1.percent_complete AS pct1, t1.is_closed AS clsd1,
                     t1.closure_comment AS com1, u1c.real_name AS clsdby1,
                     r1.item_name as res1,
                     t2.task_id AS id2, t2.prefix_id AS pxid2, p2.project_prefix AS ppx2, t2.item_summary AS sum2,
                     t2.percent_complete AS pct2, t2.is_closed AS clsd2,
                     t2.closure_comment AS com2, u2c.real_name AS clsdby2,
                     r2.item_name as res2
               FROM  {dependencies} AS d
               JOIN  {tasks} AS t1 ON d.task_id=t1.task_id
          LEFT JOIN  {users} AS u1c ON t1.closed_by=u1c.user_id
          LEFT JOIN  {projects} AS p1 ON t1.project_id = p1.project_id
          LEFT JOIN  {list_items} AS r1 ON t1.resolution_reason=r1.list_item_id
               JOIN  {tasks} AS t2 ON d.dep_task_id=t2.task_id
          LEFT JOIN  {users} AS u2c ON t2.closed_by=u2c.user_id
          LEFT JOIN  {projects} AS p2 ON t2.project_id = p2.project_id
          LEFT JOIN  {list_items} AS r2 ON t2.resolution_reason=r2.list_item_id
              WHERE  t1.project_id= ?
           ORDER BY  d.task_id, d.dep_task_id';

        $edges = $db->x->getAll($sql, null, $proj->id);

        $edge_list = array();
        $rvrs_list = array();
        $node_list = array();
        foreach ($edges as $row) {
            extract($row, EXTR_REFS);
            $edge_list[$id1][] = $id2;
            $rvrs_list[$id2][] = $id1;
            if (!isset($node_list[$id1])) {
                $node_list[$id1] =
              array('id'=>$id1, 'sum'=>$sum1, 'pct'=>$pct1, 'clsd'=>$clsd1, 'ppx' => $ppx1, 'pxid' => $pxid1,
                 'com'=>$com1, 'clsdby'=>$clsdby1, 'res'=>$res1);
            }
            if (!isset($node_list[$id2])) {
                $node_list[$id2] =
              array('id'=>$id2, 'sum'=>$sum2, 'pct'=>$pct2, 'clsd'=>$clsd2, 'ppx' => $ppx2, 'pxid' => $pxid2,
                'com'=>$com2, 'clsdby'=>$clsdby2, 'res'=>$res2);
            }
        }

        // Now we have our lists of nodes and edges, along with a helper
        // list of reverse edges. Time to do the graph coloring, so we know
        // which ones are in our particular connected graph. We'll set up a
        // list and fill it up as we visit nodes that are connected to our
        // main task.

        $connected  = array();
        $levelsdown = 0;
        $levelsup   = 0;
        function ConnectsTo($id, $down, $up, &$connected, &$edge_list, &$rvrs_list, &$levelsdown, &$levelsup, &$prunemode, &$node_list) {
            if (!isset($connected[$id])) { $connected[$id]=1; }
            if ($down > $levelsdown) { $levelsdown = $down; }
            if ($up   > $levelsup  ) { $levelsup   = $up  ; }
            
            $selfclosed = $node_list[$id]['clsd'];
            if (isset($edge_list[$id])) {
                foreach ($edge_list[$id] as $neighbor) {
                    $neighborclosed = $node_list[$neighbor]['clsd'];
                    if (!isset($connected[$neighbor]) &&
                            !($prunemode==1 && $selfclosed && $neighborclosed) &&
                            !($prunemode==2 && $neighborclosed)) {
                        ConnectsTo($neighbor, $down, $up+1, $connected, $edge_list, $rvrs_list, $levelsdown, $levelsup, $prunemode, $node_list);
                    }
                }
            }
            if (isset($rvrs_list[$id])) {
                foreach ($rvrs_list[$id] as $neighbor) {
                    $neighborclosed = $node_list[$neighbor]['clsd'];
                    if (!isset($connected[$neighbor]) &&
                            !($prunemode==1 && $selfclosed && $neighborclosed) &&
                            !($prunemode==2 && $neighborclosed)) {
                        ConnectsTo($neighbor, $down+1, $up, $connected, $edge_list, $rvrs_list, $levelsdown, $levelsup, $prunemode, $node_list);
                    }
                }
            }
        }

        ConnectsTo($id, 0, 0, $connected, $edge_list, $rvrs_list, $levelsdown, $levelsup, $prunemode, $node_list);
        $connected_nodes = array_keys($connected);
        sort($connected_nodes);
        
        // Now lets get rid of the extra junk in our arrays.
        // In prunemode 0, we know we're only going to have to get rid of
        // whole lists, and not elements in the lists, because if they were
        // in the list, they'd be connected, so we wouldn't be removing them.
        // In prunemode 1 or 2, we may have to remove stuff from the list, because
        // you can have an edge to a node that didn't end up connected.
        foreach (array("edge_list", "rvrs_list", "node_list") as $l) {
            foreach (${$l} as $n => $list) {
                if (!isset($connected[$n])) {
                    unset(${$l}[$n]);
                }
                if ($prunemode!=0 && $l!="node_list" && isset(${$l}[$n])) {
                    // Only keep entries that appear in the $connected_nodes list
                    ${$l}[$n] = array_intersect(${$l}[$n], $connected_nodes);
                }
            }
        }
        
        // Now we've got everything we need... let's draw the pretty pictures

        //Open the graph, and print global options
        $lj = 'n'; // label justification - l, r, or n (for center)
        $graphname = "task_${id}_dependencies";
        $dotgraph = "digraph $graphname {\n".
            "node [width=1.1, shape=ellipse, border=10, color=\"#00E11E\", style=\"filled\", ".
            "fontsize=10.0, pencolor=black, margin=\"0.1, 0.0\"];\n";
        // define the nodes
        foreach ($node_list as $n => $r) {
            $col = "";
            if ($r['clsd'] && $n!=$id) { $r['pct'] = 120; }
            // color code: shades of gray for % done
            $x = dechex(255-($r['pct']+10));
            $col = "#$x$x$x";
            // Make sure label terminates in \n!
            $label = $r['ppx'] . '#' . $r['pxid'] . " \n". (($useLocal) ? addslashes(utf8_substr($r['sum'], 0, 15)) . "\n" : '') .
                ($r['clsd'] ? L('closed') :
                 "$r[pct]% ".L('complete'));
            $tooltip =
                ($r['clsd'] ? L('closed') . ": $r[res]".
                    (!empty($r['clsdby']) ? " ($r[clsdby])" : '').
                    ($r['com']!='' ? ' - ' . str_replace(array("\r", "\n"), '', $r['com']) : '')
                    : $r['pct']);
            $dotgraph .= "FS$n [label=\"".str_replace("\n", "\\$lj", $label)."\", ".
                ($r['clsd'] ? 'color=black,' : '') .
                ($r['clsd'] ? 'fillcolor=white,' : "fillcolor=\"$col\",") .
                ($n == $id ? 'shape=box,' : '') .
                "href=\"javascript:top.window.location.href='".CreateURL(array("details", 'task' . $n))."'\", target=\"_top\" ".
                "tooltip=\"$tooltip\"];\n";
        }
        // Add edges
        foreach ($edge_list as $src => $dstlist) {
            foreach ($dstlist as $dst) {
                $dotgraph .= "FS$src -> FS$dst;\n";
            }
        }
        // all done
        $dotgraph .= "}\n";

        // All done with the graph. Save it to a temp file (new name if the data has changed)
        $dotfilename = sprintf('cache/fs_depends_dot_%d_%s.dot', $id, md5($dotgraph));
        $imgfilename = sprintf('%s/%s.%s', BASEDIR, $dotfilename, $fmt);
        $mapfilename = sprintf('%s/%s.%s', BASEDIR, $dotfilename, 'map');

        //cannot use tempnam( ) as file has to end with $ftm extension

        if(!$useLocal) {
            //cannot use tempnam() as file has to end with $ftm extension
            $tname = $dotfilename;
        } else {
            // we are operating on the command line, avoid races.
            $tname = tempnam(Flyspray::get_tmp_dir(), md5(uniqid(mt_rand() , true)));
        }
        //get our dot done..
        file_put_contents($tname, $dotgraph, LOCK_EX);

        // Now run dot on it, if target file does not already exist
        if (!is_file($imgfilename))
        {
            if (!$useLocal) {

                require_once 'Zend/Rest/Client.php';
                $client = new Zend_Rest_Client('http://webdot.flyspray.org/');
                $data = base64_decode($client->getGraph(base64_encode($dotgraph), $fmt)->post());

                file_put_contents($imgfilename, $data, LOCK_EX);
                
                $data = base64_decode($client->getGraph(base64_encode($dotgraph), 'cmapx')->post());

                file_put_contents($mapfilename, $data, LOCK_EX);
             
            } else {

                $tfn = escapeshellarg($tname);
                shell_exec(sprintf('%s -T %s -o %s %s', $path_to_dot, escapeshellarg($fmt), escapeshellarg($imgfilename), $tfn));
                $data['map'] = shell_exec(sprintf('%s -T cmapx %s', $path_to_dot, $tfn));
                file_put_contents($mapfilename, $data['map'], LOCK_EX);

                // Remove files so that they are not exposed to the public
                unlink($tname);
            }
        }

        $page->assign('map', file_get_contents($mapfilename));
        $page->assign('image', sprintf('%s%s.%s', $baseurl, $dotfilename, $fmt));


        // we have to find out the image size if it is SVG
        if ($fmt == 'svg') {
            if (!$remote) {
                $data = file_get_contents(BASEDIR . '/' . $file_name . '.' . $fmt);
            }
            preg_match('/<svg width="([0-9.]+)([a-zA-Z]+)" height="([0-9.]+)([a-zA-Z]+)"/', $data, $matches);
            $page->assign('width',  round($matches[1] * (($matches[2] == 'pt') ? 1.4 : (($matches[2] == 'in') ? 1.33 * 72.27 : 1)), 0));
            $page->assign('height', round($matches[3] * (($matches[4] == 'pt') ? 1.4 : (($matches[4] == 'in') ? 1.35 * 72.27 : 1)), 0));
        }

        /*
        [TC] We cannot have this stuff outputting here, so I put it in a quick template
        */
        $page->assign('taskid', $id);
        $page->assign('fmt', $fmt);
        $page->assign('graphname', $graphname);

        $endtime = microtime();
        list($startusec, $startsec) = explode(' ', $starttime);
        list($endusec, $endsec) = explode(' ', $endtime);
        $diff = ($endsec - $startsec) + ($endusec - $startusec);
        $page->assign('time', round($diff, 2));

        $page->setTitle($this->task['project_prefix'] . '#' . $this->task['prefix_id'] . ': ' . L('dependencygraph'));
        $page->pushTpl('depends.tpl');
    }
}

?>
