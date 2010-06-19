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
*/

function tremulous_strip_colors( $in ) 
{
  $in = preg_replace( "/\^.{1}/", "", $in );
  $in = preg_replace( "//", "", $in );
  return $in;
}

function tremulous_get_players( $server, $port, $cbf=FALSE ) 
{
  global $bstatus;
  
  $fp = fsockopen( "udp://".$server, $port, $errno, $errstr, 0 );
  if( !$fp )
    return;
  
  $bstatus['trem']++;
  $status_str = "xxxxgetstatus";
  for($i=0;$i<4;$i++) $status_str[$i] = pack("v", 0xff);
  fwrite($fp, $status_str);
  socket_set_timeout( $fp, 0, 10000 );
  do
  {
    $data = fread( $fp, 8192 );
    if( strlen( $data ) == 0 )
      break;
    $packets[] = $data;
  } while( true );
  $data_full = implode( $packets );
  $data_full = substr( $data_full, 19 );
  $server_data = explode( "\\", $data_full );
  $data = explode("\n", $data_full);
  fclose($fp);
  for( $i=1; $i<count( $server_data ); $i+=2 )
  {
    $server_status[$server_data[$i]] = $server_data[$i+1];
    if( $server_data[$i] == "mapname" )
      $map = $server_data[$i+1];
    else if( $server_data[$i] == "P" )
      $P = str_replace( "-", "", $server_data[$i+1] );
    else if( $server_data[$i] == "sv_hostname" )
      $info[ 'servername' ] = trim( tremulous_replace_colors_irc( $server_data[ $i + 1 ] ) ); 
    else if( $server_data[$i] == "sv_maxclients" )
      $maxclients = $server_data[ $i + 1 ];
    else if( $server_data[$i] == "sv_privateClients" || $server_data[$i] == "sv_privateclients" )
      $privateclients = $server_data[ $i + 1 ];
    else if( $server_data[$i] == "sv_democlients" )
      $democlients = $data_full[ $i + 1 ];
  }
  $info[ 'maxplayers' ] = $maxclients-$privateclients-$democlients;

  $i = 1;
  next($data); // skip settings
  while(list(,$p) = each($data)) {
    if(preg_match("/^(-\d+|\d+) (\d+) \"(.*)\"$/", $p, $m)) {
      $pinfo = array( 
        "kills" => $m[1],
        "ping" => $m[2],
        "raw_name" => $m[3],
        "name" => tremulous_strip_colors($m[3]),
        "colored_name" => 
          tremulous_replace_colors_irc($m[3]));
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
  return array(
    "alien_players" => $alien,
    "human_players" => $human,
    "spec_players" => $spec,
    "map" => $map,
    "maxplayers" => $info[ 'maxplayers' ],
    "servername" => $info[ 'servername' ]
    );
}
function find_player( $name, $which="bot" )
{
  $ipandport = find_servers( );
  $name = preg_quote( $name ); //Search for everything
  for( $i=0; $i < count( $ipandport ); $i++ )
  {
    if( $which == "bot" )
      $server[ $i ] = tremulous_get_players( $ipandport[ $i ][ 0 ], $ipandport[ $i ][ 1 ] );
    else
      $server[ $i ] = tremulous_get_players( $ipandport[ $i ][ 0 ], $ipandport[ $i ][ 1 ] );

    if( !$server )
      continue;
    for( $h=0; $h < count( $server[ $i ][ alien_players ] ); $h++ )
    {
      if( preg_match( "/$name/i", $server[ $i ][ alien_players ][ $h ][ 'name' ] ) )
      {
        $currid = count( $found );
        $found[ $currid ]['name'] = $server[ $i ][ alien_players ][ $h ][ 'colored_name' ];
        $found[ $currid ]['kills'] = $server[ $i ][ alien_players ][ $h ][ 'kills' ];
        $found[ $currid ]['ping'] = $server[ $i ][ alien_players ][ $h ][ 'ping' ];
        $found[ $currid ]['team'] = "aliens";
        $found[ $currid ]['server'] = $server[ $i ]['servername'];
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
        $found[ $currid ]['server'] = $server[ $i ]['servername'];
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
        $found[ $currid ]['server'] = $server[ $i ]['servername'];
      }
    }
  }
  return $found;
}

function find_servers( )
{
  $ipsandports = tremulous_getserverlist( );
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

function tremulous_rcon( $server, $port, $command, $rcon, $cbf = FALSE, $colors = TRUE, $lastline = FALSE ) 
{
  global $bstatus;
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
      $out = tremulous_rcon( $server, $port, $command, $rcon, "TRUE" );
      echo "Retrying, no data recieved...\n";
      if( $out != NULL )
        return tremulous_replace_colors_irc( $out );
      $tries++;
    }
  }*/
  $data_full = implode( "\n", $data_full );
  $data_full = substr( $data_full, 10 );

  $data = explode( "\n", $data_full );
  
  if( !$lastline )
    unset( $data[ count( $data ) - 1 ] );
  if( $colors )
    return tremulous_replace_colors_irc( $data );
  else
    return $data;
}

function svnmon( )
{
  global $con, $CONFIG, $svnservers;

  if( $CONFIG[svnmon] == "FALSE" || count( $svnservers ) <= 0 )
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
     $con['nextsvnmontime'] += 60;
     continue;
   }
   $tries = 0;

    if( $svnout[1] == $thissvnlog )
      continue;
    else
    {
      $svnout2 = $svnout;
      $svnlogout[$c] = $svnout[1];
      $con['data'][svnstuffs][$c] = $svnout[1];
      
      writedata( $con['data'] );

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
      
      for( $i=0; $i<count( $con['data'][channels] ); $i++ )
      {
        if( $con['data'][channels][$i]['svnmon'] == FALSE )
          continue;
          
        talk( $con['data'][channels][$i]['name'], $svnservers[$c]['name']." SVN update:" );
        for( $j=0; $j<count($string); $j++ )
        {
          if( $string[$j] == "\n" || $string[$j] == NULL )
            continue;
          talk( $con['data'][channels][$i]['name'], $string[$j] );
        }
      }
      /*for( $j=0; $j<count($CONFIG['servers']['KOR']); $j++ )
      {
        unset( $thisserver );
        $thisserver = $CONFIG['servers']['KOR'][$j];
        tremulous_rcon( $thisserver['ip'], $thisserver['port'], "!print ".$svnservers[$c]['name']." SVN Update:", $thisserver['rcon'] );
        for( $k=0; $k<count($string); $k++ )
        {
          if( $string[$k] == "\n" || $string[$k] == NULL )
            continue;
          tremulous_rcon( $thisserver['ip'], $thisserver['port'], "!print ".$string[$k], $thisserver['rcon'] );
        }
      }*/
    }
  }
}

function hgmon( )
{
  global $con, $hgservers;
  
  define( "MAX_REPORT", 10 );
  if( count( $hgservers ) <= 0 )
    return;
  //max number of commits at a time
  for( $i = 0; $i < count( $hgservers ); $i++ )
  {
    //here for a reason!
    $thisdata = $con['data'][hgstuffs][$i];

    //check stuff
    exec( "cd ".$hgservers[$i]['loc']." && hg pull -u" );
    exec( "hg log -l1 -M --template \"{rev} ||| {node|short} ||| {author} ||| {branches} ||| {desc}\" ".$hgservers[$i]['loc'], $whatitsays );
    
    $whatitsays = implode( "<br />", $whatitsays );
    
    if( $whatitsays == $thisdata )
      continue;
    
    $con['data'][hgstuffs][$i] = $whatitsays;
    writedata( $con['data'] );
    
    for( $k = 1; $k < MAX_REPORT; $k++ )
    {
      unset( $stufftoreport );
      exec( "hg log -l$k -M --template \"{rev} ||| {node|short} ||| {author} ||| {branches} ||| {desc}<cibr>\" ".$hgservers[$i]['loc'], $stufftoreport );
      if( is_array( $stufftoreport ) )
        $stufftoreport = implode( "<br />", $stufftoreport );
      if( stripos( $stufftoreport, $thisdata ) !== FALSE )
        break;
    }
    if( $k == MAX_REPORT )
    {
      //this is our first time
      echo "Initial setup on hgmon detected.\n";
      continue;
    }
    
    //$stufftoreport = implode( "<br />", $stufftoreport );
    $stufftoreport = explode( "<cibr>", $stufftoreport );
    
    for( $k = 0; $k < count( $stufftoreport )-2; $k++ )
    {
      $thiscommit = explode( " ||| ", $stufftoreport[$k] );
      $rev = $thiscommit[0];
      $cset = $thiscommit[1];
      $author = $thiscommit[2];
      $branches = $thiscommit[3];
      if( $branches == NULL )
        $branches = "default";
      $descr = $thiscommit[4];
      $descr = explode( "<br />", $descr );
      
      //go through the channels and see if they want to hear from us
      for( $j = 0; $j < count( $con['data'][channels] ); $j++ )
      {
        if( $con['data'][channels][$j]['hgmon'] != TRUE )
          continue;
        
        if( $k == 0 )
          talk( $con['data'][channels][$j]['name'], $hgservers[$i]['name']." HG update:" );
        talk( $con['data'][channels][$j]['name'], "$author * $branches * r$rev:$cset " );
        if( is_array( $descr ) )
        {
          for( $l = 0; $l < count( $descr ); $l++ )
            talk( $con['data'][channels][$j]['name'], $descr[$l] );
        }
        else
          talk( $con['data'][channels][$j]['name'], $descr );
      }
    }
  }
}

function cmd_send( $command, $end = FALSE, $now = FALSE )
{
  global $con, $time, $CONFIG, $buffers, $bstatus;
  
  if( strlen( $command ) > $CONFIG[maxstring] && !$now )
  {
    echo "$command exceeded max string length\n\r";
    return;
  }
  if( !$end )
    fputs( $con['socket'], $command."\n\r" );
  else
    fputs( $con['socket'], $command );
  $con['stimes']++;
  $con[lastspeaktime] = time();
  $bstatus['commandstoserv']++;
  print ( date("[ m/d/y @ H:i:s ]") ."-> ". $command. "\n\r" );
  return;
}

function runbuffers( )
{
  global $con, $time, $buffers, $CONFIG, $bstatus;
  
  if( time() - $con[lastspeaktime] >= $CONFIG[chatspeaktimeout] && count( $buffers ) > 0 )
  {
    fputs( $con['socket'], $buffers[0]."\n\r" );
    print ( date("[ m/d/y @ H:i:s ][ BUF ]") ."-> ".$buffers[0]."\n\r" );
    unset( $buffers[0] );
    $buffers = array_values( $buffers );
    $con[lastspeaktime] = time();
    $con[stimes]++;
    $bstatus['talked']++;
  }
  else if( time() - $con[lastspeaktime] >= $CONFIG[chatspeaktimeout] + 3  && $con[stimes] > 0 )
    $con[stimes] = 0;
}

function talk( $channel, $text, $now=FALSE )
{
  global $con, $CONFIG, $buffers, $bstatus;
  
  if( strlen( $text ) > $CONFIG[maxstring] )
  {
    $text = str_split( $text, $CONFIG[maxstring] );
          
    if( time() - $con[lastspeaktime] >= $CONFIG[chatspeaktimeout] || $now )
    {
      for( $i=1; $i<count( $text ); $i++ )
        $buffers[count($buffers)] = "PRIVMSG $channel :".$text[$i];
      $text = $text[0];
    }
    else
    {
      for( $i=0; $i<count( $text ); $i++ )
        $buffers[count($buffers)] = "PRIVMSG $channel :".$text[$i];
    }
  }
    
  if( time() - $con[lastspeaktime] >= $CONFIG[chatspeaktimeout] || $con[stimes] < 3 || $now || !$CONFIG[chatspeaktimeout] )
  {
    if( $con['name'] == $CONFIG[nick] )
      return; //lets not and say we did
    fputs( $con['socket'], "PRIVMSG $channel :".$text."\n\r" );
    $con[stimes]++;
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
  global $con, $CONFIG;
  if( stripos( $con['buffer']['all'], "KICK ") === FALSE )
    return;
  $channelz = stripos( $con['buffer']['all'], "KICK ")+5;
  $channellen = stripos( $con['buffer']['all'], " ".$CONFIG['name']." :")-$channelz;
  $channel = substr( $con['buffer']['all'], $channelz, $channellen );
  for( $i=0; $i < count( $con['data'][channels] ); $i++ )
  {
    if( $channel == $con['data'][channels][$i]['name'] )
    {
      $chanid = $i;
      break;
      }
  }  
  if( stripos( $con['buffer']['all'], 'KICK '.$channel.' '.$CONFIG['name'].' :' ) !== FALSE )
    cmd_send( "JOIN :".$con['data'][channels][$chanid]['name']." ".$con['data'][channels][$chanid]['password'] );
}

function check_admin( $hostmask )
{
  global $con;
  
  $admins = $con['data'][admins];
  for( $i=0;$i<count($admins);$i++ )
  {
    if( stripos( $admins[$i], "!" ) !== FALSE && stripos( $hostmask, "!" ) === FALSE )
    {
      $admins[$i] = explode( "!", $admins[$i] );
      $admins[$i] = $admins[$i][1];
    }
    else if( stripos( $admins[$i], "!" ) === FALSE && stripos( $hostmask, "!" ) !== FALSE )
    {
      $hostmask = explode( "!", $hostmask );
      $hostmask = $hostmask[1];
    }
    if( $hostmask == $admins[$i] )
      return TRUE;
    //echo "Comparing $hostmask to ".$admins[$i]."\n";
  }
  
  return FALSE;
}

function call_vote( $string, $type, $caller, $channel )
{
  global $CONFIG, $con;
  
  //Check to see if we support this vote
  if( $type != "poll" && $type != "kick" && $type != "mute" )
  {
    talk( $channel, "$caller: Sorry, but I don't recognize the vote type '$type'." );
    return;
  }
  //If there's already a vote being called
  if( $con['vote']['inprogress'] == TRUE )
  {
    talk( $channel, "$caller: Sorry, but a vote is already in progress." );
    return;
  }
  
  if( $type == "poll" )
  {
    talk( $channel, "$caller called a vote: [POLL] \"$string\"" );
    $votestring = NULL;
  }
  else if( $type == "kick" )
  {
    $stringex = explode( " ", $string );
    $target = $stringex['0'];
    unset( $stringex['0'] );
    $string = implode( " ", $stringex );
    //Check to see if the target is in the channel
    cmd_send( "NAMES $channel" );
    if( stripos( $target, $CONFIG[nick] ) !== FALSE )
    {
      $target = $caller;
      $string = "I guess the joke is on you ~".$CONFIG[nick];
    }
    $names = fetch_next_message( );
    if( $string != NULL )
      $votestring = "KICK $channel $target :vote kick: $string";
    else
      $votestring = "KICK $channel $target :vote kick: no reason";
    
    if( stripos( $names, $target ) === FALSE )
    {
      talk( $channel, "I don't believe $target is in this channel." );
      return;
    }
    else if( stripos( $names, "@".$CONFIG[nick] ) === FALSE )
    {
      talk( $channel, "I don't have operator status in this channel." );
      return;
    }
    if( $string )
      talk( $channel, "$caller called a vote: Kick $target with reason \"$string\"" );
    else
      talk( $channel, "$caller called a vote: Kick $target" );
  }
  else if( $type == "mute" )
  {
    $stringex = explode( " ", $string );
    $target = $stringex['0'];
    unset( $stringex['0'] );
    $string = implode( " ", $stringex );
    //Check to see if the target is in the channel
    cmd_send( "NAMES $channel" );
    if( stripos( $target, $CONFIG[nick] ) !== FALSE )
      $target = $caller;
    $names = fetch_next_message( );
    $votestring = "MODE $channel -v $target";
    
    if( stripos( $names, $target ) === FALSE )
    {
      talk( $channel, "I don't believe $target is in this channel." );
      return;
    }
    else if( stripos( $names, "@".$CONFIG[nick] ) === FALSE )
    {
      talk( $channel, "I don't have operator status in this channel." );
      return;
    }
    if( $string )
      talk( $channel, "$caller called a vote: Mute $target with reason \"$string\"" );
    else
      talk( $channel, "$caller called a vote: Mute $target" );
  }
  
  talk( $channel, "Type F1 (yes) or F2 (no) in this channel, or send me it in a CTCP ping / private message to vote." );
  
  unset( $con['vote'] );
  $con['vote']['starttime'] = time();
  $con['vote']['endtime'] = time()+$CONFIG[maxvotetime];
  $con['vote']['inprogress'] = TRUE;
  $con['vote']['percenttopass'] = ".5";
  $con['vote']['channel'] = $channel;
  $con['vote']['string'] = $votestring;
  $con['vote']['yes'] = 0;
  $con['vote']['no'] = 0;
}

function vote_check( $hostmask, $name, $channel, $text )
{
  global $CONFIG, $con;
  
  //If a vote isn't in progress or we're trying to vote
  if( $con['vote']['inprogress'] != TRUE || $name == $CONFIG[nick] )
    return;

  if( stripos( $text, "F1" ) !== FALSE || stripos( $text, "F2" ) !== FALSE )
  {
    if( stripos( $text, "F1" ) !== FALSE && stripos( $text, "F2" ) !== FALSE )
    {
        talk( $name,  "A tad bit indecisive, aren't we?" );
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
        talk( $name,  "You've already voted" );
        return;
      }
    }
    $con['vote']['voters'][] = $hostmask;
    if( $vote == "yes" )
      $con['vote']['yes']++;
    else
      $con['vote']['no']++;
    talk( $name, "Vote cast" );
  }
}

function run_votes( )
{
  global $con;
  
  if( !$con['vote']['inprogress'] )
    return;
  if( $con['vote']['endtime'] < time() )
  {
    $totalvotes = $con['vote']['yes'] + $con['vote']['no'];
    $yeses = $con['vote']['yes'];
    $noes = $con['vote']['no'];
    if( $totalvotes == 0 )
      $totalvotes = 1;
    $yespercent = $con['vote']['yes']/$totalvotes;
    if( $yespercent > $con['vote']['percenttopass'] )
      talk( $con['vote']['channel'], tremulous_replace_colors_irc("^2Vote Passed ^0(^2Y^0:$yeses ^1N^0:$noes, ".round(($yespercent*100))."%)"));
    else
      talk( $con['vote']['channel'], tremulous_replace_colors_irc("^1Vote Failed ^0(^2Y^0:$yeses ^1N^0:$noes, ".round(($yespercent*100))."%)"));
    $con['vote']['inprogress'] = FALSE;
    if( $con['vote']['string'] != NULL && $yespercent > $con['vote']['percenttopass'] )
      cmd_send( $con['vote']['string'] );
    unset( $con['vote'] );
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

function snarf_url( $url, $dead=FALSE )
{
  if( stripos( $url, "http://" ) === FALSE && stripos( $url, "ftp://" ) === FALSE && stripos( $url, "https://" ) === FALSE )
    $url = "http://".$url;
  
  if( !$dead )
  {
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
    }
  
  if( stripos( $head[0], "403" ) !== FALSE )
    $title[0] = "403, Forbidden";
  else if( stripos( $head[0], "404" ) !== FALSE )
    $title[0] = "404, File Not Found";
  else if( stripos( $head[0], "401" ) !== FALSE )
    $title[0] = "401, Unauthorized";
  else if( stripos( $head[0], "405" ) !== FALSE )
    $title[0] = "405, Method Not Allowed";
  else if( $head == NULL || count( $head ) <= 2 || $dead )
    $title[0] = "Unable to connect";
  else if( stripos( $head[0], "408" ) !== FALSE  )
    $title[0] = "408, Request Timeout";
  else if( stripos( $type,  "text/html" ) === FALSE && stripos( $type, "text/xhtml" ) === FALSE && $size != "unknown" )
    $title[0] = $type." file (".filesize_range( $size ).")";
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
  $title = replace( "&bull;", "•", $title );
  
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
  global $CONFIG, $con, $serverstatus;
  
  for( $i=0;$i<count($serverarray);$i++ )
  {
    $ip = $serverarray[$i]['ip'];
    $port = $serverarray[$i]['port'];
    $backupname = $serverarray[$i]['bakname'];
    $server = tremulous_get_players( $ip, $port );
    $servername = $server['servername'];
    $map = $server['map'];
    $players = count( $server[ alien_players ]  ) + count( $server[ spec_players ]  ) + count( $server[ human_players ]  );
    $aliens = count( $server[ alien_players ] );
    $humans = count( $server[ human_players ] );
    $specs = count( $server[ spec_players ] );
    $activeplayers = $aliens + $humans;
    $players = $aliens + $humans; $specs;
    $maxplayers = $server[ 'maxplayers' ];
    $averageping = average_ping( $server );
    if( $servername == "" )
      $servername = $backupname;
    if( $map == NULL && $maxplayers == NULL && $server == NULL )
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
        $message[$count] = tremulous_replace_colors_irc( $serverarray[$i]['bakname'] )." is now ".tremulous_replace_colors_irc( $status[$i] )."";
      else
        $message[$count] = tremulous_replace_colors_irc( $serverarray[$i]['bakname'] )." just now ".tremulous_replace_colors_irc( $status[$i] )."";
      $count++;
      $serverstatus[$set][$i]['status'] = $status[$i];
    }
  }
  for( $i=0; $i<count($message); $i++ )
    talk( $channel, $message[$i] );
}

function weather( $locale )
{  
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

  $wind = replace( "&nbsp;", " ", $wind );
  $pressure = replace( "&nbsp;", " ", $pressure );
  $temp = replace( "&nbsp;", " ", $temp );
  $temp = replace( "&#176;", "", $temp );
  $advisories = replace( "&nbsp;", "", $advisories );

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
  global $CONFIG, $con;
  
  $fh = fopen( $CONFIG[datafilelocation], "r" );
  $newdata = fread( $fh, filesize( $CONFIG[datafilelocation] ) );
  fclose( $fh );
  
  if( $newdata == serialize( $datavar ) )
    return( $datavar );

  if( !is_array( unserialize( $newdata ) ) )
  {
    echo "Error! Old datafile is no longer an array, please remove it!\n";
    return( $datavar );
  }
  else if( $newdata == NULL )
  {
    echo "No old data file found, making new one...\n";
    return( $datavar );
  }
  $newdata = unserialize( $newdata );
  
  if( $datavar['time'] != NULL && $newdata['time'] != NULL )
  {
    if( $newdata['time'] <= $datavar['time'] )
      echo "Our data is newer?\n";
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
  global $CONFIG, $con;
  
  $fh = fopen( $CONFIG[datafilelocation], "r" );
  $olddata = fread( $fh, filesize( $CONFIG[datafilelocation] ) );
  fclose( $fh );
  
  if( $olddata == serialize( $datavar ) )
    return;
    
  $time = time();
  $datavar['time'] = $time;
  $fh = fopen( $CONFIG[datafilelocation], "w" );
  fwrite( $fh, serialize( $datavar ) );
  fclose( $fh );
  $con['data'] = $datavar;
}

function tremulous_getserverlist( )
{
  global $con;
  
  if( $con && $con['serverlistcache']['time'] + 120 <= time() )
    return( $con['serverlistcache']['con'] );
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

function fetch_next_message( )
{
  global $con, $bstatus;
  
  sleep( 1 ); //Wait a second for any recently sent commands to set in
  $con['buffer']['all'] = trim( fgets( $con['socket'], 4096 ) );
  if( $con['buffer']['all'] != NULL )
    print date("[ m/d/y @ H:i:s ]")."<- ".$con['buffer']['all'] ."\n";
  else
    return NULL;
  
  return( $con['buffer']['all'] );
}

function run_checks( )
{
  global $con;
  
  if( count( $con['checks'] ) == 0 )
    return;
    
  //run through our triggers and see if we meet any of them
  for( $i=0; $i<count( $con['checks'] ); $i++ )
  {
    if( stripos( $con['buffer']['all'], $con['checks'][$i]['trigger'] ) !== FALSE )
      eval( $con['checks'][$i]['command'] );
  }
}

function add_ignore( $ignoree, $time, $hostmask )
{
  global $con;
  
  //this function assumes all entered data is correct
  $c = count( $con['data'][ignore] );
  
  $exp = time()+$time;
  $con['data'][ignore][$c]['hostmask'] = $ignoree;
  $con['data'][ignore][$c]['expiration'] = $exp;
  
  writedata( $con['data'] );
}

function debug_message( $message, $force=FALSE )
{
  global $CONFIG;
  
  if( $CONFIG[debug] || $force )
    echo date("[ m/d/y @ H:i:s ]") ."<-DEBUG-> $message\n\r";
}

function check_file( $file, $shortname, $channel )
{
  global $con;
  if( !( time() % 5 ) )
    return;
  exec( "cat $file | tail", $contents );
  
  for( $i=0; $i<count( $contents ); $i++ )
  {
    if( $contents[$i] == $con['sess'][$shortname] )
    {
      $end = $i;
      break;
    }
    else if( $i+1 == count( $contents ) )
    {
      $con['sess'][$shortname] = $contents[$i];
      return;
    }
  }
  
  if( $i > 0 )
  {
    for( $i=0; $i<$end; $i++ )
      unset( $contents[$i] );
    $contents = array_values( $contents );
  }
  
  if( !$contents )
    return;
  
  for( $i=0; $i<count( $contents ); $i++ )
    talk( $channel, tremulous_replace_colors_irc( $contents[$i] ) );
}

function filesize_range( $filesize, $size = NULL )
{
  if( is_array( $filesize ) )
    return;
  
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
  
  if( $filesize >= 1024 && $filesize < pow( 1024, 2 ) )
    $out = round( ( $filesize/1024 ), 2 )." KiB";
  else if( $filesize >= pow( 1024, 2 ) && $filesize < pow( 1024, 3 ) )
    $out = round( ( $filesize / pow( 1024, 2 ) ), 2 )." MiB";
  else if( $filesize >= pow( 1024, 3 ) && $filesize < pow( 1024, 4 ) )
    $out = round( ( $filesize / pow( 1024, 3 ) ), 2 )." GiB";
  else if( $filesize >= pow( 1024, 4 ) )
    $out = round( ( $filesize / pow( 1024, 4 ) ), 2 )." TiB";
  else
    $out = $filesize." B";   
    
  return( $out );
}

function replace( $replacewhat, $withwhat, $inwhat )
{
  $inwhat = explode( $replacewhat, $inwhat );
  $inwhat = implode( $withwhat, $inwhat );
  return( $inwhat );
}

function connect_to_irc( )
{
  global $CONFIG, $con, $lasttime;
  if( is_int( $CONFIG[server] ) )
  {
    if( $CONFIG[server] == 0 )
      $CONFIG[server] = "irc.freenode.net";
    else if( $CONFIG[server] == 1 )
      $CONFIG[server] = "irc.quakenet.org";
  }
  $old = $CONFIG[server];
  $CONFIG[server] = gethostbyname( $CONFIG[server] );
  debug_message( "Resolved $old to ".$CONFIG[server] );
  $con['socket'] = fsockopen( $CONFIG[server], $CONFIG[port], $errno, $errstr, 1 );
  // Eh, I did this crud a long time ago... I doubt it's highly necessary
  stream_set_blocking( $con['socket'], 0 );
  stream_set_timeout( $con['socket'], 100 );
  //FIXME: this should be in $con
	$lasttime = time();
  usleep( $CONFIG[sleeptime] );
  cmd_send( "USER ". $CONFIG[nick] ." ".$CONFIG[vhost]." ".$CONFIG[vhost]." :". $CONFIG[name], FALSE, TRUE );
  cmd_send( "NICK ". $CONFIG[nick] ." ".$CONFIG[vhost], FALSE, TRUE );
}

function ping_server( )
{
  global $lasttime, $con;
  if( substr( $con['buffer']['all'], 0, 6 ) == 'PING :' )
  {
    cmd_send( 'PONG :'.substr( $con['buffer']['all'], 6 ), FALSE, TRUE );
    $lasttime = time();
  }
}

function identify( )
{
  global $CONFIG;
  
  if( $CONFIG[nickserv] == "NickServ" || $CONFIG[nickserv] == "Q@CServe.quakenet.org" )
  {
    if( $CONFIG[server] == 0 || $CONFIG[nickserv] == "NickServ" )
      cmd_send( "PRIVMSG NickServ :IDENTIFY ".$CONFIG[nick]." ".$CONFIG[nickpass], TRUE, TRUE );
    else if( $CONFIG[server] == 1 || $CONFIG[nickserv] == "Q@CServe.quakenet.org" )
      cmd_send( "PRIVMSG Q@CServe.quakenet.org :AUTH ".$CONFIG[nick]." ".$CONFIG[nickpass], TRUE, TRUE );
  }
  else
    cmd_send( "PRIVMSG ".$CONFIG[nickserv]." :AUTH ".$CONFIG[nick]." ".$CONFIG[nickpass], TRUE, TRUE );
}

function delete_admin( $offset )
{
  global $con;
  
  $offset--;
  if( $offset >= count( $con['data'][admins] ) || $offset < 0 || $offset == NULL )
    return "Invalid admin number.";
  else if( $con['data'][admins][$offset] == NULL )
    return "Invalid admin (???).";
  
  return "Admin at hostmask ".$con['data'][admins][$offset]." was removed";
  unset( $con['data'][admins][$offset] );
  $con['data'][admins][$offset] = array_values( $con['data'][admins][$offset] );
  writedata( $con['data'] );
}
?>
