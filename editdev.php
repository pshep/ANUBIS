<?php
require("config.inc.php");
require("func.inc.php");

$dbh = anubis_db_connect();
$config = get_config_data();

if (!isset($id))
  $id = 0 + $_GET['id'];
if (!$id || $id == 0) 
{
    echo "Need a Host to deal with !";
    die;
}

if (isset($_GET['dev']))
  $dev = 0 + $_GET['dev'];
else
{
    echo "Need a device number to deal with !";
    die;
}

if (isset($_GET['type']))
  $type = $_GET['type'];
else
{
    echo "Need a device type to deal with !";
    die;
}


if($host_data = get_host_data($id))
{
  if($host_alive = get_host_status($host_data))
  {
    /* Determine if we can change values on this host */
    $privileged = (get_privileged_status($host_data) && ($type != 'CPU'));

    if ($privileged)
    {
      /* Process POST data - send any changes to host */
      $value_changed = false;

      if ($type == 'ASC')
      {
        if (isset($_POST['start']))
        {
          $arr = array ('command'=>'ascenable','parameter'=>$dev);
          $dev_response = send_request_to_host($arr, $host_data);
          $value_changed = true;
        }
  
        if (isset($_POST['stop']))
        {
          $arr = array ('command'=>'ascdisable','parameter'=>$dev);
          $dev_response = send_request_to_host($arr, $host_data);
          $value_changed = true;
        }
      }

      if ($type == 'PGA')
      {
        if (isset($_POST['start']))
        {
          $arr = array ('command'=>'pgaenable','parameter'=>$dev);
          $dev_response = send_request_to_host($arr, $host_data);
          $value_changed = true;
        }
  
        if (isset($_POST['stop']))
        {
          $arr = array ('command'=>'pgadisable','parameter'=>$dev);
          $dev_response = send_request_to_host($arr, $host_data);
          $value_changed = true;
        }
      }

      if ($type == 'GPU')
      {
        if (isset($_POST['start']))
        {
          $arr = array ('command'=>'gpuenable','parameter'=>$dev);
          $dev_response = send_request_to_host($arr, $host_data);
          $value_changed = true;
        }
  
        if (isset($_POST['stop']))
        {
          $arr = array ('command'=>'gpudisable','parameter'=>$dev);
          $dev_response = send_request_to_host($arr, $host_data);
          $value_changed = true;
        }
      
        if (isset($_POST['restart']))
        {
          $arr = array ('command'=>'gpurestart','parameter'=>$dev);
          $dev_response = send_request_to_host($arr, $host_data);
          $value_changed = true;
        }
  
        if(isset($_POST['apply']))
        {
          if(isset($_POST['gpuclk_chk']))
          {
            $arr = array ('command'=>'gpuengine','parameter'=>$dev.','.$_POST['gpuclk_dro']);
            $gpu_response[0] = send_request_to_host($arr, $host_data);
            $value_changed = true;
          }
          
          if(isset($_POST['memclk_chk']))
          {
            $arr = array ('command'=>'gpumem','parameter'=>$dev.','.$_POST['memclk_dro']);
            $gpu_response[1] = send_request_to_host($arr, $host_data);
            $value_changed = true;
          }
          
          if(isset($_POST['gpuvolt_chk']))
          {
            $arr = array ('command'=>'gpuvddc','parameter'=>$dev.','.$_POST['gpuvolt_dro']);
            $gpu_response[2] = send_request_to_host($arr, $host_data);
            $value_changed = true;
          }
          
          if(isset($_POST['gpufan_chk']))
          {
            $arr = array ('command'=>'gpufan','parameter'=>$dev.','.$_POST['gpufan_dro']);
            $gpu_response[3] = send_request_to_host($arr, $host_data);
            $value_changed = true;
          }
      
          if(isset($_POST['intensity_chk']))
          {
            $arr = array ('command'=>'gpuintensity','parameter'=>$dev.','.$_POST['intensity_dro']);
            $gpu_response[4] = send_request_to_host($arr, $host_data);
            $value_changed = true;
          }
        }
      }
      /* wait a couple of seconds if a change occured */
      if ($value_changed)
        sleep(2);
    }
    $gpu_data_array = get_dev_data($host_data, $dev, $type);
  }
}



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Anubis - a cgminer web frontend</title>

<link href="templatemo_style.css" rel="stylesheet" type="text/css" />
<link type="text/css" href="css/ui-lightness/jquery-ui.custom.css" rel="Stylesheet" />
<script type="text/javascript" src="scripts/jquery.min.js"></script>
<script type="text/javascript" src="scripts/jquery-ui.custom.min.js"></script>
<link rel="stylesheet" type="text/css" href="css/ddsmoothmenu.css" />
<script type="text/javascript" src="scripts/ddsmoothmenu.js">

/***********************************************
* Smooth Navigational Menu- (c) Dynamic Drive DHTML code library (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
***********************************************/

</script>


<script type="text/javascript">

ddsmoothmenu.init({
    mainmenuid: "templatemo_menu", //menu DIV id
    orientation: 'h', //Horizontal or vertical menu: Set to "h" or "v"
    classname: 'ddsmoothmenu', //class added to menu's outer DIV
    //customtheme: ["#1c5a80", "#18374a"],
    contentsource: "markup" //"markup" or ["container_id", "path_to_menu_file"]
})

$(function()
{
  $( "#gpuclk_slider" ).slider({
    value: <?php echo $gpu_data_array['GPU Clock']; ?>,
    min: 100,
    max: 1500,
    step: 5,
    slide: function( event, ui ) 
    {
      $( "#gpuclk_dro" ).val(ui.value );
      $( "#gpuclk_chk" ).each(function(){ this.checked = true; });
    },
  });
  $( "#gpuclk_dro" ).val($( "#gpuclk_slider" ).slider( "value" ) );
  
  $( "#memclk_slider" ).slider({
    value: <?php echo $gpu_data_array['Memory Clock']; ?>,
    min: 100,
    max: 1500,
    step: 5,
    slide: function( event, ui ) 
    {
      $( "#memclk_dro" ).val(ui.value );
      $( "#memclk_chk" ).each(function(){ this.checked = true; });
    },
  });
  $( "#memclk_dro" ).val($( "#memclk_slider" ).slider( "value" ) );
  
  $( "#gpuvolt_slider" ).slider({
    value: <?php echo $gpu_data_array['GPU Voltage']; ?>,
    min: 0.5,
    max: 1.5,
    step: 0.01,
    slide: function( event, ui ) 
    {
      $( "#gpuvolt_dro" ).val(ui.value );
      $( "#gpuvolt_chk" ).each(function(){ this.checked = true; });
    },
  });
  $( "#gpuvolt_dro" ).val($( "#gpuvolt_slider" ).slider( "value" ) );
  
  $( "#gpufan_slider" ).slider({
    value: <?php echo $gpu_data_array['Fan Percent']; ?>,
    min: 0,
    max: 100,
    step: 1,
    slide: function( event, ui ) 
    {
      $( "#gpufan_dro" ).val(ui.value );
      $( "#gpufan_chk" ).each(function(){ this.checked = true; });
    },
  });
  $( "#gpufan_dro" ).val($( "#gpufan_slider" ).slider( "value" ) );
  
  <?php
  $intensity = ($gpu_data_array['Intensity'] == 'D') ? -1 : $gpu_data_array['Intensity'];
  ?>
  
  $( "#intensity_slider" ).slider({
    value: <?php echo $intensity; ?>,
    min: -1,
    max: 20,
    step: 1,
    slide: function( event, ui )
    {
      $( "#intensity_dro" ).val(ui.value );
      $( "#intensity_chk" ).each(function(){ this.checked = true; });
      if ($( "#intensity_dro" ).val() == -1){$( "#intensity_dro" ).val("D");}
    }
  });
  $( "#intensity_dro" ).val($( "#intensity_slider" ).slider( "value" ) );
  if ($( "#intensity_dro" ).val() == -1){$( "#intensity_dro" ).val("D");}
});
    </script>



</head>
<body>

<div id="templatemo_wrapper">

<?php include ('header.inc.php'); ?>
    
    <div id="templatemo_main">
        <div class="col_fw">
            <div class="templatemo_megacontent">
                <h2>Device detail</h2>
                <div class="cleaner h20"></div>
<?php

if ($host_data)
{
  echo "<table id='rounded-corner' summary='HostSummary' align='center'>";
  echo create_host_header();
  echo get_host_summary($host_data);
  echo "</table>";
  if ($host_alive)
  {
    echo "<form name='control' action='editdev.php?id=".$id."&dev=".$dev."&type=".$type."' method='post'>";
    echo "<table id='rounded-corner' summary='DevsSummary' align='center'>";
    echo create_devs_header();
    echo process_dev_disp($gpu_data_array, $privileged);

    if (isset($dev_response))
    {
      if ($dev_response['STATUS'][0]['STATUS'] == 'S')
        $dev_message = "Action successful: ";
      else if ($dev_response['STATUS'][0]['STATUS'] == 'I')
         $dev_message = "Action info: ";
      else if ($dev_response['STATUS'][0]['STATUS'] == 'W')
         $dev_message = "Action warning: ";
      else
         $dev_message = "Action error: ";

      echo "<thead><tr>
              <th colspan='16'  scope='col' class='rounded-company'>"
                . $dev_message . $dev_response['STATUS'][0]['Msg'].
             "</th>
            </tr></thead>";
    }
    echo "</table>";
    echo "</form>";
  }
  
  if ($privileged && ($type == 'GPU'))
  {
?>
<form name='apply' action='editdev.php?id=<?php echo $id; ?>&dev=<?php echo $dev; ?>&type=<?php echo $type; ?>' method='post'>
<table id='rounded-corner' summary='DevsControl' align='center'>
<thead>
    <tr>
      <th width='20' scope='col' class='rounded-q1'>Set</th>
      <th colspan='3' scope='col' class='rounded-q1'> Edit settings below for <?php echo $type; ?> <?php echo $dev; ?> on <?php echo $host_data['name']; ?></th>
    </tr>
</thead>
<tr>
  <td width='20' rowspan="2"><input type="checkbox" name="gpuclk_chk"  id="gpuclk_chk" value="1"/></td>
  <td width='20'>100</td>
  <td align='center'>Set GPU Clock Speed: <input type="text" name="gpuclk_dro"  id="gpuclk_dro" style="border:0; font-weight:bold;" size="3" /> MHz</td>
  <td width='20'>1500</td>
</tr>
<tr>
  <td colspan='3'><div id="gpuclk_slider"></div></td>
</tr>
<tr>
  <td width='20' rowspan="2"><input type="checkbox" name="memclk_chk"  id="memclk_chk" value="1"/></td>
  <td>100</td>
  <td align='center'>Set Memory Clock Speed: <input type="text" name="memclk_dro" id="memclk_dro" style="border:0; font-weight:bold;" size="3" /> MHz</td>
  <td>1500</td>
</tr>
<tr>
  <td colspan='3'><div id="memclk_slider"></div></td>
</tr>
<tr>
  <td width='20' rowspan="2"><input type="checkbox" name="gpuvolt_chk"  id="gpuvolt_chk" value="1"/></td>
  <td>0.50</td>
  <td align='center'>Set GPU Voltage: <input type="text" name="gpuvolt_dro" id="gpuvolt_dro" style="border:0;  font-weight:bold;" size="3" /> V</td>
  <td>1.50</td>
</tr>
<tr>
  <td colspan='3'><div id="gpuvolt_slider"></div></td>
</tr>
<tr>
  <td width='20' rowspan="2"><input type="checkbox" name="gpufan_chk"  id="gpufan_chk" value="1"/></td>
  <td>0</td>
  <td align='center'>Set Fan Speed: <input type="text" name="gpufan_dro" id="gpufan_dro" style="border:0; font-weight:bold;" size="3" /> %</td>
  <td>100</td>
</tr>
<tr>
  <td colspan='3'><div id="gpufan_slider"></div></td>
</tr>
<tr>
  <td width='20' rowspan="2"><input type="checkbox" name="intensity_chk"  id="intensity_chk" value="1"/></td>
  <td>D</td>
  <td align='center'>Set Intensity: <input type="text" name="intensity_dro" id="intensity_dro" style="border:0; font-weight:bold;" size="3" /></td>
  <td>20</td>
</tr>
<tr>
  <td colspan='3'><div id="intensity_slider"></div></td>
</tr>
<thead>
  <tr>
    <th colspan='4' scope='col' class='rounded-q1'>
        <input type='submit' value='Apply Settings' name='apply'><br>
    </th>
  </tr>
<?php
    if (isset($_POST['apply']))
    {
      for ($i=0; $i<5; $i++)
      {
        if (isset($gpu_response[$i]))
        {
          if ($gpu_response[$i]['STATUS'][0]['STATUS'] == 'S')
            $dev_message = "Action successful: ";
          else if ($gpu_response[$i]['STATUS'][0]['STATUS'] == 'I')
             $dev_message = "Action info: ";
          else if ($gpu_response[$i]['STATUS'][0]['STATUS'] == 'W')
             $dev_message = "Action warning: ";
          else
             $dev_message = "Action error: ";

          echo "<tr><th colspan='4'>"
                    . $dev_message . $gpu_response[$i]['STATUS'][0]['Msg'] .
                 "</th><tr>";
        }
      }
    }

?>
</thead>
</table>
</form>
<?php
  }
}
else 
{
  echo "Host not found or you just deleted the host !<BR>";
}
?>
                <div class="cleaner h20"></div>
<!--                 <a href="#" class="more float_r"></a> -->
            </div>

            <div class="cleaner"></div>
        </div>

        <div class="cleaner"></div>
        </div>
    </div>
    
    <div class="cleaner"></div>

<div id="templatemo_footer_wrapper">
    <div id="templatemo_footer">
        <?php include("footer.inc.php"); ?>
        <div class="cleaner"></div>
    </div>
</div> 
  
</body>
</html>
