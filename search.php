<?php
include_once(dirname(__FILE__) . '/imCrawler/data/Data.php');
include_once(dirname(__FILE__) . '/inc/header.inc.php');
$name = '';
if(isset($_GET['name'])){
    $name = trim($_GET['name']);
}
?>

<div class="container">
    <div class="well-sm">

        <h2>Search</h2>
        <form method="get">
            <input type="text" name="name">
            <input type="submit" value="Search">
        </form>
        <?php
        if($name != '') {
            ?>
            <h2><b>Results for <?php echo $name; ?></b></h2>
            <table class="table table-striped">
                <tr>
                    <th>Rank</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Violations</th>
                </tr>
                <?php
                $results = Data::selectBusinessLikeName($name);

                for ($i = 0; $i < count($results); $i++) {
                    echo '<tr>';
                    echo '<td>';
                    echo $i + 1;
                    echo '</td>';
                    echo '<td>';
                    echo $results[$i]['name'];
                    echo '</td>';
                    echo '<td>';
                    echo $results[$i]['address'];
                    echo '</td>';
                    echo '<td>';
                    $violations = Data::selectViolationById($results[$i]['ahs_id']);
                    foreach ($violations as $v) {
                        if($v['critical'] == 1){
                            echo '<strong>CRITICAL VIOLATION</strong>';
                        }
                        echo '<p>' . $v['comments'] . '</p>';
                    }
                    echo '</td>';
                }
                ?>
            </table>
        <?php
        }
        ?>
    </div>
</div>
<script type="text/javascript">
    $("ul li:nth-of-type(3)").addClass("active");
</script>
</body>
</html>
