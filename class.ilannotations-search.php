<?php

class ILAnnotations_Search {
    
    private $searchstring;
    private $searchstringbefore;
    private $searchstringafter;
    private $deletepos;
    private $expandpos;
    private $text;
    private $textbefore;
    private $textafter;
    private $borderworddeleted;
    
    function __construct($searchstring, $text, $textbefore='', $textafter='', $deletepos=0, $expandpos=0, $searchstringbefore='', $searchstringafter='', $borderworddeleted = 0) {
        $this->searchstring = $searchstring;
        $this->searchstringbefore = $searchstringbefore;
        $this->searchstringafter = $searchstringafter;
        $this->deletepos = $deletepos;
        $this->expandpos = $expandpos;
        $this->text = $text;
        $this->textbefore = $textbefore;
        $this->textafter = $textafter;
        $this->borderworddeleted = $borderworddeleted;
    }
    
    function hasResult(){
        $r = preg_match_all($this->getRexString(), $this->text, $matches, PREG_OFFSET_CAPTURE+PREG_SET_ORDER );
        //Test if lazzysearch had the right one or if multiple are possilbe: TEST Other Multiple
        if($r == 1){
            if(preg_match_all($this->getRexString(), $this->text, $matches, PREG_OFFSET_CAPTURE+PREG_SET_ORDER, $matches[0][0][1]+1 ) == 0){
                return(1);    
            }else{
                return(2);
            }
        }
        return($r);
    }
    
    function getReducedSearchs(){
        $out = array();
        
        $a = preg_split ( '/\W+/', $this->searchstring);
        $a = array_values(array_filter($a, 'strlen'));
        if($this->searchstringbefore != ''){
            $ab = preg_split ( '/\W+/', $this->searchstringbefore);
            
        }else{
            $ab = array();
        }
        if($this->searchstringafter != ''){
            $aa = preg_split ( '/\W+/', $this->searchstringafter);
        }else{
            $aa = array();
        }
        
        $l = sizeof($a);
        $lb = sizeof($ab);
        $la = sizeof($aa);
        $lt = $l+$lb+$la;
            
        if($lt > 0){
            for ($i=$this->deletepos; $i < $lt; $i++){
                $ai = $a;
                $abi = $ab;
                $aai = $aa;

                if($i<$lb){
                    unset($abi[$i]);
                }elseif($i-$lb<$l){
                    unset($ai[$i-$lb]);
                }else{
                    unset($aai[$i-$lb-$l]);
                }
                
                $bw = $this->borderworddeleted;
                if($i == 0 || $i+1 == $lt){
                    $bw = 2;
                }
                $n = new ILAnnotations_Search(implode(" ", $ai), $this->text, $this->textbefore, $this->textafter,  $i, $this->expandpos, implode(" ", $abi), implode(" ", $aai), $bw);
                $out[] = $n;
            }
        }
        return($out);
    }
    
    function getExpandetSearch(){
        $ok = false;
        $b = $this->searchstringbefore;
        if($this->textbefore != ''){
            $ab = preg_split ( '/\W+/', $this->textbefore);
            if(sizeof($ab)-$this->expandpos >= 0){
                if($b == ""){
                    $b = $ab[sizeof($ab)-$this->expandpos-1];
                }else{
                    $b = $ab[sizeof($ab)-$this->expandpos-1]." ".$b;
                }
                $ok = true;
            }
        }
        $a = $this->searchstringafter;
        if($this->textafter != ''){
            $aa = preg_split ( '/\W+/', $this->textafter);
            if(sizeof($aa) > $this->expandpos){
                if($a == ""){
                    $a = $aa[$this->expandpos];
                }else{
                    $a = $a." ".$aa[$this->expandpos];
                }
                $ok = true;
            }
        }
        if($ok){
            $this->expandpos ++;
            $bw = $this->borderworddeleted;
            if($bw == 2){
                $bw = 1;
            }
            return(new ILAnnotations_Search($this->searchstring, $this->text, $this->textbefore, $this->textafter,  0, $this->expandpos, $b, $a, $bw));
        }else{
            return(null);   
        }
    }
    
    public function getRexString(){
        if($this->searchstring != ""){
            $s = preg_replace ('/\W+/', '(?:.|\n)*?', $this->searchstring);
            if($this->borderworddeleted == 1){
                $s = '(?:.|\n)*?'.$s.'(?:.|\n)*?';
            }
        }else{
            $s = '(?:.|\n)*?';
        }
        if($this->searchstringbefore != ''){
            $sb = preg_replace ('/\W+/', '(?:.|\n)*?', $this->searchstringbefore);
            if($this->borderworddeleted != 1){
                $sb = $sb.'(?:.|\n)*?';
            }
        }else{
            $sb = "";    
        }
        if($this->searchstringafter != ''){
            $sa = preg_replace ('/\W+/', '(?:.|\n)*?', $this->searchstringafter);
            if($this->searchstring != "" && $this->borderworddeleted != 1){
                $sa = '(?:.|\n)*?'.$sa;
            }
        }else{
            $sa = "";    
        }
        $s = '/('.$sb.')('.$s.')('.$sa.')/';
        return($s);
    }
    
    public function getBorderworddeleted(){
        return($this->borderworddeleted);
    }
    
    public function getSearchstring(){
        return $this->searchstring;
    }
    public function getSearchstringbefore(){
        return $this->searchstringbefore;
    }
    public function getSearchstringafter(){
        return $this->searchstringafter;
    }
}