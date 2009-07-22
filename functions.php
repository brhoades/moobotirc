<?php
/*

Moobot is Copyright 2008-2009 Billy "Aaron5367" Rhoades

This file is part of Moobot.

Moobot is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Moobot is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Moobot.  If not, see <http://www.gnu.org/licenses/>.  
 
Example Servers:
$CONFIG['servers']['KOR'][0]['ip']  = "64.92.167.138";
$CONFIG['servers']['KOR'][0]['port']  = "30800";
$CONFIG['servers']['KOR'][0]['bakname']  = "^0AAA ^2|KoR| KoRx";
$CONFIG['servers']['KOR'][1]['ip']  = "64.92.167.138";
$CONFIG['servers']['KOR'][1]['port']  = "30740";
$CONFIG['servers']['KOR'][1]['bakname']  = "^0AAA ^2|KoR| Layouts";
$CONFIG['servers']['KOR'][2]['ip']  = "64.92.167.138";
$CONFIG['servers']['KOR'][2]['port']  = "30720";
$CONFIG['servers']['KOR'][2]['bakname']  = "^0AAA ^2K^7nights ^2o^7f ^2R^7eason.org";
$CONFIG['servers']['KOR'][3]['ip']  = "64.92.167.138";
$CONFIG['servers']['KOR'][3]['port']  = "30750";
$CONFIG['servers']['KOR'][3]['bakname']  = "^0AAAAAA ^2|KoR| Scrim Server";
$CONFIG['servers']['KOR'][4]['ip']  = "64.92.167.138";
$CONFIG['servers']['KOR'][4]['port']  = "30801";
$CONFIG['servers']['KOR'][4]['bakname']  = "^0AAA ^2|KoR| KoRx Test";

*/

///////////
//CLASSES//
///////////
$bot = new bot();
$other = new other();

class other
{
  function check()
  {
    global $other, $bot;
    
    $username = $_COOKIE['username'];
    $username = $other->sanitize($other->sanitize2($username));
    $password = $_COOKIE['password'];
    $user = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE username='$username'"));

    echo mysql_error();

    if( $password != $user['password'] || mysql_error() != NULL )
      $error = "Invalid Username/Password";

    if( $error != NULL )
    {
      setcookie("username", "");
      setcookie("password", "");
      setcookie("error", $error);
      echo "<META HTTP-EQUIV=\"refresh\" CONTENT=\"0;URL=?p=1\">"; 
      //exit();
      //Back to the main page we go.
    }
  }
  function sanitize2($input) 
  {
      if (is_array($input)) 
      {
          foreach($input as $var=>$val) 
          {
              $output[$var] = sanitize($val);
          }
      }
      else 
      {
          if ( get_magic_quotes_gpc() ) 
          {
              $input = stripslashes( $input );
          }
          $output = mysql_real_escape_string($input);
      }
      return $output;
  }

	function sanitize($input) //http://www.denhamcoote.com/php-howto-sanitize-database-inputs
  { 
    $search = array(
    '@<script[^>]*?>.*?</script>@si',   // Strip out javascript
    '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
    '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
    '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
    );

    $output = preg_replace($search, '', $input);
    return $output;
  }

  function redirect($when, $where)
  {
    if( $when < 0 )
      die("Invalid time");
    return("<META HTTP-EQUIV=\"refresh\" CONTENT=\"$when;URL=$where\">"); 
  }

  function greet( $userdata )
  {
    $TOD = date("G");
    if( $TOD <= 12 )
    {
      $topC = "Morning";
      $topL = "morning";
    }
    else if( $TOD > 12 && $TOD <= 18 )
    {
      $topC = "Afternoon";
      $topL = "afternoon";
    }
    else if( $TOD > 18 )
    {
      $topC = "Evening";
      $topL = "evening";
    }
    
    if( $userdata['lastlogin'] % 2 )
      $greet = "Good $topL ".$userdata['name'].", good to see you on this fine day.";
    else if( $userdata['lastlogin'] % 3 )
      $greet = "Good $topL ".$userdata['name'].", didn't expect to see you here!.";
    else if( $userdata['lastlogin'] % 5 )
      $greet = "Good $topL ".$userdata['name'].", I hope you are having a good day.";
    else if( $userdata['lastlogin'] % 7 )
      $greet = "Good $topL ".$userdata['name'].", great to see you today.";
    else if( $userdata['lastlogin'] % 11 )
      $greet = "Good...er...$topL ".$userdata['name'].".";
    else
      $greet = "Good $topL ".$userdata['name'];
      
    return( $greet ); 
  }
  function get_server_settings( $server, $port ) 
  {
    global $bot, $other;
    $fp = fsockopen( "udp://".$server, $port, $errno, $errstr, 2 );
    if( !$fp )
    {
      while( !$fp && $tries<10 )
      {
        $fp = fsockopen( "udp://".$server, $port, $errno, $errstr, 2 );
        $tries++;
      }
      if( !$fp )
        return;
    }
     
    $status_str = "xxxxgetstatus";
    for($i=0;$i<4;$i++) $status_str[ $i ] = pack("v", 0xff);
    fwrite( $fp, $status_str );
    socket_set_timeout( $fp, 1 );
    $data_full = fread( $fp, 10000 );
    $data_full = substr( $data_full, 19 );
    //echo $data_full."<br /> <br />\n";
    $data_full = explode( "\\", $data_full );
    for( $i=0; $i<count( $data_full ); $i++ )
    {
      //if( ( $i % 2 ) )
      //  echo $data_full[ $i ]." = ";
      //else
      //  echo $data_full[ $i ]."<br />\n";
      
      if( $data_full[ $i ] == "sv_hostname" )
        $info[ 'servername' ] = trim(  $other->tremulous_replace_colors_web( $data_full[ $i + 1 ] ) ); 
      if( $data_full[ $i ] == "sv_maxclients" )
        $maxclients = $data_full[ $i + 1 ];
      if( $data_full[ $i ] == "sv_privateClients" || $data_full[ $i ] == "sv_privateclients" )
        $privateclients = $data_full[ $i + 1 ];
    }
    $info[ 'maxplayers' ] = $maxclients-$privateclients;
    return $info;
  }
  function tremulous_replace_colors_web( $in ) 
  {
    $in = preg_replace( "/\^1/", "<font color=\"#ff040f\">", $in, -1, $c );
    $count += $c;
    $in = preg_replace( "/\^0/", "<font color=\"gray\">", $in, -1, $c );
    $count += $c;
    $in = preg_replace( "/\^8/", "<font color=\"gray\">", $in, -1, $c );
    $count += $c;
    $in = preg_replace( "/\^2/", "<font color=\"#00ff00\">", $in, -1, $c );
    $count += $c;
    $in = preg_replace( "/\^3/", "<font color=\"#fcff03\">", $in, -1, $c );
    $count += $c;
    $in = preg_replace( "/\^4/", "<font color=\"#0200fc\">", $in, -1, $c );
    $count += $c;
    $in = preg_replace( "/\^5/", "<font color=\"#00fff0\">", $in, -1, $c );
    $count += $c;
    $in = preg_replace( "/\^6/", "<font color=\"#ff06ff\">", $in, -1, $c );
    $count += $c;
    $in = preg_replace( "/\^7/", "<font color=\"lightgray\">", $in, -1, $c );
    $count += $c;
  //	$in = preg_replace( "/\^.{1}/", "", $in );
    $in = preg_replace( "//", "", $in );
    for( $i=0; $i<$count; $i++ )
      $in = $in."</font>";
    return $in;
  }
  function tremulous_get_players( $server, $port ) 
  {
    global $bot, $other;
    $fp = @fsockopen( "udp://".$server, $port, $errno, $errstr, 1 );
    if( !$fp )
    {
      while( !$fp && $tries < 3 )
      {
        $fp = @fsockopen( "udp://".$server, $port, $errno, $errstr, 1 );
        $tries++;
      }
      if( !$fp )
        return;
    }
         
    $status_str = "xxxxgetstatus";
    for($i=0;$i<4;$i++) $status_str[$i] = pack("v", 0xff);
    fwrite($fp, $status_str);
    socket_set_timeout( $fp, 1 );
    stream_set_timeout( $fp, 1 );
    $data_full = fread($fp, 10000);
    $data_full = substr( $data_full, 19 );
    //echo $data_full;
    $data = explode("\n", $data_full);
    fclose($fp);
    $server_data = explode("\\", $data[0]);

    for ($i=1; $i<count($server_data); $i+=2) {
      $server_status[$server_data[$i]] = $server_data[$i+1];
      if($server_data[$i] == "mapname") {
        $map = $server_data[$i+1];
      }
      elseif($server_data[$i] == "P") {
        $P = str_replace("-", "", $server_data[$i+1]);
      }
    }

    $i = 1;
    next($data); // skip settings
    while(list(,$p) = each($data)) {
      if(preg_match("/^(-\d+|\d+) (\d+) \"(.*)\"$/", $p, $m)) {
        if( array( $m ) )
          $pinfo = array( 
            "kills" => $m[1],
            "ping" => $m[2],
            "raw_name" => $m[3],
            "name" => $bot->tremulous_strip_colors($m[3]),
            "colored_name" => 
              $other->tremulous_replace_colors_web($m[3]));
        if($P[$i-1] == 2) {
          $human[] = $pinfo;
        }
        elseif($P[$i-1] == 1) {
          $alien[] = $pinfo;
        }
        else {
          $spec[] = $pinfo;
        }
        $i++;
      }
    }
    if( $map == "" )
    {
      while( $tries < 2 )
      {
        $out = $other->tremulous_get_players( $ip, $port );
        if( $out['map'] != "" )
          return $out;
        $tries++;
      }
    }

    return array(
      "alien_players" => $alien,
      "human_players" => $human,
      "spec_players" => $spec,
      "map" => $map
      );
  }
  function url_exists($url) 
  {
    $hdrs = @get_headers( $url );
    return is_array( $hdrs ) ? preg_match( '/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/', $hdrs[0] ) : FALSE;
  }  
  /**
   * A function for making time periods readable
   *
   * @author      Aidan Lister <aidan@php.net>
   * @version     2.0.0
   * @link        http://aidanlister.com/2004/04/making-time-periods-readable/
   * @param       int     number of seconds elapsed
   * @param       string  which time periods to display
   * @param       bool    whether to show zero time periods
   */
  function time_duration($seconds, $use = null, $zeros = false)
  {
      // Define time periods
      $periods = array (
          'years'     => 31556926,
          'Months'    => 2629743,
          'weeks'     => 604800,
          'days'      => 86400,
          'hours'     => 3600,
          'minutes'   => 60,
          'seconds'   => 1
          );

      // Break into periods
      $seconds = (float) $seconds;
      foreach ($periods as $period => $value) {
          if ($use && strpos($use, $period[0]) === false) {
              continue;
          }
          $count = floor($seconds / $value);
          if ($count == 0 && !$zeros) {
              continue;
          }
          $segments[strtolower($period)] = $count;
          $seconds = $seconds % $value;
      }

      // Build the string
      foreach ($segments as $key => $value) {
          $segment_name = substr($key, 0, -1);
          $segment = $value . ' ' . $segment_name;
          if ($value != 1) {
              $segment .= 's';
          }
          $array[] = $segment;
      }

      $str = implode(', ', $array);
      return $str;
  }
  function file_to_array( $file )
  {
    $fileh = fopen( $file, 'r' );
    $i = 0;
    while( !feof( $hfile ) )
    {
      $out = fgets( $hfile );
      if( $out == NULL )
        continue;
      if( substr( "//", 0, 2 ) )
        continue;
      $result[$i] = $out;
      $i++;
    }
    fclose( $hfile );
    return $result;
  }
  
  function replace( $replacewhat, $withwhat, $inwhat )
  {
    $inwhat = explode( $replacewhat, $inwhat );
    $inwhat = implode( $withwhat, $inwhat );
    return( $inwhat );
  }
  
  function can_vote( $levelarray, $votetype )
  {
    for( $i=0; $i<count($levelarray); $i++ )
    {
      $thislevel = $levelarray[$i];
      if( $votetype == IN_CLAN_VOTE && $thislevel == CLAN_MEMBER )
        return TRUE;
      else if( $votetype == VIP_MOD_VOTE 
               && ( $thislevel == VIP_OPERATOR || $thislevel == MODERATOR 
                    || $thislevel == GLOBAL_MODERATOR || $thislevel == ADMINISTRATOR ) )
        return TRUE;
      else if( $votetype == VIP_OP_VOTE
                && ( $thislevel == MODERATOR || $thislevel == GLOBAL_MODERATOR 
                     || $thislevel == ADMINISTRATOR ) )
        return TRUE;
      else if( $votetype == TYPICAL_PROMOTION_VOTE
                && ( $thislevel == GLOBAL_MODERATOR || $thislevel == ADMINISTRATOR ) )
        return TRUE;
      else if( $votetype == GMOD_PROMOTION_VOTE && $thislevel == ADMINISTRATOR )
        return TRUE;
      else if( $votetype == DEMOTION_VOTE 
               && ( $thislevel == ADMINISTRATOR || $thislevel == GLOBAL_MODERATOR ) )
        return TRUE;
      else if( $votetype == OTHER && $thislevel == CLAN_MEMBER )
        return TRUE;
      else if( $votettype == VIP_MOD_OP_OVERRIDE_VOTE
                && ( $thislevel == MODERATOR || $thislevel == GLOBAL_MODERATOR 
                     || $thislevel == ADMINISTRATOR ) )
        return TRUE;
    }
    return FALSE;
  }
  
  function level_test( $level, $array )
  {
    if( array_search( $level, $array ) !== FALSE )
      return TRUE;
    else
      return FALSE;
  }

  function array_search_recursive( $needle, $haystack, $strict=FALSE, $path=array( ) )
  {
    if( !is_array( $haystack ) ) 
        return FALSE;
 
    foreach( $haystack as $key => $val ) 
    {
      if( is_array( $val ) && $subPath = array_search_recursive( $needle, $val, $strict, $path ) ) 
      {
        $path = array_merge( $path, array( $key ), $subPath );
        return TRUE;
      } 
      else if( ( !$strict && $val == $needle) || ( $strict && $val === $needle ) ) 
      {
        $path[] = $key;
        return TRUE;
      }
    }
    return FALSE;
  }
  
  function filesize_range( $filesize, $size = NULL )
  {
    if( !is_int( $filesize ) )
    {
      echo "Error, a bad argument was inserted into filesize_range, ".$filesize;
      return;
    }
    if( $size != NULL )
    {
      $size = strtolower( $size );
      if( $size == "tb" )
        $size = 4;
      else if( $size == "gb" )
        $size = 3;
      else if( $size == "mb" )
        $size = 2;
      else if( $size == "kb" )
        $size = 1;
      else if( $size == "b" )
        return $filesize;
      
      return( $filesize / ( 1024^$size ) );
    }
    
    if( $filesize > 0 && $filesize < 1024 )
      $out = $filesize." B";
    else if( $filesize >= 1024 && $filesize < pow( 1024, 2 ) )
      $out = round( ( $filesize/1024 ), 2 )." KiB";
    else if( $filesize >= pow( 1024, 2 ) && $filesize < pow( 1024, 3 ) )
      $out = round( ( $filesize / pow( 1024, 2 ) ), 2 )." MiB";
    else if( $filesize >= pow( 1024, 3 ) && $filesize < pow( 1024, 4 ) )
      $out = round( ( $filesize / pow( 1024, 3 ) ), 2 )." GiB";
    else if( $filesize >= pow( 1024, 4 ) )
      $out = round( ( $filesize / pow( 1024, 4 ) ), 2 )." TiB";
    
    return( $out );
  }
}

class bot
{
  function count_words($str)
  {
    $words = 0;
    $str = eregi_replace(" +", " ", $str);
    $array = explode(" ", $str);
    for($i=0;$i < count($array);$i++)
    {
      if (eregi("[0-9A-Za-zÀ-ÖØ-öø-ÿ]", $array[$i])) 
      $words++;
    
    }  
    return $words;
  }

  function tremulous_strip_colors( $in ) 
  {
    $in = preg_replace( "/\^.{1}/", "", $in );
    $in = preg_replace( "//", "", $in );
    return $in;
  }

  function tremulous_get_players( $server, $port, $cbf ) 
  {
    global $bot, $other, $bstatus;
    $fp = fsockopen( "udp://".$server, $port, $errno, $errstr, 1 );
    if( !$fp )
    {
      while( !$fp && $tries < 3 )
      {
        $fp = fsockopen( "udp://".$server, $port, $errno, $errstr, 1 );
        $tries++;
      }
      if( !$fp )
        return;
    }
    
    $bstatus['trem']++;
    $status_str = "xxxxgetstatus";
    for($i=0;$i<4;$i++) $status_str[$i] = pack("v", 0xff);
    fwrite($fp, $status_str);
    socket_set_timeout( $fp, 1 );
    stream_set_timeout( $fp, 1 );
    $data_full = fread( $fp, 10000 );
    $data_full = substr( $data_full, 19 );
    $data = explode("\n", $data_full);
    fclose($fp);
    $server_data = explode("\\", $data[0]);

    for ($i=1; $i<count($server_data); $i+=2) {
      $server_status[$server_data[$i]] = $server_data[$i+1];
      if($server_data[$i] == "mapname") {
        $map = $server_data[$i+1];
      }
      elseif($server_data[$i] == "P") {
        $P = str_replace("-", "", $server_data[$i+1]);
      }
    }

    $i = 1;
    next($data); // skip settings
    while(list(,$p) = each($data)) {
      if(preg_match("/^(-\d+|\d+) (\d+) \"(.*)\"$/", $p, $m)) {
        $pinfo = array( 
          "kills" => $m[1],
          "ping" => $m[2],
          "raw_name" => $m[3],
          "name" => $bot->tremulous_strip_colors($m[3]),
          "colored_name" => 
            $bot->tremulous_replace_colors_irc($m[3]));
        if($P[$i-1] == 2) {
          $human[] = $pinfo;
        }
        elseif($P[$i-1] == 1) {
          $alien[] = $pinfo;
        }
        else {
          $spec[] = $pinfo;
        }
        $i++;
      }
    }
    if( $map == "" && $cbf != "TRUE" )
    {
      while( $tries < 2 )
      {
        $out = $bot->tremulous_get_players( $ip, $port, "TRUE" );
        if( $out['map'] != "" )
          return $out;
        $tries++;
      }
    }
    
    return array(
      "alien_players" => $alien,
      "human_players" => $human,
      "spec_players" => $spec,
      "map" => $map
      );
  }

  function get_server_settings( $server, $port, $cbf ) 
  {
    global $bot, $other, $bstatus;
    $fp = fsockopen( "udp://".$server, $port, $errno, $errstr, 1 );
    if( !$fp )
    {
      while( !$fp && $tries<3 )
      {
        $fp = fsockopen( "udp://".$server, $port, $errno, $errstr, 1 );
        $tries++;
      }
      if( !$fp )
        return;
    }
     
    $bstatus['trem']++;
    $status_str = "xxxxgetstatus";
    for($i=0;$i<4;$i++) $status_str[ $i ] = pack("v", 0xff);
    fwrite( $fp, $status_str );
    socket_set_timeout( $fp, 1 );
    stream_set_timeout( $fp, 1 );
    $data_full = fread( $fp, 10000 );
    $data_full = substr( $data_full, 19 );
    if( $data_full == NULL && $cbf != "TRUE" )
    {
      while( $tries < 2 )
      {
        $out = $bot->get_server_settings( $ip, $port, "TRUE" );
        if( $out != "" )
          return $out;
        $tries++;
      }
    }
    //echo $data_full."<br /> <br />\n";
    $data_full = explode( "\\", $data_full );
    fclose( $fp );
    for( $i=0; $i<count( $data_full ); $i++ )
    {
      if( $data_full[ $i ] == "sv_hostname" )
        $info[ 'servername' ] = trim( $bot->tremulous_replace_colors_irc( $data_full[ $i + 1 ] ) ); 
      if( $data_full[ $i ] == "sv_maxclients" )
        $maxclients = $data_full[ $i + 1 ];
      if( $data_full[ $i ] == "sv_privateClients" || $data_full[ $i ] == "sv_privateclients" )
        $privateclients = $data_full[ $i + 1 ];
    }
    $info[ 'maxplayers' ] = $maxclients-$privateclients;
    return $info;
  }

  function find_player( $name )
  {
    global $bot, $other;
    $ipandport = $bot->find_servers( );
    echo count( $ipandport );
    $name = preg_quote( $name ); //Search for everything
    for( $i=0; $i < count( $ipandport ); $i++ )
    {
      $server[ $i ] = $bot->tremulous_get_players( $ipandport[ $i ][ 0 ], $ipandport[ $i ][ 1 ] );
      for( $h=0; $h < count( $server[ $i ][ alien_players ] ); $h++ )
      {
        if( preg_match( "/$name/i", $server[ $i ][ alien_players ][ $h ][ 'name' ] ) )
        {
          $currid = count( $found );
          $found[ $currid ]['name'] = $server[ $i ][ alien_players ][ $h ][ 'colored_name' ];
          $found[ $currid ]['kills'] = $server[ $i ][ alien_players ][ $h ][ 'kills' ];
          $found[ $currid ]['ping'] = $server[ $i ][ alien_players ][ $h ][ 'ping' ];
          $found[ $currid ]['team'] = "aliens";
          $serveri = $bot->get_server_settings( $ipandport[ $i ][ 0 ], $ipandport[ $i ][ 1 ] );
          $found[ $currid ]['server'] = $serveri['servername'];
        }
      }
      for( $h=0; $h < count( $server[ $i ][ human_players ] ); $h++ )
      {
        if( preg_match( "/$name/i", $server[ $i ][ human_players ][ $h ][ 'name' ] ) )
        {
          $currid = count( $found );
          $found[ $currid ]['name'] = $server[ $i ][ human_players ][ $h ][ 'colored_name' ];
          $found[ $currid ]['kills'] = $server[ $i ][ human_players ][ $h ][ 'kills' ];
          $found[ $currid ]['ping'] = $server[ $i ][ human_players ][ $h ][ 'ping' ];
          $found[ $currid ]['team'] = "humans";
          $serveri = $bot->get_server_settings( $ipandport[ $i ][ 0 ], $ipandport[ $i ][ 1 ] );
          $found[ $currid ]['server'] = $serveri['servername'];
        }
      }
      for( $h=0; $h < count( $server[ $i ][ spec_players ] ); $h++ )
      {
        if( preg_match( "/$name/i", $server[ $i ][ spec_players ][ $h ][ 'name' ] ) )
        {
          $currid = count( $found );
          $found[ $currid ]['name'] = $server[ $i ][ spec_players ][ $h ][ 'colored_name' ];
          $found[ $currid ]['ping'] = $server[ $i ][ spec_players ][ $h ][ 'ping' ];
          $found[ $currid ]['team'] = "spectators";
          $serveri = $bot->get_server_settings( $ipandport[ $i ][ 0 ], $ipandport[ $i ][ 1 ] );
          $found[ $currid ]['server'] = $serveri['servername'];
        }
      }
    }
    return $found;
  }

  function find_servers( )
  {
    global $bot;
    $ipsandports = $bot->tremulous_getserverlist( );
    for( $i = 0; $i < count( $ipsandports ); $i++ )
    {
      $ip = $ipsandports[$i][ 'ip' ];
      $port = $ipsandports[$i][ 'port' ];
      $ipandport[$i][0] = $ip;
      $ipandport[$i][1] = $port;
    }
    return( $ipandport );
  }

  function average_ping( $server )
  {
    $counta = count( $server[ alien_players ]  );
    $counth = count( $server[ human_players ]  );
    $counts = count( $server[ spec_players ]  );
    $count = $counta + $counts + $counth;
    if( $count <= 0 )
      $count = 1;
    $pingtotal = 0;
    for( $i=0;$i<$counta;$i++ )
      $pingtotal = $pingtotal + $server[ alien_players ][ $i ][ 'ping' ];
    for( $i=0;$i<$counth;$i++ )
      $pingtotal = $pingtotal + $server[ human_players ][ $i ][ 'ping' ];
    for( $i=0;$i<$counts;$i++ )
      $pingtotal = $pingtotal + $server[ spec_players ][ $i ][ 'ping' ];
    return(round($pingtotal/$count));
  }

  function tremulous_rcon( $server, $port, $command, $rcon, $cbf = FALSE, $colors = TRUE ) 
  {
    global $bstatus, $bot;
    if( $server == NULL )
      return( "Error, the IP argument was empty." );
    else if( $port == NULL )
      return( "Error, the port argument was empty." );
    $fp = fsockopen( "udp://".$server, $port, $errno, $errstr, 2 );
    if( !$fp )
    {
      while( !$fp && $tries<3 )
      {
        $fp = fsockopen( "udp://".$server, $port, $errno, $errstr, 2 );
        $tries++;
        echo "Retrying, no connection established...\n";
      }
      if( !$fp )
        return "Error connecting\n";
    }
    $bstatus['trem']++;
    $status_str = "xxxxrcon ".$rcon." $command";
    for($i=0;$i<4;$i++) 
      $status_str[$i] = pack("v", 0xff);
    fwrite( $fp, $status_str );
    socket_set_timeout($fp, 1);
    $k=1;
    do
    {
      $data = fread( $fp, 8*1024 );
      if( strlen( $data ) == 0 )
        break;
      $data_full[] = $data;
      unset( $data );
      $k++;
    } while( true );
    fclose( $fp );
    //stream_set_timeout($fp, 1);
    /*if( $data_full == NULL && $cbf != "TRUE" )
    {
      while( $tries < 2 )
      {
        $out = $bot->tremulous_rcon( $server, $port, $command, $rcon, "TRUE" );
        echo "Retrying, no data recieved...\n";
        if( $out != NULL )
          return $bot->tremulous_replace_colors_irc( $out );
        $tries++;
      }
    }*/
    $data_full = implode( "\n", $data_full );
    $data_full = substr( $data_full, 10 );

    $data = explode( "\n", $data_full );
    if( $colors )
      return $bot->tremulous_replace_colors_irc( $data );
    else
      return $data;
  }

  function svnmon( )
  {
    global $con, $CONFIG, $channels, $bot, $svnservers, $nextsvnmon;

    if( $CONFIG['svnmon'] == "FALSE" )
      return;
    
    for( $c=0; $c<count($svnservers); $c++ )
    {
     unset( $svnurl, $tries, $thissvnlog, $svnout, $svnout2, $committer, $message, $string, $svnout3, $thisserver );
     $svnurl = $svnservers[$c]['url'];
     $thissvnlog = $con['data'][svnstuffs][$c];
     exec( "svn log -l1  $svnurl", $svnout );
     $tries = 0;
     while( count( $svnout ) == 0 && $tries < 3 )
     {
       unset( $svnout );
       exec( "svn log -l1 $svnurl", $svnout );
       $tries++;
     }
     if( count( $svnout ) == 0 )
     {
       echo "SVN: Couldn't check $svnurl\n"; 
       $nextsvnmon += 60;
       continue;
     }
     $tries = 0;

      if( $svnout[1] == $thissvnlog )
        continue;
      else
      {
        echo $svnout[1]." != '$thissvnlog'";
        print_r( $con['data'] );
        $svnout2 = $svnout;
        $svnlogout[$c] = $svnout[1];
        $con['data'][svnstuffs][$c] = $svnout[1];
        
        $bot->writedata( $con['data'] );

        $svnarray = explode( " | ", $svnout[1] );
        $rev = $svnarray['0'];
        $revnum = ltrim( $rev, "r" );
        $revold = $revnum-1; //for now
        $committer = $svnarray['1'];
        while( count( $svnout3 ) == 0 )
        {
            unset( $svnout3 );
            exec( "svn diff -r$revold:$revnum $svnurl", $svnout3 );
        }
        $diffsize = count($svnout3);
        $k = 0;
        for( $i=0; $i<count($svnout2)-1; $i++ )
        {
          if( $i == 0 || $i == 1 || $i == 2 || $i == count($svnmon2)-1 )
            continue;
          if( stripos( $svnmon2[$i], "----------" ) !== FALSE )
            continue;
          $message[$k] = $svnout2[$i];
          $k++;
        }

        $svnarray = $svnout3;      
        for( $i=0; $i<count($svnout3); $i++ )
        {
          if( stripos( $svnarray[$i], "Index: " ) !== FALSE )
          {
            $fileid = count($file);
            $file[$fileid]['fullname'] = substr( $svnarray[$i], 7 );
            $file[$fileid]['xdir'] = explode( "/", $file[$fileid]['fullname'] );
            $file[$fileid]['filename'] = $file[$fileid]['xdir'][count( $file[$fileid]['xdir'] ) - 1 ];
            unset( $file[$fileid]['xdir'][count( $file[$fileid]['xdir'] ) - 1 ] );
            $file[$fileid]['xdir'] = array_values( $file[$fileid]['xdir'] );
            $file[$fileid]['dirs'] = implode( "/", $file[$fileid]['xdir'] );
            $infile = "TRUE";
          }
          if( $infile == "TRUE" && stripos( $svnarray[$i], "+++" ) )
          {
            $file[$fileid]['type'] = "mod";
            $infile = "FALSE";
            $modded++;
          }
          if( $infile == "TRUE" && stripos( $svnarray[$i], "Added" ) )
          {
            $file[$fileid]['type'] = "add";
            $infile = "FALSE";
            $added++;
          }
        }
        $dirs = 1;
        if( count( $file ) > 1 )
        {
          $dirs = 0;
          for( $j=0; $j<(count($file)); $j++ )
          {
            if( array_search( $file[$j]['dirs'], $dirlist ) !== FALSE )
              continue;
            //ELSE
            
            $dirs++;
            $dirlist[count($dirlist)] = $file[$j]['dirs'];
          }
        }
        if( $dirs > count( $file ) )
          $dirs = count( $file );
        if( $dirs == 1 && count($file) == 2 )
        {
          $tempfile = $file[0]['dirs'];
          $files = "in ".$tempfile." : ".$file[0]['filename']." and ".$file[1]['filename'];
        }
        else if( $dirs == 1 && count($file) <= 5 && count($file) > 2 )
        {
          for( $i=0;$i<count($file);$i++ )
          {
            $tempfile = $file[0]['dirs'];
            if( $i == 1 )
              $files = "in ".$tempfile." : ".$file[$i]['filename'];
            else if( $i < count($file) )
              $files = $files.", ".$file[$i]['filename'];
            else if( $i == count($file) )
              $files = $files.", and ".$file[$i]['filename'];
          }
        }
        else if( $dirs > 1 )
          $files = "(".count($file)." files in $dirs directories)";
        else if( count( $file ) == 1 && $dirs == 1 )
        {
          $tempfile = $file[0]['dirs'];
          $files = "in ".$tempfile." : ".$file[0]['filename'];
        }
          
        $string[0] = $committer." * ".$rev." ".$files;
        for( $i=0; $i<count($message); $i++ )
        {
          $string[1+$i] = $message[$i];
        }
        
        for( $i=0; $i<count($channels); $i++ )
        {
          if( $channels[$i]['svnmon'] == "FALSE" )
            continue;
            
          $bot->talk( $channels[$i]['name'], $svnservers[$c]['name']." SVN update:" );
          for( $j=0; $j<count($string); $j++ )
          {
            if( $string[$j] == "\n" || $string[$j] == NULL )
              continue;
            $bot->talk( $channels[$i]['name'], $string[$j] );
          }
        }
        /*for( $j=0; $j<count($CONFIG['servers']['KOR']); $j++ )
        {
          unset( $thisserver );
          $thisserver = $CONFIG['servers']['KOR'][$j];
          $bot->tremulous_rcon( $thisserver['ip'], $thisserver['port'], "!print ".$svnservers[$c]['name']." SVN Update:", $thisserver['rcon'] );
          for( $k=0; $k<count($string); $k++ )
          {
            if( $string[$k] == "\n" || $string[$k] == NULL )
              continue;
            $bot->tremulous_rcon( $thisserver['ip'], $thisserver['port'], "!print ".$string[$k], $thisserver['rcon'] );
          }
        }*/
      }
    }
  }
  
  function hgmon( )
  {
    global $bot, $con, $hgservers;
    define( "MAX_REPORT", 10 );
    //max number of commits at a time
    for( $i = 0; $i < count( $hgservers ); $i++ )
    {
      //here for a reason!
      $thisdata = $con['data'][hgstuffs][$i];
  
      //check stuff
      exec( "hg pull -u ".$hgservers[$i]['loc'] );
      exec( "hg log -l1 -M --template \"{rev} ||| {node|short} ||| {author} ||| {branches} ||| {desc}\" ".$hgservers[$i]['loc'], $whatitsays );
      
      $whatitsays = implode( "<br />", $whatitsays );
      
      if( $whatitsays == $thisdata )
        continue;
      
      $con['data'][hgstuffs][$i] = $whatitsays;
      $bot->writedata( $con['data'] );
      
      for( $k = 2; $k < MAX_REPORT+2; $k++ )
      {
        unset( $stufftoreport );
        exec( "hg log -l$k -M --template \"{rev} ||| {node|short} ||| {author} ||| {branches} ||| {desc}<cibr>\" ".$hgservers[$i]['loc'], $stufftoreport );
        if( is_array( $stufftoreport ) )
          $stufftoreport = implode( "<br />" );
        echo "Searching for '$thisdata' in '$stufftoreport'...\n";
        if( stripos( $stufftoreport, $thisdata ) !== FALSE )
          break;
      }
      if( $k == MAX_REPORT + 2 )
      {
        //this is our first time
        echo "Initial setup on hgmon detected.\n";
        continue;
      }
      
      echo "HG UPDATE!!!\n";
      //$stufftoreport = implode( "<br />", $stufftoreport );
      $stufftoreport = explode( "<cibr>", $stufftoreport );
      
      for( $k = 0; $k < count( $stufftoreport ); $k++ )
      {
        $thiscommit = explode( " ||| ", $stufftoreport[$k] );
        $rev = $thiscommit[0];
        $cset = $thiscommit[1];
        $author = $thiscommit[2];
        $branches = $thiscommit[3];
        $descr = $thiscommit[4];
        $descr = explode( "<br />", $descr );
        
        //go through the channels and see if they want to hear from us
        for( $j = 0; $j < count( $channels ); $j++ )
        {
          $si = explode( ",", $channel[$j]['hgmon'] );
          if( $channel[$j]['hgmon'] != $i
              && array_search( $i, $si ) === FALSE )
            continue;
          
          $bot->talk( $channel[$j]['name'], $hgservers[$i]['name']." HG update:" );
          $bot->talk( $channel[$j]['name'], "$author * $branches * r$rev:$cset " );
          for( $l = 0; $l < count( $descr ); $l++ )
            $bot->talk( $channel[$j]['name'], $descr[$l] );
        }
      }
    }
  }

  function cmd_send( $command, $end = FALSE )
  {
    global $con, $time, $CONFIG, $buffers, $bstatus;
    
    if( time() - $con[lastspeaktime] >= $CONFIG[chatspeaktimeout] ) //|| $con['stimes'] <= 3 )
    {
      if( $con['name'] == $CONFIG[nick] )
        return; //lets not and say we did
      if( !$end )
        fputs( $con['socket'], $command."\n\r" );
      else
        fputs( $con['socket'], $command );
      $con['stimes']++;
      $con[lastspeaktime] = time();
      $bstatus['commandstoserv']++;
      print ( date("[ d/m/y @ H:i:s ]") ."-> ". $command. "\n\r" );
    }
    else
      $buffers[ count($buffers) ] = $command;
    return;
  }

  function runbuffers( )
  {
    global $bot, $con, $time, $buffers, $CONFIG, $bstatus;
    
    if( time() - $con[lastspeaktime] >= $CONFIG[chatspeaktimeout] && count( $buffers ) > 0 )
    {
      $buffers = array_values( $buffers );
      fputs( $con['socket'], $buffers[0]."\n\r" );
      print ( date("[ d/m/y @ H:i:s ]") ."-> ".$buffers[0]."\n\r" );
      unset( $buffers[0] );
      $con[lastspeaktime] = time();
      $con['stimes']++;
      $bstatus['talked']++;
    }
    //else if( time() - $con['3lastspeaktime'] >= $CONFIG[chatspeaktimeout] + 2  && $con['stimes'] > 0 )
    //  $con['stimes'] = 0;
    return;
  }

  function talk( $channel, $text ) 
  {
    global $con, $CONFIG, $buffers, $bstatus;
    
    if( time() - $con[lastspeaktime] < $CONFIG[chatspeaktimeout] ) // || $con['stimes'] <= 3 )
    {
      if( $con['name'] == $CONFIG[nick] )
        return; //lets not and say we did
      fputs( $con['socket'], "PRIVMSG $channel :".$text."\n\r" );
      $con['stimes']++;
      $con[lastspeaktime] = time();
      $bstatus['talked']++;
      print (date("[d/m/y @ H:i:s]") ."-> PRIVMSG $channel :".$text."\n\r");
    }
    else
      $buffers[count($buffers)] = "PRIVMSG $channel :".$text;
    return;
  }

  function sysinfo( )
  {
    $kernel = shell_exec("uname -r") . substr("\n", 0, -2);
    $host = shell_exec("hostname");
    $hostname = shell_exec("hostname -f");
    $uptime = shell_exec("uptime");
    $cpuvendor = shell_exec("cat /proc/cpuinfo | head -n2 | tail -n1 | cut -f2 -d: | sed 's| ||'");
    $cpuc = shell_exec("grep '^processor' /proc/cpuinfo | tail -n 1 | awk '{print\$3+1}'");
    $cpumodel = shell_exec("cat /proc/cpuinfo | head -n5 | tail -n1 | cut -f2 -d: | sed 's| ||'");
    $cpumhz = shell_exec("cat /proc/cpuinfo | head -n7 | tail -n1 | cut -f2 -d: | sed 's| ||'");
    $cpucache = shell_exec("cat /proc/cpuinfo | head -n8 | tail -n1 | cut -f2 -d: | sed 's| ||'");
    $t = shell_exec("df | grep -vE \"^Filesystem|shm\" | awk '{print $2}'");
    $l = explode("\n", $t);
    $hdtotal = 0;
    foreach ($l as $n)
      $hdtotal += $n;
    if ($hdtotal > 1500000) 
    {
      $hdtotal = chr(2) . round($hdtotal / 1024 / 1024, 1) . chr(2) . " GB";
    } 
    else 
    {
      $hdtotal = chr(2) . round($hdtotal / 1024, 1) . chr(2) . " MB";
    }
    $t = shell_exec("df | grep -vE \"^Filesystem|shm\" | awk '{print $3}'");
    $l = explode("\n", $t);
    $hdused = 0;
    foreach ($l as $n)
      $hdused += $n;
    if ($hdused > 1500000) 
    {
      $hdused = chr(2) . round($hdused / 1024 / 1024, 1) . chr(2) . " GB";
    }
    else
    {
      $hdused = chr(2) . round($hdused / 1024, 1) . chr(2) . " MB";
    }
    $memtotal = shell_exec("free -m | head -n2 | tail -n1 | awk '{ print $2 }'");
    $memused = shell_exec("free -m | head -n3 | tail -n1 | awk '{ print $3 }'");
    $memfree = shell_exec("free -m | head -n3 | tail -n1 | awk '{ print $4 }'");
    $mempused = round($memused / $memtotal * 100);
    $mempfree = round($memfree / $memtotal * 100);
    $order = array("\r\n", "\n", "\r", "\t");
    $output = str_replace($order, "", 'Host ' . chr(2) . $host . chr(2) . ' Running: ' . chr(2) . $kernel . chr(2) . ' Uptime:' . chr(2) . $uptime . chr(2) . ' | Sysinfo: ' . $cpuvendor . ' ' . $cpumodel . ' @ ' . chr(2) . $cpumhz . chr(2) . " MHz " . chr(2) . $cpucache . chr(2) . ' cache [' . chr(2) . $cpuc . chr(2) . ' Core(s)] | Meminfo: Total Memory: ' . chr(2) . $memtotal . 'mb' . chr(2) . ' Used: ' . chr(2) . $memused . 'mb ' . chr(2) . "($mempused%)" . ' Free: ' . chr(2) . $memfree . 'mb ' . chr(2) . "($mempfree%)" . " | HDD: $hdused/$hdtotal Used");
    return trim( $output );
  }

  function checks( )
  {
    global $bot, $con, $CONFIG;
    if( stripos( $con['buffer']['all'], "KICK ") === FALSE )
      return;
    $channelz = stripos( $con['buffer']['all'], "KICK ")+5;
    $channellen = stripos( $con['buffer']['all'], " ".$CONFIG['name']." :")-$channelz;
    $channel = substr( $con['buffer']['all'], $channelz, $channellen );
    for( $i=0; $i < count($channels); $i++ )
    {
      if( $channel == $channels[$i]['name'] )
      {
        $chanid = $i;
        break;
        }
    }  
    if( stripos( $con['buffer']['all'], 'KICK '.$channel.' '.$CONFIG['name'].' :' ) !== FALSE )
    {
      $bot->cmd_send( "JOIN :".$channels[$chanid]['name']." ".$channels[$chanid]['password'] );
    }
  }

  function check_admin( $hostmask )
  {
    global $CONFIG;
    $admins = $CONFIG[adminname];
    for( $i=0;$i<count($admins);$i++ )
    {
      if( $hostmask == $admins[$i] )
        return TRUE;
    }
    
    return FALSE;
  }

  function call_vote( $string, $type, $caller, $channel )
  {
    global $CONFIG, $con, $bot, $other;
    
    //Check to see if we support this vote
    if( $type != "poll" && $type != "kick" )
    {
      $bot->talk( $channel, "$caller: Sorry, but I don't recognize the vote type '$type'." );
      return;
    }
    //If there's already a vote being called
    if( $con['vote']['inprogress'] == TRUE )
    {
      $bot->talk( $channel, "$caller: Sorry, but a vote is already in progress." );
      return;
    }
    
    if( $type == "poll" )
      $bot->talk( $channel, "$caller called a vote: [POLL] \"$string\"" );
    else if( $type == "kick" )
    {
      $stringex = explode( " ", $string );
      $target = $tringex['0'];
      unset( $stringex['0'] );
      $string = implode( " ", $stringex );
      //Check to see if the target is in the channel
      
    }
    
    $bot->talk( $channel, "Type F1 (yes) or F2 (no) in this channel, or send me it in a CTCP ping / private message to vote." );
    
    unset( $con['vote'] );
    $con['vote']['starttime'] = time();
    $con['vote']['endtime'] = time()+$CONFIG[maxvotetime];
    $con['vote']['inprogress'] = TRUE;
    $con['vote']['percenttopass'] = ".5";
    $con['vote']['channel'] = $channel;
    $con['vote']['yes'] = 0;
    $con['vote']['no'] = 0;
  }

  function vote_check( $hostmask, $name, $channel, $text )
  {
    global $CONFIG, $con, $bot, $other;
    
    //If a vote isn't in progress or we're trying to vote
    if( $con['vote']['inprogress'] != TRUE || $name == $CONFIG[nick] )
      return;

    if( stripos( $text, "F1" ) !== FALSE || stripos( $text, "F2" ) !== FALSE )
    {
      if( stripos( $text, "F1" ) !== FALSE && stripos( $text, "F2" ) !== FALSE )
      {
          $bot->talk( $name,  "A tad bit indecisive, aren't we?" );
          return;
      }
       
      if( stripos( $text, "F1" ) !== FALSE )
        $vote = "yes";
      else
        $vote = "no";

      //Run them through checks
      //
      //Have we already voted? 
      for( $i=0;$i<count($con['vote']['voters']);$i++)
      {
        if( $con['vote']['voters'][$i] == $hostmask )
        {
          $bot->talk( $name,  "You've already voted" );
          return;
        }
      }
      $con['vote']['voters'][] = $hostmask;
      if( $vote == "yes" )
        $con['vote']['yes']++;
      else
        $con['vote']['no']++;
      $bot->talk( $name, "Vote cast" );
    }
    
    if( $con['vote']['endtime'] < time() )
    {
      $totalvotes = $con['vote']['yes'] + $con['vote']['no'];
      $yeses = $con['vote']['yes'];
      $noes = $con['vote']['no'];
      if( $totalvotes == 0 )
        $totalvotes = 1;
      $yespercent = $con['vote']['yes']/$totalvotes;
      if( $yespercent > $con['vote']['percenttopass'] )
        $bot->talk( $con['vote']['channel'], $bot->tremulous_replace_colors_irc("^2Vote Passed ^0(^2Y^0:$yeses ^1N^0:$noes, ".round(($yespercent*100))."%)"));
      else
        $bot->talk( $con['vote']['channel'], $bot->tremulous_replace_colors_irc("^1Vote Failed ^0(^2Y^0:$yeses ^1N^0:$noes, ".round(($yespercent*100))."%)"));
      $con['vote']['inprogress'] = FALSE;
    }
  }

  function help_parser( )
  {
    global $CONFIG, $con;
    if( stripos( $con['buffer']['all'], "is" ) !== FALSE 
        && stripos( $con['buffer']['all'], "are" ) !== FALSE )
      return;
    //TBA
  }

  function tremulous_replace_colors_irc( $in ) 
  {
    $in = preg_replace( "/\^1/", "04", $in );
    $in = preg_replace( "/\^0/", "14", $in );
    $in = preg_replace( "/\^8/", "14", $in );
    $in = preg_replace( "/\^2/", "09", $in );
    $in = preg_replace( "/\^3/", "08", $in );
    $in = preg_replace( "/\^4/", "12", $in );
    $in = preg_replace( "/\^5/", "11", $in );
    $in = preg_replace( "/\^6/", "13", $in );
    $in = preg_replace( "/\^7/", "15", $in );
    $in = preg_replace( "//", "", $in );
    return $in;
  }
  
  function snarf_url( $url )
  {
    global $other, $bot;
    if( stripos( $url, "http://" ) === FALSE && stripos( $url, "ftp://" ) === FALSE && stripos( $url, "https://" ) === FALSE )
      $url = "http://".$url;
    
    $head = get_headers( $url, 1 );
    $type = $head[ "Content-Type" ];
    if( is_array( $type ) )
    {
      for( $i = 0; $i < count( $type ); $i++ )
      {
        if( stripos( $type[$i], "text/html" ) !== FALSE )
        {
          $type = "text/html"; 
          break;
        }
      }
    }
    else if( stripos( $type, "text/html" ) !== FALSE && $type != "text/html" )
      $type = "text/html";
    $type = rtrim( $type, ";" );
    $size = $head[ "Content-Length" ];
    if( $size == NULL && $type != "text/html" )
      $size = "unknown";
    
    if( stripos( $head[0], "403" ) !== FALSE )
      $title[0] = "403, Forbidden";
    else if( stripos( $head[0], "404" ) !== FALSE )
      $title[0] = "404, File Not Found";
    else if( stripos( $head[0], "401" ) !== FALSE )
      $title[0] = "401, Unauthorized";
    else if( stripos( $head[0], "405" ) !== FALSE )
      $title[0] = "405, Method Not Allowed";
    else if( stripos( $head[0], "408" ) !== FALSE )
      $title[0] = "408, Request Timeout";
    else if( stripos( $type,  "text/html" ) === FALSE && stripos( $type, "text/xhtml" ) === FALSE && $size != "unknown" )
      $title[0] = $type." file (".$other->filesize_range( $size ).")";
    else if(  stripos( $type,  "text/html" ) === FALSE && stripos( $type, "text/xhtml" ) === FALSE && $size == "unknown" )
      $title[0] = $type." file (".$size.")";
    else
    {
      $urlf = file( $url );
      if( $urlf == NULL )
        return;
      
      $i = preg_grep( "/<title>.*<\/title>/i", $urlf );
      $i = array_values( $i );
      $i = $i[0];
        
      $title = explode( "<title>", $i );
      $title = explode( "</title>", $title[1] );
    }

    //Our title will be the first value of $title
    $title = html_entity_decode( strip_tags( $title[0] ) );
    unset( $i, $urlf, $url );
    return $title;
  }
  
  function urban_lookup( $term, $num )
  {
    
    $page = ceil( $num/7 );
    $term = explode( " ", $term );
    $term = implode( "%20", $term );
    $web = file( "http://www.urbandictionary.com/define.php?page=$page&term=$term" );
    $test = preg_grep( "/".preg_quote("but these are close:")."/i", $web );
    $test2 = preg_grep( "/".preg_quote("isn't defined yet")."/i", $web );
    
    if( count( $test ) > 0 || count( $test2 ) > 0 )
      return( "Definition $num of $term not found!" );
    $web = implode( "\n", $web );
    $def1 = explode( "<div class='definition'>", $web );
    for( $i=0; $i<count($def1); $i++ )
    {
      $def2[$i] = explode( "</div>", $def1[$i] );
    }
    for( $i=1; $i<count($def2); $i++ )
    {
      $def3[$i] = $def2[$i][0];
      $def3[$i] = explode( "<br/>", $def3[$i] );
      for( $k=0; $k<count($def3[$i]); $k++ )
        $def3[$i][$k] = html_entity_decode( strip_tags( $def3[$i][$k] ) );
      
      $defs[$i] = $def3[$i];
    }
    unset( $web, $def1, $def2, $def3 );
    
    if( $num > 7 )
      $lnum = $num % 7;
    else
      $lnum = $num;
      
    for( $i=1; $i<=count($defs); $i++ )
    {
      if( $i == $lnum )
      {
        //$defs[$i][$k]["COUNT"] = $defsc;
        return( $defs[$i] );
      }
    }
    $term = explode( "%20", $term );
    $term = implode( " ", $term );
    $var[0] = "Definition #$num of $term not found!";
    return( $var );
  }
  function server_check( $serverarray, $channel, $set )
  {
    global $CONFIG, $con, $bot, $other, $serverstatus;
    
    for( $i=0;$i<count($serverarray);$i++ )
    {
      $ip = $serverarray[$i]['ip'];
      $port = $serverarray[$i]['port'];
      $backupname = $serverarray[$i]['bakname'];
      $serverp = $bot->tremulous_get_players( $ip, $port );
      $serveri = $bot->get_server_settings( $ip, $port );
      $servername = $serveri['servername'];
      $map = $serverp['map'];
      $players = count( $serverp[ alien_players ]  ) + count( $serverp[ spec_players ]  ) + count( $serverp[ human_players ]  );
      $aliens = count( $server[ alien_players ] );
      $humans = count( $server[ human_players ] );
      $specs = count( $server[ spec_players ] );
      $activeplayers = $aliens + $humans;
      $players = $aliens + $humans; $specs;
      $maxplayers = $serveri[ 'maxplayers' ];
      $averageping = $bot->average_ping( $serverp );
      if( $servername == "" )
        $servername = $backupname;
      if( $map == NULL && $maxplayers == NULL && $serverp == NULL && $serveri == NULL )
      {
        $status[$i] = "^1OFFLINE";
      }
      else if( $averageping >= 999 && $map != "" && $players >= 3 )
      {
        $status[$i] = "^1CRASHED";
      }
      else
        $status[$i] = "^2ONLINE";
    }
    
    $count = 0;
    for( $i=0; $i<count($status); $i++ )
    {
      if( $serverstatus[$set][$i]['status'] == NULL )
        $serverstatus[$set][$i]['status'] = $status[$i];
      else if( $status[$i] != $serverstatus[$set][$i]['status'] )
      {
        if( $status[$i] == "^1OFFLINE" || $status[$i] == "^2ONLINE" )
          $message[$count] = $bot->tremulous_replace_colors_irc( $serverarray[$i]['bakname'] )." is now ".$bot->tremulous_replace_colors_irc( $status[$i] )."";
        else
          $message[$count] = $bot->tremulous_replace_colors_irc( $serverarray[$i]['bakname'] )." just now ".$bot->tremulous_replace_colors_irc( $status[$i] )."";
        $count++;
        $serverstatus[$set][$i]['status'] = $status[$i];
      }
    }
    for( $i=0; $i<count($message); $i++ )
      $bot->talk( $channel, $message[$i] );
  }
  
  function weather( $locale )
  {
    global $bot, $other;
    if( is_int( $locale ) )
      $weather = file( "http://www.weatherunderground.com/cgi-bin/findweather/getForecast?query=$locale" );
    else
    {
      $locale = explode( " ", $locale );
      $locale = implode( "+", $locale );
      
      $weather = file( "http://www.wunderground.com/cgi-bin/findweather/hdfForecast?query=$locale&searchType=WEATHER" );
    }
    for( $i=0; $i<count($weather); $i++ )
    {
      if( $place == NULL && stripos( trim( $weather[$i] ), "<td class=\"nobr full\">" ) !== FALSE )
        $place = strip_tags( $weather[$i+1] );

      if( $humidity == NULL && stripos( trim( $weather[$i] ), "<td>Humidity:</td>" ) !== FALSE )
        $humidity = strip_tags( $weather[$i+1] );
        
      if( $wind == NULL && stripos( trim( $weather[$i] ), "<td>Wind:</td>" ) !== FALSE )
        $wind = strip_tags( $weather[$i+3] );
        
      if( $pressure == NULL && stripos( trim( $weather[$i] ), "<td>Pressure:</td>" ) !== FALSE )
        $pressure = strip_tags( $weather[$i+3] );
        
      if( $clouds == NULL && stripos( trim( $weather[$i] ), "<td class=\"vaT\">Clouds:</td>" ) !== FALSE )
        $clouds = strip_tags( $weather[$i+3] );

      if( $advisories == NULL && stripos( trim( $weather[$i] ), "<td class=\"full\">" ) !== FALSE
          && stripos( trim( $weather[$i+1] ), "Active Advisory" ) !== FALSE )
        $advisories = strip_tags( $weather[$i+2] );
        
      if( $temp == NULL && stripos( trim( $weather[$i] ), "<div style=\"font-size: 17px;\"><span class=\"pwsrt\"" ) !== FALSE )
        $temp = strip_tags( $weather[$i+1] );
        
    }

    $wind = $other->replace( "&nbsp;", " ", $wind );
    $pressure = $other->replace( "&nbsp;", " ", $pressure );
    $temp = $other->replace( "&nbsp;", " ", $temp );
    $temp = $other->replace( "&#176;", "", $temp );
    $advisories = $other->replace( "&nbsp;", "", $advisories );

    //PACK!
    $out['wind'] = trim( $wind );
    $out['pressure'] = trim( $pressure );
    $out['advisories'] = trim( $advisories );
    $out['clouds'] = trim( $clouds );
    $out['humidity'] = trim( $humidity );
    $out['place'] = trim( $place );
    $out['temp'] = trim( $temp );

    return( $out );
  }
  
  function readdata( $datavar )
  {
    global $CONFIG, $con, $bot;
    
    $fh = fopen( $CONFIG[datafilelocation], "r" );
    $newdata = fread( $fh, filesize( $CONFIG[datafilelocation] ) );
    fclose( $fh );
    
    if( $newdata == serialize( $datavar ) )
      return( $datavar );

    $newdata = unserialize( $newdata );
    
    if( $datavar['time'] != NULL && $newdata['time'] != NULL )
    {
      if( $newdata['time'] <= $datavar['time'] )
        echo "Our data is newer?";
      else
        $datavar = $newdata;
    }
    else 
      $datavar = $newdata;

    $con['data'] = $datavar;
    return( $datavar );
  }
  
  function writedata( $datavar )
  {
    global $CONFIG, $bot, $con;
    
    $fh = fopen( $CONFIG[datafilelocation], "r" );
    $olddata = fread( $fh, filesize( $CONFIG[datafilelocation] ) );
    fclose( $fh );
    
    if( $olddata == serialize( $datavar ) )
      return;
      
    $time = time();
    $con['data']['time'] = $time;
    $datavar['time'] = $time;
    $fh = fopen( $CONFIG[datafilelocation], "w" );
    fwrite( $fh, serialize( $datavar ) );
    fclose( $fh );
  }

  function tremulous_getserverlist( )
  {
    $server = "master.tremulous.net";
    $port = 30710;
    $shortserver = "";
    //$status_str = "xxxxgetservers 69 full empty\n";
    $status_str = "xxxxgetservers 69 full\n";
    $return_str = "xxxxgetserversResponse";
    $end_str = "EOTxxx";
    for( $i = 0; $i < 4; $i++ ) $status_str[$i] = pack("v", 0xff);
    for( $i = 0; $i < 4; $i++ ) $return_str[$i] = pack("v", 0xff);
    for( $i = 3; $i < 6; $i++ ) $end_str[$i] = pack("v", 0x00);

    $fp = fsockopen( "udp://".$server, $port, $errno, $errstr, 2 );   // Opens connection to server
    socket_set_timeout( $fp, 1 );  // Socket Timeout

    if (!$fp)
    {
      echo "$errstr ($errno)<br>\n";
    }
    else
    {
      $i = fwrite( $fp, $status_str );

      // Read all available data from socket
      do
      {
        $data = fread( $fp, 8192 );
        if( strlen( $data ) == 0 )
          break;
        $packets[] = $data;
      } while( true );
      foreach( $packets as $packet )
      {
        $servers_raw = explode( "\\", $packet );
        foreach( $servers_raw as $server_raw )
        {
          if( $return_str == $server_raw )
            continue;
          if( $end_str == $server_raw )
            continue;
          if( strlen( $server_raw ) != 6 )
          {
            if( strlen( $shortserver ) > 0 )
            {
              $server_raw = $shortserver."\\".$server_raw;
              $shortserver = "";
            }
            else
            {
              $shortserver = $server_raw;
              continue;
            }
          }
          //get the port
          $tempstring = substr( $server_raw, 4, 2 );
          $tempint = unpack( "nint", $tempstring );
          $serverport = $tempint['int'];
          
          //get the ip
          $tempstring = substr ( $server_raw , 0, 4 );
          $serverip = ord( $tempstring[0] ).".".ord( $tempstring[1] ).".".ord( $tempstring[2] ).".".ord( $tempstring[3] );
          $server_list[]= array( 'ip' => $serverip, 'port' => $serverport );
        }
      }
    }
    return $server_list;
  }
}  
?>
