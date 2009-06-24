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
//
//I highly recommend not editing beyond this point without PHP experience.
//
$dbcnx = mysql_connect( "localhost", $CONFIG['dbuser'], $CONFIG['dbpass'] );
mysql_select_db( $CONFIG['dbname'], $dbcnx );
$con = array();
$alive = "TRUE";
$bstatus['scrstarttime'] = time();

if( $_GET['ctrl'] == NULL )
{
  while( $alive == "TRUE" )
  {
    $bstatus['botstarttime'] = time();
    init();
  }
}

if( $_GET['ctrl'] != NULL || $_COOKIE['username'] != NULL 
    || $_COOKIE['password'] != NULL )
{
  //Room for control panel...
}

function init()
{
  global $con, $CONFIG, $servers, $maxtimeout, $channels, $admins, $privs, $ignores, $svnmontimeout, $svnmontime, $nextsvnmontime, $other, $bot, $bstatus; 
  $firsttime = "TRUE";
  $con['socket'] = fsockopen( $CONFIG['server'], $CONFIG['port'] );
	$lasttime = time();
  if (!$con['socket'] ) 
  {
    print("Could not connect to: ". $CONFIG['server'] ." on port ". $CONFIG['port'] );
  }
  else 
  {
    //$con['stimes'] = 0;
    //$con['lastspeaktime'] = time()-10;
    //$firsttime = "FALSE";
    $bot->cmd_send("USER ". $CONFIG['nick'] ." aaronh.servehttp.com aaronh.servehttp.com :". $CONFIG['name'] );
    $bot->cmd_send("NICK ". $CONFIG['nick'] ." aaronh.servehttp.com");
    while( !feof($con['socket']) )
    {
      $con['buffer']['all'] = trim( fgets( $con['socket'], 4096 ) );
      if( $con['buffer']['all'] != NULL )
        print date("[ d/m/y @ H:i:s ]")."<- ".$con['buffer']['all'] ."\n";

      $bstatus['lines']++;

      if( $CONFIG[ 'nextservertime' ] < time() )
      {
        //$test = find_servers( "TRUE" );
        $bstatus['cacheups']++;
        exec( "php /srv/http/moobot/updatecache.php > /dev/null 2>&1 &" );
        $CONFIG[ 'nextservertime' ] = time()+$CONFIG[ 'servertimeout' ];
        $con['cached'] = $bot->find_servers( "COUNTS" );
      }
      else if( time() % 15 )
        $con['cached'] = $bot->find_servers( "COUNTS" );

      if( $nextsvnmon <= time() )
      {
        $bot->svnmon();
        $nextsvnmon = time() + $svnmontimeout; 
        $bstatus['svnchecks']++;
        $bot->server_check( $CONFIG['servers']['KOR'], "#knightsofreason", "KOR" );
      }
      
      if( substr( $con['buffer']['all'], 0, 6) == 'PING :' )
      {
        $bot->cmd_send( 'PONG :'.substr($con['buffer']['all'], 6 ) );
        $lasttime = time();
      }
      
      //
      //checks are here!
      //
      $bot->runbuffers( );
      $bot->vote_check( );
      //
      //
      //
      
      if( $firsttime == "TRUE" && stripos( $con['buffer']['all'], "/motd" ) )
      {
        $max = count( $channels );
        for( $i=0; $i<$max; $i++ )
        {
          if( $channels[$i]['password'] != NULL )
            $bot->cmd_send( "JOIN ". $channels[$i]['name'] ." ". $channels[$i]['password'] );
          else
            $bot->cmd_send( "JOIN ". $channels[$i]['name'] );
        }
        $firsttime = "FALSE";
        //Skip the logs //msg Q@CServe.quakenet.org auth Aaron5367 lolpass
        //fputs($con['socket'], "PRIVMSG NICKSERV GHOST ".$CONFIG['nick']." ".$CONFIG['pass']."\n\r");
        $bot->cmd_send( "PRIVMSG Q@CServe.quakenet.org auth ".$CONFIG['9pass']." \n\r" );
        $bot->cmd_send( "MODE ".$CONFIG['name']." +x \n\r" );

        //config();
      }
      
      if( substr( $con['buffer']['all'], 0, 15 ) == ":servercentral." )
        continue;
      /*if( $con['buffer']['all'] == $con['buffer']['old'] )
        continue;
      else
        $con['buffer']['old'] = $con['buffer']['all'];*/
//****************
//
//COMMANDS
//
//****************
      
      $bufarray = explode( " ", $con['buffer']['all'] );
      $channel = $bufarray['2'];
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
      {
        $text = ltrim( $text, ":" );
      }
      else
      {
        unset( $textarray['0'] );
        $textarray = array_values( $textarray );
      }
        
      if( stripos( $text, ":%" ) !== FALSE )
      {
        for( $i=0; $i<count($channels); $i++ )
        {
          if( $channel == $channels[$i]['name'] )
          {
            $chanid = $i;
            break;
          }
        }
        //:ikusari!~ikusari@99-195-146-130.dyn.centurytel.net PRIVMSG #knightsofreason :Aaron5367: skype me you whore
        //Parse the string into what we need
        for( $i=0; $i<3; $i++ )
          unset( $bufarray[$i] );
        $command = ltrim( $bufarray['3'], ":%" );
        unset( $bufarray['3'] );
        //$bufarray[4]  = ltrim( $bufarray[3], ":" );
        $bufarray = array_values( $bufarray );
        if( $channels[$chanid]['cmds'] == "FALSE" )		//hax
          $command = "channelhasdisabledcommands";
        echo  "$name, $command, $channel, \"$hostmask\"\n\t";
        $bstatus['cmds']++;
        switch( $command )
        {
          case "rehash":
            if( $bot->check_admin( $hostmask ) )
            {
              $bot->cmd_send( "QUIT :Killed by $name" );
              exec("php ".$CONFIG['pathtoourself']);
              die( "Restart time" );
            }
            else
              $bot->talk( $channel, "$name: You do not have permission to use this command." );
            break;
          /*case "logstatus":
            $numberofentries = mysql_num_rows( mysql_query( "SELECT * FROM log" ) );
            $bot->talk( $channel, "I currently have ".$numberofentries." entries in my database." );
            break;
          case "userstatus":
            $username = $other->sanitize( $other->sanitize2( $bufarray['0'] ) );
            if( $username == NULL )
              break;
            $lines = mysql_num_rows( mysql_query( "SELECT * FROM log WHERE user=\"$username\"" ) );
            if( $lines <= 0 )
              $bot->talk( $channel, "I don't think I've ever heard from ".$username." before." );
            else
            {
             $user = mysql_fetch_array( mysql_query( "SELECT * FROM users WHERE user=\"$username\"" ) );
             $chars = $user['charcount'];
             $words = $user['wordcount'];
              $bot->talk( $channel , "I've seen $username speak a total of $lines times." );
            }
            break;*/
          case "uptime":
            exec( "uptime", $uptime );
            $bot->talk( $channel, "The server I am currently on has the following info regarding uptime:" );
            $bot->talk( $channel, $uptime[0] );
            break;
          case "sysinfo":
            $output = $bot->sysinfo();
            $bot->talk( $channel, $output );
            break;  
          /*case "randquote":
            unset( $quote );
            $lines = mysql_num_rows( mysql_query( "SELECT * FROM log" ) );
            while( strlen( $quote['text'] ) < 10 )
            {
              $lineid = rand( 0, $lines );
              $quote = mysql_fetch_array( mysql_query( "SELECT * FROM log WHERE lineid=\"$lineid\"" ));
            }
            $date =  date( 'l jS \of F Y h:i:s A', $quote['time'] );
            $bot->talk( $channel, "\"".$quote['text']."\" by ".$quote['user']." on ".$date." in ".$quote['channel'] );
            break;*/
          case "svnmon":
            if( $bot->check_admin( $hostmask ) )
            {
              if( $CONFIG['svnmon'] == "TRUE" )
              {
                $CONFIG['svnmon'] = "FALSE";
                $bot->talk( $channel, "SVN monitor is now on." );
              }
              else if( $CONFIG['svnmon'] == "FALSE" )
              {
                $CONFIG['svnmon'] = "TRUE";
                $bot->talk( $channel, "SVN monitor is now off." );
              }
              break;
            }
            else
            {
              $bot->talk( $channel, $name.": You do not have permission to use this command." );
              break;
            }
          case "rot13":
            $newtext = str_rot13( trim( implode( " ", $bufarray ) ) );
            $bot->talk( $channel , $newtext );
            break;
          case "google":
            $url = trim( implode( "+", $buffarray ) );
            $bot->talk( $channel, "http://letmegooglethatforyou.com/?q=$url" );
            break;
          case "urban":
            if( $bufarray[0] == NULL )
            {
              $bot->talk( $channel, "Please use the following syntax:" );
              $bot->talk( $channel, "%urban term (term number)" );
              break;
            }
            
            if( $bufarray[1] == NULL )
            {
              $term = $bufarray[0];
              $def = $bot->urban_lookup( $term, 1 );
              //$count = $def["COUNT"];
              print_r( $def );
              //unset( $def["COUNT"] );
              if( count( $def ) > 5 )
              {
                $bot->talk( $channel, "The definition is too long, you can view it at http://urbandictionary.com/define.php?term=$term" );
                break;
              }
              $bot->talk( $channel, "Definition of $term:" );
              for( $i=0; $i<count($def); $i++ )
                $bot->talk( $channel, trim($def[$i]) );
            }
            else
            {
              //$termstuff = explode( " ", $bufarray );
              //if( is_int( $term[ count($term)-1 ] ) )
              //{
              //  $num  = $termstuff[ count($termstuff)-1 ]; 
              //  unset( $termstuff[ count($termstuff)-1 ] );
              //}
              //else
              $num = 1;
              $term = implode( " ", $bufarray );
              //$num  = $termstuff[ count($termstuff)-1 ]; 
              //$term = $bufarray[0];
              //$num = $bufarray[1];
              $def = $bot->urban_lookup( $term, $num );
              //print_r( $def );
              //$count = $def["COUNT"];
              //unset( $def["COUNT"] );
              if( count( $def ) > 5 )
              {
                $max = 4;
                $bot->talk( $channel, "Definition of $term:" );
                $page = ceil( $num/7 );
                for( $i=0; $i<$max; $i++ )
                  $bot->talk( $channel, $def[$i] );
                $bot->talk( $channel, "[...]" );
                if( $page > 1 )
                  $bot->talk( $channel, "You can view the rest of the definition here: http://urbandictionary.com/define.php?page=$page&term=$term" );
                else
                  $bot->talk( $channel, "You can view the rest of the definition here: http://urbandictionary.com/define.php?term=$term" );
                break;
              }
              $bot->talk( $channel, "Definition of $term:" );
              for( $i=0; $i<count($def); $i++ )
                $bot->talk( $channel, trim($def[$i]) );
            }
            unset( $def );
            unset( $count );
            unset( $num );
            unset( $term );
            break;
          case "help":
            $bot->talk( $channel, "Current bot commands are:" );
            $bot->talk( $channel, "svnmon(A), rehash(A), part(A), join(A), msg(A), voice/unmute(A), devoice/mute(A), passvote(A), cancelvote(A), op(A), deop(A), logstatus, userstatus, uptime, sysinfo, randquote, rot13, google, urban, help, and status." );
            $bot->talk( $channel, "Current tremulous commands are:" );
            $bot->talk( $channel, "msgs(A), clan, servers, server, and find" );
            break;
          case "server":
            if( count( $bufarray ) <= 0 )
            {
              $bot->talk( $channel, $name.": Please specify a server and port or an alias with this command." );
              break;
            }
            else if( count( $bufarray ) == 1 && stripos( $bufarray[0], ":" ) === FALSE )
            { 
              //Alias
              $set = $bufarray[0];
              if( !array_key_exists( strtoupper($set), $CONFIG['servers'] ) )
              {
                $bot->talk( $channel, "I don't have any server stored for the alias $set, try one of the following:" );
                $bot->talk( $channel, "X, AA, A<3, uBP, or SoH" );
                break;
              }
              $set = strtoupper($set);
              $ip = $CONFIG['servers'][$set]['ip'];
              $port = $CONFIG['servers'][$set]['port'];
              $backupname = $CONFIG['servers'][$set]['bakname'];
            }
            else if( count( $bufarray ) == 1 && stripos( $bufarray[0], ":" ) !== FALSE )
            {
              $bufarray = explode( ":", $bufarray[0] );
              $ip = $bufarray[0];
              $port = $bufarray[1];
            }
            else if( count( $bufarray ) == 2 )
            {
              $ip = $bufarray[0];
              $port = $bufarray[1];
            }
            else if( count( $bufarray ) > 2 )
            {
              $bot->talk( $channel, "Too many arguments (".count($bufarray).")." );
              break;
            }
            else
            {
              $bot->talk( $channel, "Unknown arguments" );
              break;
            }
            unset( $server );
            unset( $serverinfo );
            unset( $servername );
            $server = $bot->tremulous_get_players( $ip, $port );
            $serverinfo = $bot->get_server_settings( $ip, $port );
            $servername = $serverinfo['servername'];
            if( ( $servername == NULL || $servername == "" ) && $set != NULL )
              $servername = $backupname;
            else if( $servername == NULL || $servername == "" )
            {
              $bot->talk( $channel, "Unable to get a valid server name from $ip:$port" );
              break;
            }
            unset( $map );
            unset( $maxplayers );
            unset( $players );
            unset( $status );
            $map = $server['map'];
            $players = count( $server[ alien_players ]  ) + count( $server[ spec_players ]  ) + count( $server[ human_players ]  );
            $maxplayers = $serverinfo['maxplayers'];
            if( ( $map == NULL || $map == "" ) && $set != NULL )
              $status = "OFFLINE";
            else if( ( $map == NULL || $map == "" ) && $servername != NULL )
            {
              $bot->talk( $channel, "Unable to make further contact with $ip:$port, $servername" );
              break;
            }
            else if( $map == NULL && $servername != NULL )
            {
              $tries = 0;
              while( $map == NULL && $tries < 10 )
              {
                $tries++;
                $server = $bot->tremulous_get_players( $ip, $port );
                $map = $server['map'];              
              }
            }
            
            if( $status != "OFFLINE" )
              $bot->talk( $channel, $bot->tremulous_replace_colors_irc( $servername )." - $map - ($players/$maxplayers)" );
            else
              $bot->talk( $channel, $bot->tremulous_replace_colors_irc( $servername )." - OFFLINE" );
          unset( $map );
          unset( $server );
          unset( $players );
          unset( $maxplayers );
          unset( $servername );
          unset( $ip );
          unset( $port );
          unset( $status );
          unset( $serverinfo );
          unset( $set );
          unset( $pset );
          break;
          case "servers":
            $bufarray = $bufarray;
              if( $bufarray[0] == NULL || $bufarray[0] == "" )
                $set = "KOR";
              else
              {
                $pset = $bufarray[0];
                if( !array_key_exists( strtoupper($pset), $CONFIG['servers'] ) )
                {
                  $bot->talk( $channel, "I don't have any servers stored for $pset, try one of the following:" );
                  $bot->talk( $channel, "KoR, MG, or RK" );
                  break;
                }
                $set = strtoupper($pset);
              }
            //Retrieve the info
            for( $i=0; $i<count($CONFIG['servers'][$set]); $i++)
            {
              $ip = $CONFIG['servers'][$set][$i]['ip'];
              $port = $CONFIG['servers'][$set][$i]['port'];
              $server = $bot->tremulous_get_players( $ip, $port );
              $backupname = $CONFIG['servers'][$set][$i]['bakname'];
              $serverinfo = $bot->get_server_settings( $ip, $port );
              $servername = $serverinfo['servername'];
              if( $servername == NULL || $servername == "" )
                $servername = $backupname;
              $map = $server['map'];
              $players = count( $server[ alien_players ]  ) + count( $server[ spec_players ]  ) + count( $server[ human_players ]  );
              $maxplayers = $serverinfo['maxplayers'];
              $averageping = $bot->average_ping( $server );
              if( $map == NULL || $map == "" )
                $status = "OFFLINE";
              else if( $averageping >= 999 && $players > 1 )
                $status = "CRASHED";
              else
                $status = "ONLINE";
              
              $servers[$i]['done'] = "FALSE";
              $servers[$i]['name'] = $servername;
              $servers[$i]['status'] = $status;
              if( $status == "ONLINE" )
              {
                $servers[$i]['players'] = $players;
                $servers[$i]['maxplayers'] = $maxplayers;
                $servers[$i]['map'] = $map;
                $servers[$i]['averageping'] = $averageping;
              }
              unset( $map );
              unset( $server );
              unset( $players );
              unset( $averageping );
            }
            
            //Make an array of the players for sorting
            for( $i=0;$i<count($servers);$i++ )
              $players[$i] = $servers[$i]['players'];
            
            //Sort!
            sort( $players );
            $players = array_reverse( $players );
            //print_r($CONFIG['servers']);
            //Find out which player value belongs to which and print it out
            for( $i=0;$i<count($servers);$i++ )
            {
              $valuetofind = $players[$i];
              for( $j=0;$j<count($servers);$j++ )
              {
                if( $valuetofind == $servers[$j]['players'] && $servers[$j]['done'] == "FALSE" )
                  $serverval = $j;
                else
                  continue;
              }
              //unpack
              $playernum = $servers[$serverval]['players'];
              $maxplayers = $servers[$serverval]['maxplayers'];
              $map = $servers[$serverval]['map'];
              $status = $servers[$serverval]['status'];
              $name = $servers[$serverval]['name'];
              $servers[$serverval]['done'] = "TRUE";
              $averageping = $servers[$serverval]['averageping'];
              if( $status == "ONLINE" )
              {
                if( $playernum > 0 )
                  $bot->talk( $channel, $bot->tremulous_replace_colors_irc( $name )." - $map - ($playernum/$maxplayers) - $averageping" );
                else
                  $bot->talk( $channel, $bot->tremulous_replace_colors_irc( $name )." - $map - ($playernum/$maxplayers)" );
              }
              else if( $status == "OFFLINE" )
                $bot->talk( $channel, $bot->tremulous_replace_colors_irc( $name )." - OFFLINE" );
              else if( $status == "CRASHED" )
                $bot->talk( $channel, $bot->tremulous_replace_colors_irc( $name )." - CRASHED" );
            }
            unset( $map );
            unset( $server );
            unset( $players );
            unset( $maxplayers );
            unset( $servername );
            unset( $ip );
            unset( $port );
            unset( $servers );
            unset( $serverval );
            unset( $playernum );
            unset( $serverinfo );
            unset( $averageping );
            break;
          case "find":
            $player = $bufarray['0'];
            $bot->talk( $channel, "Searching ".$con['cached']." cached server(s) for \"$player\"..." );
            $found = $bot->find_player( $player );
            echo count($found)."\n";
            print_r($found);

            if( count( $found ) > 10 )
            {
              $bot->talk( $channel, "Your search returned ".count( $found )." results, please be more specific with your search." );
              break;
            }
            
            if( count( $found ) > 0 )
            {
              for( $i=0; $i<count( $found ); $i++ )
              {
                $name = $found[$i]['name'];
                $server = $found[$i]['server'];
                $ping = $found[$i]['ping'];
                $kills = $found[$i]['kills'];
                $team = $found[$i]['team'];
                if( $team == "humans" )
                  $bot->talk( $channel, "Found $name on $server, with a ping of $ping, $kills kills, on the humans. " );
                else if( $team == "spectators" )
                  $bot->talk( $channel, "Found $name on $server, with a ping of $ping, on the spectators. " );
                else
                  $bot->talk( $channel, "Found $name on $server, with a ping of $ping, $kills kills, on the aliens. " );
              }
            }
            else
              $bot->talk( $channel, "No players with ".$player." in their name." );
            unset( $name );
            unset( $server );
            unset( $team );
            unset( $kills );
            unset( $ping );
            unset( $found );
            break;
          case "clan":
            $bot->talk( $channel, "Searching ".$con['cached']." cached server(s) for clan members..." );
            $found = $bot->find_player( "|KoR|" );
            
            if( count( $found ) > 0 )
            {
              for( $i=0; $i<count( $found ); $i++ )
              {
                $name = $found[$i]['name'];
                $server = $found[$i]['server'];
                $team = $found[$i]['team'];
                $kills = $found[$i]['kills'];
                $ping = $found[$i]['ping'];
                if( $team == "humans" )
                  $bot->talk( $channel, "Found $name on $server, with a ping of $ping, $kills kills, on the humans. " );
                else if( $team == "spectators" )
                  $bot->talk( $channel, "Found $name on $server, with a ping of $ping, on the spectators. " );
                else
                  $bot->talk( $channel, "Found $name on $server, with a ping of $ping, $kills kills, on the aliens. " );
              }
            }
            else
              $bot->talk( $channel, "No clan members were found." );
            unset( $name );
            unset( $server );
            unset( $team );
            unset( $kills );
            unset( $ping );
            unset( $found );
            break;
          case "msgs":
          case "says":
          case "cmds":
            if( !$bot->check_admin( $hostmask ) )
            {
              $bot->talk( $channel, "$name: Sorry, you do not have permission to use this command" );
              break;
            }
            $serveralias = $textarray['0'];
            unset( $textarray['0'] );
            if( $command == "msgs" )
            {
              $target = $textarray['1'];
              unset( $textarray['1'] );
            }
            $textarray = array_values( $textarray );
            $message = implode( " ", $textarray );
            if( $serveralias != "korx1" && $serveralias != "korx2" )
            {
              $bot->talk( "The alias $serveralias is not known, please use korx1 or korx2." );
              break;
            }
            else if( $serveralias == "korx1" )
            {
              $ip = $CONFIG['servers']['KOR'][0]['ip'];
              $port = $CONFIG['servers']['KOR'][0]['port'];
              $rcon = $CONFIG['servers']['KOR'][0]['rcon'];
            }
            else if( $serveralias == "korx2" )
            {
              $ip = $CONFIG['servers']['KOR'][1]['ip'];
              $port = $CONFIG['servers']['KOR'][1]['port'];
              $rcon = $CONFIG['servers']['KOR'][1]['rcon'];
            }
            
            //echo "IP:$ip, port:$port, message:$message, commands:$command";
            if( $command == "msgs" )
              $message2 = $bot->tremulous_rcon( $ip, $port, "m $target ^2$message", $rcon, "FALSE" );
            else if( $command == "says" )
              $message2 = $bot->tremulous_rcon( $ip, $port, "!print ^7[IRC][$name^7]: ^2$message", $rcon, "FALSE" );
            else if( stripos( trim($message), "!" ) !== FALSE &&  stripos( trim($message), "!" ) == 0 )
              $message2 = $bot->tremulous_rcon( $ip, $port, $message, $rcon, "FALSE" );
            else
            {
              $bot->talk( $channel, "$name: Please use commands, no variable modifications please." );
              break;
            }
            
            if( count( $message2 ) > 5 )
            {
              $bot->talk( $channel, "That returned more than 4 lines of text. It was executed, however." );
              break;
            }
            else
            {
              for( $i=0; $i<count($message2); $i++ )
                $bot->talk( $channel, $message2[$i] );
            }
            break;
          case "rng":
            //$bufarray['0'] = trim( $bufarray['0'] );
            srand();
            if( count( $bufarray ) == 0 )
            {
              $return = rand();
            }
            else if( count( $bufarray ) == 1 )
            {
              if( ((int)$bufarray['0']) > 50 )
                $return = "I will not return more than ".$bufarray['0']." random numbers.";
              else
              {
                for( $o=0; $o < ((int)$bufarray['0']); $o++ )
                {
                  if( $o == 0 )
                  {
                    $return = rand();
                    continue;
                  }
                  $return = $return." ".rand();
                }
              }
            }
            $bot->talk( $channel, $return );
            break;
          case "join":
            if( $bot->check_admin( $hostmask ) )
            {
              $bot->cmd_send("JOIN ".$bufarray[ 0 ] );
              $bot->talk( $channel, $name.": ".$bufarray[0]." has been joined" );
            }
            else
              $bot->talk( $channel, "You do not have permission to use this command." );
            break;
          case "mute":
          case "voice":
            if( $bot->check_admin( $hostmask ) )
              $bot->cmd_send( "MODE $channel -v ".$bufarray[0] );
            else
              $bot->talk( $channel,  $name.": You do not have permission to use this command." );
            break;
          case "devoice":
          case "unmute":
            if( $bot->check_admin( $hostmask ) )
              $bot->cmd_send( "MODE $channel +v ".$bufarray[0] );
            else
              $bot->talk( $channel,  $name.": You do not have permission to use this command." );
            break;
          case "deop":
            if( $bot->check_admin( $hostmask ) )
              $bot->cmd_send( "MODE $channel -o ".$bufarray[0] );
            else
              $bot->talk( $channel,  $name.": You do not have permission to use this command." );
            break;
          case "op":
            if( $bot->check_admin( $hostmask ) )
              $bot->cmd_send( "MODE $channel +o ".$bufarray[0] );
            else
              $bot->talk( $channel,  $name.": You do not have permission to use this command." );
            break;
          case "callvote":
            $type = $bufarray['0'];
            //function call_vote( $string, $type, $caller, $channel )
            unset( $bufarray['0'] );
            $string = implode( " ", $bufarray );
            $bot->call_vote( $string, $type, $name, $channel );
            if( stripos( $channel, "#" ) === FALSE )
            {
              $bot->talk( $channel, "Sorry, but please only use callvote in a channel." );
              break;
            } 
            break;
            case "part":
            if( $bot->check_admin( $hostmask ) )
            {
              $bot->cmd_send("PART ".$bufarray[0] );
              if( $channel != $bufarray[0] )
                $bot->talk( $channel, $name.": I have parted ".$bufarray[0] );
            }
            else
              $bot->talk( $channel, $name.": You do not have permission to use this command." );
            break;
          case "msg":
            if( $bot->check_admin( $hostmask ) )
            {
              $target = $bufarray['0'];
              unset( $bufarray['0'] );
              $text = implode( " ", $bufarray );
              $bot->talk( $target, $text );
              $bot->talk( $channel, "Message sent to $target" );
            }
           else
             $bot->talk( $channel, $name.": You do not have permission to use this command." );
           break;
          case "cancelvote":
            if( $bot->check_admin( $hostmask ) )
            {
              $con['vote']['endtime'] = time()-1;
              $con['vote']['yes'] = 0;
              $con['vote']['no'] = 1;
            }
            else
              $bot->talk( $channel,  $name.": You do not have permission to use this command." );
            break;
          case "passvote":
            if( $bot->check_admin( $hostmask ) )
            {
              $con['vote']['endtime'] = time()-1;
              $con['vote']['yes'] = 1;
              $con['vote']['no'] = 0;
            }
            else
              $bot->talk( $channel,  $name.": You do not have permission to use this command." );
            break;
          case "status":
            $uptime = $other->time_duration( time() - $bstatus['scrstarttime'] );
            $contime = $other->time_duration( time() - $bstatus['botstarttime'] );
            $servers = $bstatus['trem'];
            $cmdstoserv = $bstatus['commandstoserv'];
            $talked = $bstatus['talked'];
            $cmds = $bstatus['cmds'];
            $snarfs = $bstatus['snarfs'];
            if( $snarfs == NULL )
              $snarfs = 0;
            $lines = $bstatus['lines'];
            
            $bot->talk( $channel, "I've been up for $uptime, sent $cmdstoserv command(s) to the server, processed $lines line(s) of text, talked $talked time(s), ran $cmds command(s), snarfed $snarfs url(s), and contacted a tremulous server $servers time(s)." );
            break;
        }
      }
      else if( stripos( $text, " is " ) !== FALSE || stripos( $text, " are " ) !== FALSE )
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
        
      }
      else if( stripos($con['buffer']['all'], " :", 10 ) !== FALSE )
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
/*        if( mysql_num_rows(mysql_query("SELECT * FROM users WHERE user=\"$name\"")) == 1 ) 
        {
          $words = mysql_query("SELECT wordcount FROM users WHERE user=\"$name\"")+count_words($text);
          $char = mysql_query("SELECT charcount FROM users WHERE user=\"$name\"")+strlen($text);
          mysql_query("UPDATE users SET wordcount=\"$words\" WHERE user=\"$name\"");
          mysql_query("UPDATE users SET charcount=\"$char\" WHERE user=\"$name\"");
        }
        else if( mysql_num_rows(mysql_query("SELECT * FROM users WHERE user=\"$name\"")) == 0 )
        {
          $words = count_words($text);
          $char = strlen($text);
          mysql_query("INSERT INTO users (user,wordcount,charcount) VALUES (\"$name\",\"$words\",\"$char\")");
        } */
        //print "Submitting query... ". mysql_error()."\n\r";   
       } 
       else if( stripos( $con['buffer']['all'], "(Nick collision from services.)" ) !== FALSE )
       {
				 echo "Dying, nick collision";
         return;
       }
			 else if( $lasttime < ( time() - $maxtimeout*60 ) )
			 {
				 echo "Dying, haven't recived a response in $maxtimeout minutes.";
				 return;
			 }
       else if( stripos( $con['buffer']['all'], ' :VERSION' ) !== FALSE )
       {
         $nameend = strpos( $con['buffer']['all'], "!", 1 )-1;
         $name = substr( $con['buffer']['all'], 1, $nameend);
         $bot->cmd_send("PRIVMSG ".$name." :".$CONFIG['version']."");
       }
       else if( stripos( $con['buffer']['all'], 'JOIN #' ) )
       {
         //:Aaron5367!~Aaron5367@99-195-146-130.dyn.centurytel.net JOIN #knightsofreason
         $parts = explode( " ", $con['buffer']['all'] );
         $name = explode( "!", $parts['0'] );
         $hostmask = $name[1];
         $name = ltrim( $name['0'], ":" );
         $channel = $parts['2'];
         if( $bot->check_admin( $hostmask ) && $name != $CONFIG['nick'] )
         {
           $bot->cmd_send( "MODE $channel +v $name" );
           $bot->cmd_send( "MODE $channel +o $name" );
         }
         else if( $name != $CONFIG['nick'] )
           $bot->cmd_send( "MODE $channel +v $name" );
       }
      
      $start = strpos( $con['buffer']['all'], ":", 1 )+1;
      $text = substr( $con['buffer']['all'], $start );
        
      if( preg_match( "/.{1,500}\.(com|org|net|co\.uk|us|tk|rs|uk|gov|de|es)/i", $text ) != NULL )
      {
        $bufarray = explode( " ", $con['buffer']['all'] );
        $channel = $bufarray['2'];
        for( $i=0; $i<3; $i++ )
          unset( $bufarray[$i] );
        $bufarray = array_values( $bufarray );
        $urls = preg_grep( "@\.(com|org|net|co\.uk|us|tk|rs|uk|gov|de|es)@", $bufarray );
        $urls = array_values( $urls );
        
        //print_r( $urls );
        if( count( $urls ) > 3 )
          return;
        for( $i=0; $i<count($urls);$i++ )
        {
          $url = trim( $urls[$i], "\x00..\x1F" );
          $url = ltrim( $urls[$i], ":" );
          $titles[$i] = $bot->snarf_url( $url );
          $urlarray[$i] = explode( "/", $url );
          echo "<pre>";
          print_r( $urlarray[$i] );
          echo "</pre>";
          if( count( $urlarray[$i] ) > 1 && ( stripos( $url, "http://" ) !== FALSE || stripos( $url, "ftp://" ) !== FALSE || stripos( $url, "https://" ) !== FALSE ) )
          {
            for( $j=0; $j<count($urlarray[$i]);$j++ )
            {
              if( $urlarray[$i][$j] == NULL && stripos( $urlarray[$i][$j-1], ":" ) && $j > 0 )
              {
                $url = $urlarray[$i][$j+1];
                break;
              }
            }
          }
          else
            $url = $titles[$i];
            
          if( $url != NULL && $titles[$i] != NULL )
          {
            $bstatus['snarfs']++;
            $bot->talk( $channel, "(".$url.") ".$titles[$i] );
          }
        }
        unset( $titles );
        unset( $urlarray );
        unset( $urls );
        unset( $url );
      }//:Aaron5367!~Aaron5367@75-121-28-61.dyn.centurytel.net INVITE Moobot5367 #supersecretroom
      else if( preg_match( "/\:*.INVITE ".preg_quote( $CONFIG['nick'] )."/", $con['buffer']['all'] ) )
      {
        $bufarray = explode( " ", $con['buffer']['all'] );
        $channel = $bufarray['3'];
        $bot->cmd_send( "JOIN $channel" );
      }
    }
  }
}

?>
