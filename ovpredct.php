<?php
/*
Plugin Name: Ovulation Predictor
Plugin URI: http://calendarscripts.info/ovulation-predictor-wordpress-plugin.html
Description: This plugin displays functional ovulation and due date predictor. It can be used from women to check their future fertile time and due date.
Author: CalendarScripts
Version: 1.2
Author URI: http://calendarscripts.info
*/ 

/*  Copyright 2008  CalendarScripts (email : info@calendarscripts.info)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


function ovpredct_add_page()
{
	add_submenu_page('plugins.php', 'Ovulation Predictor', 'Ovulation Predictor', 8, __FILE__, 'ovpredct_options');
}

// ovpredct_options() displays the page content for the Ovpredct Options submenu
function ovpredct_options($widget_mode=false) 
{
    // Read in existing option value from database
    $ovpredct_table = stripslashes( get_option( 'ovpredct_table' ) );
    
    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( $_POST[ 'ocalc_update' ] == 'Y' ) 
    {
        // Read their posted value
        $ovpredct_table = $_POST[ 'ovpredct_table' ];
        

        // Save the posted value in the database
        update_option( 'ovpredct_table', $ovpredct_table );
        
        // Put an options updated message on the screen
		?>
		<div class="updated"><p><strong><?php _e('Options saved.', 'ovpredct_domain' ); ?></strong></p></div>
		<?php		
	 }
		
		 // Now display the options editing screen
		    echo '<div class="wrap">';		
		    // header
		    echo "<h2>" . __( 'Ovulation Predictor Options', 'ovpredct_domain' ) . "</h2>";		
		    // options form		    
		    ?>
		
        <?php if(!$widget_mode):?>
		<form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <?php endif;?>    
		<input type="hidden" name="ocalc_update" value="Y">
		
		<p><?php _e("<p>You can use this calculator in two ways: as a standard Wordpress widget or by placing it in your post or page. For the latter please include the tag <b>[ovulation-predictor]</b> in the content of your page or post and the calculator will appear there.</p>
        <p>These options are accessible both from the \"Ovulation Predictor\" page under your Plugins menu or from your Widgets section.</p>
        <p>Check out some more of our <a href='http://calendarscripts.info/free-calculators.html' target='_blank'>free calculators</a>.</p>
        <p>CSS class definition for the predictor wrapper div &lt;div&gt;:</p>", 'ovpredct_domain' ); ?> 
		<textarea name="ovpredct_table" rows='5' cols='70'><?php echo stripslashes ($ovpredct_table); ?></textarea>
		</p><hr />
		
        <?php if(!$widget_mode):?>
    		<p class="submit">
    		<input type="submit" name="Submit" value="<?php _e('Update Options', 'ovpredct_domain' ) ?>" />
    		</p>
    		
    		</form>
        <?php endif;?>
		</div>
		<?php
}

function ovpredct_datechooser($name,$value="")
{
	$months=array('','January','February','March','April','May','June','July','August',
	'September','October','November','December');
	
	if(empty($value)) $value=date("Y-m-d");
	
	$parts=explode("-",$value);
	
	$day=$parts[2]+0;
	$month=$parts[1]+0;
	$year=$parts[0];
	
	$chooser="";
	
	$chooser.="<select name=".$name."month>";
	for($i=1;$i<=12;$i++)
	{
		if($i==$month) $selected='selected';
		else $selected='';
		$chooser.="<option $selected value=$i>$months[$i]</option>";
	}
	$chooser.="</select> / ";
	
	$chooser.="<select name=".$name."day>";
	for($i=1;$i<=31;$i++)
	{
		if($i==$day) $selected='selected';
		else $selected='';
		$chooser.="<option $selected>$i</option>";
	}
	$chooser.="</select> / ";
	
	$chooser.="<select name=".$name."year>";
	for($i=(date("Y")-1);$i<=2050;$i++)
	{
		if($i==$year) $selected='selected';
		else $selected='';
		$chooser.="<option $selected>$i</option>";
	}
	$chooser.="</select> ";	
	
	return $chooser;
}

function ovpredct_generate_html()
{
    //construct the calculator page	
	$ovcalc="<style type=\"text/css\">
	.ovpredct_table
	{
		".get_option('ovpredct_table')."
	}
	</style>\n\n";
	
	if(!empty($_POST['calculator_ok']))
	{
		//last cycle date
		$date="$_POST[dateyear]-$_POST[datemonth]-$_POST[dateday]";
		
		//convert to time
		$lasttime=mktime(0,0,0,$_POST[datemonth],$_POST[dateday],$_POST[dateyear]);
		
		//first fertile day
		$firstdaytime=$lasttime + $_POST[days]*24*3600 - 16*24*3600;
		$firstday=date("F d, Y",$firstdaytime);
		
		//last fertile day
		$lastdaytime=$lasttime + $_POST[days]*24*3600 - 12*24*3600;
		$lastday=date("F d, Y",$lastdaytime);
		
		//have to adjust due date?
		$diff=$_POST[days] - 28;
		
		//due date $date + 280 days
		$duedatetime=$lasttime + 280*24*3600 + $diff*24*3600;
		$duedate=date("F d, Y",$duedatetime);
	
			
		//the result is here
		$ovcalc.='<div class="ovpredct_table">
		Here are the results based on the information you provided:<br /><br />
		You next most fertile period is <strong>'.$firstday.' to '.$lastday.'</strong>.<br ><br />
		If you conceive within this timeframe, your estimated due date will be <strong>'.$duedate.'</strong>	
		<p align="center"><input type="button" value="Calculate again!" onclick="javascript:history.back();"></p>
		</div>';
		
	}
	else
	{
		$ovcalc.='<div class="ovpredct_table">
		<form method="post">
		Please select the first day of your last menstrual period:<br /><br />
		'.ovpredct_datechooser("date",date("Y-m-d")).'<br><br>
		Usual number of days in your cycle: <select name="days">';
				
		for($i=20;$i<=45;$i++)
		{
			if($i==28) $selected='selected';
			else $selected='';
			$ovcalc.="<option $selected value='$i'>$i</option>";
		}
		
		$ovcalc.='</select>
		<p align="center"><input type="submit" name="calculator_ok" value="Calculate"></p>
		</form>		
		</div>';
	}

    return $ovcalc;
}

// This just echoes the text
function ovpredct($content) 
{	
	if(!strstr($content,"[ovulation-predictor]")) return $content;
	
	$ovcalc=ovpredct_generate_html();
	
	$content=str_replace("[ovulation-predictor]",$ovcalc,$content);
	return $content;
}

// the widget object
class OvPredct extends WP_Widget {
    /** constructor */
    function OvPredct() {
        parent::WP_Widget(false, $name = 'Ovulation Predictor');
    }
    
    function form()
    {
        ovpredct_options(true);
    }
    
    function widget($args, $instance) 
    {
        echo ovpredct_generate_html();
    }
}

add_action('admin_menu','ovpredct_add_page');
add_filter('the_content', 'ovpredct');
add_action('widgets_init', create_function('', 'return register_widget("OvPredct");'));
?>