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

//Basic includes
require( "functions.php" );

//main loop
while( $alive )
  main();
  
function main( )
{
  global $con;
  
  $con = conSetup( );
  
  while( !feof( $con['socket'] )
  {
    loadFrame( );
    runTriggers( );
    
    nextFrame( );
  }
}

?>
