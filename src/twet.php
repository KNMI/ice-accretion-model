<?php

  echo "dit is een test <br>";
  
  //floats $zt, $zt2;
  
  
  // invoer (hier vaste test waarden)
  $ptt = 5.7;   // temperature
  $pee = 8.405631;        // vapour pressure
  
  
  $zes2 = 6.107 * pow(10, 7.6 * $ptt / ($ptt + 242.0));
  $zt2 = $ptt - (1 - $pee / $zes2) / 0.19;
  
  do 
  {
     $zt = $zt2;
     if ($zt < 0.0) 
     {
        $zes = 6.107 * pow(10, 9.5 * $zt / ($zt + 266.0));
        $zt2 = $zt - ($zes - $pee + 0.57 * ($zt - $ptt)) / (6154. * $zes2 / pow($zt + 273., 2.) + 0.57);
     }
     else
     {
        $zes = 6.107 * pow(10, 7.6 * $zt / ($zt + 242.0));
        $zt2 = $zt - ($zes - $pee + 0.66 * ($zt - $ptt)) / (5419. * $zes2 / pow($zt + 273., 2.) + 0.66);
     }
  } while (abs($zt - $zt2) > 0.05);
  
  
  $twet = $zt2;  
  
  echo "temp = " . $ptt . "<br>";
  echo "dampspanning = " . $pee . "<br>";
  echo "twet = " . $twet;
  
?>