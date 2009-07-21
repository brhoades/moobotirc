<?php

//brackets are required, parenthesis are optional
$commandtree = 
array( 
    array
    ("rehash", "\$commands->rehash();", TRUE,
      "Restarts moobot compeltely, so that it loads its file again.",
      ""
    ),
    array
    ("uptime", "\$commands->uptime();", FALSE,
      "Shows the uptime of Moobot's server.",
      ""
    ),
    array
    ("sysinfo", "\$commands->sysinfo();", FALSE,
      "Shows detailed information about Moobot's server.",
      ""
    ),
    array
    ("svnmon", "\$commands->svnmon();", TRUE,
      "Enables or disables Moobot's svn monitor for the channel it is called in.",
      ""
    ),
    array
    ("rot13", "\$commands->rot13();", FALSE,
      "Converts a string into rot13.",
      "[text]"
    ),
    array
    ("google", "\$commands->google();", FALSE,
      "Converts a term into a lmgtfy link.",
      "[term]"
    ),
    array
    ("urban", "\$commands->urban();", FALSE,
      "Looks up the first definition of a term on urban dictionary.",
      "[term]"
    ),
    array
    ("help", "\$commands->help();", FALSE,
      "Returns a list of all commands available, or help on a specific one.",
      "(command)"
    ),
    array
    ("server", "\$commands->server();", FALSE,
      "Returns information about the server specified.",
      "([serveralias]|[ip]:[port])"
    ),
    array
    ("servers", "\$commands->servers();", FALSE,
      "Returns information about the servers owned by the group specified.",
      "[group]"
    ),
    array
    ("find", "\$commands->find();", FALSE,
      "Finds a person on any tremulous server with the contents of the first argument in their name.",
      "[name]"
    ),
    array
    ("clan", "\$commands->clan();", FALSE,
      "Locates any clan members in KoR.",
      ""
    ),
    array
    ("msgs", "\$commands->server_command();", TRUE,
      "Messages someone on a certain server, with a certain name.",
      "[server alias] [user] [message]"
    ),
    array
    ("says", "\$commands->server_command();", TRUE,
      "Says something to a server.",
      "[server alias] [message]"
    ),
    array
    ("cmds", "\$commands->server_command();", TRUE,
      "Sends a command to a server.",
      "[server alias] [command]"
    ),
    array
    ("join", "\$commands->join();", TRUE,
      "Makes this bot join a channel.",
      "[channel]"
    ),
    array
    ("mute", "\$commands->mute();", TRUE,
      "Mutes a person in the current channel.",
      "[target]"
    ),
    array
    ("unmute", "\$commands->mute();", TRUE,
      "Unmutes a person in the current channel.",
      "[target]"
    ),
    array
    ("op", "\$commands->op();", TRUE,
      "Grants a person in the current channel operator status.",
      "[target]"
    ),
    array
    ("deop", "\$commands->op();", TRUE,
      "Denies a person in the current channel operator status.",
      "[target]"
    ),
    array
    ("callvote", "\$commands->callvote();", FALSE,
      "Calls a vote in the current channel.",
      "[type] [message/target]"
    ),
    array
    ("part", "\$commands->part();", TRUE,
      "Causes this bot to leave a channel.",
      "[channel] (reason)"
    ),
    array
    ("msg", "\$commands->msg();", TRUE,
      "Causes this message a channel or person.",
      "[channel/username] [message]"
    ),
    array
    ("passvote", "\$commands->finishvote();", TRUE,
      "Passes a vote that is in progress, in the current channel.",
      ""
    ),
    array
    ("cancelvote", "\$commands->finishvote();", TRUE,
      "Fails a vote that is in progress, in the current channel.",
      ""
    ),
    array
    ("status", "\$commands->status();", FALSE,
      "Returns a bot-related status report.",
      ""
    ),
    array
    ("invite", "\$commands->invite();", TRUE,
      "Invites a person to the current channel.",
      "[target]"
    ),
    array
    ("autoop", "\$commands->autoop();", TRUE,
      "Toggles auto op/auto voice in this channel.",
      ""
    ),
    array
    ("ccmds", "\$commands->invite();", TRUE,
      "Toggles command usage in this channel.",
      ""
    ),
    array
    ("weather", "\$commands->weather();", FALSE,
      "Returns a weather report from weatherunderground.",
      "[zipcode|cityname]"
    )
);

$commands = new commands();

class commands
{
  
  function rehash( )
  {
    global $bot, $con;
    
    $bot->cmd_send( "QUIT :Killed by ".$con['name'] );
    exec( "php ".$CONFIG[pathtoourself] );
    sleep( 1 );
    die( "Restart time" );
  }
  
  function uptime( )
  { 
    global $con, $bot;

    exec( "uptime", $uptime );
    $bot->talk( $con['channel'], "The server I am hosted on has the following uptime:" );
    $bot->talk( $con['channel'], $uptime[0] );
  }
  
  function sysinfo( )
  {
    global $con, $bot;
    
    //I wish all functions were like this
    $bot->talk( $con['channel'], $bot->sysinfo() );
  }
  
  function svnmon( )
  {
    global $bot, $CONFIG, $con;
    
    if( $CONFIG[svnmon] )
    {
      $CONFIG[svnmon] = FALSE;
      $bot->talk( $con['channel'], "SVN monitor is now off, globally." );
    }
    else
    {
      $CONFIG[svnmon] = TRUE;
      $bot->talk( $con['channel'], "SVN monitor is now on, globally." );
    }
  }
  
  function rot13( )
  {
    global $bot, $con;
    
    $newtext = str_rot13( trim( implode( " ", $con['bufarray'] ) ) );
    $bot->talk( $con['channel'] , $newtext );
  }
  
  function google( )
  {
    global $con, $bot;
    
    $url = trim( implode( "+", $con['textarray'] ) );
    $bot->talk( $con['channel'], "http://letmegooglethatforyou.com/?q=$url" );
  }
  
  function urban( )
  {
    global $bot, $con;
    
    if( $con['bufarray'][0] == NULL )
    {
      $bot->talk( $con['channel'], "Please use the following syntax:" );
      $bot->talk( $con['channel'], "%urban term" );
      return;
    }
    
    if( $con['bufarray'][1] == NULL )
    {
      $term = $con['bufarray'][0];
      $def = $bot->urban_lookup( $term, 1 );
      if( count( $def ) > 5 )
      {
        $bot->talk( $con['channel'], "The definition is too long, you can view it at http://urbandictionary.com/define.php?term=$term" );
        return;
      }
      $bot->talk( $channel, "Definition of $term:" );
      for( $i=0; $i<count($def); $i++ )
        $bot->talk( $con['channel'], trim($def[$i]) );
    }
    else
    {
      $num = 1;
      $term = implode( " ", $con['bufarray'] );
      $def = $bot->urban_lookup( $term, $num );
      if( count( $def ) > 5 )
      {
        $max = 4;
        $bot->talk( $con['channel'], "Definition of $term:" );
        $page = ceil( $num/7 );
        for( $i=0; $i<$max; $i++ )
          $bot->talk( $con['channel'], $def[$i] );
        $bot->talk( $con['channel'], "[...]" );
        if( $page > 1 )
          $bot->talk( $con['channel'], "You can view the rest of the definition here: http://urbandictionary.com/define.php?page=$page&term=$term" );
        else
          $bot->talk( $con['channel'], "You can view the rest of the definition here: http://urbandictionary.com/define.php?term=$term" );
          return;
      }
      $bot->talk( $con['channel'], "Definition of $term:" );
      for( $i=0; $i<count($def); $i++ )
        $bot->talk( $con['channel'], trim($def[$i]) );
    }
  }
  
  function help( )
  {
    global $commandtree, $con, $bot;
    
    $hostmask = $con['hostmask'];
    $name = $con['name'];
    if( !$con['textarray'][1] )
    {
        $numcmds = count( $commandtree );
        $alwcmds = 0;

        $k=0;
        for( $i=0; $i<count( $commandtree ); $i++ )
        {
          if( ( $commandtree[$i][2] == TRUE && $bot->check_admin( $con['hostmask'] ) )
                || $commandtree[$i][2] != TRUE )
          {
            $alwcmds++;
            $cmdarray[$k] = "%".$commandtree[$i][0];
            $k++;
          }
        }
        $out[0] = "You are currently allowed to use $alwcmds/$numcmds of all the commands.";
        for( $i=0; $i<count( $cmdarray ); $i++ )
        {
          if( $i == 0 )
            $out[1] = $cmdarray[$i];
          else
            $out[1] .= " ".$cmdarray[$i];
        }
      }
      else
      {
        for( $i=0; $i<count( $commandtree ); $i++ )
        {
          if( $commandtree[$i][0] == $con['textarray'][1] )
          {
            $out[0] = "%".$con['textarray'][1]." ".$commandtree[$i][3];
            if( $commandtree[$i][4] != NULL )
              $out[1] = $commandtree[$i][4];
            else
              $out[1] = "No Arguments Required or Allowed";
          }
        }
        if( $out == NULL )
          $out[0] = "%".$con['textarray'][1].": Not found!";
      }
      
      for( $i=0; $i<count( $out ); $i++ )
        $bot->talk( $name, $out[$i] );
  }
  
  function server( )
  {
    global $con, $bot;
    
    if( count( $con['bufarray'] ) <= 0 )
    {
      $bot->talk( $con['channel'], $con['name'].": Please specify a server and port or an alias with this command." );
      return;
    }
    else if( count( $con['bufarray'] ) == 1 && stripos( $con['bufarray'][0], ":" ) === FALSE )
    { 
      $set = $con['bufarray'][0];
      if( !array_key_exists( strtoupper($set), $CONFIG['servers'] ) )
      {
        $bot->talk( $con['channel'], "I don't have any server stored for the alias $set, try one of the following:" );
        $bot->talk( $con['channel'], "X, AA, A<3, uBP, or SoH" );
        return;
      }
      $set = strtoupper($set);
      $ip = $CONFIG['servers'][$set]['ip'];
      $port = $CONFIG['servers'][$set]['port'];
      $backupname = $CONFIG['servers'][$set]['bakname'];
    }
    else if( count( $con['bufarray'] ) == 1 && stripos( $con['bufarray'][0], ":" ) !== FALSE )
    {
      $con['bufarray'] = explode( ":", $con['bufarray'][0] );
      $ip = $con['bufarray'][0];
      $port = $con['bufarray'][1];
    }
    else if( count( $con['bufarray'] ) == 2 )
    {
      $ip = $con['bufarray'][0];
      $port = $con['bufarray'][1];
    }
    else if( count( $con['bufarray'] ) > 2 )
    {
      $bot->talk( $con['channel'], "Too many arguments (".count($con['bufarray']).")." );
      return;
    }
    else
    {
      $bot->talk( $con['channel'], "Unknown arguments" );
      return;
    }
    $server = $bot->tremulous_get_players( $ip, $port );
    $serverinfo = $bot->get_server_settings( $ip, $port );
    $servername = $serverinfo['servername'];
    if( ( $servername == NULL || $servername == "" ) && $set != NULL )
      $servername = $backupname;
    else if( $servername == NULL || $servername == "" )
    {
      $bot->talk( $con['channel'], "Unable to get a valid server name from $ip:$port" );
      return;
    }
    $map = $server['map'];
    $players = count( $server[ alien_players ]  ) + count( $server[ spec_players ]  ) + count( $server[ human_players ]  );
    $maxplayers = $serverinfo['maxplayers'];
    if( ( $map == NULL || $map == "" ) && $set != NULL )
      $status = "OFFLINE";
    else if( ( $map == NULL || $map == "" ) && $servername != NULL )
    {
      $bot->talk( $con['channel'], "Unable to make further contact with $ip:$port, $servername" );
      return;
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
      $bot->talk( $con['channel'], $bot->tremulous_replace_colors_irc( $servername )." - $map - ($players/$maxplayers)" );
    else
      $bot->talk( $con['channel'], $bot->tremulous_replace_colors_irc( $servername )." - OFFLINE" );
  }
  
  function servers( )
  {
    global $con, $bot;
    
    if( $con['bufarray'][0] == NULL || $con['bufarray'][0] == "" )
      $set = "KOR";
    else
    {
      $pset = $con['bufarray'][0];
      if( !array_key_exists( strtoupper($pset), $CONFIG['servers'] ) )
      {
        $bot->talk( $con['channel'], "I don't have any servers stored for $pset, try one of the following:" );
        $bot->talk( $con['channel'], "KoR, MG, or RK" );
        return;
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
    unset( $map, $server, $players, $averageping );
  }
  
  //Make an array of the players for sorting
  for( $i=0;$i<count($servers);$i++ )
    $players[$i] = $servers[$i]['players'];
  
  //Sort!
  sort( $players );
  $players = array_reverse( $players );

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
        $bot->talk( $con['channel'], $bot->tremulous_replace_colors_irc( $name )." - $map - ($playernum/$maxplayers) - $averageping" );
      else
        $bot->talk( $con['channel'], $bot->tremulous_replace_colors_irc( $name )." - $map - ($playernum/$maxplayers)" );
    }
    else if( $status == "OFFLINE" )
      $bot->talk( $con['channel'], $bot->tremulous_replace_colors_irc( $name )." - OFFLINE" );
    else if( $status == "CRASHED" )
      $bot->talk( $con['channel'], $bot->tremulous_replace_colors_irc( $name )." - CRASHED" );
    }
  }
  
  function find( )
  {
    global $con, $bot;
    
    $player = $con['bufarray']['0'];
    $con['cached'] = count( $bot->find_servers() );
    $bot->talk( $con['channel'], "Searching ".$con['cached']." server(s) for \"$player\"..." );
    $found = $bot->find_player( $player );
    
    if( count( $found ) > 10 )
    {
      $bot->talk( $con['channel'], "Your search returned ".count( $found )." results, please be more specific with your search." );
      return;
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
          $bot->talk( $con['channel'], "Found $name on $server, with a ping of $ping, $kills kills, on the humans. " );
        else if( $team == "spectators" )
          $bot->talk( $con['channel'], "Found $name on $server, with a ping of $ping, on the spectators. " );
        else
          $bot->talk( $con['channel'], "Found $name on $server, with a ping of $ping, $kills kills, on the aliens. " );
      }
    }
    else
      $bot->talk( $con['channel'], "No players with ".$player." in their name." );
  }
  
  function clan()
  {
    global $con, $bot;
    $con['cached'] = count( $bot->find_servers() );
    $bot->talk( $channel, "Searching ".$con['cached']." server(s) for clan members..." );
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
          $bot->talk( $con['channel'], "Found $name on $server, with a ping of $ping, $kills kills, on the humans. " );
        else if( $team == "spectators" )
          $bot->talk( $con['channel'], "Found $name on $server, with a ping of $ping, on the spectators. " );
        else
          $bot->talk( $con['channel'], "Found $name on $server, with a ping of $ping, $kills kills, on the aliens. " );
      }
    }
    else
      $bot->talk( $con['channel'], "No clan members were found." );
  }
  
  function server_command( )
  {
    global $bot, $name;
    
    $command = $con['command'];
    $name = $con['name'];
    $serveralias = $con['textarray']['0'];
    unset( $con['textarray']['0'] );
    if( $command == "msgs" )
    {
      $target = $con['textarray']['1'];
      unset( $con['textarray']['1'] );
    }
    $con['textarray'] = array_values( $con['textarray'] );
    $message = implode( " ", $con['textarray'] );
    if( $serveralias != "korx1" && $serveralias != "korx2" )
    {
      $bot->talk( $con['channel'], "The alias $serveralias is not known, please use korx1 or korx2." );
      return;
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
    
    if( $command == "msgs" )
      $message2 = $bot->tremulous_rcon( $ip, $port, "m $target ^2$message", $rcon, "FALSE" );
    else if( $command == "says" )
      $message2 = $bot->tremulous_rcon( $ip, $port, "!print ^7[IRC][$name^7]: ^2$message", $rcon, "FALSE" );
    else if( stripos( trim($message), "!" ) !== FALSE &&  stripos( trim($message), "!" ) == 0 )
      $message2 = $bot->tremulous_rcon( $ip, $port, $message, $rcon, "FALSE" );
    else
    {
      $bot->talk( $con['channel'], "$name: Please use commands, no variable modifications please." );
      return;
    }
    
    if( count( $message2 ) > 5 )
    {
      $bot->talk( $con['channel'], "That returned more than 5 lines of text. It was executed, however. Feel free to try a PM." );
      return;
    }
    else
    {
      for( $i=0; $i<count($message2); $i++ )
        $bot->talk( $con['channel'], $message2[$i] );
    }
  }
  
  function join( )
  {
    global $bot,  $con;
    
    $bot->cmd_send("JOIN ".$con['bufarray'][ 0 ] );
    $bot->talk( $con['channel'], $name.": ".$con['bufarray'][0]." has been joined." );
  }
  
  function mute( )
  {
    global $bot, $con;
    
    $command = $con['command'];
    if( $con['bufarray'][0] == NULL )
    {
      $bot->talk( $con['channel'], "Please specify a valid target." );
      return;
    }
    
    if( $command == "mute" )
      $bot->cmd_send( "MODE ".$con['channel']." +v ".$con['bufarray'][0] );
    else 
      $bot->cmd_send( "MODE ".$con['channel']." +v ".$con['bufarray'][0] );
  }
  
  function op( )
  {
    global $con, $bot; 
    
    $command = $con['command'];
    if( $con['bufarray'][0] == NULL )
    {
      $bot->talk( $con['channel'], "Please specify a valid target." );
      return;
    }
    
    if( $command == "op" )
      $bot->cmd_send( "MODE $channel +o ".$con['bufarray'][0] );
    else
      $bot->cmd_send( "MODE $channel -o ".$con['bufarray'][0] );
  }
  
  function callvote( )
  {
    global $con, $bot;
    
    $type = $con['bufarray']['0'];
    unset( $con['bufarray']['0'] );
    $string = implode( " ", $con['bufarray'] );
    if( stripos( $con['channel'], "#" ) === FALSE )
    {
      $bot->talk( $con['channel'], "Sorry, but please only use callvote in a channel." );
      return;
    } 
    $bot->call_vote( $string, $type, $name, $con['channel'] );
  }
  
  function part( )
  {
    global $bot, $con;
    
    $name = $con['name'];
    $channel = $con['bufarray'][0];
    if( $con['bufarray'][1] != NULL )
    {
      unset( $con['bufarray'][0] );
      $pmessage = implode( " ", $con['bufarray'] );                
    }
    $bot->cmd_send( "PART $channel :$pmessage" );
    if( $channel != $con['bufarray'][0] )
      $bot->talk( $con['channel'], $name.": I have parted ".$con['bufarray'][0] );
  }
  
  function msg( )
  {
    global $con, $bot;
    
    $target = $con['bufarray']['0'];
    unset( $con['bufarray']['0'] );
    $text = implode( " ", $con['bufarray'] );
    $bot->talk( $target, $text );
    $bot->talk( $con['channel'], "Message sent to $target" );
  }
  
  function finishvote( )
  {
    global $con, $bot;
    
    $command = $con['command'];
    if( $con['vote']['inprogress'] == FALSE )
    {
      $bot->talk( $con['channel'], "There is no vote currently in progress." );
      return;
    }
    
    if( $command == "passvote" )
    {
      $con['vote']['endtime'] = time()-1;
      $con['vote']['yes'] = 1;
      $con['vote']['no'] = 0;
    }
    else
    {
      $con['vote']['endtime'] = time()-1;
      $con['vote']['yes'] = 0;
      $con['vote']['no'] = 1;
    }
  }
  
  function status( )
  {
    global $other, $bot, $bstatus;
    
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
    
    $bot->talk( $con['channel'], "I've been up for $uptime, sent $cmdstoserv command(s) to the server, processed $lines line(s) of text, talked $talked time(s), ran $cmds command(s), snarfed $snarfs url(s), and contacted a tremulous server $servers time(s)." );
    return;
  }
  
  function weather( )
  {
    global $bot, $con;
    
    $lugar = implode( " ", $con['bufarray'] );
    $out = $bot->weather( $lugar );
    $wind = $out['wind'];
    $pressure = $out['pressure'];
    $advisories = $out['advisories'];
    $clouds = $out['clouds'];
    $humidity = $out['humidity'];
    $place = $out['place'];
    $temp = $out['temp'];
    
    if( $place != NULL && $wind != NULL && $humidity != NULL )
    {
      $bot->talk( $con['channel'], "Weather report for: $place" );
      $bot->talk( $con['channel'], "Temp: $temp | Humidity: $humidity | Clouds: $clouds | Pressure: $pressure | Wind: $wind " );
      if( $advisories != NULL )
        $bot->talk( $con['channel'], "Advisories: $advisories" );
    }
    else
      $bot->talk( $con['channel'], "That zip code/location was not found or was ambiguous." );
  }
  
  function invite( )
  {
    global $con, $bot;
    
    if( $con['bufarray'][0] == NULL )
    {
      $bot->talk( $con['channel'], "Please specify a valid target for this command." );
      return;
    }

    $bot->cmd_send( "INVITE ".$channel." ".$con['bufarray'][0] );
    $bot->talk( $con['channel'], "Invited" );
  }
  
  function autoop( )
  {
    global $con, $bot, $channels;
    
    $chanid = $con['chanid'];
    if( $channels[$chanid]['autoopvoice'] != TRUE )
    {
      $channels[$chanid]['autoopvoice'] = TRUE; 
      $bot->talk( $con['channel'], "Auto Op and Voice enabled" );
    }
    else
    {
      $channels[$chanid]['autoopvoice'] = FALSE; 
      $bot->talk( $con['channel'], "Auto Op and Voice disabled" );
    }
  }
  
  function ccmds( )
  {
    global $channels, $bot, $con;
    
    $chanid = $con['chanid'];
    if( $channels[$chanid]['cmds'] != TRUE )
    {
      $channels[$chanid]['cmds'] = TRUE; 
      $bot->talk( $con['channels'], "Commands enabled" );
    }
    else
    {
      $channels[$chanid]['cmds'] = FALSE; 
      $bot->talk( $con['channels'], "Commands disabled" );
    }
  }
  
}
?>
