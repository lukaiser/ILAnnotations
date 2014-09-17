<?php
require_once('class.ilannotations-search.php' );
class ILAnnotations_Searchmanager {
    
    private $searchstring;
    private $text;
    private $textbefore;
    private $textafter;
    private $finalsearch;
    
    function __construct($searchstring, $text, $textbefore='', $textafter='') {
        $this->searchstring = $searchstring;
        $this->text = $text;
        $this->textbefore = $textbefore;
        $this->textafter = $textafter;
    }
    
    function solve($s=null){
        if(!is_array($s)){
            $s = array();
            $s[] = new ILAnnotations_Search($this->searchstring, $this->text, $this->textbefore, $this->textafter);
        }
        
        $ok = array();
        $notfound = array();
        $multiplefound = array();
        foreach ($s as $ss){
            $n = $ss->hasResult();
            if($n == 1){
                $ok[] = $ss;
            }else if($n == 0){
                $notfound[] = $ss;
            }else{
                $multiplefound[] = $ss;
            }
        }
        
        $oks = sizeof($ok);
        $notfounds = sizeof($notfound);
        $multiplefounds = sizeof($multiplefound);
        if($oks == 1 && $ok[0]->getBorderworddeleted()<2){
            $this->finalsearch = $ok[0];
            return ($ok[0]->getRexString());
        }else if($oks == 1 && $ok[0]->getBorderworddeleted()==2){
            $ns = array();
            $nss = $ok[0]->getExpandetSearch();
            if(!is_null($nss)){
                $ns[] = $nss;
            }
            if(sizeof($ns)>0){
                return($this->solve($ns));
            }else{
                return "";
            }
        }else if(($oks == 0 && $multiplefounds > 0) || $oks > 1){
            $ns = array();
            foreach($ok as $ss){
                $nss = $ss->getExpandetSearch();
                if(!is_null($nss)){
                    $ns[] = $nss;
                }
            }
            foreach($multiplefound as $ss){
                $nss = $ss->getExpandetSearch();
                if(!is_null($nss)){
                    $ns[] = $nss;
                }
            }
            if(sizeof($ns)>0){
                return($this->solve($ns));
            }else{
                return "";
            }
        }else if($oks == 0 && $multiplefounds == 0 && $notfounds > 0 ){
            $ns = array();
            foreach($notfound as $ss){
                $ns = array_merge($ns, $ss->getReducedSearchs());
            }
            if(sizeof($ns)>0){
                return($this->solve($ns));
            }else{
                return "";
            }
        }else{
            return "";    
        }
    }
    
    function getRegex(){
        if(!is_null($this->finalsearch)){
            return($this->finalsearch->getRexString());
        }
        return null;
    }
    
}