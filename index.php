<?php
include(dirname(__FILE__) . '/inc/header.inc.php');
?>

<body>
<div id="map-canvas"></div>
<a href="https:github.com/ronmcg/Inspection-Mapper" target="_blank">
        <div id="source">
                Source
        </div>
</a>

<script type="text/javascript"
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCIDKrnJ-R3_rUqmg5pFvhVvYSlRRbE_0M">
</script>
<script type="text/javascript" src="js/OverlappingMarkerSpiderfier.js"></script>
<script type="text/javascript" src="js/inspection-mapper.js"></script>
<script type="text/javascript">
        $("ul li:nth-of-type(1)").addClass("active");
</script>
</body>
</html>

