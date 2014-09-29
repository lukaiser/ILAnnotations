<?php
require_once('class.ilannotations-search.php' );

/**
 * Class ILAnnotations_Searchmanager
 * Searches for the annotated text in the source
 */
class ILAnnotations_Searchmanager {

    /**
     * @var string the search string
     */
    private $searchstring;
    /**
     * @var string the source string to search in
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
     * @var ILAnnotations_Search the search that found a result
     */
    private $finalsearch;

    /**
     * @param string $searchstring the search string
     * @param string $text  the source string to search in
     * @param string $textbefore the text before the search string
     * @param string $textafter the text after the search string
     */
    function __construct($searchstring, $text, $textbefore='', $textafter='') {
        $this->searchstring = $searchstring;
        $this->text = $text;
        $this->textbefore = $textbefore;
        $this->textafter = $textafter;
    }

    /**
     * Find the right search
     * @param null $s searches currently considered for intern use
     * @return string the regex string
     */
    function solve($s=null){
        //if no searches are provided create one
        if(!is_array($s)){
            $s = array();
            $s[] = new ILAnnotations_Search($this->searchstring, $this->text, $this->textbefore, $this->textafter);
        }
        //searches that are ok
        $ok = array();
        //searches no result is found
        $notfound = array();
        //searches multiple results are found in the source
        $multiplefound = array();
        //check all searches and add the the right array
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
            //If one search is ok and no word was removed from the beginning or end of the search string.
            $this->finalsearch = $ok[0];
            return ($ok[0]->getRexString());
        }else if($oks == 1 && $ok[0]->getBorderworddeleted()==2){
            //expand the search if one is ok but the words at the begining or end are removed
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
            //expand the search if multiple results are found or more then one search if ok
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
            //reduce the search if nothing is found
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

    /**
     * Returns the regular expression of the right search
     * @return null
     */
    function getRegex(){
        if(!is_null($this->finalsearch)){
            return($this->finalsearch->getRexString());
        }
        return null;
    }
    
}