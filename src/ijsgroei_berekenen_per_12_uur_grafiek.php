<?php

  set_time_limit(360);                                                      // 360 sec = 1 minuut
  ini_set("memory_limit", "256M");                                          // default is het maar 8M of 16M
  date_default_timezone_set('UTC');
  
  // LET OP ALLEEN WAT "TERUG" GAAT MAG MET EEN ECHO
  
  $DEBUG        = 1;                           // 0=geen debug info naar debug file0; 1=wel debug info naar file
  $UITVOER_FILE = 1;                           // 0=geen uitvoerfile;1=wel uitvoerfile (nb uitvoerfile is een copy vande invoerfile (maar dan de forecasts berekend) als het goed is is dit precies het zelfde als de forecasts in de invoer file

  $INTERFACE    = $_POST['mode'];              // public/private/grafiek;           
  
  
  if ($DEBUG != 0)
  {
  	  $path_file_debug = "debug_file_ijsberekening.html";
     $file_debug = fopen($path_file_debug, "wb");

     fwrite($file_debug, "<html>");
     fwrite($file_debug, "<body>");
     
     fwrite($file_debug, "----> mode = " . $INTERFACE . "<br>");
     fwrite($file_debug, "<br>");        
  }
  else 
  {
  	  $file_debug = "";
  }
   
  
  if ($INTERFACE == "public")
  {
     // for safety remove special tags (tags are never inserted by ijsgroei application but may be directly into the webbrowser URL by a malicious person/program)
     //             NB strip_tags() destroys the whole HTML behind the tags with invalid attributes 
    
     $mzi       = strip_tags($_POST['geselecteerde_ijsdikte']);
     $mzw       = strip_tags($_POST['geselecteerde_waterdiepte']);
     $mzs       = strip_tags($_POST['geselecteerde_sneeuwhoogte']);
     $mta       = strip_tags($_POST['geselecteerde_lucht_temperatuur']);  // in 0.1 C
     $bewolking = strip_tags($_POST['geselecteerde_bewolking']);          // in 1%
     //$mrh  = strip_tags($_POST['geselecteerde_dauw_punt_temperatuur']);
     $rv        = strip_tags($_POST['geselecteerde_rv']); // rv moet nog omgezet worden naar dauw punt want hier reknt programma verder mee
     $mff       = strip_tags($_POST['geselecteerde_wind_snelheid']);
     $mprec     =  0;  // ?????????????????????????????????/
     
     //$mmm    = 12;                       // maand v/h jaar                  // verwachtings datum
     //$mdd    = 29;                       // dag v/d maand                   // verwachtings datum
     //$mhh    = 12;                       // uur v/d dag     ALTIJD 12 OF 24 // verwachtings datum
     
     // maand, dag en uur bepalen
     $nu_time_stamp  = mktime(0, 0, 0, date("m"), date("d"), date("Y"));    // datum/tijd van dit moment v/h systeem
     $mmm = date("m", $nu_time_stamp);        // 01 through 12
     $mdd = date("d", $nu_time_stamp);        // 01 to 31
     $mhh = 24;                               // vanuitgaande je wilt het om 12 uur, 12 uur later weten = 24 uur
     
     // dauwpunt bepalen (invoer door gebruiker was rv, programma rekent verder met dauwpunttemp) 
     $mrh = Bereken_Dauwpunt($rv, $mta / 10., $DEBUG, $file_debug);
     
     // bewoking bedekking van % -> oktas
     $n = Omzetten_Bewolking_Procent_Naar_Oktas($bewolking, $DEBUG, $file_debug);
     
  } // if ($INTERFACE == "public")
  else if ($INTERFACE == "private")
  {
  	  $geselecteerde_jaar   = strip_tags($_POST['geselecteerde_jaar']);   // 
     $geselecteerde_maand  = strip_tags($_POST['geselecteerde_maand']);  // maand v/h jaar                  
     $geselecteerde_dag    = strip_tags($_POST['geselecteerde_dag']);    // dag v/d maand                   
     $geselecteerde_uur    = strip_tags($_POST['geselecteerde_uur']);    // uur v/d dag     ALTIJD 12 OF 24 // verwachtings datum
  	  $geselecteerde_ysfile = strip_tags($_POST['geselecteerde_ysfile']);
     
     // ONDERSTAANDE NOG NAKIJKEN
     $record_wind_12                = 0;
     $record_luchttemp_12           = 9999;
     $record_dauwpunt_12            = 0;
     $record_cloud_cover_12         = 0;
     $record_neerslag_12            = 0;
     $forecast_uur_12_uur_vooruit   = "";
     $forecast_dag_12_uur_vooruit   = "";
     $forecast_maand_12_uur_vooruit = "";
     $record_analyse_ijsdikte       = 0;
     
     lees_ysfile_2($DEBUG, $file_debug, $geselecteerde_jaar, $geselecteerde_maand, $geselecteerde_dag, $geselecteerde_uur,
                 $record_wind_12, $record_luchttemp_12, $record_dauwpunt_12, $record_cloud_cover_12, $record_neerslag_12,
                 $forecast_uur_12_uur_vooruit, $forecast_dag_12_uur_vooruit, $forecast_maand_12_uur_vooruit,
                 $record_analyse_ijsdikte,
                 $geselecteerde_ysfile);
                 
     $mmm    = $forecast_maand_12_uur_vooruit;                     // maand v/h jaar                  // verwachtings datum
     $mdd    = $forecast_dag_12_uur_vooruit;                       // dag v/d maand                   // verwachtings datum
     $mhh    = $forecast_uur_12_uur_vooruit;                       // uur v/d dag     ALTIJD 12 OF 24 // verwachtings datum
                 
     $mff   = $record_wind_12;               
     $mta   = $record_luchttemp_12;         // NB 9999 betekent geen lucht temp in file gevonden        
     $mrh   = $record_dauwpunt_12;
     $n     = $record_cloud_cover_12;
     $mprec = $record_neerslag_12;
     
     $mzi   = $record_analyse_ijsdikte;
     
  } // else if ($INTERFACE == 2)
  else if ($INTERFACE == "grafiek")            
  {
     $geselecteerde_locatie       = strip_tags($_POST['geselecteerde_locatie']);
     $geselecteerde_sneeuwhoogte  = strip_tags($_POST['geselecteerde_sneeuwhoogte']);
     $geselecteerde_ijsdikte      = strip_tags($_POST['geselecteerde_ijsdikte']);
     $geselecteerde_waterdiepte   = strip_tags($_POST['geselecteerde_waterdiepte']); 
     
     // systeem maand, dag en uur bepalen
     $nu_time_stamp  = mktime(0, 0, 0, date("m"), date("d"), date("Y"));    // datum/tijd van dit moment v/h systeem
     $mmm = date("m", $nu_time_stamp);        // 01 through 12
     $mdd = date("d", $nu_time_stamp);        // 01 to 31
     $mhh = 12; //??????????????????????       // vanuitgaande je wilt het om 12 uur, 12 uur later weten = 24 uur
      
  }
  else                                    // geen interface
  {
     $mmm    = 12;                       // maand v/h jaar                  // verwachtings datum
     $mdd    = 29;                       // dag v/d maand                   // verwachtings datum
     $mhh    = 12;                       // uur v/d dag     ALTIJD 12 OF 24 // verwachtings datum

  	
  	  $mzi    = 169.8398;                  // ijsdikte in mm (afgerond in hele mm?)
     $mta    = -154;                      // air temperature in 0.1 C
     $mzw    = 2;                         // waterdiepte in m
     $n      = 88;                        // cloud cover                     // in % [0 - 100] 
     $mff    = 3;                         // windsnelheid op 10 m in m/s  
     $mrh    = -176;                      // dauw punt temperatuur
     $mzs    = 20;                        // sneeuwdikte in mm
     $mprec  =  0;                        // neerslag
  }
  
  
  if ($DEBUG != 0)
  {
  	  if ($INTERFACE == "public")
  	  {
        fwrite($file_debug, "----> geselecteerde_ijsdikte [mm] = " . $mzi . "<br>");
        fwrite($file_debug, "----> geselecteerde_waterdiepte [m] = " . $mzw . "<br>");
        fwrite($file_debug, "----> geselecteerde_sneeuwhoogte [mm] = " . $mzs . "<br>");
        fwrite($file_debug, "----> geselecteerde_lucht_temperatuur [0.1C] = " . $mta . "<br>");
        fwrite($file_debug, "----> geselecteerde_bewolking [1%] = " . $bewolking . "<br>");
        //fwrite($file_debug, "----> geselecteerde_dauw_punt_temperatuur [0.1C] = " . $mrh . "<br>");
        fwrite($file_debug, "----> geselecteerde_rv [1%] = " . $rv . "<br>");
        fwrite($file_debug, "----> geselecteerde_wind_snelheid [m/s] = " . $mff . "<br>");
        fwrite($file_debug, "----> berekende dauwpunttemp = " . $mrh . "<br>");
        fwrite($file_debug, "----> systeem maand = " . $mmm . "<br>");
        fwrite($file_debug, "----> systeem dag = " . $mdd . "<br>");
        fwrite($file_debug, "----> uur = " . $mhh . "<br>");
        fwrite($file_debug, "<br>");        
  	  }
     else if ($INTERFACE == "private")	
     {
        fwrite($file_debug, "----> geselecteerde_jaar = " . $geselecteerde_jaar . "<br>");
        fwrite($file_debug, "----> geselecteerde_maand = " . $geselecteerde_maand . "<br>");
        fwrite($file_debug, "----> geselecteerde_dag = " . $geselecteerde_dag . "<br>");
        fwrite($file_debug, "----> geselecteerde_uur = " . $geselecteerde_uur . "<br>");
        fwrite($file_debug, "<br>");
     }
     else if ($INTERFACE == "grafiek")	
     {
        fwrite($file_debug, "----> geselecteerde_locatie = " . $geselecteerde_locatie . "<br>");
        fwrite($file_debug, "----> geselecteerde_sneeuwhoogte = " . $geselecteerde_sneeuwhoogte . "<br>");
        fwrite($file_debug, "----> geselecteerde_ijsdikte = " . $geselecteerde_ijsdikte . "<br>");
        fwrite($file_debug, "----> geselecteerde_waterdiepte = " . $geselecteerde_waterdiepte . "<br>");
        fwrite($file_debug, "<br>");
     }
     
  } // if ($DEBUG != 0)
 

  /*************************************************************************************************************/
  /*                                                                                                           */
  /*                                                                                                           */
  /*                                                                                                           */
  /*************************************************************************************************************/
 
  $ijsdata_forecast_string_run_0    = "";   // voor grafiek
  $ijsdata_forecast_string_run_1    = "";   // voor grafiek
  $eerste_forecast_record_ingelezen = 0;    // voor grafiek
 
  if ( ($geselecteerde_ijsdikte != "") && ($geselecteerde_ijsdikte != "blanco") && ($geselecteerde_sneeuwhoogte != "") && ($geselecteerde_sneeuwhoogte != "blanco") )
  {
  	  $aantal_runs = 2;             // 'officiele' model run en de persoonlijke ijsdikte run
  }
  else 
  {
  	  $aantal_runs = 1;             // alleen de 'officiele' model run
  }
  
  
  for ($run = 0; $run < $aantal_runs; $run++)
  {
  
  
  //     ------------------------------------------------------------------
  //     1. Declaraties.
  //     ------------------------------------------------------------------
  
  // constanten
  $pptf = 0.0;                        // freezing temperature constante
  $ppb  = 5.67E-8;                    // Stephan-Boltzmann constante
  $ppy  = 0.0174527;                  // radians/degrees constante 
  
  
  // (sneeuw)arrays
  for ($i = 0; $i < 15; $i++)
  {
  	  $snwat[$i]  = 0.0;
  	  $msnage[$i] = 0;
  }
  
  $mtown1 = 0;                        // stads effect
  $jclim  = 1;
  $mtmin  = 200;
  $mtmax  = 0;

  // variablen (voor invoer door de gebruiker)
  $mprint =  0;                       // 0=nodata, 1=data, 2=data+errors
  $lpr    =  0;                       // 0=nodata, 1=data, 2=data+errors
  $mtown  =  1;                       // stads effect
  $mremov =  0;                       // snow removal
  $mclear =  0;                       // wind effect
  $mlat   = 53;                       // latitude          // NB wordt indien ysfile uitgelezen wordt overschreven (staat in de kopregel)

  $mww    =  0;                       // ww code                         // GEBEURD NIETS MEE ??????????????????????
  ////$mprec  =  0;                       // precipitation                   in 0.1 mm ??????????
  $mtw    =  0;                       // water temperatuur in 0.1 C
  
  
  //$mzs    =  20;                      // sneeuw dikte in mm               WORDT LATER OVERSCHREVEN ???????????????

  //$mark   =  9;                       // analyse
  //$forecast_periode = 0;                  // voor grafiek
  
  // in het programma aangemaakte variablen
  // $tw                              // water temperatuur in 1 C
  // $qzi                             // ijsdikte in mm (niet afgerond???)
  // $mzs                             // sneeuwdikte in mm 
  // $mx                              // surface condition [0 = ice + snow; 1 = clear ice; 2 = wet ice; 3 = open water]
  // $snwat                           // sneeuwdikte in .....
  // $msnage                          // sneeuw leeftijd (snow age)
  // $ta                              // air temperature in 1 C
  // $e                               // vapour pressure 
  // $exch0
  // $mios
  // $rprec                           // precipitation in 1 mm   ????????????????
  // $tn                              // wet bulb temperature in ????????
  // $tn0                             // wet bulb temperature        
  // $snloss                          // sneeuw verlies (snow loss)
  // $mday                            // dag v/h jaar
  // $mslow = ??????????????
  // $jclim = ??????????????
  // $tclim = ????????????????/
  // $rdglob                          // daily solar flux


  
  
  //
  // -- pptf= freezing temperature, ppb= constant Stephan-Boltzmann,
  // -- ppy=radians/degrees
  //
  //     -----------------------------------------------------------------
  //     2. Initialisation.
  //     -----------------------------------------------------------------
  //$idebug = 1;
  
  
  //
  // files
  //
  $system_time_stamp = mktime(0, 0, 0, date("m"), date("d"), date("Y"));    // datum/tijd van dit moment v/h systeem
  $system_jaar  = date("Y", $system_time_stamp);       // Y = A full numeric representation of a year, 4 digits [Examples: 1999 or 2003]
  $system_maand = date("n", $system_time_stamp);       // n = Numeric representation of a month, without leading zeros [1 through 12]
  $system_dag   = date("j", $system_time_stamp);       // j = Day of the month without leading zeros [1 to 31]
  $system_uur   = date("G", $system_time_stamp);       // G = 24-hour format of an hour without leading zeros [0 through 23]
  
  if (($system_maand == 1) && ($system_dag == 1) && ($system_uur < 8))
  {
  	  $file_jaar_4_char = $system_jaar - 1;                  // bv 2013 - 1 = 2012
  	  $file_jaar_2_char = substr($file_jaar_4_char, 2, 2);   // bv 12 
  	  $file_jaar_analyse = $system_jaar - 1;                 // $file_jaar_analyse wordt gebruikt bij de ouput bij toekenning jaar aan analyse en forecast records
  }
  if (($system_maand == 1) && (($system_dag == 1 && $system_uur >= 8) || 
                               ($system_dag == 2 && $system_uur < 8)) )
  {
     // dit allemaal i.v.m. de rare 24 uurs aanduiding in de ys-files (24 uur i.p.v. 00 uur)
  	  $file_jaar_4_char = $system_jaar;                      // bv 2013 - 1 = 2012
  	  $file_jaar_2_char = substr($file_jaar_4_char, 2, 2);   // bv 12 
  	  $file_jaar_analyse = $system_jaar - 1;                 // $file_jaar_analyse wordt gebruikt bij de ouput bij toekenning jaar aan analyse en forecast records
  }
  else 
  {
  	  $file_jaar_4_char = $system_jaar;                      // bv 2013
  	  $file_jaar_2_char = substr($file_jaar_4_char, 2, 2);   // bv 13 
  	  $file_jaar_analyse = $system_jaar;                     // $file_jaar_analyse wordt gebruikt bij de ouput bij toekenning jaar aan analyse en forecast records
  }
  

  if ($geselecteerde_locatie == "Groningen")
  {
     $ysfile_station_nummer = "280";	                     // Eelde
  }
  else if ($geselecteerde_locatie == "Friesland")
  {
     $ysfile_station_nummer = "270";                        // Leeuwarden	              
  }
  else if ($geselecteerde_locatie == "Drenthe")
  {
     $ysfile_station_nummer = "280";	                     // Eelde
  }
  else if ($geselecteerde_locatie == "Overijssel")
  {
     $ysfile_station_nummer = "290";	                     // twenthe
  }
  else if ($geselecteerde_locatie == "Flevoland")
  {
     $ysfile_station_nummer = "240";	                     // Schiphol
  }
  else if ($geselecteerde_locatie == "Gelderland")
  {
     $ysfile_station_nummer = "290";	                     // Twenthe
  }
  else if ($geselecteerde_locatie == "Utrecht")
  {
     $ysfile_station_nummer = "260";	                     // De Bilt
  }
  else if ($geselecteerde_locatie == "Noord-Holland")
  {
     $ysfile_station_nummer = "240";	                     // Schiphol
  }
  else if ($geselecteerde_locatie == "Zuid-Holland")
  {
     $ysfile_station_nummer = "344";	                     // Zestienhoven
  }
  else if ($geselecteerde_locatie == "Zeeland")
  {
     $ysfile_station_nummer = "344";	                     // Zestienhoven
  }
  else if ($geselecteerde_locatie == "Noord-Brabant")
  {
     $ysfile_station_nummer = "370";	                     // Eindhoven
  }
  else if ($geselecteerde_locatie == "Limburg")
  {
     $ysfile_station_nummer = "380";	                     // Beek
  }
  else // default
  {
  	  $ysfile_station_nummer = "270";                        // Leeuwarden
  }
 

 
  
  
  // voor TEST: $path_file_in  = "./data/" . "ys270_". "10" . "_00.txt"; // nb jaar in 2 char (bv 10)
  //$path_file_in  = "./data/" . "ys270_". $file_jaar_2_char . "_00"; // nb jaar in 2 char (bv 10)
  $path_file_in  = "./data/" . "ys" . $ysfile_station_nummer . "_". $file_jaar_2_char . "_00"; // nb jaar in 2 char (bv 10)
 

  
  // uitvoer file naam samenstellen
  //if ($UITVOER_FILE == 1 && $run == 0)
  if ($UITVOER_FILE == 1)
  {
     // voor TEST: $path_file_uit = "./data/" . "ys280_". "10" . "_00_berekend.txt";
     if ($run == 0)
     {
        $path_file_uit = "./data/" . "ys" . $ysfile_station_nummer . "_" . $file_jaar_2_char . "_00_berekend_run_globaal.txt";
     }
     else if ($run == 1)
     {
        $path_file_uit = "./data/" . "ys" . $ysfile_station_nummer . "_" . $file_jaar_2_char . "_00_berekend_run_lokaal.txt";
     	
     }
  } // if ($UITVOER_FILE == 1)
  
  // uitvoer file openen
  //if ($UITVOER_FILE == 1 && $run == 0)
  if ($UITVOER_FILE == 1)
  {
     $file_uit = fopen($path_file_uit, "wb");
     if ($file_uit == null)
     {
  	     if ($DEBUG != 0)
  	     {
           fwrite($file_uit, "+++-> (ys)uitvoer file (" . $file_uit . ") niet te openen<br>");
           fwrite($file_uit, "<br>");        
  	     }
     } // if ($file_uit == null)  
  } // if ($UITVOER_FILE == 1)
  
  // initialisatie
  $analyse_record_nummer = 0;
  $aantal_tussenliggende_forecast_records = 0;
  $aantal_forecast_records = 0;
  
  // NB indien geen waarnemingen aanwezig kunnen er forecast records tusen de ablyse records zitte
  //    daarom de constructie met $aantal_tussenliggende_forecast_records en $aantal_forecast_records (zie hieronder)
  //
  // 9 1 424 5  81  7888 0   2  69   0   0
  // 9 1 512 3  85  7088 0   0  69   0   0
  // 4 1 524 5  81  7888 0   2  70   0   0
  // 4 1 612 3  85  7088 0   0  71   0   0
  // 9 1 624 3  71  6688 0   0  70   0   0
  // 9 1 712 2  79  7488 0   0  71   0   0
  // 9 1 724 4  64  5588 0   2  69   0   0
  // 9 1 812 5  74  6388 0   1  70   0   0
  // 9 1 824 5  68  6288 0   1  69   0   0
  // 9 1 912 4  61  5988 0  70  68   0   0
  // 9 1 924 2  46  4488 0   0  66   0   0
  // 4 11012 4  61  5988 0  70  65   0   0
  // 9 11024 1  -6 -1888 0   2  55   0   0
  // 9 11112 2  12 -2988 0   0  49   0   0
  
  
  // invoer file het record nummer van de analyse record (het laatste record wat met een 9 begint) bepalen
  //
  $file = fopen($path_file_in, "rb");
  if ($file != null)
  {
     // eerste record is altijd header record 	
  	  $record = fgets($file);
  	  $analyse_record_nummer++;            // i.v.m. header record
  	  
  	  while(!feof($file))
  	  {
  	  	  $record = fgets($file);
  	  	  
  	  	  if (!feof($file))
  	  	  {
  	  	     $mark = substr($record, 0, 1);    // 9=analyse; 4=forecast

  	  	     if ($mark == 4)
  	  	     {
  	  	        $aantal_forecast_records++;	
  	  	     }
  	  	     else if ($mark == 9)
  	  	     {
  	  	        $analyse_record_nummer++;	
  	  	        
  	  	        $aantal_tussenliggende_forecast_records += $aantal_forecast_records;
  	  	        $aantal_forecast_records = 0;
  	  	     }
  	  	  } // if (!feof($file))
  	  } // while(!feof($file))

  	  $analyse_record_nummer += $aantal_tussenliggende_forecast_records;

  	  
  	  if ($DEBUG != 0)
  	  {

        fwrite($file_debug, "===============================================================");
        fwrite($file_debug, "<br>");
  	     fwrite($file_debug, "+---- aantal_forecast_records = " . $aantal_forecast_records . "<br>");
        fwrite($file_debug, "+---- aantal_tussenliggende_forecast_records = " . $aantal_tussenliggende_forecast_records . "<br>");
        fwrite($file_debug, "+---- analyse_record_nummer (inclusief tussenliggende forecast records) = " . $analyse_record_nummer . "<br>");
        fwrite($file_debug, "<br>");
        fwrite($file_debug, "===============================================================");
  	  }

 	  
  	  fclose($file);
  } // if ($file != null)
  else 
  {
  	  if ($DEBUG != 0)
  	  {
        fwrite($file_debug, "+++-> (ys)inlees file (" . $path_file_in . ") niet te openen<br>");
        fwrite($file_debug, "<br>");        
  	  }
  } // else  

  
  // initialisatie
  $record_teller = 0;
  
  // invoer file daadwerkelijk lezen records
  //
  $file = fopen($path_file_in, "rb");
  if ($file != null)
  {
     // header record inlezen
     $record = fgets($file);	
     $record_teller++;
     
     if ($DEBUG != 0)
     {
        fwrite($file_debug, "===============================================================");
        fwrite($file_debug, "<br>");
     	  fwrite($file_debug, "+---- inlees file  = " . $path_file_in . "<br>");
     	  fwrite($file_debug, "<br>");
    	
     	  fwrite($file_debug, "===============================================================");
        fwrite($file_debug, "<br>");
     	  fwrite($file_debug, "+---- header record  = " . $record . "<br>");
     	  fwrite($file_debug, "<br>");
     }
     
  	  if (strlen($record) >= 37)             // totale header record lengte 37 char
  	  {
  	  	  $mprint = substr($record, 0, 1);    // scherm uitvoer mode
  	  	  $mzw    = substr($record, 1, 2);    // waterdiepte (01 - 99 m)
  	  	  $mtown  = substr($record, 3, 2);    // scaling urban heating (01 - 89)
  	  	  $mremov = substr($record, 5, 2);    // sneeuw verwijderen (01 = sneeuw > 5cm dik wordt verwijderd; 02 = alle sneeuw wordt verwijderd aan eind 12 uur tijdstap)
  	  	  $mclear = substr($record, 7, 2);    // wind effect (01 wind verhinderd tijdelijk ijsaangroei)
  	  	  $mlat   = substr($record, 9, 4);    // breedte
  	  	  $mcnt   = substr($record, 13, 4);   // aantal data records die moet worden verwijderd
  	  	  $n      = substr($record, 17, 2);   // bedekingsgraad
  	  	  $mww    = substr($record, 19, 2);   // ww code
  	  	  $mprec  = substr($record, 21, 4);   // neerslag
  	  	  $mtw    = substr($record, 25, 4);   // water temp.
  	  	  $mzi    = substr($record, 29, 4);   // ijsdikte
  	  	  $mzs    = substr($record, 33, 4);   // sneeuwdikte
  	  }
  	  
  	  // header record uit invoer file naar uitvoer file schrijven
  	  if ($UITVOER_FILE == 1)
  	  {
  	     if ($file_uit != null)
  	     {
  	     	  if ($run == 1)
  	     	  {
  	     	  	  fwrite($file_uit, "\n-----------------------------------------------\n");
  	     	  }
  	        fwrite($file_uit, $record);
  	        //fwrite($file_uit, "\n");	
  	     }
  	  }
  	  
     if ($mzw < 1)
     {
        $mzw = 1;
     }

     if ($mtown >= 90)
     {
        $mtown = 10 * ($mtown % 10);
        $mtown1 = 1;
     }
     $tclim = $mtw / 10.0;
     $tn    = 0.6;                                                                // tn = wet bulb temperature
     $tw    = $mtw / 10.0;                                                        // mtw = water temperatuur in 0.1 C
     $exch0 = 4.0;
     $qzi   = 1.0 * $mzi;                                                         // mzi = ijsdikte in mm
     $zs    = 1.0 * $mzs;
     
     
  	  ////// volgende records ///////
  	  
  	  
     // -- Start of main loop, for each time step of 12 hours.
  	  
  	  while(!feof($file))
     {
        $mtw0 = $mtw;            // mtw0 nog nodig????                               // mtw = water temperatuur
        //$mios = -2;
       	
        $record = fgets($file);
        
        if (!feof($file))
        {
           $record_teller++;
        }
        
        if ($DEBUG != 0)
        {
        	  fwrite($file_debug, "===============================================================");
           fwrite($file_debug, "<br>");
        	  fwrite($file_debug, "+---- record  = " . $record . "<br>");
        	  fwrite($file_debug, "<br>");
        }
        
        if (strlen($record) >= 37)              
        {
           ////// data records
           //
        	  $mark  = substr($record, 0, 1);    // 9=analyse; 4=forecast
           $mmm   = substr($record, 1, 2);
           $mdd   = substr($record, 3, 2);
           $mhh   = substr($record, 5, 2);
      	  $mff   = substr($record, 7, 2);
      	  $mta   = substr($record, 9, 4); 
      	  $mrh   = substr($record, 13, 4);
      	  $n     = substr($record, 17, 2);
      	  $mww   = substr($record, 19, 2);
      	  $mprec = substr($record, 21, 4);

        	  // watertemperatuur (mtw), ijsdikte (mzi) en
        	  // sneeuwdikte (mzs) moeten worden uitgerekend
        	  $mtw  = 0;                                                                    // water temp
        	  $mzi  = 0;                                                                    // ijsdikte
           ///$mzs  = 0;  // 12-11-2013
           
        	  if ($mff <= 0)                                                               // mff = windsnelheid op 10 m
        	  {
        		  $mff = 1;
        	  }

        	  //if (($mcnt != 0) && ($jcnt == $mcnt + 2))
        	  //{
        	  //$tw        = $mtw / 10.0;                                                    // mtw = water temperatuur in 0.1 C
        	  //$qzi       = 1.0 * $mzi;                                                     // mzi = ijsdikte in mm
        	  //$mzs       = $mzs0;                                                          // mzs0 = sneeuwdikte in mm
        	  //$snwat[7]  = 0.125 * $mzs;
        	  //$msnage[7] = 8;
        	  //}


        	  if ($jclim < 30)                                        // zal dus altijd het geval zijn (omdat programma maar 1x langs komt en jclim start als 1!
        	  {
        		  $jclim = $jclim + 1;                                 // dus zal altijd 2 zijn
        	  }

        	  $tclim = $tclim * (1. - 1. / $jclim) + $mta / 10. / $jclim;
        	  $townclim = 0.;
        	  $mday = 30 * $mmm + $mdd - 30;                                           // $mday = dag van het jaar
        	  $townlim= 10.0 - 3.0 * sin(($mday - 30) * $ppy);

        	  if ($mtown1 == 1)
        	  {
        		  if ($tclim < $townlim)
        		  {
        			  $townclim = 0.4 * ($townlim - $tclim);
        		  }
        		  if ($tclim > $townlim)
        		  {
        			  $townclim = 0.4 * ($townlim - $tclim);
        		  }
        	  } // if ($mtown1 == 1)


        	  // -- Call wet bulb; test on 'rain'; correct timestep for new ice.
        	  $ta = $mta / 10.0;                                                           // ta = air temperature  // mta = air temperatuur in 0.1 0C
        	  //$e = $mrh * 0.06107 * pow(10.0, 7.6 * ta / (ta + 242.0));
        	  $e = 6.107 * pow(10.0, 0.76 * $mrh / ($mrh / 10. + 242.0));                  // e = vapour pressure // mrh = dauw punt temp
        	  $tn0 = $tn;
        	  $tn = TWET($ta, $e, $DEBUG, $file_debug);                                                         // $tn = wet bulb temp

        	  if ($DEBUG != 0)
        	  {
        		  fwrite($file_debug, "+---- maand   = " . $mmm . "<br>");
        		  fwrite($file_debug, "+---- dag     = " . $mdd . "<br>");
        		  fwrite($file_debug, "+---- uur     = " . $mhh . "<br>");
        		  fwrite($file_debug, "+---- mff = " . $mff . "<br>");
        		  fwrite($file_debug, "+---- mta = " . $mta . "<br>");
        		  fwrite($file_debug, "+---- n = " . $n . "<br>");
        		  fwrite($file_debug, "+---- mrh = " . $mrh .  "<br>");
        		  fwrite($file_debug, "+---- mprec = " . $mprec . "<br>");
        		  fwrite($file_debug, "+---- mtw = " . $mtw . "<br>");
        		  fwrite($file_debug, "+---- mzi = " . $mzi . "<br>");
        		  fwrite($file_debug, "+---- zs = " . $zs . "<br>");
        		  fwrite($file_debug, "+---- tn = " . $tn . "<br>");
        		  fwrite($file_debug, "<br>");
        	  }


        	  // -- If not already specified on input, precipitation at warm
        	  // -- wet bulb temperatures is treated as 'rain' (minus sign):
        	  $rprec = $mprec / 10.0;                                                      // mprec = precipitation in ... mm
        	  if ((($tn > 0.5) && ($tn0 > 0.5)) || ($tn + $tn0 > 2))
        	  {
        		  $rprec = -abs($rprec);
        	  }


        	  // -- For thin ice sheets the time step has to be reduced:
        	  $mslow = 1;
        	  if (($tw < 1.0) && ($qzi < 50.0))                                            // $qzi = ijsdikte in mm // $tw = water temp in 1 C
        	  {
        		  $mslow = 4;
        	  }


        	  //     3.2.Recapitulation of snow cover; ageing of old layers.
        	  // -- Ageing of old layers, shifting of them in case of fresh snow.
        	  // -- Computation of new snow thickness and aequivalent water.

        	  //echo "---- qzi = " . $qzi . "<br>";

        	  for ($j = 0; $j < 15; $j++)
        	  {
        		  //if (($qzi <= 0.0) || ($zs <= 0))                                         // $qzi = ijsdikte in mm  // $zs = sneeuw dikte in mm
        		  if (($qzi <= 0.0) || ($mzs <= 0))      // 12-11-2013
        		  {
        			  $snwat[$j]  = 0.0;
        			  $msnage[$j] = 0;
        		  }

        		  if ($msnage[$j] > 0)
        		  {
        			  $msnage[$j] = $msnage[$j] + 1;
        		  }
        	  } // for ($j = 0; $j < 15; $j++)


        	  if (($qzi > 0.0) && ($rprec > 0.0))                                          // $qzi = ijsdikte in mm
        	  {
        		  $snwat[14]  = $snwat[14] + $snwat[13];
        		  $msnage[14] = $msnage[13];

        		  //DO 203 j=14,2,-1
        		  for ($j = 13; $j >= 1; $j--)
        		  {
        			  $snwat[$j]  = $snwat[$j - 1];
        			  $msnage[$j] = $msnage[$j - 1];
        		  }

        		  $snwat[0]  = $rprec;
        		  $msnage[0] = 1;
        	  } // if (($qzi > 0.0) && ($rprec > 0.0))

        	  
        	  if ($DEBUG != 0)
        	  {
        	  	  fwrite($file_debug, "j qzi zs  snwat msnage");
        	  	  fwrite($file_debug, "<br>");
        	     for ($j = 0; $j < 15; $j++)
        	     {
                 fprintf($file_debug, "%02d", $j);
           	     fprintf($file_debug, "%10.2f", $qzi);
           	     fprintf($file_debug, "%5.2f", $zs);
           	     fprintf($file_debug, "%5.2f", $snwat[$j]);
           	     fprintf($file_debug, "%2d", $msnage[$j]);
           	     fwrite($file_debug, "<br>");
           	     
        	     } // for ($j = 0; $j < 15; $j++)
        	     
        	     fwrite($file_debug, "<br>");
        	  }	
        	  

        	  $zs    = 0;                                                                  // $zs = sneeuw dikte in mm
        	  $sntot = 0.0;
        	  for ($j = 0; $j < 15; $j++)
        	  {
        		  $zs   = $zs + round(1000.0 * $snwat[$j] / (90.0 + 6.0 * $msnage[$j]));  //round(3.4) --> 3 en round(3.5) --> 4
        		  $sntot = $sntot + $snwat[$j];
        	  }

        	  $snloss = 0.0;
        	  if ($zs <= 0)                                                                // $zs = sneeuw dikte in mm
        	  {
        		  $zs = 0;
        	  }


        	  //
        	  //       3.3.Analysis of surface conditions(mx)
        	  //
        	  //do 299 jj=1,mslow
        	  for ($jj = 1; $jj <= $mslow; $jj++)   // OF $jj = 0; $jj < $mslow ???????????????????????
        	  {
        		  $mtime = 12 / $mslow;
        		  $mx = 0;                                                                  // surface condition = ice + snow
        		  if ($qzi <= 0.0)                                                          // $qzi = ijsdikte in mm
        		  {
        			  $mx = 3;                                                               // surface condition = open water
        		  }
        		  else
        		  {
        			  if ($zs <= 0)                                                          // $zs = sneeuw dikte in mm
        			  {
        				  $mx = 1;                                                            // surface condition = clear ice
        				  if ($tn >= 0.0)                                                     // $tn = wet bulb temperature
        				  {
        					  $mx = 2;                                                         // surface condition = wet ice
        				  }
        			  } // if ($zs <= 0)
        		  } // else



        		  //       3.4.Main computation.
        		  //       3.4.1.Radiation and surface properties
        		  // -- Computation of global, long wave and net radiation:
        		  $mday = 30 * $mmm + $mdd - 30;                                            // KAN BETER VIA php date("z") //	The day of the year (starting from 0) 	0 through 365
        		  $nh = $n % 10;

        		  if ($nh > 8)
        		  {
        			  $nh = 8;
        		  }

        		  $nn = floor($n / 10);                                                     // INT == FLOOR ??????????????/

        		  if ($nn > 8)
        		  {
        			  $nn = 8;
        		  }

        		  if ($nh > $nn)
        		  {
        			  $nh = $nn;
        		  }

        		  $sollen= ($mday + 279.1 + 1.9 * sin($mday * $ppy)) * $ppy;
        		  $soldcl = atan(0.398 * sin($sollen));
        		  $rdhelp = 1353.0 * (1.0 + 0.01675 * cos($mday * $ppy)) / (1.0 - pow(0.01675, 2.));
        		  $rdglob = 0.0;


        		  // -- Estimate daily solar flux from hourly values:

        		  //DO 209, j=-11,12
        		  for ($j = -11; $j <= 12; $j++)
        		  {
        			  $solelv = cos((15 * $j + 2.47 * sin(2.0 * $sollen) - 1.9 * sin($mday * $ppy)) * $ppy);
        			  $solelv = sin($mlat * $ppy) * sin($soldcl) - cos($mlat * $ppy) * cos($soldcl) * $solelv;

        			  // -- The following turbidity function () may require local version:
        			  // --    (1992-1993 used: 0.60+0.16*solelv)
        			  if ($solelv > 0.0)
        			  {
        				  $rdglob = $rdglob + $rdhelp * $solelv * (0.45 + 0.40 * $solelv);
        			  }

        			  //echo "---- j = " . $j . "<br>";
        			  //echo "---- solelv = " . $solelv . "<br>";
        			  //echo "---- rdglob = " . $rdglob . "<br>";
        			  //echo "<br>";

          		} // for ($j = -11; $j <= 12; $j++)


          		// -- The solar flux is concentrated in the noon time step.
        		  $rdhelp = $rdglob / 24.0;

        		  //echo "---- nn = " . $nn . "<br>";
        		  //echo "---- nh = " . $nh . "<br>";


        		  if ($nn == $nh)
        		  {
        			  $rdglob = $rdglob / 12.0 * (1.0 - 0.0114 * $nh * $nn);
        		  }
        		  else
        		  {
        			  $rdglob = $rdglob / 12.0 * (1.0 - 0.0114 * ($nh + 1) * $nn);
        		  }

        		  if (($mhh == 24) || ($rdglob < 0))
        		  {
        			  $rdglob = 0.0;
        		  }

        		  if ($mx == 0)                                                             // $mx 0 = ice + snow
        		  {
        			  $emiss  = 0.9;
        			  $wcond  = ($qzi + $zs) / ($qzi / 2.1 + $zs / (2.0 * $sntot / $zs));

          		  $albedo = 0.95 - 0.025 * $msnage[1];
          		  if ($albedo < 0.30)
         		  {
        				  $albedo = 0.30;
        			  }
        		  } // if ($mx == 0)
        		  else
        		  {
        			  $emiss  = 0.95;
        			  $albedo = 0.30;
        			  $wcond  = 2.1;
        			  if ($mx >= 2)                                                          // $mx 2 = wet ice; $mx 3 = open water
        			  {
        				  if ($solelv < 0.5)
        				  {
        					  $solelv = 0.5;
        				  }
        				  $albedo = 0.22 / $solelv - 0.05;
        				  $wcond = 999.0;
        				  if ($mx == 3)                                                       // $smx3 = open water
        				  {
        					  $wcond = 0.6;
        				  }
        			  } // if ($mx >= 2)
        		  } // else

          	  $rdlong = (0.76 + 0.004 * $ta) * $ppb * pow($ta + 273.0, 4.0) + (2.25 * $nn + 5.25 * $nh);
        		  $rdnet = (1.0 - $albedo) * $rdglob - $emiss * ($ppb * pow($tn + 273.0, 4.0) - $rdlong);

        		  if ($DEBUG != 0)
        		  {
        			  //echo "---- ppb = " . $ppb . "<br>";
        			  //echo "---- (0.76 + 0.004 * ta) * ppb = " . (0.76 + 0.004 * $ta) * $ppb . "<br>";
        			  //echo "---- pow(ta + 273.0, 4.0)= " . pow($ta + 273.0, 4.0) . "<br>";
        			  //echo "---- (0.76 + 0.004 * ta) * ppb * pow(ta + 273.0, 4.0)= " . (0.76 + 0.004 * $ta) * $ppb * pow($ta + 273.0, 4.0) . "<br>";


        			  fwrite($file_debug, "+---- albedo = " . $albedo . "<br>");
        			  fwrite($file_debug, "+---- solelv = " . $solelv . "<br>");
        			  fwrite($file_debug, "+---- rdglob = " . $rdglob . "<br>");
        			  fwrite($file_debug, "+---- rdlong = " . $rdlong . "<br>");
        			  fwrite($file_debug, "+---- rdnet = " . $rdnet . "<br>");

        			  fwrite($file_debug, "+---- emiss = " . $emiss . "<br>");
        			  //fwrite($file_debug, "+---- msnage[1] = " . $msnage[1] . "<br>");
        			  fwrite($file_debug, "<br>");
        		  }

        		  //         3.4.2.Exchange coefficient and surface temperature.
        		  // -- Iterative determination of both related quantities

        		  //DO 211 j=1,20
        		  for ($j = 1; $j <= 20; $j++)
        		  {
        			  $te= $tn + $rdnet / $exch0;

        			  if (($mx >= 2) || ($qzi == 0.0))                                       // $mx 2 = wet ice; $mx 3 = open water
        			  {
        				  $ts = $tw;
        			  }
        			  else
        			  {
        				  $ts = ($te - $pptf) / (1.0 + 1000.0 * $wcond / ($exch0 * ($qzi + $zs)));
        				  if ($mx == 1)                                                       // $mx 1 = clear ice
        				  {
        					  $ts = $ts - 0.75 * (1.0 - $albedo) * $rdglob / ($exch0 + 1000.0 * $wcond / ($qzi + $zs));
        				  }

        				  if (($mx == 0) && ($sntot > $qzi / 10.0))                          // $mx 0 = ice + snow
        				  {
        					  $ts = $pptf + ($te - $pptf) / (1 + 0.002 * $sntot / $exch0);
        				  }
        			  } // else

        			  if (($ts > $pptf) && ($qzi > 0))
        			  {
        				  $ts = $pptf;
        			  }


        			  if (($ts < $ta - 0.5) || ($mff > 6.5))
        			  {
        				  $exch = 4.0 + 2.5 * $mff;
        			  }
        			  else
        			  {
        				  $exch = 4.0;
        				  $exhelp = 1.0 - 10.0 * ($ta - $ts) / pow($mff, 2.0);

        				  if ($exhelp > 1000)
        				  {
        					  $exhelp = 1000.0;
        				  }

        				  if ($exhelp > 0.001)
        				  {
        					  $exch = $exch + 2.5 * $mff * sqrt($exhelp);
        				  }
        			  } // else

        			  if (abs($exch - $exch0) < 0.1)
        			  {
        				  //goto 213
        				  break;
        			  }

        			  $exch0 = $exch;
        		  } // for ($j = 1; $j <= 20; $j++)



        		  //     3.4.3.Watertemperature and/or ice thickness
        		  // -- (the various branches are commmented between the lines)
        		  $te = $tn + $rdnet / $exch;


        		  if ($DEBUG != 0)
        		  {
        			  fwrite($file_debug, "+---- mx = " . $mx . "<br>");
        			  fwrite($file_debug, "+---- te = " . $te . "<br>");
        			  fwrite($file_debug, "+---- ts = " . $ts . "<br>");
        			  fwrite($file_debug, "+---- exch = " . $exch . "<br>");
        			  fwrite($file_debug, "+---- exch0 = " . $exch0 . "<br>");
        			  fwrite($file_debug, "<br>");
        		  }



        		  if ($mx == 3)                                                             // $mx 3 = open water
        		  {
        			  $tw = $tw + $exch * $mtime * ($te - $tw) / (1172 * $mzw);
        			  // -- new version for artificial heating with 3.34*mtown/mzw W/sq.m
        			  // -- extra corr. te in rivers with warm base flow in cold winters:
        			  $tw = $tw + $mtime * $mtown / $mzw * 3.34 / (1172 * $mzw);

        			  if ($mtown1 == 1)
        			  {
        				  $tw = $tw + $townclim * $exch * $mtime / (1172 * $mzw);
        			  }
        		  } // if ($mx == 3)

        		  // -- For first ice redistribute as latent heat of freezing:
        		  if (($tw < $pptf) && ($mx == 3))
        		  {
        			  $qzi = 13.89 * $mzw * ($pptf - $tw);
        			  $tw = $pptf;
        		  }


        		  // -- Account for heat in rain (with temperature of wet bulb):
        		  if (($mx == 2) && ($rprec < 0))                                           // $mx 2 = wet ice
        		  {
        			  $qzi = $qzi + ($tn - $pptf) * $rprec / $mslow / 79.150;
        		  }

        		  if (($qzi > 0.0) && (!(($tw == $pptf) && ($mx == 3))))
        		  {
        			  if (($mx == 0) && ($te > 0))                                           // $mx 0 = ice + snow; $te =
        			  {
        				  // -- Melting of snow:
        				  $snloss = $snloss - $mtime * ($te - $pptf) / (1.0 / $exch) / 90.0;
        			  }
        			  else
        			  {
        				  // -- Ice growth or melt:
        				  $qzi = $qzi - $mtime * ($te - $pptf) / (1 / $exch + ($qzi + $zs) / 1000. / $wcond) / 83.25;
        				  if ($qzi > 1000 * $mzw)
        				  {
        					  $qzi = 1000.0 * $mzw;
        				  }
        			  } // else

        			  // -- Heat loss to ground; estimated extra heat inside towns,
        			  // -- due to artificial effects, advection, warm base flow, etc.
        			  $qzi = $qzi - $mtown * 0.04 * $mtime / $mzw;
        			  if ($mtown1 == 1)
        			  {
        				  $qzi = $qzi - $mtime * $townclim * 4. / 83.25;
        			  }

        			  // -- Evaporation of ice or snow:
        			  $es = 6.107 * pow(10.0, 9.5 * $ts / ($ts + 266.0));
        			  $ev = $mtime * ($exch - 4.0) * ($es - $e) / 743.925;
        			  if ($ev < 0.0)
        			  {
        				  $ev = 0.0;
        			  }
        			  if ($mx != 0)                                                          // $mx 0 = ice + snow
        			  {
        				  $qzi = $qzi - $ev;
        			  }
        		  } // if (($qzi > 0.0) && (!(($tw == $pptf) && ($mx == 3))))

        		  if ($DEBUG != 0)
        		  {
          		  fwrite($file_debug, "+---- qzi = " . $qzi . "<br>");
        			  fwrite($file_debug, "+---- ev = " . $ev . "<br>");
        			  fwrite($file_debug, "+---- es = " . $es . "<br>");
        			  fwrite($file_debug, "+---- mx = " . $mx . "<br>");
        			  fwrite($file_debug, "<br>");
        			  //echo "---- mzw = " . $mzw . "<br>";
        			  //echo "---- zs = " . $zs . "<br>";
        			  //echo "---- wcond = " . $wcond . "<br>";
        		  }

        		  // -- Very thin ice (<3 mm) is usually destroyed:
        		  if ($qzi < 3.0)
        		  {
        			  $qzi = 0.0;
        			  $zs  = 0.0;
        		  }

        		  // -- Update of snow cover to account for melting or evaporation loss
        		  if ($mx == 0)
        		  {
        			  $snloss = $snloss - $ev * 0.9;
        		  }


        		  // DO 241 j=1,15
        		  for ($j = 0; $j < 15; $j++)
        		  {
        			  if (($snwat[$j] != 0.0) && ($snloss < 0.0))
        			  {
        				  if ($snloss + $snwat[$j] >= 0.0)
        				  {
        					  $snwat[$j] = $snwat[$j] + $snloss;
        					  $snloss = 0.0;
        					  //GOTO 241
        				  }
        				  else
        				  {
        					  $snloss = $snloss + $snwat[$j];
        					  $zs = $zs - round(1000.0 * $snwat[$j] / (90.0 + 6.0 * $msnage[$j]));
        					  $sntot = $sntot - $snwat[$j];
        					  $snwat[$j] = 0.0;
        				  } // else
        			  }
        		  } // for ($j = 0; $j < 15; $j++)

        		  if ($snloss < 0.0)
        		  {
        			  $zs = 0.0;
        		  }
        		  if ($snloss < 0.0)
        		  {
        			  $sntot = 0.0;
        		  }

        		  //             3.5.Extra options, e.g. snow removal.
        		  //
        		  // -- A rough criterium for wind induced clearings, taking into
        		  // -- account some effect of water depth (which is usually related
        		  // -- to fetch) and a dependence of ice strength on temperature.
        		  // -- This option simulates later freezing and earlier break-up.
        		  // -- The purpose is to properly compute qzi after re-freezing.
        		  if ($mclear > 0)
        		  {
        			  if ($mx == 3)                                                          // $mx 3 = open water
        			  {
        				  if ($qzi < (10.0 + 2.0 * $mff * pow($mzw, .3)) / $mslow)
        				  {
        					  $qzi = 0.0;
        				  }
        			  } // if ($mx == 3)
        			  else
        			  {
        				  if ($qzi < (5. + 3. * pow($mff * $mzw, .5)) * (1 - sqrt(abs($ts))))
        				  {
        					  $qzi = 0.0;
        				  }
        			  } // else
        		  } // if ($mclear > 0)

        		  if ($qzi == 0.0)
        		  {
        			  $zs = 0.0;
        		  }

        		  // -- Snow removal operations (not at nighttime or on ice<46 mm):
        		  if (!(($mhh == 24) || ($qzi < 46.0)))
        		  {
        			  if (($mremov > 0) && ($zs > 50.0))
        			  {
        				  $zs = 0.0;
        			  }

        			  if ($mremov > 1)
        			  {
        				  $zs = 0.0;
        			  }
        		  } // if (!(($mhh == 24) || ($qzi < 46.0)))
        		  // 299 CONTINUE


        		  if ($DEBUG != 0)
        		  {
        			  fwrite($file_debug, "+---- jj = " . $jj . "<br>");
        			  fwrite($file_debug, "+---- ijsdikte [mm] = " . $qzi . "<br>");
        			  fwrite($file_debug, "+---- water temperatuur [0.1 C] = " . $mtw . "<br>");
        			  fwrite($file_debug, "+---- sneeuw hoogte [mm] = " . $zs . "<br>");
        			  fwrite($file_debug, "<br>");
        		  }

        	  } // for ($jj = 1; $jj <= $mslow; $jj++)


        	  //
        	  //     -----------------------------------------------------------------
        	  //              4.Output of updated record to file; return to 2.
        	  //


        	  $mzi = round($qzi);        // $mzi = ijsdikte in mm (afgerond)
        	  $mzs = round($zs);         // 
        	  $mtw = round(10 * $tw);    // $mtw = water temperatuur in 0.1 C

        	  if ($DEBUG != 0)
        	  {
        		  fwrite($file_debug, "----> berekende ijsdikte [mm] = " . $mzi . "<br>");
        		  fwrite($file_debug, "<br>");
        	  }
/*        	  
        	  ////////// uitvoer naar ysfile //////////
        	  if ($UITVOER_FILE == 1)
        	  {
        	     if ($file_uit != null)
        	     {
        	        fprintf($file_uit, "%1d", $mark);
        	        fprintf($file_uit, "%2d", $mmm);
        	        fprintf($file_uit, "%2d", $mdd);
        	        fprintf($file_uit, "%2d", $mhh);
        	        fprintf($file_uit, "%2d", $mff);
        	        fprintf($file_uit, "%4d", $mta);
        	        fprintf($file_uit, "%4d", $mrh);
        	        fprintf($file_uit, "%2d", $n);
        	        fprintf($file_uit, "%2d", $mww);
        	        fprintf($file_uit, "%4d", $mprec);
        	        fprintf($file_uit, "%4d", $mtw);
        	        fprintf($file_uit, "%4d", $mzi);
        	        fprintf($file_uit, "%4d", $mzs);
        	        fwrite($file_uit, "\n");
        	     } // if ($file_uit != null)
        	  } // if ($UITVOER_FILE == 1)
*/        	  
        	  //////// ijsdata forecast array vullen voor Google graph visualistaie ////////
//        	  if ($mark == 9)                                         // 9=analyse
        	  if ($record_teller == $analyse_record_nummer)
        	  {
        	  	  // NB wordt dus telkens overschreven

        	  	  //$file_jaar = "2010";   // NU NOG EEN DUMMY!!!!
              //$format_analyse_datum_tijd = '%4d%02d%02d%02d';  // bv 2010122912
              //$analyse_datum_tijd = sprintf($format_analyse_datum_tijd, $file_jaar, $mmm, $mdd, $mhh);
              
              $hulp_analyse_date = mktime($mhh, 0, 0, $mmm, $mdd, $file_jaar_analyse); // 24 uur wordt automatisch 00 uur v/d volgende dag                      // NB mktime format: uur-minuut-sec-maand[1-12]-dag[1-31]-jaar-dst
              $analyse_datum_tijd = date("YmdH", $hulp_analyse_date);  // NB m[01 -12[; d[01-30]; H[00-23]  	
              
              $analyse_maand = $mmm;                           // wordt verderop gebruikt voor check jaar overgang
              
//////////////////////////////////////////////////////////////////////////////////////////
        	  	  
//if ($record_teller == $analyse_record_nummer)
//{
// dus het laatste record sie met een 9 begint is nu net ingelezen
        	  	     if ($DEBUG != 0)
  	              {
                    fwrite($file_debug, "+---- analyse_record =" . $record . "<br>");
                    fwrite($file_debug, "<br>");        
  	              }
 



              if ($run == 0)
              {
                    $ijsdata_forecast_string_run_0 = "@" . $analyse_datum_tijd . "#" . $analyse_datum_tijd . "#" . $mzi . "#";
              }
           //   else if ($run == 1)    // dus de tweede (met lokaal ingevule ijsdikte) run
           //   {
           //         $ijsdata_forecast_string_run_1 = "&@" . $analyse_datum_tijd . "#" . $forecast_datum_tijd . "#" . $mzi . "#";
           //   }
              else if ($run == 1)    // dus de tweede (met lokaal ingevule ijsdikte) run
              {
                    $ijsdata_forecast_string_run_1 = "&@" . $analyse_datum_tijd . "#" . $analyse_datum_tijd . "#" . $mzi . "#";
              }

           
           
/*       	  	  
        	  	  // eventueel bijstellen ijsdikte (NB run=0 is de 'officiele'run; run=1 is de run met ingevulde lokale ijsdikte)
        	  	  if (($run == 1) && ($geselecteerde_ijsdikte != ""))
        	  	  {
        	  	  	  // ijsdikte bijstellen
        	  	  	  $qzi = $geselecteerde_ijsdikte * 10;        // van cm -> mm
        	  	  	  
        	  	  	  // sneeuwhoogte bijstellen
        	  	  	  if ($geselecteerde_sneeuwhoogte > 0)
        	  	  	  {
   	              for ($j = 0; $j < 15; $j++)
         	        {
          			     $snwat[$j]  = 0.0;
          			     $msnage[$j] = 0;
        	  	  	     }
        	  	  	  
        	  	  	     $snwat[0] = floatval($geselecteerde_sneeuwhoogte) / 10;  // geselecteerde_sneeuwhoogte in cm
        	  	  	     $msnage[0] = 1;
        	  	  	     
        	  	  	     // NB onderstaande $zs zetten beslist nodig 
        	  	  	     $zs = floatval($geselecteerde_sneeuwhoogte) * 10;        // zs : sneeuwdikte in mm; geselecteerde_sneeuwhoogte in cm
        	  	     }
        	  	     
        	  	     if ($DEBUG != 0)
  	              {
                    fwrite($file_debug, "+---- aangepaste qzi = " . $qzi . "<br>");
                    fwrite($file_debug, "+---- aangepaste snwat[0] = " . $snwat[0] . "<br>");
                    fwrite($file_debug, "+---- aangepaste msnage[0] = " . $msnage[0] . "<br>");
                    fwrite($file_debug, "<br>");        
  	              }
        	  	  	  
        	  	  } // if (($run == 1) etc.
*/        	  	  
//}

///////////////////////////////////////////////////////////////////////////////////////////              
              
        	  } // if ($record_teller == $analyse_record_nummer)  
        	  else if (($mark == 4) && ($record_teller > $analyse_record_nummer))                               // 4=forecast
        	  {
        	  	  // testen op jaar overgang
        	  	  if ($analyse_maand == 12 && $analyse_maand != $mmm)
        	  	  {
        	  	  	  $forecast_jaar = $file_jaar_analyse + 1;    // file_jaar = analyse jaar per definitie!!
        	  	  }
        	  	  else 
        	  	  {
        	  	  	  $forecast_jaar = $file_jaar_analyse;
        	  	  }
         	  	  
        	  	  //$format_forecast_datum_tijd = '%4d%02d%02d%02d';  // bv 2010122912
        	  	  //$forecast_datum_tijd = sprintf($format_forecast_datum_tijd, $forecast_jaar, $mmm, $mdd, $mhh);
              $hulp_forecast_date = mktime($mhh, 0, 0, $mmm, $mdd, $forecast_jaar); // 24 uur wordt automatisch 00 uur v/d volgende dag                      // NB mktime format: uur-minuut-sec-maand[1-12]-dag[1-31]-jaar-dst
              $forecast_datum_tijd = date("YmdH", $hulp_forecast_date);  // NB m[01 -12[; d[01-30]; H[00-23]  	
        	  	  
              
              
 
/*             
              if ($run == 0)
              {
        	  	     if ($ijsdata_forecast_string_run_0 == "")
        	  	     {
                    $ijsdata_forecast_string_run_0 = "@" . $analyse_datum_tijd . "#" . $forecast_datum_tijd . "#" . $mzi . "#";
        	  	     }
        	  	     else 
        	  	     {
        	  	  	     //$ijsdata_forecast_string = $ijsdata_forecast_string . $mdd . "#" . $mhh . "#" . $mzi . "#";
        	  	  	     $ijsdata_forecast_string_run_0 = $ijsdata_forecast_string_run_0 . $forecast_datum_tijd . "#" . $mzi . "#";
        	  	     }
              }
              else if ($run == 1)    // dus de tweede (met lokaal ingevule ijsdikte) run
              {
        	  	     if ($ijsdata_forecast_string_run_1 == "")
        	  	     {
                    $ijsdata_forecast_string_run_1 = "&@" . $analyse_datum_tijd . "#" . $forecast_datum_tijd . "#" . $mzi . "#";
        	  	     }
        	  	     else 
        	  	     {
        	  	  	     //$ijsdata_forecast_string = $ijsdata_forecast_string . $mdd . "#" . $mhh . "#" . $mzi . "#";
        	  	  	     $ijsdata_forecast_string_run_1 = $ijsdata_forecast_string_run_1 . $forecast_datum_tijd . "#" . $mzi . "#";
        	  	     }
              }
*/
      	  	  
              
              
              
        	  	  // eventueel bijstellen ijsdikte (NB run=0 is de 'officiele'run; run=1 is de run met ingevulde lokale ijsdikte)
        	  	  if (($run == 1) && ($eerste_forecast_record_ingelezen == 0) && ($geselecteerde_ijsdikte != ""))
        	  	  {
        	  	  	  // ijsdikte bijstellen
        	  	  	  $qzi = $geselecteerde_ijsdikte * 10;        // van cm -> mm
        	  	  	  
        	  	  	  // water temp bijstellen (altijd nu op 0 zetten omdat er een ijsdikte is gegeven)
        	  	  	  $mtw = 0;                                   // water temperatuur in 0.1 C
        	  	  	  $tw  = 0;
        	  	  	  
        	  	  	  // sneeuwhoogte bijstellen
        	  	  	  if ($geselecteerde_sneeuwhoogte > 0)
        	  	  	  {
   	              for ($j = 0; $j < 15; $j++)
         	        {
          			     $snwat[$j]  = 0.0;
          			     $msnage[$j] = 0;
        	  	  	     }
        	  	  	  
     /********/     //$snwat[0] = floatval($geselecteerde_sneeuwhoogte) / 10;  // geselecteerde_sneeuwhoogte in cm
        	  	  	     $snwat[0] = floatval($geselecteerde_sneeuwhoogte) * 10 / 10;  // geselecteerde_sneeuwhoogte in cm -> * 10 om mm te krijgen delen door 10 om het equivalent in water te krijgen!!! 
        	  	  	     $msnage[0] = 1;    // NB $msnage[0] = 0; wellicht zuiverder maar dan vreemde waarden
        	  	  	     
        	  	  	     
        	  	  	     // NB onderstaande $zs zetten beslist nodig 
        	  	  	     //$zs = floatval($geselecteerde_sneeuwhoogte) * 10;        // zs: (sneeuwdikte) in mm; geselecteerde_sneeuwhoogte: in cm
        	  	  	     $mzs = floatval($geselecteerde_sneeuwhoogte) * 10;
        	  	  	     
        	  	     }

        	  	     else if ($geselecteerde_sneeuwhoogte == 0)
        	  	     {
   	              for ($j = 0; $j < 15; $j++)
         	        {
          			     $snwat[$j]  = 0.0;
          			     $msnage[$j] = 0;
        	  	  	     }
        	  	     	
        	  	     	  $snwat[0] = 0.0;
        	  	     	  $msnage[0] = 1;
        	  	     	  $mzs = 0.0;
        	  	     	  
        	  	     }
        	  	     
        	  	     
        	  	     
        	  	     //$eerste_forecast_record_ingelezen = 1;
        	  	     
        	  	     
        	  	     if ($DEBUG != 0)
  	              {
                    fwrite($file_debug, "+---- aangepaste qzi = " . $qzi . "<br>");
                    fwrite($file_debug, "+---- aangepaste snwat[0] = " . $snwat[0] . "<br>");
                    fwrite($file_debug, "+---- aangepaste msnage[0] = " . $msnage[0] . "<br>");
                    fwrite($file_debug, "<br>");        
  	              }
        	  	  	  
        	  	  } // if (($run == 1) etc.
        	  	  
        	  	  
        	  	  
        	  	  
        	  	  
       	  	  
        	  	  
              if ($run == 0)
              {
        	  	     if ($ijsdata_forecast_string_run_0 == "")
        	  	     {
                    $ijsdata_forecast_string_run_0 = "@" . $analyse_datum_tijd . "#" . $forecast_datum_tijd . "#" . $mzi . "#";
        	  	     }
        	  	     else 
        	  	     {
        	  	  	     //$ijsdata_forecast_string = $ijsdata_forecast_string . $mdd . "#" . $mhh . "#" . $mzi . "#";
        	  	  	     $ijsdata_forecast_string_run_0 = $ijsdata_forecast_string_run_0 . $forecast_datum_tijd . "#" . $mzi . "#";
        	  	     }
              }
              else if ($run == 1)    // dus de tweede (met lokaal ingevule ijsdikte) run
              {
        	  	     if ($ijsdata_forecast_string_run_1 == "")
        	  	     {
                    $ijsdata_forecast_string_run_1 = "&@" . $analyse_datum_tijd . "#" . $forecast_datum_tijd . "#" . $qzi . "#";
        	  	     }
        	  	     else 
        	  	     {
        	  	  	     //$ijsdata_forecast_string_run_1 = $ijsdata_forecast_string_run_1 . $forecast_datum_tijd . "#" . $qzi . "#";
        	  	  	     
                    if ($eerste_forecast_record_ingelezen == 0)
                    {
        	  	  	        $ijsdata_forecast_string_run_1 = $ijsdata_forecast_string_run_1 . $forecast_datum_tijd . "#" . $qzi . "#";
        	  	  	        $mzi = $qzi;
                    }
                    else 
                    {
        	  	  	        $ijsdata_forecast_string_run_1 = $ijsdata_forecast_string_run_1 . $forecast_datum_tijd . "#" . $mzi . "#";
                    	
                    }
        	  	     } // else
        	  	     
        	  	     $eerste_forecast_record_ingelezen = 1;
        	  	     
              } // else if ($run == 1)
        	  	  
        	  	  
        	  	  //$eerste_forecast_record_ingelezen = 1;
        	  	  
        	  	  
        	  	  
        	  } // else if (($mark == 4) && if ($record_teller > $analyse_record_nummer))
        	  
        	  
       	  ////////// uitvoer naar ysfile //////////
        	  if ($UITVOER_FILE == 1)
        	  {
        	     if ($file_uit != null)
        	     {
        	        fprintf($file_uit, "%1d", $mark);
        	        fprintf($file_uit, "%2d", $mmm);
        	        fprintf($file_uit, "%2d", $mdd);
        	        fprintf($file_uit, "%2d", $mhh);
        	        fprintf($file_uit, "%2d", $mff);
        	        fprintf($file_uit, "%4d", $mta);
        	        fprintf($file_uit, "%4d", $mrh);
        	        fprintf($file_uit, "%2d", $n);
        	        fprintf($file_uit, "%2d", $mww);
        	        fprintf($file_uit, "%4d", $mprec);
        	        fprintf($file_uit, "%4d", $mtw);
        	        fprintf($file_uit, "%4d", $mzi);
        	        fprintf($file_uit, "%4d", $mzs);
        	        fwrite($file_uit, "\n");
        	     } // if ($file_uit != null)
        	  } // if ($UITVOER_FILE == 1)

        } // if (strlen($record) > 10)
     } // while(!feof($file)) 
     
     fclose($file);
  } // if ($file != null)
  else 
  {
  	  if ($DEBUG != 0)
  	  {
        fwrite($file_debug, "+++-> (ys)inlees file (" . $path_file_in . ") niet te openen<br>");
        fwrite($file_debug, "<br>");        
  	  }
  } // else  
  
  
  // indien correct geopend, de (ys)uitvoer file ook weer sluiten
  if ($UITVOER_FILE == 1 && (($aantal_runs == 1 && run == 0) || ($aantal_runs == 2 && run == 1)))
  {
     if ($file_uit != null)  
     {
  	     fclose($file_uit);
     }
  } // if ($UITVOER_FILE == 1)
  
  
  } // for ($run = 0; $run < $aantal_runs; $run++)
  

  if ($DEBUG != 0)
  {
     fwrite($file_debug, "+---- ijsdata_forecast_string_run_0 = " . $ijsdata_forecast_string_run_0 . "<br>");
     fwrite($file_debug, "+---- ijsdata_forecast_string_run_1 = " . $ijsdata_forecast_string_run_1 . "<br>");
     fwrite($file_debug, "<br>"); 
  }

  ////////////// return naar aanroepende index file
  // 
  $ijsdata_forecast_string = $ijsdata_forecast_string_run_0 . $ijsdata_forecast_string_run_1;
  echo $ijsdata_forecast_string;
  
  
  if ($DEBUG != 0)
  {
     fwrite($file_debug, "</body>");
     fwrite($file_debug, "</html>");
     fclose($file_debug);
  }
  
  
  
  /*************************************************************************************************************/
  /*                                                                                                           */
  /*                                                                                                           */
  /*                                                                                                           */
  /*************************************************************************************************************/
  function TWET($ptt, $pee, $DEBUG, $file_debug)
  {
  	  // $ptt: lucht temp [1 C]
  	  // $pee: dampspanning (vapour pressure)
  	
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
  
     
     if ($DEBUG != 0)
     {
        fwrite($file_debug, "+---- function TWET: lucht temp [1 C] = " . $ptt . "<br>");
        fwrite($file_debug, "+---- function TWET: dampspanning = " . $pee . "<br>");
        fwrite($file_debug, "+---- function TWET: twet = " . $twet . "<br>");
        fwrite($file_debug, "<br>");
     }

     
     return $twet;
     
  } // function twet()
  

  
  /*************************************************************************************************************/
  /*                                                                                                           */
  /*                                                                                                           */
  /*                                                                                                           */
  /*************************************************************************************************************/ 
  function Bereken_Dauwpunt($rv, $tdry, $DEBUG, $file_debug)
  {
  	  // rv in %
  	  // tdry in 1 C
  	  // twet in 1 C
  	
     $c1  = 6.112;        
     $c2  = 17.62;       
     $c3  = 243.12;
 	  $tdew = 9999;

     
     $ew = $c1 * exp($c2 * $tdry / ($c3 + $tdry));
     
     if ($rv == 100) 
     {                                // 100 % r.v.
        $tdew = $tdry;
     }   
     else
     {
   	  $tdew = $c3 / (-1 + $c2 / (log(($rv / 100) * $ew / $c1)));
     }  

     if ($DEBUG != 0)
     {
        fwrite($file_debug, "+---- function Bereken_Dauwpunt: lucht temp [1 C] = " . $tdry . "<br>");
        fwrite($file_debug, "+---- function Bereken_Dauwpunt: ew = " . $ew . "<br>");
        fwrite($file_debug, "+---- function Bereken_Dauwpunt: tdew [1 C] = " . $tdew . "<br>");
        fwrite($file_debug, "<br>");
     }
  
     
	  return $tdew;
  }  
  
  
  
  /*************************************************************************************************************/
  /*                                                                                                           */
  /*                                                                                                           */
  /*                                                                                                           */
  /*************************************************************************************************************/ 
  function Omzetten_Bewolking_Procent_Naar_Oktas($bewolking, $DEBUG, $file_debug)
  {
     // programma wil 2 char dus 2 okta cijfers, invoer is eenmalig in procenten
     // daar invoer in % omzetten naar okta en dat als 2 identieke chat wegschrijven
     
     $okta_1 = round($bewolking * 8 / 100);               // bv 50%: 50 * 8 /100 = 4; 
  	  //$oktas = $okta_1 * 10 + $okta_1;           // dus bv 4 -> 44
     
  	  $oktas = $okta_1 . $okta_1;                  // dus bv 4 -> 44
  	   
     if ($DEBUG != 0)
     {
        fwrite($file_debug, "+---- function Omzetten_Bewolking_Procent_Naar_Oktas: bewolkingsgraad [1%] = " . $bewolking . "<br>");
        fwrite($file_debug, "+---- function Omzetten_Bewolking_Procent_Naar_Oktas: oktas [code] = " . $oktas . "<br>");
        fwrite($file_debug, "<br>");
     }
  	  
  	  
  	  return $oktas;
  }

//  You probably have an empty line after the closing PHP tag and as you know, 
// content outside the PHP tags is sent out unaltered.
//
//That is why is recommended to not close the PHP tag at the end of the file. 
// Remove the closing tag and your JavaScript will probably work. 
// ( Unless you have some static content before the opening tag too. )
?>  
