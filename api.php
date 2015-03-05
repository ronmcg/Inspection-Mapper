<?php

include_once(dirname(__FILE__) . '/imCrawler/data/Data.php');

/**
 * Encode all items in multidimensional array in UTF8
 * @param $item
 * @param $key
 */
function utf8Array(&$item, $key)
{
    $item = utf8_encode($item);
}

if(isset($_GET['all'])){
    $data = Data::selectAllBusiness();
    array_walk_recursive($data, 'utf8Array');
    $data = json_encode($data, true);
    echo $data;
} else if(isset($_GET['ahsID'])) {
    $ahsID = $_GET['ahsID'];
    $data = Data::selectViolationById($ahsID);
    array_walk_recursive($data, 'utf8Array');
    $data = json_encode($data, true);
    echo $data;
} else if(isset($_GET['name'])) {
    $name = trim($_GET['name']);
    $data = Data::selectBusinessLikeName($name);
    array_walk_recursive($data, 'utf8Array');
    echo json_encode($data, true);
} else{
    //no query strings means show the API docs
    include_once(dirname(__FILE__) . '/inc/header.inc.php');
    ?>
<div class="container">
    <div class="well-sm">
        <h1>API <small>It's easy as 1, 2, 3</small></h1>
        <h2>Violations by ID</h2>
        <h3>GET</h3>
        <h3>Param: <code>ahsID</code></h3>
        <p class="lead">Get all of the violations from an Alberta Health Services ID.</p>
        <h3>Example: <code>/api.php?ahsID=0B4FAC8D-FCBC-4224-868E-51E438C08AF5</code></h3>
        <h3>Response:</h3>
    <pre>
        [{
            "id":"3630",
            "ahs_id":"0B4FAC8D-FCBC-4224-868E-51E438C08AF5",
            "name":"Mitillini's Pizza & Steak House",
            "type":"Restaurant",
            "address":"2 - 5720 Silver Springs Boulevard NW Calgary AB T3B 4N7",
            "lat":"51.104889",
            "lng":"-114.18786",
            "phone":"403-288-7737",
            "permit_holder":"Mohamed Zaarour"
        }]
    </pre>
    </div>

    <div class="well-sm">
        <h2>Businesses like name</h2>
        <h3>GET</h3>
        <h3>Param: <code>name</code></h3>
        <p class="lead">Get all of the businesses that are like the given name</p>
        <h3>Example: <code>/api.php?name=soup</code></h3>
        <h3>Response:</h3>
    <pre>
        [{
            "id":"3320",
            "ahs_id":"4AC11C23-A63C-4F67-B15A-560929C26A4E",
            "name":"Marcello's Market & Deli - Sandwich, Soups, Display & Coffee areas",
            "type":"Restaurant",
            "address":"215 - 300 5 Avenue SW Calgary AB T2P 0L4",
            "lat":"51.0486961",
            "lng":"-114.0684146",
            "phone":"403-232-6233",
            "permit_holder":"6357580 Canada Inc."
        },
        {
            "id":"3604",
            "ahs_id":"67B7587B-C567-4821-90F4-AE14729BAF47",
            "name":"Mina's Vietnamese Noodle Soup",
            "type":"Restaurant",
            "address":"715 - 12100 Macleod Trail SE Calgary AB T2J 7G9",
            "lat":"50.9448715",
            "lng":"-114.0689667",
            "phone":"403-225-8954",
            "permit_holder":"17055584 Alberta Ltd."
        },
        {
            "id":"3965",
            "ahs_id":"B5AF93D0-88CF-436A-A181-1321D0C2D6F0",
            "name":"Nourish Soupworks",
            "type":"Food Processor",
            "address":"D - 3513 78 Avenue SE Calgary AB T2C 1J7",
            "lat":"50.9826892",
            "lng":"-113.9854436",
            "phone":"403-279-6665",
            "permit_holder":"Nourish Soupworks Ltd."
        },
        {
            "id":"4472",
            "ahs_id":"1037F1F2-4693-4805-8833-07931C4F2F96",
            "name":"Primal Soup Company",
            "type":"Market Vendor",
            "address":"7711 Macleod Trail SW Calgary AB",
            "lat":"50.9975219",
            "lng":"-114.071709",
            "phone":"",
            "permit_holder":"Primal Grounds Ltd."
        },
        {
            "id":"4473",
            "ahs_id":"22EDC0A3-1174-4750-A9B1-C9CF2FCDABDC",
            "name":"Primal Soup Company - Main Kitchen",
            "type":"Restaurant",
            "address":"7711 Macleod Trail S Calgary AB",
            "lat":"50.9840253",
            "lng":"-114.0728675",
            "phone":"403-978-7243",
            "permit_holder":"Primal Grounds Ltd."
        },
        {
            "id":"4926",
            "ahs_id":"1A921AD4-0EF2-4722-8B91-556B644D8881",
            "name":"T & T Supermarket - Soup",
            "type":"Restaurant",
            "address":"1000 - 9650 Harvest Hills Boulevard NE Calgary AB T3K 0B3",
            "lat":"51.1411042",
            "lng":"-114.0700541",
            "phone":"403-237-6608",
            "permit_holder":"T & T Supermarket Incorporated"
        },
        {
            "id":"5595",
            "ahs_id":"084D973F-ECD6-42AA-A059-B53A32CDA121",
            "name":"Tuscan Soup Garden",
            "type":"Restaurant",
            "address":"1090 - 2600 Portland Street SE Calgary AB T2G 4M6",
            "lat":"51.0313879",
            "lng":"-114.032477",
            "phone":"403-262-9797",
            "permit_holder":"Andrew Kim"
        }]
    </pre>
    </div>
</div>
    <script type="text/javascript">
        $("ul li:nth-of-type(4)").addClass("active");
    </script>
    </body>
    </html>
<?php
}
?>

