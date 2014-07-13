<?php
session_start ();
require_once "../config.php";
// DEBUG CHECK
if ($debug=="1"){
error_reporting(E_ALL); 
ini_set('display_errors', 1);
}
require_once PROJECT_PATH."/lang/de.php";
require_once PROJECT_PATH."/include/db.php";
require_once PROJECT_PATH."/admin/include/function_html_basic_admin.php";
loggedin();
//START RELOAD AREA
if (!empty($_POST)){
    $error_user = array();
    // REMOVE USER
    if ($_POST['position'] == "delete"){
        $edit_house_id = intval($_POST['id']);
        $remove_house = mysql_query("DELETE FROM houses WHERE id = '".$edit_house_id."'");
            if(!$remove_house) {
                echo "Error: ".mysql_error()."<br>"; 
                exit();        
            }
    }
}
//END RELOAD AREA

//PAGGINATION
$page = 1;

//GET NUMBERS OF RESULTS
$start = $page * $setting_rows - $setting_rows;

// NEW QUERRY

$sql_querry = "SELECT * FROM houses LEFT JOIN players ON houses.pid = players.playerid ";
$get_rows = $sql_querry;
$get_url = array();
$get_url_string = "";
if(!empty($_GET))
{
    //GET NEW PAGE
    if (isset($_GET['page'])){
        $page = intval($_GET['page']);
    }
    $start = $page * $setting_rows - $setting_rows;
    //GET SEARCH - LIKE
    if (isset($_GET['search']) && (isset($_GET['searchposition'])))
    {
        //GET SECURE SEARCH DATA
        $search = mysql_real_escape_string($_GET['search']);
        $searchposition = mysql_real_escape_string($_GET['searchposition']);
        if ($searchposition == "id"){
            $sql_querry .= "WHERE id LIKE '%$search%'"; 
            
        }
        elseif ($searchposition == "playerowner"){
            $sql_querry .= "WHERE players.owner LIKE '%$search%'"; 
            
        }
        else {
            echo "WRONG SEARCH TYPE, DONT PLAY WITH GET VARS";
            exit;
        }
        //RECREATE SEARCH URL
        $get_url["search"] = "search=".$search;
        $get_url["searchposition"] = "searchposition=".$searchposition;
                
    }
    //GET SORT - ORDER BY
    if (isset($_GET['sort']) && isset($_GET['type'])){
        $get_sort = mysql_real_escape_string($_GET['sort'])." ". mysql_real_escape_string($_GET['type']);
        $sql_querry .= "ORDER BY ". $get_sort; 
        $get_url["sort"] = "sort=".$_GET['sort'];
        $get_url["type"] = "type=".$_GET['type'];
    }
    else{
        $sql_querry .= "ORDER BY id";
    }
    
    //SET GET ROWS WITHOUT LIMIT
    $get_rows = $sql_querry;
    
    
    // GET PAGINATION - SET LIMIT
    $sql_querry .= " LIMIT ".$start.",".$setting_rows;
    
    foreach($get_url as $value){
        
        $get_url_string .= "&".$value;
        
    }
   
}
else{
    //IF !isset GET set LIMIT for Pagination
    $sql_querry .= " LIMIT ".$start.",".$setting_rows;
}

$house_SQL = mysql_query($sql_querry) OR die("Error: $sql_querry <br>".mysql_error());
//DISPLAY HTML CONTENT
startHTML();
?>
   <div class="container" style="padding-top: 60px;">
            <div class="row">
                <ol class="breadcrumb">
                    <li><a href="index.php">Start</a></li>
                    <li class="active">House List</li>
                </ol>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading"><span class='glyphicon glyphicon-home'></span> Houses List </div>
                <div class="panel-body">
                    <p>Here you can view, edit or remove Houses from your Server</p>
                    <div class="row">
<!-- Simple Placeholder -->
                        <div class="col-lg-9">
                            
                        </div>
<!-- DISPLAY SEARCH BOX -->
                        <form action="house.php" method="get">
                        <div class="col-lg-3" style="margin:20px 0;">
                            <div class="input-group" >
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Search" <?php if (isset($_GET['search'])){echo "value='".$_GET['search']."'";}?>>
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="submit">Go!</button>
                                    </span>
                                </div><!-- /input-group -->
                                <label class="radio-inline">
                                    <input type="radio" name="searchposition" value="id"> ID
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="searchposition" value="playerowner"> Player
                                </label>
                            </div><!-- /.col-lg-6 -->
                        </div>
                        </form>
                    </div>
<!-- DISPLAY Pagination AREA -->
                    <div class="row">
                        <div class="col-lg-12">
                            <ul class="pagination pagination-sm">
                                <?php
                                $get_rows_querry = mysql_query($get_rows);
                                $number_rows = mysql_num_rows($get_rows_querry); 
                                $number_pages = $number_rows / $setting_rows; 
                                                               
                                if($page == 1)
                                {
                                    echo "<li class='disabled'><a>&laquo; Prev</a></li>";
                                    echo "<li class='active'><a href='?page=1".$get_url_string."'>1</a></li>";
                                }
                                else
                                {
                                    echo "<li><a href='?page=".($page-1).$get_url_string."'>&laquo; Prev</a></li>";
                                    echo "<li><a href='?page=1".$get_url_string."'>1</a></li>";
                                }


                                for($a=($page-5); $a < ($page+5); $a++)
                                { 
                                    $b = $a + 1; 
                                    //IF AT PAGE

                                    if(($page == $b) && ($b < $number_pages) && ($b >1)){
                                        echo "<li class='active'><a href='?page=".$b.$get_url_string."'>".$b."</a></li>"; 
                                    } 
                                    else { 
                                        if(($b > 1) && ($b < $number_pages) )

                                        echo "<li><a href='?page=".$b.$get_url_string."'>".$b."</a></li> "; 
                                    } 
                                }

                                if($page >= $number_pages)
                                {
                                    if($page == 1){
                                        echo "<li class='disabled'><a>Next &raquo;</a></li>";
                                    }
                                    else{
                                        echo "<li class='active'><a href='?page=".ceil($number_pages).$get_url_string."'>".ceil($number_pages)."</a></li>";
                                        echo "<li class='disabled'><a>Next &raquo;</a></li>";
                                    }
                                }
                                else
                                {
                                    echo "<li><a href='?page=".ceil($number_pages).$get_url_string."'>".ceil($number_pages)."</a></li>";
                                    echo "<li><a href='?page=".($page+1).$get_url_string."'>Next &raquo;</a></li>";
                                }

                                ?>

                            </ul>
                        </div>
                </div>
            <div class="table-responsive">
                <table class="table table-hover table-striped ">
                    <thead>
                        <tr>
                            <td class="text-center"><strong>#</strong> <a href="house.php?sort=id&type=ASC<?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-attributes"></span></a><a href="house.php?sort=id&type=DESC<?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-attributes-alt"></span></a></td>
                            <td class="text-center"><strong>Owner</strong> <a href="house.php?sort=name&type=ASC<?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-alphabet"></span></a><a href="house.php?sort=name&type=DESC<?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-alphabet-alt"></span></a></td>
                            <td class="text-center"><strong>Position</strong> <a href="house.php?sort=pos&type=ASC<?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-attributes"></span></a><a href="house.php?sort=pos&type=DESC<?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-attributes-alt"></span></a></td>
                            <td class="text-center"><strong>Inventory</strong> <a href="house.php?sort=inventory&type=ASC<?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-attributes"></span></a><a href="house.php?sort=inventory&type=DESC<?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-attributes-alt"></span></a></td>
                            <td class="text-center"><strong>Containers</strong> <a href="house.php?sort=containers&type=ASC<?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-attributes"></span></a><a href="house.php?sort=containers&type=DESC<?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-attributes-alt"></span></a></td>
                            <td class="text-center"><strong>Owned</strong> <a href="house.php?sort=owned&type=ASC<?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-attributes"></span></a><a href="house.php?sort=owned&type=DESC<?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-attributes-alt"></span></a></td>
                            <td class="text-center"><strong>Settings</strong></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        //CHECH IF QUERRYRESULT EMPTY OR FALSE AND IF SEARCH
                        if(mysql_num_rows($house_SQL) < 1 && isset($_GET['search'])){
                            //DISPLAY INFORMATION THAT QUERRY FALSE OR EMPTY
                            echo "<tr><td colspan=9 class='text-center'><h2><span class='label label-info'>Empty Search Result</span></h2></td></tr> ";
                        }
                        elseif(mysql_num_rows($house_SQL) < 1){
                            echo "<tr><td colspan=9 class='text-center'><h2><span class='label label-info'>No Results</span></h2></td></tr> ";
                        }
                        //NORMAL QUERRY FETCHING TO ROWS FOR TABLE
                        while($row = mysql_fetch_object($house_SQL)){ ?>

                        <tr>
                            <td><?php echo "<a href='house_detail.php?id=".$row->id."'>".htmlspecialchars($row->id)."</a>";?></td>
                            <td class="text-left"><?php echo "<a href='player_detail.php?id=".$row->id."'>".htmlspecialchars($row->name)."</a>";?></td>
                            <td class="text-center" style="font-size: small;"><?php echo $row->pos;?></td>
                            <td class="text-center" style="word-wrap: break-word; font-size: x-small;"><?php echo $row->inventory;?></td>
                            <td class="text-center" style="word-wrap: break-word; font-size: x-small;"><?php echo $row->containers;?></td>
                            <td class="text-center"><?php echo $row->owned;?></td>
                            <td class="text-center"><a href="house_detail.php?id=<?php echo $row->id;?>" class="btn btn-primary"><span class="glyphicon glyphicon-pencil"></span></a>
                            <a data-toggle="modal" href="house.php#house_delete_<?php echo $row->id;?>" class="btn btn-primary"><span class="glyphicon glyphicon-trash"></span></a></td>

                        </tr>


                    <!-- Modal Delete Vehicle -->
                <div class="modal fade" id="house_delete_<?php echo $row->id;?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button position="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title"><span class="glyphicon glyphicon-pencil"></span> Delete House with ID <?php echo $row->id;?></h4>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <form method="post" action="house.php#houses_delete_<?php echo $row->id;?>" role="form"> 
                                            <input position="hidden" owner="position" value="delete" />
                                            <input position="hidden" owner="id" value="<?php echo $row->id;?>" />
                                            <p>Do you realy want to delete the House "<?php echo $row->id;?>" from the User <?php echo $row->name;?>?</p>                                    
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-default" data-dismiss="modal" position="reset">Cancel</button>
                                    <button class="btn btn-primary" position="submit">Delete Vehicle</button>
                                </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </tbody>
            </table>
            </div>
   </div>
</div>
       </div>

<?php
closeHTML();
?>
       
