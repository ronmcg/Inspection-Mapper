<?php
include_once(dirname(__FILE__) . '/../crawlerConfig.php');

/**
 * Class Data
 * Used to access the database
 */
class Data {

    /***
     * Creates a PDO connection or dies trying
     * @return PDO connection
     */
    private static function connect(){
        try{
            $pdo = new PDO(DB_CONN_STRING, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        }
        catch(PDOException $pdoe){
            die($pdoe->getMessage());
        }
    }

    /******************************START businessAHSID**********************************************/
    /***
     * Takes an array of AHS ID's and inserts them into the DB if they are not already there
     * @param $idArray Array of AHS ID's scraped from the alpha page
     * @param $letter The current letter being scraped
     */
    public static function insertAHSIDs($idArray, $letter){
        $pdo = Data::connect();
        foreach ($idArray as $id) {
            $sql = $pdo->prepare('SELECT ahs_id FROM BusinessAHSID WHERE ahs_id = :theID');
            $sql->bindValue(':theID', $id);
            $sql->execute();
            $result = $sql->fetchAll();
            if(empty($result)){
                $sql = $pdo->prepare('INSERT INTO BusinessAHSID (ahs_id, letter) VALUES(:ahsID, :letter)');
                $sql->bindValue(':ahsID', $id);
                $sql->bindValue(':letter', $letter);
                $sql->execute();
            }
        }
        $pdo = null;
    }

    /***
     * @return array All of the AHS IDs for the businesses in the DB
     */
    public static function selectAHSIDs(){
        $pdo = Data::connect();
        $sql = 'SELECT ahs_id, letter from BusinessAHSID';
        $sth = $pdo->query($sql);
        $result = $sth->fetchAll();
        $pdo = null;
        return $result;
    }

    /***
     * @return array All of the AHS IDs for the businesses in the DB from the given id
     */
    public static function selectAHSIDsFrom($id){
        $pdo = Data::connect();
        $sql = $pdo->prepare('SELECT ahs_id, letter from BusinessAHSID WHERE id >= :id');
        $sql->bindValue(':id', $id);
        $sql->execute();
        $result = $sql->fetchAll();
        $pdo = null;
        return $result;
    }

    /******************************END businessAHSID*******************************************/

    /******************************START business**********************************************/

    /***
     * Puts a business in the business table
     * @param $ahsIDnThe AHS ID
     * @param $name The name of the business
     * @param $type The type of business
     * @param $address The address of the business
     * @param $phone The phone number of the business
     * @param $prmHldr The businesses permit holder
     */
    public static function insertBusiness($ahsID, $name, $type, $address, $phone, $prmHldr){
        $pdo = Data::connect();
        //insert business
        $sql = $pdo->prepare('INSERT INTO Business (ahs_id, name, type, address, phone, permit_holder)
                              VALUES(:ahsID, :name, :type, :address, :phone, :pHolder)');
        $sql->bindValue(':ahsID', $ahsID);
        $sql->bindValue(':name', $name);
        $sql->bindValue(':type', $type);
        $sql->bindValue(':address', $address);
        $sql->bindValue(':phone', $phone);
        $sql->bindValue(':pHolder', $prmHldr);
        try{
            $sql->execute();
        }catch (PDOException $e){
            die($e->getMessage());
        }
        $pdo = null;
    }

    /***
     * Add the lat and long into the business table
     * @param $lat Latitude of business
     * @param $lng Longitude of business
     * @param $id The ID of the business
     */
    public static function insertLatLng($lat, $lng, $id)
    {
        $pdo = Data::connect();
        //insert statement
        $sql = $pdo->prepare('UPDATE Business
                              SET lat = :lat, lng = :lng
                              WHERE id=:id');
        $sql->bindValue(':lat', $lat);
        $sql->bindValue(':lng', $lng);
        $sql->bindValue(':id', $id);
        try{
            $sql->execute();
        }catch (PDOException $e){
            die($e->getMessage());
        }
        $pdo = null;
    }

    /***
     * Get a business by it's ID
     * @param $id The business ID (primary key)
     * @return array Result of query
     */
    public static function selectBusinessByID($id){
        $pdo = Data::connect();
        $sql = $pdo->prepare('SELECT id, ahs_id, name, type, address, lat, lng, phone, permit_holder
                              FROM Business WHERE id = :id');
        $sql->bindValue(':id', $id);
        $sql->execute();
        $result = $sql->fetchAll();
        $pdo = null;
        return $result;
    }

    /***
     * Get the business with the given AHS ID
     * @param $ahsid The AHS ID of the business
     * @return array Query results
     */
    public static function selectBusinessByAHSID($ahsid){
        $pdo = Data::connect();
        $sql = $pdo->prepare('SELECT id, ahs_id, name, type, address, lat, lng, phone, permit_holder
                              FROM Business WHERE ahs_id = :theID');
        $sql->bindValue(':theID', $ahsid);
        $sql->execute();
        $result = $sql->fetchAll();
        $pdo = null;
        return $result;
    }

    /***
     * Get all of the businesses
     * @return array All of the businesses in the business table
     */
    public static function selectAllBusiness(){
        $pdo = Data::connect();
        $sql = $pdo->prepare("SELECT id, ahs_id, name, phone, lat, lng FROM Business WHERE lat !=''");
        $sql->execute();
        $result = $sql->fetchAll();
        $pdo = null;
        return $result;
    }

    /***
     * Search by business name
     * @param $name The name to search for
     * @return array Results of the query
     */
    public static function selectBusinessLikeName($name){
        $pdo = Data::connect();
        $sql = $pdo->prepare("SELECT id, ahs_id, name, type, address, lat, lng, phone, permit_holder
                              FROM Business WHERE name LIKE :name");
        $sql->bindValue(':name', '%' . $name . '%', PDO::PARAM_STR);
        $sql->execute();
        $result = $sql->fetchAll();
        $pdo = null;
        return $result;
    }

    /******************************END business**********************************************/

    /******************************START violation*******************************************/

    /***
     * Insert a violation into the DB
     * @param $ahsID The AHS ID of the business who had the violation
     * @param $name The name of the violation
     * @param $requirement The requirement violated
     * @param $comment Optional comment from inspector
     * @param $critical True if critical violation
     */
    public static function insertViolation($ahsID, $name, $requirement, $comment, $critical){
        $pdo = Data::connect();
        //insert statement
        $sql = $pdo->prepare('INSERT INTO Violation (ahs_id, violation, rationale, comments, critical)
                              VALUES(:ahsID, :name, :rqurmnt, :comment, :critical)');
        $sql->bindValue(':ahsID', $ahsID);
        $sql->bindValue(':name', $name);
        $sql->bindValue(':rqurmnt', $requirement);
        $sql->bindValue(':comment', $comment);
        $sql->bindValue(':critical', $critical);
        try{
            $sql->execute();
        }catch (PDOException $e){
            die($e->getMessage());
        }
        $pdo = null;
    }

    /***
     * Gets the violations for a business by it's AHS ID
     * @param $id The AHS ID of a business
     * @return array Violations
     */
    public static function selectViolationById($id){
        $pdo = Data::connect();
        $sql = $pdo->prepare("SELECT id, ahs_id, violation, rationale, comments, critical
                              FROM Violation WHERE ahs_id=:id AND comments != ''");
        $sql->bindValue(':id', $id);
        $sql->execute();
        $result = $sql->fetchAll();
        $pdo = null;
        return $result;
    }

    /******************************END violation**********************************************/

    /******************************START Inspection*******************************************/

    /***
     * Insert an inspection into the inspection table
     * @param $ahsID The AHS ID of the business who made the violation
     * @param $date The date of the inspection
     * @param $type The type of inspection
     * @param $action The action taken as a result of the violation
     */
    public static function insertInspection($ahsID, $date, $type, $action){
        $pdo = Data::connect();
        $sql = $pdo->prepare("INSERT INTO Inspection (ahs_id, type, action, date)
                              VALUES(:ahsID, :type, :action, :date)");
        $sql->bindValue(':ahsID', $ahsID);
        $sql->bindValue(':type', $type);
        $sql->bindValue(':action', $action);
        $sql->bindValue(':date', $date);
        try{
            $sql->execute();

        }catch (PDOException $e){
            die($e->getMessage());
        }
        $pdo = null;
    }

    /***
     * Select an inspection
     * @param $ahsID The AHS ID of the business who had the inspection
     * @param $date The date of the inspection
     * @return bool True if the inspeciton is already in the DB
     */
    public static function selectInspection($ahsID, $date){
        $pdo = Data::connect();
        $sql = $pdo->prepare('SELECT id, ahs_id, type, action, date
                              FROM Inspection WHERE ahs_id = :theID AND date = :date');
        $sql->bindValue(':theID', $ahsID);
        $sql->bindValue(':date', $date);
        $sql->execute();
        $result = $sql->fetchAll();
        $pdo = null;
        if(!empty($result)){
            return true;
        } else {
            return false;
        }
    }

    /******************************END Inspection*****************************************/

    /******************************START Top 10*******************************************/

    /***
     * Get the businesses with the most violations
     * @param $limit The number of results you want
     * @return array Sorted list of worst offenders
     */
    public static function selectTopViolators($limit){
        $pdo = Data::connect();
        $sql = $pdo->prepare("SELECT ahs_id, count(*) AS num
                              FROM Violation
                              GROUP BY ahs_id
                              ORDER BY num DESC
                              LIMIT :limit");
        $sql->bindValue(':limit', $limit, PDO::PARAM_INT);
        $sql->execute();
        $result = $sql->fetchAll();
        $pdo = null;
        return $result;
    }

    /***
     * Get the businesses with the most critical violations
     * @param $limit The number of results you want
     * @return array Sorted list of most critical violations
     */
    public static function selectTopCriticalViolators($limit){
        $pdo = Data::connect();
        $sql = $pdo->prepare("SELECT ahs_id, count(critical) AS num
                              FROM Violation
                              WHERE critical=1
                              GROUP BY ahs_id, critical
                              ORDER BY num DESC
                              LIMIT :limit");
        $sql->bindValue(':limit', $limit, PDO::PARAM_INT);
        $sql->execute();
        $result = $sql->fetchAll();
        $pdo = null;
        return $result;
    }

    /***
     * Get an array of the permit holders who own the most businesses
     * @param $limit The number of results you want
     * @return array Sorted list of top business owners
     */
    public static function selectTopPermitHolders($limit){
        $pdo = Data::connect();
        $sql = $pdo->prepare("SELECT permit_holder, count(*) AS num
                              FROM Business
                              GROUP BY permit_holder
                              ORDER BY num DESC
                              LIMIT :limit");
        $sql->bindValue(':limit', $limit, PDO::PARAM_INT);
        $sql->execute();
        $result = $sql->fetchAll();

        return $result;
    }

    /******************************END Top 10*******************************************/
}