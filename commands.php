<?php

//brackets are required, parenthesis are optional
$commandtree = 
array( 
    array
    ("rehash", "\$commands->rehash( \$name );", TRUE,
      "Restarts moobot compeltely, so that it loads its file again.",
      ""
    ),
    array
    ("uptime", "\$commands->uptime( \$channel );", FALSE,
      "Shows the uptime of Moobot's server.",
      ""
    ),
    array
    ("sysinfo", "\$commands->sysinfo( \$channel );", FALSE,
      "Shows detailed information about Moobot's server.",
      ""
    ),
    array
    ("svnmon", "\$commands->svnmon( \$channel, \$chanid );", TRUE,
      "Enables or disables Moobot's svn monitor for the channel it is called in.",
      ""
    ),
    array
    ("rot13", "\$commands->rot13( \$channel, \$bufarray );", FALSE,
      "Converts a string into rot13.",
      "[text]"
    ),
    array
    ("google", "\$commands->google( \$channel, \$textarray );", FALSE,
      "Converts a term into a lmgtfy link.",
      "[term]"
    ),
    array
    ("urban", "\$commands->urban( \$channel, \$bufarray );", FALSE,
      "Looks up the first definition of a term on urban dictionary.",
      "[term]"
    ),
    array
    ("help", "\$commands->help( \$hostmask, \$name, \$textarray );", FALSE,
      "Returns a list of all commands available, or help on a specific one.",
      "(command)"
    ),
    array
    ("server", "\$commands->server( \$bufarray, \$channel, \$name );", FALSE,
      "Returns information about the server specified.",
      "([serveralias]|[ip]:[port])"
    ),
    array
    ("servers", "\$commands->servers( \$bufarray, \$channel, \$name );", FALSE,
      "Returns information about the servers owned by the group specified.",
      "[group]"
    ),
    array
    ("find", "\$commands->find( \$bufarray, \$channel );", FALSE,
      "Finds a person on any tremulous server with the contents of the first argument in their name.",
      "[name]"
    ),
    array
    ("clan", "\$commands->clan( \$channel );", FALSE,
      "Locates any clan members in KoR.",
      ""
    ),
    array
    ("msgs", "\$commands->server_command( \$name, \$textarray, \$command, \$channel );", TRUE,
      "Messages someone on a certain server, with a certain name.",
      "[server alias] [user] [message]"
    ),
    array
    ("says", "\$commands->server_command( \$name, \$textarray, \$command, \$channel );", TRUE,
      "Says something to a server.",
      "[server alias] [message]"
    ),
    array
    ("cmds", "\$commands->server_command( \$name, \$textarray, \$command, \$channel );", TRUE,
      "Sends a command to a server.",
      "[server alias] [command]"
    ),
    array
    ("join", "\$commands->join( \$bufarray, \$channel, \$name );", TRUE,
      "Makes this bot join a channel, and join it in all future sessions.",
      "[channel] (password)"
    ),
    array
    ("mute", "\$commands->mute( \$command, \$bufarray, \$channel );", TRUE,
      "Mutes a person in the current channel.",
      "[target]"
    ),
    array
    ("unmute", "\$commands->mute( \$command, \$bufarray, \$channel);", TRUE,
      "Unmutes a person in the current channel.",
      "[target]"
    ),
    array
    ("op", "\$commands->op( \$command, \$bufarray, \$channel );", TRUE,
      "Grants a person in the current channel operator status.",
      "[target]"
    ),
    array
    ("deop", "\$commands->op( \$command, \$bufarray, \$channel );", TRUE,
      "Denies a person in the current channel operator status.",
      "[target]"
    ),
    array
    ("callvote", "\$commands->callvote( \$bufarray, \$channel, \$name );", FALSE,
      "Calls a vote in the current channel.",
      "[type] [message/target]"
    ),
    array
    ("part", "\$commands->part( \$bufarray, \$name, \$channel );", TRUE,
      "Causes this bot to leave a channel, and no longer automatically join it.",
      "[channel] (reason)"
    ),
    array
    ("msg", "\$commands->msg( \$bufarray, \$channel );", TRUE,
      "Causes this message a channel or person.",
      "[channel/username] [message]"
    ),
    array
    ("passvote", "\$commands->finishvote( \$command, \$channel );", TRUE,
      "Passes a vote that is in progress, in the current channel.",
      ""
    ),
    array
    ("cancelvote", "\$commands->finishvote( \$command, \$channel );", TRUE,
      "Fails a vote that is in progress, in the current channel.",
      ""
    ),
    array
    ("status", "\$commands->status( \$channel );", FALSE,
      "Returns a bot-related status report.",
      ""
    ),
    array
    ("invite", "\$commands->invite( \$channel, \$bufarray );", TRUE,
      "Invites a person to the current channel.",
      "[target]"
    ),
    array
    ("autoop", "\$commands->autoop( \$channel, \$chanid );", TRUE,
      "Toggles auto op/auto voice in this channel.",
      ""
    ),
    array
    ("ccmds", "\$commands->ccmds( \$channel, \$chanid );", TRUE,
      "Toggles command usage in this channel.",
      ""
    ),
    array
    ("weather", "\$commands->weather( \$channel, \$bufarray );", FALSE,
      "Returns a weather report from weatherunderground.",
      "[zipcode|cityname]"
    ),
    array
    ("kick", "\$commands->kick( \$channel, \$bufarray, \$name );", TRUE,
      "Kicks a user with an optional reason.",
      "[target] (reason)"
    ),
    array
    ("topic", "\$commands->topic( \$channel, \$bufarray );", TRUE,
      "Changes the topic for a channel.",
      "[topic]"
    ),
    array
    ("hgmon", "\$commands->hgmon( \$channel, \$chanid );", TRUE,
      "Enables or disables Moobot's hg (mercurial) monitor for the channel it is called in.",
      ""
    ),
    array
    ("listchannels", "\$commands->listchannels( \$channel );", FALSE,
      "Lists channels that this bot is on.",
      ""
    ),
    array
    ("addadmin", "\$commands->addadmin( \$channel, \$bufarray );", TRUE,
      "Adds an admin for a user name.",
      "[hostmask]"
    ),
    array
    ("password", "\$commands->override_password( \$channel, \$hostmask, \$bufarray );", FALSE,
      "Uses the admin password, defined in the config file, to grant the user admin status.",
      "[password]"
    ),
    array
    ("snarf", "\$commands->snarf_toggle( \$channel, \$chanid );", TRUE,
      "Toggles snarfing in the current channel.",
      ""
    ),
    array
    ("listadmins", "\$commands->list_admins( \$channel, \$textarray );", FALSE,
      "Lists current admins",
      "(offset|name)"
    )
);

$commands = new commands();

class commands
{
  
  function rehash( $name )
  {
    global $bot, $con, $CONFIG;
    
    $bot->cmd_send( "QUIT :Killed by ".$name );
    exec( "php ".$CONFIG[pathtoourself] );
    sleep( 1 );
    die( "Restart time" );
  }
  
  function uptime( $channel )
  { 
    global $con, $bot;

    exec( "uptime", $uptime );
    $bot->talk( $channel, "The server I am hosted on has the following uptime:" );
    $bot->talk( $channel, $uptime[0] );
  }
  
  function sysinfo( $channel )
  {
    global $con, $bot;
    
    //I wish all functions were like this
    $bot->talk( $channel, $bot->sysinfo() );
  }
  
  function svnmon( $channel, $chanid )
  {
    global $bot, $con;
    
    if( $channel != $con['data'][channels][$chanid]['name'] )
    {
      $bot->talk( $channel, "You can't turn svnmon off in a PM." );
      return;     //Likely a PM or some weird bug
    }
    
    if( $con['data'][channels][$chanid]['svnmon'] == TRUE )
    {
      $con['data'][channels][$chanid]['svnmon'] = FALSE;
      $bot->talk( $channel, "SVN monitor is now off for this channel." );
    }
    else
    {
      $con['data'][channels][$chanid]['svnmon']  = TRUE;
      $bot->talk( $channel, "SVN monitor is now on for this channel." );
    }
    
    $bot->writedata( $con['data'] );
  }
  
  function hgmon( $channel, $chanid )
  {
    global $bot, $con;
    
    if( $channel != $con['data'][channels][$chanid]['name'] )
    {
      $bot->talk( $channel, "You can't turn hgmon off in a PM." );
      return;     //Likely a PM or some weird bug
    }
    
    if( $con['data'][channels][$chanid]['hgmon'] == TRUE )
    {
      $con['data'][channels][$chanid]['hgmon'] = FALSE;
      $bot->talk( $channel, "HG monitor is now off for this channel." );
    }
    else
    {
      $con['data'][channels][$chanid]['hgmon']  = TRUE;
      $bot->talk( $channel, "HG monitor is now on for this channel." );
    }
    
    $bot->writedata( $con['data'] );
  }
  
  function rot13( $channel, $bufarray )
  {
    global $bot, $con;
    
    $newtext = str_rot13( trim( implode( " ", $bufarray ) ) );
    $bot->talk( $channel, $newtext );
  }
  
  function google( $channel, $textarray )
  {
    global $con, $bot;
    
    $url = trim( implode( "+", $textarray ) );
    $bot->talk( $channel, "http://letmegooglethatforyou.com/?q=$url" );
  }
  
  function urban( $channel, $bufarray )
  {
    global $bot, $con;
    
    if( $bufarray[0] == NULL )
    {
      $bot->talk( $channel, "Please use the following syntax:" );
      $bot->talk( $channel, "%urban term" );
      return;
    }
    
    if( $bufarray[1] == NULL )
    {
      $term = $bufarray[0];
      $def = $bot->urban_lookup( $term, 1 );
      if( count( $def ) > 5 )
      {
        $bot->talk( $channel, "The definition is too long, you can view it at http://urbandictionary.com/define.php?term=$term" );
        return;
      }
      $bot->talk( $channel, "Definition of $term:" );
      for( $i=0; $i<count( $def ); $i++ )
        $bot->talk( $channel, trim($def[$i]) );
    }
    else
    {
      $num = 1;
      $term = implode( " ", $bufarray );
      $def = $bot->urban_lookup( $term, $num );
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
          return;
      }
      $bot->talk( $channel, "Definition of $term:" );
      for( $i=0; $i<count($def); $i++ )
        $bot->talk( $channel, trim($def[$i]) );
    }
  }
  
  function help( $hostmask, $name, $textarray )
  {
    global $commandtree, $con, $bot;
    
    if( !$textarray[0] )
    {
        $numcmds = count( $commandtree );
        $alwcmds = 0;

        $k=0;
        for( $i=0; $i<count( $commandtree ); $i++ )
        {
          if( ( $commandtree[$i][2] == TRUE && $bot->check_admin( $hostmask ) )
                || $commandtree[$i][2] != TRUE )
          {
            $alwcmds++;
            $cmdarray[$k] = "%".$commandtree[$i][0];
            $k++;
          }
        }
        $out[0] = "You are currently allowed to use $alwcmds/$numcmds of the commands.";
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
        $textarray[0] = ltrim( $textarray[0], "%" );
        for( $i=0; $i<count( $commandtree ); $i++ )
        {
          if( $commandtree[$i][0] == $textarray[0] )
          {
            rtrim( $textarray, "%" );
            $out[0] = "%".$textarray[0]." ".$commandtree[$i][3];
            if( $commandtree[$i][4] != NULL )
              $out[1] = $commandtree[$i][4];
            else
              $out[1] = "No arguments required or allowed.";
          }
        }
        if( $out == NULL )
          $out[0] = "%".$textarray[0].": Not found!";
      }
      
      for( $i=0; $i<count( $out ); $i++ )
        $bot->talk( $name, $out[$i] );
  }
  
  function server( $bufarray, $channel, $name )
  {
    global $con, $bot, $CONFIG;
    
    if( count( $bufarray ) <= 0 )
    {
      $bot->talk( $channel, $name.": Please specify a server and port or an alias with this command." );
      return;
    }
    else if( count( $bufarray ) == 1 && stripos( $bufarray[0], ":" ) === FALSE )
    { 
      $set = $bufarray[0];
      if( !array_key_exists( strtoupper($set), $CONFIG['servers'] ) )
      {
        $bot->talk( $channel, "I don't have any server stored for the alias $set, try one of the following:" );
        $bot->talk( $channel, "X, AA, A<3, uBP, or SoH" );
        return;
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
      return;
    }
    else
    {
      $bot->talk( $channel, "Unknown arguments" );
      return;
    }
    $server = $bot->tremulous_get_players( $ip, $port );
    $servername = $server['servername'];
    if( ( $servername == NULL || $servername == "" ) && $set != NULL )
      $servername = $backupname;
    else if( $servername == NULL || $servername == "" )
    {
      $bot->talk( $channel, "Unable to get a valid server name from $ip:$port" );
      return;
    }
    $map = $server['map'];
    $players = count( $server[ alien_players ]  ) + count( $server[ spec_players ]  ) + count( $server[ human_players ]  );
    $maxplayers = $server['maxplayers'];
    if( ( $map == NULL || $map == "" ) && $set != NULL )
      $status = "OFFLINE";
    else if( ( $map == NULL || $map == "" ) && $servername != NULL )
    {
      $bot->talk( $channel, "Unable to make further contact with $ip:$port, $servername" );
      return;
    }
    
    if( $status != "OFFLINE" )
      $bot->talk( $channel, $bot->tremulous_replace_colors_irc( $servername )." - $map - ($players/$maxplayers)", TRUE );
    else
      $bot->talk( $channel, $bot->tremulous_replace_colors_irc( $servername )." - OFFLINE", TRUE );
  }
  
  function servers( $bufarray, $channel, $name )
  {
    global $con, $bot, $CONFIG;
    
    if( $bufarray[0] == NULL || $bufarray[0] == "" )
      $set = "KOR";
    else
    {
      $pset = $bufarray[0];
      if( !array_key_exists( strtoupper( $pset ), $CONFIG['servers'] ) )
      {
        $bot->talk( $channel, "I don't have any servers stored for $pset, try one of the following:" );
        $bot->talk( $channel, "KoR, MG, or RK" );
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
    $servername = $server['servername'];
    if( $servername == NULL || $servername == "" )
      $servername = $backupname;
    $map = $server['map'];
    $players = count( $server[ alien_players ]  ) + count( $server[ spec_players ]  ) + count( $server[ human_players ]  );
    $maxplayers = $server['maxplayers'];
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
        $bot->talk( $channel, $bot->tremulous_replace_colors_irc( $name )." - $map - ($playernum/$maxplayers) - $averageping" );
      else
        $bot->talk( $channel, $bot->tremulous_replace_colors_irc( $name )." - $map - ($playernum/$maxplayers)" );
    }
    else if( $status == "OFFLINE" )
      $bot->talk( $channel, $bot->tremulous_replace_colors_irc( $name )." - OFFLINE" );
    else if( $status == "CRASHED" )
      $bot->talk( $channel, $bot->tremulous_replace_colors_irc( $name )." - CRASHED" );
    }
  }
  
  function find( $bufarray, $channel )
  {
    global $con, $bot;
    
    $player = $bufarray[0];
    if( !$player )
    {
      $bot->talk( $channel, "Please specify someone to search for." );
      return;
    }
    $bot->talk( $channel, "Searching ".$con['cached']." servers for $player...", TRUE );
    $found = $bot->find_player( $player );
    
    if( count( $found ) > 10 )
    {
      $bot->talk( $channel, "Your search returned ".count( $found )." results, please be more specific with your search." );
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
          $bot->talk( $channel, "Found $name on $server, with a ping of $ping, $kills kills, on the humans. " );
        else if( $team == "spectators" )
          $bot->talk( $channel, "Found $name on $server, with a ping of $ping, on the spectators. " );
        else
          $bot->talk( $channel, "Found $name on $server, with a ping of $ping, $kills kills, on the aliens. " );
      }
    }
    else
      $bot->talk( $channel, "No players with ".$player." in their name." );
  }
  
  function clan( $channel )
  {
    global $con, $bot;

    $bot->talk( $channel, "Searching ".$con['cached']." servers for clan members...", TRUE );
    $found = $bot->find_player( "KoR" );
    
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
  }
  
  function server_command( $name, $textarray, $command, $channel )
  {
    global $bot, $CONFIG;
    
    $serveralias = $textarray['0'];
    unset( $textarray['0'] );
    if( $command == "msgs" )
    {
      $target = $textarray['1'];
      unset( $textarray['1'] );
    }
    $textarray = array_values( $textarray );
    $message = implode( " ", $textarray );
    if( $serveralias != "korx" && $serveralias != "layouts" )
    {
      $bot->talk( $channel, "The alias $serveralias is not known, please use korx or layouts." );
      return;
    }
    else if( $serveralias == "korx" )
    {
      $ip = $CONFIG['servers']['KOR'][0]['ip'];
      $port = $CONFIG['servers']['KOR'][0]['port'];
      $rcon = $CONFIG['servers']['KOR'][0]['rcon'];
    }
    else if( $serveralias == "layouts" )
    {
      $ip = $CONFIG['servers']['KOR'][1]['ip'];
      $port = $CONFIG['servers']['KOR'][1]['port'];
      $rcon = $CONFIG['servers']['KOR'][1]['rcon'];
    }
    
    if( $command == "msgs" )
      $message2 = $bot->tremulous_rcon( $ip, $port, "m $target ^2$message", $rcon );
    else if( $command == "says" )
      $message2 = $bot->tremulous_rcon( $ip, $port, "!print ^7[IRC][$name^7]: ^2$message", $rcon );
    else if( stripos( trim($message), "!" ) !== FALSE &&  stripos( trim($message), "!" ) == 0 )
      $message2 = $bot->tremulous_rcon( $ip, $port, $message, $rcon, "FALSE" );
    else
    {
      $bot->talk( $channel, "$name: Please use commands, no variable modifications please." );
      return;
    }
    
    if( count( $message2 ) > 5 && stripos( $channel, "#" ) !== FALSE )
    {
      $bot->talk( $channel, "That returned more than 5 lines of text. It was executed, however. Feel free to try a PM." );
      return;
    }
    else
    {
      for( $i=0; $i<count($message2); $i++ )
      {
        if( $i != count( $message2 ) - 1 )
          $bot->talk( $channel, $message2[$i] );
      }
    }
  }
  
  function join( $bufarray, $channel, $name )
  {
    global $bot,  $con;
    
    if( stripos( $bufarray[0], "#" ) === FALSE )
    {
      $bot->talk( $channel, $name.": That doesn't appear to be a valid channel" );
      return;
    }

    for( $i=0; $i<count( $con['data'][channels] ); $i++ )
    {
      if( $con['data'][channels][$i]['name'] == $bufarray[0]
          && $con['data'][channels][$i]['active'] == TRUE )
      {
        $bot->talk( $channel, "Erm... I'm already there ".$name );
        return;
      }
      else if( $con['data'][channels][$i]['name'] == $bufarray[0]
               && $con['data'][channels][$i]['active'] == TRUE )
      {
        $bot->talk( $channel, "That channel is now active again (rejoined)" );
        if( $bufarray[1] != NULL )
          $bot->cmd_send ("JOIN ".$bufarray[0]." ".$bufarray[1] );
        else
          $bot->cmd_send( "JOIN ".$bufarray[0] );
        $con['data'][channels][$i]['active'] = TRUE;
        $bot->writedata( $con['data'] );
        return;
      }
    }
    
    
    if( $bufarray[1] != NULL )
      $bot->cmd_send ("JOIN ".$bufarray[0]." ".$bufarray[1] );
    else
      $bot->cmd_send( "JOIN ".$bufarray[0] );

    $id = count( $con['data'][channels] );
    $bot->talk( $channel, $name.": ".$bufarray[0]." has been joined, and will be automatically joined in the future." );
    $con['data'][channels][$id]['name'] = $bufarray[0];
    $con['data'][channels][$id]['password'] = $bufarray[1];
    $con['data'][channels][$id]['svnmon'] = FALSE;
    $con['data'][channels][$id]['hgmon'] = FALSE;
    $con['data'][channels][$id]['log'] = TRUE;
    $con['data'][channels][$id]['cmds'] = TRUE;
    $con['data'][channels][$id]['autoopvoice'] = FALSE;
    $con['data'][channels][$id]['snarf'] = TRUE;
    $con['data'][channels][$id]['active'] = TRUE;
    $bot->writedata( $con['data'] );
  }
  
  function mute( $command, $bufarray, $channel )
  {
    global $bot, $con;
    
    if( $bufarray[0] == NULL )
    {
      $bot->talk( $channel, "Please specify a valid target." );
      return;
    }
    
    if( $command == "mute" )
      $bot->cmd_send( "MODE ".$channel." -v ".$bufarray[0] );
    else 
      $bot->cmd_send( "MODE ".$channel." +v ".$bufarray[0] );
  }
  
  function op( $command, $bufarray, $channel )
  {
    global $con, $bot; 
    
    if( $bufarray[0] == NULL )
    {
      $bot->talk( $channel, "Please specify a valid target." );
      return;
    }
    
    if( $command == "op" )
      $bot->cmd_send( "MODE $channel +o ".$bufarray[0] );
    else
      $bot->cmd_send( "MODE $channel -o ".$bufarray[0] );
  }
  
  function callvote( $bufarray, $channel, $name )
  {
    global $con, $bot;
    
    $type = $bufarray['0'];
    unset( $bufarray['0'] );
    $string = implode( " ", $bufarray );
    if( stripos( $channel, "#" ) === FALSE )
    {
      $bot->talk( $channel, "Sorry, but please only use callvote in a channel." );
      return;
    } 
    $bot->call_vote( $string, $type, $name, $channel );
  }
  
  function part( $bufarray, $name, $channel )
  {
    global $bot, $con;
    
    $channeltopart = $bufarray[0];
    for( $i=0; $i<count( $con['data'][channels] ); $i++ )
    {
      if( $con['data'][channels][$i]['name'] == $channeltopart )
      {
        $present = TRUE;
        $id = $i;
        break;
      }
      else
        $present = FALSE;
    }
    
    if( $present == FALSE )
    {
      $bot->talk( $channel, "I find it rather hard to part channels I'm not present in, $name." );
      return;
    }
    if( $bufarray[1] != NULL )
    {
      unset( $bufarray[0] );
      $pmessage = implode( " ", $bufarray );                
    }
    
    $bot->cmd_send( "PART $channeltopart :$pmessage" );
    if( $channel != $channeltopart )
      $bot->talk( $channel, $name.": I have parted ".$bufarray[0].", and will no longer join it at startup." );
    
    $con['data'][channels][$id]['active'] = FALSE;
    $bot->writedata( $con['data'] );
  }
  
  function msg( $bufarray, $channel )
  {
    global $con, $bot;
    
    $target = $bufarray['0'];
    unset( $bufarray['0'] );
    $text = implode( " ", $bufarray );
    $bot->talk( $target, $text );
    $bot->talk( $channel, "Message sent to $target" );
  }
  
  function finishvote( $command, $channel )
  {
    global $con, $bot;
    
    if( $con['vote']['inprogress'] == FALSE )
    {
      $bot->talk( $channel, "There is no vote currently in progress." );
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
  
  function status( $channel )
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
    
    $bot->talk( $channel, "I've been up for $uptime, sent $cmdstoserv command(s) to the server, processed $lines line(s) of text, talked $talked time(s), ran $cmds command(s), snarfed $snarfs url(s), and contacted a tremulous server $servers time(s)." );
    return;
  }
  
  function weather( $channel, $bufarray )
  {
    global $bot, $con;
    
    $lugar = implode( " ", $bufarray );
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
      $bot->talk( $channel, "Weather report for: $place" );
      $bot->talk( $channel, "Temp: $temp | Humidity: $humidity | Clouds: $clouds | Pressure: $pressure | Wind: $wind " );
      if( $advisories != NULL )
        $bot->talk( $channel, "Advisories: $advisories" );
    }
    else
      $bot->talk( $channel, "That zip code/location was not found or was ambiguous." );
  }
  
  function invite( $channel, $bufarray )
  {
    global $con, $bot;
    
    if( $bufarray[0] == NULL )
    {
      $bot->talk( $channel, "Please specify a valid target for this command." );
      return;
    }

    $bot->cmd_send( "INVITE ".$bufarray[0]." ".$channel );
    $bot->talk( $channel, "Invited" );
  }
  
  function autoop( $channel, $chanid )
  {
    global $con, $bot;

    if( $chanid == -1 )
    {
      $bot->talk( $channel, "You can't change settings like those in a channel I was invited to." );
      return;     //Likely a PM or some weird bug
    }

    if( $channel != $con['data'][channels][$chanid]['name'] )
    {
      $bot->talk( $channel, "You can't turn autoop/autovoice off in a PM." );
      return;     //Likely a PM or some weird bug
    }

    if( $con['data'][channels][$chanid]['autoopvoice'] != TRUE )
    {
      $con['data'][channels][$chanid]['autoopvoice'] = TRUE; 
      $bot->talk( $channel, "Auto Op and voice has been enabled in this channel." );
    }
    else
    {
      $con['data'][channels][$chanid]['autoopvoice'] = FALSE; 
      $bot->talk( $channel, "Auto Op and voice has been disabled in this channel." );
    }
    
    $bot->writedata( $con['data'] );
  }
  
  function ccmds( $channel, $chanid )
  {
    global $bot, $con;
    
    if( $chanid == -1 )
    {
      $bot->talk( $channel, "You can't change settings like those in a channel I was invited to." );
      return;     //Likely a PM or some weird bug
    }
    
    if( $channel != $con['data'][channels][$chanid]['name'] )
    {
      $bot->talk( $channel, "You can't turn commands off in a PM." );
      return;     //Likely a PM or some weird bug
    }
    
    if( $con['data'][channels][$chanid]['cmds'] != TRUE )
    {
      $con['data'][channels][$chanid]['cmds'] = TRUE; 
      $bot->talk( $channel, "Commands have been enabled in this channel." );
    }
    else
    {
      $con['data'][channels][$chanid]['cmds'] = FALSE; 
      $bot->talk( $channel, "Commands have been disabled in this channel." );
    }
    
    $bot->writedata( $con['data'] );
  }
  
  function kick( $channel, $bufarray, $name )
  {
    global $bot;
        
    $target = $bufarray[0];
    
    if( !$target )
    {
      $bot->talk( $channel, "$name: Please specify a target for your kick." );
      return;
    }
    
    unset( $bufarray[0] );
    $message = implode( " ", $bufarray );
    
    if( !$message )
      $message = "Kicked by $name";
    else
      $message = $name.": ".$message;
    
    $bot->cmd_send( "KICK $channel $target :$message" );
  }
  
  function topic( $channel, $bufarray )
  {
    global $bot;
    
    $topic = implode( " ", $bufarray );

    if( !$topic )
      $topic = " ";
    
    $bot->cmd_send( "TOPIC $channel :$topic" );
  }
  
  function listchannels( $channel )
  {
    global $bot, $con;
    
    if( count( $con['data'][channels] ) > 2 )
    {
      $bot->talk( $channel, "I am currently automatically joining the following channels:" );
      for( $i=0; $i<count( $con['data'][channels] ); $i++ )
      {
        if( $i == 0 )
          $chans = $con['data'][channels][$i]['name']." , ";
        else if( $i == count( $con['data'][channels] )-1 )
          $chans .= $con['data'][channels][$i]['name'].".";
        else
          $chans .= $con['data'][channels][$i]['name']." , ";
      }
      $bot->talk( $channel, $chans );
    }
    else if( count( $con['data'][channels] ) == 2 )
    {
      $bot->talk( $channel, "I am currently automatically joining the following channels:" );
      for( $i=0; $i<count( $con['data'][channels] ); $i++ )
      {
        if( $i == 0 )
          $chans = $con['data'][channels][$i]['name']." and ";
        else
          $chans .= $con['data'][channels][$i]['name'].".";
      }
      $bot->talk( $chanel, $chans );
    }
    else if( count( $con['data'][channels] ) == 1 )
      $bot->talk( $channel, "I am currently only automatically joining ".$con['data'][channels][0]['name']."." );
    else
      $bot->talk( $channel, "I do not appear to be automatically joining any channels." );
  }
  
  function addadmin( $channel, $bufarray )
  {
    global $con, $bot;
    
    $hostmask = $bufarray[0];
    //begin overcomplicated hostmask verification
    $hostmaskc = explode( "@", $hostmask );
    if( count( $hostmaskc ) <= 1 )
      $error = TRUE;
    
    if( $error )
    {
      $bot->talk( $channel, "That doesn't appear to be a valid hostmask (ie uselikethis@hostmask.com)" );
      return;
    }
      
    for( $i=0; $i<count($con['data'][admins]); $i++ )
    {
      if( $hostmask == $con['data'][admins][$i] )
      {
        $bot->talk( $channel, "That hostmask is already an admin." );
        return;
      }
    }
    
    $con['data'][admins][] = $hostmask;
    $bot->writedata( $con['data'] );
    $bot->talk( $channel, $hostmaskcf[0]." successfully added." );
  }
  
  function override_password( $channel, $hostmask, $bufarray )
  {
    global $CONFIG, $con, $bot;

    for( $i=0; $i<count($con['data'][admins]); $i++ )
    {
      if( $hostmask == $con['data'][admins][$i] )
      {
        $bot->talk( $channel, "You are already an admin." );
        return;
      }
    }

    if( $CONFIG[admin_pass] == NULL )
      $bot->talk( $channel, "An admin password is not set." );
    else if( $bufarray[0] == $CONFIG[admin_pass] )
    {
      $con['data'][admins][] = $hostmask;
      $bot->writedata( $con['data'] );
      $bot->talk( $channel, "You are now an Administrator." );
    }
    else
      $bot->talk( $channel, "Permission Denied" );    //meant to be ambiguous
  }
  
  function snarf_toggle( $channel, $chanid )
  {
    global $con, $bot;
    
    if( $chanid == -1 )
      $bot->talk( $channel, "This channel is not in my data file." );
    else if( $channel != $con['data'][channels][$chanid]['name'] )
      $bot->talk( $channel, "You can't turn commands off in a PM." );
    else if( $con['data'][channels][$chanid]['snarf'] == FALSE )
    {
      $con['data'][channels][$chanid]['snarf'] = TRUE;
      $bot->talk( $channel, "Snarfing is now enabled for this channel" );
      $bot->writedata( $con['data'] );
    }
    else if( $con['data'][channels][$chanid]['snarf'] == TRUE )
    {
      $con['data'][channels][$chanid]['snarf'] = FALSE;
      $bot->talk( $channel, "Snarfing is now disabled for this channel" );
      $bot->writedata( $con['data'] );
    }
  }
  
  function list_admins( $channel, $textarray )
  {
    global $bot, $con;
    
    $start = 0;
    $admins = $con['data'][admins];
    
    if( $textarray )
    {
      //possible integer offset or name
      if( is_int( $textarray[0] ) )
      {
        if( $textarray[0] >= count( $admins ) || $textarray[0] < 0 )
        {
          $bot->talk( $channel, "That is not a valid offset." );
          return;
        }
        $start = $textarray[0];
      }
      else if( is_string( $textarray[0] ) )
      {
        for( $i=0; $i<count( $admins ); $i++ )
        {
          if( stripos( $admins[$i], $textarray[0] ) !== FALSE )
          {
            $k = count( $found );
            $found[$k]['n'] = $admins[$i];
            $found[$k]['#'] = $i;
          }
        }
        if( !$found )
        {
          $bot->talk( $channel, "That username was not found." );
          return;
        }
        else
        {
          if( count( $found ) > 5 )
          {
            $bot->talk( $channel, "More than 5 matches were found, please be more specific." );
            return;
          }
          else
          {
            if( count( $found ) > 1 )
              $bot->talk( $channel, "The following users (partially) matched your search:" );
            else
              $bot->talk( $channel, "The following user (partially) matched your search:" );
            for( $i=0; $i<count( $found ); $i++ )
              $bot->talk( $channel, $found[$i]['#']." ".$found[$i]['n'] );
            return;
          }
          
        }
      }
    }
    $k = 0;
    for( $i=$start;$i<$start+5; $i++ )
    {
      if( $admins[$i] )
      {
        $k++;
        $bot->talk( $i." ".$admins[$i] );
      }
    }
    $bot->talk( $channel, "Displaying admins #$start-#".($k+$start) );
  }
}
?>
