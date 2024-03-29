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

require( "moobot.conf" );
require( "functions.php" );
require( "commands.php" );

debug_message( "Inital start of Moobot" );
//$dbcnx = mysql_connect( "localhost", $CONFIG[dbuser], $CONFIG[dbpass] );
//mysql_select_db( $CONFIG[dbname], $dbcnx );
$con = array();
$alive = TRUE;

while( $alive == TRUE )
{
  $bstatus['botstarttime'] = time();
  init();
  debug_message( "Exited main moobot function, restarting." );
}

function init()
{
  global $con, $CONFIG, $servers, $other, $bot, $bstatus, $commandtree, $commands, $lasttime; 
  
  debug_message( "Entering main moobot function." );
  $firsttime = TRUE;
  connect_to_irc( );
  debug_message( "Connected to main server, starting socket read loop." );
  while( !feof( $con['socket'] ) )
  {
    if( $con['buffer']['old'] != $con['buffer']['all'] )
      $con['buffer']['old'] = $con['buffer']['all'];
    else
    {
      $con['buffer']['all'] = trim( fgets( $con['socket'], 4096 ) );
      $con['buffer']['old'] = $con['buffer']['all'];
    }

    if( $con['buffer']['all'] != NULL )
      print date("[ m/d/y @ H:i:s ]")."<- ".$con['buffer']['all'] ."\n";

    $bstatus['lines']++;
    
    if( $firsttime == TRUE && ( stripos( $con['buffer']['all'], "/motd" ) !== FALSE 
        || stripos( $con['buffer']['all'], "MOTD File" ) !== FALSE )
        || stripos( $con['buffer']['all'], "End of message of the day" ) !== FALSE )
    {
      //
      //Loads data
      //
      $con['data'] = readdata( $con['data'] );
      //
      //
      //
      
      $max = count( $con['data'][channels] );
      for( $i=0; $i<$max; $i++ )
      {
        if( $con['data'][channels][$i]['active'] == FALSE )
          continue;
        if( $con['data'][channels][$i]['password'] != NULL )
          cmd_send( "JOIN ". $con['data'][channels][$i]['name'] ." ". $con['data'][channels][$i]['password'] );
        else
          cmd_send( "JOIN ". $con['data'][channels][$i]['name'] );
      }
      $firsttime = FALSE;
      if( $CONFIG[nickpass] != NULL )
        identify( );
        
      //cmd_send( "MODE ".$CONFIG[nick]." +x " );
    }

    if( $con['buff']['array'] == NULL )
    {
      if( $con['nextsvnmontime'] <= time() && $firsttime == FALSE )
      {
        debug_message( "Time for a SVN/HG check" );
        $con['nextsvnmontime'] = time() + $CONFIG[svnmontimeout]; 
        svnmon( );
        hgmon( );
        $bstatus['svnchecks']++;
      }
      
      if( $con['serverlistcache']['time'] + 120 <= time() )
      {
        debug_message( "Server list cache refresh time" );
        $con['serverlistcache']['con'] = tremulous_getserverlist( );
        $con['serverlistcache']['time'] = time();
      }

      //
      //checks are here!
      //
      runbuffers( );
      run_votes( );
      //check_file( "/srv/http/logs/serverlog", "serverlog", "#knightsofreason" );
      //check_file( "/srv/http/logs/adminlog", "adminlog", "#knightsofreason" );
      //check_file( "/srv/http/logs/maplog", "maplog", "#knightsofreason" );
      //server_check( $CONFIG['servers']['KOR'], "#knightsofreason", "KOR" );
      //vote_check( );
      //
      //
      // 

      //No text, so we skip everything else, as it is all dependant on text and causes warnings
      if( $con['buffer']['all'] == NULL
          && $con['buffer']['all'] != ":" )
      {
        usleep( $CONFIG[sleeptime]*1000 );
        continue;
      }
    }
      if( stripos( $con['buffer']['all'], $CONFIG[serverspam] ) !== FALSE )
        continue;

    ping_server( );

    //****************
    //
    //COMMANDS
    //
    //****************
    $start = strpos( $con['buffer']['all'], ":", 1 )+1;
    $text = substr( $con['buffer']['all'], $start );
    $bufarray = explode( " ", $con['buffer']['all'] );
    $channel = strtolower( $bufarray['2'] );
    $hostmaskchunk = ltrim( $bufarray['0'], ":" );
    $hostmaskchunk = explode( "!", $hostmaskchunk );
    $hostmask = $hostmaskchunk['1'];
    $name = $hostmaskchunk['0'];
    $text = $bufarray;
    for( $i=0; $i<3; $i++ )
      unset( $text[$i] );
    $text = array_values( $text );
    $textarray = $text;
    $text = implode( " ", $text );
    if( stripos( $text, ":%" ) === FALSE )
      $text = ltrim( $text, ":" );
    else
    {
      unset( $textarray['0'] );
      $textarray = array_values( $textarray );
    }
    if( $channel == strtolower( $CONFIG[nick] ) )         //Private Message
    {
      $channel = strtolower( $name );
      $pm = TRUE;
    }
    else
      $pm = FALSE;

    vote_check( $hostmask, $name, $channel, $text );

    if( stripos( $text, ":%" ) !== FALSE )
    {
      for( $i=0; $i<count($con['data'][channels]); $i++ )
      {
        if( strtolower( $channel ) == strtolower( $con['data'][channels][$i]['name'] ) )
        {
          $chanid = $i;
          break;
        }
        else if( $i == count( $con['data'][channels] ) - 1 )
          $chanid = "-1";
      }
      for( $i=0; $i<3; $i++ )
        unset( $bufarray[$i] );
      $command = ltrim( $bufarray['3'], ":%" );
      unset( $bufarray['3'] );
      $bufarray = array_values( $bufarray );
      if( $chanid != -1 && $con['data'][channels][$chanid]['cmds'] == FALSE 
          && $command != "ccmds" && $pm == FALSE )		//hax
        continue;
      $bstatus['cmds']++;
      $eval = FALSE;

      for( $i=0; $i<count($commandtree); $i++ )
      {
        if( $commandtree[$i][0] == $command )
        {
          if( $commandtree[$i][2] == TRUE && !check_admin( $hostmask ) )
          {
            talk( $channel, $name.": You do not have permission to use that command." );
            $eval = TRUE;
            break;
          }
          else
          {
            debug_message( "Evaluating command \"".$command."\"" );
            eval( $commandtree[$i][1] );
            $eval = TRUE;
            break;
          }
        }
      }
      
      if( !$eval && $command != NULL )
        talk( $channel, "%$command is not known, try using %help." );
    }
     else if( stripos( $con['buffer']['all'], "(Nick collision from services.)" ) !== FALSE )
     {
       debug_message( "Dying, nick collision.\r\n", TRUE );
       return;
     }
     else if( $lasttime < ( time() - ( $CONFIG[servertimeout] * 60 ) ) )
     {
       debug_message( "Dying, haven't recived a response in ".$CONFIG[servertimeout]." minutes.\r\n", TRUE );
       return;
     }
     else if( stripos( $con['buffer']['all'], ' :VERSION' ) !== FALSE )
       cmd_send( "PRIVMSG ".$name." :".$CONFIG[version]."" );
     else if( stripos( $con['buffer']['all'], 'JOIN #' ) )
     {
      if( $con['data'][channels][$chanid]['autoopvoice'] == TRUE )
      {
         if( check_admin( $hostmask ) && $name != $CONFIG[nick] )
         {
           cmd_send( "MODE $channel +v $name" );
           cmd_send( "MODE $channel +o $name" );
         }
         else if( $name != $CONFIG[nick] )
           cmd_send( "MODE $channel +v $name" );
      }
     }
     else if( preg_match( "/.{1,500}\.(com|org|net|co\.uk|us|tk|rs|uk|gov|de|es|cz)/i", $text ) != NULL )
    {
      if( $con['data'][channels][$chanid]['snarf'] == FALSE )
        continue;
      $bufarray = explode( " ", $con['buffer']['all'] );
      $channel = $bufarray['2'];
      for( $i=0; $i<3; $i++ )
        unset( $bufarray[$i] );
      $bufarray = array_values( $bufarray );
      $urls = preg_grep( "@\.(com|org|net|co\.uk|us|tk|rs|uk|gov|de|es|cz)@", $bufarray );
      $urls = array_values( $urls );
      
      if( count( $urls ) > 2 )
        continue;
      for( $i=0; $i<count($urls);$i++ )
      {
        $url = trim( $urls[$i], "\x00..\x1F" );
        $url = ltrim( $url, ":" );
        if( stripos( $url, "@" ) !== FALSE )
          continue;
        $urltest = explode( ".", $url );
        if( count( $urltest ) < 2 )
          continue;
        
        $urlar = explode( "/", $url );
        
        for( $j=0; $j<count($urlar); $j++ )
        {
          if( preg_match( "/.{1,500}\.(com|org|net|co\.uk|us|tk|rs|uk|gov|de|cz|es)/i", $urlar[$j] ) != NULL )
            $urlb = $urlar[$j];
        }

        exec( "ping -c1 -w2 $urlb", $out );

        //don't try to contact sites that don't exist
        $doit = FALSE;
        for( $j=0; $j<count( $out ); $j++ )
        {
          if( stripos( $out[$j], "1 received" ) !==FALSE )
            $doit = TRUE;
        }
        if( !$doit ) 
          continue;
          
        $titles[$i] = snarf_url( $url );
          
        if( $url != NULL && $titles[$i] != NULL )
        {
          $bstatus['snarfs']++;
          talk( $channel, "(".$urlb.") ".$titles[$i] );
        }
      }
      unset( $titles, $urlarray, $urls, $url, $urlb, $out, $urltest, $doit );
    }
    else if( stripos( $con['buffer']['all'], "INVITE ".$CONFIG[nick] ) !== FALSE )
    {
      $channel = $bufarray['3'];
      if( stripos( $channel, "#" ) !== FALSE )
        cmd_send( "JOIN $channel" );
    }
    else if( stripos( $con['buffer']['all'], "KICK ".$bufarray['2']." ".$CONFIG[nick] ) !== FALSE )
    {
      $channel = $bufarray['2'];
      if( stripos( $channel, "#" ) !== FALSE )
        cmd_send( "JOIN $channel" );
    }//terra.unvanquished.net 353 Moobot5367 = #aaron5367 
    //$con['data'][channels][$chanid]['cmds']
    else if( stripos( $con['buffer']['all'], "$username = #" ) !== FALSE )
    {
      $con['data'][channels][$chanid]['users'];
      for( $i=0; $i<count( $textarray ); $i++ )
      {
        $con['data'][channels][$chanid]['users'][$i]['rawname'] = $textarray[$i];
        //The lazy way, since I can't think of another:
        if( trim( $textarray[$i], "+" ) != $textarray[$i] )
        {
          $con['data'][channels][$chanid]['users'][$i]['name'] = trim( $textarray[$i], "+" );
          $con['data'][channels][$chanid]['users'][$i]['flags'] = "+";
        }
        else if( trim( $textarray[$i], "%" ) != $textarray[$i] )
        {
          $con['data'][channels][$chanid]['users'][$i]['name'] = trim( $textarray[$i], "%" );
          $con['data'][channels][$chanid]['users'][$i]['flags'] = "%";
        }
        else if( trim( $textarray[$i], "@" ) != $textarray[$i] )
        {
          $con['data'][channels][$chanid]['users'][$i]['name'] = trim( $textarray[$i], "%" );
          $con['data'][channels][$chanid]['users'][$i]['flags'] = "@";
        }
        else
        {
          $con['data'][channels][$chanid]['users'][$i]['name'] = $textarray[$i];
          $con['data'][channels][$chanid]['users'][$i]['flags'] = "";
        }
      }
    }
    
    usleep( $CONFIG[sleeptime]*1000 );
  }
}
?>
