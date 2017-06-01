
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" 
                      "http://www.w3.org/TR/html4/strict.dtd">
<html lang="nl">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<title>IJsgroei berekenen</title>

<?php
  //
  // --------------- mode setting -------------
  //
  //$mode = "public";       // public simpele 1 dikte verwachting (geen extra files etc. nodig als invoer)
  $mode = "grafiek";    // grafiek is een 5/10 daagse ijsdikte verwachting die aangepast kan worden 
  //$mode = "private";    // private = voor verificatie
  $tussenresultaten_link_tonen = "nee";  // ja (voor testen op local host) of nee (nee indien op internet)

  if ($mode == "public")
  {
     $geselecteerde_ijsdikte              = $_POST['input_ijsdikte'];              // bv 2
     $geselecteerde_waterdiepte           = $_POST['input_waterdiepte'];           // bv 3,0
     $geselecteerde_sneeuwhoogte          = $_POST['input_sneeuwhoogte'];          // bv 1
     $geselecteerde_sneeuwleeftijd        = $_POST['input_sneeuwleeftijd'];        // bv 3
     $geselecteerde_bewolking             = $_POST['input_bewolking'];             // bv 88
     $geselecteerde_lucht_temperatuur     = $_POST['input_lucht_temperatuur'];     // bv 
     //$geselecteerde_dauw_punt_temperatuur = $_POST['input_dauw_punt_temperatuur']; // bv
     $geselecteerde_rv                    = $_POST['input_rv'];                    // bv 90
     $geselecteerde_wind_snelheid         = $_POST['input_wind_snelheid'];         // bv
     $geselecteerde_aantal_forecast_uren  = $_POST['input_aantal_forecast_uren'];
  }
  else if ($mode == "private")
  {
     $geselecteerde_jaar                  = $_POST['input_jaar'];              
     $geselecteerde_maand                 = $_POST['input_maand'];
     $geselecteerde_dag                   = $_POST['input_dag'];
     $geselecteerde_uur                   = $_POST['input_uur'];
     $geselecteerde_ysfile                = $_POST['input_ysfile'];
  }
  else if ($mode == "grafiek")
  {
  	  $geselecteerde_locatie               = $_POST['input_locatie'];
  	  $geselecteerde_sneeuwhoogte          = $_POST['input_sneeuwhoogte'];
     $geselecteerde_ijsdikte              = $_POST['input_ijsdikte'];              // bv 2
     $geselecteerde_waterdiepte           = $_POST['input_waterdiepte'];           // bv 3,0
  }  
?>	
	

<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">google.load('visualization', '1', {packages: ['corechart']});</script>

<script type="text/javascript">	





/***********************************************************/
/*                                                         */
/***********************************************************/
function drawVisualization() 
{  
 	if (js_error != "")
	{
      document.getElementById("error_field").innerHTML = "<span style='color:red'>" + js_error + "</span>";	      	
	}
	else
	{
		document.getElementById("error_field").innerHTML = "<span style='font-weight:normal;color:black;font-size:14px;'>" + "berekenen kan een aantal seconden duren" + "</span>";	      	
	}
	      
	// terug zetten van de invoer (pagina is gerefreshed)
 	document.getElementById("input_ijsdikte_listbox").value        = js_geselecteerde_ijsdikte;   
 	document.getElementById("input_sneeuwhoogte_listbox").value    = js_geselecteerde_sneeuwhoogte;   
 	document.getElementById("input_locatie_listbox").value         = js_geselecteerde_locatie;

   
	      
	
	/////////----- analyse ------/////////// 
	// bv js_analyse_datum_tijd = 2012120612
	
	//alert ("js_analyse_datum_tijd = " + js_analyse_datum_tijd);
	//alert(js_analyse_datum_tijd.length)
	
   js_analyse_jaar  = js_analyse_datum_tijd_run_0.substr(0, 4);  // substr maar er is ook een substring!!
   js_analyse_maand = js_analyse_datum_tijd_run_0.substr(4, 2);
   js_analyse_dag   = js_analyse_datum_tijd_run_0.substr(6, 2);
   js_analyse_uur   = js_analyse_datum_tijd_run_0.substr(8, 2);

   //alert("js_analyse_jaar = " + js_analyse_jaar);
   //alert("js_analyse_maand = " + js_analyse_maand);
   //alert("js_analyse_dag = " + js_analyse_dag);
   //alert("js_analyse_uur = " + js_analyse_uur);
   
   
   // NB IE:
   // radix
   //
   // Optional. A value between 2 and 36 that specifies the base of the number in numString. 
   // If this argument is not supplied, strings with a prefix of '0x' are considered hexadecimal. 
   // All other strings are considered decimal.

   var d_analyse = new Date(parseInt(js_analyse_jaar, 10), parseInt(js_analyse_maand, 10) -1, parseInt(js_analyse_dag, 10), parseInt(js_analyse_uur, 10));

   var js_analyse_jaar_voor_title = d_analyse.getFullYear();   
   
   var weekday_analyse = new Array(7);
   weekday_analyse[0] = "Zondag";
   weekday_analyse[1] = "Maandag";
   weekday_analyse[2] = "Dinsdag";
   weekday_analyse[3] = "Woensdag";
   weekday_analyse[4] = "Donderdag";
   weekday_analyse[5] = "Vrijdag";
   weekday_analyse[6] = "Zaterdag";

   var js_analyse_weekdag_voor_title = weekday_analyse[d_analyse.getDay()];
   
   var js_analyse_dag_voor_title = d_analyse.getDate();
   
   //alert (js_analyse_weekdag_voor_title);


   var month = new Array(12);
   month[0] = "Januari";
   month[1] = "Februari";
   month[2] = "Maart";
   month[3] = "April";
   month[4] = "Mei";
   month[5] = "Juni";
   month[6] = "Juli";
   month[7] = "Augustus";
   month[8] = "September";
   month[9] = "Oktober";
   month[10] = "November";
   month[11] = "December";
   
   var js_analyse_maand_voor_title = month[d_analyse.getMonth()];    
   //alert (js_analyse_maand_voor_title);

   /////////------ forecast ------///////// 
   var js_forecast_dag = new Array(js_aantal_forecast_periodes);  // Ma, Di, Wo etc.
   for (i = 0; i < js_aantal_forecast_periodes; i++)
   {
      // bv js_line_chart_forecast_datum_tijd[i] = 2012120612
      js_jaar  = js_line_chart_forecast_datum_tijd_run_0[i].substr(0, 4);  // substr maar er is ook een substring!!
      js_maand = js_line_chart_forecast_datum_tijd_run_0[i].substr(4, 2);
      js_dag   = js_line_chart_forecast_datum_tijd_run_0[i].substr(6, 2);
      js_uur   = js_line_chart_forecast_datum_tijd_run_0[i].substr(8, 2);

      //var d = new Date(parseInt(js_jaar), parseInt(js_maand) -1, parseInt(js_dag), parseInt(js_uur));
      js_hulp_maand = (parseInt(js_maand) -1).toString(); ;
      var d = new Date(js_jaar, js_hulp_maand, js_dag, js_uur);

      //alert(js_jaar);
      //alert(js_maand);
      //alert(js_dag);
      //alert(js_uur);
      //alert(d);
      
      var weekday = new Array(7);
      weekday[0] = "Zo";
      weekday[1] = "Ma";
      weekday[2] = "Di";
      weekday[3] = "Wo";
      weekday[4] = "Do";
      weekday[5] = "Vr";
      weekday[6] = "Za";

      js_forecast_dag[i] = weekday[d.getDay()] + "-" + js_uur;  
      
      //alert (js_forecast_dag[i]);
      
   } // for (i = 0; i < js_forecast_periodes; i++)
   
   if (js_geselecteerde_locatie.length < 1 || js_geselecteerde_locatie.indexOf("blanco") != -1)
   {
   	js_referentie_locatie = "Friesland";
   }
   else
   {
   	js_referentie_locatie = js_geselecteerde_locatie;
   }
   
   
   var data = new google.visualization.DataTable();
   data.addColumn('string', 'datum');
   //data.addColumn('number', 'referentie ijsdikte Leeuwarden');
   data.addColumn('number', 'referentie ijsdikte ' + js_referentie_locatie);
   data.addColumn('number', 'ijsdikte lokaal');
   data.addRows([
     [js_forecast_dag[0], parseFloat(js_line_chart_ijsdikte_run_0[0]) / 10, null],
     //[js_forecast_dag[0], parseFloat(js_line_chart_ijsdikte_run_0[0]) / 10, parseFloat(js_line_chart_ijsdikte_run_1[0]) / 10],
     [js_forecast_dag[1], parseFloat(js_line_chart_ijsdikte_run_0[1]) / 10, parseFloat(js_line_chart_ijsdikte_run_1[1]) / 10],
     [js_forecast_dag[2], parseFloat(js_line_chart_ijsdikte_run_0[2]) / 10, parseFloat(js_line_chart_ijsdikte_run_1[2]) / 10],
     [js_forecast_dag[3], parseFloat(js_line_chart_ijsdikte_run_0[3]) / 10, parseFloat(js_line_chart_ijsdikte_run_1[3]) / 10],
     [js_forecast_dag[4], parseFloat(js_line_chart_ijsdikte_run_0[4]) / 10, parseFloat(js_line_chart_ijsdikte_run_1[4]) / 10],
     [js_forecast_dag[5], parseFloat(js_line_chart_ijsdikte_run_0[5]) / 10, parseFloat(js_line_chart_ijsdikte_run_1[5]) / 10],
     [js_forecast_dag[6], parseFloat(js_line_chart_ijsdikte_run_0[6]) / 10, parseFloat(js_line_chart_ijsdikte_run_1[6]) / 10],
     [js_forecast_dag[7], parseFloat(js_line_chart_ijsdikte_run_0[7]) / 10, parseFloat(js_line_chart_ijsdikte_run_1[7]) / 10],
     [js_forecast_dag[8], parseFloat(js_line_chart_ijsdikte_run_0[8]) / 10, parseFloat(js_line_chart_ijsdikte_run_1[8]) / 10],
     [js_forecast_dag[9], parseFloat(js_line_chart_ijsdikte_run_0[9]) / 10, parseFloat(js_line_chart_ijsdikte_run_1[9]) / 10],
     [js_forecast_dag[10], parseFloat(js_line_chart_ijsdikte_run_0[10]) / 10, parseFloat(js_line_chart_ijsdikte_run_1[10]) / 10],
     [js_forecast_dag[11], parseFloat(js_line_chart_ijsdikte_run_0[11]) / 10, parseFloat(js_line_chart_ijsdikte_run_1[11]) / 10]
   ]);    


    
    // Create and draw the visualization.
    //new google.visualization.LineChart(document.getElementById('visualization')).
    //                                                   draw(data, {curveType: "function",
    //                                                        width: 500, height: 400,
    //                                                        vAxis: {maxValue: 5}}
    //                                                       );
    //var options = { title: 'KNMI IJsdikte verwachting',
    //                width: 800, height: 400,
    //                vAxis: {maxValue: 40}       
    //              };    
    //var options = { title: 'KNMI ijsdikte verwachting' + " (startdatum: " + js_analyse_datum_tijd_run_0 + ") " + js_analyse_weekdag_voor_title + " " + js_analyse_dag + " " + js_analyse_maand_voor_title + " " + js_analyse_jaar,
    var options = { title: 'KNMI ijsdikte verwachting' + " " + js_analyse_weekdag_voor_title + " " + js_analyse_dag_voor_title + " " + js_analyse_maand_voor_title + " " + js_analyse_jaar_voor_title,
                    width: 800, height: 400,
                    
                    //hAxis: {title: 'verwachting', minorGridlines: {count: 10},  titleTextStyle: {color: 'red'}},
                    hAxis: {title: 'verwachting', minorGridlines: {count: 10}},
                   
                    vAxis: {minValue: 0, title: 'ijsdikte [cm]'}         
                  };    

                  
                                   
    var chart = new google.visualization.LineChart(document.getElementById('visualization'));         
    chart.draw(data, options);                                                            

    
	 // terug zetten van de invoer (pagina is gerefreshed)
 	 //document.getElementById("input_locatie_text_field").value      = js_geselecteerde_locatie;   
 	 //document.getElementById("input_sneeuwhoogte_text_field").value = js_geselecteerde_sneeuwhoogte;
 	 //document.getElementById("input_ijsdikte_text_field").value     = js_geselecteerde_ijsdikte;
 	 //document.getElementById("input_waterdiepte_text_field").value  = js_geselecteerde_waterdiepte;
}


// globaal
var js_error = "";
//var js_line_chart_forecast_datum_tijd = new Array();  // voor grafiek
//var js_line_chart_ijsdikte = new Array();             // voor grafiek
//var js_analyse_datum_tijd = "";                       // voor grafiek

var js_line_chart_forecast_datum_tijd_run_0 = new Array();  // voor grafiek
var js_line_chart_forecast_datum_tijd_run_1 = new Array();  // voor grafiek
var js_line_chart_ijsdikte_run_0 = new Array();             // voor grafiek
var js_line_chart_ijsdikte_run_1 = new Array();             // voor grafiek
var js_analyse_datum_tijd_run_0 = "";                       // voor grafiek
var js_analyse_datum_tijd_run_1 = "";                       // voor grafiek

var js_aantal_forecast_periodes = 12;                       // voografiek bv analyse + 5 dagen forecast met een waarde elke 12 en 24 uur geeft 12 perioden

// van php -> JS
var js_mode                                   = "<?php echo $mode ?>"  

//alert (js_mode);

if (js_mode == "public")
{
   var js_geselecteerde_ijsdikte              = "<?php echo $geselecteerde_ijsdikte ?>"              // ijsdikte in cm
   var js_geselecteerde_waterdiepte           = "<?php echo $geselecteerde_waterdiepte ?>"           // waterdiepte in m
   var js_geselecteerde_sneeuwhoogte          = "<?php echo $geselecteerde_sneeuwhoogte ?>"          // sneeuwhoogte in cm
   var js_geselecteerde_sneeuwleeftijd        = "<?php echo $geselecteerde_sneeuwleeftijd ?>"        // dagen
   var js_geselecteerde_bewolking             = "<?php echo $geselecteerde_bewolking ?>"             // %
   var js_geselecteerde_lucht_temperatuur     = "<?php echo $geselecteerde_lucht_temperatuur ?>"     //
   var js_geselecteerde_rv                    = "<?php echo $geselecteerde_rv ?>" //
   var js_geselecteerde_wind_snelheid         = "<?php echo $geselecteerde_wind_snelheid ?>"         
   var js_geselecteerde_aantal_forecast_uren  = "<?php echo $geselecteerde_aantal_forecast_uren ?>" 
}
else if (js_mode == "private")
{
   // geselecteerd gebied  	
   var js_geselecteerde_jaar                  = "<?php echo $geselecteerde_jaar ?>"              
   var js_geselecteerde_maand                 = "<?php echo $geselecteerde_maand ?>"
   var js_geselecteerde_dag                   = "<?php echo $geselecteerde_dag ?>"
   var js_geselecteerde_uur                   = "<?php echo $geselecteerde_uur ?>"	
   var js_geselecteerde_ysfile                = "<?php echo $geselecteerde_ysfile ?>"
}
else if (js_mode == "grafiek")
{
  	var js_geselecteerde_locatie               = "<?php echo $geselecteerde_locatie ?>"
  	var js_geselecteerde_sneeuwhoogte          = "<?php echo $geselecteerde_sneeuwhoogte ?>"
   var js_geselecteerde_ijsdikte              = "<?php echo $geselecteerde_ijsdikte ?>"
   var js_geselecteerde_waterdiepte           = "<?php echo $geselecteerde_waterdiepte ?>"
}  


	
/***********************************************************/
/*                                                         */
/***********************************************************/
function getXMLObject()  //XML OBJECT
{
   var xmlHttp = false;
   try 
   {
     xmlHttp = new ActiveXObject("Msxml2.XMLHTTP")  // For Old Microsoft Browsers
   }
   catch (e) 
   {
     try 
     {
       xmlHttp = new ActiveXObject("Microsoft.XMLHTTP")  // For Microsoft IE 6.0+
     }
     catch (e2) 
     {
       xmlHttp = false   // No Browser accepts the XMLHTTP Object then false
     }
   }
   if (!xmlHttp && typeof XMLHttpRequest != 'undefined') 
	{
     xmlHttp = new XMLHttpRequest();        //For Mozilla, Opera Browsers
   }
   
   
   return xmlHttp;  // Mandatory Statement returning the ajax object created
}	
	
	
var xmlhttp = new getXMLObject();	//xmlhttp holds the ajax object	




/***********************************************************/
/*                                                         */
/***********************************************************/
function ajax_update() 
{
   var getdate = new Date();  //Used to prevent caching during ajax call
   if (xmlhttp) 
   { 
      
      //xmlhttp.open("POST", "ijsgroei_berekenen.php?geselecteerde_ijsdikte=99", true);
      
      if (js_mode == "public")
      {
      	//xmlhttp.open("POST", "ijsgroei_berekenen_per_12_uur_sneeuw_simpel.php", true);
         xmlhttp.open("POST", "ijsgroei_berekenen_per_1_uur_sneeuw_simpel.php", true); 
      }
      else if (js_mode == "private") 
      {
         xmlhttp.open("POST", "ijsgroei_berekenen.php", true); 
      }
      else if (js_mode == "grafiek")
      {
      	// NB grafiek gaat synchroom (derde parameter = false; dit moet wel anders is bij grafiek tekenen niet alle data aanwezig!!)
         xmlhttp.open("POST", "ijsgroei_berekenen_per_12_uur_grafiek.php", false); 
      }
         
      if (js_mode != "grafiek")
      {
      	// NB grafiek gaat synchroom de rest asynchroom
         xmlhttp.onreadystatechange = handleServerResponse;
      }
      
      xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

      if (js_mode == "public")
      {   
      	var par_0 = "mode=" + (js_mode);
         var par_1 = "geselecteerde_ijsdikte=" + (js_geselecteerde_ijsdikte * 10);                          // verzonden in mm
         var par_2 = "geselecteerde_waterdiepte=" + (js_geselecteerde_waterdiepte);                         // verzonden in m
         var par_3 = "geselecteerde_sneeuwhoogte=" + (js_geselecteerde_sneeuwhoogte);                       // verzonden in cm
         var par_4 = "geselecteerde_sneeuwleeftijd=" + (js_geselecteerde_sneeuwleeftijd);                  // verzonden in hele dagen (dus 24 uur perioden)
         var par_5 = "geselecteerde_lucht_temperatuur=" + (js_geselecteerde_lucht_temperatuur * 10);        // verzonden in 0.1 C
         var par_6 = "geselecteerde_bewolking=" + (js_geselecteerde_bewolking);                             // verzonden in code
         //var par_ = "geselecteerde_dauw_punt_temperatuur=" + (js_geselecteerde_dauw_punt_temperatuur * 10);// verzonden in 0.1 C
         var par_7 = "geselecteerde_rv=" + (js_geselecteerde_rv);// verzonden in 1%
         var par_8 = "geselecteerde_wind_snelheid=" + (js_geselecteerde_wind_snelheid);                     // verzonden in m/s
         var par_9 = "geselecteerde_aantal_forecast_uren=" + (js_geselecteerde_aantal_forecast_uren);
         
         xmlhttp.send(par_0 + "&" + par_1 + "&" + par_2 + "&" + par_3 + "&" + par_4 + "&" + par_5 + "&" + par_6 + "&" + par_7 + "&" + par_8 + "&" + par_9);
      }
      else if (js_mode == "private")
      {
      	var par_0 = "mode=" + (js_mode);
         var par_1 = "geselecteerde_jaar=" + (js_geselecteerde_jaar); 	
         var par_2 = "geselecteerde_maand=" + (js_geselecteerde_maand); 	
         var par_3 = "geselecteerde_dag=" + (js_geselecteerde_dag);	
         var par_4 = "geselecteerde_uur=" + (js_geselecteerde_uur);	
         var par_5 = "geselecteerde_ysfile=" + (js_geselecteerde_ysfile);
      
         xmlhttp.send(par_0 + "&" + par_1 + "&" + par_2 + "&" + par_3 + "&" + par_4 + "&" + par_5);
      }
      else if (js_mode == "grafiek")
      {
///////////////////////////////////      	
// 	      if (js_error != "")
//	      {
//            document.getElementById("error_field").innerHTML = "<span style='color:red'>" + js_error + "</span>";	      	
//	      	
//	      }
//	     	
///////////////////////////////////      	
      	var par_0 = "mode=" + (js_mode);
   	   var par_1 = "geselecteerde_locatie=" + js_geselecteerde_locatie;
  	      var par_2 = "geselecteerde_sneeuwhoogte=" + js_geselecteerde_sneeuwhoogte;
         var par_3 = "geselecteerde_ijsdikte=" + js_geselecteerde_ijsdikte;
         var par_4 = "geselecteerde_waterdiepte=" + js_geselecteerde_waterdiepte;
        
         xmlhttp.send(par_0 + "&" + par_1 + "&" + par_2 + "&" + par_3 + "&" + par_4);
         
         var server_response = xmlhttp.responseText;             
		   //alert (server_response);
		   
	      var js_ijsdata_forecast_string = server_response;    
	      // NB bv   
         // @2013010700#2013010700#0#2013010712#0#2013010800#0#2013010812#0#2013010900#0#2013010912#0#2013011000#0#2013011012#0#2013011100#0#2013011112#0#2013011200#0#2013011212#0#
	      // &@2013010700#2013010700#0#2013010712#67#2013010800#67#2013010812#53#2013010900#52#2013010912#39#2013011000#38#2013011012#22#2013011100#23#2013011112#11#2013011200#18#2013011212#13#

	      
	      //alert (js_ijsdata_forecast_string.substr(0,50));
	      //js_ijsdata_forecast_string = js_ijsdata_forecast_string.replace(/ /g,"_");
	      //js_ijsdata_forecast_string.split('2').join('_');
	      
	      
 	      //alert(js_ijsdata_forecast_string.substr(0, 100));
 	         
 	      // split the string
 	      var js_ijsdata_forecast_string_split = js_ijsdata_forecast_string.split("&");

 	      //js_ijsdata_forecast_string_split[0].replace(/&/g, ''); // bv example string: "crt/r2002_2" ->  "ct/2002_2"
 	      //js_ijsdata_forecast_string_split[1].replace(/&/g, ''); // bv example string: "crt/r2002_2" ->  "ct/2002_2"
 	      
 	      for(s = 0; s < js_ijsdata_forecast_string_split.length; s++)
 	      {
 	         //alert("s = " + js_ijsdata_forecast_string_split[s].substr(0, 100));
 	      
 	         // intialisatie
 	         var js_teller_hekje     = -1;      //  
 	         var js_forecast_periode = 0;       // bv eerste 12 uur is periode 0 volgende om 24 uur is periode 1 etc.
            var js_begin_string     = 0;       // kunnen spaties zittten voor het begin van de return string
 	      
 	         // initialisatie
 	         for (i = 0; i < js_aantal_forecast_periodes; i++)
 	         {
 	      	   if (s == 0)
 	      	   {
 	      	      js_line_chart_forecast_datum_tijd_run_0[i] = "";
 	        	      js_line_chart_ijsdikte_run_0[i] = "";
 	      	   }
 	      	   else if (s == 1)
 	      	   {
 	      	      js_line_chart_forecast_datum_tijd_run_1[i] = "";
 	        	      js_line_chart_ijsdikte_run_1[i] = "";
 	         	}
 	         } // for (i = 0; i < js_aantal_forecast_periodes; i++)
 	      
 	         
 	         // initialisatie
 	         js_analyse_datum_tijd = "";
 	         
 	         for (i = 0; i < js_ijsdata_forecast_string_split[s].length; i++)
            {
         	   if (js_teller_hekje == -1)
           	   {
           		   // analyse datum-tijd voor in title grafiek
           		   if (js_ijsdata_forecast_string_split[s].charAt(i) != "#")
           		   {
           			   // NB ER ZITTEN OP DE TERUGONTVANGEN STRING SPATIES DAAROM DEZE CHECK !!!!
           			   if (js_begin_string == 1)
           			   {
           				   if (s == 0)
           				   {
           				      js_analyse_datum_tijd_run_0 += js_ijsdata_forecast_string_split[s].charAt(i); 
           				   }
           				   else if (s == 1)   
           				   {
           				      js_analyse_datum_tijd_run_1 += js_ijsdata_forecast_string_split[s].charAt(i); 
           				   }
           			   }
           			   if (js_ijsdata_forecast_string_split[s].charAt(i) == '@')
           			   {
           	            js_begin_string = 1;
           			   }
           		   }
           		   else
           		   {
           			   //js_teller_hekje++;
           			   js_teller_hekje = 0;
           		   }
           	   } // if (js_teller_hekje == -1)
               else
               {	
            	   js_forecast_periode = Math.floor(js_teller_hekje / 2);
            	
                  if (js_ijsdata_forecast_string_split[s].charAt(i) != "#")
                  {
                     if (js_teller_hekje % 2 == 0)
                     {
                  	   if (s == 0)
                  	   {
                           js_line_chart_forecast_datum_tijd_run_0[js_forecast_periode] += js_ijsdata_forecast_string_split[s].charAt(i); 
                  	   }
                  	   else if (s == 1)
                  	   {
                           js_line_chart_forecast_datum_tijd_run_1[js_forecast_periode] += js_ijsdata_forecast_string_split[s].charAt(i); 
                  	   }
                     }
                     else if ((js_teller_hekje - 1) % 2 == 0)      // ijsdikte
                     {
                  	   if (s == 0)
                  	   {
                           js_line_chart_ijsdikte_run_0[js_forecast_periode] += js_ijsdata_forecast_string_split[s].charAt(i);
                  	   }
                  	   else if (s == 1)
                  	   {
                           js_line_chart_ijsdikte_run_1[js_forecast_periode] += js_ijsdata_forecast_string_split[s].charAt(i);
                  	   }
                     } // else if ((js_teller_hekje - 1) % 2 == 0) 
                  }
                  else
                  {
                     js_teller_hekje++;
                  } // else
               } // else
            } // for (i = 0; i < js_ijsdata_forecast_string.length; i++) 
 	      } // for(s = 0; i < js_ijsdata_forecast_string_split.length; s++)
         
         //alert(js_analyse_datum_tijd.length)
         
      } // else if (js_mode == "grafiek")
      
   } // if (xmlhttp) 
}


/***********************************************************/
/*                                                         */
/***********************************************************/
function handleServerResponse() 
{
   if (xmlhttp.readyState == 4) 
	{
	   if (xmlhttp.status == 200) 
		{
		   var server_response = xmlhttp.responseText;             // bv 176 mm
		   //alert (server_repsonse);
		   
	      //document.getElementById("uitvoer_ijsdikte_to_change").value = parseFloat(server_response) / 10;   // van mm -> cm
	      if ((js_mode == "public") || (js_mode == "private"))
	      {
	         var hulp_server_response = parseFloat(server_response) / 10;                       // van mm -> cm
	         server_response = hulp_server_response.toString().replace(".",",");
	         document.getElementById("uitvoer_ijsdikte_to_change").value = server_response;   
	      }
	      
 	      if (js_mode == "public")
 	      {
            js_geselecteerde_lucht_temperatuur     = js_geselecteerde_lucht_temperatuur.replace(".",",");
            //js_geselecteerde_dauw_punt_temperatuur = js_geselecteerde_dauw_punt_temperatuur.replace(".",","); 
            js_geselecteerde_wind_snelheid         = js_geselecteerde_wind_snelheid.replace(".",","); 
 	      
	         // terug zetten van de invoer (pagina is gerefreshed)
 	         document.getElementById("input_ijsdikte_listbox").value                 = js_geselecteerde_ijsdikte;   
	         document.getElementById("input_waterdiepte_listbox").value              = js_geselecteerde_waterdiepte;
	         document.getElementById("input_sneeuwhoogte_listbox").value             = js_geselecteerde_sneeuwhoogte;
	         document.getElementById("input_sneeuwleeftijd_listbox").value           = js_geselecteerde_sneeuwleeftijd;
	         document.getElementById("input_lucht_temperatuur_text_field").value     = js_geselecteerde_lucht_temperatuur;
		      document.getElementById("input_bewolking_text_field").value             = js_geselecteerde_bewolking;
		      document.getElementById("input_rv_text_field").value                    = js_geselecteerde_rv;
		      document.getElementById("input_wind_snelheid_text_field").value         = js_geselecteerde_wind_snelheid;
  		      document.getElementById("input_aantal_forecast_uren_text_field").value  = js_geselecteerde_aantal_forecast_uren;
 	      }
 	      else if (js_mode == "private")
 	      {
	         // terug zetten van de invoer (pagina is gerefreshed)
 	         document.getElementById("input_jaar_text_field").value   = js_geselecteerde_jaar;   
	         document.getElementById("input_maand_text_field").value  = js_geselecteerde_maand;
	         document.getElementById("input_dag_text_field").value    = js_geselecteerde_dag;
		      document.getElementById("input_uur_text_field").value    = js_geselecteerde_uur;
		      document.getElementById("input_ysfile_text_field").value = js_geselecteerde_ysfile;
 	      }
 	      else if (js_mode == "grafiek")
 	      {
            // GRAFIEK GEBEURT SYNCHROOM -ZIE AJAX_UPDATE- 
 	      	
 	      } // else if (js_mode == "grafiek")
 	      
 	      
	      if (js_error != "")
	      {
            document.getElementById("error_field").innerHTML = "<span style='color:red'>" + js_error + "</span>";	      	
	      	if ((js_mode == "public") || (js_mode == "private"))
	      	{
               document.getElementById("uitvoer_ijsdikte_to_change").value = "";
	      	}
	      }
	      
	      if (js_mode == "public")
	      {
            if ( (js_geselecteerde_ijsdikte.length < 1) && 
                 (js_geselecteerde_waterdiepte.length < 1) && 
                 (js_geselecteerde_sneeuwhoogte.length < 1) && 
                 (js_geselecteerde_sneeuwleeftijd.length < 1) && 
                 (js_geselecteerde_bewolking.length < 1) && 
                 (js_geselecteerde_lucht_temperatuur.length < 1) && 
                 //(js_geselecteerde_dauw_punt_temperatuur.length < 1) &&
                 (js_geselecteerde_rv.length < 1) &&
                 (js_geselecteerde_wind_snelheid.length < 1) &&
                 (js_geselecteerde_aantal_forecast_uren.length < 1) )
            {
               document.getElementById("uitvoer_ijsdikte_to_change").value = "";  	
            }	      
	      } // if (js_mode == "public")
	      else if (js_mode == "private")
	      {
            if ( (js_geselecteerde_jaar.length < 1) && 
                 (js_geselecteerde_maand.length < 1) && 
                 (js_geselecteerde_dag.length < 1) && 
                 (js_geselecteerde_uur.length < 1) && 
                 (js_geselecteerde_ysfile.length < 1) ) 
            {
               document.getElementById("uitvoer_ijsdikte_to_change").value = "";  	
            }	      
	      } // else if (js_mode == "private")
	      
		} // if (xmlhttp.status == 200) 
		// uncommenten voor testen
      //else 
      //{
	   //   alert("Error during AJAX call. Please try again");
	   //}
	} // if (xmlhttp.readyState == 4) 
}



/***********************************************************/
/*                                                         */
/***********************************************************/
if (js_mode == "public")
{
   // reformat (, -> .bv 10,5 -> 10.5)
   js_geselecteerde_lucht_temperatuur     = js_geselecteerde_lucht_temperatuur.replace(",",".");
   //js_geselecteerde_dauw_punt_temperatuur = js_geselecteerde_dauw_punt_temperatuur.replace(",","."); 
   js_geselecteerde_wind_snelheid         = js_geselecteerde_wind_snelheid.replace(",","."); 


   // ijsdikte check
   if ((js_geselecteerde_ijsdikte.length < 1) || 
       (js_geselecteerde_ijsdikte.length > 2) || 
       (parseFloat(js_geselecteerde_ijsdikte) < 0) || 
       (parseFloat(js_geselecteerde_ijsdikte) > 99))
   {
	   //alert("ijsdikte moet liggen tussen 0 - 99 cm");
      js_error = "invoer ijsdikte moet liggen tussen 0 - 99 cm";
   }

   // waterdiepte check
   else if ((js_geselecteerde_waterdiepte.length < 1) || 
            (js_geselecteerde_waterdiepte.length > 2) || 
            (parseFloat(js_geselecteerde_waterdiepte) < 0) || 
            (parseFloat(js_geselecteerde_waterdiepte) > 10))
   {
	   //alert("waterdiepte moet liggen tussen 0 - 10 m");
	   js_error = "invoer waterdiepte moet liggen tussen 0 - 10 m";
   }

   // sneeuwhoogte check
   else if ((js_geselecteerde_sneeuwhoogte.length < 1) || 
            (js_geselecteerde_sneeuwhoogte.length > 2) || 
            (parseFloat(js_geselecteerde_sneeuwhoogte) < 0) || 
            (parseFloat(js_geselecteerde_sneeuwhoogte) > 100))
   {
	   js_error = "invoer sneeuwhoogte moet liggen tussen 0 - 100 cm";
   }

   // sneeuwleeftijd check
   else if (((js_geselecteerde_sneeuwleeftijd.length < 1) || 
            (js_geselecteerde_sneeuwleeftijd.length > 2) || 
            (parseFloat(js_geselecteerde_sneeuwleeftijd) < 0) || 
            (parseFloat(js_geselecteerde_sneeuwleeftijd) > 10)) &&
            (js_geselecteerde_sneeuwleeftijd != "9999") )
   {
	   //if (js_geselecteerde_sneeuwleeftijd != "9999") // 9999 betekent 'nooit'
	   //{
   	   js_error = "invoer aantal dagen geleden laatste sneeuwval moet liggen tussen 'nooit' of 0 - 10 dagen";
	   //}
   }
   
   // bewolking check
   else if ((js_geselecteerde_bewolking.length < 1) || 
            (js_geselecteerde_bewolking.length > 3) || 
            (parseFloat(js_geselecteerde_bewolking) < 0) || 
            (parseFloat(js_geselecteerde_bewolking) > 100) ||
            (isNaN(parseFloat(js_geselecteerde_bewolking))) )
   {
	   js_error = "invoer bewolking moet liggen tussen 0 - 100 %";
   }

   // luchttemperatuur check
   else if ((js_geselecteerde_lucht_temperatuur.length < 1) || 
            (js_geselecteerde_lucht_temperatuur.length > 5) || 
            (parseFloat(js_geselecteerde_lucht_temperatuur) < -35) || 
            (parseFloat(js_geselecteerde_lucht_temperatuur) > 35) || 
            (isNaN(parseFloat(js_geselecteerde_lucht_temperatuur))) )
   {
	   js_error = "invoer luchttemperatuur moet liggen tussen -35,0 - 35,0 C";
   }

   // dauwpunttemperatuur check
//   else if ((js_geselecteerde_dauw_punt_temperatuur.length < 1) || 
//            (js_geselecteerde_dauw_punt_temperatuur.length > 5) || 
//            (parseFloat(js_geselecteerde_dauw_punt_temperatuur) < -50) || 
//            (parseFloat(js_geselecteerde_dauw_punt_temperatuur) > 50) || 
//            (isNaN(parseFloat(js_geselecteerde_dauw_punt_temperatuur))) )
//   {
//	   js_error = "invoer dauwpunttemperatuur moet liggen tussen -50,0 - 50,0 C";
//   }
   // rv check
   else if ((js_geselecteerde_rv.length < 1) || 
            (js_geselecteerde_rv.length > 3) || 
            (parseFloat(js_geselecteerde_rv) < 0) || 
            (parseFloat(js_geselecteerde_rv) > 100) || 
            (isNaN(parseFloat(js_geselecteerde_rv))) )
   {
	   js_error = "invoer relatieve vochtigheid moet liggen tussen 1 - 100 %";
   }


   // windsnelheid check
   else if ((js_geselecteerde_wind_snelheid.length < 1) || 
            (js_geselecteerde_wind_snelheid.length > 4) || 
            (parseFloat(js_geselecteerde_wind_snelheid) < 0) || 
            (parseFloat(js_geselecteerde_wind_snelheid) > 99) || 
            (isNaN(parseFloat(js_geselecteerde_wind_snelheid))) )
   {
	   js_error = "invoer windsnelheid moet liggen tussen 0 - 99 m/s";
   }
   
   // aantal forecast uren check
   else if ((js_geselecteerde_aantal_forecast_uren.length < 1) || 
            (js_geselecteerde_aantal_forecast_uren.length > 3) ||
            (parseFloat(js_geselecteerde_aantal_forecast_uren) < 0) || 
            (parseFloat(js_geselecteerde_aantal_forecast_uren) > 360) ||
            (isNaN(parseFloat(js_geselecteerde_aantal_forecast_uren))) )
   {
	   js_error = "invoer verwachtingsperiode moet liggen tussen 0 - 360 uur";
   }
   else
   {
	   js_error = "";
   }
//
//   // luchttemp versus dauwpunt check
//   if (parseFloat(js_geselecteerde_lucht_temperatuur) < parseFloat(js_geselecteerde_dauw_punt_temperatuur))
//   {
//	   js_error = "invoer luchttemperatuur moet >= invoer dauwpunttemperatuur";
//   }
//
   if ( (js_geselecteerde_ijsdikte.length < 1) && 
        (js_geselecteerde_waterdiepte < 1) && 
        (js_geselecteerde_sneeuwhoogte < 1) && 
        (js_geselecteerde_sneeuwleeftijd < 1) && 
        (js_geselecteerde_bewolking < 1) && 
        (js_geselecteerde_lucht_temperatuur < 1) && 
        //(js_geselecteerde_dauw_punt_temperatuur < 1) &&
        (js_geselecteerde_rv < 1) &&
        (js_geselecteerde_wind_snelheid < 1) &&
        (js_geselecteerde_aantal_forecast_uren < 1) )
   {
      js_error = "";  	
   }
} // if (js_mode == "public")   
//else if (js_mode == "private")   
//{
//	
//	
//}
else if (js_mode == "grafiek")
{
   if (js_geselecteerde_locatie.length < 2 || js_geselecteerde_locatie.indexOf("blanco") != -1)
   {
      js_error = "geen locatie ingevoerd";
   }
	
   else if ((js_geselecteerde_ijsdikte.length < 1) || 
       (js_geselecteerde_ijsdikte.length > 2) || 
       (parseFloat(js_geselecteerde_ijsdikte) < 0) || 
       (parseFloat(js_geselecteerde_ijsdikte) > 40))
   {
	   //alert("ijsdikte moet liggen tussen 0 - 99 cm");
      js_error = "invoer lokaal ijsdikte moet liggen tussen 0 - 40 cm";
   }
	
	//else if (js_geselecteerde_sneeuwhoogte.length < 1)
	//{
	//	js_error = "sneeuwhoogte niet ingevuld";
	//}
	
   else if ((js_geselecteerde_sneeuwhoogte.length < 1) || 
       (js_geselecteerde_sneeuwhoogte.length > 2) || 
       (parseFloat(js_geselecteerde_sneeuwhoogte) < 0) || 
       (parseFloat(js_geselecteerde_sneeuwhoogte) > 90))
   {
	   js_error = "invoer lokaal sneeuwhoogte moet liggen tussen 0 - 90 cm";
   }
	
	
	
	//else if (js_geselecteerde_locatie.length < 1)
	//{
	//	js_error = "locatie niet ingevuld";
	//}
	//else if (js_geselecteerde_waterdiepte.length < 1)
	//{
	//	js_error = "waterdiepte niet ingevuld";
	//}
	
   //if ( (js_geselecteerde_locatie.length < 1) && 
   //     (js_geselecteerde_sneeuwhoogte < 1) && 
   //     (js_geselecteerde_ijsdikte < 1) && 
   //     (js_geselecteerde_waterdiepte < 1) ) 
/*   
   if ( (js_geselecteerde_sneeuwhoogte.length < 1) && 
        (js_geselecteerde_ijsdikte.length < 1) &&
        //(js_geselecteerde_locatie.length < 1 || js_geselecteerde_locatie == "blanco")) 
        ((js_geselecteerde_locatie.length < 1) || (js_geselecteerde_locatie.indexOf("blanco") != -1)) )
   {
      js_error = "";  	
   }	
*/
   
   if ( /*(js_geselecteerde_locatie.length < 1 || js_geselecteerde_locatie.indexOf("blanco") != -1) &&*/
        (js_geselecteerde_sneeuwhoogte.length < 1 || js_geselecteerde_sneeuwhoogte.indexOf("blanco") != -1) &&
        (js_geselecteerde_ijsdikte.length < 1 || js_geselecteerde_ijsdikte.indexOf("blanco") != -1) )
   {
   	//alert(js_geselecteerde_locatie);
   	js_error = ""; 
   }
	
   
}


/////////////////
//
ajax_update();


google.setOnLoadCallback(drawVisualization);



	
</script>	
	
	







	
</head>
<body style="background-image:url(./images/ijsgroei_7_cm_sepia_b.png)">



<?php

/*
//
// zie voor php form list: http://1888software.com/articles/article_select_list.php
//
*/ 

ini_set(error_reporting, 0);           // geen syntax error meldingen i.v.m. veiligheid

/* initialisatie */
$doorgaan = true;



//print "geselecteerde_sneeuwhoogte =" . $geselecteerde_sneeuwhoogte . "<br>";


//
/////////////////////////////// format checks //////////////////////////
//

// for safety remove special tags (tags are never inserted by ijsgroei application but may be directly into the webbrowser URL by a malicious person/program)
//             NB strip_tags() destroys the whole HTML behind the tags with invalid attributes 
//
//$positie = strip_tags($positie);







if ($doorgaan == true)
{
   print "<form action=\"index_ijsgroei_berekenen.php\" method=\"post\">\n";
   print "<div align=\"center\">\n";   
   print "<h3>IJsgroei berekenen</h3>\n";
   print "<table>\n";
   //print "<table id=\"ijs_table\">\n";                        // werkt op zich wel

   // lege regel
   print "\t<tr>\n";
   print "\t\t<td><p></p></td>\n";
   print "\t<tr>\n";
   
   
   print "\t<tr>\n";
   if (($mode == "public") || ($mode == "private"))
   {
      print "\t\t<th colspan=\"2\">invoer</th>";
      print "\t\t<th colspan=\"3\">uitvoer</th>";
   }
   else if ($mode == "grafiek")
   {
      print "\t\t<th colspan=\"2\">invoer lokaal</th>";
   }
   
   print "\t</tr>\n";
  
   
   if ($mode == "public")
   {
      //
      ////////// ijsdikte
      //
      print "\t<tr>\n";
      print "\t\t<td style=\"text-align:left;\">ijsdikte:</td>\n";
      print "\t\t<td style=\"text-align:left;\"><select id=\"input_ijsdikte_listbox\" name=\"input_ijsdikte\">\n";
      print "\t\t\t<option value=\"blanco\" selected></option>\n";
      print "\t\t\t<option value=\"0\">0</option>\n";
      print "\t\t\t<option value=\"1\">1</option>\n";
      print "\t\t\t<option value=\"2\">2</option>\n";
      print "\t\t\t<option value=\"3\">3</option>\n";
      print "\t\t\t<option value=\"4\">4</option>\n";
      print "\t\t\t<option value=\"5\">5</option>\n";
      print "\t\t\t<option value=\"6\">6</option>\n";
      print "\t\t\t<option value=\"7\">7</option>\n";
      print "\t\t\t<option value=\"8\">8</option>\n";
      print "\t\t\t<option value=\"9\">9</option>\n";
      print "\t\t\t<option value=\"10\">10</option>\n";
      print "\t\t\t<option value=\"11\">11</option>\n";
      print "\t\t\t<option value=\"12\">12</option>\n";
      print "\t\t\t<option value=\"13\">13</option>\n";
      print "\t\t\t<option value=\"14\">14</option>\n";
      print "\t\t\t<option value=\"15\">15</option>\n";
      print "\t\t\t<option value=\"16\">16</option>\n";
      print "\t\t\t<option value=\"17\">17</option>\n";
      print "\t\t\t<option value=\"18\">18</option>\n";
      print "\t\t\t<option value=\"19\">19</option>\n";
      print "\t\t\t<option value=\"20\">20</option>\n";
      print "\t\t\t<option value=\"22\">22</option>\n";
      print "\t\t\t<option value=\"24\">24</option>\n";
      print "\t\t\t<option value=\"26\">26</option>\n";
      print "\t\t\t<option value=\"28\">28</option>\n";
      print "\t\t\t<option value=\"30\">30</option>\n";
      print "\t\t\t<option value=\"32\">32</option>\n";
      print "\t\t\t<option value=\"34\">34</option>\n";
      print "\t\t\t<option value=\"36\">36</option>\n";
      print "\t\t\t<option value=\"38\">38</option>\n";
      print "\t\t\t<option value=\"40\">40</option>\n";
      print "\t\t\t</select>cm\n";
      print "\t\t</td>\n";
   
      //
      //////////// berekende ijsdikte    
      //
      print "\t\t<td>&nbsp;</td>\n";
      print "\t\t<td style=\"text-align:left;\">ijsdikte verwachting:</td><td style=\"text-align:left;\"><input type=\"text\" id=\"uitvoer_ijsdikte_to_change\" size=\"3\" >cm</td>\n";  // NB input type text (edit field) heeft geen zin
      print "\t</tr>\n";

      //
      ////////// waterdiepte
      //
      print "\t<tr>\n";
      print "\t\t<td style=\"text-align:left;\">waterdiepte:</td>\n";
      print "\t\t<td style=\"text-align:left;\"><select id=\"input_waterdiepte_listbox\" name=\"input_waterdiepte\">\n";
      print "\t\t\t<option value=\"blanco\" selected></option>\n";
      //print "\t\t\t<option value=\"0,5\">0,5</option>\n";
      //print "\t\t\t<option value=\"1,0\">1,0</option>\n";
      //print "\t\t\t<option value=\"1,5\">1,5</option>\n";
      //print "\t\t\t<option value=\"2,0\">2,0</option>\n";
      //print "\t\t\t<option value=\"2,5\">2,5</option>\n";
      //print "\t\t\t<option value=\"3,0\">3,0</option>\n";
      //print "\t\t\t<option value=\"3,5\">3,5</option>\n";
      //print "\t\t\t<option value=\"4,0\">4,0</option>\n";
      //print "\t\t\t<option value=\"4,5\">4,5</option>\n";
      //print "\t\t\t<option value=\"5,0\">5,0</option>\n";
      print "\t\t\t<option value=\"1\">1</option>\n";
      print "\t\t\t<option value=\"2\">2</option>\n";
      print "\t\t\t<option value=\"3\">3</option>\n";
      print "\t\t\t<option value=\"4\">4</option>\n";
      print "\t\t\t<option value=\"5\">5</option>\n";
      print "\t\t\t<option value=\"6\">6</option>\n";
      print "\t\t\t<option value=\"7\">7</option>\n";
      print "\t\t\t<option value=\"8\">8</option>\n";
      print "\t\t\t<option value=\"9\">9</option>\n";
      print "\t\t\t<option value=\"10\">10</option>\n";
      print "\t\t\t</select>m\n";
      print "\t\t</td>\n";
      
      //
      //////////// berekende ijsgroei per uur    
      //
      print "\t\t<td>&nbsp;</td>\n";
      print "\t\t<td style=\"text-align:left;\">ijsgroei:</td><td style=\"text-align:left;\"><input type=\"text\" id=\"uitvoer_ijsgroei_per_uur_to_change\" size=\"3\" >cm/uur</td>\n";  
      print "\t</tr>\n";

      //
      ////////// sneeuwhoogte
      //
      print "\t<tr>\n";
      print "\t\t<td style=\"text-align:left;\">sneeuwhoogte:</td>\n";
      print "\t\t<td style=\"text-align:left;\"><select id=\"input_sneeuwhoogte_listbox\" name=\"input_sneeuwhoogte\">\n";
      print "\t\t\t<option value=\"blanco\" selected></option>\n";
      print "\t\t\t<option value=\"0\">0</option>\n";
      print "\t\t\t<option value=\"1\">1</option>\n";
      print "\t\t\t<option value=\"2\">2</option>\n";
      print "\t\t\t<option value=\"3\">3</option>\n";
      print "\t\t\t<option value=\"4\">4</option>\n";
      print "\t\t\t<option value=\"5\">5</option>\n";
      print "\t\t\t<option value=\"6\">6</option>\n";
      print "\t\t\t<option value=\"7\">7</option>\n";
      print "\t\t\t<option value=\"8\">8</option>\n";
      print "\t\t\t<option value=\"9\">9</option>\n";
      print "\t\t\t<option value=\"10\">10</option>\n";
      print "\t\t\t<option value=\"12\">12</option>\n";
      print "\t\t\t<option value=\"14\">14</option>\n";
      print "\t\t\t<option value=\"16\">16</option>\n";
      print "\t\t\t<option value=\"18\">18</option>\n";
      print "\t\t\t<option value=\"20\">20</option>\n";
      print "\t\t\t<option value=\"30\">30</option>\n";
      print "\t\t\t<option value=\"40\">40</option>\n";
      print "\t\t\t<option value=\"50\">50</option>\n";
      print "\t\t\t<option value=\"60\">60</option>\n";
      print "\t\t\t<option value=\"70\">70</option>\n";
      print "\t\t\t<option value=\"80\">80</option>\n";
      print "\t\t\t<option value=\"90\">90</option>\n";
      print "\t\t\t<option value=\"100\">100</option>\n";
      print "\t\t\t</select>cm\n";
      print "\t\t</td>\n";
      
      //
      //////////// berekende uitstraling    
      //
      print "\t\t<td>&nbsp;</td>\n";
      print "\t\t<td style=\"text-align:left;\">uitstraling:</td><td style=\"text-align:left;\"><input type=\"text\" id=\"uitvoer_uitstraling_to_change\" size=\"3\" >W/m2</td>\n";  // NB input type text (edit field) heeft geen zin
      print "\t</tr>\n";
      
      //
      /////////////// sneeuwleeftijd
      //
      print "\t<tr>\n";
      print "\t\t<td style=\"text-align:left;\">laatste sneeuwval:</td>\n";
      print "\t\t<td style=\"text-align:left;\"><select id=\"input_sneeuwleeftijd_listbox\" name=\"input_sneeuwleeftijd\">\n";
      print "\t\t\t<option value=\"blanco\" selected></option>\n";
      print "\t\t\t<option value=\"9999\">nooit</option>\n";
      print "\t\t\t<option value=\"0\">0</option>\n";
      print "\t\t\t<option value=\"1\">1</option>\n";
      print "\t\t\t<option value=\"2\">2</option>\n";
      print "\t\t\t<option value=\"3\">3</option>\n";
      print "\t\t\t<option value=\"4\">4</option>\n";
      print "\t\t\t<option value=\"5\">5</option>\n";
      print "\t\t\t<option value=\"6\">6</option>\n";
      print "\t\t\t<option value=\"7\">7</option>\n";
      print "\t\t\t<option value=\"8\">8</option>\n";
      print "\t\t\t<option value=\"9\">9</option>\n";
      print "\t\t\t<option value=\"10\">10</option>\n";
      print "\t\t\t</select>dagen geleden\n";
      print "\t\t</td>\n";

      //
      //////////// berekende warmtegeleiding    
      //
      print "\t\t<td>&nbsp;</td>\n";
      print "\t\t<td style=\"text-align:left;\">warmtegeleiding:</td><td style=\"text-align:left;\"><input type=\"text\" id=\"uitvoer_warmtegeleiding_to_change\" size=\"3\" >W/m2</td>\n";  // NB input type text (edit field) heeft geen zin
      print "\t</tr>\n";
      
      //
      /////////////// bewolking
      //
      print "\t<tr>\n";
      print "\t\t<td style=\"text-align:left;\">bewolkingsgraad:</td><td style=\"text-align:left;\"><input type=\"text\" size=\"3\" id=\"input_bewolking_text_field\" name=\"input_bewolking\">%</td>\n";

      //
      //////////// berekende verdamping    
      //
      print "\t\t<td>&nbsp;</td>\n";
      print "\t\t<td style=\"text-align:left;\">verdamping:</td><td style=\"text-align:left;\"><input type=\"text\" id=\"uitvoer_verdamping_to_change\" size=\"3\" >W/m2</td>\n";  // NB input type text (edit field) heeft geen zin
      print "\t</tr>\n";

      //
      /////////////// lucht temp
      //
      print "\t<tr>\n";
      print "\t\t<td style=\"text-align:left;\">lucht temperatuur:</td><td style=\"text-align:left;\"><input type=\"text\" size=\"5\" id=\"input_lucht_temperatuur_text_field\" name=\"input_lucht_temperatuur\">C</td>\n";
      print "\t</tr>\n";

      //
      /////////////// dauw punt temp
      //
      //print "\t<tr>\n";
      //print "\t\t<td>dauwpunt temperatuur:</td><td><input type=\"text\" size=\"5\" id=\"input_dauw_punt_temperatuur_text_field\" name=\"input_dauw_punt_temperatuur\">C</td>\n";
      //print "\t</tr>\n";
      //
      /////////////// rv
      //
      print "\t<tr>\n";
      print "\t\t<td style=\"text-align:left;\">relatieve vochtigheid:</td><td style=\"text-align:left;\"><input type=\"text\" size=\"3\" id=\"input_rv_text_field\" name=\"input_rv\">%</td>\n";
      print "\t</tr>\n";

      //
      /////////////// wind snelheid
      //
      print "\t<tr>\n";
      print "\t\t<td style=\"text-align:left;\">windsnelheid:</td><td style=\"text-align:left;\"><input type=\"text\" size=\"5\" id=\"input_wind_snelheid_text_field\" name=\"input_wind_snelheid\">m/s</td>\n";
      print "\t</tr>\n";

      //
      /////////////// forecast uren
      //
      print "\t<tr>\n";
      print "\t\t<td style=\"text-align:left;\">verwachtingsperiode:</td><td style=\"text-align:left;\"><input type=\"text\" size=\"3\" id=\"input_aantal_forecast_uren_text_field\" name=\"input_aantal_forecast_uren\">uur vooruit</td>\n";
      print "\t</tr>\n";

      // lege regel
      print "\t<tr>\n";
      print "\t\t<td><p></p></td>\n";
      print "\t<tr>\n";
      
      //
      /////////////// Submit/verzend button
      //
      print "\t<tr>\n";
      print "\t\t<td><input type=\"submit\" value=\"bereken\"></td>\n";
      print "\t</tr>\n";

      // lege regel
      print "\t<tr>\n";
      print "\t\t<td><p></p></td>\n";
      print "\t<tr>\n";

      // lege regel
      print "\t<tr>\n";
      print "\t\t<td><p></p></td>\n";
      print "\t<tr>\n";

      // lege regel
      print "\t<tr>\n";
      print "\t\t<td><p></p></td>\n";
      print "\t<tr>\n";


      //
      ////////////// uitvoer ijsdikte
      //
      //print "\t\t<td>ijsdikte:</td><td><input type=\"text\" id=\"uitvoer_ijsdikte_to_change\" size=\"3\" >cm</td>\n";  // NB input type text (edit field) heeft geen zin


   } // if (js_mode == "public")
   else if ($mode == "private")
   {
      //
      /////////////// jaar
      //
      print "\t<tr>\n";
      print "\t\t<td style=\"text-align:left;\">analyse jaar:</td><td style=\"text-align:left;\"><input type=\"text\" size=\"4\" id=\"input_jaar_text_field\" name=\"input_jaar\">[bv 2012]</td>\n";
      print "\t</tr>\n"; 
   	
      //
      /////////////// maand
      //
      print "\t<tr>\n";
      print "\t\t<td style=\"text-align:left;\">analyse maand:</td><td style=\"text-align:left;\"><input type=\"text\" size=\"4\" id=\"input_maand_text_field\" name=\"input_maand\">[1-12]</td>\n";
      print "\t</tr>\n"; 
   	
      //
      /////////////// dag
      //
      print "\t<tr>\n";
      print "\t\t<td style=\"text-align:left;\">analyse dag:</td><td style=\"text-align:left;\"><input type=\"text\" size=\"4\" id=\"input_dag_text_field\" name=\"input_dag\">[1-31]</td>\n";
      print "\t</tr>\n"; 
      
      //
      /////////////// uur
      //
      print "\t<tr>\n";
      print "\t\t<td style=\"text-align:left;\">analyse uur:</td><td style=\"text-align:left;\"><input type=\"text\" size=\"2\" id=\"input_uur_text_field\" name=\"input_uur\">[12/24]</td>\n";
      print "\t</tr>\n"; 
   	
      //
      /////////////// invoer file (ysfile)
      //
      print "\t<tr>\n";
      print "\t\t<td style=\"text-align:left;\">path ysfile:</td><td style=\"text-align:left;\"><input type=\"text\" size=\"100\" id=\"input_ysfile_text_field\" name=\"input_ysfile\"></td>\n";
      print "\t</tr>\n"; 
      
      // lege regel
      print "\t<tr>\n";
      print "\t\t<td><p></p></td>\n";
      print "\t<tr>\n";

      //
      /////////////// Submit/verzend button
      //
      print "\t<tr>\n";
      //print "\t\t<td><input type=\"submit\" value=\"bereken\"></td>\n";
      print "\t\t<td style=\"text-align:left;\"><input type=\"submit\" value=\"bereken\"></td>\n";
      //print "\t\t<th colspan=\"2\">invoer</th>";
      print "\t</tr>\n";
      
      // lege regel
      print "\t<tr>\n";
      print "\t\t<td><p></p></td>\n";
      print "\t<tr>\n";
   
      // lege regel
      print "\t<tr>\n";
      print "\t\t<td><p></p></td>\n";
      print "\t<tr>\n";
   
      // lege regel
      print "\t<tr>\n";
      print "\t\t<td><p></p></td>\n";
      print "\t<tr>\n";
   
      //
      ////////////// uitvoer ijsdikte
      //
      //print "\t\t<td>ijsdikte +12hr:</td><td><input type=\"text\" id=\"uitvoer_ijsdikte_to_change\" size=\"3\" >cm</td>\n";  // NB input type text (edit field) heeft geen zin
      
   } // else if (js_mode == "private")
   else if ($mode == "grafiek")
   {
/*   	
      //
      /////////////// locatie
      //
      print "\t<tr>\n";
      print "\t\t<td style=\"text-align:left;\">locatie:</td><td style=\"text-align:left;\"><input type=\"text\" size=\"4\" id=\"input_locatie_text_field\" name=\"input_locatie\">[bv 53.0,4.5]</td>\n";
      print "\t</tr>\n"; 
*/   

      print "\t<tr>\n";
      print "\t\t<td style=\"text-align:left;\">uw locatie ligt in provincie:</td>\n";
      print "\t\t<td style=\"text-align:left;\"><select id=\"input_locatie_listbox\" name=\"input_locatie\">\n";
      print "\t\t\t<option value=\"blanco\" selected></option>\n";
      print "\t\t\t<option value=\"Groningen\">Groningen</option>\n";
      print "\t\t\t<option value=\"Friesland\">Friesland</option>\n";
      print "\t\t\t<option value=\"Drenthe\">Drenthe</option>\n";
      print "\t\t\t<option value=\"Overijssel\">Overijssel</option>\n";
      print "\t\t\t<option value=\"Flevoland\">Flevoland</option>\n";
      print "\t\t\t<option value=\"Gelderland\">Gelderland</option>\n";
      print "\t\t\t<option value=\"Utrecht\">Utrecht</option>\n";
      print "\t\t\t<option value=\"Noord-Holland\">Noord-Holland</option>\n";
      print "\t\t\t<option value=\"Zuid-Holland\">Zuid-Holland</option>\n";
      print "\t\t\t<option value=\"Zeeland\">Zeeland</option>\n";
      print "\t\t\t<option value=\"Noord-Brabant\">Noord-Brabant</option>\n";
      print "\t\t\t<option value=\"Limburg\">Limburg</option>\n";
      print "\t\t\t</select>\n";
      print "\t\t</td>\n";




      //
      /////////////// sneeuwhoogte
      //
//      print "\t<tr>\n";
//      print "\t\t<td style=\"text-align:left;\">sneeuwhoogte:</td><td style=\"text-align:left;\"><input type=\"text\" size=\"4\" id=\"input_sneeuwhoogte_text_field\" name=\"input_sneeuwhoogte\">[cm]</td>\n";
//      print "\t</tr>\n"; 
      print "\t<tr>\n";
      print "\t\t<td style=\"text-align:left;\">sneeuwhoogte op ijs op uw locatie:</td>\n";
      print "\t\t<td style=\"text-align:left;\"><select id=\"input_sneeuwhoogte_listbox\" name=\"input_sneeuwhoogte\">\n";
      print "\t\t\t<option value=\"blanco\" selected></option>\n";
      print "\t\t\t<option value=\"0\">0</option>\n";
      print "\t\t\t<option value=\"1\">1</option>\n";
      print "\t\t\t<option value=\"2\">2</option>\n";
      print "\t\t\t<option value=\"3\">3</option>\n";
      print "\t\t\t<option value=\"4\">4</option>\n";
      print "\t\t\t<option value=\"5\">5</option>\n";
      print "\t\t\t<option value=\"6\">6</option>\n";
      print "\t\t\t<option value=\"7\">7</option>\n";
      print "\t\t\t<option value=\"8\">8</option>\n";
      print "\t\t\t<option value=\"9\">9</option>\n";
      print "\t\t\t<option value=\"10\">10</option>\n";
      print "\t\t\t<option value=\"12\">12</option>\n";
      print "\t\t\t<option value=\"14\">14</option>\n";
      print "\t\t\t<option value=\"16\">16</option>\n";
      print "\t\t\t<option value=\"18\">18</option>\n";
      print "\t\t\t<option value=\"20\">20</option>\n";
      print "\t\t\t<option value=\"30\">30</option>\n";
      print "\t\t\t<option value=\"40\">40</option>\n";
      print "\t\t\t<option value=\"50\">50</option>\n";
      print "\t\t\t<option value=\"60\">60</option>\n";
      print "\t\t\t<option value=\"70\">70</option>\n";
      print "\t\t\t<option value=\"80\">80</option>\n";
      print "\t\t\t<option value=\"90\">90</option>\n";
      print "\t\t\t</select>cm\n";
      print "\t\t</td>\n";





      //
      /////////////// ijsdikte
      //
//      print "\t<tr>\n";
//      print "\t\t<td style=\"text-align:left;\">ijsdikte:</td><td style=\"text-align:left;\"><input type=\"text\" size=\"4\" id=\"input_ijsdikte_text_field\" name=\"input_ijsdikte\">[cm]</td>\n";
//      print "\t</tr>\n"; 
      print "\t<tr>\n";
      print "\t\t<td style=\"text-align:left;\">ijsdikte op uw locatie (watertemp 0&deg C):</td>\n";
      print "\t\t<td style=\"text-align:left;\"><select id=\"input_ijsdikte_listbox\" name=\"input_ijsdikte\">\n";
      print "\t\t\t<option value=\"blanco\" selected></option>\n";
      print "\t\t\t<option value=\"0\">0</option>\n";
      print "\t\t\t<option value=\"1\">1</option>\n";
      print "\t\t\t<option value=\"2\">2</option>\n";
      print "\t\t\t<option value=\"3\">3</option>\n";
      print "\t\t\t<option value=\"4\">4</option>\n";
      print "\t\t\t<option value=\"5\">5</option>\n";
      print "\t\t\t<option value=\"6\">6</option>\n";
      print "\t\t\t<option value=\"7\">7</option>\n";
      print "\t\t\t<option value=\"8\">8</option>\n";
      print "\t\t\t<option value=\"9\">9</option>\n";
      print "\t\t\t<option value=\"10\">10</option>\n";
      print "\t\t\t<option value=\"11\">11</option>\n";
      print "\t\t\t<option value=\"12\">12</option>\n";
      print "\t\t\t<option value=\"13\">13</option>\n";
      print "\t\t\t<option value=\"14\">14</option>\n";
      print "\t\t\t<option value=\"15\">15</option>\n";
      print "\t\t\t<option value=\"16\">16</option>\n";
      print "\t\t\t<option value=\"17\">17</option>\n";
      print "\t\t\t<option value=\"18\">18</option>\n";
      print "\t\t\t<option value=\"19\">19</option>\n";
      print "\t\t\t<option value=\"20\">20</option>\n";
      print "\t\t\t<option value=\"22\">22</option>\n";
      print "\t\t\t<option value=\"24\">24</option>\n";
      print "\t\t\t<option value=\"26\">26</option>\n";
      print "\t\t\t<option value=\"28\">28</option>\n";
      print "\t\t\t<option value=\"30\">30</option>\n";
      print "\t\t\t<option value=\"32\">32</option>\n";
      print "\t\t\t<option value=\"34\">34</option>\n";
      print "\t\t\t<option value=\"36\">36</option>\n";
      print "\t\t\t<option value=\"38\">38</option>\n";
      print "\t\t\t<option value=\"40\">40</option>\n";
      print "\t\t\t</select>cm\n";
      print "\t\t</td>\n";
/*            
      //
      /////////////// waterdiepte
      //
      print "\t<tr>\n";
      print "\t\t<td style=\"text-align:left;\">waterdiepte:</td><td style=\"text-align:left;\"><input type=\"text\" size=\"4\" id=\"input_waterdiepte_text_field\" name=\"input_waterdiepte\">[m]</td>\n";
      print "\t</tr>\n"; 
      
      // lege regel
      print "\t<tr>\n";
      print "\t\t<td><p></p></td>\n";
      print "\t<tr>\n";
*/
      //
      /////////////// Submit/verzend button
      //
      print "\t<tr>\n";
      print "\t\t<td style=\"text-align:left;\"><input type=\"submit\" value=\"bereken\"></td>\n";
      
      
      //print "\t\t<th colspan=\"2\">invoer</th>";
      print "\t</tr>\n";
      
      // lege regel
      print "\t<tr>\n";
      print "\t\t<td><p></p></td>\n";
      print "\t<tr>\n";
   
      // lege regel
      print "\t<tr>\n";
      print "\t\t<td><p></p></td>\n";
      print "\t<tr>\n";
   
      // lege regel
      print "\t<tr>\n";
      print "\t\t<td><p></p></td>\n";
      print "\t<tr>\n";
      
   } // else if ($mode == "grafiek")
   
   print "</table>\n";
   
   print "<p></p>\n";
   
   if ($mode == "public")
   {
   	//
      ////////////// berichten (foute invoer) veld
      //
      print "<b id=\"error_field\">&nbsp;</b>\n";   
   }
   else if ($mode == "private") 
   {
   	//
   	///////////// verwijzing naar debug(tussenresultaten)html file
   	//
   	print "<a href=\"./debug_file_ijsberekening.html\">tussenresulaten</a>\n";
   }
   else if ($mode == "grafiek")
   {
   	//
      ////////////// berichten (foute invoer) veld
      //
      print "<b id=\"error_field\">&nbsp;</b>\n";   
      
      // lege regel
      print "\t<tr>\n";
      print "\t\t<td><p></p></td>\n";
      print "\t<tr>\n";
      
   	//
   	///////////// verwijzing naar debug(tussenresultaten)html file
   	//
   	if ($tussenresultaten_link_tonen == "ja")
   	{
   	   print "<a href=\"./debug_file_ijsberekening.html\">tussenresulaten</a>\n";
   	
         // lege regel
         print "\t<tr>\n";
         print "\t\t<td><p></p></td>\n";
         print "\t<tr>\n";
   	} 
      //
      /////////////
      //  	
   	print "<div id=\"visualization\" style=\"width: 800px; height: 400px;\"></div>"; 
   }
   
   print "</div>\n";
   print "</form>\n";
   
  

} // if ($doorgaan == true)



?>



</body>
</html>