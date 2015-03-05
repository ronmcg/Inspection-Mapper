<?php
include_once(dirname(__FILE__) . '/imCrawler/data/Data.php');
include_once(dirname(__FILE__) . '/inc/header.inc.php');

$number = 10;
if(isset($_GET['number'])){
    $number = intval(trim($_GET['number']));
    if(!is_int($number)){
        $number = 10;
    }
}
?>
<div class="container">
    <form method="get">
        <select name="number">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
        <input type="submit" value="Update">
    </form>
    <h2><b>Top <?php echo $number; ?> Health Violators</b></h2>
    <table class="table table-striped">
        <tr>
            <th>Rank</th>
            <th>Name</th>
            <th>Address</th>
            <th>Violations</th>
        </tr>
        <?php
        $top = Data::selectTopViolators($number);
        $business = array();
        foreach($top as $entry){
            $name = Data::selectBusinessByAHSID($entry['ahs_id']);
            array_push($business, $name);
        }

        for ($i =0 ; $i < count($business); $i++) {
            echo '<tr>';
            echo '<td>';
            echo $i + 1;
            echo '</td>';
            echo '<td>';
            echo $business[$i][0]['name'];
            echo '</td>';
            echo '<td>';
            echo $business[$i][0]['address'];
            echo '</td>';
            echo '<td>';
            echo $top[$i]['num'];
            echo '</td>';
        }
        ?>
    </table>

    <h2><b>Top <?php echo $number; ?> Critical Health Violators</b></h2>
    <table class="table table-striped">
        <tr>
            <th>Rank</th>
            <th>Name</th>
            <th>Address</th>
            <th>Critical Violations</th>
        </tr>
        <?php
        $top = Data::selectTopCriticalViolators($number);
        $business = array();
        foreach($top as $entry){
            $name = Data::selectBusinessByAHSID($entry['ahs_id']);
            array_push($business, $name);
        }

        for ($i =0 ; $i < count($business); $i++) {
            echo '<tr>';
            echo '<td>';
            echo $i + 1;
            echo '</td>';
            echo '<td>';
            echo $business[$i][0]['name'];
            echo '</td>';
            echo '<td>';
            echo $business[$i][0]['address'];
            echo '</td>';
            echo '<td>';
            echo $top[$i]['num'];
            echo '</td>';
        }
        ?>
    </table>

    <h2><b>Top <?php echo $number; ?> Business Owners</b></h2>
    <table class="table table-striped">
        <tr>
            <th>Rank</th>
            <th>Owner</th>
            <th>Number of Businesses</th>
        </tr>
        <?php
        $ph = Data::selectTopPermitHolders($number);

        for ($i =0 ; $i < count($ph); $i++) {
            echo '<tr>';
            echo '<td>';
            echo $i + 1;
            echo '</td>';
            echo '<td>';
            echo $ph[$i]['permit_holder'];
            echo '</td>';
            echo '<td>';
            echo $ph[$i]['num'];
            echo '</td>';
        }
        ?>
    </table>
</div>
<script type="text/javascript">
    $("ul li:nth-of-type(2)").addClass("active");
</script>
</body>
</html>
