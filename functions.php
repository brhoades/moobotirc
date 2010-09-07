<?php
/*

Moobot is Copyright 2008-2010 Billy "Aaron5367" Rhoades

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

////////////////////////////
//ESSENTIAL FUNCTIONS     //
////////////////////////////

// returns a new con array
function conSetup( )
{
  //  Basic Structure //
  //loaded from moobot.conf (if available)
  $con['config'] = initConfig( );
  
  //everything in here gets written and read to/from data
  $con['data'] = initData( $con );
  
  //everything in here gets erased on a rehash/disconnect/shutdown
  $con['session'] = initSess( );
  
  //full command tree from commands.php
  $con['commands'] = initCmdTree( );
  
  //triggers for various stuff
  $con['triggers'] = initTriggers( );

  //socket setup
  $con['socket'] = initCon( $con );

  return $con;
}

//Checks for moobot.conf/moobot.conf.example and loads it into
//$con['config']
function initConfig( )
{
  if( include( "moobot.conf" ) != 'OK' )
  {
    if( include( "moobot.conf.example" ) != 'OK' )
      die( "Error, you need either a moobot.conf or the example file" );
  }
  
  return $CONFIG;
}

//Initilize our $con['data'] variable by reading our data file
//if it doesn't exist, make one
function initData( $con )
{
  //Try to read our data
  $data = readData( $con );
  
  if( $data == NULL )
  {
    
    
  }
}

////////////////////////
// Data functions     //
////////////////////////  

function readData( $localcon = NULL )
{
  global $con;
  
  if( $con == NULL && $localcon != NULL )
    $config = $localcon['config'];
  else if( $con != NULL )
    $config = $con['config'];
  else
    die( "Cannot find config." );

  $fh = fopen( $config['datapath'].$config['datafn'], "r" );
  $newdata = fread( $fh, filesize( $CONFIG[datafilelocation] ) );
  fclose( $fh );
  
  if( !is_array( unserialize( $newdata ) ) && $newdata != NULL )
  {
    echo "Error! Old datafile is no longer an array, please remove it!\n";
    if( $con != NULL )
      return $con['data'] ;
    else
      return NULL;
  }
  
  if( $con == NULL )
    return $newdata;
  else if( $newdata == NULL )
    return $con['data'];
  
  $newdata = unserialize( $newdata );
  
  //compare times of both, times are added when written
  if( $datavar['time'] != NULL && $newdata['time'] != NULL )
  {
    if( $newdata['time'] <= $datavar['time'] )
      echo "Our data is newer?\n";
    else
      $con['data'] = $newdata;
  }
  else 
    $con['data'] = $newdata;

  $con['data'] = $datavar;
  return( $datavar );
}

function writeData( $datavar )
{
  global $con;
  
  if( $con == NULL )
  {
    echo "Cannot write without config and whatnot.\n";
    return;
  }
  
  $config = $con['config'];

  $fh = fopen( $config['datapath'].$config['datafn'], "r" );
  $olddata = fread( $fh, filesize( $config['datapath'].$config['datafn'] ) );
  fclose( $fh );
  
  if( $olddata == serialize( $datavar ) )
    return;
    
  $time = time();
  $datavar['time'] = $time;
  $fh = fopen( $config['datapath'].$config['datafn'], "w" );
  fwrite( $fh, serialize( $datavar ) );
  fclose( $fh );
  $con['data'] = $datavar;
}

////////////////////////////
//  End of data variables //
////////////////////////////

//Initalizes the $con['socket'] global with a connection
function initCon( $con )
{
  $socket = fsockopen( "irc.unvanquished.net", 6667, $errno, $errstr, 1 );
  
  //depricated unknown stuff
  stream_set_blocking( $socket, 0 );
  stream_set_timeout( $socket, 100 );
  
  //let things catch up...
  usleep( 100000 );
  
  //send our user and nick strings, we do this longhand as there is 
  //no $con global yet
  fputs( $socket, "USER ". "Moobot0.6" ." "."knightsofreason.net"." "."knightsofreason.net"." :". "moobot" );
  fputs( $socket, "NICK ". "Moobot0.6" ." "."knightsofreason.net" );
  
  return $socket;
}

?>
