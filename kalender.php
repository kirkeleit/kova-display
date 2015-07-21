<?php
  header("Content-Type: text/html; charset=utf-8");
  header("Cache-Control: no-cache, must-revalidate");
  header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

  /* http://stackoverflow.com/questions/4757061/which-ics-parser-written-in-php-is-good */
  function icsToArray() {
    $icsFile = file_get_contents("http://kova.no/Public/iCalendar.ashx?Organization=x&Key=y");

    $icsData = explode("BEGIN:", $icsFile);

    foreach($icsData as $key => $value) {
        $icsDatesMeta[$key] = explode("\n", $value);
    }

    foreach($icsDatesMeta as $key => $value) {
        foreach($value as $subKey => $subValue) {
            if ($subValue != "") {
                if ($key != 0 && $subKey == 0) {
                    $icsDates[$key]["BEGIN"] = preg_replace( "/\r|\n/", "", $subValue);
                } else {
                    $subValueArr = explode(":", $subValue, 2);
                    $icsDates[$key][$subValueArr[0]] = preg_replace( "/\r|\n/", "", $subValueArr[1]);
                    if ($subValueArr[0] == "SUMMARY") {
                      $temp = explode(": ", $subValueArr[1],2);
                      $icsDates[$key][$subValueArr[0]] = $temp[1];
                      $icsDates[$key]['ETYPE'] = $temp[0];
                    }
                }
            }
        }
    }

    foreach($icsDates as $key1 => $value1) {
      if ($value1['BEGIN'] == "VEVENT") {
        $start = strtotime($value1['DTSTART']);
        if ($start > mktime(0,0,0,date("n"),date("j"),date("Y"))) {
          $data[$key1] = $value1;
        }
      }
    }

    return $data;
  }

  $kalender = icsToArray();
?>
<html>
<head>
  <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
  <meta http-equiv="CACHE-CONTROL" content="NO-CACHE" />
  <meta http-equiv="refresh" content="3600" />
  <title>KOVA DS - KALENDER</title>
  <style type="text/css">
    body {
      padding: 0px;
      margin: 0px;
      font-family: Verdana, sans-serif;
      overflow: hidden;
    }

    #DIVSide {
      position: absolute;
      width: 1280px;
      height: 1024px;
      background-image: url(logo.jpg);
      background-repeat: repeat-x;
      background-color: white;
      background-position: center center;
      overflow: hidden;
    }

    .DIVDatoTittel {
      position: static;
      display: block;
      background-color: red;
      color: white;
      font-weight: bold;
      text-align: center;
      font-size: 24px;
      text-transform: uppercase;
    }

    TABLE#Aktiviteter {
      position: absolute;
      top: 5px;
      left: 5px;
      width: 1270px;
      height: 914px;
    }

    TABLE#Aktiviteter TR {
      background-color: rgba(200,200,200,0.7);
      vertical-align: top;
    }

    TABLE#Aktiviteter TD {
      padding: 6px;
    }

    TABLE#Aktiviteter TD.Dato {
      padding: 0px;
      width: 130px;
      height: 110px;
      border: 2px solid red;
      font-size: 3.2em;
      text-align: center;
      font-weight: none;
    }

    TABLE#Aktiviteter TD.Tid {
      width: 120px;
      text-align: center;
      font-size: 1.2em;
    }

    TABLE#Aktiviteter TD.EType {
      width: 100px;
      text-align: center;
    }

    SPAN.StartTid {
      font-weight: bold;
      font-size: 1.4em;
    }

    SPAN.Tittel {
      font-size: 1.6em;
      font-weight: bold;
    }

    SPAN.AntallDeltakere {
      font-size: 1.6em;
      font-weight: bold;
    }

    #BoksBeredskap {
      position: absolute;
      left: 5px;
      bottom: 5px;
      width: 915px;
      height: 80px;
      background-color: rgba(200,200,200,0.7);
      padding: 5px;
      text-align: center;
      font-weight: bold;
      font-size: 1.4em;
      font-family: "Courier New",Courier;
      visibility: hidden;
    }

    #BoksDato {
      position: absolute;
      left: 5px;
      bottom: 5px;
      width: 915px;
      height: 80px;
      background-color: rgba(200,200,200,0.7);
      padding: 5px;
      text-align: center;
      font-weight: bold;
      font-size: 3em;
      font-family: "Courier New",Courier;
    }

    #BoksKlokke {
      position: absolute;
      right: 5px;
      bottom: 5px;
      width: 320px;
      height: 70px;
      background-color: rgba(190,190,190,0.7);
      padding: 10px;
      text-align: center;
      font-weight: bold;
      font-size: 3.8em;
      font-family: "Courier New",Courier;
    }
  </style>
  <script language="JavaScript">
    function OppdaterKlokke() {
      var Maaneder = new Array('januar','februar','mars','april','mai','juni','juli','august','september','oktober','november','desember');
      var Klokke = new Date();
      var Time = Klokke.getHours();
      if (Time < 10) { Time = "0"+Time; }
      var Minutt = Klokke.getMinutes();
      if (Minutt < 10) { Minutt = "0"+Minutt; }
      var Sekund = Klokke.getSeconds();
      if (Sekund < 10) { Sekund = "0"+Sekund; }
      var Aar = Klokke.getFullYear();
      var Dag = Klokke.getDate();
      var Maaned = Maaneder[Klokke.getMonth()];
      document.getElementById("BoksKlokke").innerHTML = Time+":"+Minutt+":"+Sekund;
      document.getElementById("BoksDato").innerHTML = Dag+". "+Maaned+" "+Aar
    }
  </script>
</head>
<body>

<div id="DIVSide">
<table id="Aktiviteter">
<?php
  foreach (array_slice($kalender,0,8) as $key => $aktivitet) {
    $start = strtotime($aktivitet['DTSTART']);
    $slutt = strtotime($aktivitet['DTEND']);
    $varighet = (($slutt - $start)/60)/60;
    $deltakere = explode('\n',substr($aktivitet['DESCRIPTION'],strpos($aktivitet['DESCRIPTION'],"Deltakere (oppdatert")+43));
    foreach($deltakere as $key => $deltaker) {
      if (strpos($deltaker,"- åpen -") > 0) {
        unset($deltakere[$key]);
      }
    }
?>
  <tr>
    <td class="Dato"><div class="DIVDatoTittel"><?php echo date("M",$start); ?></div><?php echo date("d",$start); ?></td>
    <td class="Tid"><span class="StartTid"><?php echo date("H:i",$start); ?></span><br /><?php if ($varighet == 1) { echo $varighet." time"; } else { echo $varighet." timer"; } ?></td>
    <td><span class="Tittel"><?php echo $aktivitet['SUMMARY']; ?></span><br /><?php if (strlen($aktivitet['DESCRIPTION']) > 0) { echo substr($aktivitet['DESCRIPTION'],0,100)."..."; } else { echo "Ingen beskrivelse."; } ?></td>
    <td class="EType"><span class="AntallDeltakere"><?php echo sizeof($deltakere); ?></span><br /><?php echo $aktivitet['ETYPE']; ?></td>
  </tr>
<?php
  }
?>
</table>
</div>
<div id="BoksBeredskap">#BEREDSKAP#</div>
<div id="BoksDato">#DATO#</DIV>
<div id="BoksKlokke">#KLOKKE#</div>
<script>
  OppdaterKlokke();
  setInterval("OppdaterKlokke()",500);
</script>
</body>
</html>
