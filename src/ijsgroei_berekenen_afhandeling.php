<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html lang="nl">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>IJsdikte berekenen</title>
</head>
<body>



<?php

ini_set(error_reporting, 0);           // geen syntax error meldingen i.v.m. veiligheid

/* constanten */
//$EMAIL_ADRES_IJSGROEI                    = "ijsgroei@knmi.nl";   // ontvangen meting op de server wordt hiernaar toe doorgestuurd

/* initialisatie */
//$to          = "";

/* initialisatie */
$doorgaan = true;



//$locatie      = $_POST['input_locatie'];               // bv 52.110544,5.18169
$sneeuwhoogte = $_POST['input_sneeuwhoogte'];          // bv 1
$ijsdikte     = $_POST['input_ijsdikte'];              // bv 2
$waterdiepte  = $_POST['input_waterdiepte'];           // bv 3,0



//
/////////////////////////////// format checks (tegen malicious persons/software) //////////////////////////
//

// for safety remove special tags (tags are never inserted by ijsgroei application but may be directly into the webbrowser URL by a malicious person/program)
//             NB strip_tags() destroys the whole HTML behind the tags with invalid attributes 
//
// NB locatie is al gecontroleerd (zie: index_ijs.php)
$sneeuwhoogte = strip_tags($sneeuwhoogte);
$ijsdikte     = strip_tags($ijsdikte);
$waterdiepte  = strip_tags($waterdiepte);

// input strings lengte checks
//
if (strlen($sneeuwhoogte) > 3)
{
	$doorgaan = false;
	echo("data format fout in sneeuwhoogte");
} 
else 
{
	$doorgaan = true;
}

if ($doorgaan == true)
{
   if (strlen($ijsdikte) > 2)
   {
	   $doorgaan = false;
	   echo("data format fout in ijssdikte");
   } 
   else 
   {
	   $doorgaan = true;
   }
} // if ($doorgaan == true)

if ($doorgaan == true)
{
   if (strlen($waterdiepte) > 3)
   {
	   $doorgaan = false;
	   echo("data format fout in waterdiepte");
   } 
   else 
   {
	   $doorgaan = true;
   }
} // if ($doorgaan == true)


// data format per char
//
if ($doorgaan == true)
{
   if (ereg("[[:alpha:]]", $sneeuwhoogte))                           // NB [[:alpha:]] = Iedere hoofdletter of kleine letter
   {
      $doorgaan = false;
      echo("data format fout in sneeuwhoogte");
   }
} // if ($doorgaan == true)

if ($doorgaan == true)
{
   if (ereg("[[:alpha:]]", $ijsdikte))                           // NB [[:alpha:]] = Iedere hoofdletter of kleine letter
   {
      $doorgaan = false;
      echo("data format fout in ijsdikte");
   }
} // if ($doorgaan == true)

if ($doorgaan == true)
{
   if (ereg("[[:alpha:]]", $waterdiepte))                           // NB [[:alpha:]] = Iedere hoofdletter of kleine letter
   {
      $doorgaan = false;
      echo("data format fout in waterdiepte");
   }
} // if ($doorgaan == true)



if ($doorgaan == true)
{
 
   //
   ///////////////////// controle (voor normale gebruiker) //////////////
   //

   // initialisatie
   $error_code = 0;

   //if  ($error_code == 0)
   //{
   //	if (strlen($locatie) == 0)
   //	{
   //		$error_code = 1;
   //	}
   //}

   if  ($error_code == 0)
   {
   	$pos = strpos($sneeuwhoogte, "blanco");
   	if ($pos !== false)
   	{
   		$error_code = 2;
   	}
   }

   if  ($error_code == 0)
   {
   	$pos = strpos($ijsdikte, "blanco");
   	if ($pos !== false)
   	{
   		$error_code = 3;
   	}
   }

   if  ($error_code == 0)
   {
   	$pos = strpos($waterdiepte, "blanco");
   	if ($pos !== false)
   	{
   		$error_code = 4;
   	}
   }


   //
   /////////////////////// mail intern verder versturen ////////////////////////
   //
   //if ($error_code == 0)
   //{
   // 
   //}


   //
   ////////////////// print error message if error encoutered
   //
   if ($error_code != 0)
   {
   	print_error_code($error_code/*, $email_adres_voor_info*/);
   }

} // if ($doorgaan == true)




/**************************************************************************************/
/*                                                                                    */
/*                                                                                    */
/*                                                                                    */
/**************************************************************************************/
function print_error_code($error_code/*, $email_adres_voor_info*/)
{
	print gmdate("d-m-Y H:i:s ") . " UTC" . "<br>";
	print "Bericht van KNMI server: ";

	if ($error_code == 1)
	{
	   print "Meting verzonden naar KNMI server geweigerd, reden: ";
      print "locatie onbekend". "<br>";
      //print "Voor eventueel meer informatie: ". $email_adres_voor_info  . "<br>";
	}
	else if ($error_code == 2)
	{
	   print "Meting verzonden naar KNMI server geweigerd, reden: ";
      print "sneeuwhoogte niet ingevuld". "<br>";
   }
	else if ($error_code == 3)
	{
	   print "Meting verzonden naar KNMI server geweigerd, reden: ";
      print "ijsdikte niet ingevuld". "<br>";
	}
	else if ($error_code == 4)
	{
	   print "Meting verzonden naar KNMI server geweigerd, reden: ";
      print "waterdiepte niet ingevuld". "<br>";
   }
	else if ($error_code == 5)
	{
	   print "Interne fout, doorsturen email vanaf KNMI server mislukt" . "<br>";
   }
	else 
	{
      print "onbekende fout nummer". "<br>";
	}
	
} // function print_error_code($error_code, $obs_jws, $email_address_for_aanvraag_white_list)



?>



</body>
</html>
