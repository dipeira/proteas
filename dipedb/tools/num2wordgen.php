<?php

/*
Copyright 2007-2008 Brenton Fletcher. http://bloople.net/num2text
You can use this freely and modify it however you want.
*/
function convertNumber($num)
{
   list($num, $dec) = explode(".", $num);

   $output = "";

   if($num{0} == "-")
   {
      $output = "negative ";
      $num = ltrim($num, "-");
   }
   else if($num{0} == "+")
   {
      $output = "positive ";
      $num = ltrim($num, "+");
   }
   
   if($num{0} == "0")
   {
      $output .= "zero";
   }
   else
   {
      $num = str_pad($num, 36, "0", STR_PAD_LEFT);
      $group = rtrim(chunk_split($num, 3, " "), " ");
      $groups = explode(" ", $group);

      $groups2 = array();
      foreach($groups as $g) $groups2[] = convertThreeDigit($g{0}, $g{1}, $g{2});

      for($z = 0; $z < count($groups2); $z++)
      {
         if($groups2[$z] != "")
         {
            $output .= $groups2[$z].convertGroup(11 - $z).($z < 11 && !array_search('', array_slice($groups2, $z + 1, -1))
             && $groups2[11] != '' && $groups[11]{0} == '0' ? " and " : ", ");
         }
      }

      $output = rtrim($output, ", ");
   }

   if($dec > 0)
   {
      $output .= " point";
      for($i = 0; $i < strlen($dec); $i++) $output .= " ".convertDigit($dec{$i});
   }

   return $output;
}

function convertGroup($index)
{
   switch($index)
   {
      case 11: return " decillion";
      case 10: return " nonillion";
      case 9: return " octillion";
      case 8: return " septillion";
      case 7: return " sextillion";
      case 6: return " quintrillion";
      case 5: return " quadrillion";
      case 4: return " trillion";
      case 3: return " billion";
      case 2: return " million";
      case 1: return " thousand";
      case 0: return "";
   }
}

function convertThreeDigit($dig1, $dig2, $dig3)
{
   $output = "";

   if($dig1 == "0" && $dig2 == "0" && $dig3 == "0") return "";

   /*
   if($dig1 != "0")
   {
      $output .= convertDigit($dig1)." ������";
      if($dig2 != "0" || $dig3 != "0") $output .= " and ";
   }
   */
   // 30-01-2013 added three digits in greek
   if($dig1 != "0")
   {
    switch($dig1)
        {
            case "1": 
                $output =  "������ ";
                break;
            case "2": 
                $output =  "��������� ";
                break;
            case "3": 
                $output =  "���������� ";
                break;
            case "4": 
                $output =  "����������� ";
                break;
            case "5": 
                $output =  "����������� ";
                break;
            case "6": 
                $output =  "��������� ";
                break;
            case "7": 
                $output =  "���������� ";
                break;
            case "8": 
                $output =  "���������� ";
                break;
            case "9": 
                $output =  "����������� ";
                break;
        }
        if($dig2 != "0" || $dig3 != "0") $output .= "";
   }

   if($dig2 != "0") $output .= convertTwoDigit($dig2, $dig3);
   else if($dig3 != "0") $output .= convertDigit($dig3);

   return $output;
}

function convertTwoDigit($dig1, $dig2)
{
   if($dig2 == "0")
   {
      switch($dig1)
      {
         case "1": return "����";
         case "2": return "������";
         case "3": return "�������";
         case "4": return "�������";
         case "5": return "�������";
         case "6": return "������";
         case "7": return "���������";
         case "8": return "�������";
         case "9": return "��������";
      }
   }
   else if($dig1 == "1")
   {
      switch($dig2)
      {
         case "1": return "������";
         case "2": return "������";
         case "3": return "���������";
         case "4": return "������������";
         case "5": return "���������";
         case "6": return "�������";
         case "7": return "��������";
         case "8": return "��������";
         case "9": return "���������";
      }
   }
   else
   {
      $temp = convertDigit($dig2);
      switch($dig1)
      {
         case "2": return "������$temp";
         case "3": return "�������$temp";
         case "4": return "�������$temp";
         case "5": return "�������$temp";
         case "6": return "������$temp";
         case "7": return "���������$temp";
         case "8": return "�������$temp";
         case "9": return "��������$temp";
      }
   }
}
      
function convertDigit($digit)
{
   switch($digit)
   {
      case "0": return "�����";
      case "1": return "����";
      case "2": return "���";
      case "3": return "�����";
      case "4": return "��������";
      case "5": return "�����";
      case "6": return "���";
      case "7": return "����";
      case "8": return "����";
      case "9": return "�����";
   }
}
?>