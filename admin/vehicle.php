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
    if ($_POST['type'] == "delete"){
        $edit_vehicle_id = intval($_POST['id']);
        $remove_vehicle = mysql_query("DELETE FROM vehicles WHERE id = '".$edit_vehicle_id."'");
            if(!$remove_vehicle) {
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

$sql_querry = "SELECT * FROM vehicles LEFT JOIN players ON vehicles.pid = players.playerid ";
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
    if (isset($_GET['search']) && (isset($_GET['searchtype'])))
    {
        //GET SECURE SEARCH DATA
        $search = mysql_real_escape_string($_GET['search']);
        $searchtype = mysql_real_escape_string($_GET['searchtype']);
        if ($searchtype == "name"){
            $sql_querry .= "WHERE classname LIKE '%$search%'"; 
            
        }
        elseif ($searchtype == "id"){
            $sql_querry .= "WHERE uid LIKE '%$search%'"; 
            
        }
        elseif ($searchtype == "playername"){
            $sql_querry .= "WHERE players.name LIKE '%$search%'"; 
            
        }
        else {
            echo "WRONG SEARCH TYPE, DONT PLAY WITH GET VARS";
            exit;
        }
        //RECREATE SEARCH URL
        $get_url["search"] = "search=".$search;
        $get_url["searchtype"] = "searchtype=".$searchtype;
                
    }
    //GET LETTER - WHERE name LIKE
    if (isset($_GET['letter'])){
        if ($_GET['letter'] == "special"){
            $sql_querry .= "WHERE name NOT RLIKE '^[A-Z]'";
        }
        else {
            $sql_querry .= "WHERE name LIKE '".mysql_real_escape_string($_GET['letter'])."%'";
        }
        
        //RECREATE SEARCH URL
        $get_url["letter"] = "letter=".$_GET['letter'];
    }
    //GET SORT - ORDER BY
    if (isset($_GET['sort']) && isset($_GET['type'])){
        $get_sort = mysql_real_escape_string($_GET['sort'])." ". mysql_real_escape_string($_GET['type']);
        $sql_querry .= "ORDER BY ". $get_sort; 
        $get_url["sort"] = "sort=".$_GET['sort'];
        $get_url["type"] = "type=".$_GET['type'];
    }
    else{
        $sql_querry .= "ORDER BY classname";
    }
    
    //RECREATE SEARCH URL
    
    

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

$vehicle_SQL = mysql_query($sql_querry) OR die("Error: $sql_querry <br>".mysql_error());
//DISPLAY HTML CONTENT
startHTML();
?>
   <div class="container" style="padding-top: 60px;">
            <div class="row">
                <ol class="breadcrumb">
                    <li><a href="index.php">Start</a></li>
                    <li class="active">Vehicle List</li>
                </ol>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading"><span class='glyphicon glyphicon-road'></span> Vehicle List </div>
                <div class="panel-body">
                    <p>Here you can view, edit or remove Vehicles from your Server</p>
                    <div class="row">
<!-- Simple Placeholder -->
                        <div class="col-lg-9">
                            
                        </div>
<!-- DISPLAY SEARCH BOX -->
                        <form action="vehicle.php" method="get">
                        <div class="col-lg-3" style="margin:20px 0;">
                            <div class="input-group" >
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Search" <?php if (isset($_GET['search'])){echo "value='".$_GET['search']."'";}?>>
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="submit">Go!</button>
                                    </span>
                                </div><!-- /input-group -->
                                <label class="radio-inline">
                                    <input type="radio" name="searchtype" value="name" checked> Vehicle Name
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="searchtype" value="id"> ID
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="searchtype" value="playername"> Player
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
                            <td class="text-center"><strong>#</strong> <a href="vehicle.php?sort=uid&type=ASC<?php if(isset($_GET['letter'])) {echo "&letter=".$_GET['letter'];}?><?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-attributes"></span></a><a href="vehicle.php?sort=uid&type=DESC<?php if(isset($_GET['letter'])) {echo "&letter=".$_GET['letter'];}?><?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-attributes-alt"></span></a></td>
                            <td class="text-center"><strong>Name</strong> <a href="vehicle.php?sort=name&type=ASC<?php if(isset($_GET['letter'])) {echo "&letter=".$_GET['letter'];}?><?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-alphabet"></span></a><a href="vehicle.php?sort=name&type=DESC<?php if(isset($_GET['letter'])) {echo "&letter=".$_GET['letter'];}?><?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-alphabet-alt"></span></a></td>
                            <td class="text-center"><strong>Type</strong> <a href="vehicle.php?sort=type&type=ASC<?php if(isset($_GET['letter'])) {echo "&letter=".$_GET['letter'];}?><?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-attributes"></span></a><a href="vehicle.php?sort=type&type=DESC<?php if(isset($_GET['letter'])) {echo "&letter=".$_GET['letter'];}?><?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-attributes-alt"></span></a></td>
                            <td class="text-center"><strong>Owner</strong> <a href="vehicle.php?sort=pid&type=ASC<?php if(isset($_GET['letter'])) {echo "&letter=".$_GET['letter'];}?><?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-attributes"></span></a><a href="vehicle.php?sort=pid&type=DESC<?php if(isset($_GET['letter'])) {echo "&letter=".$_GET['letter'];}?><?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-attributes-alt"></span></a></td>
                            <td class="text-center"><strong>Side</strong> <a href="vehicle.php?sort=side&type=ASC<?php if(isset($_GET['letter'])) {echo "&letter=".$_GET['letter'];}?><?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-attributes"></span></a><a href="vehicle.php?sort=side&type=DESC<?php if(isset($_GET['letter'])) {echo "&letter=".$_GET['letter'];}?><?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-attributes-alt"></span></a></td>
                            <td class="text-center"><strong>Alive</strong> <a href="vehicle.php?sort=alive&type=ASC<?php if(isset($_GET['letter'])) {echo "&letter=".$_GET['letter'];}?><?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-attributes"></span></a><a href="vehicle.php?sort=alive&type=DESC<?php if(isset($_GET['letter'])) {echo "&letter=".$_GET['letter'];}?><?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-attributes-alt"></span></a></td>
                            <td class="text-center"><strong>Active</strong> <a href="vehicle.php?sort=active&type=ASC<?php if(isset($_GET['letter'])) {echo "&letter=".$_GET['letter'];}?><?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-attributes"></span></a><a href="vehicle.php?sort=active&type=DESC<?php if(isset($_GET['letter'])) {echo "&letter=".$_GET['letter'];}?><?php if(isset($_GET['page'])) {echo "&page=".$_GET['page'];}?>" style="color:grey;"><span class="glyphicon glyphicon-sort-by-attributes-alt"></span></a></td>
                            <td class="text-center"><strong>Settings</strong></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        //CHECH IF QUERRYRESULT EMPTY OR FALSE AND IF SEARCH
                        if(mysql_num_rows($vehicle_SQL) < 1 && isset($_GET['search'])){
                            //DISPLAY INFORMATION THAT QUERRY FALSE OR EMPTY
                            echo "<tr><td colspan=9 class='text-center'><h2><span class='label label-info'>Empty Search Result</span></h2></td></tr> ";
                        }
                        elseif(mysql_num_rows($vehicle_SQL) < 1){
                            echo "<tr><td colspan=9 class='text-center'><h2><span class='label label-info'>No Results</span></h2></td></tr> ";
                        }
                        //NORMAL QUERRY FETCHING TO ROWS FOR TABLE
                        while($row = mysql_fetch_object($vehicle_SQL)){ ?>

                        <tr>
                            <td class="text-center"><?php echo $row->id;?></td>
                            <td><?php echo "<a href='vehicle_detail.php?id=".$row->id."'>".htmlspecialchars($row->classname)."</a>";?></td>
                            <td class="text-center"><?php echo $row->type;?></td>
                            <td class="text-left"><?php echo "<a href='player_detail.php?uid=".$row->uid."'>".htmlspecialchars($row->name)."</a>";?></td>
                            <td class="text-center"><?php echo $row->side;?></td>
                            <td class="text-center"><?php echo $row->alive;?></td>
                            <td class="text-center"><?php echo $row->active;?></td>
                            <td class="text-center"><a href="vehicle_detail.php?id=<?php echo $row->id;?>" class="btn btn-primary"><span class="glyphicon glyphicon-pencil"></span></a>
                            <a data-toggle="modal" href="vehicle.php#vehicle_delete_<?php echo $row->id;?>" class="btn btn-primary"><span class="glyphicon glyphicon-trash"></span></a></td>

                        </tr>


                    <!-- Modal Delete Vehicle -->
                <div class="modal fade" id="vehicle_delete_<?php echo $row->id;?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title"><span class="glyphicon glyphicon-pencil"></span> Delete <?php echo $row->classname;?></h4>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <form method="post" action="vehicle.php#vehicles_delete_<?php echo $row->id;?>" role="form"> 
                                            <input type="hidden" name="type" value="delete" />
                                            <input type="hidden" name="id" value="<?php echo $row->id;?>" />
                                            <p>Do you realy want to delete the Vehicle "<?php echo $row->classname;?>" from the User <?php echo $row->name;?>?</p>                                    
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-default" data-dismiss="modal" type="reset">Cancel</button>
                                    <button class="btn btn-primary" type="submit">Delete Vehicle</button>
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
       
