<?php

  ini_set(error_reporting, 0);           // geen syntax error meldingen i.v.m. veiligheid
  set_time_limit(360);                                                      // 360 sec = 1 minuut
  ini_set("memory_limit", "256M");                                          // default is het maar 8M of 16M

  // set the default timezone to use. Available since PHP 5.1
  date_default_timezone_set('UTC');

  
  //include ("./berichten_verwerken.php");

  //$FILE_IN = "/usr/people/stam/IJSGROEI/input/20121113/ys280_12_00";


  
  /*************************************************************************************************************/
  /*                                                                                                           */
  /*                                                                                                           */
  /*                                                                                                           */
  /*************************************************************************************************************/
  function lees_ysfile($DEBUG, $file_debug, $analyse_jaar, $analyse_maand, $analyse_dag, $analyse_uur,
                       &$record_wind_12, &$record_luchttemp_12, &$record_dauwpunt_12, &$record_cloud_cover_12, &$record_neerslag_12,
                       &$forecast_uur_12_uur_vooruit, &$forecast_dag_12_uur_vooruit, &$forecast_maand_12_uur_vooruit,
                       &$record_analyse_ijsdikte,
                       $geselecteerde_ysfile)
  {
  	  //$file_in = "/data/apl/wsdata/IJSGROEI/" . "ys260_". substr($analyse_jaar, 2, 2) . "_00"; // nb jaar in 2 char (bv 12)
  	  //$file_in = "./test_data/" . "ys280_". "11" . "_00"; // nb jaar in 2 char (bv 12)
     $file_in = $geselecteerde_ysfile;
     
     if ($DEBUG != 0)
     {
        fwrite($file_debug, "----> ysfile: geselecteerde ysfile = " . $geselecteerde_ysfile . "<br>");
        fwrite($file_debug, "<br>");
     }
  	  
  	  // initialisatie ???????????????
  	  //$record_luchttemp_12 = 9999;
  	  
  	  
  	  
  	  //$forecast_date = mktime(0, 0, 0, 1, 1 + $i, $JAAR);                       // beginnen op 01-01-jaar (mktime format: uur-minuut-sec-maand-dag-jaar-dst)
     //$file_input = $INVOER_PATH . "FO" . date("ymd", $forecast_date) . ".DAT"; // bv "FO071017.DAT";
/* 	
     // systeem tijd
     $day_1_time_stamp  = mktime(0, 0, 0, date("m"), date("d") +1, date("Y"));           // morgen
     $day_1_maand = date("m", $day_1_time_stamp);        // 01 through 12
     $day_1_dag   = date("d", $day_1_time_stamp);        // 01 to 31
     //$day_1_uur   = date("H", $day_1_time_stamp);        // 00 through 23
*/
     if ($analyse_uur == 12)
     {
        $forecast_uur_12_uur_vooruit   = 24;
        $forecast_dag_12_uur_vooruit   = $analyse_dag;
        $forecast_maand_12_uur_vooruit = $analyse_maand; 	
     }
     else if ($analyse_uur == 24)
     {
        $forecast_uur_12_uur_vooruit = 12;
        
        //$hulp_time_stamp  = mktime(0, 0, 0, date("m"), date("d") +1, date("Y"));           // morgen
        $hulp_time_stamp  = mktime(0, 0, 0, $analyse_maand, $analyse_dag +1, $analyse_jaar);
        $hulp_maand       = date("m", $hulp_time_stamp);        // 01 through 12
        $hulp_dag         = date("d", $hulp_time_stamp);        // 01 to 31
        
        $forecast_dag_12_uur_vooruit   = $hulp_dag;
        $forecast_maand_12_uur_vooruit = $hulp_maand;
        
     } // else if ($analyse_uur == 24)

  	   
     // initialisatie
     $analyse_record_gevonden = 0;
     
     $file = fopen($file_in, "rb");
     if ($file != null)
     {
        while(!feof($file))
        {
           $record = fgets($file);
           if (strlen($record) > 10)             // 10 is willekeurig maar groter dan 1 (zitten een paar loze regels aan het eind)
           {
           	  //$forecast_indicatie = substr($record, 0, 1);    // 9=analyse; 4=forecast
              $record_maand        = substr($record, 1, 2);
              $record_dag          = substr($record, 3, 2);
              $record_uur          = substr($record, 5, 2);
      	     
      	     if ( (intval($record_maand) == intval($analyse_maand)) &&
      	          (intval($record_dag) == intval($analyse_dag)) &&
      	          (intval($record_uur) == intval($analyse_uur)) )
      	     {
                 $record_analyse_ijsdikte     = substr($record, 29, 4);
                 $record_analyse_sneeuwhoogte = substr($record, 33, 4);
      	     	
                 $analyse_record_gevonden = 1;
                 
      	        if ($DEBUG != 0)
     	           {
                    fwrite($file_debug, "----> ysfile: analyse_maand = " . $analyse_maand . "<br>");
     	              fwrite($file_debug, "----> ysfile: analyse_dag = " . $analyse_dag . "<br>");
     	              fwrite($file_debug, "----> ysfile: analyse_uur = " . $analyse_uur . "<br>");
     	           	  fwrite($file_debug, "----> ysfile: record_analyse_ijsdikte = " . $record_analyse_ijsdikte . "<br>");
     	           	  fwrite($file_debug, "----> ysfile: record_analyse_sneeuwhoogte = " . $record_analyse_sneeuwhoogte . "<br>");
                    fwrite($file_debug, "<br>");        
     	           } // if ($DEBUG != 0)
                 
              } // if ( ($record_maand == $analyse_maand) etc.
              
      	     if ( (intval($record_maand) == intval($forecast_maand_12_uur_vooruit)) &&
      	          (intval($record_dag) == intval($forecast_dag_12_uur_vooruit)) &&
      	          (intval($record_uur) == intval($forecast_uur_12_uur_vooruit)) )
      	     {
      	        $record_wind_12        = substr($record, 7, 2);
      	     	  $record_luchttemp_12   = substr($record, 9, 4); 
      	        $record_dauwpunt_12    = substr($record, 13, 4);
      	        $record_cloud_cover_12 = substr($record, 17, 2);
      	        $record_neerslag_12    = substr($record, 21, 4);

      	        if ($DEBUG != 0)
     	           {
                    fwrite($file_debug, "----> ysfile: forecast_maand_12_uur_vooruit = " . $forecast_maand_12_uur_vooruit . "<br>");
     	              fwrite($file_debug, "----> ysfile: forecast_dag_12_uur_vooruit = " . $forecast_dag_12_uur_vooruit . "<br>");
     	              fwrite($file_debug, "----> ysfile: forecast_uur_12_uur_vooruit = " . $forecast_uur_12_uur_vooruit . "<br>");
     	              fwrite($file_debug, "----> ysfile: wind [m/s] 12 uur vooruit = " . $record_wind_12 . "<br>");
     	              fwrite($file_debug, "----> ysfile: lucht_temperatuur [0.1C] 12 uur vooruit = " . $record_luchttemp_12 . "<br>");
     	              fwrite($file_debug, "----> ysfile: dauwpunt [0.1C] 12 uur vooruit = " . $record_dauwpunt_12 . "<br>");
     	              fwrite($file_debug, "----> ysfile: cloud-cover [code] 12 uur vooruit = " . $record_cloud_cover_12 . "<br>");
     	              fwrite($file_debug, "----> ysfile: lucht_neerslag [mm] 12 uur vooruit = " . $record_neerslag_12 . "<br>");
     	              fwrite($file_debug, "<br>");        
     	           } // if ($DEBUG != 0)
      	        
      	     } // if ( ($record_maand == $forecast_maand_12_uur_vooruit) &&
        
              //print "totaal_aantal_records =" . $totaal_aantal_records . "         " . $line . "\n". "\n" . "<br>";
           } // if (strlen($line) > 10)  
	     } // while(!feof($file))

	     fclose($file);
     } // if ($file != null)
     else 
     {
     	  if ($DEBUG != 0)
     	  {
           fwrite($file_debug, "+++-> (ys)inlees file (" . $file_in . ") niet te openen<br>");
           fwrite($file_debug, "<br>");        
     	  }
     } // else  
     
     if ($analyse_record_gevonden == 0)
     {
     	  fwrite($file_debug, "+++-> geen analyse record gevonden<br>");
        fwrite($file_debug, "<br>");
     }
  	
  }
  


  
  /*************************************************************************************************************/
  /*                                                                                                           */
  /*                                                                                                           */
  /*                                                                                                           */
  /*************************************************************************************************************/
  function lees_ysfile_2($DEBUG, $file_debug, $analyse_jaar, $analyse_maand, $analyse_dag, $analyse_uur,
                       &$record_wind_12, &$record_luchttemp_12, &$record_dauwpunt_12, &$record_cloud_cover_12, &$record_neerslag_12,
                       &$forecast_uur_12_uur_vooruit, &$forecast_dag_12_uur_vooruit, &$forecast_maand_12_uur_vooruit,
                       &$record_analyse_ijsdikte,
                       $geselecteerde_ysfile)
  {
  	  //$file_in = "/data/apl/wsdata/IJSGROEI/" . "ys260_". substr($analyse_jaar, 2, 2) . "_00"; // nb jaar in 2 char (bv 12)
  	  //$file_in = "./test_data/" . "ys280_". "11" . "_00"; // nb jaar in 2 char (bv 12)
     $file_in = $geselecteerde_ysfile;
     
     if ($DEBUG != 0)
     {
        fwrite($file_debug, "----> ysfile: geselecteerde ysfile = " . $geselecteerde_ysfile . "<br>");
        fwrite($file_debug, "<br>");
     }
  	  
  	  // initialisatie ???????????????
  	  //$record_luchttemp_12 = 9999;
  	  
  	  
  	  
  	  //$forecast_date = mktime(0, 0, 0, 1, 1 + $i, $JAAR);                       // beginnen op 01-01-jaar (mktime format: uur-minuut-sec-maand-dag-jaar-dst)
     //$file_input = $INVOER_PATH . "FO" . date("ymd", $forecast_date) . ".DAT"; // bv "FO071017.DAT";
/* 	
     // systeem tijd
     $day_1_time_stamp  = mktime(0, 0, 0, date("m"), date("d") +1, date("Y"));           // morgen
     $day_1_maand = date("m", $day_1_time_stamp);        // 01 through 12
     $day_1_dag   = date("d", $day_1_time_stamp);        // 01 to 31
     //$day_1_uur   = date("H", $day_1_time_stamp);        // 00 through 23
*/

     $gevraagd_uur   = $analyse_uur;
     $gevraagd_dag   = $analyse_dag;
     $gevraagd_maand = $analyse_maand;
     $gevraagd_jaar  = $analyse_jaar;
     
     // initialisatie
     $analyse_record_gevonden = 0;
     $teller = 0;
     
     $file = fopen($file_in, "rb");
     if ($file != null)
     {
        while(!feof($file))
        {
           $record = fgets($file);
           // NB EERSTE RECORD OVERSLAAN 
           
           // NB NOG CONTROLEREN OP JAAR OVERGANG
           if ( (strlen($record) > 10) && ((substr($record, 0, 1) == '9') || (substr($record, 0, 1) == '4')) )  // 10 is willekeurig maar groter dan 1 (zitten een paar loze regels aan het eind)
           {
           	  //$forecast_indicatie = substr($record, 0, 1);    // 9=analyse; 4=forecast
              $record_maand        = substr($record, 1, 2);
              $record_dag          = substr($record, 3, 2);
              $record_uur          = substr($record, 5, 2);
              
      	     if ( (intval($record_maand) == intval($gevraagd_maand)) &&
      	          (intval($record_dag) == intval($gevraagd_dag)) &&
      	          (intval($record_uur) == intval($gevraagd_uur)) )
      	     {
   	           $record_wind         = substr($record, 7, 2);
      	     	  $record_luchttemp    = substr($record, 9, 4); 
      	        $record_dauwpunt     = substr($record, 13, 4);
      	        $record_cloud_cover  = substr($record, 17, 2);
      	        $record_neerslag     = substr($record, 21, 4);
      	        $record_watertemp    = substr($record, 25, 4);
                 $record_ijsdikte     = substr($record, 29, 4);
                 $record_sneeuwhoogte = substr($record, 33, 4);
                 
                 $analyse_record_gevonden = 1;
                 
                 
                 $record_array[0][$teller]  = $gevraagd_jaar; // ????????????????????????
                 $record_array[1][$teller]  = $record_maand;
                 $record_array[2][$teller]  = $record_dag;
                 $record_array[3][$teller]  = $record_uur;
                 
                 $record_array[4][$teller]  = $record_wind;
                 $record_array[5][$teller]  = $record_luchttemp;
                 $record_array[6][$teller]  = $record_dauwpunt;
                 $record_array[7][$teller]  = $record_cloud_cover;
                 $record_array[8][$teller]  = $record_neerslag;
                 $record_array[9][$teller]  = $record_watertemp;
                 $record_array[10][$teller] = $record_ijsdikte;
                 $record_array[11][$teller] = $record_sneeuwhoogte;
                 
                 
                 
      	        if ($DEBUG != 0)
     	           {
     	           	  fwrite($file_debug, "----> ysfile: record teller = " . $teller . "<br>");
                    fwrite($file_debug, "----> ysfile: record_maand = " . $record_maand . "<br>");
     	              fwrite($file_debug, "----> ysfile: record_dag = " . $record_dag . "<br>");
     	              fwrite($file_debug, "----> ysfile: record_uur = " . $record_uur . "<br>");
     	           	  fwrite($file_debug, "----> ysfile: record_ijsdikte = " . $record_ijsdikte . "<br>");
     	           	  fwrite($file_debug, "----> ysfile: record_sneeuwhoogte = " . $record_sneeuwhoogte . "<br>");
     	              fwrite($file_debug, "----> ysfile: record_wind [m/s] = " . $record_wind . "<br>");
     	              fwrite($file_debug, "----> ysfile: record_lucht_temperatuur [0.1C] = " . $record_luchttemp . "<br>");
     	              fwrite($file_debug, "----> ysfile: record_dauwpunt [0.1C] = " . $record_dauwpunt . "<br>");
     	              fwrite($file_debug, "----> ysfile: record-cloud-cover [code] = " . $record_cloud_cover . "<br>");
     	              fwrite($file_debug, "----> ysfile: record_lucht_neerslag [mm] = " . $record_neerslag . "<br>");
     	           	  fwrite($file_debug, "<br>");        
     	           } // if ($DEBUG != 0)
     	           
     	           $teller++;
     	           

     	           //
     	           //// 12 uur verder
     	           //
     	           if ($gevraagd_uur == 12)
     	           {
     	              $gevraagd_uur   = 24;
     	           	  $gevraagd_dag   = $gevraagd_dag;
     	           	  $gevraagd_maand = $gevraagd_maand;
     	           }
     	           else if ($gevraagd_uur == 24)
     	           {
     	              $gevraagd_uur = 12;

     	           	  $hulp_time_stamp  = mktime(0, 0, 0, $gevraagd_maand, $gevraagd_dag +1, $gevraagd_jaar);
     	           	  $hulp_maand       = date("m", $hulp_time_stamp);        // 01 through 12
     	           	  $hulp_dag         = date("d", $hulp_time_stamp);        // 01 to 31

     	           	  $gevraagd_dag     = $hulp_dag;
     	           	  $gevraagd_maand   = $hulp_maand;

     	           } // else if ($gevraagd_uur == 24)
     	           
              } // if ( ($record_maand == $analyse_maand) etc.
              
         
              //print "totaal_aantal_records =" . $totaal_aantal_records . "         " . $line . "\n". "\n" . "<br>";
           } // if (strlen($line) > 10)  
           
	     } // while(!feof($file))

	     fclose($file);
     } // if ($file != null)
     else 
     {
     	  if ($DEBUG != 0)
     	  {
           fwrite($file_debug, "+++-> (ys)inlees file (" . $file_in . ") niet te openen<br>");
           fwrite($file_debug, "<br>");        
     	  }
     } // else  
     
     if ($analyse_record_gevonden == 0)
     {
     	  fwrite($file_debug, "+++-> geen analyse record gevonden<br>");
        fwrite($file_debug, "<br>");
     }
  	
  }



?>


