<?php
    //Normal Test
    $searchstring = "dolor sit amet";
    $text = "Lorem ipsum dolor sit <b>amet, consetetur sadipscing elit amet";
    $answer = "Lorem ipsum [annot]dolor sit <b>amet[/annot], consetetur sadipscing elit amet";
    
    tsearch("Noraml Test", $answer, $searchstring, $text);

    //Eingefügt Test
    $searchstring = "dolor sit (1988) haha amet";
    $text = "Lorem ipsum dolor sit [year] amet, consetetur sadipscing elit";
    $answer = "Lorem ipsum [annot]dolor sit [year] amet[/annot], consetetur sadipscing elit";

    tsearch("Eingefuegt Test", $answer, $searchstring, $text);
    
    //Weniger Test
    $searchstring = "dolor sit amet";
    $text = "Lorem ipsum dolor sit [hide]blabla[/hide] amet, consetetur sadipscing elit";
    $answer = "Lorem ipsum [annot]dolor sit [hide]blabla[/hide] amet[/annot], consetetur sadipscing elit";

    tsearch("Weniger Test", $answer, $searchstring, $text);

    //Eingefügt am Ende Test
    $searchstring = "dolor sit (1988)";
    $searchstringbefor = "Lorem ipsum";
    $searchstringafter = "amet, consetetur sadipscing elit";
    $text = "Lorem ipsum dolor sit [year] amet, consetetur sadipscing elit";
    $answer = "Lorem ipsum[annot] dolor sit [year] [/annot]amet, consetetur sadipscing elit";

    tsearch("Eingefuegt am Ende Test", $answer, $searchstring, $text, $searchstringbefor, $searchstringafter);

    //Eingefügt am Anfang
    $searchstring = "(1988) amet, consetetur";
    $searchstringbefor = "Lorem ipsum dolor sit";
    $searchstringafter = "sadipscing elit";
    $text = "Lorem ipsum dolor sit [year] amet, consetetur sadipscing elit";
    $answer = "Lorem ipsum dolor sit[annot] [year] amet, consetetur [/annot]sadipscing elit";
    
    tsearch("Eingefuegt am Anfang Test", $answer, $searchstring, $text, $searchstringbefor, $searchstringafter);
    
    //Mehrfach
    $searchstring = "Lorem ipsum";
    $searchstringbefor = "Lorem ipsum dolor";
    $searchstringafter = "sit <b>amet, consetetur sadipscing elit";
    $text = "Lorem ipsum dolor Lorem ipsum sit <b>amet, consetetur sadipscing elit";
    $answer = "Lorem ipsum dolor [annot]Lorem ipsum[/annot] sit <b>amet, consetetur sadipscing elit";

    tsearch("Mehrfach Test", $answer, $searchstring, $text, $searchstringbefor, $searchstringafter);

    //Kein innen Match
    $searchstring = "bla bla";
    $searchstringbefor = "Lorem ipsum dolor";
    $searchstringafter = "sit <b>amet, consetetur sadipscing elit";
    $text = "Lorem ipsum dolor Lorem ipsum sit <b>amet, consetetur sadipscing elit";
    $answer = "Lorem ipsum dolor[annot] Lorem ipsum [/annot]sit <b>amet, consetetur sadipscing elit";
    
    tsearch("Kein innen Match Test", $answer, $searchstring, $text, $searchstringbefor, $searchstringafter);

    //Start und Ende im Wort
    $searchstring = "lor sit ame";
    $searchstringbefor = "Lorem ipsum do";
    $searchstringafter = "t, consetetur sadipscing elit";
    $text = "Lorem ipsum dolor sit <b>amet, consetetur sadipscing elit amet";
    $answer = "Lorem ipsum do[annot]lor sit <b>ame[/annot]t, consetetur sadipscing elit amet";
    
    tsearch("Start und Ende im Wort Test", $answer, $searchstring, $text, $searchstringbefor, $searchstringafter);

    //HTML Tag im Wort
    $searchstring = "dolor sit amet";
    $searchstringbefor = "Lorem ipsum";
    $searchstringafter = "consetetur sadipscing elit";
    $text = "Lorem ipsum do<b>lor sit ame</b>t, consetetur sadipscing elit amet";
    $answer = "Lorem ipsum[annot] do<b>lor sit ame</b>t, [/annot]consetetur sadipscing elit amet";
    
    tsearch("HTML Tag im Wort Test", $answer, $searchstring, $text, $searchstringbefor, $searchstringafter);

    //Other Multiple
    $searchstring = "add new parts";
    $searchstringbefor = "This is the first chapter in the main body of the text. You can change the text, rename the chapter, add new chapters, and";
    $searchstringafter = "";
    $text = "This is the first chapter in the main body of the text. You can change the text, rename the chapter, add new chapters, and add new parts.";
    $answer = "This is the first chapter in the main body of the text. You can change the text, rename the chapter, add new chapters, and [annot]add new parts[/annot].";
    
    tsearch("Other Multiple Test", $answer, $searchstring, $text, $searchstringbefor, $searchstringafter);
    
    function tsearch($name, $answer, $s, $t, $sb = '', $sa = ''){
        require_once('class.ilannotations-searchmanager.php' );
        $sm = new ILAnnotations_Searchmanager($s, $t, $sb, $sa);
        $rg = $sm->solve();
        $nt = preg_replace($rg, '$1[annot]$2[/annot]$3', $t);
        if ($answer == $nt){
            echo("<h3 style='color:white; background-color:green;'>".$name." passed</h3>");
        }else{
            echo("<h1 style='color:white; background-color:red;'>".$name." failed</h1>");   
        }
        echo("<ul>");
        echo("<li>Searchstring: ".htmlentities($s)."</li>");
        echo("<li>Search Regex: ".htmlentities($rg)."</li>");
        echo("<li>Text: ".htmlentities($t)."</li>");
        echo("<li>New Text: ".htmlentities($nt)."</li>");
        echo("</ul>");
        return($nt);
    }

    //Reduzierfunktion Testen
    require_once('class.ilannotations-search.php' );
    $test = new ILAnnotations_Search("cc xx dd", "aa bb cc dd ee ff gg", "aa bb", "ee ff gg", 0, 1, "bb", "ee");
    $s = $test->getReducedSearchs();
    
    if ($s[0]->getSearchstring() == "cc xx dd" && $s[0]->getSearchstringbefore() == "" && $s[0]->getSearchstringafter() == "ee" &&
        $s[1]->getSearchstring() == "xx dd" && $s[1]->getSearchstringbefore() == "bb" && $s[1]->getSearchstringafter() == "ee" &&
        $s[2]->getSearchstring() == "cc dd" && $s[2]->getSearchstringbefore() == "bb" && $s[2]->getSearchstringafter() == "ee" &&
        $s[3]->getSearchstring() == "cc xx" && $s[3]->getSearchstringbefore() == "bb" && $s[3]->getSearchstringafter() == "ee" &&
        $s[4]->getSearchstring() == "cc xx dd" && $s[4]->getSearchstringbefore() == "bb" && $s[4]->getSearchstringafter() == "" &&
        sizeof($s) == 5){
        echo("<h3 style='color:white; background-color:green;'>Reduzierfunktion Test passed</h3>");
    }else{
        echo("<h1 style='color:white; background-color:red;'>Reduzierfunktion Test failed</h1>");   
    }
    


?>