<?php

/**
 * Class ILAnnotations_Search
 * A single search
 */
class ILAnnotations_Search {

    /**
     * @var string the search string
     */
    private $searchstring;
    /**
     * @var string the added search string before the original search string
     */
    private $searchstringbefore;
    /**
     * @var string the added search string after the original search string
     */
    private $searchstringafter;
    /**
     * @var int the position the last word was deleted (only words after that get deleted the next time - prevents to have the same search string multiple times)
     */
    private $deletepos;
    /**
     * @var int how may words where added to the search string
     */
    private $expandpos;
    /**
     * @var string the source text to search in
     */
    private $text;
    /**
     * @var string the text before the search string
     */
    private $textbefore;
    /**
     * @var string the text after the search string
     */
    private $textafter;
    /**
     * @var int if words at the beginning or the end of the search string where deleted (2=yes and nothing around added, 1=yes but words where added)
     */
    private $borderworddeleted;

    /**
     * @param string $searchstring the search string
     * @param string $text the source text to search in
     * @param string $textbefore the text before the search string
     * @param string $textafter the text after the search string
     * @param int $deletepos the position the last word was deleted
     * @param int $expandpos how may words where added to the search string
     * @param string $searchstringbefore the added search string before the original search string
     * @param string $searchstringafter the added search string after the original search string
     * @param int $borderworddeleted if words at the beginning or the end of the search string where deleted
     */
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

    /**
     * Test if the search has a result
     * @return int 1 = one result; 2 = at least two results
     */
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

    /**
     * Returns searches with less words in the search string
     * Every word gets deleted in one search string
     * @return array
     */
    function getReducedSearchs(){
        $out = array();

        //split words
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
            //delete every word once and create a new search
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
                //determine if a word at the beginning or end of the search string was deleted
                if($i == 0 || $i+1 == $lt){
                    $bw = 2;
                }
                $n = new ILAnnotations_Search(implode(" ", $ai), $this->text, $this->textbefore, $this->textafter,  $i, $this->expandpos, implode(" ", $abi), implode(" ", $aai), $bw);
                $out[] = $n;
            }
        }
        return($out);
    }

    /**
     * Returns a search with one word added at the beginning and the end
     * @return ILAnnotations_Search|null
     */
    function getExpandetSearch(){
        $ok = false;
        //Add word before
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
        //Add word after
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
            //set borderwoddeleted to 1 if necessary because the string was expanded and is now big enough
            if($bw == 2){
                $bw = 1;
            }
            return(new ILAnnotations_Search($this->searchstring, $this->text, $this->textbefore, $this->textafter,  0, $this->expandpos, $b, $a, $bw));
        }else{
            return(null);   
        }
    }

    /**
     * Returns the regular expression
     * @return string
     */
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

    /**
     * if words at the beginning or the end of the search string where deleted
     * (2=yes and nothing around added, 1=yes but words where added)
     * @return int
     */
    public function getBorderworddeleted(){
        return($this->borderworddeleted);
    }

    /**
     * the search string
     * @return string
     */
    public function getSearchstring(){
        return $this->searchstring;
    }

    /**
     * the added search string before the original search string
     * @return string
     */
    public function getSearchstringbefore(){
        return $this->searchstringbefore;
    }

    /**
     * the added search string after the original search string
     * @return string
     */
    public function getSearchstringafter(){
        return $this->searchstringafter;
    }
}