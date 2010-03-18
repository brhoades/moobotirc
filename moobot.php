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

$bot->debug_message( "Inital start of Moobot" );
//$dbcnx = mysql_connect( "localhost", $CONFIG[dbuser], $CONFIG[dbpass] );
//mysql_select_db( $CONFIG[dbname], $dbcnx );
$con = array();
$alive = "TRUE";

while( $alive == "TRUE" )
{
  $bstatus['botstarttime'] = time();
  init();
  $bot->debug_message( "Exited main moobot function, restarting." );
}

function init()
{
  global $con, $CONFIG, $servers, $other, $bot, $bstatus, $commandtree, $commands; 
  
  $bot->debug_message( "Entering main moobot function." );
  $firsttime = TRUE;
  if( is_int( $CONFIG[server] ) )
  {
    if( $CONFIG[server] == 0 )
      $CONFIG[server] = "irc.freenode.net";
    else if( $CONFIG[server] == 1 )
      $CONFIG[server] = "irc.quakenet.org";
  }
  $old = $CONFIG[server];
  $CONFIG[server] = gethostbyname( $CONFIG[server] );
  $bot->debug_message( "Resolved $old to ".$CONFIG[server] );
  $con['socket'] = fsockopen( $CONFIG[server], $CONFIG[port], $errno, $errstr, 1 );
  stream_set_blocking( $con['socket'], 0 );
  stream_set_timeout( $con['socket'], 100 );
	$lasttime = time();
  usleep( $CONFIG[sleeptime] );
  $bot->cmd_send( "USER ". $CONFIG[nick] ." ".$CONFIG[vhost]." ".$CONFIG[vhost]." :". $CONFIG[name], FALSE, TRUE );
  $bot->cmd_send( "NICK ". $CONFIG[nick] ." ".$CONFIG[vhost], FALSE, TRUE );
  $bot->debug_message( "Connected to main server, starting socket read loop." );
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

    if( substr( $con['buffer']['all'], 0, 6 ) == 'PING :' )
    {
      $bot->cmd_send( 'PONG :'.substr( $con['buffer']['all'], 6 ), FALSE, TRUE );
      $lasttime = time();
    }
    
    if( $firsttime == TRUE && ( stripos( $con['buffer']['all'], "/motd" ) !== FALSE 
        || stripos( $con['buffer']['all'], "MOTD File" ) !== FALSE )
        || stripos( $con['buffer']['all'], "End of message of the day" ) !== FALSE )
    {
      //
      //Loads data
      //
      $con['data'] = $bot->readdata( $con['data'] );
      //
      //
      //
      
      $max = count( $con['data'][channels] );
      for( $i=0; $i<$max; $i++ )
      {
        if( $con['data'][channels][$i]['active'] == FALSE )
          continue;
        if( $con['data'][channels][$i]['password'] != NULL )
          $bot->cmd_send( "JOIN ". $con['data'][channels][$i]['name'] ." ". $con['data'][channels][$i]['password'] );
        else
          $bot->cmd_send( "JOIN ". $con['data'][channels][$i]['name'] );
      }
      $firsttime = FALSE;
      if( $CONFIG[nickpass] != NULL )
      {
        if( $CONFIG[nickserv] == "NickServ" || $CONFIG[nickserv] == "Q@CServe.quakenet.org" )
        {
          if( $CONFIG[server] == 0 || $CONFIG[nickserv] == "NickServ" )
            $bot->cmd_send( "PRIVMSG NickServ :IDENTIFY ".$CONFIG[nick]." ".$CONFIG[nickpass], TRUE, TRUE );
          else if( $CONFIG[server] == 1 || $CONFIG[nickserv] == "Q@CServe.quakenet.org" )
            $bot->cmd_send( "PRIVMSG Q@CServe.quakenet.org :AUTH ".$CONFIG[nick]." ".$CONFIG[nickpass], TRUE, TRUE );
        }
        else
          $bot->cmd_send( "PRIVMSG ".$CONFIG[nickserv]." :AUTH ".$CONFIG[nick]." ".$CONFIG[nickpass], TRUE, TRUE );
      }
      //$bot->cmd_send( "MODE ".$CONFIG[nick]." +x " );
    }

    if( $con['buff']['array'] == NULL )
    {
      if( $con['nextsvnmontime'] <= time() && $firsttime == FALSE )
      {
        $bot->debug_message( "Time for a SVN/HG check" );
        $con['nextsvnmontime'] = time() + $CONFIG[svnmontimeout]; 
        $bot->svnmon( );
        $bot->hgmon( );
        $bstatus['svnchecks']++;
      }
      
      if( $con['serverlistcache']['time'] + 120 <= time() )
      {
        $bot->debug_message( "Server list cache refresh time" );
        $con['serverlistcache']['con'] = $bot->tremulous_getserverlist( );
        $con['serverlistcache']['time'] = time();
      }

      //
      //checks are here!
      //
      $bot->runbuffers( );
      $bot->run_votes( );
      //$bot->server_check( $CONFIG['servers']['KOR'], "#knightsofreason", "KOR" );
      //$bot->vote_check( );
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

    $bot->vote_check( $hostmask, $name, $channel, $text );

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
          if( $commandtree[$i][2] == TRUE && !$bot->check_admin( $hostmask ) )
          {
            $bot->talk( $channel, $name.": You do not have permission to use that command." );
            $eval = TRUE;
            break;
          }
          else
          {
            $bot->debug_message( "Evaluating command \"".$command."\"" );
            eval( $commandtree[$i][1] );
            $eval = TRUE;
            break;
          }
        }
      }
      
      if( !$eval && $command != NULL )
        $bot->talk( $channel, "%$command is not known, try using %help." );
    }
    /*else if( stripos( $text, " is " ) !== FALSE || stripos( $text, " are " ) !== FALSE )
    {
      if( stripos( $text, " is " ) )
      {
        $statement = explode( " is ", $text );
        $verb = "is";
      }
      else
      {
        $statement = explode( " are ", $text );
        $verb = "are";
      }
      
      //Check to see if it has been said before...
      $test = mysql_fetch_array( mysql_query( "SELECT * FROM state WHERE verb=\"".mysql_escape_string($verb)." AND this=\"".mysql_escape_string($this)."\" AND that=\"".mysql_escape_string($that)."\" " ) );
      //If it has, then add to the times it has been said
      if( count( $test ) > 0 )
      {
        $test['timessaid']++;
        mysql_query( "UPDATE state SET timessaid=".$test['timessaid']." WHERE verb=\"".mysql_escape_string($verb)." AND this=\"".mysql_escape_string($this)."\" AND that=\"".mysql_escape_string($that)."\" " );
      }
      else         //If it hasn't add it in
      mysql_query( "INSERT INTO state (\"this\",\"verb\",\"that\",\"whostarted\") VALUES (\"".mysql_escape_string($statement[0])."\",\"".$verb."\",\"".mysql_escape_string($statement[1])."\",\"".mysql_escape_string($name)."\")");
    }
    else if( preg_match( "/?/", $text ) )
    {
      $text = rtrim( $text, "?" );
      $count = 0;
      while( $row = mysql_fetch_array( $query ) )
      {
        if( count( $row ) < 2 )
          break;
        $response[$count] = $row;
        $count++;
      }
      if( count( $response ) > 0 )
      {
        for( $i=0; $i<count($response); $i++ )
        {
          $cur = $response[$i];
          if( $cur['timessaid'] > $top['stated'] )
          {
            $top['stated'] = $cur['timessaid'];
            $top['id'] = $i;
          }
        }
        //Okay, we know the top and now we are going to update the times quiered and say it!
        $bot->talk( $channel, "I think ".$top['this']." ".$top['verb']." ".$top['that'] );
        
        $top['quieries']++;
        mysql_query( "UPDATE state SET queries=".$top['queries']." WHERE verb=\"".$top['verb']." AND this=\"".$top['this']."\" AND that=\"".$top['that']."\" " );
      }
      
    }*/
    /*else if( stripos($con['buffer']['all'], " :", 10 ) !== FALSE )
    {
      $start = strpos( $con['buffer']['all'], ":", 1 )+1;
      $text = $other->sanitize2($other->sanitize(substr( $con['buffer']['all'], $start )));
      $time = time();
      if( $channels[$chanid]['log'] == "TRUE" )
      {
        $entryid = mysql_num_rows( mysql_query( "SELECT * FROM log" ) );
        mysql_query("INSERT INTO log (user,text,channel,time,lineid) VALUES (\"$name\",\"$text\",\"$channel\",\"$time\",\"$entryid\")");
      }
      else if( $channels[$chanid]['log'] == "FALSE" )
        print "Channel has disabled log submission.\r\n"; 
     } */
     else if( stripos( $con['buffer']['all'], "(Nick collision from services.)" ) !== FALSE )
     {
       $bot->debug_message( "Dying, nick collision.\r\n", TRUE );
       return;
     }
     else if( $lasttime < ( time() - ( $CONFIG[servertimeout] * 60 ) ) )
     {
       $bot->debug_message( "Dying, haven't recived a response in ".$CONFIG[servertimeout]." minutes.\r\n", TRUE );
       return;
     }
     else if( stripos( $con['buffer']['all'], ' :VERSION' ) !== FALSE )
       $bot->cmd_send( "PRIVMSG ".$name." :".$CONFIG[version]."" );
     else if( stripos( $con['buffer']['all'], 'JOIN #' ) )
     {
      if( $con['data'][channels][$chanid]['autoopvoice'] == TRUE )
      {
         if( $bot->check_admin( $hostmask ) && $name != $CONFIG[nick] )
         {
           $bot->cmd_send( "MODE $channel +v $name" );
           $bot->cmd_send( "MODE $channel +o $name" );
         }
         else if( $name != $CONFIG[nick] )
           $bot->cmd_send( "MODE $channel +v $name" );
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
        $doit=FALSE;
        for( $j=0; $j<count( $out ); $j++ )
        {
          if( stripos( $out[$j], "1 received" ) !==FALSE )
            $doit = TRUE;
        }
        if( !$doit )          
          $titles[$i] = $bot->snarf_url( $url, TRUE );
        else
          $titles[$i] = $bot->snarf_url( $url );
          
        if( $url != NULL && $titles[$i] != NULL )
        {
          $bstatus['snarfs']++;
          $bot->talk( $channel, "(".$urlb.") ".$titles[$i] );
        }
      }
      unset( $titles, $urlarray, $urls, $url, $urlb, $out, $urltest, $doit );
    }
    else if( stripos( $con['buffer']['all'], "INVITE ".$CONFIG[nick] ) !== FALSE )
    {
      $channel = $bufarray['3'];
      if( stripos( $channel, "#" ) !== FALSE )
        $bot->cmd_send( "JOIN $channel" );
    }
    else if( stripos( $con['buffer']['all'], "KICK ".$bufarray['2']." ".$CONFIG[nick] ) !== FALSE )
    {
      $channel = $bufarray['2'];
      if( stripos( $channel, "#" ) !== FALSE )
        $bot->cmd_send( "JOIN $channel" );
    }
    
    usleep( $CONFIG[sleeptime]*1000 );
  }
}

?>
