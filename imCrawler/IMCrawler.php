<?php
include_once(dirname(__FILE__) . '/data/Data.php');
include_once(dirname(__FILE__) . '/crawlerConfig.php');
include_once(dirname(__FILE__) . '/simple_html_dom.php');
include_once(dirname(__FILE__) . '/model/Business.php');
include_once(dirname(__FILE__) . '/model/Inspection.php');
include_once(dirname(__FILE__) . '/model/Violation.php');

//CONSTANTS
define("AHS_HEALTH_INSPECTION_SEARCH_URL", "http://www12.albertahealthservices.ca/health-inspections/SearchServlet?");
define("ALPHA_QUERY_P1", "direct=alphaSearch&letter=");
define("ALPHA_QUERY_P2", "&name=&address=&municipality=7E5A1D5E-35E1-4363-B78B-FFEEDD820E02");
define("ALL_BUSINESS_OPT_URL", "http://www12.albertahealthservices.ca/health-inspections/SearchServlet?direct=showReport&ID=");
define("ALL_VIOL_END_QUERY", "&showAllViolations=true&requestedInspectLocationName=Main");

/**
 * Class IMCrawler
 * This class crawls, scrapes, and parses the AHS site and passes the data to the database
 */
class IMCrawler{

    private $alpha;
    //the typo is intentional
    private $cookie = 'disclaimerRead=The disclaimer has benn acknowledged';
    private $AHSIDArray;
    private $business;   // A Business Object to collect page data

    function __construct(){
        ini_set('user_agent', USER_AGENT);
        $this->AHSIDArray = array();

        $this->alpha = array(
            '0'=>'0-9',
            'a'=>'A',
            'b'=>'B',
            'c'=>'C',
            'd'=>'D',
            'e'=>'E',
            'f'=>'F',
            'g'=>'G',
            'h'=>'H',
            'i'=>'I',
            'j'=>'J',
            'k'=>'K',
            'l'=>'L',
            'm'=>'M',
            'n'=>'N',
            'o'=>'O',
            'p'=>'P',
            'q'=>'Q',
            'r'=>'R',
            's'=>'S',
            't'=>'T',
            'u'=>'U',
            'v'=>'V',
            'w'=>'W',
            'x'=>'X',
            'y'=>'Y',
            'z'=>'Z');
    }

    /**
     * Run a full crawl that will update the BusinessAHSID table and scrape all businesses, inspections, and violations
     * and put them in the DB.
     */
    function centralCrawlFullAssault(){
        //look for new businesses and update the business IDs in businessAHSID table
        $this->updateAHSIDs();
        //get an array of IDs from the businessAHSID table we just updated
        $result = Data::selectAHSIDs();
        //Crawl each business in our table
        $this->crawl($result);
    }

    /**
     * @param $startID ID to start crawling from
     */
    function partialCrawl($startID){
        //get an array of IDs from the businessAHSID table we just updated
        $result = Data::selectAHSIDsFrom($startID);
        //Crawl each business in our table
        $this->crawl($result);
    }

    /**
     * Start crawling the given AHSIDs
     * @param $result array of AHSIDs
     */
    function crawl($result){
        foreach ($result as $row) {
            // crawl business, load all to db: business, inspection, violation tables
            $newBusiness = $this->scrapeBusinessPage($row['ahs_id']);
            //check if business is in db and insert if not
            $bResult = Data::selectBusinessByAHSID($newBusiness->getAHSID());
            if(empty($bResult)){
                Data::insertBusiness($newBusiness->getAHSID(),
                    $newBusiness->getName(),
                    $newBusiness->getType(),
                    $newBusiness->getAddress(),
                    $newBusiness->getPhone(),
                    $newBusiness->getPermitHolder());
            }else{
                echo 'business: ' . $newBusiness->getName() . ' already in DB \n';
            }
            //Loop through inspections adding them and the violations to the DB
            foreach ($newBusiness->getInspectionsArray() as $inspection) {
                //format date to date object
                date_default_timezone_set('America/Denver');
                $date = new DateTime($inspection->getDate());
                $newDate = $date->format('Y-m-d');
                //query inspection table to check if it is already there
                $inspectionIsHere = Data::selectInspection($newBusiness->getAHSID(), $newDate);
                if(!$inspectionIsHere){
                    Data::insertInspection($newBusiness->getAHSID(),
                        $newDate,
                        $inspection->getType(),
                        $inspection->getAction());
                    //insert the violations
                    foreach ($inspection->getViolations() as $violation) {
                        Data::insertViolation($newBusiness->getAHSID(),
                            $violation->getName(),
                            $violation->getRequirement(),
                            $violation->getComment(),
                            $violation->getCritical());
                    }
                }else{
                    echo 'Inspection ' . $newBusiness->getName() . '/' . $inspection->getDate() . ' already in db \n';
                }
            }
        }
    }
    /**
     * Goes through each alpha page and scrapes the AHS IDs
     */
    function updateAHSIDs(){
        //scrape alpha pages and load into db
        foreach ($this->alpha as $key => $letter) {
            $this->scrapeAlphaPage($letter);
        }
    }

    /**
     * Grabs the contents of the URL passed
     * @param string $url
     * @return simple_html_dom
     */
    function getPage($url){
        //use curl since we need to set a cookie
        $c = curl_init($url);
        curl_setopt($c, CURLOPT_VERBOSE, 1);
        curl_setopt($c, CURLOPT_COOKIE, $this->cookie);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        $page = curl_exec($c);
        curl_close($c);
        $c = null;
        $html = str_get_html($page); //simplehtmldom method
        return $html;
    }

    /**
     * Use the simplehtmldom object to find the AHS IDs and store them in an array
     * @param simplehtmldom $html
     * @return array AHS IDs
     */
    function getAHSIDs($html){
        $idArray = array();
        //the html is pretty hacky, this is the safest way to find the right tag
        foreach($html->find('form[name=searchResults]') as $element){
            foreach ($element->find('a') as $a) {
                $ID = $this->findID($a);
                array_push($idArray, $ID[1]);
            }
        }
        return $idArray;
    }

    function findID($anchor){
        preg_match("~'(.*)'~", $anchor, $ID);
        return $ID;
    }

    /**
     * Parse the html of the business page and make a Business object out of it
     * @param simplehtmldom $htmlDom
     * @param string $busID business id to store in Business object
     * @return Business
     */
    function parseBusinessPage($htmlDom, $busID){
        $html = $htmlDom;
        $id = $busID;
        $name = '';
        $type = '';
        $tele = '';
        $address = '';
        $permHldr = '';
        $i = 0;
        $key = '';
        /*
         * The html is really dicey here so we have no choice but to do this the hard way
         * basically we loop around a few times looking for different tables and when
         * we have the right ones we loop through the td tags to grab the important parts
         */

        foreach($html->find('table[width=50%]') as $element){
            foreach ($element->find('td') as $td) {
                if ($i == 1){
                    foreach ($td->find('font') as $bText) {
                        $key = $bText->innertext;
                    }
                }else{
                    $key = $td->innertext;
                }
                /**
                 * switch to load data to business object
                 *  1 => restaurant name
                 *  3 => type
                 *  5 => telephone
                 *  7 => address
                 *  9 => permit holder
                 */
                switch($i){
                    case 1:
                        $name = $key;
                        break;
                    case 3:
                        $type = $key;
                        break;
                    case 5:
                        $tele = $key;
                        break;
                    case 7:
                        $address = $key;
                        break;
                    case 9:
                        $permHldr = $key;
                        break;
                }
                $i++;
            }
        }
        $newBusiness = new Business($id, $name, $type, $address, $tele, $permHldr);
        return $newBusiness;
    }

    /**
     *
     * NOTE There are unneeded foreachs in here. Also I think we can always
     * go to the all violations page. TODO fix.
     * Parse the html of the business page and make an Inspection object out of it
     * @param simplehtmldom $html
     * @return mixed
     */
    function parseInspections($html){
        $iArray = array();
        $iCount = 0;
        $allOptUrl = ALL_BUSINESS_OPT_URL;
        $newInspection = null;
        $newViolation = null;
        $iDate = null;
        $iType = null;
        $iAction = null;
        $trCount = 0;
        $tblCount = 0;

        /*
         * More complicated html. Find the table then loop through the tr and td tags respectively
         */
        foreach ($html->find('table[class=tableBorderThin]') as $iTable) {
            //there can be two of these tables but we only ever want the first one
            if($tblCount == 0){
                foreach ($iTable->find('tr') as $tr) {
                    $iCount = 0;
                    foreach ($tr->find('td') as $td) {
                        $iCount++;
                        //always in the same order: date, type, action
                        switch($iCount){
                            case 1:
                                //the page looks different depending on the number of inspections so
                                //we check to see which one we have
                                if($trCount == 1){
                                    foreach ($td->find('font') as $f) {
                                        $td = $f;
                                    }
                                }elseif($trCount >  1){
                                    foreach ($td->find('a') as $a) {
                                        $td = $a;
                                    }
                                }
                                $iDate = $td->innertext;
                                strip_tags($iDate);
                                break;
                            case 2:
                                $iType = $td->innertext;
                                break;
                            case 3:
                                $iAction = $td->innertext;
                                break;
                        }
                    }
                    // >0 if we actually got something
                    if($iCount > 0){
                        $newInspection = new Inspection($iDate, $iType, $iAction);
                        array_push($iArray, $newInspection);
                    }
                    $trCount++;
                }
            }
            $tblCount++;
        }
        return $iArray;
    }

    /**
     * @param $bID
     * @return array
     * note: pass in number of inspections to decide on using showallopt url or pre-
     *  existing html object, do by counting iArray, do all this on outside.
     */
    function parseViolations($html, $inspectionCount){
        $vArray = array();
        $vCount = 0;
        $trCount = 0;
        $tdCount = 0;
        $date = null;
        $comment = null;
        $critical = null;
        $name = null;
        $requirement = null;
        /*
         * Get the second table with class=tableBorderThin then loop through the tr and td
         * tags respectively grabbing the relevant data
         */
        foreach ($html->find('table[class=tableBorderThin]') as $vTable) {
            if ($vCount == 1) {
                foreach ($vTable->find('tr') as $tr) {
                    $tdCount = 0;
                    //skip the first row, just titles
                    if ($trCount > 0) {
                        //grab each td tag
                        foreach ($tr->find('td') as $td) {
                            $tdCount++;
                            /*
                             *
                             *I think this can be cleaned up
                             *
                             */
                            switch ($tdCount) {
                                case 1:
                                    if($inspectionCount > 1){
                                        $date = $td->innertext;
                                    }elseif($inspectionCount == 1){
                                        $date = null;
                                        $name = $td->innertext;
                                    }
                                    break;
                                case 2:
                                    if($inspectionCount > 1){
                                        $name = $td->innertext;
                                    }elseif($inspectionCount == 1){
                                        $requirement = $td->innertext;
                                    }
                                    break;
                                case 3:
                                    if($inspectionCount > 1){
                                        $requirement = $td->innertext;
                                    }elseif($inspectionCount == 1){
                                        $comment = $td->innertext;
                                    }
                                    break;
                                case 4:
                                    if($inspectionCount > 1){
                                        $comment = $td->innertext;

                                    }elseif($inspectionCount == 1){
                                        $critical = $td->innertext;
                                    }
                                    break;
                                case 5:
                                    if($inspectionCount > 1){
                                        $critical = $td->innertext;
                                    }
                                    break;
                            }
                        }
                        //change critical to boolean
                        if(isset($critical)){
                            $date = substr($date, 0, -6);
                            $comment = substr($comment, 0, -6);
                            $name = substr($name, 0, -6);
                            $requirement = substr($requirement, 0, -6);
                            $critical = substr($critical, 0, -6);
                            if($critical == 'Yes'){
                                $critical = 1;
                            }else{
                                $critical = 0;
                            }
                            $newViolation = new Violation($date, $name, $requirement, $comment, $critical);
                            array_push($vArray, $newViolation);
                        }
                    }
                    $trCount++;
                }
            }
            $vCount++;
        }
        return $vArray;
    }

    /**
     * Goes to an alpha page (eg. page.php?letter=A) and get all of the AHS IDs
     * @param string $letter
     */
    function scrapeAlphaPage($letter){
        //build the URL for this letter then grab the html
        $url = AHS_HEALTH_INSPECTION_SEARCH_URL . ALPHA_QUERY_P1 . $letter . ALPHA_QUERY_P2;
        $html = $this->getPage($url);
        //scrape the AHS IDs from the page
        $AHSIDs = $this->getAHSIDs($html);
        $this->AHSIDArray = $AHSIDs;
        //load ids to db
        Data::insertAHSIDs($AHSIDs, $letter);
        //clear $html to avoid memory leaks
        $html->clear();
        unset($html);
        $html = null;
    }

    /**
     * Goes through the business page and creates objects representing the business and it's inspections
     * and violations
     * @param string $AHSID
     * @return Business
     */
    function scrapeBusinessPage($AHSID){
        $url = ALL_BUSINESS_OPT_URL . $AHSID;
        //get simplehtmldom object
        $html = $this->getPage($url);
        //get a Business object form the page
        $business = $this->parseBusinessPage($html, $AHSID);
        //get an Inspection object from business page
        $inspections = $this->parseInspections($html);
        //If there was more than one inspection we will add another query string to show all of the violations
        if(count($inspections) > 1){
            //clear $html to avoid memory leaks
            $html->clear();
            unset($html);
            $html = null;
            //get url that shows all violations
            $vURL = ALL_BUSINESS_OPT_URL . $AHSID . ALL_VIOL_END_QUERY;
            $html = $this->getPage($vURL);
        }
        //get Violation object from business page
        $violations = $this->parseViolations($html, count($inspections));
        // sync violations with inspections
        foreach ($inspections as $inspection) {
            $subViolArray = array();
            $iDate = $inspection->getDate();
            if(!empty($violations)){
                foreach ($violations as $violation) {
                    //get violation date and strip any tags
                    $vDate = strip_tags($violation->getDate());
                    if($vDate == $iDate || $vDate == ""){
                        array_push($subViolArray, $violation);
                    }
                }
                //place violation array in the inspection object
                $inspection->setViolations($subViolArray);
            }
        }
        // update Business object's inspection array
        $business->setInspectionsArray($inspections);
        //clear $html to avoid memory leaks
        $html->clear();
        unset($html);
        $html = null;
        return $business;
    }
}
?>