<?php

// Check if the user is an admin

require("lang/$lang/newtask.php");

if ($_SESSION['can_open_jobs'] == "1" OR $flyspray_prefs['anon_open'] == "1") {
?>

<h3><?php echo $newtask_text['createnewtask'];?></h3>

<div id="taskdetails">

<form name="form1" action="index.php" method="post">
<table>
<colgroup><col width="25%" span="4"></colgroup>
  <tr>
    <td>
      <input type="hidden" name="do" value="modify">
      <input type="hidden" name="action" value="newtask">
      <input type="hidden" name="project_id" value="<?php echo $project_id;?>">
      <label for="itemsummary"><?php echo $newtask_text['summary'];?></label>
    </td>
    <td colspan="3">
      <input id="itemsummary" type="text" name="item_summary" size="50" maxlength="100">
    </td>
  </tr>
  <tr>
    <td><label for="tasktype"><?php echo $newtask_text['tasktype'];?></label></td>
    <td><select name="task_type" id="tasktype">
        <?php
        // Get list of task types
        $get_severity = $fs->dbQuery("SELECT tasktype_id, tasktype_name FROM flyspray_list_tasktype WHERE show_in_list = ? ORDER BY list_position", array('1'));
        while ($row = $fs->dbFetchArray($get_severity)) {
            echo "<option value=\"{$row['tasktype_id']}\">{$row['tasktype_name']}</option>";
        };
        ?>
        </select>
     </td>
     <td><label for="taskseverity"><?php echo $newtask_text['severity'];?></label></td>
     <td><select id="taskseverity" class="adminlist" name="task_severity">
        <?php
        // Get list of severities
      require("lang/$lang/severity.php");
      foreach($severity_list as $key => $val) {
          echo "<option value=\"$key\">$val</option>\n";
      };
        ?>
        </select>
      </td>
   </tr>
   <tr>
     <td><label for="productcategory"><?php echo $newtask_text['category'];?></label></td>
     <td>
       <select class="adminlist" name="product_category" id="productcategory">
        <?php
        // Get list of categories
        $get_categories = $fs->dbQuery("SELECT category_id, category_name FROM flyspray_list_category WHERE project_id = ? AND show_in_list = ? ORDER BY list_position",
			array($project_id, '1'));
        while ($row = $fs->dbFetchArray($get_categories)) {
          echo "<option value=\"{$row['category_id']}\">{$row['category_name']}</option>";
        };
        ?></select>
     </td>
     <td><label for="productversion"><?php echo $newtask_text['reportedversion'];?></label></td>
     <td>
       <select class="adminlist" name="product_version" id="productversion">
        <?php
        // Get list of versions
        $get_version = $fs->dbQuery("SELECT version_id, version_name FROM flyspray_list_version WHERE project_id = ? AND show_in_list = ? ORDER BY list_position",
				    array($project_id, '1'));
        while ($row = $fs->dbFetchArray($get_version)) {
          echo "<option value=\"{$row['version_id']}\">{$row['version_name']}</option>\n";
        };
        ?>
       </select>
     </td>
   </tr>
   <tr>
     <td><label for="itemstatus"><?php echo $newtask_text['status'];?></label></td>
     <td>
        <select id="itemstatus" name="item_status" <?php if ($_SESSION['can_modify_jobs'] != "1") { echo " disabled=\"disabled\"";};?>>
        <?php
        // Get list of statuses
        require("lang/$lang/status.php");
        foreach($status_list as $key => $val) {
          echo "<option value=\"$key\">$val</option>\n";
        };
        ?>
        </select>
     </td>
     <td><label for="closedbyversion"><?php echo $newtask_text['dueinversion'];?></label></td>
     <td><select id="closedbyversion" name="closedby_version" <?php if ($_SESSION['can_modify_jobs'] != "1") { echo " disabled=\"disabled\"";};?>>
        <?php
        echo "<option value=\"\">Undecided</option>\n";

        $get_version = $fs->dbQuery("SELECT version_id, version_name FROM flyspray_list_version WHERE project_id = ? AND show_in_list = ? ORDER BY list_position",
				    array($project_id, '1'));
        while ($row = $fs->dbFetchArray($get_version)) {
          echo "<option value=\"{$row['version_id']}\">{$row['version_name']}</option>\n";
        };
        ?>
        </select>
     </td>
   </tr>
   <tr>
     <td>
      <?php
      // If the user can't modify jobs, we will have to set a hidden field for the status
      if ($_SESSION['can_modify_jobs'] != "1") {
        echo "<input type=\"hidden\" name=\"item_status\" value=\"1\">";
      };
      ?>
        <label for="assignedto"><?php echo $newtask_text['assignedto'];?></label></td>
     <td>
        <select id="assignedto" name="assigned_to" <?php if ($_SESSION['can_modify_jobs'] != "1") { echo " disabled=\"disabled\"";};?>>
        <?php
        // Get list of users
        echo "<option value=\"0\">{$newtask_text['noone']}</option>\n";

        $fs->ListUsers();
        ?>
        </select>
     </td>
     <td colspan="2"></td>
   </tr>
   <tr>
     <td><label for="operatingsystem"><?php echo $newtask_text['operatingsystem'];?></label></td>
     <td><select id="operatingsystem" name="operating_system">
        <?php
        // Get list of operating systems
        $get_os = $fs->dbQuery("SELECT os_id, os_name FROM flyspray_list_os WHERE project_id = ? AND show_in_list = ? ORDER BY list_position",
				array($project_id, '1'));
        while ($row = $fs->dbFetchArray($get_os)) {
          echo "<option value=\"{$row['os_id']}\">{$row['os_name']}</option>";
        };
        ?>
        </select>
     </td>
     <td colspan="2"></td>
   </tr>
   <tr>
     <td><label for="detaileddesc"><?php echo $newtask_text['details'];?></label></td>
     <td colspan="3">
       <textarea id="detaileddesc" class="admintext" name="detailed_desc" cols="60" rows="10"></textarea>
     </td>
  </tr>
  <tr>
    <?php
    if ($_SESSION['userid'] != '') {
      echo "<td colspan=\"3\"><label>";
      echo "{$newtask_text['notifyme']}&nbsp;&nbsp;<input class=\"admintext\" type=\"checkbox\" name=\"notifyme\" value=\"1\"></label>";
      echo "</td>";
    };
    ?>
    <td colspan="3">
    <input class="adminbutton" type="submit" name="buSubmit" value="<?php echo $newtask_text['addthistask'];?>" onclick="Disable1()">
    </td>
  </tr>
</table>
</form>
</div>

<?php
// ####################################################################################
// ####################################################################################
// If the user isn't an admin
} else {

echo $newtask_text['nopermission'];

// End of checking admin status
};
?>