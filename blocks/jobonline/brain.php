<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

///define('AJAX_SCRIPT', true);

require_once('../../config.php');
require_once(__DIR__."/block_jobonline.php");

//require_login(0,false);

$fn=optional_param('fn',null,PARAM_RAW);

if($fn)
{
   $fn();
}
else
{
   print "";
}

function update_sector()
{
   $sector=optional_param('sector','',PARAM_TEXT);
   $user=optional_param('user',0,PARAM_INT);

   global $DB;

   if($user)
   {
       $USER=core_user::get_user($user);
   };

   if($USER
      and ($sector===''
           or in_array($sector,block_jobonline::allareas())))
   {
       block_jobonline::set_pref('jobonline_preferred_sector',strtolower($sector));
       print block_jobonline::buildcontents();
   }
   else
   {
       print "failed";
   }
}

function update_location()
{
   $location=optional_param('location','',PARAM_TEXT);
   $user=optional_param('user',0,PARAM_INT);

   global $DB;

   if($user)
   {
       $USER=core_user::get_user($user);
   };

   if($USER
      and ($location===''
           or in_array($location,block_jobonline::alllocations())))
   {
       block_jobonline::set_pref('jobonline_preferred_location',strtolower($location));
       print block_jobonline::buildcontents();
   }
   else
   {
       print "failed";
   }
}

function update_type()
{
   $type=trim(optional_param('type','',PARAM_TEXT));
   $user=optional_param('user',0,PARAM_INT);

   global $USER,$DB,$SESSION;

   if($user)
   {
       $USER=core_user::get_user($user);
   };

   if($USER)
   {
       block_jobonline::set_pref('jobonline_preferred_type',strtolower($type));
       print block_jobonline::buildcontents();
   }
   else
   {
       print "failed";
   }
}

function update_sort()
{
   $sort=trim(optional_param('sort','ending-sort',PARAM_TEXT));
   $user=optional_param('user',0,PARAM_INT);

   global $USER,$DB,$SESSION;

   if($user)
   {
       $USER=core_user::get_user($user);
   };

   if($USER
      and in_array($sort,array('ending-sort','post-sort')))
   {
       block_jobonline::set_pref('jobonline_preferred_sort',$sort);
       print block_jobonline::buildcontents();
   }
   else
   {
       print "failed";
   }
}
